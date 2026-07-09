<?php

namespace App\Http\Controllers;

use App\Models\Archive;
use App\Models\ArchiveFolder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;

abstract class DirectionArchiveController extends Controller
{
    abstract protected function getDirection(): string;

    protected function getRoutePrefix(): string
    {
        return 'archives.' . strtolower($this->getDirection()) . '.';
    }

    /**
     * Retourne le nom du layout Blade a utiliser pour les vues archives.
     * Surcharger dans les sous-classes ayant un portail dedie (ex: Cpm, Dpse, Dtl).
     */
    protected function getLayout(): string
    {
        return 'layouts.admin';
    }

    public function index(Request $request)
    {
        $direction = $this->getDirection();
        $folders = ArchiveFolder::where('direction', $direction)
            ->whereNull('parent_id')
            ->with('children')
            ->get();

        $allFolders = ArchiveFolder::where('direction', $direction)
            ->orderBy('name')
            ->get(['id', 'name', 'parent_id']);

        $query = Archive::where('direction', $direction)
            ->with('folder', 'creator')
            ->whereNull('deleted_at');

        if ($request->filled('folder_id')) {
            $query->where('folder_id', $request->folder_id);
        }
        if ($request->filled('annee')) {
            $query->where('annee', $request->annee);
        }
        if ($request->filled('reference')) {
            $query->where('reference', 'like', '%' . $request->reference . '%');
        }
        if ($request->filled('q')) {
            $query->where(function ($q) use ($request) {
                $q->where('title', 'like', '%' . $request->q . '%')
                  ->orWhere('reference', 'like', '%' . $request->q . '%')
                  ->orWhere('description', 'like', '%' . $request->q . '%');
            });
        }
        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }
        if ($request->filled('mime_type')) {
            $query->where('mime_type', $request->mime_type);
        }
        if ($request->filled('created_by')) {
            $query->where('created_by', $request->created_by);
        }

        $archives = $query->orderBy('annee', 'desc')->orderBy('created_at', 'desc')->paginate(20);
        $annees = Archive::where('direction', $direction)->distinct()->pluck('annee');

        $mimeTypes = Archive::where('direction', $direction)->distinct()->pluck('mime_type');
        $creators = \App\Models\User::whereIn('id', function ($q) use ($direction) {
            $q->select('created_by')->from('archives')->where('direction', $direction);
        })->get(['id', 'name']);

        return view('archives.index', [
            'archives' => $archives,
            'folders' => $folders,
            'allFolders' => $allFolders,
            'annees' => $annees,
            'mimeTypes' => $mimeTypes,
            'creators' => $creators,
            'direction' => $direction,
            'request' => $request,
            'layout' => $this->getLayout(),
        ]);
    }

    public function store(Request $request)
    {
        $direction = $this->getDirection();
        Gate::authorize('create', \App\Models\Archive::class);

        $request->validate([
            'title' => 'required|string|max:255',
            'annee' => 'required|integer|min:2000|max:' . (now()->year + 1),
            'folder_id' => 'nullable|exists:archive_folders,id',
            'description' => 'nullable|string',
            'file' => 'required|file|mimes:pdf,doc,docx,xls,xlsx,ppt,pptx,jpg,jpeg,png|max:51200',
        ]);

        $file = $request->file('file');
        $path = $file->store('archives/' . strtolower($direction), 'public');

        $pages = null;
        if ($file->getMimeType() === 'application/pdf') {
            $pages = $this->countPdfPages($file->getRealPath());
        }

        $archive = Archive::create([
            'title' => $request->title,
            'description' => $request->description,
            'direction' => $direction,
            'folder_id' => $request->folder_id,
            'annee' => $request->annee,
            'file_path' => $path,
            'file_name' => $file->getClientOriginalName(),
            'file_size' => $file->getSize(),
            'mime_type' => $file->getMimeType(),
            'page_count' => $pages,
            'created_by' => Auth::id(),
        ]);

        return redirect()->back()->with('success', 'Document archivé : ' . $archive->reference);
    }

    public function update(Request $request, Archive $archive)
    {
        Gate::authorize('update', $archive);

        $request->validate([
            'title' => 'required|string|max:255',
            'file_name' => 'required|string|max:255',
            'annee' => 'required|integer|min:2000|max:' . (now()->year + 1),
            'folder_id' => 'nullable|exists:archive_folders,id',
            'description' => 'nullable|string',
        ]);

        $archive->update($request->only('title', 'file_name', 'annee', 'folder_id', 'description'));

        return redirect()->back()->with('success', 'Document mis à jour : ' . $archive->reference);
    }

    public function destroy(Archive $archive)
    {
        Gate::authorize('delete', $archive);

        $archive->update(['deleted_by' => Auth::id()]);
        $archive->delete();

        return redirect()->back()->with('success', 'Document supprimé : ' . $archive->reference);
    }

    public function show(Archive $archive)
    {
        Gate::authorize('view', $archive);

        $archive->accessLogs()->create([
            'user_id' => Auth::id(),
            'action' => 'view',
            'ip_address' => request()->ip(),
        ]);

        return view('archives.show', [
            'archive' => $archive,
            'direction' => $this->getDirection(),
            'layout' => $this->getLayout(),
        ]);
    }

    public function download(Archive $archive)
    {
        Gate::authorize('download', $archive);

        $archive->accessLogs()->create([
            'user_id' => Auth::id(),
            'action' => 'download',
            'ip_address' => request()->ip(),
        ]);

        return Storage::disk('public')->download($archive->file_path, $archive->file_name);
    }

    public function print(Archive $archive, Request $request)
    {
        Gate::authorize('print', $archive);

        $pages = $request->input('pages');
        $pageRanges = $this->parsePageRange($pages);
        $originalPath = Storage::disk('public')->path($archive->file_path);

        // If no pages specified or not PDF, return full file
        if (empty($pageRanges) || $archive->mime_type !== 'application/pdf') {
            $archive->accessLogs()->create([
                'user_id' => Auth::id(),
                'action' => 'print',
                'ip_address' => request()->ip(),
                'meta' => ['pages' => 'all'],
            ]);
            return Storage::disk('public')->download($archive->file_path);
        }

        try {
            if (!class_exists(\setasign\Fpdi\Fpdi::class)) {
                // FPDI non installé : fallback vers téléchargement complet
                $archive->accessLogs()->create([
                    'user_id' => Auth::id(),
                    'action' => 'print',
                    'ip_address' => request()->ip(),
                    'meta' => ['pages' => $pages, 'note' => 'FPDI not installed, full file returned'],
                ]);
                return Storage::disk('public')->download($archive->file_path);
            }

            $pdf = new \setasign\Fpdi\Fpdi();
            $pageCount = $pdf->setSourceFile($originalPath);

            foreach ($pageRanges as $pageNo) {
                if ($pageNo <= $pageCount) {
                    $tpl = $pdf->importPage($pageNo);
                    $pdf->addPage();
                    $pdf->useTemplate($tpl);
                }
            }

            $archive->accessLogs()->create([
                'user_id' => Auth::id(),
                'action' => 'print',
                'ip_address' => request()->ip(),
                'meta' => ['pages' => $pages],
            ]);

            $output = $pdf->Output('S');
            return response($output, 200, [
                'Content-Type' => 'application/pdf',
                'Content-Disposition' => 'attachment; filename="' . $archive->file_name . '"',
            ]);
        } catch (\Exception $e) {
            return Storage::disk('public')->download($archive->file_path);
        }
    }

    private function countPdfPages($path)
    {
        try {
            $pdf = file_get_contents($path);
            $pages = preg_match_all("/\/Type\s*\/Page[^s]/", $pdf, $dummy);
            return $pages ?: null;
        } catch (\Exception $e) {
            return null;
        }
    }

    private function parsePageRange($input)
    {
        if (empty($input)) {
            return [];
        }

        $pages = [];
        foreach (explode(',', $input) as $part) {
            $part = trim($part);
            if (str_contains($part, '-')) {
                [$start, $end] = explode('-', $part);
                $pages = array_merge($pages, range((int) $start, (int) $end));
            } else {
                $pages[] = (int) $part;
            }
        }
        return array_unique(array_filter($pages));
    }
}
