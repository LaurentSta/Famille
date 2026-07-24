<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Dish extends Model
{
    protected $fillable = [
        'name',
        'type',
        'low_carb',
        'dessert_suggestion',
        'notes',
    ];

    protected $casts = [
        'low_carb' => 'boolean',
    ];

    public function ingredients(): BelongsToMany
    {
        return $this->belongsToMany(Ingredient::class);
    }
}
