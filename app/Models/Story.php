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
}
