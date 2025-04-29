<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Currency extends Model
{
    /**
     * @var bool
     */
    public $incrementing = false;

    /**
     * @var string
     */
    protected $primaryKey = 'code';

    /**
     * @var string
     */
    protected $keyType = 'string';

    /**
     * @var string[]
     */
    protected $fillable = ['code', 'country_code', 'symbol', 'is_base'];

    /**
     * Disable is_base in other currency when a new base currency is saved
     * @return void
     */
    protected static function booted()
    {
        static::saving(function ($currency) {
            if ($currency->is_base) {
                Currency::where('code', '!=', $currency->code)->update(['is_base' => false]);
            }
        });
    }
}
