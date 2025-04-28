<?php

namespace App\Repositories;

use App\Models\Currency;

class CurrencyRepository
{
    public function getAll()
    {
        return Currency::all();
    }

    public function getBaseCurrency()
    {
        return Currency::where('is_base', true)->first();
    }

    public function findByCode(string $code)
    {
        return Currency::where('code', $code)->first();
    }

    public function create(array $data)
    {
        if (!empty($data['is_base']) && $data['is_base']) {
            Currency::where('is_base', true)->update(['is_base' => false]);
        }
        return Currency::create($data);
    }

    public function update(Currency $currency, array $data)
    {
        if (!empty($data['is_base']) && $data['is_base']) {
            Currency::where('is_base', true)->where('id', '!=', $currency->id)->update(['is_base' => false]);
        }
        $currency->update($data);
        return $currency;
    }
}
