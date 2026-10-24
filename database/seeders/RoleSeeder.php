<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {

        DB::table('roles')->delete();

        $roles = [
            'super_admin',
            'admin',
            'academic_supervisor',
            'student_affairs',
            'finance',
            'faculty',
            'student',
        ];

        foreach ($roles as $role) {
            DB::table('roles')->insert(
                [
                    'name' => $role,
                    'guard_name' => 'web',
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            );
        }

    }
}
