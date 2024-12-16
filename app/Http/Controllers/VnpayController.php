<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Services\VnpayService;
use App\Http\Response\ApiResponse;
use App\Models\Subscription;
use App\Services\MailService;

class VnpayController extends Controller
{
    private $VnpayService;
    public function __construct(VnpayService $service)
    {
        $this->VnpayService = $service;
    }
    public function createPaymentSubscription()
    {
        // Your code here to get data from request
        $val = $this->validator(request()->post(), [
            'code' => "nullable| exists:discounts,code",
            "service_id" => "required| exists:services,id",
        ]);
        if (array_key_exists('error', $val)) {
            return ApiResponse::BAD_REQUEST($val);
        }
        if (!auth()->guard()->user()->taskee->isSubcription()) {
            $url = $this->VnpayService->createPaymentSubscription(auth()->guard()->user()->taskee, $val['service_id'], $val['code']);
            return ApiResponse::OK($url);
        } else {
            return ApiResponse::FORBIDDEN('You have already subscribed');
        }
    }
    public function paymentReturn()
    {
        $vnp_HashSecret = env('VNP_HASH_SECRET');
        $vnp_SecureHash = request()->vnp_SecureHash;

        $inputData = request()->all();
        $vnp_SecureHash = $inputData['vnp_SecureHash'];
        unset($inputData['vnp_SecureHash']);
        ksort($inputData);
        $i = 0;
        $hashData = "";
        foreach ($inputData as $key => $value) {
            if ($i == 1) {
                $hashData = $hashData . '&' . urlencode($key) . "=" . urlencode($value);
            } else {
                $hashData = $hashData . urlencode($key) . "=" . urlencode($value);
                $i = 1;
            }
        }

        $secureHash = hash_hmac('sha512', $hashData, $vnp_HashSecret);
        $vnpTranId = $inputData['vnp_TransactionNo']; //Mã giao dịch tại VNPAY
        $vnp_BankCode = $inputData['vnp_BankCode']; //Ngân hàng thanh toán
        $vnp_Amount = $inputData['vnp_Amount'] / 100; // Số tiền thanh toán VNPAY phản hồi

        $Status = 0; // Là trạng thái thanh toán của giao dịch chưa có IPN lưu tại hệ thống của merchant chiều khởi tạo URL thanh toán.
        $orderId = $inputData['vnp_TxnRef'];

        try {
            //Check Orderid    
            //Kiểm tra checksum của dữ liệu
            if ($secureHash == $vnp_SecureHash) {
                //Lấy thông tin đơn hàng lưu trong Database và kiểm tra trạng thái của đơn hàng, mã đơn hàng là: $orderId            
                //Việc kiểm tra trạng thái của đơn hàng giúp hệ thống không xử lý trùng lặp, xử lý nhiều lần một giao dịch
                //Giả sử: $order = mysqli_fetch_assoc($result);   

                $order = Subscription::where('order_id', $orderId)->first();
                if ($order != NULL) {
                    if ($order->amount_paid == $vnp_Amount) //Kiểm tra số tiền thanh toán của giao dịch: giả sử số tiền kiểm tra là đúng. //$order["Amount"] == $vnp_Amount
                    {
                        if ($order->status == 'pending') {
                            if ($inputData['vnp_ResponseCode'] == '00' && $inputData['vnp_TransactionStatus'] == '00') {
                                $Status = 1; // Trạng thái thanh toán thành công
                            } else {
                                $Status = 2; // Trạng thái thanh toán thất bại / lỗi
                            }
                            //Cài đặt Code cập nhật kết quả thanh toán, tình trạng đơn hàng vào DB
                            //
                            //
                            //
                            //Trả kết quả về cho VNPAY: Website/APP TMĐT ghi nhận yêu cầu thành công 
                            if ($Status == 1) {
                                $order->status = 'success';
                                $order->transaction_id = $vnpTranId;
                                $order->taskee->update([
                                    'gold_expired' => $order->gold_expired,
                                    'gold_registration_date' => Carbon::now()
                                ]);
                            } else {
                                $order->status = 'fail';
                            }
                            $order->save();
                            $returnData['RspCode'] = '00';
                            $returnData['Message'] = 'Confirm Success';
                            MailService::paymentSuccess([
                                'username' => $order->taskee->user->username,
                                'amount' => $order->amount_paid,
                                'service' => $order->service->name,
                                'email' => $order->taskee->user->email
                            ]);
                        } else {
                            $returnData['RspCode'] = '02';
                            $returnData['Message'] = 'Order already confirmed';
                        }
                    } else {
                        $returnData['RspCode'] = '04';
                        $returnData['Message'] = 'invalid amount';
                    }
                    return redirect(env('FRONTEND_REDIRECT_URL_VNPAY') . "?code={$returnData['RspCode']}&message={$returnData['Message']}&orderID={$order->id}");
                } else {
                    $returnData['RspCode'] = '01';
                    $returnData['Message'] = 'Order not found';
                }
            } else {
                $returnData['RspCode'] = '97';
                $returnData['Message'] = 'Invalid signature';
            }
        } catch (\Exception $e) {
            $returnData['RspCode'] = '99';
            $returnData['Message'] = 'Unknow error' . get_class($e) . ": " . $e->getMessage();
        }
        //Trả lại VNPAY theo định dạng JSON
        return redirect(env('FRONTEND_REDIRECT_URL_VNPAY') . "?code={$returnData['RspCode']}&message={$returnData['Message']}");
    }
}
