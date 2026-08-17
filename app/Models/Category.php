<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Category extends Model
{
    protected $fillable = ['user_id', 'name', 'type', 'icon', 'color'];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(Transaction::class);
    }

    public function budgets(): HasMany
    {
        return $this->hasMany(Budget::class);
    }

    /**
     * Find an existing category for the user or create it on the fly.
     * Used by the API so clients only need to send a category name.
     */
    public static function findOrCreateForUser(User $user, string $type, string $name): self
    {
        $name = trim($name);

        return static::firstOrCreate(
            [
                'user_id' => $user->id,
                'type' => $type,
                'name' => $name,
            ],
            [
                'icon' => '🏷️',
                'color' => static::defaultColor($name),
            ]
        );
    }

    /**
     * Pick a deterministic default color for auto-created categories.
     */
    private static function defaultColor(string $name): string
    {
        $palette = ['#6750A4', '#00696D', '#7D5260', '#386A20', '#8B5000', '#B3261E', '#4F378B', '#006A6A', '#5B6E00', '#00639B'];

        return $palette[crc32($name) % count($palette)];
    }
}
