<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Investment extends Model
{
    protected $fillable = [
        'user_id', 'name', 'type', 'bank_name', 'term_months',
        'interest_rate', 'shares', 'avg_cost',
        'currency', 'exchange_rate', 'amount_invested_foreign',
        'amount_invested', 'current_value', 'current_value_foreign',
        'purchase_date', 'maturity_date',
        'notes', 'target_id', 'status', 'sold_date',
    ];

    protected function casts(): array
    {
        return [
            'shares' => 'decimal:4',
            'avg_cost' => 'decimal:2',
            'amount_invested' => 'decimal:2',
            'amount_invested_foreign' => 'decimal:2',
            'current_value' => 'decimal:2',
            'current_value_foreign' => 'decimal:2',
            'exchange_rate' => 'decimal:2',
            'interest_rate' => 'decimal:2',
            'term_months' => 'integer',
            'purchase_date' => 'date',
            'maturity_date' => 'date',
            'sold_date' => 'date',
        ];
    }

    public function target(): BelongsTo
    {
        return $this->belongsTo(FinancialTarget::class, 'target_id');
    }

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopeSold($query)
    {
        return $query->where('status', 'sold');
    }

    public function getReturnPerShareAttribute(): ?float
    {
        if (!$this->shares || $this->shares == 0 || !$this->avg_cost) return null;
        $currentPerShare = $this->current_value / $this->shares;
        return round($currentPerShare - $this->avg_cost, 2);
    }

    public function getEstimatedInterestAttribute(): ?float
    {
        if (!$this->amount_invested || !$this->interest_rate || !$this->term_months) return null;
        return round($this->amount_invested * ($this->interest_rate / 100) * ($this->term_months / 12), 2);
    }

    public function getMaturityValueAttribute(): ?float
    {
        if (!$this->amount_invested) return null;
        return round($this->amount_invested + ($this->estimated_interest ?? 0), 2);
    }

    public function isTermDeposit(): bool
    {
        return $this->type === 'term_deposit';
    }

    public function isSavings(): bool
    {
        return $this->type === 'savings';
    }

    public function getSavingsCurrentValueAttribute(): ?float
    {
        if (!$this->isSavings() || !$this->amount_invested || !$this->interest_rate || !$this->purchase_date) {
            return null;
        }
        $principal = (float) $this->amount_invested;
        $rate = (float) $this->interest_rate;
        $daysSinceDeposit = now()->diffInDays($this->purchase_date);
        $grossInterest = $principal * ($rate / 100) * ($daysSinceDeposit / 365);
        // 20% tax on interest (80% net)
        return round($principal + ($grossInterest * 0.8), 2);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function getProfitLossAttribute(): float
    {
        return $this->current_value - $this->amount_invested;
    }

    public function getReturnPercentageAttribute(): float
    {
        if ($this->amount_invested == 0) {
            return 0;
        }
        return round(($this->profit_loss / $this->amount_invested) * 100, 2);
    }
}
