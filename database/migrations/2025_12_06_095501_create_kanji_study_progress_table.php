<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('kanji_study_progress', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('kanji', 10); // Символ кандзи (может быть несколько символов, но обычно один)
            $table->string('translation_ru'); // Перевод на русский
            $table->integer('level')->default(0); // Уровень знаний (0-10)
            $table->dateTime('last_reviewed_at')->nullable();
            $table->timestamps();
            
            // Уникальный индекс: один пользователь может изучать один кандзи один раз
            $table->unique(['user_id', 'kanji']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('kanji_study_progress');
    }
};
