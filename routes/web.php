<?php

use App\Http\Controllers\RedisController;
use App\Http\Controllers\PropertiesControlleer;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/', [RedisController::class, 'index']);

Route::get('/search', [RedisController::class, 'search']);
Route::get('/seeding', [RedisController::class, 'seeding']);
Route::get('/insertproperties',[PropertiesControlleer::class,'index']);