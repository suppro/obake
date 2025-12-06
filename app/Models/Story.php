<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Story extends Model
{
    protected $fillable = [
        'title',
        'content',
        'level',
        'description',
        'is_active',
        'audio_path',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    /**
     * Слова из словаря, используемые в рассказе
     */
    public function words(): BelongsToMany
    {
        return $this->belongsToMany(GlobalDictionary::class, 'story_words', 'story_id', 'word_id')
            ->withTimestamps();
    }

    /**
     * Пользователи, которые прочитали этот рассказ
     */
    public function readers(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'read_stories', 'story_id', 'user_id')
            ->withPivot('read_at')
            ->withTimestamps();
    }
}
