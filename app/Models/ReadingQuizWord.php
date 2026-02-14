<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ReadingQuizWord extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
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
     * Отношение к пользователю
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Получить списки где находится это слово
     */
    public function lists()
    {
        return $this->belongsToMany(ReadingQuizList::class, 'reading_quiz_list_items', 'word_id', 'list_id')
            ->where('reading_quiz_list_items.word_type', 'reading_quiz_word');
    }
}
