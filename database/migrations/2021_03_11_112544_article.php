<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class Article extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('article', function (Blueprint $table) {
            $table->id();
            $table->char('title', 255)->comment('記事タイトル'); // char(255)
            $table->longText('contents')->comment('記事内容');
            $table->tinyInteger('status')->default(1)->comment('1:公開 2:非公開 3:下書き');
            $table->tinyInteger('article_auth')->default(1)->comment('1:管理者のみ 2:管理者+作成者');
            $table->string('path')->unique()->comment('パス名を入力');
            $table->integer('icatch')->nullable()->comment('アイキャッチ画像 画像ID');
            $table->char('seo_description', 255)->nullable()->comment('SEO説明文');
            $table->dateTime('publish_at')->default(DB::raw('CURRENT_TIMESTAMP'));
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
        Schema::dropIfExists('article');
    }
}
