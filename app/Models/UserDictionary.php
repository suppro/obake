<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserDictionary extends Model
{
    protected $table = 'user_dictionary';

    protected $fillable = [
        'user_id',
        'word_id',
    ];

    /**
     * Пользователь
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Слово из глобального словаря
     */
    public function word(): BelongsTo
    {
        return $this->belongsTo(GlobalDictionary::class, 'word_id');
    }
}
