<?php

namespace App\Http\Resources;

use Carbon\Carbon;
use Illuminate\Http\Resources\Json\ResourceCollection;
use App\Http\Resources\OrderProductCollection;

class OrderCollection extends ResourceCollection
{
    public function toArray($request)
    {
        return [
            'data' => $this->collection->map(function($data) {
                return [
                    'id' => $data->id,
                    'code' => $data->code,
                    'user' => [
                        'name' => $data->user->name,
                        'email' => $data->user->email,
                        'phone' => $data->user->phone,
                        'avatar' => api_asset($data->user->avatar),
                    ],
                    'shipping_address' => json_decode($data->shipping_address),
                    'billing_address' => json_decode($data->billing_address),
                    'payment_type' => $data->payment_type,
                    'delivery_type' => $data->delivery_type,
                    'delivery_status' => $data->delivery_status,
                    'payment_status' => $data->payment_status,
                    'grand_total' => (double) $data->grand_total,
                    'coupon_discount' => (double) $data->coupon_discount,
                    'shipping_cost' => (double) $data->shipping_cost,
                    'subtotal' => (double) $data->orderDetails->sum('total') - $this->calculateTotalTax($data->orderDetails),
                    'tax' => (double) $this->calculateTotalTax($data->orderDetails),
                    'products' => new OrderProductCollection($data->orderDetails),
                    'date' => $data->created_at->toFormattedDateString()
                ];
            })
        ];
    }

    public function with($request)
    {
        return [
            'success' => true,
            'status' => 200
        ];
    }

    protected function calculateTotalTax($orderDetails){
        $tax = 0;
        foreach($orderDetails as $item){
            $tax += $item->tax*$item->quantity;
        }
        return $tax;
    }
}
