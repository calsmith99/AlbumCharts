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
        Schema::table('users', function (Blueprint $table) {
            $table->string('lastfm_username')->nullable();
            $table->timestamp('lastfm_connected_at')->nullable();
            $table->json('preferences')->nullable(); // UI settings, default chart size, etc.
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['lastfm_username', 'lastfm_connected_at', 'preferences']);
        });
    }
};
