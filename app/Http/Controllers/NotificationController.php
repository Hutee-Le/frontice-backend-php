<?php

namespace App\Http\Controllers;

use App\Models\Notification;
use App\Http\Response\ApiResponse;
use App\Http\Response\NotificationResponse;
use App\Http\Requests\StoreNotificationRequest;
use App\Http\Requests\UpdateNotificationRequest;

class NotificationController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $data = null;
        $query = Notification::query();
        $query->where('to', auth()->guard()->user()->id)->latest()->take(50);
        $notifications = Notification::customPaginateStatic(request(), $query);

        if ($notifications) {
            foreach ($notifications as $notification) {
                $data['notifications'][] = NotificationResponse::notification($notification);
            }
        }
        $data['total'] = $notifications->total();
        $data['currentPage'] = $notifications->currentPage();
        $data['lastPage'] = $notifications->lastPage();
        $data['perPage'] = $notifications->perPage();
        return ApiResponse::OK($data);
    }
    public function seen($id)
    {
        $notification = Notification::where('to', auth()->guard()->user()->id)->findOrFail($id);
        $notification->status = 'seen';
        $notification->save();
        return ApiResponse::OK(NotificationResponse::notification($notification));
    }
}
