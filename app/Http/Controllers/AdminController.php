<?php

namespace App\Http\Controllers;

use Validator;
use Carbon\Carbon;
use App\Models\User;
use App\Models\Admin;
use App\Models\Taskee;
use App\Models\Tasker;
use Illuminate\Support\Str;
use App\Models\Subscription;
use App\Services\UserService;
use App\Services\AdminService;
use Illuminate\Http\JsonResponse;
use App\Http\Response\ApiResponse;
use Illuminate\Support\Facades\DB;
use App\Http\Requests\StoreAdminRequest;
use App\Http\Requests\UpdateAdminRequest;

class AdminController extends Controller
{
    private $users;
    private $admins;
    public function __construct(UserService $userService, AdminService $Admin)
    {
        $this->users = $userService;
        $this->admins = $Admin;
    }
    public function index()
    {
        $data = [];
        $currentDate = Carbon::today();      // Ngày hiện tại
        $currentMonth = Carbon::now()->month; // Tháng hiện tại
        $currentYear = Carbon::now()->year;  // Năm hiện tại
        $total = Subscription::where('status', 'success')->sum('amount_paid');
        $dailyTotal = Subscription::where('status', 'success')->whereDate('created_at', '=', $currentDate)->sum('amount_paid');
        $monthlyTotal = Subscription::where('status', 'success')->whereMonth('created_at', '=', $currentMonth)->whereYear('created_at', '=', $currentYear)->sum('amount_paid');
        $yearlyTotal = Subscription::where('status', 'success')->whereYear('created_at', '=', $currentYear)->sum('amount_paid');
        $premiumer = Taskee::where('gold_expired', '>', Carbon::now())->count();
        $taskees = Taskee::count();
        $taskers = Tasker::count();
        $data['statistic'] = ['total' => $total, 'dailyTotal' => $dailyTotal, 'monthlyTotal' => $monthlyTotal, 'yearlyTotal' => $yearlyTotal];
        $data['premiumAccounts'] = $premiumer;
        $data['totalTaskees'] = $taskees;
        $data['totalTaskers'] = $taskers;
        return ApiResponse::OK($data);
    }
    public function getDailyRevenues()
    {
        $startDate = request()->query('start_date');
        $endDate = request()->query('end_date', now());

        $revenues = DB::table('subscriptions')
            ->select(DB::raw('DATE(created_at) as date'), DB::raw('SUM(amount_paid) as total'))
            ->where('status', 'success') // Chỉ tính doanh thu với status = success
            ->whereBetween('created_at', [$startDate, $endDate])
            ->groupBy('date')
            ->orderBy('date', 'ASC')
            ->get();

        return ApiResponse::OK(
            $revenues
        );
    }
    public function getMonthlyRevenues()
    {
        $startDate = request()->query('start_date');
        $endDate = request()->query('end_date', now());

        $revenues = DB::table('subscriptions')
            ->select(DB::raw('YEAR(created_at) as year'), DB::raw('MONTH(created_at) as month'), DB::raw('SUM(amount_paid) as total'))
            ->where('status', 'success')
            ->whereBetween('created_at', [$startDate, $endDate])
            ->groupBy('year', 'month')
            ->orderBy('year', 'ASC')
            ->orderBy('month', 'ASC')
            ->get();

        return ApiResponse::OK(
            $revenues
        );
    }
    public function getYearlyRevenues()
    {
        $startDate = request()->query('start_date');
        $endDate = request()->query('end_date', now());

        $revenues = DB::table('subscriptions')
            ->select(DB::raw('YEAR(created_at) as year'), DB::raw('SUM(amount_paid) as total'))
            ->where('status', 'success')
            ->whereBetween('created_at', [$startDate, $endDate])
            ->groupBy('year')
            ->orderBy('year', 'ASC')
            ->get();

        return ApiResponse::OK(
            $revenues
        );
    }

    //GET USERS
    public function getTaskees()
    {
        $res = new ApiResponse();
        $taskees = $this->users->getUsersByRole('taskee');
        $res->setResponse('success', $taskees);
        return $res();
    }
    public function getTaskers()
    {
        $res = new ApiResponse();
        $taskers = $this->users->getUsersByRole('tasker');
        $res->setResponse('success', $taskers, 200);
        return $res();
    }
    public function getAdmins()
    {
        $res = new ApiResponse();
        $admins = $this->users->getUsersByRole('admin');
        $res->setResponse('success', $admins);
        return $res();
    }
    public function getUserByUsername($uname)
    {
        $res = new ApiResponse();
        $this->validator(['username' => $uname], ['username' => 'required|string'], ['username.required' => 'kaksjfk']);
        $user = $this->users->getUserByUsername($uname);
        if (!$user) {
            $res->setResponse('USER NOT FOUND', null, 404);
        } else
            $res->setResponse('success', $user);
        return $res();
    }



    //CREATE USER
    public function createAdmin(): JsonResponse
    {
        $response = new ApiResponse();
        $validate = $this->validator_create_admin(request()->post());
        if (array_key_exists('error', $validate)) {
            return ApiResponse::BAD_REQUEST($validate);
        }
        $user = $this->users->createAdmin($validate);
        if ($user instanceof JsonResponse) {
            return $user;
        }
        $response->setResponse('Admin created successfully', $user, 201);
        return $response();
    }
    public function createTaskee(): JsonResponse
    {
        $validator = $this->validator_create_taskee(request()->post());
        $response = new ApiResponse();
        if (array_key_exists('error', $validator)) {
            return ApiResponse::BAD_REQUEST($validator);
        }
        $user = $this->users->createTaskee($validator);
        if ($user instanceof JsonResponse) {
            return $user;
        }
        $response->setResponse('Taskee created successfully', $user, 201);
        return $response();
    }
    public function createTasker(): JsonResponse
    {
        $validator = $this->validator_create_tasker(request()->post());
        $response = new ApiResponse();
        if (array_key_exists('error', $validator)) {
            return ApiResponse::BAD_REQUEST($validator);
        }

        $user = $this->users->createTasker($validator);
        if ($user instanceof JsonResponse)
            return $user;

        $response->setResponse('Tasker created successfully', $user, 201);
        return $response();
    }
    public function updateAdmin($username): JsonResponse
    {
        $validator = $this->validator_update_admin(request()->post());
        $response = new ApiResponse();
        if (array_key_exists('error', $validator)) {
            return ApiResponse::BAD_REQUEST($validator);
        }

        $user = $this->admins->updateAdmin($username, $validator);
        if ($user instanceof JsonResponse)
            return $user;

        $response->setResponse('Admin updated successfully', $user, 201);
        return $response();
    }
    public function updateTasker($username): JsonResponse
    {
        $validator = $this->validator_update_tasker(request()->post());
        $response = new ApiResponse();
        if (array_key_exists('error', $validator)) {
            return ApiResponse::BAD_REQUEST($validator);
        }

        $user = $this->admins->updateTasker($username, $validator);
        if ($user instanceof JsonResponse)
            return $user;

        $response->setResponse('Tasker updated successfully', $user, 201);
        return $response();
    }
    public function updateTaskee($username): JsonResponse
    {
        $validator = $this->validator_update_taskee(request()->post());
        $response = new ApiResponse();
        if (array_key_exists('error', $validator)) {
            return ApiResponse::BAD_REQUEST($validator);
        }

        $user = $this->admins->updateTaskee($username, $validator);
        if ($user instanceof JsonResponse)
            return $user;

        $response->setResponse('Taskee updated successfully', $user, 201);
        return $response();
    }
    public function showTaskerDisapprove()
    {
        $taskers = $this->admins->showTaskerDisapprove();
        return $taskers;
    }

    public function approveTasker()
    {
        $validator = $this->validator(request()->post(), [
            'tasker_id' => 'required|exists:taskers,id'
        ]);
        if (array_key_exists('error', $validator)) {
            return ApiResponse::BAD_REQUEST($validator);
        }
        $user = $this->admins->approveTasker(auth()->guard()->user()->admin, $validator['tasker_id']);
        return $user;
    }
    public function deleteUser($id)
    {
        $user = User::findOrFail($id);
        if (!$user) {
            return ApiResponse::NOT_FOUND();
        }

        return $this->admins->deleteUser($user);;
    }
}
