<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ApiController;

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

Route::post('login', [ApiController::class, 'authenticate']);
Route::post('register', [ApiController::class, 'register']);
Route::get('refresh', [ApiController::class, 'refresh']);
Route::get('user/{userId}', [ApiController::class, 'userById']);

Route::group(['middleware' => ['jwt.verify']], function() {
    Route::get('check', [ApiController::class, 'check']);
    Route::get('logout', [ApiController::class, 'logout']);
});
