<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

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

// Route::group(['prefix' => 'v1'], function () {

//     Route::post('login', 'API\AuthController@authenticate');
//     Route::group(['middleware' => 'jwt-auth'], function () {
//         Route::get('users', 'API\AuthController@users');
//         Route::get('categories', 'API\V1Controller@categories');
//         Route::post('products', 'API\V1Controller@products');
//         Route::post('categories/save', 'API\V1Controller@save_category');
//         Route::post('orders/add', 'API\V1Controller@orders_add');
//         Route::post('orders/min', 'API\V1Controller@orders_min');
//         Route::post('orders/summary', 'API\V1Controller@summaryTransaction');
//         Route::post('orders/edit', 'API\V1Controller@orders_edit');
//         Route::post('orders', 'API\V1Controller@orders');
//         Route::post('saldo', 'API\V1Controller@saldo');
//         Route::post('topup_saldo', 'API\V1Controller@topup_saldo');
//         Route::post('products/save', 'API\V1Controller@save_product');
//         Route::post('categories/selection', 'API\V1Controller@category_selections');
//         Route::post('categories/selection/last', 'API\V1Controller@last_category_selections');
//     });
// });

