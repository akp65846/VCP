<?php

use App\Http\Controllers\api\AuthController;
use App\Http\Controllers\api\ContentCreatorController;
use App\Http\Controllers\api\MediaController;
use App\Http\Controllers\api\PlatformController;
use App\Http\Controllers\api\PublishmentController;
use App\Http\Controllers\api\VideoController;
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


//Auth
Route::post('/auth/login', [AuthController::class, 'login'])->name('login');
Route::post('/auth/register', [AuthController::class, 'register']);

//Platform
Route::post('platform', [PlatformController::class, 'store']);
Route::get('platform', [PlatformController::class, 'index']);
Route::get('/platform/{id}', [PlatformController::class, 'show']);
Route::patch('/platform/{id}', [PlatformController::class, 'update']);

//ContentCreator
Route::get('content-creator', [ContentCreatorController::class, 'index']);
Route::post('content-creator', [ContentCreatorController::class, 'store']);
Route::get('/content-creator/{id}', [ContentCreatorController::class, 'show']);
Route::patch('/content-creator/{id}', [ContentCreatorController::class, 'update']);

//Video
Route::get('video', [VideoController::class, 'index']);
Route::get('/video/{id}', [VideoController::class, 'show']);
Route::patch('/video/{id}', [VideoController::class, 'update']);
Route::post('/video/approve', [VideoController::class, 'approveVideo']);
Route::post('/video/reject', [VideoController::class, 'rejectVideo']);

//Media
Route::get('media', [MediaController::class, 'index']);
Route::get('/media/{id}', [MediaController::class, 'show']);
Route::patch('/media/{id}', [MediaController::class, 'update']);

//Publishment
Route::post('publishment', [PublishmentController::class, 'publish']);
