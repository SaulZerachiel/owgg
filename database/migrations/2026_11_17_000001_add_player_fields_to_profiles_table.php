<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public $withinTransaction = false;

    public function up(): void
    {
        Schema::table('profiles', function (Blueprint $table) {
            $table->foreignId('user_id')->after('id')->constrained()->cascadeOnDelete();
            $table->string('player_id')->unique()->after('user_id');
            $table->string('name')->after('player_id');
            $table->string('avatar')->nullable();
            $table->string('namecard')->nullable();
            $table->string('title')->nullable();
            $table->string('career_url')->nullable();
            $table->string('blizzard_id')->nullable()->index();
            $table->timestamp('last_updated_at')->nullable();
            $table->boolean('is_public')->default(false);
        });
    }

    public function down(): void
    {
        Schema::table('profiles', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
            $table->dropColumn([
                'user_id',
                'player_id',
                'name',
                'avatar',
                'namecard',
                'title',
                'career_url',
                'blizzard_id',
                'last_updated_at',
                'is_public',
            ]);
        });
    }
};
