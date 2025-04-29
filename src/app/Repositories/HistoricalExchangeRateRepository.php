<?php

namespace App\Repositories;

use App\Models\HistoricalExchangeRate;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;

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


    /**
     * @param string $baseCurrency
     * @return Collection
     */
    public function getRatesFromBaseCurrency(string $baseCurrency): Collection
    {
        return HistoricalExchangeRate::where('from_code', $baseCurrency)
            ->whereDate('date', '>=', now()->subDays(HistoricalExchangeRate::DAYS_TO_COMPARE))
            ->orderBy('date')
            ->get();
    }
}
