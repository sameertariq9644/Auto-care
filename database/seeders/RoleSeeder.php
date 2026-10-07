<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        foreach (['admin', 'mechanic', 'customer'] as $role) {
            Role::findOrCreate($role, 'web');
        }

        $admin = User::firstOrCreate(
            ['email' => 'admin@autocarepro.test'],
            [
                'name' => 'AutoCare Admin',
                'password' => Hash::make('password'),
                'email_verified_at' => now(),
            ]
        );

        $admin->syncRoles(['admin']);
    }
}
