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
        Schema::table('kanji_study_progress', function (Blueprint $table) {
            $table->boolean('is_selected_for_study')->default(false)->after('is_completed');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('kanji_study_progress', function (Blueprint $table) {
            $table->dropColumn('is_selected_for_study');
        });
    }
};
