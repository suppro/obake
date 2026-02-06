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
        Schema::table('kanji', function (Blueprint $table) {
            $table->text('alternative_translations')->nullable()->after('translation_ru');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('kanji', function (Blueprint $table) {
            $table->dropColumn('alternative_translations');
        });
    }
};
