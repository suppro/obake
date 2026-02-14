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
        Schema::create('reading_quiz_words', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->string('japanese_word');
            $table->string('reading');
            $table->text('translation_ru');
            $table->text('translation_en')->nullable();
            $table->string('word_type')->nullable();
            $table->text('example_ru')->nullable();
            $table->text('example_jp')->nullable();
            $table->string('audio_path')->nullable();
            $table->timestamps();
            $table->index('user_id');
            $table->fullText(['japanese_word', 'reading', 'translation_ru']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('reading_quiz_words');
    }
};
