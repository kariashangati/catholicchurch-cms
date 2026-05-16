<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    protected $fillable = [
        'phone',
        'amount',
        'currency',
        'country',
        'payment_reference',
        'third_party_conversation_id',
        'conversation_id',
        'transaction_id',
        'status',
        'request_payload',
        'mpesa_response',
        'callback_payload',
        'paid_at',
    ];

    protected $casts = [
        'request_payload' => 'array',
        'mpesa_response' => 'array',
        'callback_payload' => 'array',
        'paid_at' => 'datetime',
    ];
}