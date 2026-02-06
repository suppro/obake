<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WordStudyProgress extends Model
{
    protected $table = 'word_study_progress';

    protected $fillable = [
        'user_id',
        'word_id',
        'started_at',
        'last_reviewed_at',
        'days_studied',
        'level',
        'next_review_at',
        'is_completed',
        'completed_at',
    ];

    protected $casts = [
        'started_at' => 'date',
        'last_reviewed_at' => 'date',
        'completed_at' => 'date',
        'is_completed' => 'boolean',
        'days_studied' => 'integer',
        'level' => 'integer',
        'next_review_at' => 'datetime',
    ];

    /**
     * Пользователь
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Слово
     */
    public function word(): BelongsTo
    {
        return $this->belongsTo(GlobalDictionary::class, 'word_id');
    }
}
