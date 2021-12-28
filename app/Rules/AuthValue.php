<?php
/**
 * 権限値の存在チェックを行うValidation
 */
namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;

class AuthValue implements Rule
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
        return array_key_exists($value, config('umekoset.auth'));
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
