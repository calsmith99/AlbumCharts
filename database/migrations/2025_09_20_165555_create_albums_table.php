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
        Schema::create('albums', function (Blueprint $table) {
            $table->id();
            $table->string('lastfm_id')->nullable()->unique();
            $table->foreignId('artist_id')->constrained()->onDelete('cascade');
            $table->string('name');
            $table->date('release_date')->nullable();
            $table->text('image_url')->nullable();
            $table->json('metadata')->nullable(); // Additional Last.fm data
            $table->timestamps();

            $table->index('lastfm_id');
            $table->index(['artist_id', 'name']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('albums');
    }
};
