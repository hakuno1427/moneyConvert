<?php

namespace Tests\Unit\Console\Command;

use App\Models\Currency;
use App\Models\HistoricalExchangeRate;
use App\Repositories\CurrencyRepository;
use App\Repositories\HistoricalExchangeRateRepository;
use App\Services\CurrencyAPIService;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Mockery;
use Tests\TestCase;

class SyncHistoricalRatesTest extends TestCase
{
    protected $currencyRepository;
    protected $currencyAPIService;
    protected $exchangeRateRepository;

    public function test_syncs_historical_rates_successfully()
    {
        $baseCurrency = new Currency(['code' => 'USD']);
        $this->currencyRepository->shouldReceive('getBaseCurrency')->andReturn($baseCurrency);
        $this->exchangeRateRepository->shouldReceive('getLastSyncedDate')
            ->andReturn(Carbon::today()->subDays(1));

        $currencies = new Collection([
            new Currency(['code' => 'USD']),
            new Currency(['code' => 'EUR']),
            new Currency(['code' => 'GBP']),
        ]);
        $this->currencyRepository->shouldReceive('getAll')->andReturn($currencies);

        $this->currencyAPIService->shouldReceive('getHistoricalRates')
            ->once()
            ->withArgs([Carbon::today()->toDateString(), ['USD', 'EUR', 'GBP']])
            ->andReturn([
                'rates' => [
                    'USD' => 1,
                    'EUR' => 0.85,
                    'GBP' => 0.75,
                ]
            ]);

        $this->exchangeRateRepository->shouldReceive('create')->twice(); // EUR and GBP (not USD)

        $this->artisan('currency:sync-historical')
            ->expectsOutput('Fetching historical rates for ' . Carbon::today()->toDateString())
            ->expectsOutput('Synced rates for ' . Carbon::today()->toDateString())
            ->expectsOutput('Historical rate sync complete.')
            ->assertExitCode(0);
    }

    public function test_new_syncs_historical_rates_successfully()
    {
        $baseCurrency = new Currency(['code' => 'USD']);
        $this->currencyRepository->shouldReceive('getBaseCurrency')->andReturn($baseCurrency);
        $this->exchangeRateRepository->shouldReceive('getLastSyncedDate')
            ->andReturnNull();

        $currencies = new Collection([
            new Currency(['code' => 'USD']),
            new Currency(['code' => 'EUR']),
            new Currency(['code' => 'GBP']),
        ]);
        $this->currencyRepository->shouldReceive('getAll')->andReturn($currencies);

        for ($i = 0; $i < HistoricalExchangeRate::DAYS_TO_COMPARE; $i++) {
            $this->currencyAPIService->shouldReceive('getHistoricalRates')
                ->withArgs([Carbon::today()->subDays($i)->toDateString(), ['USD', 'EUR', 'GBP']])
                ->andReturn([
                    'rates' => [
                        'USD' => 1,
                        'EUR' => 0.85,
                        'GBP' => 0.75,
                    ]
                ]);
        }

        $this->exchangeRateRepository->shouldReceive('create');

        $this->artisan('currency:sync-historical')
            ->expectsOutput('Fetching historical rates for ' . Carbon::today()->toDateString())
            ->expectsOutput('Synced rates for ' . Carbon::today()->toDateString())
            ->expectsOutput('Historical rate sync complete.')
            ->assertExitCode(0);
    }

    public function test_not_sync_if_rates_are_already_synced_today()
    {
        $baseCurrency = new Currency(['code' => 'USD']);
        $this->currencyRepository->shouldReceive('getBaseCurrency')->andReturn($baseCurrency);
        $this->exchangeRateRepository->shouldReceive('getLastSyncedDate')
            ->once()
            ->andReturn(now()->startOfDay());

        $this->artisan('currency:sync-historical')
            ->expectsOutput('Historical rates already synced for today.')
            ->assertExitCode(0);
    }

    public function test_it_handles_api_failure_gracefully()
    {
        $baseCurrency = new Currency(['code' => 'USD']);
        $this->currencyRepository->shouldReceive('getBaseCurrency')->andReturn($baseCurrency);
        $this->exchangeRateRepository->shouldReceive('getLastSyncedDate')
            ->andReturn(Carbon::today()->subDays(1));

        $currencies = new Collection([
            new Currency(['code' => 'USD']),
            new Currency(['code' => 'EUR']),
            new Currency(['code' => 'GBP']),
        ]);
        $this->currencyRepository->shouldReceive('getAll')->andReturn($currencies);

        $this->currencyAPIService->shouldReceive('getHistoricalRates')
            ->once()
            ->andReturn(null); // Simulate API failure

        $this->artisan('currency:sync-historical')
            ->expectsOutput('Fetching historical rates for ' . Carbon::today()->toDateString())
            ->expectsOutput('Failed to fetch historical rates for ' . Carbon::today()->toDateString())
            ->expectsOutput('Historical rate sync complete.')
            ->assertExitCode(0); // Matches the command's return value in failure case
    }

    public function test_it_exits_gracefully_if_base_currency_is_null()
    {
        $this->currencyRepository->shouldReceive('getBaseCurrency')->andReturn(null);

        $this->artisan('currency:sync-historical')
            ->expectsOutput('Base currency is not set')
            ->assertExitCode(0);
    }

    protected function setUp(): void
    {
        parent::setUp();

        // Mock dependencies
        $this->currencyRepository = Mockery::mock(CurrencyRepository::class);
        $this->currencyAPIService = Mockery::mock(CurrencyAPIService::class);
        $this->exchangeRateRepository = Mockery::mock(HistoricalExchangeRateRepository::class);

        // Bind mocks into the container
        $this->app->instance(CurrencyRepository::class, $this->currencyRepository);
        $this->app->instance(CurrencyAPIService::class, $this->currencyAPIService);
        $this->app->instance(HistoricalExchangeRateRepository::class, $this->exchangeRateRepository);
    }

}
