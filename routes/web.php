<?php

use App\Http\Controllers\ImageController;
use App\Http\Controllers\RedisController;
use App\Http\Controllers\PropertiesControlleer;
use App\Http\Controllers\ShowallController;
use App\Library\PropertiesIndex;
use Illuminate\Support\Facades\Redis;
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
Route::get('/searchtest', [PropertiesControlleer::class, 'Searchtest']);
Route::get('/searchtest/{id?}', [PropertiesControlleer::class, 'Searchbyid']);
Route::get('/seeding', [RedisController::class, 'seeding']);
Route::get('/wilayah', [RedisController::class, 'wilayah']);
Route::get('/searchdata', [PropertiesControlleer::class, 'Searchdeveloper']);
Route::get('/insertproperties',[PropertiesControlleer::class,'index']);

Route::get('/showalldata',[ShowallController::class,'index']);
Route::get('/showalldatas',[ShowallController::class,'loadData']);
Route::get('/filter-products', [ShowallController::class, 'filterProducts'])->name('filter.products');


Route::get('/insertcategories', function () {
    // Membuat 30 data kategori
    $categories = [];

    for ($i = 1; $i <= 30; $i++) {
        $category = [
            'id' => $i,
            'namecategori' => 'Kategori ' . $i,
        ];

        $categories[] = $category;
    }

    Redis::set('categories', json_encode($categories));

    return 'Data categories have been saved to Redis.';
});

Route::get('/insertproduct', function () {
    for ($i = 1; $i <= 15; $i++) {
        $product = [
            'nama' => 'Produk ' . $i,
            'harga' => $i * 10,
            'idkategori' => $i % 3 + 1,
        ];

        Redis::hmset('product:'. $i, $product);
    }

    return 'Data products have been saved to Redis.';
});


