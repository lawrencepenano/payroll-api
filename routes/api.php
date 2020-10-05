<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\UserController;
use App\Http\Controllers\API\BlogController;
use App\Http\Controllers\API\CommentController;

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

Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});

Route::post('/login',[UserController::class, 'login']);
Route::post('/register',[UserController::class, 'register']);
Route::resource('/blogs', BlogController::class)->only([
    'index', 'store', 'show', 'update', 'destroy'
]);
Route::resource('/comments', CommentController::class)->only([
    'store', 'update', 'destroy'
]);


