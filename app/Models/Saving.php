<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Saving extends Model
{
    protected $fillable = [
        'user_id', 'name', 'amount_invested', 'interest_rate',
        'currency', 'exchange_rate', 'amount_invested_foreign',
        'purchase_date', 'current_value', 'notes', 'target_id',
    ];

    protected function casts(): array
    {
        return [
            'amount_invested' => 'decimal:2',
            'interest_rate' => 'decimal:2',
            'current_value' => 'decimal:2',
            'exchange_rate' => 'decimal:2',
            'amount_invested_foreign' => 'decimal:2',
            'purchase_date' => 'date',
        ];
    }

    public function target(): BelongsTo
    {
        return $this->belongsTo(FinancialTarget::class, 'target_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
