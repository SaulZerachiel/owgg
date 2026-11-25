<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CounterwatchHeroMapRecommendation extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'hero_id',
        'map_id',
        'suitability',
        'priority',
        'source',
        'source_url',
    ];

    protected function casts(): array
    {
        return [
            'priority' => 'integer',
        ];
    }
}
