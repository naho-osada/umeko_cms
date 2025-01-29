<?php
/**
 * 梅子機能で使う設定ファイル
 */
return [
    // OGPタグ関連
    'site_name' => '梅子-Umeko-',
    'default_description' => 'オープンソースのブログCMS「梅子」',
    'default_published_time' => '2021-12-31T00:00:00+09:00',
    'top_image' => '/images/umeko-logo.png',
    'noimage' => '/images/umeko-hana-logo.png',
    'separate' => ' | ',
    'twitter_name' => '',

    'default_message' => '表示する情報がありません。',

    'auth' => ['' => '選択してください', 1 => '管理者', 2 => '一般ユーザー'],
    'auth_admin' => 1,
    'auth_user' => 2,

    'status' => [1 => '公開中', 2 => '非公開', 3 => '下書き'],
    'status_publish' => 1,
    'status_private' => 2,
    'status_draft' => 3,

    // 画像の有効拡張子設定
    'image_ex' => ['image/jpeg', 'image/png', 'image/gif'],
    // ファイル保存タイプ
    'image_type' => 1, // 画像
    'other_type' => 2, // その他
    // 最大画像サイズ
    'image_size' => [
        'large' => ['width' => 1024, 'height' => 1024],
        'middle' => ['width' => 600, 'height' => 600],
        'small' => ['width' => 300, 'height' => 300],
    ],
    // ファイル最大サイズ（kb）
    'max_filesize' => 2048,
    // HTML purifierを使用するか 使用する場合はscriptタグ使用不可となる
    'html_purifier' => false,

    // ログインキャプチャの使用 true → 使用する false → 使用しない
    'login_captcha' => false,

    // ログイン通知メールの設定 true → 送信する false → 送信しない
    'login_mail_alert' => false,

    // HTML出力機能
    'html_creater' => true,
    // 出力先のデフォルトドメイン名
    'html_domain' => 'http://localhost',

    // 公開日時の指定範囲 ±〇年
    'open_year_set' => 5,

    // 一覧のデフォルト表示件数
    'default_index_num' => 10,

    // 記事一覧の取得件数
    'article_index_num' => 10,

    // アーカイブの表示件数
    'archive_list_num' => 12,

    // ファイル一覧の取得件数
    'file_index_num' => '',

    // 公開側投稿記事の関連記事取得件数
    'related_article_num' => 6,

    // サイドバー最新記事の表示件数
    'sidebar_num' => 5,
    // サイドバー最新記事の範囲日数
    'sidebar_recent_day' => 30,

    // 記事投稿の作成権限
    'article_auth' => [1 => '管理者のみ', 2 => '管理者+作成者'],
    'article_auth_admin' => 1,
    'article_auth_creator' => 2,
];