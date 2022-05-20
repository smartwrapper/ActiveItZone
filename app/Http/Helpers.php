<?php

use App\Models\Currency;
use App\Models\Setting;
use App\Models\Attribute;
use App\Models\AttributeValue;
use App\Models\Translation;



//highlights the selected navigation on admin panel
if (! function_exists('areActiveRoutes')) {
    function areActiveRoutes(Array $routes, $output = "active")
    {
        foreach ($routes as $route) {
            if (Route::currentRouteName() == $route) return $output;
        }

    }
}


//highlights the selected navigation on frontend
if (! function_exists('default_language')) {
    function default_language()
    {
        return env("DEFAULT_LANGUAGE");
    }
}


/**
 * Save JSON File
 * @return Response
*/
if (! function_exists('convert_to_usd')) {
    function convert_to_usd($amount) {
        $business_settings = Setting::where('type', 'system_default_currency')->first();
        if($business_settings!=null){
            $currency = Currency::find($business_settings->value);
            return floatval($amount) / floatval($currency->exchange_rate);
        }
    }
}



//formats currency
// $show_tag like 12M, 13K 
if (! function_exists('format_price')) {
    function format_price($price, $show_tag = false)
    {
        
        if (get_setting('decimal_separator') == 1) {
            $fomated_price = number_format($price, get_setting('no_of_decimals'));
        } else {
            $fomated_price = number_format($price, get_setting('no_of_decimals'), ',', ' ');
        }

        if($show_tag){
            if ($price < 1000000) {
                // Anything less than a million
                $fomated_price = number_format($price, get_setting('no_of_decimals'));
            } else if ($price < 1000000000) {
                // Anything less than a billion
                $fomated_price = number_format($price / 1000000, get_setting('no_of_decimals')) . 'M';
            } else {
                // At least a billion
                $fomated_price = number_format($price / 1000000000, get_setting('no_of_decimals')) . 'B';
            }
    
        }

        if (get_setting('symbol_format') == 1) {
            return currency_symbol() . $fomated_price;
        } else if (get_setting('symbol_format') == 3) {
            return currency_symbol() . ' ' . $fomated_price;
        } else if (get_setting('symbol_format') == 4) {
            return $fomated_price . ' ' . currency_symbol();
        }
        return $fomated_price . currency_symbol();

    }
}


if (! function_exists('currency_symbol')) {
    function currency_symbol()
    {
        return Cache::rememberForever('system_default_currency_symbol', function () {
            return Currency::find(get_setting('system_default_currency'))->first()->symbol;
        });
    }
}

if(! function_exists('renderStarRating')){
    function renderStarRating($rating,$maxRating=5) {
        $fullStar = "<i class = 'las la-star active'></i>";
        $halfStar = "<i class = 'las la-star half'></i>";
        $emptyStar = "<i class = 'las la-star'></i>";
        $rating = $rating <= $maxRating?$rating:$maxRating;

        $fullStarCount = (int)$rating;
        $halfStarCount = ceil($rating)-$fullStarCount;
        $emptyStarCount = $maxRating -$fullStarCount-$halfStarCount;

        $html = str_repeat($fullStar,$fullStarCount);
        $html .= str_repeat($halfStar,$halfStarCount);
        $html .= str_repeat($emptyStar,$emptyStarCount);
        echo $html;
    }
}


if (! function_exists('product_base_price')) {
    function product_base_price($product, $with_tax = true)
    {
        $price = $product->lowest_price;
        $tax = 0;

        foreach ($product->taxes as $product_tax) {
            if ($product_tax->tax_type == 'percent') {
                $tax += ($price * $product_tax->tax) / 100;
            } elseif ($product_tax->tax_type == 'flat') {
                $tax += $product_tax->tax;
            }
        }
        $price += $with_tax ? $tax : 0;

        return $price;
    }
}

if (! function_exists('product_highest_price')) {
    function product_highest_price($product, $with_tax = true)
    {
        $price = $product->highest_price;
        $tax = 0;

        foreach ($product->taxes as $product_tax) {
            if ($product_tax->tax_type == 'percent') {
                $tax += ($price * $product_tax->tax) / 100;
            } elseif ($product_tax->tax_type == 'flat') {
                $tax += $product_tax->tax;
            }
        }
        $price += $with_tax ? $tax : 0;

        return $price;
    }
}

if (! function_exists('product_discounted_base_price')) {
    function product_discounted_base_price($product, $with_tax = true)
    {
        $price = $product->lowest_price;
        $tax = 0;

        $discount_applicable = false;

        if ($product->discount_start_date == null) {
            $discount_applicable = true;
        }
        elseif (strtotime(date('d-m-Y H:i:s')) >= $product->discount_start_date &&
            strtotime(date('d-m-Y H:i:s')) <= $product->discount_end_date) {
            $discount_applicable = true;
        }

        if ($discount_applicable) {
            if($product->discount_type == 'percent'){
                $price -= ($price*$product->discount)/100;
            }
            elseif($product->discount_type == 'flat'){
                $price -= $product->discount;
            }
        }

        foreach ($product->taxes as $product_tax) {
            if ($product_tax->tax_type == 'percent') {
                $tax += ($price * $product_tax->tax) / 100;
            } elseif ($product_tax->tax_type == 'flat') {
                $tax += $product_tax->tax;
            }
        }
        $price += $with_tax ? $tax : 0;
        return $price;
    }
}

if (! function_exists('product_discounted_highest_price')) {    
    /**
     * product_discounted_highest_price
     *
     * @param  mixed $product
     * @return int
     */
    function product_discounted_highest_price($product, $with_tax = true)
    {
        $price = $product->highest_price;
        $tax = 0;

        $discount_applicable = false;

        if ($product->discount_start_date == null) {
            $discount_applicable = true;
        }
        elseif (strtotime(date('d-m-Y H:i:s')) >= $product->discount_start_date &&
            strtotime(date('d-m-Y H:i:s')) <= $product->discount_end_date) {
            $discount_applicable = true;
        }

        if ($discount_applicable) {
            if($product->discount_type == 'percent'){
                $price -= ($price*$product->discount)/100;
            }
            elseif($product->discount_type == 'flat'){
                $price -= $product->discount;
            }
        }

        foreach ($product->taxes as $product_tax) {
            if ($product_tax->tax_type == 'percent') {
                $tax += ($price * $product_tax->tax) / 100;
            } elseif ($product_tax->tax_type == 'flat') {
                $tax += $product_tax->tax;
            }
        }

        $price += $with_tax ? $tax : 0;
        return $price;
    }
}

if (! function_exists('product_tax')) {
    function product_variation_tax($product,$variation)
    {
        $price = $variation->price;
        $tax = 0;

        $discount_applicable = false;

        if ($product->discount_start_date == null) {
            $discount_applicable = true;
        }
        elseif (strtotime(date('d-m-Y H:i:s')) >= $product->discount_start_date &&
            strtotime(date('d-m-Y H:i:s')) <= $product->discount_end_date) {
            $discount_applicable = true;
        }

        if ($discount_applicable) {
            if($product->discount_type == 'percent'){
                $price -= ($price*$product->discount)/100;
            }
            elseif($product->discount_type == 'flat'){
                $price -= $product->discount;
            }
        }

        foreach ($product->taxes as $product_tax) {
            if ($product_tax->tax_type == 'percent') {
                $tax += ($price * $product_tax->tax) / 100;
            } elseif ($product_tax->tax_type == 'flat') {
                $tax += $product_tax->tax;
            }
        }

        return $tax;
    }
}

if (! function_exists('variation_price')) {
    function variation_price($product,$variation, $with_tax = true)
    {
        $price = $variation->price;
        $tax = 0;

        foreach ($product->taxes as $product_tax) {
            if ($product_tax->tax_type == 'percent') {
                $tax += ($price * $product_tax->tax) / 100;
            } elseif ($product_tax->tax_type == 'flat') {
                $tax += $product_tax->tax;
            }
        }

        $price += $with_tax ? $tax : 0;
        return $price;
    }
} 

if (! function_exists('variation_discounted_price')) {    
    /**
     * variation_discounted_price
     *
     * @param  mixed $product object
     * @param  mixed $variation object
     * @return int variation price
     */
    function variation_discounted_price($product,$variation, $with_tax = true)
    {
        $price = $variation->price;
        $tax = 0;

        $discount_applicable = false;

        if ($product->discount_start_date == null) {
            $discount_applicable = true;
        }
        elseif (strtotime(date('d-m-Y H:i:s')) >= $product->discount_start_date &&
            strtotime(date('d-m-Y H:i:s')) <= $product->discount_end_date) {
            $discount_applicable = true;
        }

        if ($discount_applicable) {
            if($product->discount_type == 'percent'){
                $price -= ($price*$product->discount)/100;
            }
            elseif($product->discount_type == 'flat'){
                $price -= $product->discount;
            }
        }

        foreach ($product->taxes as $product_tax) {
            if ($product_tax->tax_type == 'percent') {
                $tax += ($price * $product_tax->tax) / 100;
            } elseif ($product_tax->tax_type == 'flat') {
                $tax += $product_tax->tax;
            }
        }

        $price += $with_tax ? $tax : 0;
        return $price;
    }
}

// for api
if (! function_exists('filter_product_variations')) {    
    /**
     * filter_product_variations
     *
     * @param  mixed $variations product all variations collection
     * @param  mixed $product product object
     * @return void array of product variations after trimming
     */
    function filter_product_variations($variations,$product)
    {   
        if(count($variations) == 0){
            return $variations;
        }
        $new_variations = array();
        foreach($variations as $variation){
            $data['id'] = $variation->id;
            $data['code'] = ($variation->code == null) ? $variation->code : array_filter(explode("/",$variation->code));
            $data['img'] = $variation->img;
            $data['price'] = variation_discounted_price($product, $variation);
            $data['stock'] = $variation->stock;

            array_push($new_variations, $data);
        }
        return $new_variations;
    }
}

// for api
if (! function_exists('filter_variation_combinations')) {    
    /**
     * filter_variation_combinations
     *
     * @param  mixed $combinations collection of a product variation
     * @return void array of attribute name + value name
     */
    function filter_variation_combinations($combinations)
    {   
        if(!$combinations || count($combinations) == 0){
            return $combinations;
        }
        $new_combinations = array();
        foreach($combinations as $combination){
            $data['attribute'] = $combination->attribute->getTranslation('name');
            $data['value'] = $combination->attribute_value->getTranslation('name');

            array_push($new_combinations, $data);
        }
        return $new_combinations;
    }
}

// for api
if (! function_exists('generate_variation_options')) {    
    /**
     * generate_variation_options
     *
     * @param  mixed $options all combinations of all variations
     * @return void 2d array of attribute id + name > attribute values id + name
     */
    function generate_variation_options($options)
    {
        if(count($options) == 0){
            return $options;
        }
        $attrbute_ids = array();
        foreach($options as $option){

            $value_ids = array();
            if(isset($attrbute_ids[$option->attribute_id])){
                $value_ids = $attrbute_ids[$option->attribute_id];
            }
            if(!in_array($option->attribute_value_id,$value_ids)){
                array_push($value_ids, $option->attribute_value_id);
            }
            $attrbute_ids[$option->attribute_id] = $value_ids;
        }
        $options = array();
        foreach($attrbute_ids as $id => $values){
            $vals = array();
            foreach($values as $value){
                $val = array(
                    'id' => $value,
                    'name' => AttributeValue::find($value)->getTranslation('name')
                );
                array_push($vals, $val);
            }
            $data['id'] = $id;
            $data['name'] = Attribute::find($id)->getTranslation('name');
            $data['values'] = $vals;

            array_push($options, $data);
        }
        return $options;
    }
}

// for api
if (! function_exists('banner_array_generate')) {  
    function banner_array_generate($images,$links){
        if(!$images){
            return [];
        }
        $banners = [];

        foreach(json_decode($images) as $key => $value){
            $arr['img'] = api_asset($value);
            $arr['link'] = json_decode($links,true)[$key];
            array_push($banners,$arr);
        }
        return $banners;
    }
}


function translate($key, $lang = null){
    if($lang == null){
        $lang = App::getLocale();
    }

    $lang_key = preg_replace('/[^A-Za-z0-9\_]/', '', str_replace(' ', '_', strtolower($key)));

    $translations_default = Cache::rememberForever('translations-'.env('DEFAULT_LANGUAGE', 'en'), function () {
        return Translation::where('lang', env('DEFAULT_LANGUAGE', 'en'))->pluck('lang_value', 'lang_key')->toArray();
    });

    if(!isset($translations_default[$lang_key])){
        $translation_def = new Translation;
        $translation_def->lang = env('DEFAULT_LANGUAGE', 'en');
        $translation_def->lang_key = $lang_key;
        $translation_def->lang_value = str_replace(array("\r", "\n", "\r\n"), "", $key);
        $translation_def->save();
        Cache::forget('translations-'.env('DEFAULT_LANGUAGE', 'en'));
    }

    $translation_locale = Cache::rememberForever("translations-{$lang}", function () use ($lang) {
        return Translation::where('lang', $lang)->pluck('lang_value', 'lang_key')->toArray();
    });

    //Check for session lang
    if(isset($translation_locale[$lang_key])){
        return trim($translation_locale[$lang_key]);
    }
    elseif(isset($translations_default[$lang_key])){
        return trim($translations_default[$lang_key]);
    }
    else{
        return trim($key);
    }
}


function hex2rgba($color, $opacity = false)
{
    return Colorcodeconverter::convertHexToRgba($color, $opacity);
}


function timezones()
{
    return Timezones::timezonesToArray();
}


if (!function_exists('api_asset')) {
    function api_asset($id)
    {
        if (($asset = \App\Models\Upload::find($id)) != null) {
            return my_asset($asset->file_name);
        }
        return "";
    }
}

//return file uploaded via uploader
if (!function_exists('uploaded_asset')) {
    function uploaded_asset($id)
    {
        if (($asset = \App\Models\Upload::find($id)) != null) {
            return my_asset($asset->file_name);
        }
        return null;
    }
}

if (! function_exists('my_asset')) {
    /**
     * Generate an asset path for the application.
     *
     * @param  string  $path
     * @param  bool|null  $secure
     * @return string
     */
    function my_asset($path, $secure = null)
    {
        if(env('FILESYSTEM_DRIVER') == 's3'){
            return Storage::disk('s3')->url($path);
        }
        else {
            return app('url')->asset('public/'.$path, $secure);
        }
    }
}

if (! function_exists('static_asset')) {
    /**
     * Generate an asset path for the application.
     *
     * @param  string  $path
     * @param  bool|null  $secure
     * @return string
     */
    function static_asset($path, $secure = null)
    {
        return app('url')->asset('public/'.$path, $secure);
    }
}

if (!function_exists('getBaseURL')) {
    function getBaseURL()
    {
        return (isset($_SERVER['HTTPS']) ? "https://" : "http://").$_SERVER['HTTP_HOST'];
    }
}


if (!function_exists('getFileBaseURL')) {
    function getFileBaseURL()
    {
        if(env('FILESYSTEM_DRIVER') == 's3'){
            return env('AWS_URL').'/';
        }
        else {
            return getBaseURL().'/public/';
        }
    }
}


if (!function_exists('get_setting')) {
    function get_setting($key, $default = null)
    {
        $settings = Cache::remember('settings', 86400, function () {
            return Setting::all();
        });

        $setting = $settings->where('type', $key)->first();

        return $setting == null ? $default : $setting->value;
    }
}


if (!function_exists('formatBytes')) {
    function formatBytes($bytes, $precision = 2)
    {
        $units = array('B', 'KB', 'MB', 'GB', 'TB');

        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);

        // Uncomment one of the following alternatives
        $bytes /= pow(1024, $pow);
        // $bytes /= (1 << (10 * $pow));

        return round($bytes, $precision) . ' ' . $units[$pow];
    }
}

?>
