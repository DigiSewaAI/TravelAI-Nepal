<?php

namespace App\Http\Controllers;

use App\Models\Agency;
use App\Models\Plan;
use App\Models\Trek;
use Illuminate\Http\Request;

class PageController extends Controller
{
    /**
     * Show the features page.
     */
    public function features()
    {
        return view('pages.features');
    }

    /**
     * Show the "How It Works" page.
     */
    public function howItWorks()
    {
        return view('pages.how-it-works');
    }

    /**
     * Show the agencies list page (LEGACY – to be deprecated).
     */
    public function agencies(Request $request)
    {
        $query = Agency::query();

        if ($request->filled('search')) {
            $query->where('name', 'like', '%' . $request->search . '%')
                  ->orWhere('email', 'like', '%' . $request->search . '%');
        }

        $agencies = $query->paginate(12)->appends($request->query());

        return view('pages.agencies', compact('agencies'));
    }

    /**
     * Show the pricing page.
     */
    public function pricing()
    {
        $plans = Plan::all();
        return view('public.pricing', compact('plans'));
    }

    // ========================================
    // 🔥 NEW FOOTER PAGES (Company & Legal)
    // ========================================

    /**
     * Show the About page.
     */
    public function about()
    {
        return view('pages.about');
    }

    /**
     * Show the Careers page.
     */
    public function careers()
    {
        return view('pages.careers');
    }

    /**
     * Show the Press page.
     */
    public function press()
    {
        return view('pages.press');
    }

    /**
     * Show the Contact page.
     */
    public function contact()
    {
        return view('pages.contact');
    }

    /**
     * Show the Privacy Policy page.
     */
    public function privacy()
    {
        return view('pages.privacy');
    }

    /**
     * Show the Terms of Service page.
     */
    public function terms()
    {
        return view('pages.terms');
    }

    /**
     * Show the GDPR & Data Safety page.
     */
    public function gdpr()
    {
        return view('pages.gdpr');
    }
        public function submitContact(Request $request)
    {
        $validated = $request->validate([
            'name'    => 'required|string|max:100',
            'email'   => 'required|email|max:150',
            'subject' => 'required|string|max:200',
            'message' => 'required|string|max:2000',
        ]);

        try {
            \Illuminate\Support\Facades\Mail::to('support@travelainepal.com')
                ->send(new \App\Mail\ContactInquiryMail($validated));

            return back()->with('contact_success', __('messages.contact_form_success'));
        } catch (\Throwable $e) {
            \Log::error('Contact form submission failed', [
                'email' => $validated['email'],
                'error' => $e->getMessage(),
            ]);
            return back()->with('contact_error', __('messages.contact_form_error'))->withInput();
        }
    }
}