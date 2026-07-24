<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PlannedMeal extends Model
{
    protected $fillable = [
        'date',
        'meal_slot',
        'dish_id',
    ];

    protected $casts = [
        'date' => 'date',
    ];

    public function dish(): BelongsTo
    {
        return $this->belongsTo(Dish::class);
    }
}
