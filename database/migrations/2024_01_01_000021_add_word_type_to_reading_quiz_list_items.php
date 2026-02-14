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
        // Добавляем поле word_type к существующей таблице
        Schema::table('reading_quiz_list_items', function (Blueprint $table) {
            if (!Schema::hasColumn('reading_quiz_list_items', 'word_type')) {
                $table->string('word_type')->default('global_dictionary')->after('word_id');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('reading_quiz_list_items', function (Blueprint $table) {
            if (Schema::hasColumn('reading_quiz_list_items', 'word_type')) {
                $table->dropColumn('word_type');
            }
        });
    }
};
