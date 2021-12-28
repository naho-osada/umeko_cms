<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Http\Request;
use App\Mail\LoginMail;
use App\Mail\LoginAdminMail;
use Illuminate\Support\Facades\Mail;

class LoginController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Login Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles authenticating users for the application and
    | redirecting them to your home screen. The controller uses a trait
    | to conveniently provide its functionality to your applications.
    |
    */

    use AuthenticatesUsers;

    /**
     * Where to redirect users after login.
     *
     * @var string
     */
    protected $redirectTo = '/admin/top';

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('guest')->except('logout');
    }

    protected function loggedOut()
    {
        return redirect(route('login'));
    }

    /**
     * credentials
     * Captchaのvalidationを追加
     * @access public
     * @param Request $request
     */
    protected function credentials(Request $request)
    {
        // ログイン試験ではcaptcha判定を無視する
        if (!app()->runningUnitTests()) {
            $request->validate(['captcha' => 'required|captcha']);
        }
        return $request->only('email', 'password');
    }

    /**
     * authenticated
     * ログイン後、ログインユーザーと管理者にメールを送信する機能
     * @access public
     */
    public function authenticated()
    {
        // ログインした人と管理者にメールを送る
        if (!app()->runningUnitTests()) {
            Mail::send(new LoginMail);
            Mail::send(new LoginAdminMail);
        }
    }
}
