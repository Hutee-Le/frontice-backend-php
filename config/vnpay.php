<?php
date_default_timezone_set('Asia/Ho_Chi_Minh');
/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

$vnp_TmnCode = "35FBTFEX"; //Mã định danh merchant kết nối (Terminal Id)
$vnp_HashSecret = "SQ1ML23ZB7O9A9V5NAJQI2SYZCI3DYRQ"; //Secret key
$vnp_Url = "https://sandbox.vnpayment.vn/paymentv2/vpcpay.html";
$vnp_Returnurl = "http://localhost/vnpay_php/vnpay_return.php";
$vnp_apiUrl = "http://sandbox.vnpayment.vn/merchant_webapi/merchant.html";
$apiUrl = "https://sandbox.vnpayment.vn/merchant_webapi/api/transaction";
//Config input format
//Expire
$startTime = date("YmdHis");
$expire = date('YmdHis', strtotime('+15 minutes', strtotime($startTime)));
return [
    'vnp_TmnCode' => $vnp_TmnCode,
    'vnp_HashSecret' => $vnp_HashSecret,
    'vnp_Url' => $vnp_Url,
    'vnp_Returnurl' => $vnp_Returnurl,
    'vnp_apiUrl' => $vnp_apiUrl,
    'apiUrl' => $apiUrl,
    'expire' => $expire,
    'startTime' => $startTime,
    'expire' => $expire,
    'orderType' => 'billpayment',
    'locale' => 'vn',
    'bankCode' => 'NCB',
    'amount' => 100000,
    'orderInfo' => 'Thanh toán đơn hàng',
];
