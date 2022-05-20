<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Models\Order;
use App\Models\Cart;
use App\Models\OrderDetail;
use App\Models\Address;
use App\Http\Resources\OrderCollection;
use App\Http\Resources\OrderSingleCollection;
use App\Models\City;
use App\Models\Language;
use App\Notifications\OrderPlacedNotification;
use PDF;
use DB;

class OrderController extends Controller
{
    public function index()
    {
        return new OrderCollection(Order::with(['user','orderDetails'])->where('user_id', auth('api')->user()->id)->latest()->paginate(12));
    }

    public function show($order_code)
    {
        $order = Order::where('code',$order_code)->with(['user','orderDetails.variation.product','orderDetails.variation.combinations'])->first();
        if($order){
            if(auth('api')->user()->id == $order->user_id){
                return new OrderSingleCollection($order);
            }else{
                return response()->json([
                    'success' => false,
                    'message' => translate("This order is not your. You can't check details of this order"),
                    'status' => 200
                ]);
            }
        }else{
            return response()->json([
                'success' => false,
                'message' => translate("No order found by this code"),
                'status' => 404
            ]);
        }
    }

    public function get_shipping_cost(Request $request,$address_id){
        $address = Address::find($address_id);
        $city = City::find($address->city_id);

        if($city && $city->zone != null){
            return response()->json([
                'success' => true,
                'standard_delivery_cost' => $city->zone->standard_delivery_cost,
                'express_delivery_cost' => $city->zone->express_delivery_cost,
            ]);
        }else{
            return response()->json([
                'success' => false,
                'standard_delivery_cost' => 0,
                'express_delivery_cost' => 0,
            ]);
        }
    }

    public function invoice_download(Request $request,$order_code)
    {
        $currency_code = env('DEFAULT_CURRENCY_CODE');

        $language_code = app()->getLocale();

        if(optional(Language::where('code', $language_code)->first())->rtl == 1){
            $direction = 'rtl';
            $default_text_align = 'right';
            $reverse_text_align = 'left';
        }else{
            $direction = 'ltr';
            $default_text_align = 'left';
            $reverse_text_align = 'right';            
        }


        if($currency_code == 'BDT' || $language_code == 'bd'){
            // bengali font
            $font_family = "'Hind Siliguri','sans-serif'";
        }elseif($currency_code == 'KHR' || $language_code == 'kh'){
            // khmer font
            $font_family = "'Hanuman','sans-serif'";
        }elseif($currency_code == 'AMD'){
            // Armenia font
            $font_family = "'arnamu','sans-serif'";
        }elseif($currency_code == 'ILS'){
            // Israeli font
            $font_family = "'Varela Round','sans-serif'";
        }elseif($currency_code == 'AED' || $currency_code == 'EGP' || $language_code == 'sa' || $currency_code == 'IQD'|| $language_code == 'ir'){
            // middle east/arabic font
            $font_family = "'XBRiyaz','sans-serif'";
        }else{
            // general for all
            $font_family = "'Roboto','sans-serif'";
        }

        $order = Order::where('code',$order_code)->first();
        $pdf =  PDF::loadView('backend.invoices.invoice',[
            'order' => $order,
            'font_family' => $font_family,
            'direction' => $direction,
            'default_text_align' => $default_text_align,
            'reverse_text_align' => $reverse_text_align
        ], [], [])->save(public_path('invoices/').'order-invoice-'.$order->code.'.pdf');

        $pdf = static_asset('invoices/'.'order-invoice-'.$order->code.'.pdf');

        try {
            return response()->json([
                'success' => true,
                'message' => translate('Invoice generated.'),
                'invoice_url' => $pdf
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => translate('Something went wrong!'),
                'invoice_url' => ''
            ]);
        }
    }

    public function cancel($order_code)
    {
        $order = Order::where('code',$order_code)->first();
        if(auth('api')->user()->id !==  $order->user_id){
            return response()->json(null, 401);
        }

        if($order->delivery_status == 'order_placed' && $order->payment_status == 'unpaid'){
            $order->delivery_status = 'cancelled';
            $order->save();

            foreach($order->orderDetails as $orderDetail){
                try{
                    foreach($orderDetail->product->categories as $category){
                        $category->sales_amount -= $orderDetail->total;
                        $category->save();
                    }
        
                    $brand = $orderDetail->product->brand;
                    if($brand){
                        $brand->sales_amount -= $orderDetail->total;
                        $brand->save();
                    }
                }
                catch(\Exception $e){
                    
                }
            }

            return response()->json([
                'success' => true,
                'message' => translate("Order has been cancelled"),
            ]);
        }else{
            return response()->json([
                'success' => false,
                'message' => translate("This order can't be cancelled."),
            ]);
        }
    }

    public function store(Request $request)
    {
        $cartItems = Cart::whereIn('id',$request->cart_item_ids)->with(['variation.product'])->get();
        $shippingAddress = Address::find($request->shipping_address_id);
        $billingAddress = Address::find($request->billing_address_id);
        $shippingCity = City::with('zone')->find($shippingAddress->city_id);

        $subTotal = 0;
        $shipping = 0;
        $tax = 0;
        foreach ($cartItems as $cartItem) {
            $itemPriceWithoutTax = variation_discounted_price($cartItem->variation->product,$cartItem->variation,false)*$cartItem->quantity;
            $itemTax = product_variation_tax($cartItem->variation->product,$cartItem->variation)*$cartItem->quantity;

            $subTotal += $itemPriceWithoutTax;
            $tax += $itemTax;
        }

        if($cartItems->count() < 1)
            return response()->json([
                'success' => false,
                'message' => translate('Your cart is empty. Please select a product.')
            ]);
        
        if(!$request->shipping_address_id)
            return response()->json([
                'success' => false,
                'message' => translate('Please select a shipping address.')
            ]);
            
        if(!$request->billing_address_id)
            return response()->json([
                'success' => false,
                'message' => translate('Please select a billing address.')
            ]);

        if($request->delivery_type != 'standard' && $request->delivery_type != 'express')
            return response()->json([
                'success' => false,
                'message' => translate('Please select a delivery option.')
            ]);

        if(!$shippingCity->zone)
            return response()->json([
                'success' => false,
                'message' => translate('Sorry, delivery is not available in this shipping address.')
            ]);

        foreach ($cartItems as $cartItem) {
            if(!$cartItem->variation->stock){
                return response()->json([
                    'success' => false,
                    'message' => $cartItem->variation->product->getTranslation('name').' '.translate('is out of stock.')
                ]);
            }
        }


        if($request->delivery_type == 'standard'){
            $shipping = $shippingCity->zone->standard_delivery_cost;
        }elseif($request->delivery_type == 'express'){
            $shipping = $shippingCity->zone->express_delivery_cost;
        }

        $coupon_discount = 0;
        $total = $subTotal + $shipping + $tax;

        // coupon check
        if ($request->coupon_code && $request->coupon_code != '') {

            $coupon = (new CouponController)->apply($request)->getData();

            if(!$coupon->success){
                return response()->json([
                    'success' => false,
                    'message' => $coupon->message
                ]);
            }
            if($coupon->coupon_details->coupon_type == 'cart_base'){

                if($coupon->coupon_details->discount_type == 'percent'){
                    $coupon_discount += ($total * $coupon->coupon_details->discount)/100;
                    if ($coupon_discount > $coupon->coupon_details->conditions->max_discount) {
                        $coupon_discount = $coupon->coupon_details->conditions->max_discount;
                    }
                }else if($coupon->coupon_details->discount_type == 'amount'){
                    $coupon_discount += $coupon->coupon_details->discount;
                }

            }elseif($coupon->coupon_details->coupon_type == 'product_base'){

                $applicable_product_ids = array_map(function($item){
                            return (int) $item->product_id;
                        },$coupon->coupon_details->conditions);

                foreach ($cartItems as $cartItem) {

                    if(in_array($cartItem->product_id,$applicable_product_ids)){

                        if($coupon->coupon_details->discount_type == 'percent'){

                            $dicounted_price = variation_discounted_price($cartItem->variation->product,$cartItem->variation);

                            $coupon_discount += (($dicounted_price*$coupon->coupon_details->discount)/100) * $cartItem->quantity;

                        }else if($coupon->coupon_details->discount_type == 'amount'){
                            $coupon_discount += $cartItem->quantity*$coupon->coupon_details->discount;
                        }
                    }

                };

            }
        }
        
        $grand_total = $total - $coupon_discount;

        if($request->payment_type == 'wallet' && $grand_total > auth('api')->user()->balance){
            return response()->json([
                'success' => false,
                'message' => translate('You do not have enough balance in your wallet. Please recharge your wallet or select another payment method.')
            ]);
        }

            
        
        

        $order = Order::create([
            'user_id' => auth('api')->user()->id,
            'code' => date('Ymd-His') . rand(10, 99),
            'shipping_address' => json_encode($shippingAddress),
            'billing_address' => json_encode($billingAddress),
            'shipping_cost' => $shipping,
            'grand_total' => $grand_total,
            'coupon_code' => $request->coupon_code,
            'coupon_discount' => $coupon_discount,
            'delivery_type' => $request->delivery_type,
            'payment_type' => $request->payment_type,
        ]);

        foreach ($cartItems as $cartItem) {
            $itemPriceWithoutTax = variation_discounted_price($cartItem->variation->product,$cartItem->variation,false);
            $itemTax = product_variation_tax($cartItem->variation->product,$cartItem->variation);

            $orderDetail = OrderDetail::create([
                'order_id' => $order->id,
                'product_id' => $cartItem->product_id,
                'product_variation_id' => $cartItem->product_variation_id,
                'price' => $itemPriceWithoutTax,
                'tax' => $itemTax,
                'total' => ($itemPriceWithoutTax+$itemTax)*$cartItem->quantity,
                'quantity' => $cartItem->quantity,
            ]);

            $cartItem->product->update([
                'num_of_sale' => DB::raw('num_of_sale + ' . $cartItem->quantity)
            ]);

            foreach($orderDetail->product->categories as $category){
                $category->sales_amount += $orderDetail->total;
                $category->save();
            }

            $brand = $orderDetail->product->brand;
            if($brand){
                $brand->sales_amount += $orderDetail->total;
                $brand->save();
            }
        }

        // clear user's cart
        Cart::destroy($request->cart_item_ids);
        
        $user = auth('api')->user();
        if($request->payment_type == 'wallet'){
            $user->balance -= $order->grand_total;
            $user->save();

            $this->paymentDone($order, $request->payment_type);
        }

        try {
            $user->notify(new OrderPlacedNotification($order));
        }catch(\Exception $e) {

        }

        if($request->payment_type =='cash_on_delivery' || $request->payment_type == 'wallet'){
            return response()->json([
                'success' => true,
                'go_to_payment' => false,
                'grand_total' => $grand_total,
                'payment_method' => $request->payment_type,
                'message' => translate('Your order has been placed successfully'),
                'order_code' => $order->code
            ]);
        }else{
            return response()->json([
                'success' => true,
                'go_to_payment' => true,
                'grand_total' => $grand_total,
                'payment_method' => $request->payment_type,
                'message' => translate('Your order has been placed successfully'),
                'order_code' => $order->code
            ]);
        }
    }


    public function paymentDone($order,$payment_method,$payment_info = null){
        $order->payment_status = 'paid';
        $order->payment_type = $payment_method;
        $order->payment_details = $payment_info;
        $order->save();
    }
}
