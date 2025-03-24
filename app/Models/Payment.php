<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    use HasFactory;

    protected $fillable = ['merchantId', 'merchantTransactionId','payment_status','status','merchantUserId', 'amount', 'redirectUrl', 'redirectMode', 'callbackUrl', 'paymentInstrument', 'order_id'];
}
