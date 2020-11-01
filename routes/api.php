<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\UserController;
use App\Http\Controllers\API\CompanyController;
use App\Http\Controllers\API\GlobalParameterController;
use App\Http\Controllers\API\CostCenterController;
use App\Http\Controllers\API\DepartmentController;
use App\Http\Controllers\API\PayGroupController;
use App\Http\Controllers\API\TotalWorkDaysPerYearController;
use App\Http\Controllers\API\TotalWorkMonthsPerYearController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

/* Load User base on token */
Route::middleware('auth:sanctum')->get('/auth', [UserController::class, 'auth']);
Route::post('/register',[UserController::class, 'register']);
Route::post('/login',[UserController::class, 'login']);


/* Global Parameter Routes */
Route::middleware(['auth:sanctum'])->group(function () {
    /* Module */
    Route::get('/modules',[GlobalParameterController::class, 'getModules']);

     /* Role */
     Route::get('/roles',[GlobalParameterController::class, 'getRoles']);
});

/*  User  */
Route::middleware(['auth:sanctum'])->group(function () {
    /* User Routes */
    Route::resource('/users', UserController::class)->only([
        'index', 'show', 'update', 'destroy'
    ]);
     /* Reset Password*/
     Route::get('/reset_password/{id}',[UserController::class, 'reset_password']);
});

/* Company */
Route::middleware(['auth:sanctum'])->group(function () {
    /* Company Routes */
    Route::resource('/company', CompanyController::class)->only([
        'index', 'show', 'destroy'
    ]);

    /* To cater the Form Data */
    Route::post('/company/{id}',[CompanyController::class, 'update']);
});


/* Cost Centers */
Route::middleware(['auth:sanctum'])->group(function () {
    /* Cost Center Routes */
    Route::resource('/cost_center', CostCenterController::class)->only([
        'index', 'store', 'show', 'update' , 'destroy'
    ]);
});

/* Departments */
Route::middleware(['auth:sanctum'])->group(function () {
    /* Department Routes */
    Route::resource('/department', DepartmentController::class)->only([
        'index', 'store', 'show', 'update' , 'destroy'
    ]);
});

/* Pay Groups */
Route::middleware(['auth:sanctum'])->group(function () {
    /* Pay Group Routes */
    Route::resource('/pay_group', PayGroupController::class)->only([
        'index', 'store', 'show', 'update' , 'destroy'
    ]);
});

/* Total Work Days Per Year */
Route::middleware(['auth:sanctum'])->group(function () {
    /* Total Work Months Per Yea Routes */
    Route::resource('/total_work_days_per_year', TotalWorkDaysPerYearController::class)->only([
        'index', 'store', 'show', 'update' , 'destroy'
    ]);
});

/* Total Work Months Per Year */
Route::middleware(['auth:sanctum'])->group(function () {
    /* Total Work Months Per YeaRoutes */
    Route::resource('/total_work_months_per_year', TotalWorkMonthsPerYearController::class)->only([
        'index', 'store', 'show', 'update' , 'destroy'
    ]);
});