<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Famille extends Model
{
    protected $table = 'families';
    protected $fillable = [
        'name',
        'code',
    ];

    public static function trouverOuCreerParCode(string $code): self
    {
        $normalized = strtoupper(trim($code));

        return static::firstOrCreate(
            ['code' => $normalized],
            ['name' => $normalized],
        );
    }

    public function users(): HasMany
    {
        return $this->hasMany(User::class, 'family_id');
    }

    public function plats(): HasMany
    {
        return $this->hasMany(Plat::class, 'family_id');
    }

    public function repasPlanifies(): HasMany
    {
        return $this->hasMany(RepasPlanifie::class, 'family_id');
    }

    public function modificationsListeCourses(): HasMany
    {
        return $this->hasMany(ModificationListeCourses::class, 'family_id');
    }

    public function stocksIngredients(): HasMany
    {
        return $this->hasMany(StockIngredient::class, 'family_id');
    }
}
