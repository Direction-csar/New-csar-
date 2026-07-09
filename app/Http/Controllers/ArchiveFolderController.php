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

    public function update(Request $request, ArchiveFolder $folder)
    {
        if (!in_array(Auth::user()->role, ['admin', 'super_admin'], true)) {
            abort(403);
        }

        $request->validate([
            'name' => 'required|string|max:255',
            'parent_id' => 'nullable|exists:archive_folders,id',
        ]);

        $folder->update($request->only('name', 'parent_id'));

        return redirect()->back()->with('success', 'Dossier renommé : ' . $folder->name);
    }

    public function destroy(ArchiveFolder $folder)
    {
        if (!in_array(Auth::user()->role, ['admin', 'super_admin'], true)) {
            abort(403);
        }

        if ($folder->archives()->count() > 0 || $folder->children()->count() > 0) {
            return redirect()->back()->with('error', 'Impossible de supprimer un dossier qui contient des documents ou des sous-dossiers.');
        }

        $folder->delete();

        return redirect()->back()->with('success', 'Dossier supprimé.');
    }
}
