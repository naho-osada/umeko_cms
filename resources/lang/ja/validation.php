<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Validation Language Lines
    |--------------------------------------------------------------------------
    |
    | The following language lines contain the default error messages used by
    | the validator class. Some of these rules have multiple versions such
    | as the size rules. Feel free to tweak each of these messages here.
    |
    */
    'required' => '必須項目です。',
    'unique' => '既に存在します。別の情報を入力してください。',
    'email'    => '有効なメールアドレスではありません。',
    'max' => [
        'numeric' => ':max字以内で入力してください。',
        'file' => 'ファイルは:maxkbyte以内にしてください。',
        'string' => ':max字以内で入力してください。',
        'array' => 'The :attribute may not have more than :max items.',
    ],
    'integer' => '半角数字で入力してください。',
    'halfstring' => '半角英数字で入力してください。',
    'invalidvalue' => '不正な値が入力されています。',
    'halfstringsymbol' => '半角英数字と記号（ハイフン、アンダーバー）で入力してください。',
    'duplicate' => '既に登録されている内容です。別の内容を入力してください。',
    'date_format' => '日付の形式に誤りがあります。',
    'image' => '拡張子は' . implode(' 、 ', config('umekoset.image_ex')) . ' のいずれかのファイルを指定してください。',
    'captcha' => '入力された文字列と画像の文字列が異なります。',

    /*
    |--------------------------------------------------------------------------
    | Custom Validation Language Lines
    |--------------------------------------------------------------------------
    |
    | Here you may specify custom validation messages for attributes using the
    | convention "attribute.rule" to name the lines. This makes it quick to
    | specify a specific custom language line for a given attribute rule.
    |
    */

    'custom' => [
        'attribute-name' => [
            'rule-name' => 'custom-message',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Custom Validation Attributes
    |--------------------------------------------------------------------------
    |
    | The following language lines are used to swap our attribute placeholder
    | with something more reader friendly such as "E-Mail Address" instead
    | of "email". This simply helps us make our message more expressive.
    |
    */

    'attributes' => [],

];
