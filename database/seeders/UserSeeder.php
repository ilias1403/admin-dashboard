<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $admin = User::updateOrCreate(
        [
            'username' => 'admin',

        ],[
            'name' => 'Superadmin',
            'username' => 'admin',
            'email' => 'admin@admin.io',
            'password' => bcrypt('password'),
        ]);

        $admin->assignRole('superadmin');
    }
}
