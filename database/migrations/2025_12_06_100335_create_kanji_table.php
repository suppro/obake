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
        Schema::create('kanji', function (Blueprint $table) {
            $table->id();
            $table->string('kanji', 10)->unique(); // Символ кандзи
            $table->string('translation_ru'); // Перевод на русский
            $table->text('description')->nullable(); // Описание (опционально)
            $table->integer('stroke_count')->nullable(); // Количество черт (опционально)
            $table->boolean('is_active')->default(true); // Активен ли кандзи
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('kanji');
    }
};
