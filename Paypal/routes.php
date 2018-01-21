<?php

/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| Here is where you can register all of the routes for an application.
| It's a breeze. Simply tell Laravel the URIs it should respond to
| and give it the controller to call when that URI is requested.
|
*/

Route::get('/', function () {
    return view('welcome');
});


 Route::get('/paypal','PaypalController@paypal');  //initate payment
 Route::get('/paypal_success_product','PaypalController@paypal_success_product'); //payment success if no subscription products are in the cart
 Route::get('/paypal_success_subscription','PaypalController@paypal_success_subscription'); //payment success if subscription is in the cart
 Route::get('/paypal_cancel','PaypalController@paypal_cancel'); //users cancel the payment
 Route::get('/paypal_refund/{resource_id}','PaypalController@paypal_refund'); //refund
 Route::get('/paypal_subscription_cancel/{transaction_id}','PaypalController@paypal_subscription_cancel'); //cancel subscription
 Route::get('/paypal_update_recurring_details/{start_date}/{agreement_id}','PaypalController@paypal_update_recurring_details');
 Route::get('/get_transaction_detail/{transaction_id}','PaypalController@get_transaction_detail');

