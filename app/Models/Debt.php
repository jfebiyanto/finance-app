<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Debt extends Model
{
    protected $fillable = [
        'user_id', 'name', 'total_amount', 'principal_amount', 'remaining_amount',
        'interest_rate', 'payment_term', 'term_count', 'term_amount',
        'due_date', 'status', 'notes',
    ];

    protected function casts(): array
    {
        return [
            'total_amount' => 'decimal:2',
            'principal_amount' => 'decimal:2',
            'remaining_amount' => 'decimal:2',
            'interest_rate' => 'decimal:2',
            'term_amount' => 'decimal:2',
            'term_count' => 'integer',
            'due_date' => 'date',
        ];
    }

    public function generateSchedule(): array
    {
        if (!$this->payment_term || !$this->term_count || !$this->term_amount) {
            return [];
        }

        $schedule = [];
        $startDate = $this->due_date ? Carbon::parse($this->due_date) : now();

        for ($i = 1; $i <= $this->term_count; $i++) {
            $dueDate = match ($this->payment_term) {
                'weekly' => $startDate->copy()->addWeeks($i - 1),
                'biweekly' => $startDate->copy()->addWeeks(($i - 1) * 2),
                'monthly' => $startDate->copy()->addMonthsNoOverflow($i - 1),
                'yearly' => $startDate->copy()->addYearsNoOverflow($i - 1),
                default => $startDate->copy()->addMonthsNoOverflow($i - 1),
            };
            $schedule[] = [
                'term' => $i,
                'due_date' => $dueDate->format('Y-m-d'),
                'amount' => $this->term_amount,
                'paid' => $this->payments()
                    ->whereDate('payment_date', $dueDate->format('Y-m-d'))
                    ->sum('amount') >= $this->term_amount,
            ];
        }

        return $schedule;
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(DebtPayment::class);
    }
}
