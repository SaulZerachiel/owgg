<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOneOrMany;

class Role extends Model
{
    protected $fillable = [
        'role_id', //PK
        'role_name',
        'role_icon',
    ];

    public function ranks(): HasMany
    {
        return $this->hasMany(Rank::class, 
        'role_id', // FK
        'role_id' // Local Key
        );
    }

    public function heroes(): HasMany
    {
        return $this->hasMany(Hero::class,
        'role_id', // Foreign key in the heroes table
        'role_id'  // Primary key in the roles table
        );
    }
}
