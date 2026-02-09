<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WordStudyListItem extends Model
{
    protected $fillable = [
        'list_id',
        'word_id',
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
        return $this->belongsTo(WordStudyList::class, 'list_id');
    }

    /**
     * Слово (связь с таблицей global_dictionary)
     */
    public function word()
    {
        return $this->belongsTo(GlobalDictionary::class, 'word_id');
    }
}
