<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;

class LoginMail extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        $date = Carbon::now();
        return $this
                    ->to(Auth::user()->email)
                    ->subject(config('umekoset.site_name') . 'にログインしました ' . config('umekoset.separate') . ' ' . config('umekoset.site_name'))
                    ->view('mail.login', compact('date'));
    }
}
