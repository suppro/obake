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
        Schema::create('kanji_study_list_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('list_id')->constrained('kanji_study_lists')->onDelete('cascade');
            $table->string('kanji');
            $table->timestamps();
            
            $table->unique(['list_id', 'kanji']);
            $table->foreign('kanji')->references('kanji')->on('kanji')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('kanji_study_list_items');
    }
};
