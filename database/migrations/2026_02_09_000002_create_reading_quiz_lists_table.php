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
        Schema::create('reading_quiz_lists', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('name')->index();
            $table->text('description')->nullable();
            $table->integer('word_count')->default(0);
            $table->integer('repetitions_completed')->default(0);
            $table->timestamps();
            
            // Уникальный индекс: один пользователь не может иметь два списка с одинаковым названием
            $table->unique(['user_id', 'name']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('reading_quiz_lists');
    }
};
