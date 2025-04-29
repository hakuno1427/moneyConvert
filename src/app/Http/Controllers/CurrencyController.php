<?php

namespace App\Http\Controllers;

use App\Services\CurrencyService;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

class CurrencyController extends Controller
{

    /**
     * @var CurrencyService
     */
    protected $currencyService;

    /**
     * @param CurrencyService $currencyService
     */
    public function __construct(
        CurrencyService $currencyService,
    )
    {
        $this->currencyService = $currencyService;
    }

    /**
     * @return \Illuminate\View\View
     */
    public function index()
    {
        $currencies = $this->currencyService->getAllCurrencies();
        $historicalRates = $this->getHistoricalRates();
        return view('index', compact('currencies', 'historicalRates'));
    }

    /**
     * @return array|null
     */
    public function getHistoricalRates(): ?array
    {
        return $this->currencyService->getHistoricalExchangeRates();
    }

    /**
     * @return JsonResponse
     */
    public function getRates()
    {
        return $this->currencyService->getRates();
    }
}
