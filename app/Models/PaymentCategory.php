<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PaymentCategory extends Model
{
    protected $fillable = [
        'title',
        'description',
        'nominal',
        'status',
        'type',
    ];
}
