<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class HeroMatchup extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'hero_id',
        'target_hero_id',
        'relation_type',
        'score',
    ];

    protected function casts(): array
    {
        return [
            'score' => 'integer',
        ];
    }
}
