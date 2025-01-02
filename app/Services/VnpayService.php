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
        // Lấy thông tin cấu hình từ ENV hoặc trực tiếp
        $this->vnp_TmnCode = "35FBTFEX"; // Mã website tại VNPAY
        $this->vnp_HashSecret = "SQ1ML23ZB7O9A9V5NAJQI2SYZCI3DYRQ"; // Chuỗi bí mật
        $this->vnp_Url = "https://sandbox.vnpayment.vn/paymentv2/vpcpay.html"; // URL thanh toán của VNPAY
    }

    // Hàm tạo URL thanh toán
    public function createPaymentSubscription(Taskee $taskee, $service_id, $code = null)
    {
        date_default_timezone_set('Asia/Ho_Chi_Minh');

        // Lấy thông tin dịch vụ và chiết khấu
        $service = Service::findOrFail($service_id);
        $discount = $code ? Discount::where('code', $code)->where('expired', ">=", now())->first() : null;
        if ($discount && !$discount->usable()) {
            $discount = null;
        }
        $amount_paid = $service->price *  (100 - $discount?->value) / 100;

        // Lấy thông tin cấu hình từ ENV
        $vnp_TmnCode = $this->vnp_TmnCode;
        $vnp_HashSecret = $this->vnp_HashSecret;
        $vnp_Url = $this->vnp_Url;
        $vnp_Returnurl = "https://localhost/vnpay_php/vnpay_return.php"; // URL trả về

        // Mã đơn hàng duy nhất
        $vnp_TxnRef = time() . uniqid();
        $vnp_OrderInfo = "Thanh toán Gói Gold Frontice";
        $vnp_OrderType = 'billpayment';
        $vnp_Locale = 'vn';
        $vnp_BankCode = 'NCB';
        $vnp_IpAddr = request()->ip();
        $expire = Carbon::now()->addMinutes(15)->format('YmdHis');

        // Tạo input data gửi sang VNPay server
        $inputData = array(
            "vnp_Version" => "2.1.0",
            "vnp_TmnCode" => $vnp_TmnCode,
            "vnp_Amount" => $amount_paid * 100,
            "vnp_Command" => "pay",
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

        // Tạo URL thanh toán
        $vnp_Url = $vnp_Url . "?" . $query;

        if (isset($vnp_HashSecret)) {
            // Tính toán mã bảo mật
            $vnpSecureHash = hash_hmac('sha512', $hashdata, $vnp_HashSecret);
            $vnp_Url .= 'vnp_SecureHash=' . $vnpSecureHash;
        }

        // Tính toán ngày hết hạn của dịch vụ
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

        // Lưu thông tin vào cơ sở dữ liệu
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

        // Trả về URL thanh toán
        return ['url' => $vnp_Url];
    }
}
