<?php

namespace App\Http\Controllers\PublicPage;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class ContactSalesController extends Controller
{
    public function show()
    {
        return view('public.contact-sales');
    }

    public function submit(Request $request)
    {
        $validated = $request->validate([
            'company_name'   => 'required|string|max:150',
            'contact_person' => 'required|string|max:100',
            'email'          => 'required|email|max:150',
            'phone'          => 'nullable|string|max:30',
            'requirements'   => 'required|string|max:2000',
        ]);

        try {
            Mail::to('info@travelainepal.com')
                ->send(new \App\Mail\EnterpriseInquiryMail($validated));

            return back()->with('enterprise_success', __('messages.enterprise_form_success'));
        } catch (\Throwable $e) {
            Log::error('Enterprise inquiry failed', [
                'email' => $validated['email'],
                'error' => $e->getMessage(),
            ]);
            return back()->with('enterprise_error', __('messages.enterprise_form_error'))->withInput();
        }
    }
}