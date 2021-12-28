<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CategoryTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // カテゴリを追加
        $category = [
            [
                'category_name' => '01',
                'disp_name' => 'カテゴリ01',
                'sort_no' => 1,
                'user_id' => 1,
                'created_at' => now(),
                'updated_user_id' => 1,
                'updated_at' => now(),
            ],
            [
                'category_name' => '02',
                'disp_name' => 'カテゴリ02',
                'sort_no' => 2,
                'user_id' => 2,
                'created_at' => now(),
                'updated_user_id' => 2,
                'updated_at' => now(),
            ],
            [
                'category_name' => '03',
                'disp_name' => 'カテゴリ03',
                'sort_no' => 3,
                'user_id' => 3,
                'created_at' => now(),
                'updated_user_id' => 3,
                'updated_at' => now(),
            ],
        ];
        foreach($category as $data) {
            DB::table('category')->insert($data);
        }

        \App\Models\Category::factory()->count(20)->create();
    }
}
