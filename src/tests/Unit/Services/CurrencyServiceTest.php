<?php

namespace Tests\Unit\Service;

use App\Models\Currency;
use App\Models\HistoricalExchangeRate;
use App\Repositories\CurrencyRepositoryInterface;
use App\Repositories\HistoricalExchangeRateRepositoryInterface;
use App\Services\CurrencyAPIService;
use App\Services\CurrencyService;
use Exception;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\JsonResponse;
use Mockery;
use ReflectionClass;
use Tests\TestCase;

class CurrencyServiceTest extends TestCase
{
    protected $currencyRepository;
    protected $exchangeRateRepository;
    protected $currencyApiService;
    protected $currencyService;

    public function test_get_all_currencies_returns_collection()
    {
        $currencies = new Collection([
            new Currency(['code' => 'USD']),
            new Currency(['code' => 'EUR']),
        ]);

        $this->currencyRepository
            ->shouldReceive('getAll')
            ->once()
            ->andReturn($currencies);

        $result = $this->currencyService->getAllCurrencies();

        $this->assertInstanceOf(Collection::class, $result);
        $this->assertCount(2, $result);
    }

    public function test_get_rates_returns_json_response()
    {
        $currencies = new Collection([
            new Currency(['code' => 'USD']),
            new Currency(['code' => 'EUR']),
        ]);

        $this->currencyRepository
            ->shouldReceive('getAll')
            ->once()
            ->andReturn($currencies);

        $this->currencyRepository
            ->shouldReceive('getBaseCurrency')
            ->once()
            ->andReturn(new Currency(['code' => 'USD']));

        $this->currencyApiService
            ->shouldReceive('getLatestRates')
            ->once()
            ->andReturn([
                'rates' => [
                    'USD' => 1.0,
                    'EUR' => 0.85,
                ]
            ]);

        $response = $this->currencyService->getRates();

        $this->assertIsArray($response);
        $this->assertArrayHasKey('EUR', $response);
        $this->assertEquals(0.85, $response['EUR']);
    }

    public function test_get_rates_throws_exception()
    {
        $this->currencyRepository
            ->shouldReceive('getBaseCurrency')
            ->once()
            ->andReturnNull();

        $this->expectException(Exception::class);
        $this->currencyService->getRates();
    }

    public function test_get_latest_usd_rates_fails()
    {
        $currencies = new Collection([
            new Currency(['code' => 'USD']),
            new Currency(['code' => 'EUR']),
        ]);

        $this->currencyRepository
            ->shouldReceive('getAll')
            ->once()
            ->andReturn($currencies);

        $this->currencyApiService
            ->shouldReceive('getLatestRates')
            ->once()
            ->andReturn([
                'rates' => null,
            ]);

        $result = $this->invokeMethod($this->currencyService, 'getLatestUSDRates');

        $this->assertInstanceOf(JsonResponse::class, $result);
        $this->assertEquals(500, $result->getStatusCode());
    }

    /**
     * Helper to invoke protected methods
     */
    protected function invokeMethod(&$object, $methodName, array $parameters = [])
    {
        $reflection = new ReflectionClass(get_class($object));
        $method = $reflection->getMethod($methodName);
        $method->setAccessible(true);

        return $method->invokeArgs($object, $parameters);
    }

    public function test_get_historical_exchange_rates_returns_array()
    {
        $baseCurrency = new Currency(['code' => 'USD']);

        $this->currencyRepository
            ->shouldReceive('getBaseCurrency')
            ->once()
            ->andReturn($baseCurrency);

        $historicalRates = new Collection([
            new HistoricalExchangeRate([
                'from_code' => 'USD',
                'to_code' => 'EUR',
                'rate' => 0.9,
                'date' => now(),
            ]),
            new HistoricalExchangeRate([
                'from_code' => 'USD',
                'to_code' => 'EUR',
                'rate' => 0.92,
                'date' => now()->addDay(),
            ]),
        ]);

        $this->exchangeRateRepository
            ->shouldReceive('getRatesFromBaseCurrency')
            ->once()
            ->with('USD')
            ->andReturn($historicalRates);

        $result = $this->currencyService->getHistoricalExchangeRates();

        $this->assertIsArray($result);
        $this->assertArrayHasKey('EUR', $result);
        $this->assertCount(2, $result['EUR']);
    }

    public function test_get_historical_exchange_rates_throws_exception()
    {
        $this->currencyRepository
            ->shouldReceive('getBaseCurrency')
            ->once()
            ->andReturnNull();

        $this->expectException(Exception::class);
        $this->currencyService->getHistoricalExchangeRates();
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->currencyRepository = Mockery::mock(CurrencyRepositoryInterface::class);
        $this->exchangeRateRepository = Mockery::mock(HistoricalExchangeRateRepositoryInterface::class);
        $this->currencyApiService = Mockery::mock(CurrencyAPIService::class);

        $this->currencyService = new CurrencyService(
            $this->currencyRepository,
            $this->exchangeRateRepository,
            $this->currencyApiService
        );
    }
}
