<?php

namespace Database\Seeders;

use App\Models\Role;
use Illuminate\Database\Seeder;

class RoleSeeder extends Seeder
{
    public function run(): void
    {
        foreach ([
            'tank' => 'Tank',
            'damage' => 'Damage',
            'support' => 'Support',
        ] as $roleId => $roleName) {
            Role::query()->updateOrCreate(
                ['role_id' => $roleId],
                ['role_name' => $roleName],
            );
        }
    }
}
