<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;

/**
 * FAQ usagers et bailleurs de fonds.
 */
class FaqController extends Controller
{
    public function index()
    {
        $faqUsagers = __('faq.usagers');
        $faqBailleurs = __('faq.bailleurs');

        return view('public.faq.index', compact('faqUsagers', 'faqBailleurs'));
    }
}
