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
        Schema::create('global_dictionary', function (Blueprint $table) {
            $table->id();
            $table->string('japanese_word'); // Слово на японском (кандзи/хирагана/катакана)
            $table->string('reading')->nullable(); // Чтение (фуригана)
            $table->text('translation_ru'); // Перевод на русский
            $table->text('translation_en'); // Перевод на английский
            $table->string('word_type')->nullable(); // Тип слова (существительное, глагол и т.д.)
            $table->text('example_ru')->nullable(); // Пример использования на русском
            $table->text('example_jp')->nullable(); // Пример использования на японском
            $table->timestamps();
            
            $table->index('japanese_word');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('global_dictionary');
    }
};
