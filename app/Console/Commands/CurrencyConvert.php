<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\CurrencyRate;
use App\Jobs\FetchCurrencyRates;
use App\Services\CurrencyRateService;

class CurrencyConvert extends Command
{
    protected $signature = 'currency:convert {amount} {from_currency} {to_currency}';
    protected $description = 'Convert an amount from one currency to another using stored exchange rates';

    public function __construct()
    {
        parent::__construct();
    }

    public function handle()
    {
        // Trigger the job to fetch the latest rates
        FetchCurrencyRates::dispatchSync();

        $amount = $this->argument('amount');
        $fromCurrency = strtoupper($this->argument('from_currency'));
        $toCurrency = strtoupper($this->argument('to_currency'));

        if (!is_numeric($amount) || $amount <= 0) {
            $this->error("Invalid amount: The amount must be a positive number.");
            return self::FAILURE;
        }

        if (!$this->isValidCurrency($fromCurrency) || !$this->isValidCurrency($toCurrency)) {
            return self::FAILURE;
        }

        $fromRate = $this->getCurrencyRate($fromCurrency);
        if ($fromCurrency !== CurrencyRateService::BASE_CURRENCY && !$fromRate) {
            return self::FAILURE;
        }

        $toRate = $this->getCurrencyRate($toCurrency);
        if ($toCurrency !== CurrencyRateService::BASE_CURRENCY && !$toRate) {
            return self::FAILURE;
        }

        $convertedAmount = app(CurrencyRateService::class)->convert($amount, $fromCurrency, $toCurrency, $fromRate, $toRate);

        $this->info("{$amount} {$fromCurrency} is equal to {$convertedAmount} {$toCurrency}.");

        return self::SUCCESS;
    }

    private function getCurrencyRate(string $currency): ? CurrencyRate
    {
        if ($currency === CurrencyRateService::BASE_CURRENCY) {
            return null;
        }

        $currencyRate = CurrencyRate::where('currency_code', $currency)->first();

        if (!$currencyRate) {
            $this->error("Exchange rate for currency {$currency} not found.");
        }

        return $currencyRate;
    }

    private function isValidCurrency($currencyCode) : int|false
    {
        $match = preg_match('/^[A-Z]{3}$/', $currencyCode);
        if (!$match) {
            $this->error("Invalid currency code: {$currencyCode}. Please provide a valid 3-letter currency code.");
        }
        return $match;
    }
}
