<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class CurrencyAPIService
{
    /**
     * @var string
     */
    protected $apiKey;

    /**
     * @var string
     */
    protected $baseUrl;

    public function __construct()
    {
        $this->apiKey = config('services.openexchangerates.key');
        $this->baseUrl = 'https://openexchangerates.org/api/';
    }

    /**
     * @param array $symbols
     * @return JsonResponse|null
     */
    public function getLatestRates(array $symbols)
    {
        return $this->fetchRates('latest.json', $symbols);
    }

    /**
     * @param string $endpoint
     * @param array $symbols
     * @return JsonResponse|null
     */
    protected function fetchRates(string $endpoint, array $symbols)
    {
        $response = Http::get($this->baseUrl . $endpoint, [
            'app_id' => $this->apiKey,
            'symbols' => implode(',', $symbols),
        ]);

        return $response->successful() ? $response->json() : null;
    }

    /**
     * @param string $date
     * @param array $symbols
     * @return JsonResponse|null
     */
    public function getHistoricalRates(string $date, array $symbols)
    {
        return $this->fetchRates("historical/{$date}.json", $symbols);
    }
}
