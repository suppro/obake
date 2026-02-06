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
        Schema::table('word_study_progress', function (Blueprint $table) {
            $table->unsignedTinyInteger('level')->default(0)->after('days_studied'); // 0-10, как у кандзи
            $table->dateTime('next_review_at')->nullable()->after('level');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('word_study_progress', function (Blueprint $table) {
            $table->dropColumn(['level', 'next_review_at']);
        });
    }
};
