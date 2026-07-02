<?php

namespace App\Http\Controllers;

use App\Models\ArchiveFolder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ArchiveFolderController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'direction' => 'required|string|in:CPM,DFC,DPSE,DRH,DTL',
            'parent_id' => 'nullable|exists:archive_folders,id',
        ]);

        $folder = ArchiveFolder::create([
            'name' => $request->name,
            'direction' => $request->direction,
            'parent_id' => $request->parent_id,
            'created_by' => Auth::id(),
        ]);

        return redirect()->back()->with('success', 'Dossier créé : ' . $folder->name);
    }
}
