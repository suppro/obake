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
            $table->integer('jlpt_level')->nullable()->after('translation_ru')->comment('Уровень JLPT (N5, N4, N3, N2, N1)');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('kanji', function (Blueprint $table) {
            $table->dropColumn('jlpt_level');
        });
    }
};
