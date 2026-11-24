<?php

namespace App\Models;

use Database\Factories\ProfileFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Profile extends Model
{
    /** @use HasFactory<ProfileFactory> */
    use HasFactory;

    protected $primaryKey = 'player_id';

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = [
        'player_id', // Primary key; OverFast accepts the BattleTag as its identifier.
        'user_id', // FK to user model (one-to-one relation)
        'name',
        'avatar',
        'namecard',
        'title',
        'career_url',
        'blizzard_id',
        'last_updated_at',
        'is_public',    // Has to be true, else can't access data.
        // If private -> error with explanation on how to turn profile public on overwatch
    ];

    protected function casts(): array
    {
        return [
            'last_updated_at' => 'datetime',
            'is_public' => 'boolean',
        ];
    }

    /** @return BelongsTo<User, $this> */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /** @return HasMany<Rank, $this> */
    public function ranks(): HasMany
    {
        return $this->hasMany(Rank::class, 'player_id', 'player_id');
    }

    /** @return HasMany<RolesStats, $this> */
    public function rolesStats(): HasMany
    {
        return $this->hasMany(RolesStats::class, 'player_id', 'player_id');
    }

    /** @return HasOne<AllHeroesStats, $this> */
    public function allHeroesStats(): HasOne
    {
        return $this->hasOne(AllHeroesStats::class, 'player_id', 'player_id');
    }

    /** @return HasMany<HeroStats, $this> */
    public function heroStats(): HasMany
    {
        return $this->hasMany(HeroStats::class, 'player_id', 'player_id');
    }

    /** @return HasMany<HeroSpecificStat, $this> */
    public function heroSpecificStats(): HasMany
    {
        return $this->hasMany(HeroSpecificStat::class, 'player_id', 'player_id');
    }

    /** @return HasMany<PlayerStatSnapshot, $this> */
    public function statSnapshots(): HasMany
    {
        return $this->hasMany(PlayerStatSnapshot::class, 'player_id', 'player_id');
    }
}
