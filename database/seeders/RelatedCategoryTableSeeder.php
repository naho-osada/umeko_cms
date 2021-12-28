<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class RelatedCategoryTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // 選択されているカテゴリを追加
        $relCategory = [
            [
                'article_id' => 1,
                'category_id' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'article_id' => 1,
                'category_id' => 2,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'article_id' => 2,
                'category_id' => 3,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];
        foreach($relCategory as $data) {
            DB::table('related_category')->insert($data);
        }

        \App\Models\RelatedCategory::factory()->count(20)->create();
    }
}
