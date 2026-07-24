<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

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

    public function stocks(): HasMany
    {
        return $this->hasMany(IngredientStock::class);
    }

    public function getEmojiAttribute(): string
    {
        $map = config('emoji.ingredients');

        if (isset($map[$this->name])) {
            return $map[$this->name];
        }

        return config("emoji.ingredient_category_default.{$this->category}", config('emoji.ingredient_default'));
    }
}
