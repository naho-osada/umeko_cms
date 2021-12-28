<?php
/**
 * 画像の拡張子チェックを行うValidation
 * jpg / pngが有効 大文字可
 */
namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;

class ImageEx implements Rule
{
    /**
     * Create a new rule instance.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Determine if the validation rule passes.
     *
     * @param  string  $attribute
     * @param  mixed  $value
     * @return bool
     */
    public function passes($attribute, $value)
    {
        $result = in_array($value, config('umekoset.image_ex'));
        if($result === false) {
            return false;
        } else {
            return true;
        }
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return trans('validation.image');
    }
}
