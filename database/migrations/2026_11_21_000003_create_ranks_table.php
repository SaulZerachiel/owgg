<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ranks', function (Blueprint $table) {
            $table->string('rank_id')->primary();
            $table->string('player_id');
            $table->string('role_id');
            $table->string('season');
            $table->string('tier')->nullable();
            $table->unsignedTinyInteger('division')->nullable();
            $table->timestamps();

            $table->foreign('player_id')->references('player_id')->on('profiles')->cascadeOnDelete();
            $table->foreign('role_id')->references('role_id')->on('roles')->cascadeOnDelete();
            $table->unique(['player_id', 'role_id', 'season']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ranks');
    }
};
