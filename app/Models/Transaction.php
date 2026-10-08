<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Transaction extends Model
{
    protected $fillable = [
        'order_id',
        'amount',
        'customer_name',
        'payment_token',
        'status',
    ];
}
