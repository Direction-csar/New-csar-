<?php

namespace App\Http\Controllers\Drh;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class RessourcesHumainesController extends Controller
{
    public function formation()
    {
        return view('admin.drh.formation.index');
    }

    public function recrutement()
    {
        return view('admin.drh.recrutement.index');
    }

    public function relations()
    {
        return view('admin.drh.relations.index');
    }
}
