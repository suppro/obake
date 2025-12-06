<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class GlobalDictionary extends Model
{
    protected $table = 'global_dictionary';

    protected $fillable = [
        'japanese_word',
        'reading',
        'translation_ru',
        'translation_en',
        'word_type',
        'example_ru',
        'example_jp',
        'audio_path',
    ];

    /**
     * Пользователи, у которых это слово в словаре
     */
    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'user_dictionary', 'word_id', 'user_id')
            ->withTimestamps();
    }

    /**
     * Рассказы, в которых используется это слово
     */
    public function stories(): BelongsToMany
    {
        return $this->belongsToMany(Story::class, 'story_words', 'word_id', 'story_id')
            ->withTimestamps();
    }

    /**
     * Прогресс изучения этого слова пользователями
     */
    public function studyProgress()
    {
        return $this->hasMany(\App\Models\WordStudyProgress::class, 'word_id');
    }

    /**
     * История повторений этого слова
     */
    public function repetitions()
    {
        return $this->hasMany(\App\Models\WordRepetition::class, 'word_id');
    }
}
