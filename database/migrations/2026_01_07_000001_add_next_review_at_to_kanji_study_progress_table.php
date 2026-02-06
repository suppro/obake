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
            $table->dateTime('next_review_at')->nullable()->after('last_reviewed_at');
            $table->index(['user_id', 'next_review_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('kanji_study_progress', function (Blueprint $table) {
            $table->dropIndex(['user_id', 'next_review_at']);
            $table->dropColumn('next_review_at');
        });
    }
};


