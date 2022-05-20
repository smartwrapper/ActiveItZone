<?php

namespace App\Http\Resources;

use App\Http\Resources\OrderProductCollection;
use Illuminate\Http\Resources\Json\JsonResource;

class OrderSingleCollection extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {   
        
        return [
            'id' => $this->id,
            'code' => $this->code,
            'user' => [
                'name' => $this->user->name,
                'email' => $this->user->email,
                'phone' => $this->user->phone,
                'avatar' => api_asset($this->user->avatar),
            ],
            'shipping_address' => json_decode($this->shipping_address),
            'billing_address' => json_decode($this->billing_address),
            'payment_type' => $this->payment_type,
            'delivery_type' => $this->delivery_type,
            'delivery_status' => $this->delivery_status,
            'payment_status' => $this->payment_status,
            'grand_total' => (double) $this->grand_total,
            'coupon_discount' => (double) $this->coupon_discount,
            'shipping_cost' => (double) $this->shipping_cost,
            'subtotal' => (double) $this->orderDetails->sum('total') - $this->calculateTotalTax($this->orderDetails),
            'tax' => (double) $this->calculateTotalTax($this->orderDetails),
            'products' => new OrderProductCollection($this->orderDetails),
            'date' => $this->created_at->toFormattedDateString()
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