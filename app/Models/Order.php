<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    protected $fillable = [
        'order_number',
        'customer_name',
        'customer_dni',
        'customer_first_name',
        'customer_last_name',
        'customer_email',
        'customer_phone',
        'department',
        'province',
        'district',
        'customer_address',
        'fulfillment_method',
        'payment_method',
        'payment_account_holder',
        'payment_operation_number',
        'payment_proof',
        'status',
        'total',
        'items',
    ];

    protected function casts(): array
    {
        return [
            'total' => 'decimal:2',
            'items' => 'array',
        ];
    }
}
