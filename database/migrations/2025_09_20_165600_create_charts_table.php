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
        Schema::create('charts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->date('week_start_date');
            $table->string('chart_type')->default('weekly'); // weekly, monthly, yearly
            $table->integer('chart_size')->default(25); // 25, 50, 100 albums
            $table->timestamps();

            $table->index(['user_id', 'week_start_date']);
            $table->unique(['user_id', 'week_start_date', 'chart_type']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('charts');
    }
};
