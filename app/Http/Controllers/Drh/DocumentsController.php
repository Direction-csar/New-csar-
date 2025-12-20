<?php

namespace App\Http\Controllers\DRH;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use App\Models\Personnel;
use App\Models\HRDocument;
use Illuminate\Support\Str;

class DocumentsController extends Controller
{
    /**
     * Afficher la liste des documents RH
     */
    public function index()
    {
        try {
            $documents = HRDocument::with('personnel')
                ->orderBy('created_at', 'desc')
                ->paginate(15);
            return view('drh.documents.index', compact('documents'));

        } catch (\Exception $e) {
            Log::error('Erreur dans DRH DocumentsController@index', [
                'error' => $e->getMessage(),
                'user_id' => auth()->id()
            ]);

            return view('drh.documents.index', ['documents' => collect()]);
        }
    }

    /**
     * Formulaire de création
     */
    public function create()
    {
        try {
            $personnel = Personnel::orderBy('prenoms_nom')->get();
            return view('drh.documents.create', compact('personnel'));
        } catch (\Exception $e) {
            Log::error('Erreur dans DRH DocumentsController@create', [
                'error' => $e->getMessage(),
                'user_id' => auth()->id()
            ]);
            return redirect()->route('drh.documents.index')
                ->withErrors(['email' => 'Impossible d’ouvrir le formulaire de création de document.']);
        }
    }

    /**
     * Enregistrer un document RH
     */
    public function store(Request $request)
    {
        $request->validate([
            'personnel_id' => ['required', 'integer', 'exists:personnel,id'],
            'type' => ['required', 'string'],
            'titre' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'date_emission' => ['required', 'date'],
            'date_expiration' => ['nullable', 'date'],
            'commentaires' => ['nullable', 'string'],
            'fichier' => ['nullable', 'file', 'mimes:pdf,doc,docx,ppt,pptx', 'max:20480'],
        ]);

        try {
            $fileName = null;
            $extension = null;
            $size = null;

            if ($request->hasFile('fichier')) {
                $file = $request->file('fichier');
                $extension = strtolower($file->getClientOriginalExtension());
                $size = $file->getSize();
                $safeBase = Str::slug(pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME));
                $fileName = now()->format('Ymd_His') . '_' . $safeBase . '.' . $extension;
                $file->storeAs('public/hr-documents', $fileName);
            }

            HRDocument::create([
                'personnel_id' => (int) $request->input('personnel_id'),
                'type' => $request->input('type'),
                'titre' => $request->input('titre'),
                'description' => $request->input('description'),
                'fichier' => $fileName ?: '',
                'extension' => $extension ?: '',
                'taille_fichier' => $size ?: 0,
                'date_emission' => $request->input('date_emission'),
                'date_expiration' => $request->input('date_expiration'),
                'statut' => 'actif',
                'commentaires' => $request->input('commentaires'),
                'cree_par' => auth()->id(),
            ]);

            return redirect()->route('drh.documents.index')->with('success', 'Document créé avec succès.');
        } catch (\Exception $e) {
            Log::error('Erreur dans DRH DocumentsController@store', [
                'error' => $e->getMessage(),
                'user_id' => auth()->id()
            ]);

            return redirect()->back()->withInput()->withErrors([
                'email' => 'Erreur lors de l’enregistrement du document.'
            ]);
        }
    }

    /**
     * Télécharger un document
     */
    public function download(HRDocument $document)
    {
        if (!$document->fichier) {
            return redirect()->back()->withErrors(['email' => 'Aucun fichier associé à ce document.']);
        }

        $path = 'public/hr-documents/' . $document->fichier;
        if (!Storage::exists($path)) {
            return redirect()->back()->withErrors(['email' => 'Fichier introuvable sur le serveur.']);
        }

        return Storage::download($path, $document->fichier);
    }

    /**
     * Actions non implémentées: éviter des 500 si un lien est cliqué.
     */
    public function show(HRDocument $document)
    {
        return redirect()->route('drh.documents.index')
            ->with('success', 'Vue détail non implémentée (bientôt disponible).');
    }

    public function edit(HRDocument $document)
    {
        return redirect()->route('drh.documents.index')
            ->with('success', 'Édition non implémentée (bientôt disponible).');
    }

    public function update(Request $request, HRDocument $document)
    {
        return redirect()->route('drh.documents.index')
            ->with('success', 'Mise à jour non implémentée (bientôt disponible).');
    }

    public function destroy(HRDocument $document)
    {
        try {
            if ($document->fichier) {
                Storage::delete('public/hr-documents/' . $document->fichier);
            }
            $document->delete();
            return redirect()->route('drh.documents.index')->with('success', 'Document supprimé.');
        } catch (\Exception $e) {
            Log::error('Erreur dans DRH DocumentsController@destroy', [
                'error' => $e->getMessage(),
                'user_id' => auth()->id()
            ]);
            return redirect()->back()->withErrors(['email' => 'Erreur lors de la suppression du document.']);
        }
    }
}

