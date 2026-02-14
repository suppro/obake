<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ReadingQuizListItem extends Model
{
    protected $table = 'reading_quiz_list_items';

    protected $fillable = ['list_id', 'word_id'];

    /**
     * Список, к которому относится элемент
     */
    public function list(): BelongsTo
    {
        return $this->belongsTo(ReadingQuizList::class, 'list_id');
    }

    /**
     * Слово
     */
    public function word(): BelongsTo
    {
        return $this->belongsTo(GlobalDictionary::class, 'word_id');
    }
}
