<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class UserSeeder extends Seeder
{
    public function run()
    {
        DB::table('users')->insert([
            [
                'name' => 'Admin',
                'username' => 'admin_bps',
                'password' => bcrypt('password123'),
                'role' => 'admin',
            ],
            [
                'name' => 'Sarah',
                'username' => 'pml_sarah',
                'password' => bcrypt('sarah2026'),
                'role' => 'pml',
            ],
        ]);
    }
}
