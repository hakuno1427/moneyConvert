<?php

namespace App\Models;

use Carbon\Carbon;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class HistoricalExchangeRate extends Model
{
    /**
     * /** @use HasFactory<UserFactory>
     */
    use HasFactory;

    const DAYS_TO_COMPARE = 14;

    /**
     * @var bool
     */
    public $timestamps = false;

    /**
     * @var string[]
     */
    protected $fillable = [
        'from_code',
        'to_code',
        'rate',
        'date',
    ];

    /**
     * @var string[]
     */
    protected $casts = [
        'rate' => 'decimal:8',
        'date' => 'date',
    ];

    /**
     * @return BelongsTo
     */
    public function fromCurrency(): BelongsTo
    {
        return $this->belongsTo(Currency::class, 'from_code', 'code');
    }

    /**
     * @return BelongsTo
     */
    public function toCurrency(): BelongsTo
    {
        return $this->belongsTo(Currency::class, 'to_code', 'code');
    }
}
