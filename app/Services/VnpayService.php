<?php

namespace App\Services;

use App\Models\Discount;
use App\Models\Service;
use App\Models\Subscription;
use Carbon\Carbon;
use App\Models\Taskee;
use Illuminate\Support\Facades\Config;

class VnpayService
{
    protected $vnp_TmnCode;
    protected $vnp_HashSecret;
    protected $vnp_Url;

    public function __construct()
    {
        $this->vnp_TmnCode = config('vnpay.vnp_TmnCode');
        $this->vnp_HashSecret = config('vnpay.vnp_HashSecret');
        $this->vnp_Url = config('vnpay.vnp_Url');
    }

    // Hàm tạo URL thanh toán
    public function createPaymentSubscription(Taskee $taskee, $service_id, $code = null)
    {
        date_default_timezone_set('Asia/Ho_Chi_Minh');
        
        $service = Service::findOrFail($service_id);
        $discount = $code ? Discount::where('code', $code)->where('expired', ">=", now())->first() : null;
        if ($discount && !$discount->usable()) {
            $discount = null;
        }
        $amount_paid = $service->price *  (100 - $discount?->value) / 100;
        $vnp_TmnCode = env('VNP_TMN_CODE'); // Mã website của bạn
        $vnp_HashSecret = env('VNP_HASH_SECRET'); // Chuỗi bí mật
        $vnp_Url = env('VNP_URL');
        $vnp_Returnurl = env('VNP_RETURN_URL');
        $vnp_TxnRef = time() . $taskee->user->username; // Mã đơn hàng
        $vnp_OrderInfo = "Thanh toán Gói Gold Frontice";
        $vnp_OrderType = 'billpayment';
        $vnp_Locale = 'vn';
        $vnp_BankCode = 'NCB';
        $vnp_IpAddr = request()->ip();
        $expire = Carbon::now()->addMinutes(15)->format('YmdHis');
        $inputData = array(
            "vnp_Version" => "2.1.0",
            "vnp_TmnCode" => $vnp_TmnCode,
            "vnp_Command" => "pay",
            "vnp_Amount" => $amount_paid * 100,
            "vnp_CreateDate" => Carbon::now()->format('YmdHis'),
            "vnp_CurrCode" => "VND",
            "vnp_IpAddr" => $vnp_IpAddr,
            "vnp_Locale" => $vnp_Locale,
            "vnp_OrderInfo" => $vnp_OrderInfo,
            "vnp_OrderType" => $vnp_OrderType,
            "vnp_ReturnUrl" => $vnp_Returnurl,
            "vnp_TxnRef" => $vnp_TxnRef,
            "vnp_ExpireDate" => $expire
        );

        if (isset($vnp_BankCode) && $vnp_BankCode != "") {
            $inputData['vnp_BankCode'] = $vnp_BankCode;
        }

        ksort($inputData);
        $query = "";
        $i = 0;
        $hashdata = "";
        foreach ($inputData as $key => $value) {
            if ($i == 1) {
                $hashdata .= '&' . urlencode($key) . "=" . urlencode($value);
            } else {
                $hashdata .= urlencode($key) . "=" . urlencode($value);
                $i = 1;
            }
            $query .= urlencode($key) . "=" . urlencode($value) . '&';
        }

        $vnp_Url = $vnp_Url . "?" . $query;
        if (isset($vnp_HashSecret)) {
            $vnpSecureHash =   hash_hmac('sha512', $hashdata, $vnp_HashSecret); //  
            $vnp_Url .= 'vnp_SecureHash=' . $vnpSecureHash;
        }
        $gold_expires = Carbon::now();
        switch ($service->type) {
            case 'monthly':
                $gold_expires = $gold_expires->addMonth();
                break;
            case 'yearly':
                $gold_expires = $gold_expires->addYear();
                break;
            case '3-monthly':
                $gold_expires = $gold_expires->addMonths(3);
                break;
            case '6-monthly':
                $gold_expires = Carbon::now()->addMonths(6);
                break;
            default:
                return ['code' => '01', 'message' => 'Invalid service type'];
        }
        Subscription::create([
            'taskee_id' => $taskee->id,
            'service_id' => $service->id,
            'discount_id' => $discount?->id,
            'order_id' => $vnp_TxnRef,
            'expired' => Carbon::parse($expire),
            'amount_paid' => $amount_paid,
            'gold_expired' => $gold_expires,
            'payment_method' => 'vnpay',
            'status' => 'pending'
        ]);

        return ['url' => $vnp_Url];
    }
}
