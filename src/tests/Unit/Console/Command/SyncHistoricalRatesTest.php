<?php

namespace Tests\Unit\Console\Commands;

use App\Models\Currency;
use App\Repositories\CurrencyRepository;
use App\Repositories\HistoricalExchangeRateRepository;
use App\Services\CurrencyAPIService;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Mockery;
use Tests\TestCase;

class SyncHistoricalRatesTest extends TestCase
{
    protected $currencyRepository;
    protected $currencyAPIService;
    protected $exchangeRateRepository;
    protected $command;

    public function it_syncs_historical_rates_successfully()
    {
        // Mock return values for repository methods
        $baseCurrency = new Currency(['code' => 'USD']);
        $this->currencyRepository->shouldReceive('getBaseCurrency')->andReturn($baseCurrency);

        // Simulate currencies in the repository
        $currencies = new Collection([
            new Currency(['code' => 'EUR']),
            new Currency(['code' => 'GBP']),
        ]);
        $this->currencyRepository->shouldReceive('getAll')->andReturn($currencies);

        // Mock the historical rates API response
        $this->currencyAPIService->shouldReceive('getHistoricalRates')
            ->once()
            ->withArgs([Carbon::today()->subDays(1)->toDateString(), ['USD', 'EUR', 'GBP']])
            ->andReturn([
                'rates' => [
                    'USD' => 1,
                    'EUR' => 0.85,
                    'GBP' => 0.75,
                ]
            ]);

        // Mock saving the exchange rate
        $this->exchangeRateRepository->shouldReceive('create')->once();

        // Run the command and assert expected output
        $this->artisan('currency:sync-historical')
            ->expectsOutput('Fetching historical rates for ' . Carbon::today()->subDays(1)->toDateString())
            ->expectsOutput('Synced rates for ' . Carbon::today()->subDays(1)->toDateString())
            ->assertExitCode(0);
    }

    public function it_does_not_sync_if_rates_are_already_synced_today()
    {
        // Mock the repository methods
        $baseCurrency = new Currency(['code' => 'USD']);
        $this->currencyRepository->shouldReceive('getBaseCurrency')->andReturn($baseCurrency);

        // Mock the historical exchange rate repository
        $this->exchangeRateRepository->shouldReceive('getLastSyncedDate')
            ->once()
            ->andReturn(now()->startOfDay()); // Simulate that the rates are already synced today

        // Run the command
        $this->artisan('currency:sync-historical')
            ->expectsOutput('Historical rates already synced for today.')
            ->assertExitCode(0);
    }

    public function it_handles_api_failure_gracefully()
    {
        // Mock the repository methods
        $baseCurrency = new Currency(['code' => 'USD']);
        $this->currencyRepository->shouldReceive('getBaseCurrency')->andReturn($baseCurrency);

        // Mock API failure
        $this->currencyAPIService->shouldReceive('getHistoricalRates')
            ->once()
            ->andReturn(null); // Simulate a failed API response

        // Run the command
        $this->artisan('currency:sync-historical')
            ->expectsOutput('Failed to fetch historical rates for ' . now()->toDateString())
            ->assertExitCode(1);
    }

    protected function setUp(): void
    {
        parent::setUp();

        // Mock dependencies
        $this->currencyRepository = Mockery::mock(CurrencyRepository::class);
        $this->currencyAPIService = Mockery::mock(CurrencyAPIService::class);
        $this->exchangeRateRepository = Mockery::mock(HistoricalExchangeRateRepository::class);

        // Create the command instance
        $this->command = new \App\Console\Commands\SyncHistoricalRates(
            $this->currencyRepository,
            $this->currencyAPIService,
            $this->exchangeRateRepository
        );
    }
}
