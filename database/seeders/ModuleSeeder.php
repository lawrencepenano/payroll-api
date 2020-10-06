<?php

namespace Database\Seeders;

use DB;
use Illuminate\Database\Seeder;

class ModuleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('modules')->delete();

        $modules = [
            ['name' => "Company"],
            ['name' => "Cost Center"],
            ['name' => "Pay Group"],
            ['name' => "Department"],
            ['name' => "Employee"]
        ];

        DB::table('modules')->insert($modules);
    }
}
