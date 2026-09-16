<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\SendsPasswordResetEmails;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class ForgotPasswordController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Password Reset Controller
    |--------------------------------------------------------------------------
    |
    | This controller is responsible for handling password reset emails and
    | includes a trait which assists in sending these notifications from
    | your application to your users. Feel free to explore this trait.
    |
    */

        use SendsPasswordResetEmails {
        sendResetLinkEmail as protected traitSendResetLinkEmail;
    }

    /**
     * Display the form to request a password reset link.
     *
     * @return \Illuminate\View\View
     */
    public function showLinkRequestForm()
    {
        return view('auth.passwords.email');
    }

    /**
     * FIX-07: Log password-reset security event (hashed identity only).
     * Records the request, not account existence, to avoid enumeration.
     * Delegates actual behavior to trait (no duplicate validation).
     */
    public function sendResetLinkEmail(Request $request)
    {
        Log::info('Password reset requested', [
            'email_hash' => hash('sha256', strtolower(trim($request->input('email', '')))),
            'ip_hash'    => hash('sha256', $request->ip() ?? 'unknown'),
        ]);

        return $this->traitSendResetLinkEmail($request);
    }

    // (Optional) Override the broker if needed
    // public function broker()
    // {
    //     return Password::broker('users');
    // }
}