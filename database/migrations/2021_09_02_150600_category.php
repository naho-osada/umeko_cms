<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class Category extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('category', function (Blueprint $table) {
            $table->id();
            $table->char('category_name', 50)->comment('カテゴリ名 半角英数字、アンダーバー、ハイフン');
            $table->char('disp_name', 50)->comment('カテゴリのサイト表示名称');
            $table->integer('sort_no')->nullable()->comment('ソート番号');
            $table->integer('user_id')->comment('著者のユーザーID');
            $table->dateTime('created_at')->default(DB::raw('CURRENT_TIMESTAMP'));
            $table->integer('updated_user_id')->comment('更新したユーザーID');
            $table->dateTime('updated_at')->default(DB::raw('CURRENT_TIMESTAMP'));
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('category');
    }
}
