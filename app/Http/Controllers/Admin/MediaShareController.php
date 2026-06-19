<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\MediaEvent;
use App\Models\MediaFile;
use BaconQrCode\Renderer\ImageRenderer;
use BaconQrCode\Renderer\Image\SvgImageBackEnd;
use BaconQrCode\Renderer\RendererStyle\RendererStyle;
use BaconQrCode\Writer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class MediaShareController extends Controller
{
    private function getCurrentUserId()
    {
        return auth('ctc')->check() ? auth('ctc')->id() : auth()->id();
    }

    private function getRoutePrefix()
    {
        return request()->routeIs('ctc.*') ? 'ctc' : 'admin';
    }

    /**
     * Liste des événements/albums
     */
    public function index(Request $request)
    {
        $query = MediaEvent::withCount(['files', 'images', 'videos', 'downloads']);

        if ($request->filled('search')) {
            $s = $request->search;
            $query->where(function ($q) use ($s) {
                $q->where('title', 'like', "%{$s}%")
                  ->orWhere('description', 'like', "%{$s}%");
            });
        }

        $events = $query->orderBy('created_at', 'desc')->get();

        $stats = [
            'total_events'    => $events->count(),
            'total_medias'    => (int) $events->sum('files_count'),
            'total_views'     => (int) $events->sum('views'),
            'total_downloads' => (int) $events->sum('downloads_count'),
        ];

        return view('admin.media-share.index', compact('events', 'stats'));
    }

    public function create()
    {
        return view('admin.media-share.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'title'        => 'required|string|max:255',
            'description'  => 'nullable|string',
            'event_date'   => 'nullable|date',
            'cover_image'  => 'nullable|image|mimes:jpeg,png,jpg,webp|max:4096',
        ]);

        try {
            $coverPath = null;
            if ($request->hasFile('cover_image')) {
                $coverPath = $request->file('cover_image')->store('media-share/covers', 'public');
            }

            $event = MediaEvent::create([
                'title'       => $request->title,
                'slug'        => MediaEvent::generateUniqueSlug($request->title),
                'description' => $request->description,
                'event_date'  => $request->event_date,
                'cover_image' => $coverPath,
                'status'      => 'active',
                'created_by'  => $this->getCurrentUserId(),
            ]);

            Log::info('Événement média créé', ['user_id' => $this->getCurrentUserId(), 'event_id' => $event->id]);

            return redirect()->route($this->getRoutePrefix() . '.media-share.show', $event->id)
                ->with('success', 'Événement créé. Vous pouvez maintenant ajouter des photos et vidéos.');
        } catch (\Exception $e) {
            Log::error('Erreur création événement média', ['error' => $e->getMessage()]);
            return redirect()->back()->withInput()->with('error', 'Erreur lors de la création de l\'événement.');
        }
    }

    /**
     * Détail de l'événement + médias + QR Code
     */
    public function show($id)
    {
        $event = MediaEvent::with('files')->findOrFail($id);

        $publicUrl = route('public.media.album', $event->slug);
        $qrSvg = $this->renderQrSvg($publicUrl);

        return view('admin.media-share.show', compact('event', 'publicUrl', 'qrSvg'));
    }

    public function edit($id)
    {
        $event = MediaEvent::findOrFail($id);
        return view('admin.media-share.edit', compact('event'));
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'title'       => 'required|string|max:255',
            'description' => 'nullable|string',
            'event_date'  => 'nullable|date',
            'status'      => 'required|in:active,inactive',
            'cover_image' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:4096',
        ]);

        try {
            $event = MediaEvent::findOrFail($id);

            $data = [
                'title'       => $request->title,
                'description' => $request->description,
                'event_date'  => $request->event_date,
                'status'      => $request->status,
            ];

            if ($request->hasFile('cover_image')) {
                if ($event->cover_image && Storage::disk('public')->exists($event->cover_image)) {
                    Storage::disk('public')->delete($event->cover_image);
                }
                $data['cover_image'] = $request->file('cover_image')->store('media-share/covers', 'public');
            }

            $event->update($data);

            return redirect()->route($this->getRoutePrefix() . '.media-share.show', $event->id)
                ->with('success', 'Événement mis à jour avec succès.');
        } catch (\Exception $e) {
            Log::error('Erreur mise à jour événement média', ['error' => $e->getMessage()]);
            return redirect()->back()->with('error', 'Erreur lors de la mise à jour.');
        }
    }

    public function destroy($id)
    {
        try {
            $event = MediaEvent::with('files')->findOrFail($id);

            foreach ($event->files as $file) {
                if ($file->file_path && Storage::disk('public')->exists($file->file_path)) {
                    Storage::disk('public')->delete($file->file_path);
                }
            }
            if ($event->cover_image && Storage::disk('public')->exists($event->cover_image)) {
                Storage::disk('public')->delete($event->cover_image);
            }

            $event->delete();

            return redirect()->route($this->getRoutePrefix() . '.media-share.index')
                ->with('success', 'Événement et médias supprimés avec succès.');
        } catch (\Exception $e) {
            Log::error('Erreur suppression événement média', ['error' => $e->getMessage()]);
            return redirect()->back()->with('error', 'Erreur lors de la suppression.');
        }
    }

    /**
     * Téléverser des médias (images + vidéos) dans un événement
     */
    public function uploadMedia(Request $request, $id)
    {
        $request->validate([
            'files'   => 'required|array',
            'files.*' => 'file|mimes:jpeg,png,jpg,webp,gif,mp4,mov,avi,webm|max:153600', // 150 Mo max/fichier
        ], [
            'files.required' => 'Veuillez sélectionner au moins un fichier.',
            'files.*.mimes'  => 'Formats autorisés: images (jpg, png, webp, gif) et vidéos (mp4, mov, avi, webm).',
            'files.*.max'    => 'Chaque fichier ne doit pas dépasser 150 Mo.',
        ]);

        try {
            $event = MediaEvent::findOrFail($id);
            $count = 0;

            foreach ($request->file('files') as $file) {
                $mime = $file->getMimeType();
                $type = str_starts_with((string) $mime, 'video') ? 'video' : 'image';
                $path = $file->store('media-share/' . $event->id . '/' . $type . 's', 'public');

                MediaFile::create([
                    'media_event_id' => $event->id,
                    'type'           => $type,
                    'file_path'      => $path,
                    'file_name'      => $file->getClientOriginalName(),
                    'file_size'      => $file->getSize(),
                    'mime_type'      => $mime,
                ]);
                $count++;
            }

            Log::info('Médias ajoutés à un événement', ['user_id' => $this->getCurrentUserId(), 'event_id' => $event->id, 'count' => $count]);

            return redirect()->route($this->getRoutePrefix() . '.media-share.show', $event->id)
                ->with('success', $count . ' fichier(s) ajouté(s) avec succès.');
        } catch (\Exception $e) {
            Log::error('Erreur upload médias', ['error' => $e->getMessage()]);
            return redirect()->back()->with('error', 'Erreur lors du téléversement des fichiers.');
        }
    }

    public function destroyMedia($id, $fileId)
    {
        try {
            $file = MediaFile::where('media_event_id', $id)->findOrFail($fileId);

            if ($file->file_path && Storage::disk('public')->exists($file->file_path)) {
                Storage::disk('public')->delete($file->file_path);
            }
            $file->delete();

            return redirect()->route($this->getRoutePrefix() . '.media-share.show', $id)
                ->with('success', 'Fichier supprimé avec succès.');
        } catch (\Exception $e) {
            Log::error('Erreur suppression média', ['error' => $e->getMessage()]);
            return redirect()->back()->with('error', 'Erreur lors de la suppression du fichier.');
        }
    }

    /**
     * Télécharger le QR Code SVG de l'événement
     */
    public function downloadQr($id)
    {
        $event = MediaEvent::findOrFail($id);
        $publicUrl = route('public.media.album', $event->slug);
        $svg = $this->renderQrSvg($publicUrl, 600);

        $filename = 'qrcode-' . $event->slug . '.svg';

        return response($svg, 200, [
            'Content-Type'        => 'image/svg+xml',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ]);
    }

    protected function renderQrSvg(string $url, int $size = 240): string
    {
        $renderer = new ImageRenderer(new RendererStyle($size, 1), new SvgImageBackEnd());
        $writer = new Writer($renderer);
        return $writer->writeString($url);
    }
}
