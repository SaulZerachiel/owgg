<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public $withinTransaction = false;

    public function up(): void
    {
        Schema::create('match_hero_stats', function (Blueprint $table) {
            $table->id();
            $table->foreignId('match_id')->constrained()->cascadeOnDelete();
            $table->foreignId('hero_id')->constrained()->restrictOnDelete();
            $table->unsignedInteger('eliminations')->default(0);
            $table->unsignedInteger('deaths')->default(0);
            $table->unsignedInteger('damage')->default(0);
            $table->unsignedInteger('healing')->default(0);
            $table->unsignedSmallInteger('time_played_seconds')->default(0);
            $table->timestamps();

            $table->unique(['match_id', 'hero_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('match_hero_stats');
    }
};
