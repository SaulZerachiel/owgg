<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class HeroSpecificStat extends Model
{
    //
    protected $fillable = [
        'hero_id', // FK
        'player_id', // FK
        'stat_key', // PK
        'stat_value',
    ];

    public function hero(): BelongsTo
    {
        return $this->belongsTo(Hero::class, 'hero_id', 'hero_id');
    }

    public function profile(): BelongsTo
    {
        return $this->belongsTo(Profile::class, 'player_id', 'player_id');
    }
}
