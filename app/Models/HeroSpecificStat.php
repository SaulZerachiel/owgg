<?php

namespace App\Models;

use Database\Factories\HeroSpecificStatFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class HeroSpecificStat extends Model
{
    /** @use HasFactory<HeroSpecificStatFactory> */
    use HasFactory;

    protected $fillable = [
        'hero_id', // FK
        'player_id', // FK
        'stat_key', // PK
        'stat_value',
    ];

    /** @return BelongsTo<Hero, $this> */
    public function hero(): BelongsTo
    {
        return $this->belongsTo(Hero::class, 'hero_id', 'hero_id');
    }

    /** @return BelongsTo<Profile, $this> */
    public function profile(): BelongsTo
    {
        return $this->belongsTo(Profile::class, 'player_id', 'player_id');
    }
}
