<?php

/*
|--------------------------------------------------------------------------
| Admin Routes
|--------------------------------------------------------------------------
|
| Here is where you can register admin routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/
Route::post('/update', 'UpdateController@step0')->name('update');
Route::get('/update/step1', 'UpdateController@step1')->name('update.step1');
Route::get('/update/step2', 'UpdateController@step2')->name('update.step2');

Route::get('/refresh-csrf', function () {
	return csrf_token();
});
Route::post('/aiz-uploader', 'AizUploadController@show_uploader');
Route::post('/aiz-uploader/upload', 'AizUploadController@upload');
Route::get('/aiz-uploader/get_uploaded_files', 'AizUploadController@get_uploaded_files');
Route::delete('/aiz-uploader/destroy/{id}', 'AizUploadController@destroy');
Route::post('/aiz-uploader/get_file_by_ids', 'AizUploadController@get_preview_files');
Route::get('/aiz-uploader/download/{id}', 'AizUploadController@attachment_download')->name('download_attachment');


Route::get('/demo/cron_1', 'DemoController@cron_1');
Route::get('/demo/cron_2', 'DemoController@cron_2');
Route::get('/insert_trasnalation_keys', 'DemoController@insert_trasnalation_keys');
Route::get('/customer-products/admin', 'SettingController@initSetting');

Auth::routes(['register' => false]);
Route::get('/logout', '\App\Http\Controllers\Auth\LoginController@logout')->name('logout');



Route::group(['prefix' => 'admin', 'middleware' => ['auth', 'admin']], function () {

	Route::get('/', 'AdminController@admin_dashboard')->name('admin.dashboard');

	Route::post('/language', 'LanguageController@changeLanguage')->name('language.change');

	Route::resource('categories', 'CategoryController');
	Route::get('/categories/edit/{id}', 'CategoryController@edit')->name('categories.edit');
	Route::get('/categories/destroy/{id}', 'CategoryController@destroy')->name('categories.destroy');
	Route::post('/categories/featured', 'CategoryController@updateFeatured')->name('categories.featured');

	Route::resource('brands', 'BrandController');
	Route::get('/brands/edit/{id}', 'BrandController@edit')->name('brands.edit');
	Route::get('/brands/destroy/{id}', 'BrandController@destroy')->name('brands.destroy');

	Route::resource('attributes', 'AttributeController')->except(['destroy']);
	Route::get('/attributes/edit/{id}', 'AttributeController@edit')->name('attributes.edit');

	Route::resource('attribute_values', 'AttributeValueController')->except(['destroy']);;
	Route::get('/attribute_values/edit/{id}', 'AttributeValueController@edit')->name('attribute_values.edit');




	// Product
	Route::resource('/product', 'ProductController');
	Route::group(['prefix' => 'product'], function () {
		Route::post('/new-attribte', 'ProductController@new_attribute')->name('product.new_attribute');
		Route::post('/get-attribte-value', 'ProductController@get_attribute_values')->name('product.get_attribute_values');
		Route::post('/new-option', 'ProductController@new_option')->name('product.new_option');
		Route::post('/get-option-choices', 'ProductController@get_option_choices')->name('product.get_option_choices');

		Route::post('/sku-combination', 'ProductController@sku_combination')->name('product.sku_combination');


		Route::get('/{id}/edit', 'ProductController@edit')->name('product.edit');
		Route::get('/duplicate/{id}', 'ProductController@duplicate')->name('product.duplicate');
		Route::post('/update/{id}', 'ProductController@update')->name('product.update');
		Route::post('/published', 'ProductController@updatePublished')->name('product.published');
		Route::get('/destroy/{id}', 'ProductController@destroy')->name('product.destroy');

		Route::post('/get_products_by_subcategory', 'ProductController@get_products_by_subcategory')->name('product.get_products_by_subcategory');
	});


	//Product Bulk Upload
	// Route::get('/product-bulk-upload/index', 'ProductBulkUploadController@index')->name('product_bulk_upload.index');
	// Route::post('/bulk-product-upload', 'ProductBulkUploadController@bulk_upload')->name('bulk_product_upload');
	// Route::get('/product-csv-download/{type}', 'ProductBulkUploadController@import_product')->name('product_csv.download');

	// Route::get('/product-bulk-export', 'ProductBulkUploadController@export')->name('product_bulk_export.index');

	Route::group(['prefix' => 'bulk-upload/download'], function () {
		Route::get('/category', 'ProductBulkUploadController@pdf_download_category')->name('pdf.download_category');
		Route::get('/brand', 'ProductBulkUploadController@pdf_download_brand')->name('pdf.download_brand');
	});

	Route::resource('customers', 'CustomerController');
	Route::get('customers_ban/{customer}', 'CustomerController@ban')->name('customers.ban');
	Route::get('/customers/login/{id}', 'CustomerController@login')->name('customers.login');
	Route::get('/customers/destroy/{id}', 'CustomerController@destroy')->name('customers.destroy');

	Route::get('/newsletter', 'NewsletterController@index')->name('newsletters.index');
	Route::post('/newsletter/send', 'NewsletterController@send')->name('newsletters.send');
	Route::post('/newsletter/test/smtp', 'NewsletterController@testEmail')->name('test.smtp');

	Route::resource('profile', 'ProfileController');

	Route::post('/settings/update', 'SettingController@update')->name('settings.update');
	Route::post('/settings/update/activation', 'SettingController@updateActivationSettings')->name('settings.update.activation');
	Route::get('/general-setting', 'SettingController@general_setting')->name('general_setting.index');
	Route::get('/payment-method', 'SettingController@payment_method')->name('payment_method.index');
	Route::get('/file_system', 'SettingController@file_system')->name('file_system.index');
	Route::get('/social-login', 'SettingController@social_login')->name('social_login.index');
	Route::get('/smtp-settings', 'SettingController@smtp_settings')->name('smtp_settings.index');
	Route::post('/env_key_update', 'SettingController@env_key_update')->name('env_key_update.update');
	Route::post('/payment_method_update', 'SettingController@payment_method_update')->name('payment_method.update');

	Route::get('/third-party-settings', 'SettingController@third_party_settings')->name('third_party_settings.index');
	Route::post('/google_analytics', 'SettingController@google_analytics_update')->name('google_analytics.update');
	Route::post('/google_recaptcha', 'SettingController@google_recaptcha_update')->name('google_recaptcha.update');
	Route::post('/facebook_chat', 'SettingController@facebook_chat_update')->name('facebook_chat.update');
	Route::post('/facebook_pixel', 'SettingController@facebook_pixel_update')->name('facebook_pixel.update');

	// Currency
	Route::get('/currency', 'CurrencyController@index')->name('currency.index');
	Route::post('/currency/update', 'CurrencyController@updateCurrency')->name('currency.update');
	Route::post('/your-currency/update', 'CurrencyController@updateYourCurrency')->name('your_currency.update');
	Route::get('/currency/create', 'CurrencyController@create')->name('currency.create');
	Route::post('/currency/store', 'CurrencyController@store')->name('currency.store');
	Route::post('/currency/currency_edit', 'CurrencyController@edit')->name('currency.edit');
	Route::post('/currency/update_status', 'CurrencyController@update_status')->name('currency.update_status');

	// Language
	Route::resource('/languages', 'LanguageController');
	Route::post('/languages/update_rtl_status', 'LanguageController@update_rtl_status')->name('languages.update_rtl_status');
	Route::post('/languages/update_language_status', 'LanguageController@update_language_status')->name('languages.update_language_status');
	Route::get('/languages/destroy/{id}', 'LanguageController@destroy')->name('languages.destroy');
	Route::post('/languages/key_value_store', 'LanguageController@key_value_store')->name('languages.key_value_store');

	Route::get('/frontend_settings/home', 'HomeController@home_settings')->name('home_settings.index');
	Route::post('/frontend_settings/home/top_10', 'HomeController@top_10_settings')->name('top_10_settings.store');

	// website setting
	Route::group(['prefix' => 'website', 'middleware' => ['permission:website_setup']], function () {

		Route::view('/header', 'backend.website_settings.header')->name('website.header');
		Route::view('/footer', 'backend.website_settings.footer')->name('website.footer');
		Route::view('/banners', 'backend.website_settings.banners')->name('website.banners');
		Route::view('/pages', 'backend.website_settings.pages.index')->name('website.pages');
		Route::view('/appearance', 'backend.website_settings.appearance')->name('website.appearance');
		Route::resource('custom-pages', 'PageController');
		Route::get('/custom-pages/edit/{id}', 'PageController@edit')->name('custom-pages.edit');
		Route::get('/custom-pages/destroy/{id}', 'PageController@destroy')->name('custom-pages.destroy');
	});

	Route::resource('roles', 'RoleController');
	Route::get('/roles/edit/{id}', 'RoleController@edit')->name('roles.edit');
	Route::get('/roles/destroy/{id}', 'RoleController@destroy')->name('roles.destroy');

	Route::resource('staffs', 'StaffController');
	Route::get('/staffs/destroy/{id}', 'StaffController@destroy')->name('staffs.destroy');

	// Offers
	Route::resource('offers', 'OfferController');
	Route::get('/offers/destroy/{id}', 'OfferController@destroy')->name('offers.destroy');
	Route::post('/offers/update_status', 'OfferController@update_status')->name('offers.update_status');
	Route::post('/offers/product_discount', 'OfferController@product_discount')->name('offers.product_discount');
	Route::post('/offers/product_discount_edit', 'OfferController@product_discount_edit')->name('offers.product_discount_edit');

	//Subscribers
	Route::get('/subscribers', 'SubscriberController@index')->name('subscribers.index');

	// Orders
	Route::resource('orders', 'OrderController');
	Route::post('/orders/update_delivery_status', 'OrderController@update_delivery_status')->name('orders.update_delivery_status');
	Route::post('/orders/update_payment_status', 'OrderController@update_payment_status')->name('orders.update_payment_status');
	Route::get('/orders/destroy/{id}', 'OrderController@destroy')->name('orders.destroy');
	Route::get('/orders/invoice/{order_id}', 'InvoiceController@invoice_download')->name('orders.invoice.download');
	Route::get('/orders/print/{order_id}', 'InvoiceController@invoice_print')->name('orders.invoice.print');

	//Coupons
	Route::resource('coupon', 'CouponController');
	Route::post('/coupon/get_form', 'CouponController@get_coupon_form')->name('coupon.get_coupon_form');
	Route::post('/coupon/get_form_edit', 'CouponController@get_coupon_form_edit')->name('coupon.get_coupon_form_edit');
	Route::get('/coupon/destroy/{id}', 'CouponController@destroy')->name('coupon.destroy');

	//Reviews
	Route::get('/reviews', 'ReviewController@index')->name('reviews.index');
	Route::post('/reviews/published', 'ReviewController@updatePublished')->name('reviews.published');

	Route::any('/uploaded-files/file-info', 'AizUploadController@file_info')->name('uploaded-files.info');
	Route::resource('/uploaded-files', 'AizUploadController');
	Route::get('/uploaded-files/destroy/{id}', 'AizUploadController@destroy')->name('uploaded-files.destroy');

	// //conversation of customer
	// Route::get('conversations','ConversationController@admin_index')->name('conversations.admin_index');
	// Route::get('conversations/{id}/show','ConversationController@admin_show')->name('conversations.admin_show');

	Route::resource('addons', 'AddonController');
	Route::post('/addons/activation', 'AddonController@activation')->name('addons.activation');

	//Shipping Configuration
	Route::get('/shipping_configuration', 'SettingController@shipping_configuration')->name('shipping_configuration.index');
	Route::post('/shipping_configuration/update', 'SettingController@shipping_configuration_update')->name('shipping_configuration.update');

	Route::resource('countries', 'CountryController');
	Route::post('/countries/status', 'CountryController@updateStatus')->name('countries.status');

	Route::resource('states', 'StateController');
	Route::post('/states/status', 'StateController@updateStatus')->name('states.status');

	Route::resource('cities', 'CityController');
	Route::get('/cities/edit/{id}', 'CityController@edit')->name('cities.edit');
	Route::get('/cities/destroy/{id}', 'CityController@destroy')->name('cities.destroy');
	Route::post('/cities/status', 'CityController@updateStatus')->name('cities.status');

	Route::resource('zones', 'ZoneController');
	Route::get('/zones/destroy/{id}', 'ZoneController@destroy')->name('zones.destroy');


	Route::view('/system/update', 'backend.system.update')->middleware('permission:system_update')->name('system_update');
	Route::view('/system/server-status', 'backend.system.server_status')->middleware('permission:server_status')->name('server_status');

	// tax
	Route::resource('taxes', 'TaxController');
	Route::post('/tax/status_update', 'TaxController@updateStatus')->name('tax.status_update');
	Route::get('/taxes/destroy/{id}', 'TaxController@destroy')->name('taxes.destroy');

	//chats
	Route::resource('chats', 'ChatController');
	Route::post('/refresh/chats', 'ChatController@refresh')->name('chats.refresh');
	Route::post('/chat-reply', 'ChatController@reply')->name('chats.reply');


	Route::post('/update', 'UpdateController@step0')->name('update');
	Route::get('/update/step1', 'UpdateController@step1')->name('update.step1');
	Route::get('/update/step2', 'UpdateController@step2')->name('update.step2');
});
