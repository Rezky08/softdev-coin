<?php

use Illuminate\Http\Request;

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

// Route Pattern
Route::pattern('id', '[0-9]+');
Route::pattern('sellerId', '[0-9]+');
Route::pattern('customerId', '[0-9]+');
Route::pattern('cartId', '[0-9]+');
Route::pattern('productId', '[0-9]+');
Route::pattern('shopId', '[0-9]+');

//

// Route coin
Route::post('/coin/register', 'Coin\CoinRegisterController@store');
Route::post('/coin/topup', 'Coin\CoinBalanceController@coinTopUp');
Route::get('/coin/balance', 'Coin\CoinBalanceController@show');
Route::post('/coin/transaction', 'Coin\CoinTransactionController@store');


// Route::get('/test', 'TesterController@checkShop');
// Route::get('/test/supplier', 'TesterController@supplierCheck');
// Route::post('/test/sendbalance', 'CoinTransactionController@store');
