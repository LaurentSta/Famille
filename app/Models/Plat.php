<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Plat extends Model
{
    protected $table = 'dishes';
    protected $fillable = [
        'family_id',
        'name',
        'type',
        'cuisine_origin',
        'low_carb',
        'regime',
        'gluten_free',
        'dessert_suggestion',
        'notes',
    ];

    protected $casts = [
        'low_carb' => 'boolean',
        'gluten_free' => 'boolean',
    ];

    public function famille(): BelongsTo
    {
        return $this->belongsTo(Famille::class, 'family_id');
    }

    public function ingredients(): BelongsToMany
    {
        return $this->belongsToMany(Ingredient::class, 'dish_ingredient', 'dish_id', 'ingredient_id');
    }

    public function getEmojiAttribute(): string
    {
        return config("emoji.dish_types.{$this->type}", config('emoji.dish_type_default'));
    }

    public function getOriginEmojiAttribute(): ?string
    {
        return config("emoji.dish_origins.{$this->cuisine_origin}");
    }

    public function getRegimeEmojiAttribute(): ?string
    {
        return config("emoji.dish_regimes.{$this->regime}");
    }
}
