<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PlannedMeal extends Model
{
    protected $fillable = [
        'date',
        'meal_slot',
        'course',
        'position',
        'dish_id',
    ];

    protected $casts = [
        'date' => 'date:Y-m-d',
    ];

    public function dish(): BelongsTo
    {
        return $this->belongsTo(Dish::class);
    }
}
