<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use DB;

class CommentTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('comments')->insert([
            'blog_id' => 1,
            'comment' => 'Test',
            'commentor' => 1
        ]);
    }
}
