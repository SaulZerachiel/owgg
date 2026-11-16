<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class GameMatch extends Model
{
    use HasFactory;

    protected $table = 'matches';

    protected $fillable = [
        'profile_id',
        'external_id',
        'mode',
        'won',
        'duration_seconds',
        'played_at',
    ];

    protected function casts(): array
    {
        return ['won' => 'boolean', 'played_at' => 'datetime'];
    }

    public function profile(): BelongsTo
    {
        return $this->belongsTo(Profile::class);
    }

    public function heroStats(): HasMany
    {
        return $this->hasMany(MatchHeroStat::class, 'match_id');
    }

    public function analysis(): HasOne
    {
        return $this->hasOne(MatchAnalysis::class, 'match_id');
    }
}
