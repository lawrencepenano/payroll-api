<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\UserController;
use App\Http\Controllers\API\CompanyController;
use App\Http\Controllers\API\GlobalParameterController;

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