<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Family extends Model
{
    protected $fillable = [
        'name',
        'code',
    ];

    public static function findOrCreateByCode(string $code): self
    {
        $normalized = strtoupper(trim($code));

        return static::firstOrCreate(
            ['code' => $normalized],
            ['name' => $normalized],
        );
    }

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    public function dishes(): HasMany
    {
        return $this->hasMany(Dish::class);
    }

    public function plannedMeals(): HasMany
    {
        return $this->hasMany(PlannedMeal::class);
    }

    public function shoppingListOverrides(): HasMany
    {
        return $this->hasMany(ShoppingListOverride::class);
    }

    public function ingredientStocks(): HasMany
    {
        return $this->hasMany(IngredientStock::class);
    }
}
