<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\TaskController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\VnpayController;
use App\Http\Controllers\TaskeeController;
use App\Http\Controllers\TaskerController;
use App\Http\Controllers\CommentController;
use App\Http\Controllers\ServiceController;
use App\Http\Controllers\DiscountController;
use App\Http\Controllers\ChallengeController;
use App\Http\Controllers\InteractionController;
use App\Http\Controllers\TaskCommentController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\TaskSolutionController;
use App\Http\Controllers\Upload\UploadController;
use App\Http\Controllers\ChallengeSolutionController;
use App\Http\Controllers\Auth\ForgotPasswordController;
use App\Http\Controllers\MentorController;

//Auth
Route::group([
    'middleware' => 'api',
    'prefix' => 'auth'
], function ($router) {
    Route::post('/send-otp', [AuthController::class, 'sendOtp']);
    Route::post('/verify-otp', [AuthController::class, 'verifyOtp']);
    Route::post('/register', [AuthController::class, 'register'])->name('register');
    Route::post('/login', [AuthController::class, 'login'])->name('login');
    Route::put('/update', [AuthController::class, 'update'])->middleware('auth:api')->name('update');
    Route::post('/upload/cv', [UploadController::class, 'cv'])->middleware('role:taskee');
    Route::delete('/delete', [UploadController::class, 'remove'])->middleware('role:all');
    Route::post('/upload/image', [UploadController::class, 'image'])->middleware('role:all');
    Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth:api')->name('logout');
    Route::put('/change-password', [AuthController::class, 'changePassword'])->middleware('auth:api')->name('changePassword');
    Route::post('/refresh', [AuthController::class, 'refresh'])->name('refresh');
    Route::post('/verify', [AuthController::class, 'verifyEmail']);
    Route::get('/me', [AuthController::class, 'me'])->middleware(['auth:api', 'role:all'])->name('me');
    Route::post('/otp-resend', [AuthController::class, 'resendOTP']);
    Route::post('/forgotPassword/send', [ForgotPasswordController::class, 'forgot']);
    Route::post('/forgotPassword/verify', [ForgotPasswordController::class, 'verifyEmail']);
    Route::post('/forgotPassword/reset', [ForgotPasswordController::class, 'resetPassword']);

    Route::get('/github', [AuthController::class, 'redirectToGitHub'])->middleware(['web']);
    Route::get('/github/callback', [AuthController::class, 'handleGitHubCallback'])->middleware(['web']);
});
Route::get('/payment-vnpay', [VNPayController::class, 'createPayment'])->middleware(['api']);
Route::get('/vnpay/return', [VnpayController::class, 'paymentReturn'])->name('vnpay.return')->middleware(['web']);
//Users
Route::group(['prefix' => 'taskees', 'middleware' => ['api', 'role:taskee']], function () {

    Route::post('/follow/{username}', [TaskeeController::class, 'followTasker']);
    Route::get('/following', [TaskeeController::class, 'getFollows']);
    Route::delete('/unfollow/{username}', [TaskeeController::class, 'unfollowTasker']);
});
Route::get('taskers/followers', [TaskerController::class, 'getFollowers']);
//ROLE:ALL
Route::group(['middleware' => ['api', 'role:all']], function () {
    Route::get('taskers/{username}', [TaskerController::class, 'getTaskerByUsername']);
    Route::get('taskee/challenge-solutions/{id}', [ChallengeSolutionController::class, 'getSolutionsByTaskeeId']);
    Route::get('taskees/{username}', [TaskeeController::class, 'getTaskeeByUsername']);
    Route::get('notifications', [NotificationController::class, 'index']);
    Route::get('notifications/{id}', [NotificationController::class, 'seen']);
    Route::get('tasks/{id}', [TaskController::class, 'getTaskById']);
});
Route::get('technical', [ChallengeController::class, 'getTechnical']);

//Admins
Route::group(['prefix' => 'admin', 'middleware' => ['api', 'admin:root']], function () {
    Route::get('statistics', [AdminController::class, 'index']);
    Route::get('/revenues/daily', [AdminController::class, 'getDailyRevenues']);
    Route::get('/revenues/monthly', [AdminController::class, 'getMonthlyRevenues']);
    Route::get('/revenues/yearly', [AdminController::class, 'getYearlyRevenues']);

    Route::post('user/create', [AdminController::class, 'createAdmin']);
    Route::get('get/taskees', [AdminController::class, 'getTaskees']);
    Route::get('get/taskees/premium', [TaskeeController::class, 'getPremiumAccounts']);
    Route::get('get/admins', [AdminController::class, 'getAdmins']);
    Route::get('get/taskers', [AdminController::class, 'getTaskers']);
    Route::get('get/user/{username}', [AdminController::class, 'getUserbyUsername']);
    Route::put('update/admin/{username}', [AdminController::class, 'updateAdmin']);
    Route::put('update/taskee/{username}', [AdminController::class, 'updateTaskee']);
    Route::put('update/tasker/{username}', [AdminController::class, 'updateTasker']);
    Route::get('taskers/disapprove', [AdminController::class, 'showTaskerDisapprove']);
    Route::post('tasker/approve', [AdminController::class, 'approveTasker']);
    Route::delete('/delete/{id}', [AdminController::class, 'deleteUser']);
});
Route::group(['prefix' => 'admin/tasks', 'middleware' => ['api', 'admin:challenge']], function () {
    Route::get('', [TaskController::class, 'getAll']);

    Route::get('reports', [TaskController::class, 'getTaskReport']);
    Route::get('/filters', [TaskController::class, 'getFilters']);
    Route::get('reports/{id}', [TaskController::class, 'getTasksReportDetail']);
    Route::put('/valid', [TaskController::class, 'valid']);
    Route::put('/invalid', [TaskController::class, 'invalid']);
    Route::get('/{id}/solutions', [TaskSolutionController::class, 'AdminGetTaskSolutionByTaskId']);
    Route::get('/solutions/{id}', [TaskSolutionController::class, 'adminGetTaskSolution']);
    Route::get('/solutions', [TaskSolutionController::class, 'getAll']);
});
Route::group(['prefix' => 'admin/challenges', 'middleware' => ['api', 'admin:challenge']], function () {
    Route::get('', [ChallengeController::class, 'adminGetAll']);
    Route::get('comments', [CommentController::class, 'adminGetComment']);
    Route::get('comments/{id}', [CommentController::class, 'getComments']);
    Route::delete('comments/{id}/delete', [CommentController::class, 'adminRemove']);
    Route::get('/filters', [ChallengeController::class, 'getFilters']);
    Route::get('/solutions/reports', [ChallengeSolutionController::class, 'getSolutionReport']);
    Route::put('/solutions/reports/{id}', [ChallengeSolutionController::class, 'changeStatus']);
    Route::get('/{id}/taskees', [ChallengeSolutionController::class, 'getTaskeeByChallengeID']);
    Route::get('/{id}/solutions', [ChallengeSolutionController::class, 'adminGetSolutionsByChallengeID']);
    Route::get('/solutions/{id}', [ChallengeSolutionController::class, 'adminGetSotlutionByID']);
    Route::get('/solutions', [ChallengeSolutionController::class, 'getAll']);
    Route::put('/solutions/update/{id}', [ChallengeSolutionController::class, 'update']);
    Route::delete('/solutions/delete/{id}', [ChallengeSolutionController::class, 'deleteByAdmin']);
    Route::get('/{id}', [ChallengeController::class, 'adminGetChallengeById']);
    Route::post('/create', [ChallengeController::class, 'create']);
    Route::put('/update/{id}', [ChallengeController::class, 'update']);
    Route::delete('/delete/{id}', [ChallengeController::class, 'destroy']);
    Route::post('upload/image', [UploadController::class, 'imageChallenge']);
    Route::post('upload/source', [UploadController::class, 'sourceChallenge']);
    Route::post('upload/figma', [UploadController::class, 'figmaChallenge']);
    Route::delete('delete-file', [UploadController::class, 'remove']);
});
//Admin Challenge & Admin Mentors
Route::group(['prefix' => 'admin/challenge-solutions'], function () {
    Route::get('', [MentorController::class, 'getChallenges'])->middleware('admin:challenge&mentor');
    Route::get('/{id}', [ChallengeSolutionController::class, 'adminGetSotlutionByID'])->middleware('admin:challenge&mentor')->where('id', '[0-9a-fA-F\-]{36}');
    Route::get('/feedback', [MentorController::class, 'getFeedback'])->middleware('admin:challenge&mentor');
    Route::post('/{id}', [MentorController::class, 'feedback'])->middleware('admin:challenge&mentor');
});

//Tasks
Route::group(['prefix' => 'tasks', 'middleware' => ['api', 'role:tasker']], function () {
    Route::post('create', [TaskController::class, 'create']);
    Route::get('', [TaskController::class, 'getTasks']);
    Route::put('update/{id}', [TaskController::class, 'update']);
    Route::delete('/delete/{id}', [TaskController::class, 'destroy']);
    Route::post('upload/image', [UploadController::class, 'imageTask']);
    Route::post('upload/source', [UploadController::class, 'sourceTask']);
    Route::post('upload/figma', [UploadController::class, 'figmaTask']);
    Route::delete('delete-file', [UploadController::class, 'remove']);
});
Route::prefix('task')->middleware('role:taskee')->group(function () {
    Route::get('/get', [TaskController::class, 'taskeeGetTasks']);
    Route::get('/get/{username}', [TaskController::class, 'taskeeGetTasksByTaskerUsername']);
    Route::post('/report', [TaskController::class, 'reportTask']);
});
Route::prefix('task-solution')->middleware('role:tasker')->group(function () {
    Route::get('/get/{id}', [TaskSolutionController::class, 'getTaskSolution']);
    Route::get('get-solutions/{id}', [TaskSolutionController::class, 'getTaskSolutions']);
    Route::put('change-status/{id}', [TaskSolutionController::class, 'updateTaskSolution']);
    Route::delete('delete/{id}', [TaskSolutionController::class, 'deleteTaskSolution']);
});
Route::prefix('task-solution')->middleware('role:tt')->group(function () {
    Route::post('/comment', [TaskCommentController::class, 'create']);
    Route::get('/comments/{id}', [TaskCommentController::class, 'getComments']);
    Route::get('/comment/reply/{id}', [TaskCommentController::class, 'getReplys']);
    Route::put('/comment/{id}', [TaskCommentController::class, 'edit']);
    Route::delete('/comment/{id}', [TaskCommentController::class, 'remove']);
});
Route::get('task-solution/comment/{id}', [TaskCommentController::class, 'getComments'])->middleware('admin:challenge');
//DOWNLOAD
Route::get('task/download-source/{id}', [TaskSolutionController::class, 'downloadSource'])->middleware('role:taskee');
Route::get('task/download-figma/{id}', [TaskSolutionController::class, 'downloadFigma'])->middleware('role:taskee');
Route::post('task/join/{id}', [TaskSolutionController::class, 'joinTask'])->middleware('role:taskee');

Route::prefix('challenges')->group(function () {
    Route::get('/', [ChallengeController::class, 'index']);
    Route::post('/{id}/join', [ChallengeSolutionController::class, 'joinChallenge'])->middleware('role:taskee');
    Route::get('/{id}/download', [ChallengeController::class, 'getLinkDownload'])->middleware('role:taskee');
    Route::get('/{id}/figma', [ChallengeController::class, 'getLinkFigma'])->middleware('role:taskee');
    Route::get('/{id}', [ChallengeController::class, 'getChallengeById']);
});

//Challenge Solution

Route::prefix('solutions')->middleware('role:taskee')->group(function () {
    Route::get('/', [ChallengeSolutionController::class, 'index']);
    Route::get('/submitted', [ChallengeSolutionController::class, 'getMySolutionSubmitted']);
    Route::post('/challenge/submit', [ChallengeSolutionController::class, 'submitChallenge']);
    Route::get('/challenge', [ChallengeSolutionController::class, 'getChallenge']);
    Route::get('/challenge/get/{id}', [ChallengeSolutionController::class, 'getSolutionById']);
    Route::get('/challenges/{id}', [ChallengeSolutionController::class, 'taskeeGetSolutionsByChallengeID']);
    Route::delete('/{id}', [ChallengeSolutionController::class, 'delete']);
    //Interaction
    Route::post('interaction/{id}', [InteractionController::class, 'index']);
    Route::delete('interaction/{id}', [InteractionController::class, 'destroy']);

    //comment
    Route::post('comment', [CommentController::class, 'create']);
    Route::get('comment/{id}', [CommentController::class, 'getComments']);
    Route::put('comment/{id}', [CommentController::class, 'edit']);
    Route::delete('comment/{id}', [CommentController::class, 'remove']);
    Route::get('comment/reply/{id}', [CommentController::class, 'getReply']);

    //task
    Route::post('task/{id}/submit', [TaskSolutionController::class, 'submitTask']);
    Route::get('task/get/{id}', [TaskSolutionController::class, 'taskeeGetTaskSolution']);
    Route::get('tasks/', [TaskSolutionController::class, 'taskeeGetTaskSolutions']);
    Route::get('tasks/submitted/', [TaskSolutionController::class, 'taskeeGetTaskSolutionsSubmitted']);
    Route::get('task', [TaskSolutionController::class, 'taskeeGetTaskSolutions']);
    Route::delete('/task/{id}', [TaskSolutionController::class, 'delete']);
});

//SUBSCRIPTION
Route::prefix('subscription')->group(function () {
    Route::get('/', [ServiceController::class, 'index']);
    Route::put('/update-price/{id}', [ServiceController::class, 'update'])->middleware('admin:root');
    Route::post('/register', [VnpayController::class, 'createPaymentSubscription'])->middleware('role:taskee');
    Route::get('/discount', [DiscountController::class, 'isUsable'])->middleware('role:taskee');;
});
//discount
Route::prefix('admin/discounts')->middleware('admin:root')->group(function () {
    Route::get('/', [DiscountController::class, 'index']);
    Route::post('/create', [DiscountController::class, 'store']);
    Route::put('/update/{id}', [DiscountController::class, 'update']);
    Route::delete('/delete/{id}', [DiscountController::class, 'destroy']);
});
