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
        Schema::create('listening_sessions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('album_id')->constrained()->onDelete('cascade');
            $table->timestamp('listened_at');
            $table->boolean('completed')->default(false);
            $table->string('format')->default('streaming');
            $table->integer('track_count_played')->nullable();
            $table->integer('total_tracks')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'listened_at']);
            $table->index(['album_id', 'listened_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('listening_sessions');
    }
};
