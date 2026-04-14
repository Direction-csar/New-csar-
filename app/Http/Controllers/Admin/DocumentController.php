<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PublicDocument;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class DocumentController extends Controller
{
    public function index(Request $request)
    {
        $query = PublicDocument::orderBy('created_at', 'desc');

        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }
        if ($request->filled('status')) {
            $query->where('is_published', $request->status === 'published');
        }

        $documents = $query->paginate(20);
        $stats = [
            'total'       => PublicDocument::count(),
            'published'   => PublicDocument::published()->count(),
            'recrutement' => PublicDocument::recrutement()->count(),
            'rapport'     => PublicDocument::rapport()->count(),
        ];

        return view('admin.documents.index', compact('documents', 'stats'));
    }

    public function create()
    {
        return view('admin.documents.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'title'        => 'required|string|max:255',
            'description'  => 'nullable|string',
            'type'         => 'required|in:recrutement,rapport,communique,appel_offre,autre',
            'file'         => 'required|file|mimes:pdf,doc,docx,ppt,pptx,xls,xlsx|max:20480',
            'published_at' => 'nullable|date',
            'expires_at'   => 'nullable|date',
            'is_published' => 'boolean',
        ]);

        try {
            $file = $request->file('file');
            $path = $file->store('documents/public', 'public');

            PublicDocument::create([
                'title'        => $request->title,
                'description'  => $request->description,
                'type'         => $request->type,
                'file_path'    => $path,
                'file_name'    => $file->getClientOriginalName(),
                'file_size'    => $file->getSize(),
                'published_at' => $request->published_at,
                'expires_at'   => $request->expires_at,
                'is_published' => $request->boolean('is_published'),
                'created_by'   => auth()->id(),
            ]);

            Log::info('Document créé', ['user_id' => auth()->id()]);
            return redirect()->route('admin.documents.index')->with('success', 'Document publié avec succès.');
        } catch (\Exception $e) {
            Log::error('Erreur création document', ['error' => $e->getMessage()]);
            return redirect()->back()->withInput()->with('error', 'Erreur : ' . $e->getMessage());
        }
    }

    public function edit($id)
    {
        $document = PublicDocument::findOrFail($id);
        return view('admin.documents.edit', compact('document'));
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'title'        => 'required|string|max:255',
            'description'  => 'nullable|string',
            'type'         => 'required|in:recrutement,rapport,communique,appel_offre,autre',
            'file'         => 'nullable|file|mimes:pdf,doc,docx,ppt,pptx,xls,xlsx|max:20480',
            'published_at' => 'nullable|date',
            'expires_at'   => 'nullable|date',
            'is_published' => 'boolean',
        ]);

        try {
            $document = PublicDocument::findOrFail($id);
            $data = [
                'title'        => $request->title,
                'description'  => $request->description,
                'type'         => $request->type,
                'published_at' => $request->published_at,
                'expires_at'   => $request->expires_at,
                'is_published' => $request->boolean('is_published'),
            ];

            if ($request->hasFile('file')) {
                if ($document->file_path) {
                    Storage::disk('public')->delete($document->file_path);
                }
                $file = $request->file('file');
                $data['file_path'] = $file->store('documents/public', 'public');
                $data['file_name'] = $file->getClientOriginalName();
                $data['file_size'] = $file->getSize();
            }

            $document->update($data);

            Log::info('Document mis à jour', ['user_id' => auth()->id(), 'document_id' => $id]);
            return redirect()->route('admin.documents.index')->with('success', 'Document mis à jour avec succès.');
        } catch (\Exception $e) {
            return redirect()->back()->withInput()->with('error', 'Erreur : ' . $e->getMessage());
        }
    }

    public function destroy($id)
    {
        try {
            $document = PublicDocument::findOrFail($id);
            if ($document->file_path) {
                Storage::disk('public')->delete($document->file_path);
            }
            $document->delete();
            Log::info('Document supprimé', ['user_id' => auth()->id(), 'document_id' => $id]);
            return redirect()->route('admin.documents.index')->with('success', 'Document supprimé avec succès.');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Erreur lors de la suppression.');
        }
    }

    public function download($id)
    {
        $document = PublicDocument::findOrFail($id);
        if (!$document->file_path || !Storage::disk('public')->exists($document->file_path)) {
            return redirect()->back()->with('error', 'Fichier non trouvé.');
        }
        return Storage::disk('public')->download($document->file_path, $document->file_name);
    }
}
