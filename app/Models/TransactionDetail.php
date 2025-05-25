<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class TransactionDetail extends Model
{
    use HasUuids;

    protected $fillable = [
        'transaction_id',
        'payment_category_id',
        'nominal',
        'notes',
    ];
}
