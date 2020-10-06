<?php

namespace Database\Seeders;

use DB;
use Illuminate\Database\Seeder;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('roles')->insert([
            "name"=>"super_admin",
            "name"=> "admin",
            "name"=>"enployee",
        ]);

        DB::table('roles')->delete();

        $roles = [
            ['name' => "Super Admin"],
            ['name' => "Admin"],
            ['name' => "Employee"],
        ];

        DB::table('roles')->insert($roles);

    }
}
