<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class KanjiStudyListItem extends Model
{
    protected $fillable = [
        'list_id',
        'kanji',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Список, к которому относится элемент
     */
    public function list(): BelongsTo
    {
        return $this->belongsTo(KanjiStudyList::class, 'list_id');
    }

    /**
     * Кандзи (связь с таблицей kanji)
     */
    public function kanji()
    {
        return $this->belongsTo(Kanji::class, 'kanji', 'kanji');
    }
}
