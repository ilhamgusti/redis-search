<?php

use App\Http\Controllers\RedisController;
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
Route::get('/data', [RedisController::class, 'originalData']);
