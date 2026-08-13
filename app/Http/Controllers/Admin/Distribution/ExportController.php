<?php

namespace App\Http\Controllers\Admin\Distribution;

use App\Http\Controllers\Controller;
use App\Models\Beneficiaire;
use App\Models\BonMatiere;
use App\Models\Campaign;
use App\Models\Planning;
use App\Models\Ticket;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ExportController extends Controller
{
    public function index()
    {
        return view('admin.distribution.exports.index');
    }

    public function campaignsCsv()
    {
        $headers = ['Nom', 'Description', 'Date début', 'Date fin', 'Stock initial (kg)', 'Exécuté (kg)', 'Statut'];
        return $this->streamCsv('campagnes.csv', $headers, function ($writer) {
            Campaign::orderBy('name')->each(function ($c) use ($writer) {
                $writer([
                    $c->name,
                    $c->description,
                    $c->start_date?->format('Y-m-d'),
                    $c->end_date?->format('Y-m-d'),
                    $c->initial_stock_kg,
                    $c->executed_stock_kg,
                    $c->status,
                ]);
            });
        });
    }

    public function planningsCsv()
    {
        $headers = ['Campagne', 'Nom', 'Catégorie', 'Quota planifié (kg)', 'Exécuté (kg)', 'Seuil alerte (kg)', 'Entrepôt', 'Statut'];
        return $this->streamCsv('plannings.csv', $headers, function ($writer) {
            Planning::with('campaign', 'warehouse')->orderBy('name')->each(function ($p) use ($writer) {
                $writer([
                    $p->campaign?->name,
                    $p->name,
                    $p->category,
                    $p->planned_quota_kg,
                    $p->executed_quota_kg,
                    $p->alert_threshold_kg,
                    $p->warehouse?->name,
                    $p->status,
                ]);
            });
        });
    }

    public function beneficiairesCsv()
    {
        $headers = ['Planning', 'Nom', 'Téléphone', 'CNI', 'Adresse', 'Catégorie', 'Quantité (kg)', 'Statut'];
        return $this->streamCsv('beneficiaires.csv', $headers, function ($writer) {
            Beneficiaire::with('planning', 'bonMatieres')->orderBy('name')->each(function ($b) use ($writer) {
                $writer([
                    $b->planning?->name,
                    $b->name,
                    $b->phone,
                    $b->cni,
                    $b->address,
                    $b->category,
                    $b->bonMatieres->first()?->quantite_kg,
                    $b->status,
                ]);
            });
        });
    }

    public function bonMatieresCsv()
    {
        $headers = ['N° bon', 'Bénéficiaire', 'Planning', 'Quantité (kg)', 'Statut', 'Attributé le', 'Livré le'];
        return $this->streamCsv('bons-matiere.csv', $headers, function ($writer) {
            BonMatiere::with('beneficiaire', 'planning')->orderBy('numero_bon')->each(function ($bon) use ($writer) {
                $writer([
                    $bon->numero_bon,
                    $bon->beneficiaire?->name,
                    $bon->planning?->name,
                    $bon->quantite_kg,
                    $bon->statut,
                    $bon->attributed_at?->format('Y-m-d H:i'),
                    $bon->delivered_at?->format('Y-m-d H:i'),
                ]);
            });
        });
    }

    public function ticketsCsv()
    {
        $headers = ['Code', 'N° bon', 'Bénéficiaire', 'Utilisé', 'Utilisé le'];
        return $this->streamCsv('tickets.csv', $headers, function ($writer) {
            Ticket::with('bonMatiere.beneficiaire')->orderBy('created_at', 'desc')->each(function ($t) use ($writer) {
                $writer([
                    $t->code,
                    $t->bonMatiere?->numero_bon,
                    $t->bonMatiere?->beneficiaire?->name,
                    $t->used ? 'Oui' : 'Non',
                    $t->used_at?->format('Y-m-d H:i'),
                ]);
            });
        });
    }

    private function streamCsv(string $filename, array $headers, callable $rowsCallback): StreamedResponse
    {
        $response = new StreamedResponse(function () use ($headers, $rowsCallback) {
            $handle = fopen('php://output', 'w');
            fprintf($handle, chr(0xEF) . chr(0xBB) . chr(0xBF));
            fputcsv($handle, $headers, ';');
            $rowsCallback(function ($row) use ($handle) {
                fputcsv($handle, $row, ';');
            });
            fclose($handle);
        });

        $response->headers->set('Content-Type', 'text/csv; charset=utf-8');
        $response->headers->set('Content-Disposition', 'attachment; filename="' . $filename . '"');

        return $response;
    }
}
