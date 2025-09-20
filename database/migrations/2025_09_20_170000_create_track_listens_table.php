<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('track_listens', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('album_id')->constrained()->onDelete('cascade');
            $table->foreignId('artist_id')->constrained()->onDelete('cascade');
            $table->string('track_name');
            $table->timestamp('listened_at');
            $table->string('source')->nullable(); // e.g. 'lastfm', 'manual'
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('track_listens');
    }
};
