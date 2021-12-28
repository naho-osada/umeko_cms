<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ArticleTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // 記事を追加
        $article = [
            [
                'title' => 'テスト投稿タイトル01',
                'contents' => '<p>これはテスト投稿です</p>',
                'user_id' => 1,
                'status' => 1,
                'article_auth' => 1,
                'path' => 'test01',
                'created_at' => now(),
                'updated_user_id' => 1,
                'updated_at' => now(),
            ],
            [
                'title' => 'テスト投稿タイトル02',
                'contents' => '<p>これはテスト投稿です</p>',
                'user_id' => 2,
                'status' => 2,
                'article_auth' => 2,
                'path' => 'test02',
                'created_at' => now(),
                'updated_user_id' => 2,
                'updated_at' => now(),
            ],
            [
                'title' => 'テスト投稿タイトル03',
                'contents' => '<p>これはテスト投稿です</p><p><strong>投稿3つ目</strong></p>',
                'user_id' => 3,
                'status' => 3,
                'article_auth' => 1,
                'path' => 'test03',
                'created_at' => now(),
                'updated_user_id' => 3,
                'updated_at' => now(),
            ],
            [
                'title' => '画像確認用01',
                'contents' => 'test',
                'user_id' => 3,
                'status' => mt_rand(1,3),
                'icatch' => 1,
                'article_auth' => 1,
                'path' => 'test04',
                'created_at' => now(),
                'updated_user_id' => 1,
                'updated_at' => now(),
            ],
            [
                'title' => '画像確認用02',
                'contents' => 'test' . ' <img src="2021/10/test02.jpg">',
                'user_id' => 3,
                'status' => mt_rand(1,3),
                'article_auth' => 1,
                'path' => 'test05',
                'created_at' => now(),
                'updated_user_id' => 1,
                'updated_at' => now(),
            ],
            [
                'title' => '画像確認用03',
                'contents' => 'test',
                'user_id' => 3,
                'status' => mt_rand(1,3),
                'icatch' => 3,
                'article_auth' => 1,
                'path' => 'test06',
                'created_at' => now(),
                'updated_user_id' => 1,
                'updated_at' => now(),
            ],
            [
                'title' => '画像確認用04',
                'contents' => 'test' . ' <img src="2021/10/test04.jpg">',
                'user_id' => 3,
                'status' => mt_rand(1,3),
                'icatch' => 2,
                'article_auth' => 1,
                'path' => 'test07',
                'created_at' => now(),
                'updated_user_id' => 1,
                'updated_at' => now(),
            ],
            [
                'title' => '画像確認用05',
                'contents' => 'test' . ' <img src="2021/10/test.jpg">',
                'user_id' => 3,
                'status' => mt_rand(1,3),
                'icatch' => 5,
                'article_auth' => 1,
                'path' => 'test08',
                'created_at' => now(),
                'updated_user_id' => 1,
                'updated_at' => now(),
            ],
        ];
        foreach($article as $data) {
            DB::table('article')->insert($data);
        }

        \App\Models\Article::factory()->count(100)->create();
    }
}
