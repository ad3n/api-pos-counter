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

Route::group(['prefix' => 'v1'], function () {

    Route::post('login', 'Auth\AuthController@authenticate');

    Route::group(['middleware' => 'jwt-admin'], function () {

        // User & Auth
        Route::get('logout', 'Auth\AuthController@logout');

        // Super Roles
        Route::get('supers', 'SuperController@getAll');
        Route::post('super/new', 'SuperController@createUser');
        Route::post('super/edit/{id}', 'SuperController@updateUser')->where("id", "[0-9]+");
        Route::post('super/flag/{id}', 'SuperController@flagUser')->where("id", "[0-9]+");
        Route::get('roles', 'SuperController@getRoles');

        // Category
        Route::get('category', 'CategoryController@getAll');
        Route::post('category/new', 'CategoryController@create');
        Route::post('category/edit/{id}', 'CategoryController@update')->where("id", "[0-9]+");
        Route::delete('category/delete/{id}', 'CategoryController@deleteCat')->where("id", "[0-9]+");

        // Merchants
        Route::get('merchant/list', 'MerchantController@getAll');
        Route::get('merchant/dashboard', 'MerchantController@getDashboard');
        Route::get('merchant/detail/{id}', 'MerchantController@getDetail')->where("id", "[0-9]+");
        Route::post('merchant/activate/user/{id}', 'MerchantController@updateUserActive')->where("id", "[0-9]+");
        Route::delete('merchant/remove/user/{id}', 'MerchantController@deleteUser')->where("id", "[0-9]+");
        Route::post('merchant/edit/{id}', 'MerchantController@updateMerchant')->where("id", "[0-9]+");
        Route::post('merchant/types/new', 'MerchantController@createType');
        Route::post('merchant/types/edit/{id}', 'MerchantController@updateType')->where("id", "[a-z_-]+");
        Route::delete('merchant/types/delete/{id}', 'MerchantController@deleteType')->where("id", "[a-z_-]+");
        Route::get('merchant/types/{type}', 'MerchantController@getMerchantTypes')->where("type", "[a-z]+");

        // Saldo
        Route::get('saldo/topup', 'SaldoController@getProperties');
        Route::post('saldo/topup/{id}', 'SaldoController@topupSaldo')->where("id", "[A-Za-z0-9_-]+");
        Route::get('saldo/{merchant_id}', 'SaldoController@getMerchantSaldo')->where("merchant_id", "[0-9]+");
    });
});

