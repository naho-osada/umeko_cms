<?php
/**
 * 公開側共通関数
 */
namespace app\Library;

class CommonPublic
{
    /**
     * setDefaultData
     * 画面表示するアイキャッチ画像とURLを設定する
     * @access public
     * @param $data
     * @return $data
     */
    public function setDefaultData($data, $size)
    {
        $data = $this->setImage($data, $size);
        $data = $this->setUrl($data);
        return $data;
    }
    /**
     * setImage
     * アイキャッチ画像を設定する
     * @access private
     * @param $data
     * @param $size 画像サイズ small / middle / large
     * @return $data
     */
    private function setImage($data, $size)
    {
        foreach($data as $key=>$d) {
            if(!isset($d->icatch_file)) continue;
            // アイキャッチ画像の取得
            if($d->icatch) {
                $data[$key]->icatch_thumbnail = asset('storage/uploads/image/' . $d->icatch_y . '/' . $d->icatch_m . '/' . $size . '/' . $d->icatch_file);
            }
        }
        return $data;
    }

    /**
     * setUrl
     * 公開用URLを生成する
     * @access private
     * @param $data
     * @return $data
     */
    private function setUrl($data)
    {
        foreach($data as $key=>$d) {
            if(!isset($d->publish_at)) continue;
            // URLの生成
            $dateAry = explode('-', $d->publish_at);
            $year = array_shift($dateAry);
            $month = array_shift($dateAry);
            $data[$key]->url = asset('/' . $year  . '/' . $month . '/' . $d->path);
        }
        return $data;
    }
}
