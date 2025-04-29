<?php

namespace App\Services;

use App\Models\Currency;
use App\Models\HistoricalExchangeRate;
use Illuminate\Database\Eloquent\Collection;
use App\Repositories\CurrencyRepositoryInterface;
use App\Repositories\HistoricalExchangeRateRepositoryInterface;
use Illuminate\Http\JsonResponse;

class CurrencyService
{

    /**
     * @var CurrencyRepositoryInterface
     */
    protected $currencyRepository;

    /**
     * @var HistoricalExchangeRateRepositoryInterface
     */
    protected $exchangeRateRepository;

    /**
     * @var CurrencyAPIService
     */
    protected $currencyApiService;

    /**
     * @var Collection<Currency>|null
     */
    protected $currencies = null;


    /**
     * @var array|null
     */
    protected $historicalExchangeRates = null;

    /**
     * @param CurrencyRepositoryInterface $currencyRepository
     * @param HistoricalExchangeRateRepositoryInterface $exchangeRateRepository
     * @param CurrencyAPIService $currencyAPIService
     */
    public function __construct(
        CurrencyRepositoryInterface $currencyRepository,
        HistoricalExchangeRateRepositoryInterface $exchangeRateRepository,
        CurrencyAPIService $currencyAPIService
    )
    {
        $this->currencyRepository = $currencyRepository;
        $this->exchangeRateRepository = $exchangeRateRepository;
        $this->currencyApiService = $currencyAPIService;
    }

    /**
     * @return Collection<Currency>
     */
    public function getAllCurrencies(): Collection
    {
        if ($this->currencies !== null) {
            return $this->currencies;
        }

        $this->currencies = $this->currencyRepository->getAll();

        return $this->currencies;
    }

    /**
     * @return mixed
     */
    public function getRates()
    {
        $USDRates = $this->getLatestUSDRates();

        $usdToBase = $USDRates[$this->currencyRepository->getBaseCurrency()->code];

        $conversionRate = [];
        foreach ($this->currencies as $currency) {

            if ($currency->code == $usdToBase) {
                continue;
            }
            $conversionRate[$currency->code] = $USDRates[$currency->code] / $usdToBase;
        }

        return response()->json($conversionRate);
    }


    /**
     * @return JsonResponse|array
     */
    protected function getLatestUSDRates()
    {
        $currencies = $this->getAllCurrencies();
        $currency_codes = $currencies->pluck('code')->toArray();

        $rates = $this->currencyApiService->getLatestRates($currency_codes)['rates'];

        if (!$rates) {
            return response()->json(['error' => 'Unable to fetch rates.'], 500);
        }

        return $rates;
    }

    /**
     * @return array|null
     */
    public function getHistoricalExchangeRates(): ?array
    {
        if ($this->historicalExchangeRates !== null) {
            return $this->historicalExchangeRates;
        }

        $baseCurrency = $this->currencyRepository->getBaseCurrency()->code;

        $historicalExchangeRates = HistoricalExchangeRate::where('from_code', $baseCurrency)
            ->whereDate('date', '>=', now()->subDays(14))
            ->orderBy('date')
            ->get();

        $groupedRates = $historicalExchangeRates->groupBy('to_code');

        $formattedRates = [];

        foreach ($groupedRates as $currencyCode => $rates) {
            $formattedRates[$currencyCode] = $rates->map(function ($rate) {
                return [
                    'date' => $rate->date->format('Y-m-d'),
                    'rate' => $rate->rate,
                ];
            })->toArray();
        }

        $this->historicalExchangeRates = $formattedRates;

        return $this->historicalExchangeRates;
    }
}
