<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class KanjiStudyList extends Model
{
    protected $fillable = [
        'user_id',
        'name',
        'description',
        'kanji_count',
        'repetitions_completed',
        'multiple_choice_only',
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
     * Элементы списка (кандзи)
     */
    public function items(): HasMany
    {
        return $this->hasMany(KanjiStudyListItem::class, 'list_id');
    }

    /**
     * Получить все кандзи в списке
     */
    public function getKanjis(): array
    {
        return $this->items()->pluck('kanji')->toArray();
    }

    /**
     * Добавить кандзи в список
     */
    public function addKanji(string $kanji): void
    {
        $exists = $this->items()->where('kanji', $kanji)->exists();
        if (!$exists) {
            $this->items()->create(['kanji' => $kanji]);
            $this->update(['kanji_count' => $this->items()->count()]);
        }
    }

    /**
     * Удалить кандзи из списка
     */
    public function removeKanji(string $kanji): void
    {
        $this->items()->where('kanji', $kanji)->delete();
        $this->update(['kanji_count' => $this->items()->count()]);
    }

    /**
     * Проверить наличие кандзи в списке
     */
    public function hasKanji(string $kanji): bool
    {
        return $this->items()->where('kanji', $kanji)->exists();
    }
}
