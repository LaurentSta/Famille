<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Ingredient extends Model
{
    protected $fillable = [
        'name',
        'category',
    ];

    public function dishes(): BelongsToMany
    {
        return $this->belongsToMany(Dish::class);
    }
}
