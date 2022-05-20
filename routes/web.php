<?php

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/


Route::get('/offline', 'HomeController@index')->name('offline');

Route::group(['prefix' => 'payment'], function(){
    // stripe
    Route::any('/stripe/pay', 'Api\StripePaymentController@stripe');
    Route::any('/stripe/create-session', 'Api\StripePaymentController@create_checkout_session')->name('stripe.get_token');
    Route::get('/stripe/success', 'Api\StripePaymentController@success')->name('stripe.success');
    Route::get('/stripe/cancel', 'Api\StripePaymentController@cancel')->name('stripe.cancel');

    // paypal
    Route::any('/paypal/pay', 'Api\PaypalPaymentController@paypal');
    Route::get('/paypal/success', 'Api\PaypalPaymentController@success')->name('paypal.success');
    Route::get('/paypal/cancel', 'Api\PaypalPaymentController@cancel')->name('paypal.cancel');

    //sslcommerz
    Route::any('/sslcommerz/pay', 'Api\SSLCommerzPaymentController@index')->name('sslcommerz.pay');
    Route::any('/sslcommerz/success', 'Api\SSLCommerzPaymentController@success')->name('sslcommerz.success');
    Route::any('/sslcommerz/fail', 'Api\SSLCommerzPaymentController@fail')->name('sslcommerz.fail');
    Route::any('/sslcommerz/cancel', 'Api\SSLCommerzPaymentController@cancel')->name('sslcommerz.cancel');

    //paystack
    Route::any('/paystack/pay', 'Api\PaystackPaymentController@index')->name('paystack.pay');
    Route::any('/paystack/callback', 'Api\PaystackPaymentController@return')->name('paystack.return');

    //paytm
    Route::any('/paytm/pay', 'Api\PaytmPaymentController@index');
    Route::any('/paytm/callback', 'Api\PaytmPaymentController@callback')->name('paytm.callback');

    //flutterwave
    Route::any('/flutterwave/pay', 'Api\FlutterwavePaymentController@pay')->name('flutterwave.pay');
    Route::any('/flutterwave/callback', 'Api\FlutterwavePaymentController@callback')->name('flutterwave.callback');
});

Route::any('/social-login/redirect/{provider}', 'Auth\LoginController@redirectToProvider')->name('social.login');
Route::get('/social-login/{provider}/callback', 'Auth\LoginController@handleProviderCallback')->name('social.callback');


Route::get('/product/{slug}', 'HomeController@index')->name('product');
Route::get('/category/{slug}', 'HomeController@index')->name('products.category');

Route::get('/', 'HomeController@index')->name('home');
Route::get('{slug}', 'HomeController@index')->where('slug','.*');






// Route::get('/demo/cron_1', 'DemoController@cron_1');
// Route::get('/demo/cron_2', 'DemoController@cron_2');


