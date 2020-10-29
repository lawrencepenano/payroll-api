<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use DB;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('roles')->delete();

        $roles = [
            ['name' => "Super Admin"],
            ['name' => "Admin"],
            ['name' => "Employee"],
        ];

        DB::table('roles')->insert($roles);
    }
}
