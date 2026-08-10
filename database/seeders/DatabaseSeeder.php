<?php

namespace Database\Seeders;

use App\Models\User;
use App\Support\Role as R;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

// Role & jenjang sudah dibuat di migration create_default_roles.
class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $super = User::firstOrCreate(
            ['email' => 'superadmin@lms.test'],
            [
                'name' => 'Super Admin',
                'password' => Hash::make('password'),
                'status' => 'active',
            ]
        );
        $super->syncRoles([R::SUPER_ADMIN]);
    }
}
