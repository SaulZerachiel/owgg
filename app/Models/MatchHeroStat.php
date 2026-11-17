<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MatchHeroStat extends Model
{
    use HasFactory;

    protected $fillable = [
        'match_id',
        'hero_id',
        'eliminations',
        'deaths',
        'damage',
        'healing',
        'time_played_seconds',
    ];

    public function match(): BelongsTo
    {
        return $this->belongsTo(GameMatch::class, 'match_id');
    }

    public function hero(): BelongsTo
    {
        return $this->belongsTo(Hero::class);
    }
}
