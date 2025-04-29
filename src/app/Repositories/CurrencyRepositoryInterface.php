<?php

namespace App\Repositories;

use App\Models\Currency;
use Illuminate\Database\Eloquent\Collection;

interface CurrencyRepositoryInterface
{
    /**
     * @return Collection<Currency>
     */
    public function getAll(): Collection;

    /**
     * @return Currency|null
     */
    public function getBaseCurrency(): ?Currency;

    /**
     * @param string $code
     * @return Currency|null
     */
    public function findByCode(string $code): ?Currency;

    /**
     * @param array $data
     * @return Currency
     */
    public function create(array $data): Currency;

    /**
     * @param Currency $currency
     * @param array $data
     * @return Currency
     */
    public function update(Currency $currency, array $data): Currency;
}
