<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\AccountController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\admin\JobApplicationController;
use App\Http\Controllers\Admin\JobController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\JobsController;
use App\Models\JobApplication;

// Route::get('/', function () {
//    return view('welcome');
//});

// job routes
Route::get('/', [HomeController::class, 'index'])->name('home');
Route::get('/jobs', [JobsController::class, 'index'])->name('jobs');
Route::get('/jobs/detail{id}', [JobsController::class, 'detail'])->name('jobsDetail');
Route::post('/apply-job', [JobsController::class, 'applyJob'])->name('applyJob');
Route::post('/save-job', [JobsController::class, 'saveJob'])->name('saveJob');

// forgot password
Route::get('/forgot-password', [AccountController::class, 'forgotPassword'])->name('account.forgotPassword');
Route::post('/process-forgot-password', [AccountController::class, 'processForgotPassword'])->name('account.processForgotPassword');
Route::get('/forgot-password/{token}', [AccountController::class, 'resetPassword'])->name('account.resetPassword');
Route::post('/process-reset-password', [AccountController::class, 'processResetPassword'])->name('account.processResetPassword');

// account routes
Route::get('/account/register', [AccountController::class, 'registration'])->name('account.registration');
Route::post('/account/process-register', [AccountController::class, 'processRegistration'])->name('account.processRegistration');
Route::get('/account/login', [AccountController::class, 'login'])->name('account.login')->middleware('guest');
Route::post('/account/authenticate', [AccountController::class, 'authenticate'])->name('account.authenticate');
// Cho role user
Route::group(['middleware' => 'CheckUser'], function(){
    Route::get('/account/profile', [AccountController::class, 'profile'])->name('account.profile');
    Route::get('/account/logout', [AccountController::class, 'logout'])->name('account.logout');
    Route::put('/account/update-profile', [AccountController::class, 'updateProfile'])->name('account.updateProfile');
    Route::post('/update-profile-pic',[AccountController::class,'updateProfilePic'])->name('account.updateProfilePic');
    Route::get('/account/create-job', [AccountController::class, 'createJob'])->name('account.createJob');
    Route::post('/account/save-job', [AccountController::class, 'saveJob'])->name('account.saveJob');
    Route::get('/account/my-jobs', [AccountController::class, 'myJobs'])->name('account.myJobs');
    Route::get('/account/my-jobs/edit/{jobId}', [AccountController::class, 'editJob'])->name('account.editJob');
    Route::post('/account/update-job/{jobId}', [AccountController::class, 'updateJob'])->name('account.updateJob');
    Route::post('/account/delete-job', [AccountController::class, 'deleteJob'])->name('account.deleteJob');
    Route::get('/account/my-job-applications', [AccountController::class, 'myJobApplications'])->name('account.myJobApplications');
    Route::post('/account/remove-job-application', [AccountController::class, 'removeJobs'])->name('account.removeJobs');
    Route::get('/account/saved-jobs', [AccountController::class, 'savedJobs'])->name('account.savedJobs');
    Route::post('/account/remove-saved-job', [AccountController::class, 'removeSavedJobs'])->name('account.removeSavedJobs');
    Route::post('/account/update-password', [AccountController::class, 'updatePassword'])->name('account.updatePassword');
});

// admin routes
Route::group(['prefix' => 'admin','middleware' => 'CheckAdmin'], function(){
    Route::get('/account/profile', [AccountController::class, 'profile'])->name('account.profile');
    Route::get('/account/logout', [AccountController::class, 'logout'])->name('account.logout');
    Route::put('/account/update-profile', [AccountController::class, 'updateProfile'])->name('account.updateProfile');
    Route::post('/update-profile-pic',[AccountController::class,'updateProfilePic'])->name('account.updateProfilePic');
    Route::get('/account/create-job', [AccountController::class, 'createJob'])->name('account.createJob');
    Route::post('/account/save-job', [AccountController::class, 'saveJob'])->name('account.saveJob');
    Route::get('/account/my-jobs', [AccountController::class, 'myJobs'])->name('account.myJobs');
    Route::get('/account/my-jobs/edit/{jobId}', [AccountController::class, 'editJob'])->name('account.editJob');
    Route::post('/account/update-job/{jobId}', [AccountController::class, 'updateJob'])->name('account.updateJob');
    Route::post('/account/delete-job', [AccountController::class, 'deleteJob'])->name('account.deleteJob');
    Route::get('/account/my-job-applications', [AccountController::class, 'myJobApplications'])->name('account.myJobApplications');
    Route::post('/account/remove-job-application', [AccountController::class, 'removeJobs'])->name('account.removeJobs');
    Route::get('/account/saved-jobs', [AccountController::class, 'savedJobs'])->name('account.savedJobs');
    Route::post('/account/remove-saved-job', [AccountController::class, 'removeSavedJobs'])->name('account.removeSavedJobs');
    Route::post('/account/update-password', [AccountController::class, 'updatePassword'])->name('account.updatePassword');
    Route::get('/Admin/dashboard', [DashboardController::class, 'index'])->name('admin.dashboard');
    Route::get('/Admin/users', [UserController::class, 'index'])->name('admin.users');
    Route::get('/Admin/users/edit/{id}', [UserController::class, 'edit'])->name('admin.users.edit');
    Route::put('/Admin/users{id}', [UserController::class, 'update'])->name('admin.users.update');
    Route::delete('/Admin/users', [UserController::class, 'destroy'])->name('admin.users.destroy');
    Route::get('/Admin/jobs', [JobController::class, 'index'])->name('admin.jobs');
    Route::get('/Admin/jobs/edit/{id}', [JobController::class, 'edit'])->name('admin.jobs.edit');
    Route::put('/Admin/jobs/update/{id}', [JobController::class, 'update'])->name('admin.jobs.update');
    Route::delete('/Admin/jobs', [JobController::class, 'destroy'])->name('admin.jobs.destroy');
    Route::get('/Admin/job-applications', [JobApplicationController::class, 'index'])->name('admin.JobApplication');
    Route::delete('/Admin/jobs-application', [JobApplicationController::class, 'destroy'])->name('admin.JobApplication.destroy');
});

// Middleware
// Route::middleware(['role:user'])->group(function() {
//     Route::get('/account/profile', [AccountController::class, 'profile'])->name('account.profile');
// });