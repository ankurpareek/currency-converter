<?php

namespace Tests\Feature;

use Tests\TestCase;
use \PHPUnit\Framework\Attributes\Test;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Queue;
use App\Jobs\FetchCurrencyRates;

class CurrencyConvertCommandTest extends TestCase
{

    #[Test]
    public function it_converts_currency_successfully()
    {
        $amount = 100;
        $fromCurrency = 'USD';
        $toCurrency = 'EUR';
        
        $exitCode = Artisan::call('currency:convert', [
            'amount' => $amount,
            'from_currency' => $fromCurrency,
            'to_currency' => $toCurrency,
        ]);

        $output = Artisan::output();

        $this->assertEquals(0, $exitCode);
        $this->assertStringContainsString("{$amount} {$fromCurrency} is equal to", $output);
        $this->assertStringContainsString("EUR", $output);
    }

    #[Test]
    public function it_fails_with_invalid_currency_code()
    {
        $exitCode = Artisan::call('currency:convert', [
            'amount' => 100,
            'from_currency' => 'USD',
            'to_currency' => 'XXX', // Invalid currency code
        ]);

        $output = Artisan::output();

        $this->assertEquals(1, $exitCode);
        $this->assertStringContainsString("Exchange rate for currency XXX not found.", $output);
    }

    #[Test]
    public function it_fails_with_invalid_amount()
    {
        $exitCode = Artisan::call('currency:convert', [
            'amount' => -100,
            'from_currency' => 'USD',
            'to_currency' => 'EUR',
        ]);

        $this->assertEquals(1, $exitCode);
        $this->assertStringContainsString("Invalid amount: The amount must be a positive number.", Artisan::output());
    }

    #[Test]
    public function it_dispatches_fetch_currency_rates_job()
    {
        // Assert that the job is dispatched
        Queue::fake();

        // Act: Run the console command
        Artisan::call('currency:convert', [
            'amount' => 100,
            'from_currency' => 'USD',
            'to_currency' => 'EUR',
        ]);

        // Assert: Check if the FetchCurrencyRates job was dispatched
        Queue::assertPushed(FetchCurrencyRates::class);
    }
}