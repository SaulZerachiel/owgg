<?php

namespace App\Models;

use Database\Factories\RolesStatsFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RolesStats extends Model
{
    /** @use HasFactory<RolesStatsFactory> */
    use HasFactory;

    protected $primaryKey = 'roles_stats_id';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'roles_stats_id', // PK
        'role_id', // FK
        'player_id', // FK
        'average_assists',
        'average_damage',
        'average_deaths',
        'average_eliminations',
        'average_healing',
        'games_lost',
        'games_played',
        'games_won',
        'kda',
        'time_played',
        'winrate',
        'total_assists',
        'total_damage',
        'total_deaths',
        'total_eliminations',
        'total_healing',
    ];

    /** @return BelongsTo<Profile, $this> */
    public function profile(): BelongsTo
    {
        return $this->belongsTo(Profile::class, 'player_id', 'player_id');
    }

    /** @return BelongsTo<Role, $this> */
    public function role(): BelongsTo
    {
        return $this->belongsTo(Role::class, 'role_id', 'role_id');
    }
}
