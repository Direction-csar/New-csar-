<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;

class DonController extends Controller
{
    public function index()
    {
        return view('public.don');
    }
}
