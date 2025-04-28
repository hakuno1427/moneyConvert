<?php

namespace App\Http\Controllers;

use App\Services\CurrencyService;
use Illuminate\Http\Request;
use App\Repositories\CurrencyRepository;

class CurrencyController extends Controller
{
    protected $currencyRepo;
    protected $currencyService;
    protected $currencies;

    public function __construct(CurrencyRepository $currencyRepo, CurrencyService $currencyService)
    {
        $this->currencyRepo = $currencyRepo;
        $this->currencies = $currencyRepo->getAll();
        $this->currencyService = $currencyService;
    }

    public function index()
    {
        $currencies = $this->currencies;
        return view('index', compact('currencies'));
    }

    protected function getLatestUSDRates()
    {
        $currencies = $this->currencies;
        $currency_codes = $currencies->pluck('code')->toArray();

        $rates = $this->currencyService->getLatestRates($currency_codes)['rates'];

        if (!$rates) {
            return response()->json(['error' => 'Unable to fetch rates.'], 500);
        }

        return $rates;
    }

    public function getRates()
    {
        $USDRates = $this->getLatestUSDRates();

        $usdToBase = $USDRates[$this->currencyRepo->getBaseCurrency()->code];

        $conversionRate = [];
        foreach ($this->currencies as $currency) {

            if ($currency->code == $usdToBase) {
                continue;
            }
            $conversionRate[$currency->code] = $USDRates[$currency->code] / $usdToBase;
        }

        return response()->json($conversionRate);
    }
}
