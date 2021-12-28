<?php
/**
 * PathのPath重複チェックを行うValidation
 */
namespace App\Rules;

use Article;
use Illuminate\Contracts\Validation\Rule;
use Illuminate\Support\Facades\DB;

class DuplicatePath implements Rule
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
        $data = DB::table('article')->where('path', $value)->count();
        if($data > 0) {
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
        return trans('validation.duplicate');
    }
}
