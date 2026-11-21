<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('heroes', function (Blueprint $table) {
            $table->string('hero_id')->primary();
            $table->string('role_id');
            $table->string('hero_name');
            $table->timestamps();

            $table->foreign('role_id')->references('role_id')->on('roles')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('heroes');
    }
};
