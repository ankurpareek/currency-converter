<?php

namespace App\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use App\Services\CurrencyRateService;

class FetchCurrencyRates implements ShouldQueue
{
    use Queueable;

    protected $currencyRateService;

    public function handle() : void
    {
        app(CurrencyRateService::class)->fetchRates();
    }
}
