<?php


Route::group(['prefix' => 'v1', 'as' => 'api.'], function () {

    Route::group(['prefix' => 'auth'], function () {

        Route::post('login', 'Api\AuthController@login');
        Route::post('signup', 'Api\AuthController@signup');
        Route::post('verify', 'Api\AuthController@verify');
        Route::post('resend-code', 'Api\AuthController@resend_code');

        Route::post('password/create', 'Api\PasswordResetController@create');
        Route::post('password/reset', 'Api\PasswordResetController@reset');

        Route::group(['middleware' => 'auth:api'], function () {
            Route::get('logout', 'Api\AuthController@logout');
            Route::get('user', 'Api\AuthController@user');
        });
    });

    Route::get('locale/{language_code}', 'Api\TranslationController@index');
    Route::get('setting/home/{section}', 'Api\SettingController@home_setting');
    Route::get('setting/footer', 'Api\SettingController@footer_setting');
    Route::get('setting/header', 'Api\SettingController@header_setting');
    Route::post('subscribe', 'Api\SubscribeController@subscribe');

    Route::get('all-categories', 'Api\CategoryController@index');
    Route::get('all-brands', 'Api\BrandController@index');
    Route::get('all-offers', 'Api\OfferController@index');
    Route::get('offer/{slug}', 'Api\OfferController@show');
    Route::get('page/{slug}', 'Api\PageController@show');


    Route::group(['prefix' => 'product'], function () {
        Route::get('/details/{product_slug}', 'Api\ProductController@show');
        Route::post('get-by-ids', 'Api\ProductController@get_by_ids');
        Route::get('search', 'Api\ProductController@search');
        Route::get('related/{product_id}', 'Api\ProductController@related');
        Route::get('bought-together/{product_id}', 'Api\ProductController@bought_together');
        Route::get('random/{limit}/{product_id?}', 'Api\ProductController@random_products');
        Route::get('latest/{limit}', 'Api\ProductController@latest_products');
        Route::get('reviews/{product_id}', 'Api\ReviewController@index');
    });


    Route::get('all-countries', 'Api\AddressController@get_all_countries');
    Route::get('states/{country_id}', 'Api\AddressController@get_states_by_country_id');
    Route::get('cities/{state_id}', 'Api\AddressController@get_cities_by_state_id');


    Route::post('carts', 'Api\CartController@index');
    Route::post('carts/add', 'Api\CartController@add');
    Route::post('carts/change-quantity', 'Api\CartController@changeQuantity');
    Route::post('carts/destroy', 'Api\CartController@destroy');

    Route::group(['middleware' => 'auth:api'], function () {

        Route::group(['prefix' => 'checkout'], function () {
            Route::get('get-shipping-cost/{address_id}', 'Api\OrderController@get_shipping_cost');
            Route::post('order/store', 'Api\OrderController@store');
            Route::post('coupon/apply', 'Api\CouponController@apply');
        });

        Route::group(['prefix' => 'user'], function () {

            Route::get('dashboard', 'Api\UserController@dashboard');

            Route::get('chats', 'Api\ChatController@index');
            Route::post('chats/send', 'Api\ChatController@send');
            Route::get('chats/new-messages', 'Api\ChatController@new_messages');

            Route::get('info', 'Api\UserController@info');
            Route::post('info/update', 'Api\UserController@updateInfo');

            Route::get('coupons', 'Api\CouponController@index');

            Route::get('orders', 'Api\OrderController@index');
            Route::get('order/{order_code}', 'Api\OrderController@show');
            Route::get('order/cancel/{order_code}', 'Api\OrderController@cancel');
            Route::get('order/invoice-download/{order_code}', 'Api\OrderController@invoice_download');

            Route::get('review/check/{product_id}', 'Api\ReviewController@check_review_status');
            Route::post('review/submit', 'Api\ReviewController@submit_review');

            Route::apiResource('wishlists', 'Api\WishlistController')->except(['update', 'show']);

            Route::get('addresses', 'Api\AddressController@addresses');
            Route::post('address/create', 'Api\AddressController@createShippingAddress');
            Route::post('address/update', 'Api\AddressController@updateShippingAddress');
            Route::get('address/delete/{id}', 'Api\AddressController@deleteShippingAddress');
            Route::get('address/default-shipping/{id}', 'Api\AddressController@defaultShippingAddress');
            Route::get('address/default-billing/{id}', 'Api\AddressController@defaultBillingAddress');

            Route::post('wallet/recharge', 'Api\WalletController@recharge');
            Route::get('wallet/history', 'Api\WalletController@walletRechargeHistory');
        });
    });
});

Route::fallback(function () {
    return response()->json([
        'data' => [],
        'success' => false,
        'status' => 404,
        'message' => 'Invalid Route'
    ]);
});
