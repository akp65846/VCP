<?php

use App\Http\Controllers\api\AuthController;
use App\Http\Controllers\api\ContentCreatorController;
use App\Http\Controllers\api\PlatformController;
use Illuminate\Support\Facades\Route;

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

//Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
//    return $request->user();
//});

//Route::get('user', PlatformController::class);

Route::post('platform', [PlatformController::class, 'store']);
Route::get('platform', [PlatformController::class, 'index']);
Route::get('content-creator', [ContentCreatorController::class, 'index']);
Route::post('/auth/login', [AuthController::class, 'login']);
Route::post('/auth/register', [AuthController::class, 'register']);
