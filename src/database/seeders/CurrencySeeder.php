<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

use App\Models\Currency;

class CurrencySeeder extends Seeder
{
    public function run()
    {
        $currencies = [
            ['code' => 'AUD', 'country_code' => 'au', 'symbol' => '$', 'is_base' => 1],
            ['code' => 'USD', 'country_code' => 'us', 'symbol' => '$', 'is_base' => 0],
            ['code' => 'EUR', 'country_code' => 'eu', 'symbol' => '€', 'is_base' => 0],
            ['code' => 'GBP', 'country_code' => 'gb', 'symbol' => '£', 'is_base' => 0],
            ['code' => 'NZD', 'country_code' => 'nz', 'symbol' => '$', 'is_base' => 0],
            ['code' => 'CAD', 'country_code' => 'ca', 'symbol' => '$', 'is_base' => 0],
        ];

        foreach ($currencies as $currency) {
            Currency::create($currency);
        }
    }
}

