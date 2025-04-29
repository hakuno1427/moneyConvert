<?php

namespace App\Repositories;

use App\Models\Currency;
use Illuminate\Database\Eloquent\Collection;

class CurrencyRepository implements CurrencyRepositoryInterface
{
    /**
     * @return Collection<Currency>
     */
    public function getAll(): Collection
    {
        return Currency::all();
    }

    /**
     * @return Currency|null
     */
    public function getBaseCurrency(): ?Currency
    {
        return Currency::where('is_base', true)->first();
    }

    /**
     * @param string $code
     * @return Currency|null
     */
    public function findByCode(string $code): ?Currency
    {
        return Currency::where('code', $code)->first();
    }

    /**
     * @param array $data
     * @return Currency
     */
    public function create(array $data): Currency
    {
        if (!empty($data['is_base']) && $data['is_base']) {
            Currency::where('is_base', true)->update(['is_base' => false]);
        }
        return Currency::create($data);
    }

    /**
     * @param Currency $currency
     * @param array $data
     * @return Currency
     */
    public function update(Currency $currency, array $data): Currency
    {
        if (!empty($data['is_base']) && $data['is_base']) {
            Currency::where('is_base', true)->where('id', '!=', $currency->id)->update(['is_base' => false]);
        }
        $currency->update($data);
        return $currency;
    }
}
