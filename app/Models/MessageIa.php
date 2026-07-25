<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MessageIa extends Model
{
    protected $table = 'ai_messages';
    protected $fillable = [
        'family_id',
        'role',
        'content',
        'dish',
        'added',
    ];

    protected $casts = [
        'dish' => 'array',
        'added' => 'boolean',
    ];

    public function famille(): BelongsTo
    {
        return $this->belongsTo(Famille::class, 'family_id');
    }
}
