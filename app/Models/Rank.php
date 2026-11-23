<?php

namespace App\Models;

use Database\Factories\RankFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Rank extends Model
{
    /** @use HasFactory<RankFactory> */
    use HasFactory;

    protected $primaryKey = 'rank_id';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'rank_id', // PK
        'player_id', // FK
        'role_id', // FK
        'season',
        'tier',
        'division',
    ];

    public function profile(): BelongsTo
    {
        return $this->belongsTo(Profile::class, 'player_id', 'player_id');
    }

    public function role(): BelongsTo
    {
        return $this->belongsTo(Role::class, 'role_id', 'role_id');
    }
}
