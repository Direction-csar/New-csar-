<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Archive;
use App\Models\ArchiveAccessLog;
use App\Models\ArchiveFolder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ArchiveController extends Controller
{
    public function index(Request $request)
    {
        $query = Archive::withTrashed()
            ->with('folder', 'creator', 'deleter')
            ->orderBy('created_at', 'desc');

        if ($request->filled('direction')) {
            $query->where('direction', $request->direction);
        }
        if ($request->filled('annee')) {
            $query->where('annee', $request->annee);
        }
        if ($request->filled('status')) {
            if ($request->status === 'deleted') {
                $query->whereNotNull('deleted_at');
            } else {
                $query->whereNull('deleted_at');
            }
        }
        if ($request->filled('q')) {
            $query->where(function ($q) use ($request) {
                $q->where('title', 'like', '%' . $request->q . '%')
                  ->orWhere('reference', 'like', '%' . $request->q . '%');
            });
        }

        $archives = $query->paginate(30);
        $directions = Archive::distinct()->pluck('direction');
        $annees = Archive::distinct()->pluck('annee');
        $stats = [
            'total' => Archive::withTrashed()->count(),
            'active' => Archive::whereNull('deleted_at')->count(),
            'deleted' => Archive::onlyTrashed()->count(),
        ];

        return view('admin.archives.index', compact('archives', 'directions', 'annees', 'stats', 'request'));
    }

    public function destroy($id)
    {
        $archive = Archive::withTrashed()->findOrFail($id);
        Storage::disk('public')->delete($archive->file_path);
        $archive->forceDelete();
        return redirect()->back()->with('success', 'Document définitivement supprimé.');
    }

    public function restore($id)
    {
        $archive = Archive::withTrashed()->findOrFail($id);
        $archive->restore();
        return redirect()->back()->with('success', 'Document restauré.');
    }

    public function logs(Request $request)
    {
        $query = ArchiveAccessLog::with('archive', 'user')->orderBy('created_at', 'desc');

        if ($request->filled('archive_id')) {
            $query->where('archive_id', $request->archive_id);
        }
        if ($request->filled('user_id')) {
            $query->where('user_id', $request->user_id);
        }
        if ($request->filled('action')) {
            $query->where('action', $request->action);
        }

        $logs = $query->paginate(50);
        return view('admin.archives.logs', compact('logs'));
    }
}
