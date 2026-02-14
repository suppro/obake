<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Обновляем все существующие записи в reading_quiz_list_items на правильный тип
        // Потому что по умолчанию миграция 2024_01_01_000021 установила 'global_dictionary'
        DB::table('reading_quiz_list_items')
            ->update(['word_type' => 'reading_quiz_word']);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Не синхронизируем - слишком опасно
    }
};
