<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class WordStudyList extends Model
{
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
        return $this->hasMany(WordStudyListItem::class, 'list_id');
    }

    /**
     * Получить все слова в списке
     */
    public function getWords(): array
    {
        return $this->items()->pluck('word_id')->toArray();
    }

    /**
     * Добавить слово в список
     */
    public function addWord(int $wordId): void
    {
        $exists = $this->items()->where('word_id', $wordId)->exists();
        if (!$exists) {
            $this->items()->create(['word_id' => $wordId]);
            $this->update(['word_count' => $this->items()->count()]);
        }
    }

    /**
     * Удалить слово из списка
     */
    public function removeWord(int $wordId): void
    {
        $this->items()->where('word_id', $wordId)->delete();
        $this->update(['word_count' => $this->items()->count()]);
    }

    /**
     * Проверить наличие слова в списке
     */
    public function hasWord(int $wordId): bool
    {
        return $this->items()->where('word_id', $wordId)->exists();
    }
}
