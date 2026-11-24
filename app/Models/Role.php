<?php

namespace App\Models;

use Database\Factories\RoleFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOneOrMany;

/**
 * @property string $role_name
 */
class Role extends Model
{
    /** @use HasFactory<RoleFactory> */
    use HasFactory;

    protected $primaryKey = 'role_id';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'role_id', //PK
        'role_name',
        'role_icon',
    ];

    /** @return HasMany<Rank, $this> */
    public function ranks(): HasMany
    {
        return $this->hasMany(Rank::class, 
        'role_id', // FK
        'role_id' // Local Key
        );
    }

    /** @return HasMany<Hero, $this> */
    public function heroes(): HasMany
    {
        return $this->hasMany(Hero::class,
        'role_id', // Foreign key in the heroes table
        'role_id'  // Primary key in the roles table
        );
    }
}
