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
        Schema::create('reading_quiz_progress', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('word_id')->constrained('global_dictionary')->onDelete('cascade');
            $table->date('started_at')->default(now());
            $table->date('last_reviewed_at')->nullable();
            $table->integer('days_studied')->default(0); // Количество дней изучения
            $table->unsignedTinyInteger('level')->default(0); // 0-10, как у кандзи
            $table->dateTime('next_review_at')->nullable(); // Для интервального повторения
            $table->boolean('is_completed')->default(false);
            $table->date('completed_at')->nullable();
            $table->timestamps();
            
            // Уникальный индекс: один пользователь может изучать одно слово в квизе чтения один раз
            $table->unique(['user_id', 'word_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('reading_quiz_progress');
    }
};
