<?php

namespace App\Repositories;

use App\Models\HistoricalExchangeRate;
use Carbon\Carbon;

class HistoricalExchangeRateRepository implements HistoricalExchangeRateRepositoryInterface
{
    /**
     * @param array $data
     * @return HistoricalExchangeRate
     */
    public function create(array $data): HistoricalExchangeRate
    {
        return HistoricalExchangeRate::create($data);
    }

    /**
     * @param array $attributes
     * @param array $values
     * @return HistoricalExchangeRate
     */
    public function updateOrCreate(array $attributes, array $values = []): HistoricalExchangeRate
    {
        return HistoricalExchangeRate::updateOrCreate($attributes, $values);
    }

    /**
     * @param $fromCode
     * @return Carbon|null
     */
    public function getLastSyncedDate($fromCode): ?Carbon
    {
        $lastRecord = HistoricalExchangeRate::where('from_code', $fromCode)
            ->orderBy('date', 'desc')
            ->first();

        return $lastRecord ? Carbon::parse($lastRecord->date) : null;
    }
}
