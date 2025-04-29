<?php

namespace App\Http\Controllers;

use App\Services\CurrencyService;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;

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
     * @return View|JsonResponse
     */
    public function index()
    {
        try {
            $currencies = $this->currencyService->getAllCurrencies();
            $historicalRates = $this->getHistoricalRates();
            return view('index', compact('currencies', 'historicalRates'));
        } catch (Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * @return array|null
     * @throws Exception
     */
    public function getHistoricalRates(): ?array
    {
        return $this->currencyService->getHistoricalExchangeRates();
    }

    /**
     * @return JsonResponse
     * @throws Exception
     */
    public function getRates()
    {
        return response()->json($this->currencyService->getRates());
    }
}
