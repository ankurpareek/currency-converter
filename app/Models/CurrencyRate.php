<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CurrencyRate extends Model
{
    protected $table = 'currency_rates';

    public $timestamps = true;

    protected $fillable = ['currency_code', 'rate', 'fetched_at'];

    protected $casts = [
        'rate' => 'decimal:8',
        'fetched_at' => 'datetime',
    ];
}
