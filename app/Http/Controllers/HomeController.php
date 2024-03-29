<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Country;
use App\Models\Currency;
use Illuminate\Http\Request;
use App\Models\Language;
use App\Models\Page;
use App\Models\Product;
use Cache;
use Route;

class HomeController extends Controller
{
    public function index(Request $request, $slug = null)
    {
        $meta = [
            'meta_title' => get_setting('meta_title'),
            'meta_description' => get_setting('meta_description'),
            'meta_image' => api_asset(get_setting('meta_image')),
            'meta_keywords' => get_setting('meta_keywords'),
        ];
        $meta['meta_title'] = $meta['meta_title'] ? $meta['meta_title'] : config('app.name');

        if (Route::currentRouteName() == 'product') {
            $product = Product::where('slug', $slug)->first();
            if ($product) {
                $meta['meta_title'] = $product->meta_title ? $product->meta_title : $meta['meta_title'];
                $meta['meta_description'] = $product->meta_description ? $product->meta_description : $meta['meta_description'];
                $meta['meta_image'] = $product->meta_image ? api_asset($product->meta_image) : $meta['meta_image'];
            }
        } elseif (Route::currentRouteName() == 'products.category') {
            $category = Category::where('slug', $slug)->first();
            if ($category) {
                $meta['meta_title'] = $category->meta_title ? $category->meta_title : $meta['meta_title'];
                $meta['meta_description'] = $category->meta_description ? $category->meta_description : $meta['meta_description'];
                $meta['meta_image'] = $category->meta_image ? api_asset($category->meta_image) : $meta['meta_image'];
            }
        } elseif ($slug) {
            $page = Page::where('slug', $slug)->first();
            if ($page) {
                $meta['meta_title'] = $page->meta_title ? $page->meta_title : $meta['meta_title'];
                $meta['meta_description'] = $page->meta_description ? $page->meta_description : $meta['meta_description'];
                $meta['meta_image'] = $page->meta_image ? api_asset($page->meta_image) : $meta['meta_image'];
                $meta['meta_keywords'] = $page->keywords ? $page->keywords : $meta['meta_keywords'];
            }
        }

        $settings = [
            'appName' => config('app.name'),
            'appLogo' => get_setting('header_logo') ? api_asset(get_setting('header_logo')) : static_asset('assets/img/logo.svg'),
            'appUrl' => getBaseURL(),
            'demoMode' => env('DEMO_MODE') == "On" ? true : false,
            'cacheVersion' => get_setting('force_cache_clear_version'),
            'appLanguage' => app()->getLocale(),
            'allLanguages' => Language::all('name', 'code', 'flag', 'rtl'),
            'availableCountries' => Country::where('status', 1)->pluck('code')->toArray(),
            'paymentMethods' => [
                [
                    'status' => get_setting('paypal_payment'),
                    'code' => 'paypal',
                    'name' => 'Paypal',
                    'img' => static_asset("assets/img/cards/paypal.png")
                ],
                [
                    'status' => get_setting('stripe_payment'),
                    'code' => 'stripe',
                    'name' => 'Stripe',
                    'img' => static_asset("assets/img/cards/stripe.png")
                ],
                [
                    'status' => get_setting('sslcommerz_payment'),
                    'code' => 'sslcommerz',
                    'name' => 'SSLCommerz',
                    'img' => static_asset("assets/img/cards/sslcommerz.png")
                ],
                [
                    'status' => get_setting('paystack_payment'),
                    'code' => 'paystack',
                    'name' => 'Paystack',
                    'img' => static_asset("assets/img/cards/paystack.png")
                ],
                [
                    'status' => get_setting('flutterwave_payment'),
                    'code' => 'flutterwave',
                    'name' => 'Flutterwave',
                    'img' => static_asset("assets/img/cards/flutterwave.png")
                ],
                [
                    'status' => get_setting('paytm_payment'),
                    'code' => 'paytm',
                    'name' => 'Paytm',
                    'img' => static_asset("assets/img/cards/paytm.png")
                ],
                [
                    'status' => get_setting('cash_payment'),
                    'code' => 'cash_on_delivery',
                    'name' => translate('Cash on Delivery'),
                    'img' => static_asset("assets/img/cards/cod.png")
                ],
            ],
            'general_settings' => [
                'email_verification' => get_setting('email_verification'),
                'wallet_system' => get_setting('wallet_system'),
                'chat' => [
                    'customer_chat_logo' => api_asset(get_setting('customer_chat_logo')),
                    'customer_chat_name' => get_setting('customer_chat_name'),
                ],
                'social_login' => [
                    'google' => get_setting('google_login'),
                    'facebook' => get_setting('facebook_login'),
                    'twitter' => get_setting('twitter_login'),
                ],
                'currency' => [
                    'code' => Cache::remember('system_default_currency_symbol', 86400, function () {
                        return Currency::find(get_setting('system_default_currency'))->symbol;
                    }),
                    'decimal_separator' => get_setting('decimal_separator'),
                    'symbol_format' => get_setting('symbol_format'),
                    'no_of_decimals' => get_setting('no_of_decimals'),
                ]
            ],
            'banners' => [
                "login_page" => [
                    "img" => api_asset(get_setting('login_page_banner')),
                    "link" => get_setting('login_page_banner_link')
                ],
                "registration_page" => [
                    "img" => api_asset(get_setting('registration_page_banner')),
                    "link" => get_setting('registration_page_banner_link')
                ],
                "forgot_page" => [
                    "img" => api_asset(get_setting('forgot_page_banner')),
                    "link" => get_setting('forgot_page_banner_link')
                ],
                "listing_page" => [
                    "img" => api_asset(get_setting('listing_page_banner')),
                    "link" => get_setting('listing_page_banner_link')
                ],
                "product_page" => [
                    "img" => api_asset(get_setting('product_page_banner')),
                    "link" => get_setting('product_page_banner_link')
                ],
                "checkout_page" => [
                    "img" => api_asset(get_setting('checkout_page_banner')),
                    "link" => get_setting('checkout_page_banner_link')
                ],
                "dashboard_page_top" => [
                    "img" => api_asset(get_setting('dashboard_page_top_banner')),
                    "link" => get_setting('dashboard_page_top_banner_link')
                ],
                "dashboard_page_bottom" => [
                    "img" => api_asset(get_setting('dashboard_page_bottom_banner')),
                    "link" => get_setting('dashboard_page_bottom_banner_link')
                ],
            ],
        ];
        return view('frontend.app', compact('settings', 'meta'));
    }
}
