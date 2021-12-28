<?php
/**
 * カテゴリーの存在チェック
 */
namespace App\Rules;

use App\Models\Category;
use Illuminate\Contracts\Validation\Rule;
use Illuminate\Support\Facades\DB;

class CategoryValid implements Rule
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
        $db = new Category();
        $data = $db->getList();
        $categories = [];
        foreach($data as $d) {
            $categories[] = $d->id;
        }
        foreach($value as $v) {
            if(in_array($v, $categories) !== false) continue;
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
