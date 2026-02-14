<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ReadingQuizList extends Model
{
    protected $table = 'reading_quiz_lists';

    protected $fillable = [
        'user_id',
        'name',
        'description',
        'word_count',
        'repetitions_completed',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Владелец списка
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Элементы списка (слова)
     */
    public function items(): HasMany
    {
        return $this->hasMany(ReadingQuizListItem::class, 'list_id');
    }

    /**
     * Получить все слова в списке (только слова для чтения)
     */
    public function getWords(): array
    {
        return $this->items()
            ->where('word_type', 'reading_quiz_word')
            ->pluck('word_id')
            ->toArray();
    }

    /**
     * Проверить, содержит ли список это слово
     */
    public function hasWord(int $wordId): bool
    {
        return $this->items()
            ->where('word_id', $wordId)
            ->where('word_type', 'reading_quiz_word')
            ->exists();
    }

    /**
     * Добавить слово в список (слова для чтения)
     */
    public function addWord(int $wordId): void
    {
        if (!$this->hasWord($wordId)) {
            $this->items()->create(['word_id' => $wordId, 'word_type' => 'reading_quiz_word']);
            $this->increment('word_count');
        }
    }

    /**
     * Удалить слово из списка
     */
    public function removeWord(int $wordId): void
    {
        if ($this->hasWord($wordId)) {
            $this->items()
                ->where('word_id', $wordId)
                ->where('word_type', 'reading_quiz_word')
                ->delete();
            $this->decrement('word_count');
        }
    }
}
