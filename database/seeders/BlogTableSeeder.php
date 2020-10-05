<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use DB;

class BlogTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('blogs')->insert([
            'subject' => 'Test',
            'body' => '<h1>Test</h1>',
            'user' => 1
        ]);
    }
}
