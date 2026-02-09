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
        Schema::table('kanji_study_lists', function (Blueprint $table) {
            $table->unsignedInteger('repetitions_completed')->default(0)->after('kanji_count');
        });

        Schema::table('word_study_lists', function (Blueprint $table) {
            $table->unsignedInteger('repetitions_completed')->default(0)->after('word_count');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('kanji_study_lists', function (Blueprint $table) {
            $table->dropColumn('repetitions_completed');
        });

        Schema::table('word_study_lists', function (Blueprint $table) {
            $table->dropColumn('repetitions_completed');
        });
    }
};
