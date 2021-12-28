<?php
/**
 * カテゴリーのソート番号の最大値をチェックする
 */
namespace App\Rules;

use Category;
use Illuminate\Contracts\Validation\Rule;
use Illuminate\Support\Facades\DB;

class CategorySort implements Rule
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
        if($value == 0) return false;
        $data = DB::table('category')->count();
        if((int)$value > $data) {
            return false;
        }
        return true;
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return trans('validation.invalidvalue');
    }
}
