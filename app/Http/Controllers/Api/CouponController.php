<?php

namespace App\Http\Controllers\Api;

use App\Http\Resources\CouponCollection;
use App\Models\Cart;
use App\Models\Coupon;
use App\Models\CouponUsage;
use Illuminate\Http\Request;

class CouponController extends Controller
{
    public function index(Request $request)
    {
        return response()->json([
            'success' => true,
            'message' => '',
            'data' => new CouponCollection(Coupon::where('start_date', '<=', strtotime(date('d-m-Y H:i:s')))->where('end_date', '>=', strtotime(date('d-m-Y H:i:s')))->get()),
        ]);
    }

    public function apply(Request $request)
    {
        $coupon = Coupon::where('code', $request->coupon_code)->first();

        if ($coupon == null || strtotime(date('d-m-Y')) < $coupon->start_date || strtotime(date('d-m-Y')) > $coupon->end_date) {
            return response()->json([
                'success' => false,
                'message' => translate('The coupon is invalid.')
            ]);
        }

        if (CouponUsage::where('user_id', auth('api')->user()->id)->where('coupon_id', $coupon->id)->first() != null) {
            return response()->json([
                'success' => false,
                'message' => translate("You've already used the coupon.")
            ]);
        }

        $couponDetails = json_decode($coupon->details);

        if ($coupon->type == 'cart_base') {
            $cartPrice = 0;
            $cartItems = Cart::whereIn('id', $request->cart_item_ids)->with('variation.product')->get();

            foreach ($cartItems as $cartItem) {
                $cartPrice += $cartItem->quantity * variation_discounted_price($cartItem->variation->product, $cartItem->variation);
            }

            $min_buy = (float) $couponDetails->min_buy;

            if ($cartPrice > $min_buy) {
                return response()->json([
                    'success' => true,
                    'coupon_details' => [
                        'coupon_type' => 'cart_base',
                        'discount' => $coupon->discount,
                        'discount_type' => $coupon->discount_type,
                        'conditions' => $couponDetails
                    ],
                    'message' => translate('Coupon code applied successfully')
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => translate("Minimum order total of") . ' ' . format_price($min_buy) . ' ' . translate("is required to use this coupon code")
                ]);
            }
        } elseif ($coupon->type == 'product_base') {

            $discountApplicable = false;
            $cartItemsIds = Cart::where('user_id', auth('api')->user()->id)->pluck('product_id')->toArray();

            foreach ($couponDetails as $key => $couponDetail) {
                if (in_array($couponDetail->product_id, $cartItemsIds)) {
                    $discountApplicable = true;
                }
            }

            if ($discountApplicable) {
                return response()->json([
                    'success' => true,
                    'coupon_details' => [
                        'coupon_type' => 'product_base',
                        'discount' => $coupon->discount,
                        'discount_type' => $coupon->discount_type,
                        'conditions' => $couponDetails
                    ],
                    'message' => translate('Coupon code applied successfully')
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => translate("This coupon code is no applicable for your cart products.")
                ]);
            }
        }
    }
}
