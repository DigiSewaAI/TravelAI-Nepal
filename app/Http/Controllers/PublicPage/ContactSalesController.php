<?php

namespace App\Http\Controllers\PublicPage;

use App\Http\Controllers\Controller;

class ContactSalesController extends Controller
{
    public function show()
    {
        return view('public.contact-sales');
    }
}