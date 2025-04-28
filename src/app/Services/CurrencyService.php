<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;

class CurrencyService
{
    protected $apiKey;
    protected $baseUrl;

    public function __construct()
    {
        $this->apiKey = config('services.openexchangerates.key');
        $this->baseUrl = 'https://openexchangerates.org/api/';
    }

    public function getLatestRates(array $symbols)
    {
        $response = Http::get($this->baseUrl . 'latest.json', [
            'app_id' => $this->apiKey,
            'symbols' => implode(',', $symbols),
        ]);

        return $response->successful() ? $response->json() : null;
    }
}
