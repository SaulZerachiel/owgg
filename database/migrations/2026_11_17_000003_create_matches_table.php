<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('matches', function (Blueprint $table) {
            $table->id();
            $table->foreignId('profile_id')->constrained()->cascadeOnDelete();
            $table->string('external_id')->nullable()->unique();
            $table->string('mode');
            $table->boolean('won');
            $table->unsignedSmallInteger('duration_seconds')->nullable();
            $table->timestamp('played_at');
            $table->timestamps();

            $table->index(['profile_id', 'played_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('matches');
    }
};
