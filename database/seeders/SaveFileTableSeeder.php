<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\UploadedFile;

class SaveFileTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $fileData = [
            [
                'year' => '2021',
                'month' => '10',
                'type' => 1,
                'filename' => 'test.jpg',
                'description' => 'test01 description',
                'user_id' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'year' => '2021',
                'month' => '10',
                'type' => 1,
                'filename' => 'test02.jpg',
                'description' => 'test02 description',
                'user_id' => 2,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'year' => '2021',
                'month' => '09',
                'type' => 1,
                'filename' => 'test03.jpg',
                'description' => 'test03 description',
                'user_id' => 3,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'year' => '2021',
                'month' => '09',
                'type' => 1,
                'filename' => 'test04.jpg',
                'description' => 'test04 description',
                'user_id' => 4,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'year' => '2021',
                'month' => '10',
                'type' => 1,
                'filename' => 'test05.jpg',
                'description' => 'test05 description',
                'user_id' => 5,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];
        // ユーザーを追加
        foreach($fileData as $data) {
            DB::table('save_file')->insert($data);
        }
        // ダミーファイルを生成して保存する
        $imgSizes = config('umekoset.image_size');
        for($i=0; $i<count($fileData); $i++) {
            $upFile = [];
            foreach($imgSizes as $size=>$imgSize) {
                $path = 'public/uploads/image/' . $fileData[$i]['year'] . '/' . $fileData[$i]['month'] . '/' .$size;
                $dummy =  UploadedFile::fake()->image($fileData[$i]['filename']);
                $dummy->storeAs($path, $fileData[$i]['filename'], ['disk' => 'local']);
            }
        }
        \App\Models\SaveFile::factory()->count(20)->create();
    }
}
