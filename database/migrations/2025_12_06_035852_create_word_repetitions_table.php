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
        Schema::create('word_repetitions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('word_id')->constrained('global_dictionary')->onDelete('cascade');
            $table->date('repetition_date')->default(now());
            $table->enum('direction', ['ru_to_jp', 'jp_to_ru']); // Направление повторения
            $table->boolean('is_correct')->default(false);
            $table->text('user_answer')->nullable(); // Ответ пользователя
            $table->timestamps();
            
            // Индекс для быстрого поиска повторений по дате
            $table->index(['user_id', 'repetition_date']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('word_repetitions');
    }
};
