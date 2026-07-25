<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ModificationListeCourses extends Model
{
    protected $table = 'shopping_list_overrides';
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

    public function famille(): BelongsTo
    {
        return $this->belongsTo(Famille::class, 'family_id');
    }

    public function ingredient(): BelongsTo
    {
        return $this->belongsTo(Ingredient::class);
    }
}
