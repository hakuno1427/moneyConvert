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

    public function getLatestRates(array $symbols, string $base = 'USD')
    {
        return Cache::remember("latest_rates_{$base}", 300, function () use ($symbols, $base) {
            $response = Http::get($this->baseUrl . 'latest.json', [
                'app_id' => $this->apiKey,
                'base' => $base,
                'symbols' => implode(',', $symbols),
            ]);

            return $response->successful() ? $response->json() : null;
        });
    }
}
