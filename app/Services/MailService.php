<?php

namespace App\Services;

use Carbon\Carbon;
use App\Models\User;
use App\Models\Taskee;
use App\Mail\SendEmail;
use Illuminate\Support\Str;
use App\Models\Notification;
use App\Models\TaskSolution;
use App\Mail\NotificationMail;
use App\Mail\PaymentSuccessful;
use Illuminate\Support\Facades\Mail;

class MailService extends Service
{
    public function sendOTP(array $details)
    {
        // $otp = str_pad(mt_rand(0, 999999), 6, '0', STR_PAD_LEFT);
        $date = Carbon::now();
        $details['date'] = $date->format('d M, Y');
        // $otpExpires = $date->addMinutes(5);
        // $user = User::findOrFail($details['id']);
        // $user->otp = $otp;
        // $user->otp_expired = $otpExpires;
        // $user->save();
        Mail::to($details['email'])
            ->send(new SendEmail($details));
    }
    public function sendForgotOTP(array $details)
    {
        $otp = str_pad(mt_rand(0, 999999), 6, '0', STR_PAD_LEFT);
        $date = Carbon::now();
        $details['date'] = $date->format('d M, Y');
        $details['otp'] = $otp;
        $otpExpires = $date->addMinutes(5);
        $user = User::findOrFail($details['id']);
        $user->otp = $otp;
        $user->otp_expired = $otpExpires;
        $user->save();
        Mail::to($details['email'])
            ->send(new SendEmail($details));
    }

    /**
     * @param string $to: User_id to send mail
     * @param string $
     */
    public static function changeStatus(User $to, User $from, $status, TaskSolution $solution)
    {
        $title = $solution->task->title;
        $details['email'] = $to->email;
        $details['message'] = "Tasker đã đánh giá bài Task \"{$title}\" của bạn là: {$status}";
        $details['from'] = $from->id;
        $details['username'] = $to->username;
        $details['to'] = $to->id;
        $details['type'] = "Change Status";
        Notification::create($details);
        Mail::to($details['email'])
            ->send(new NotificationMail($details));
    }
    public static function submitTask(Taskee $taskee, array $details)
    {
        $details['type'] = 'Submit Task Solution';
        $details['message'] = "Taskee {$taskee->user->username} đã nộp Solution của Task \"{$details['task_title']}\".";
        Notification::create($details);
        Mail::to($details['email'])
            ->send(new NotificationMail($details));
    }
    public static function paymentSuccess($request)
    {
        // Giả sử $paymentDetails chứa thông tin thanh toán
        $paymentDetails = [
            'username' => $request['username'],
            'amount' => $request['amount'],
            'service' => $request['service'],
            'date' => now()->toDateTimeString(),
        ];

        // Gửi email
        Mail::to($request['email'])->send(new PaymentSuccessful($paymentDetails));
    }
}
