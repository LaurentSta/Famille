<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StockIngredient extends Model
{
    protected $table = 'ingredient_stocks';
    protected $fillable = [
        'family_id',
        'ingredient_id',
        'in_stock',
    ];

    protected $casts = [
        'in_stock' => 'boolean',
    ];

    public function famille(): BelongsTo
    {
        return $this->belongsTo(Famille::class, 'family_id');
    }

    public function ingredient(): BelongsTo
    {
        return $this->belongsTo(Ingredient::class);
    }
}
