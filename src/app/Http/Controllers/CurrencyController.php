<?php

namespace App\Http\Controllers;

use App\Services\CurrencyService;
use Illuminate\Http\Request;

class CurrencyController extends Controller
{
    protected $currencyService;
    protected $currencies;
    protected $baseCurrency;

    public function __construct(CurrencyService $currencyService)
    {
        $this->currencyService = $currencyService;
        $this->currencies = config('currencies.targets');
        $this->baseCurrency = config('currencies.base');
    }

    public function index()
    {
        return view('index', [
            'currencies' => $this->currencies,
        ]);
    }

    public function getLatest()
    {
        $currencySymbol = array_column($this->currencies, 'currency');

        $rates = $this->currencyService->getLatestRates($currencySymbol, $this->baseCurrency)['rates'];

        if (!$rates) {
            return response()->json(['error' => 'Unable to fetch rates.'], 500);
        }

        return response()->json($rates);
    }
}
