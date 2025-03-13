<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use App\Models\CurrencyRate;

class CurrencyRateService
{
    const BASE_CURRENCY = 'USD';
    const CACHE_TTL = 8;

    public function convert(float $amount, string $fromCurrency, string $toCurrency, CurrencyRate | null $fromRate, CurrencyRate | null $toRate): float
    {
        $convertedAmount = 0;
        if($fromCurrency == $toCurrency) {
            $convertedAmount = $amount;
        } else if($fromCurrency === self::BASE_CURRENCY) {
            $convertedAmount = $amount * $toRate->rate;
        } else if($toCurrency === self::BASE_CURRENCY) {
            $convertedAmount = $amount / $fromRate->rate;
        } else {
            $convertedAmount = $amount / $fromRate->rate;
        }
        return $convertedAmount;
    }

    
    public function fetchRates(): bool
    {
        // If cached data exists, return it directly
        $cacheKey = 'currency_rates_' . self::BASE_CURRENCY;
        $cachedData = Cache::get($cacheKey);
        if ($cachedData) return true;

        $apiUrl = env('EXCHANGE_API_DOMAIN') . '/live?access_key=' . env('EXCHANGE_API_KEY') . '&source=' . self::BASE_CURRENCY;
        $response = Http::get($apiUrl);

        if ($response->successful()) {
            $data = $response->json();

            if (isset($data['quotes']) && is_array($data['quotes'])) {
                foreach ($data['quotes'] as $currencyPair => $rate) {
                    // Extract the target currency code from the currency pair (e.g., USDGBP -> GBP)
                    $currencyCode = str_replace(self::BASE_CURRENCY, '', $currencyPair);
                    if (strlen($currencyCode) === 3) {
                        CurrencyRate::updateOrCreate(
                            ['currency_code' => $currencyCode],
                            ['rate' => round($rate, 6), 'fetched_at' => now()]
                        );
                    }
                }

                Log::info('Base Currency: '.self::BASE_CURRENCY.', Currency rates fetched at '.now());

                Cache::put($cacheKey, $data['quotes'], now()->addHours(self::CACHE_TTL));
                return true;
            } else {
                Log::error('Currency rates data is missing or malformed.', $data);
                return false;
            }
        } else {
            Log::error('Failed to fetch currency rates.', $response->json());
            return false;
        }

        return false;
    }

}