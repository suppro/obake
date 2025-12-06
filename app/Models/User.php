<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'is_admin',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_admin' => 'boolean',
        ];
    }

    /**
     * Слова из личного словаря пользователя
     */
    public function dictionary(): BelongsToMany
    {
        return $this->belongsToMany(GlobalDictionary::class, 'user_dictionary', 'user_id', 'word_id')
            ->withTimestamps();
    }

    /**
     * Прочитанные рассказы пользователя
     */
    public function readStories(): BelongsToMany
    {
        return $this->belongsToMany(Story::class, 'read_stories', 'user_id', 'story_id')
            ->withPivot('read_at')
            ->withTimestamps();
    }

    /**
     * Проверка, является ли пользователь администратором
     */
    public function isAdmin(): bool
    {
        return $this->is_admin === true;
    }
}
