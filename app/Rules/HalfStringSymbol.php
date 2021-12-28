<?php
/**
 * 半角英数字を許可するValidation
 */
namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;

class HalfStringSymbol implements Rule
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
        return preg_match('/\A[a-zA-Z0-9-_]+\z/', $value);
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return trans('validation.halfstringsymbol');
    }
}
