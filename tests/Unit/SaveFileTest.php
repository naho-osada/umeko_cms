<?php
/**
 * /app/Models/SaveFile.phpのテスト
 */
namespace Tests\Unit;

use Tests\TestCase;
use App\Models\SaveFile;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase; // テスト用データを自動で元に戻す

class SaveFileTest extends TestCase
{
    use RefreshDatabase;
    use WithFaker;

    protected $seed = true;

    /**
     * addFileImage
     * 画像ファイルを保存する
     */
    public function test_addFileImage()
    {
        $date = date('Y-m-d H:i:s');
        $year = date('Y');
        $month = date('m');
        $filename = 'test.jpg';
        $alt = 'test';
        $addData = [];
        $addData['year'] = $year;
        $addData['month'] = $month;
        $addData['type'] = config('umekoset.image_type');
        $addData['filename'] = $filename;
        $addData['description'] = $alt;
        $addData['user_id'] = 1;
        $addData['created_at'] = $date;
        $addData['updated_at'] = $date;
        $db = new SaveFile();
        $id = $db->addFile($addData);
        $this->assertNotNull($id);
        if($id != null) {
            $data = $db->getFile(['id'=>$id]);
            $data = $data[0];
            // データベースから取得したデータと登録したデータがすべて一致することを確認
            $this->assertSame($data->id, $id);
            $this->assertSame($data->year, $year);
            $this->assertSame($data->month, $month);
            $this->assertSame($data->type, config('umekoset.image_type'));
            $this->assertSame($data->filename, $filename);
            $this->assertSame($data->description, $alt);
            $this->assertSame($data->user_id, 1);
            $this->assertSame($data->created_at, $date);
            $this->assertSame($data->updated_at, $date);
        }
    }

    /**
     * updateFileData
     * 画像ファイルを更新する
     */
    public function test_updateFileData()
    {
        $date = date('Y-m-d H:i:s');
        $alt = 'test edit';
        $id = 1;
        $updData = [];
        $updData['id'] = $id;
        $updData['description'] = $alt;
        $updData['updated_at'] = $date;
        $db = new SaveFile();
        $result = $db->updateFileData($updData);
        $this->assertSame(1, $result);

        $data = $db->getFile(['id'=>$id]);
        $data = $data[0];
        // データベースから取得したデータと登録したデータがすべて一致することを確認
        $this->assertSame($data->id, $id);
        $this->assertSame($data->description, $alt);
        $this->assertSame($data->updated_at, $date);
    }
    /**
     * description空更新（OK）
     */
    public function test_updateFileDataAltEmpty()
    {
        $date = date('Y-m-d H:i:s');
        $alt = '';
        $id = 3;
        $updData = [];
        $updData['id'] = $id;
        $updData['description'] = $alt;
        $updData['updated_at'] = $date;
        $db = new SaveFile();
        $result = $db->updateFileData($updData);
        $this->assertSame(1, $result);

        $data = $db->getFile(['id'=>$id]);
        $data = $data[0];
        // データベースから取得したデータと登録したデータがすべて一致することを確認
        $this->assertSame($data->id, $id);
        $this->assertSame($data->description, $alt);
        $this->assertSame($data->updated_at, $date);
    }
    /**
     * id空で更新しようとする
     */
    public function test_updateFileDataErr()
    {
        $date = date('Y-m-d H:i:s');
        $alt = 'test edit';
        $updData = [];
        $updData['description'] = $alt;
        $updData['updated_at'] = $date;
        $db = new SaveFile();
        $result = $db->updateFileData($updData);
        $this->assertFalse($result);
    }
    /**
     * deleteFile
     * ファイルの削除
     */
    public function test_deleteFile()
    {
        $id = 2;
        $db = new SaveFile();
        $result = $db->deleteFile($id);
        $this->assertSame(1, $result);
    }
    /**
     * id空で削除しようとする
     */
    public function test_deleteFileErr()
    {
        $id = '';
        $db = new SaveFile();
        $result = $db->deleteFile($id);
        $this->assertFalse($result);
    }

    /**
     * getList
     * 存在するファイルを取得する
     */
    public function test_getList()
    {
        $db = new SaveFile();
        $data = $db->getList();
        $cols = ['id', 'year', 'month', 'filename', 'description', 'user_id', 'created_at', 'user_name'];
        $this->assertSame($cols, array_keys((array)$data[0]));

        $this->assertSame(1, $data[0]->id);
        $this->assertSame('2021', $data[0]->year);
        $this->assertSame('10', $data[0]->month);
        $this->assertSame('test.jpg', $data[0]->filename);
        $this->assertSame('test01 description', $data[0]->description);
        $this->assertSame(1, $data[0]->user_id);

        $this->assertSame(2, $data[1]->id);
        $this->assertSame('2021', $data[1]->year);
        $this->assertSame('10', $data[1]->month);
        $this->assertSame('test02.jpg', $data[1]->filename);
        $this->assertSame('test02 description', $data[1]->description);
        $this->assertSame(2, $data[1]->user_id);

        $this->assertSame(3, $data[2]->id);
        $this->assertSame('2021', $data[2]->year);
        $this->assertSame('09', $data[2]->month);
        $this->assertSame('test03.jpg', $data[2]->filename);
        $this->assertSame('test03 description', $data[2]->description);
        $this->assertSame(3, $data[2]->user_id);

        $this->assertSame(4, $data[3]->id);
        $this->assertSame('2021', $data[3]->year);
        $this->assertSame('09', $data[3]->month);
        $this->assertSame('test04.jpg', $data[3]->filename);
        $this->assertSame('test04 description', $data[3]->description);
        $this->assertSame(4, $data[3]->user_id);

        $this->assertSame(5, $data[4]->id);
        $this->assertSame('2021', $data[4]->year);
        $this->assertSame('10', $data[4]->month);
        $this->assertSame('test05.jpg', $data[4]->filename);
        $this->assertSame('test05 description', $data[4]->description);
        $this->assertSame(5, $data[4]->user_id);
    }

    /**
     * getFileEdit
     * 編集用のファイル情報を取得する
     */
    public function test_getFileEdit()
    {
        $id = 2;
        $db = new SaveFile();
        $data = $db->getFileEdit($id);
        $cols = ['id', 'year', 'month', 'filename', 'description', 'user_id', 'created_at', 'updated_at', 'user_name'];
        $this->assertSame($cols, array_keys((array)$data[0]));

        $this->assertSame(2, $data[0]->id);
        $this->assertSame('2021', $data[0]->year);
        $this->assertSame('10', $data[0]->month);
        $this->assertSame('test02.jpg', $data[0]->filename);
        $this->assertSame('test02 description', $data[0]->description);
        $this->assertSame(2, $data[0]->user_id);
    }
    /**
     * ID空で取得しようとする
     */
    public function test_getFileEditErr()
    {
        $id = '';
        $db = new SaveFile();
        $data = $db->getFileEdit($id);
        $this->assertFalse($data);
    }
}
