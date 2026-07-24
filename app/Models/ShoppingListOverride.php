<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ShoppingListOverride extends Model
{
    protected $fillable = [
        'family_id',
        'month',
        'ingredient_id',
        'included',
    ];

    protected $casts = [
        'month' => 'date:Y-m-d',
        'included' => 'boolean',
    ];

    public function family(): BelongsTo
    {
        return $this->belongsTo(Family::class);
    }

    public function ingredient(): BelongsTo
    {
        return $this->belongsTo(Ingredient::class);
    }
}
