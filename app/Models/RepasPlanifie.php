<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RepasPlanifie extends Model
{
    protected $table = 'planned_meals';
    protected $fillable = [
        'family_id',
        'date',
        'meal_slot',
        'course',
        'position',
        'dish_id',
    ];

    protected $casts = [
        'date' => 'date:Y-m-d',
    ];

    public function famille(): BelongsTo
    {
        return $this->belongsTo(Famille::class, 'family_id');
    }

    public function plat(): BelongsTo
    {
        return $this->belongsTo(Plat::class, 'dish_id');
    }
}
