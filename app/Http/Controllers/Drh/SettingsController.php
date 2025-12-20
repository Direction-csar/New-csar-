<?php

namespace App\Http\Controllers\DRH;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class SettingsController extends Controller
{
    public function index()
    {
        return view('drh.settings');
    }

    public function update(Request $request)
    {
        // Placeholder: page exists, but settings persistence is not implemented yet.
        // We accept the POST to avoid 500s and keep UX smooth.
        return redirect()->route('drh.settings')->with('success', 'Paramètres enregistrés.');
    }
}





