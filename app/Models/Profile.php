<?php

namespace App\Models;

use Database\Factories\ProfileFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Profile extends Model
{
    /** @use HasFactory<ProfileFactory> */
    use HasFactory;

    protected $fillable = [
        'player_id',
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
}
