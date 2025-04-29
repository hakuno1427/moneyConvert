<?php

namespace Test\Services;

use App\Services\CurrencyAPIService;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class CurrencyAPIServiceTest extends TestCase
{
    public function test_get_latest_rates()
    {
        Http::fake([
            'https://openexchangerates.org/api/latest.json*' => Http::response([
                'rates' => [
                    'USD' => 1.0,
                    'EUR' => 0.9,
                ]
            ], 200),
        ]);

        $service = new CurrencyAPIService();
        $response = $service->getLatestRates(['USD', 'EUR']);

        $this->assertArrayHasKey('rates', $response);
        $this->assertEquals(1.0, $response['rates']['USD']);
    }

    public function test_get_historical_rates()
    {
        Http::fake([
            'https://openexchangerates.org/api/historical/2024-04-28.json*' => Http::response([
                'rates' => [
                    'USD' => 1.0,
                    'EUR' => 0.88,
                ]
            ], 200),
        ]);

        $service = new CurrencyAPIService();
        $response = $service->getHistoricalRates('2024-04-28', ['USD', 'EUR']);

        $this->assertArrayHasKey('rates', $response);
        $this->assertEquals(0.88, $response['rates']['EUR']);
    }

    public function test_get_latest_rates_fails()
    {
        Http::fake([
            'https://openexchangerates.org/api/latest.json*' => Http::response([], 500),
        ]);

        $service = new CurrencyAPIService();
        $response = $service->getLatestRates(['USD', 'EUR']);

        $this->assertNull($response);
    }

    protected function setUp(): void
    {
        parent::setUp();

        Config::shouldReceive('get')
            ->with('services.openexchangerates.key', null)
            ->andReturn('fake-api-key');
    }
}
