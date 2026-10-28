<?php

namespace App\Http\Controllers;

use App\Models\ArchiveFolder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ArchiveFolderController extends Controller
{
    /**
     * Directions disposant d'un espace archives (portail ou admin).
     */
    private const ALLOWED_DIRECTIONS = [
        'CPM', 'DFC', 'DPSE', 'DRH', 'DTL', 'INSPECTEUR', 'COMPTABLE',
    ];

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'direction' => 'required|string|in:' . implode(',', self::ALLOWED_DIRECTIONS),
            'parent_id' => 'nullable|exists:archive_folders,id',
        ]);

        // Contrôle serveur : le champ "direction" est fourni par le formulaire
        // et donc falsifiable. Hors admin, l'utilisateur ne peut créer un
        // dossier que dans les archives de SA direction (rôle ou département).
        $user = Auth::user();
        $isAdmin = $user && in_array($user->role, ['admin', 'super_admin'], true);
        $ownsDirection = $user
            && (strtoupper((string) $user->role) === $request->direction
                || $user->department === $request->direction);

        if (!$isAdmin && !$ownsDirection) {
            abort(403, 'Vous ne pouvez créer un dossier que dans les archives de votre direction.');
        }

        // Le dossier parent doit appartenir à la même direction.
        if ($request->filled('parent_id')) {
            $parent = ArchiveFolder::findOrFail($request->parent_id);
            abort_unless($parent->direction === $request->direction, 422, 'Le dossier parent appartient à une autre direction.');
        }

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
