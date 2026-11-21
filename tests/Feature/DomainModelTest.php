<?php

use App\Models\Profile;
use App\Models\User;

test('a profile belongs to its user', function () {
    $user = User::factory()->create();
    $profile = Profile::factory()->create(['user_id' => $user->id]);

    expect($profile->user->is($user))->toBeTrue();
});
