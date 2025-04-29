<?php

namespace App\Repositories;
use App\Models\HistoricalExchangeRate;
use Carbon\Carbon;

interface HistoricalExchangeRateRepositoryInterface
{
    /**
     * @param array $data
     * @return HistoricalExchangeRate
     */
    public function create(array $data): HistoricalExchangeRate;

    /**
     * @param array $attributes
     * @param array $values
     * @return HistoricalExchangeRate
     */
    public function updateOrCreate(array $attributes, array $values = []): HistoricalExchangeRate;

    /**
     * @param $fromCode
     * @return Carbon|null
     */
    public function getLastSyncedDate($fromCode): ?Carbon;
}
