<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LoginAdminMail extends Mailable
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
    public function build(Request $request)
    {
        $date = Carbon::now();
        $ip = $request-> ip();
        return $this
                    ->to(config('mail.admin_mail'))
                    ->subject(config('umekoset.site_name') . 'にログインしました ' . config('umekoset.separate') . ' ' . config('umekoset.site_name'))
                    ->view('mail.admin', compact('date', 'ip'));
    }
}
