<?php

namespace App\Models;

use Database\Factories\HeroFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Hero extends Model
{
    /** @use HasFactory<HeroFactory> */
    use HasFactory;

    protected $primaryKey = 'hero_id';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'hero_id', // PK
        'role_id', // FK
        'hero_name', 
    ];

    public function role(): BelongsTo
    {
        return $this->belongsTo(Role::class, 'role_id', 'role_id');
    }
}
