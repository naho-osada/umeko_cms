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

    /**
     * setTableOfContents
     * 目次生成
     * @access public
     * @param $contents
     * @return $contents
     */
    public function setTableOfContents($contents)
    {
        // 記事内容に目次を付ける h2～h4まで自動生成
        preg_match_all("/<h[2-4](.*?)>(.*?)<\/h[2-4]>/i",$contents, $tags, PREG_PATTERN_ORDER);
        // 目次用変数
        $tableOfContents = [];
        $loopCnt = count($tags[0]);
        $headKeyCnt[] = '';
        $headKeyCnt[2] = 0;
        $headKeyCnt[3] = 0;
        $headKeyCnt[4] = 0;
        for($i=0; $i<$loopCnt; $i++) {
            $headTag = $tags[0][$i];
            $attr = $tags[1][$i];
            $str = $tags[2][$i];

            // attrにID指定が含まれる場合は目次から除外する（IDの複数指定はできないので、編集を優先する）
            if(strpos($attr, 'id="') !== false) continue;
            // h2のとき
            if(strpos($headTag, 'h2') !== false) {
                $headKey = 2;
            } else if(strpos($headTag, 'h3') !== false) {
                $headKey = 3;
            } else if(strpos($headTag, 'h4') !== false) {
                $headKey = 4;
            }
            $headKeyCnt[$headKey]++;
            $attrClass = 'lv-' . $headKey;

            $contents = str_replace($headTag, '<h' . $headKey . $attr . ' id="ttl-' . $headKey . '-' . $headKeyCnt[$headKey] . '">' . $str . '</h' . $headKey . '>', $contents);
            $tableOfContents[] = '<li class="' . $attrClass .'"><a href="#ttl-' . $headKey . '-' . $headKeyCnt[$headKey] . '">' . $str . '</a></li>';
        }
        if(!empty($tableOfContents)) {
            // 目次が存在する場合、「初めのh2タグの上」に目次を追加する
            $firstH2Tag = explode('<h2',$contents);
            $contents = $firstH2Tag[0] . '<div class="table-of-contents"><h2>目次</h2><ul>' . implode('', $tableOfContents) . '</ul></div>';
            $i = 1;
            while(isset($firstH2Tag[$i])) {
                $contents .= '<h2' . $firstH2Tag[$i];
                $i++;
            }
        }
        return $contents;
    }
}
