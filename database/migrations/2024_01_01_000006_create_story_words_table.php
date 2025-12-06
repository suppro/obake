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
        Schema::create('story_words', function (Blueprint $table) {
            $table->id();
            $table->foreignId('story_id')->constrained()->onDelete('cascade');
            $table->foreignId('word_id')->constrained('global_dictionary')->onDelete('cascade');
            $table->integer('position')->nullable(); // Позиция слова в тексте (для будущего использования)
            $table->timestamps();
            
            $table->index(['story_id', 'word_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('story_words');
    }
};
