<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CounterwatchMatchup extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'target_archetype',
        'counter_hero_key',
        'role',
        'priority',
        'reason',
    ];

    protected function casts(): array
    {
        return [
            'priority' => 'integer',
        ];
    }
}
