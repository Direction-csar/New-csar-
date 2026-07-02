<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\MediaDownload;
use App\Models\MediaEvent;
use App\Models\MediaFile;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use ZipArchive;

class MediaAlbumController extends Controller
{
    /**
     * Afficher l'album public (accès via QR Code)
     */
    public function show(Request $request, string $slug)
    {
        $event = MediaEvent::with(['images', 'videos', 'documents'])
            ->where('slug', $slug)
            ->where('status', 'active')
            ->firstOrFail();

        // Comptage des vues / scans (1 par session)
        $sessionKey = 'media_viewed_' . $event->id;
        if (!$request->session()->has($sessionKey)) {
            $event->increment('views');
            $request->session()->put($sessionKey, true);
        }

        return view('public.media.album', compact('event'));
    }

    /**
     * Télécharger un fichier individuel
     */
    public function downloadFile(Request $request, string $slug, $fileId)
    {
        $event = MediaEvent::where('slug', $slug)->where('status', 'active')->firstOrFail();
        $file = MediaFile::where('media_event_id', $event->id)->findOrFail($fileId);

        if (!Storage::disk('public')->exists($file->file_path)) {
            abort(404);
        }

        $file->increment('downloads');
        MediaDownload::create([
            'media_event_id' => $event->id,
            'media_file_id'  => $file->id,
            'ip_address'     => $request->ip(),
            'kind'           => 'file',
        ]);

        return Storage::disk('public')->download($file->file_path, $file->file_name);
    }

    /**
     * Télécharger tout (images ou vidéos) en ZIP
     */
    public function downloadZip(Request $request, string $slug, string $kind)
    {
        $event = MediaEvent::where('slug', $slug)->where('status', 'active')->firstOrFail();

        $typeMap = ['images' => 'image', 'videos' => 'video', 'documents' => 'document'];
        $type = $typeMap[$kind] ?? 'image';
        $files = MediaFile::where('media_event_id', $event->id)->where('type', $type)->get();

        if ($files->isEmpty()) {
            return redirect()->back()->with('error', 'Aucun fichier à télécharger.');
        }

        $tmpDir = storage_path('app/tmp');
        if (!is_dir($tmpDir)) {
            @mkdir($tmpDir, 0775, true);
        }

        $zipName = \Illuminate\Support\Str::slug($event->title) . '-' . $kind . '-' . time() . '.zip';
        $zipPath = $tmpDir . DIRECTORY_SEPARATOR . $zipName;

        $zip = new ZipArchive();
        if ($zip->open($zipPath, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
            return redirect()->back()->with('error', 'Impossible de créer l\'archive.');
        }

        foreach ($files as $file) {
            $abs = Storage::disk('public')->path($file->file_path);
            if (is_file($abs)) {
                $zip->addFile($abs, $file->file_name);
            }
        }
        $zip->close();

        $event->increment('views', 0); // no-op safety
        MediaDownload::create([
            'media_event_id' => $event->id,
            'media_file_id'  => null,
            'ip_address'     => $request->ip(),
            'kind'           => match ($type) {
                'video' => 'zip_videos',
                'document' => 'zip_documents',
                default => 'zip_images',
            },
        ]);

        return response()->download($zipPath, $zipName)->deleteFileAfterSend(true);
    }
}
