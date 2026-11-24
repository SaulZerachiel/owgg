<?php

namespace App\Models;

use Database\Factories\AiCallFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AiCall extends Model
{
    /** @use HasFactory<AiCallFactory> */
    use HasFactory;

    protected $fillable = [
        'user_id',
        'model',
        'prompt_tokens',
        'completion_tokens',
        'cost',
        'duration_ms',
        'success',
        'error_code',
    ];

    protected function casts(): array
    {
        return [
            'cost' => 'float',
            'success' => 'boolean',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
