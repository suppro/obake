<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Kanji extends Model
{
    use HasFactory;

    protected $table = 'kanji';

    protected $fillable = [
        'kanji',
        'reading',
        'translation_ru',
        'jlpt_level',
        'description',
        'stroke_count',
        'is_active',
        'image_path',
        'mnemonic',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    /**
     * Прогресс изучения этого кандзи пользователями
     */
    public function studyProgress(): HasMany
    {
        return $this->hasMany(KanjiStudyProgress::class, 'kanji', 'kanji');
    }
}
