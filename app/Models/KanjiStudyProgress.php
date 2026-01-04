<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class KanjiStudyProgress extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'kanji',
        'translation_ru',
        'level',
        'last_reviewed_at',
        'is_completed',
    ];

    protected $casts = [
        'last_reviewed_at' => 'datetime',
        'is_completed' => 'boolean',
    ];

    /**
     * Пользователь, изучающий кандзи
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
