<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\Admin\Rol\RolesController;
use App\Http\Controllers\Admin\Staff\StaffsController;
use App\Http\Controllers\Admin\Doctor\SpecialityController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::group([

    // 'middleware' => 'auth:api',
    'prefix' => 'auth',
    // 'middleware'=> ['role:admin','permission:publish articles'],

], function ($router) {
    Route::post('/register', [AuthController::class, 'register'])->name('register');
    Route::post('/login', [AuthController::class, 'login'])->name('login');
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
    Route::post('/refresh', [AuthController::class, 'refresh'])->name('refresh');
    Route::post('/me', [AuthController::class, 'me'])->name('me');
    Route::post('/list', [AuthController::class, 'list']);
    Route::post('/reg', [AuthController::class, 'reg']);
});

Route::group([

    'middleware' => 'auth:api',
    //'prefix' => 'auth',
    // 'middleware'=> ['role:admin','permission:publish articles'],

], function ($router) {
    Route::resource('roles', RolesController::class);
    Route::get('staffs/config', [StaffsController::class,"config"]);
    Route::post('staffs/{id}', [StaffsController::class,"update"]);
    Route::resource('staffs', StaffsController::class);

    Route::resource("specialities",SpecialityController::class);
});

