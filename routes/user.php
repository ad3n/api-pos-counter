<?php

use Illuminate\Http\Request;
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

Route::group(['prefix' => 'v2'], function () {

    Route::post('login', 'Auth\AuthController@authenticate');
    Route::get('register', 'Auth\RegisterController@getRegister');
    Route::post('register', 'Auth\RegisterController@postRegister');
    Route::get('properties/location', 'User\UserController@getProperties');

    Route::group(['middleware' => 'jwt-auth:api'], function () {

        // User & Auth
        Route::get('user/logout', 'Auth\AuthController@logout');
        Route::get('user/refresh', 'Auth\AuthController@refresh');
        Route::get('user', 'User\UserController@userGet');
        Route::put('user/update', 'User\UserController@updateUser');
        Route::put('user/merchant/update', 'User\UserController@updateMerchant');
        Route::put('user/set_date', 'User\UserController@putWorkDate');

        // Transaction
        Route::get('transaction/list', 'User\TransactionController@userTransactionByMerchant');
        Route::get('transaction/list/{type}', 'User\TransactionController@transactionByType')->where("type", "[a-z]+");

        // Create transaction
        Route::post(
            'transaction/new/{type}',
            'User\TransactionController@createTransaction'
        )->where("type", "[a-z]+");

        // Get transaction detail
        Route::get(
            'transaction/detail/{id}',
            'User\TransactionController@getTransactionDetail'
        )->where("id", "[A-Za-z0-9_\-]+");

        // Update Transaction Detail
        Route::post(
            'transaction/detail/{id}/edit',
            'User\TransactionController@editTransactionDetail'
        )->where("id", "[A-Za-z0-9_\-]+");


        Route::delete(
            'transaction/detail/{id}/delete',
            'User\TransactionController@removeTransactionItem'
        )->where("id", "[A-Za-z0-9_\-]+");

        // Expense
        Route::post(
            'transaction/expense/{id}/edit',
            'User\TransactionController@editExpense'
        )->where("id", "[A-Za-z0-9_\-]+");

        // Update Transaction
        Route::post(
            'transaction/{id}/edit',
            'User\TransactionController@editTransaction'
        )->where("id", "[A-Za-z0-9_\-]+");


        // Delete transaction
        Route::delete(
            'transaction/{id}/delete',
            'User\TransactionController@removeTransaction'
        )->where("id", "[A-Za-z0-9_\-]+");

        // Set payment status
        Route::post(
            'transaction/payment/{id}/set',
            'User\TransactionController@paymentStatus'
        )->where("id", "[A-Za-z0-9_\-]+");

        // Product
        Route::post('product/list', 'User\ProductController@getAll');
        Route::get('product/search', 'User\ProductController@getSearchBarcode');
        Route::post('product/new', 'User\ProductController@postNewProduct');
        Route::post(
            'product/edit/{id}',
            'User\ProductController@editProduct'
        )->where("id", "[0-9]+");
        Route::delete(
            'product/trash/{id}',
            'User\ProductController@trashProduct'
        )->where("id", "[0-9]+");
        Route::get('product/categorized', 'User\ProductController@getCategorized');
        Route::get('categories', 'User\ProductController@getMasterCategory');
        Route::get('categories/merchant', 'User\ProductController@getMerchantCategory');

        // Cart
        Route::get('cart/new/{type}', 'User\CartController@create')->where("type", "[a-z]+");
        Route::get('cart/{order_no}', 'User\CartController@getCart')->where("order_no", "[A-Za-z0-9_\-]+");
        //Route::post('cart/{order_no}', 'User\CartController@addCart')->where("id", "[A-Za-z0-9_\-]+");
        Route::delete('cart/flush', 'User\CartController@flushCart');
        //Route::delete('cart/{order_no}', 'User\CartController@deleteCart')->where("id", "[A-Za-z0-9_\-]+");

        // Saldo
        Route::get('saldo/list', 'User\UserController@getSaldo');
    });
});

Route::group(['prefix' => 'ddpulsa'], function () {
    Route::get('/callback', function(){
        if(request()->server('REMOTE_ADDR') === '172.104.161.223'){
            if( request()->has('content') ) {

            }
        }
    });

    Route::get('/sync/price', 'DD\SyncProductController@getPriceFromPortalPulsa');
});

// Route::middleware('auth:api')->get('/user', function (Request $request) {
//     return $request->user();
// });
