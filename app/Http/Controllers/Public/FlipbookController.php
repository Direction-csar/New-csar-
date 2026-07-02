<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\SimReport;
use Illuminate\Support\Facades\Storage;

class FlipbookController extends Controller
{
    /**
     * Afficher un rapport en mode flipbook interactif
     */
    public function show($locale, $id)
    {
        $report = SimReport::where('status', 'published')
            ->where('is_public', true)
            ->findOrFail($id);

        if (!$report->document_file || !Storage::disk('public')->exists($report->document_file)) {
            abort(404, 'Document non disponible');
        }

        $report->incrementViewCount();

        $pdfUrl = asset('storage/' . $report->document_file);

        // Labels pour la catégorie
        $categoryLabels = [
            'rapport' => 'Rapport',
            'bulletin' => 'Bulletin SIM',
            'atlas' => 'Atlas',
            'etude' => 'Étude',
            'documents_officiels' => 'Document officiel',
        ];
        $report->categoryLabel = $categoryLabels[$report->category] ?? ucfirst($report->category);

        return view('public.flipbook', compact('report', 'pdfUrl'));
    }
}
