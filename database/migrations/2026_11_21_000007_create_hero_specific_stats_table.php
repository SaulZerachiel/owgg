<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('hero_specific_stats', function (Blueprint $table) {
            $table->id();
            $table->string('hero_id');
            $table->string('player_id');
            $table->string('stat_key');
            $table->text('stat_value')->nullable();
            $table->timestamps();

            $table->foreign('hero_id')->references('hero_id')->on('heroes')->cascadeOnDelete();
            $table->foreign('player_id')->references('player_id')->on('profiles')->cascadeOnDelete();
            $table->unique(['hero_id', 'player_id', 'stat_key']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('hero_specific_stats');
    }
};
