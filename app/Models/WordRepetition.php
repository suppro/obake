<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WordRepetition extends Model
{
    protected $table = 'word_repetitions';

    protected $fillable = [
        'user_id',
        'word_id',
        'repetition_date',
        'direction',
        'is_correct',
        'user_answer',
    ];

    protected $casts = [
        'repetition_date' => 'date',
        'is_correct' => 'boolean',
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
