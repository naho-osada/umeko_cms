<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class UserTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $users = [
            [
                'user_name' => '管理者',
                'email' => 'xxx@example.com',
                'password' => Hash::make('test'),
                'auth' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'user_name' => '一般ユーザー',
                'email' => 'xxx01@example.com',
                'password' => Hash::make('test'),
                'auth' => 2,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'user_name' => '一般ユーザー02',
                'email' => 'xxx02@example.com',
                'password' => Hash::make('test'),
                'auth' => 2,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'user_name' => '一般ユーザー03',
                'email' => 'xxx03@example.com',
                'password' => Hash::make('test'),
                'auth' => 2,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'user_name' => '一般ユーザー04',
                'email' => 'xxx04@example.com',
                'password' => Hash::make('test'),
                'auth' => 2,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'user_name' => '一般ユーザー05',
                'email' => 'xxx05@example.com',
                'password' => Hash::make('test'),
                'auth' => 2,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'user_name' => '管理者02',
                'email' => 'xxxAd02@example.com',
                'password' => Hash::make('test'),
                'auth' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];
        // ユーザーを追加
        foreach($users as $user) {
            DB::table('users')->insert($user);
        }
    }
}
