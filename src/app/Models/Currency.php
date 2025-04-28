<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Currency extends Model
{
    protected $primaryKey = 'code';
    public $incrementing = false; // Important! No auto-increment
    protected $keyType = 'string'; // Primary key is a string

    protected $fillable = ['code', 'country_code', 'symbol', 'is_base'];

    protected static function booted()
    {
        static::saving(function ($currency) {
            if ($currency->is_base) {
                Currency::where('code', '!=', $currency->code)->update(['is_base' => false]);
            }
        });
    }
}
