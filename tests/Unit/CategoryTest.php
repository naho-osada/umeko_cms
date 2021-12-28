<?php
/**
 * /app/Models/Category.phpのテスト
 */
namespace Tests\Unit;

use Tests\TestCase;
use App\Models\Category;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase; // テスト用データを自動で元に戻す

class CategoryTest extends TestCase
{
    use RefreshDatabase;
    use WithFaker;

    protected $seed = true;
    /**
     * getList
     * オブジェクトを取得していること
     */
    public function test_getListCategoryObject()
    {
        $db = new Category();
        $categories = $db->getList();
        $this->assertTrue(is_object($categories));
    }
    /**
     * 必要な項目を取得していること
     */
    public function test_getListCategoryCols()
    {
        $db = new Category();
        $categories = $db->getList();
        $categories = (array)$categories[0];
        $cols = ['id', 'category_name', 'disp_name', 'user_id', 'article_cnt'];
        $this->assertSame($cols, array_keys($categories));
    }

    /**
     * getCategory
     * 配列を取得していること
     */
    public function test_getCategoryArray()
    {
        $search = [];
        $search['id'] = 1;
        $db = new Category();
        $categories = $db->getCategory($search);
        $this->assertTrue(is_array($categories));
    }
    /**
     * 必要な項目を取得していること
     */
    public function test_getCategoryCols()
    {
        $search = [];
        $search['id'] = 2;
        $db = new Category();
        $category = $db->getCategory($search);
        $category = (array)$category[0];
        $cols = ['id', 'category_name', 'disp_name', 'sort_no', 'updated_at'];
        $this->assertSame($cols, array_keys($category));
    }
    /**
     * 指定IDのデータを取得していること
     */
    public function test_getCategoryData()
    {
        $search = [];
        $search['id'] = 2;
        $db = new Category();
        $category = $db->getCategory($search);
        $category = $category[0];
        $this->assertSame($category->category_name, '02');
        $this->assertSame($category->disp_name, 'カテゴリ02');
        $this->assertSame($category->sort_no, 2);
    }
    /**
     * 空IDのときは何も取得しないこと
     */
    public function test_categoryGetEmptyData()
    {
        $db = new Category();
        $category = $db->getCategory(['id' => '']);

        $this->assertEmpty($category);
    }
    /**
     * 存在しないIDのときは空になること
     */
    public function test_ArticleGetNoData()
    {
        $db = new Category();
        $category = $db->getCategory(['id' => 999]);

        $this->assertEmpty($category);
    }

    /**
     * getCategoryNum
     * 数値を取得していること
     */
    public function test_getCategoryNum()
    {
        $db = new Category();
        $num = $db->getCategoryNum();
        $this->assertIsNumeric($num);
    }

    /**
     * addCategory
     * カテゴリーを登録する
     */
    public function test_addCategory()
    {
        $db = new Category();
        $max = $db->getCategoryNum();
        $category_name = $this->faker->realText(20);
        $disp_name = $this->faker->realText(20);
        $sort_no = mt_rand(1, $max);
        $date = date('Y-m-d H:i:s');
        $update = $date;
        $user_id = mt_rand(1, 5);

        $addData = [];
        $addData['category_name'] = $category_name;
        $addData['disp_name'] = $disp_name;
        $addData['sort_no'] = $sort_no;
        $addData['user_id'] = $user_id;
        $addData['created_at'] = $date;
        $addData['updated_user_id'] = $user_id;
        $addData['updated_at'] = $update;

        $id = $db->addCategory($addData);
        $this->assertNotNull($id);
        if($id != null) {
            $data = $db->getCategoryAll($id);
            $category = $data[0];
            // データベースから取得したデータと登録したデータがすべて一致することを確認
            $this->assertSame($category->id, $id);
            $this->assertSame($category->category_name, $category_name);
            $this->assertSame($category->disp_name, $disp_name);
            $this->assertSame($category->user_id, $user_id);
            $this->assertSame($category->created_at, $date);
            $this->assertSame($category->updated_user_id, $user_id);
            $this->assertSame($category->updated_at, $update);
        }
    }

    /**
     * categoryUpdate
     * カテゴリを更新する
     */
    public function test_updateCategory()
    {
        $db = new Category();
        $max = $db->getCategoryNum();
        $id = 3;
        $category_name = $this->faker->realText(20);
        $disp_name = $this->faker->realText(20);
        $sort_no = mt_rand(1, $max);
        $date = date('Y-m-d H:i:s', strtotime('+1day'));
        $update = $date;
        $user_id = 5;

        $updData = [];
        $updData['id'] = $id;
        $updData['category_name'] = $category_name;
        $updData['disp_name'] = $disp_name;
        $updData['sort_no'] = $sort_no;
        $updData['updated_user_id'] = $user_id;
        $updData['updated_at'] = $update;

        // 更新前データ
        $beforeData = $db->getCategoryAll($id);
        $before = $beforeData[0];

        $result = $db->updateCategory($updData);
        // 更新成功していることを確認
        $this->assertSame(1, $result);

        $afterData = $db->getCategoryAll($id);
        $after = $afterData[0];

        // 更新した内容がPOSTしたものと一致すること
        $this->assertSame($after->category_name, $category_name);
        $this->assertSame($after->disp_name, $disp_name);
        $this->assertSame($after->sort_no, $sort_no);
        $this->assertSame($after->updated_user_id, $user_id);
        $this->assertSame($after->updated_at, $update);

        // 更新前データとデータベースから取得したデータが更新部分以外は一致すること
        $this->assertSame($before->id, $after->id);
        $this->assertSame($before->user_id, $after->user_id);
        $this->assertSame($before->created_at, $after->created_at);
        // 不一致項目（更新された項目）
        $this->assertNotSame($before->category_name, $after->category_name);
        $this->assertNotSame($before->disp_name, $after->disp_name);
        $this->assertNotSame($before->updated_user_id, $after->updated_user_id);
        $this->assertNotSame($before->updated_at, $after->updated_at);
    }
    /**
     * カテゴリを更新する（カテゴリ名、ソート順）
     */
    public function test_updateCategoryParts()
    {
        $db = new Category();
        $max = $db->getCategoryNum();
        $id = 2;
        $category_name = $this->faker->realText(20);
        $sort_no = $max;
        $date = date('Y-m-d H:i:s', strtotime('+1day'));
        $update = $date;
        $user_id = 5;

        $updData = [];
        $updData['id'] = $id;
        $updData['category_name'] = $category_name;
        $updData['sort_no'] = $sort_no;
        $updData['updated_user_id'] = $user_id;
        $updData['updated_at'] = $update;

        // 更新前データ
        $beforeData = $db->getCategoryAll($id);
        $before = $beforeData[0];

        $result = $db->updateCategory($updData);
        // 更新成功していることを確認
        $this->assertSame(1, $result);

        $afterData = $db->getCategoryAll($id);
        $after = $afterData[0];

        // 更新した内容がPOSTしたものと一致すること
        $this->assertSame($after->category_name, $category_name);
        $this->assertSame($after->sort_no, $sort_no);
        $this->assertSame($after->updated_user_id, $user_id);
        $this->assertSame($after->updated_at, $update);

        // 更新前データとデータベースから取得したデータが更新部分以外は一致すること
        $this->assertSame($before->id, $after->id);
        $this->assertSame($before->disp_name, $after->disp_name);
        $this->assertSame($before->user_id, $after->user_id);
        $this->assertSame($before->created_at, $after->created_at);
        // 不一致項目（更新された項目）
        $this->assertNotSame($before->category_name, $after->category_name);
        $this->assertNotSame($before->sort_no, $after->sort_no);
        $this->assertNotSame($before->updated_user_id, $after->updated_user_id);
        $this->assertNotSame($before->updated_at, $after->updated_at);
    }
    /**
     * id空でカテゴリーを更新しようとする
     */
    public function test_updateCategoryEmpty()
    {
        $db = new Category();
        $max = $db->getCategoryNum();
        $category_name = $this->faker->realText(20);
        $disp_name = $this->faker->realText(20);
        $sort_no = mt_rand(1, $max);
        $date = date('Y-m-d H:i:s');
        $update = $date;
        $user_id = mt_rand(1, 5);

        $updData = [];
        $updData['category_name'] = $category_name;
        $updData['disp_name'] = $disp_name;
        $updData['sort_no'] = $sort_no;
        $updData['user_id'] = $user_id;
        $updData['updated_user_id'] = $user_id;
        $updData['updated_at'] = $update;

        $result = $db->updateCategory($updData);
        // 更新されなかったことを確認
        $this->assertFalse($result);
    }
    /**
     * 存在しないIDでカテゴリーを更新しようとする
     */
    public function test_updateCategoryNoExist()
    {
        $db = new Category();
        $max = $db->getCategoryNum();
        $id = 1000;
        $category_name = $this->faker->realText(20);
        $disp_name = $this->faker->realText(20);
        $sort_no = mt_rand(1, $max);
        $date = date('Y-m-d H:i:s');
        $update = $date;
        $user_id = mt_rand(1, 5);

        $updData = [];
        $updData['id'] = $id;
        $updData['category_name'] = $category_name;
        $updData['disp_name'] = $disp_name;
        $updData['sort_no'] = $sort_no;
        $updData['user_id'] = $user_id;
        $updData['updated_user_id'] = $user_id;
        $updData['updated_at'] = $update;

        $result = $db->updateCategory($updData);
        // 更新されなかったことを確認
        $this->assertSame(0, $result);
    }

    /**
     * カテゴリを削除する
     */
    public function test_deleteCategory()
    {
        $id = 10;

        $db = new Category();
        $result = $db->deleteCategory($id);
        // 削除成功していることを確認
        $this->assertSame(1, $result);

        // データがないことを確認
        $data = $db->getCategory(['id' => $id]);
        $this->assertEmpty($data);
    }
    /**
     * id空で記事を削除しようとする
     */
    public function test_deleteCategoryEmpty()
    {
        $db = new Category();
        $result = $db->deleteCategory('');
        // 失敗していることを確認
        $this->assertFalse($result);
    }
    /**
     * 存在しないidで記事を削除しようとする
     */
    public function test_deleteCategoryNoExist()
    {
        $db = new Category();
        $result = $db->deleteCategory(999);
        // 失敗していることを確認
        $this->assertSame(0, $result);
    }

    /**
     * getCategoryName
     * カテゴリ名で検索（表示名ではない、英数字）
     */
    public function test_getCategoryName()
    {
        $db = new Category();
        $data = $db->getCategoryName();
        $this->assertSame(0, count($data));

        $name = '03';
        $data = $db->getCategoryName($name);
        $this->assertSame(1, count($data));
        $this->assertSame(3, $data[0]->id);
        $this->assertSame('03', $data[0]->category_name);
        $this->assertSame('カテゴリ03', $data[0]->disp_name);

        $name = '0123456789';
        $data = $db->getCategoryName();
        $this->assertSame(0, count($data));
    }

    /**
     * getListPublish
     * 一覧用のカテゴリリストのデータを取得する
     */
    public function test_getListPublish()
    {
        $cols = ['id', 'category_name', 'disp_name', 'user_id', 'article_cnt'];
        $db = new Category();
        $categories = $db->getListPublish();
        $this->assertTrue(is_object($categories));
        // 取得項目の確認
        $checkColData = (array)$categories[0];
        $this->assertSame($cols, array_keys($checkColData));
        // 取得したカテゴリは件数0のものがないこと
        foreach($categories as $data) {
            $this->assertGreaterThanOrEqual(1, $data->article_cnt);
        }
    }
}
