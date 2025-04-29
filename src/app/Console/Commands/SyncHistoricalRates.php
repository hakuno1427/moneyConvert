<?php

namespace App\Console\Commands;

use App\Models\HistoricalExchangeRate;
use App\Repositories\CurrencyRepository;
use App\Repositories\HistoricalExchangeRateRepository;
use App\Repositories\HistoricalRateRepository;
use App\Services\CurrencyAPIService;
use Carbon\Carbon;
use Illuminate\Console\Command;

class SyncHistoricalRates extends Command
{
    protected $signature = 'currency:sync-historical';
    protected $description = 'Sync historical currency rates for the base currency daily';


    /**
     * @var CurrencyRepository
     */
    protected $currencyRepository;

    /**
     * @var CurrencyAPIService
     */
    protected $currencyAPIService;

    /**
     * @var HistoricalExchangeRateRepository
     */
    protected $exchangeRateRepository;

    /**
     * @param CurrencyRepository $currencyRepository
     * @param CurrencyAPIService $currencyAPIService
     * @param HistoricalExchangeRateRepository $exchangeRateRepository
     */
    public function __construct(
        CurrencyRepository               $currencyRepository,
        CurrencyAPIService               $currencyAPIService,
        HistoricalExchangeRateRepository $exchangeRateRepository
    )
    {
        parent::__construct();

        $this->currencyRepository = $currencyRepository;
        $this->currencyAPIService = $currencyAPIService;
        $this->exchangeRateRepository = $exchangeRateRepository;
    }

    /**
     * @return int
     */
    public function handle()
    {
        $baseCurrency = $this->currencyRepository->getBaseCurrency();

        if ($baseCurrency == null) {
            $this->info('Base currency is not set');
            return 0;
        }

        $today = now()->startOfDay();

        $lastSyncedDate = $this->exchangeRateRepository->getLastSyncedDate($baseCurrency->code)
            ?? $today->copy()->subDays(HistoricalExchangeRate::DAYS_TO_COMPARE);

        if ($lastSyncedDate->equalTo($today)) {
            $this->info('Historical rates already synced for today.');
            return 0;
        }

        $currencies = $this->currencyRepository->getAll();
        $currency_codes = $currencies->pluck('code')->toArray();

        $currentDate = $lastSyncedDate->copy()->addDay();

        while ($currentDate->lte($today)) {
            $dateString = $currentDate->toDateString();

            $this->info('Fetching historical rates for ' . $dateString);

            $historicalRates = $this->currencyAPIService->getHistoricalRates($dateString, $currency_codes)['rates'] ?? null;

            if (!$historicalRates) {
                $this->error('Failed to fetch historical rates for ' . $dateString);
                $currentDate->addDay();
                continue;
            }

            $usdToBase = $historicalRates[$baseCurrency->code];

            foreach ($currencies as $toCurrency) {
                if ($toCurrency->code === $baseCurrency->code) {
                    continue;
                }

                $rate = $historicalRates[$toCurrency->code] / $usdToBase;

                $this->exchangeRateRepository->create([
                    'from_code' => $baseCurrency->code,
                    'to_code' => $toCurrency->code,
                    'rate' => $rate,
                    'date' => $dateString,
                ]);
            }

            $this->info('Synced rates for ' . $dateString);

            $currentDate->addDay();
        }

        $this->info('Historical rate sync complete.');

        return 0;
    }

}
