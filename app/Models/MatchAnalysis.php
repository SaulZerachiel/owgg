<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MatchAnalysis extends Model
{
    use HasFactory;

    protected $fillable = [
        'match_id',
        'summary',
        'strengths',
        'weaknesses',
        'score',
        'model',
    ];

    protected function casts(): array
    {
        return ['strengths' => 'array', 'weaknesses' => 'array'];
    }

    public function match(): BelongsTo
    {
        return $this->belongsTo(GameMatch::class, 'match_id');
    }

    public function recommendations(): HasMany
    {
        return $this->hasMany(Recommendation::class);
    }
}
