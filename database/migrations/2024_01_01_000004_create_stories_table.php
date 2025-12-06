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
        Schema::create('stories', function (Blueprint $table) {
            $table->id();
            $table->string('title'); // Заголовок рассказа
            $table->text('content'); // Содержание рассказа на японском
            $table->enum('level', ['N5', 'N4', 'N3', 'N2', 'N1']); // Уровень сложности
            $table->text('description')->nullable(); // Описание рассказа
            $table->boolean('is_active')->default(true); // Активен ли рассказ
            $table->timestamps();
            
            $table->index('level');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('stories');
    }
};
