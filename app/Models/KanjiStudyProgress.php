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
        'next_review_at',
        'is_completed',
        'is_selected_for_study',
    ];

    protected $casts = [
        'last_reviewed_at' => 'datetime',
        'next_review_at' => 'datetime',
        'is_completed' => 'boolean',
        'is_selected_for_study' => 'boolean',
    ];

    /**
     * Пользователь, изучающий кандзи
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
