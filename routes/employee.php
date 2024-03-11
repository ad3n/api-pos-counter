<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Carbon\Carbon;
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
    Route::get('carbon', function () {
        $carbonNowTime = Carbon::parse(Carbon::parse("13:53:00", "Asia/Jakarta")->toTimeString());

        echo Carbon::now("Asia/Jakarta")->toTimeString();
        echo '<br />';
        echo $carbonNowTime->diffInMinutes('13:00:00', false);
        echo '<br />';
        echo $carbonNowTime->diffInHours('15:05:00', false);

        dump(request());
    });
    Route::namespace('App\Http\Controllers\API\tenant')->group(function () {
        Route::post('login', 'Auth\AuthController@authenticate');
        Route::group(['middleware' => 'jwt-employee:api'], function () {
            // User & Auth
            Route::get('logout', 'Auth\AuthController@logout');

            // Employee Roles
            Route::get('employee', 'EmployeeController@getAll');
            Route::post('employee/new', 'EmployeeController@createUser');
            Route::post('employee/edit/{id}', 'EmployeeController@updateUser')->where("id", "[0-9]+");
            Route::post('employee/password/{id}', 'EmployeeController@changePassword')->where("id", "[0-9]+");
            Route::get('employee/session', 'EmployeeController@getSession');
            Route::get('employee/state/{state}', 'EmployeeController@updateSession')->where("state", "open|close+");
            Route::post('employee/flag/{id}', 'EmployeeController@flagUser')->where("id", "[0-9]+");
            Route::post('employee/trash/{id}', 'EmployeeController@trashUser')->where("id", "[0-9]+");
            Route::post('employee/active/{id}', 'EmployeeController@activeUser')->where("id", "[0-9]+");
            Route::get('roles', 'EmployeeController@getRoles');

            // Supplier
            Route::get('suppliers', 'SupplierController@getAll');
            Route::get('suppliers/{id}', 'SupplierController@getSupplier');
            Route::post('suppliers/new', 'SupplierController@createSupplier');
            Route::post('suppliers/edit/{id}', 'SupplierController@updateSupplier')->where("id", "[0-9]+");
            Route::post('suppliers/trash/{id}', 'SupplierController@trashSupplier')->where("id", "[0-9]+");

            // Customer
            Route::get('customers', 'CustomerController@getAll');
            Route::get('customers/transactions', 'CustomerController@getOfTransaction');
            Route::get('customers/{id}', 'CustomerController@fetch');
            Route::post('customers/new', 'CustomerController@create');
            Route::post('customers/edit/{id}', 'CustomerController@update')->where("id", "[0-9]+");
            Route::delete('customers/trash/{id}', 'CustomerController@trash')->where("id", "[0-9]+");
        });
    });

    Route::namespace('App\Http\Controllers\API\v2')->group(function () {
        Route::group(['middleware' => 'jwt-employee:api'], function () {
            // Transaction
            Route::get('transaction/list', 'User\TransactionController@userTransactionByMerchant');
            Route::get('transaction/list/{type}', 'User\TransactionController@transactionByType')->where("type", "[a-z]+");

            // Cart
            Route::get('cart/new/{type}', 'User\CartController@create')->where("type", "[a-z]+");
            Route::get('cart/{order_no}', 'User\CartController@getCart')->where("order_no", "[A-Za-z0-9_\-]+");

            // Create transaction
            Route::post(
                'transaction/new/{type}',
                'User\TransactionController@createTransaction'
            )->where("type", "[a-z]+");

             // Add transaction
            Route::post(
                'transaction/add',
                'User\TransactionController@addTransaction'
            );

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

            Route::put(
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
            Route::post('product/list/p', 'User\ProductController@getAllByPaginate');
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

            // Misc
            Route::get('misc/work_date', 'User\TransactionController@getWorkDate');
        });
    });
});
