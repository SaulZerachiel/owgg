<?php

namespace App\Models;

use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property string $player_id
 * @property string $fingerprint
 * @property array<string, mixed> $snapshot
 * @property CarbonImmutable $captured_at
 */
class PlayerStatSnapshot extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'player_id',
        'fingerprint',
        'snapshot',
        'captured_at',
    ];

    protected function casts(): array
    {
        return [
            'snapshot' => 'array',
            'captured_at' => 'immutable_datetime',
        ];
    }

    /** @return BelongsTo<Profile, $this> */
    public function profile(): BelongsTo
    {
        return $this->belongsTo(Profile::class, 'player_id', 'player_id');
    }
}
