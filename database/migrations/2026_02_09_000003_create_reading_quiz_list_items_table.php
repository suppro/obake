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
        Schema::create('reading_quiz_list_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('list_id')->constrained('reading_quiz_lists')->onDelete('cascade');
            $table->foreignId('word_id')->constrained('global_dictionary')->onDelete('cascade');
            $table->timestamps();
            
            // Уникальный индекс: одно слово не может быть дважды в одном списке
            $table->unique(['list_id', 'word_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('reading_quiz_list_items');
    }
};
