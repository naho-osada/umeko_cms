<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class SaveFile extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('save_file', function (Blueprint $table) {
            $table->id();
            $table->char('year', 10)->comment('保存した年');
            $table->char('month', 10)->comment('保存した月');
            $table->tinyInteger('type')->length(4)->comment('1:画像 2:その他');
            $table->char('filename', 255)->comment('ファイルパス名');
            $table->char('description', 255)->nullable()->default(null)->comment('ファイルの説明 画像の場合はaltになる');
            $table->integer('user_id')->comment('アップロードしたユーザーID');
            $table->dateTime('created_at')->default(DB::raw('CURRENT_TIMESTAMP'));
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
        Schema::dropIfExists('save_file');
    }
}
