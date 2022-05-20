<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\User;

class Order extends Model
{
    use SoftDeletes;

    protected $fillable = ['user_id', 'code', 'shipping_address', 'billing_address', 'shipping_cost', 'delivery_type', 'payment_type', 'payment_status', 'grand_total', 'coupon_code', 'coupon_discount'];

    public function orderDetails()
    {
        return $this->hasMany(OrderDetail::class)->withTrashed();
    }

    public function user()
    {
        return $this->belongsTo(User::class)->withTrashed();
    }
}
