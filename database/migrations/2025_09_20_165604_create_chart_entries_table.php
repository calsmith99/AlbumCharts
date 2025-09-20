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
        Schema::create('chart_entries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('chart_id')->constrained()->onDelete('cascade');
            $table->foreignId('album_id')->constrained()->onDelete('cascade');
            $table->integer('position'); // 1-25, 1-50, etc.
            $table->integer('play_count');
            $table->string('format')->default('streaming'); // streaming, vinyl, tape, cd
            $table->boolean('completed_album')->default(false);
            $table->integer('streak_count')->default(1); // consecutive weeks in charts
            $table->timestamps();

            $table->unique(['chart_id', 'position']);
            $table->index(['album_id', 'position']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('chart_entries');
    }
};
