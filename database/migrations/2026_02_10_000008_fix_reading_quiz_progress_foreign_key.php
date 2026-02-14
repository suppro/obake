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
        // Удаляем foreign key constraint для reading_quiz_progress
        Schema::table('reading_quiz_progress', function (Blueprint $table) {
            try {
                $table->dropForeign('reading_quiz_progress_word_id_foreign');
            } catch (\Exception $e) {
                // Foreign key может не существовать
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Восстанавливаем foreign key constraint
        Schema::table('reading_quiz_progress', function (Blueprint $table) {
            $table->foreign('word_id')->references('id')->on('global_dictionary')->onDelete('cascade');
        });
    }
};
