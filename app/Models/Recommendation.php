<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Recommendation extends Model
{
    use HasFactory;

    protected $fillable = [
        'match_analysis_id',
        'title',
        'content',
        'priority',
        'completed',
    ];

    protected function casts(): array
    {
        return ['completed' => 'boolean'];
    }

    public function analysis(): BelongsTo
    {
        return $this->belongsTo(MatchAnalysis::class, 'match_analysis_id');
    }
}
