<?php

namespace App\Services\Drh;

use App\Models\Personnel;
use App\Models\RhDocument;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use RuntimeException;
use ZipArchive;

/**
 * Contrats CDI/CDD versionnés.
 *
 * Règles :
 * - un contrat signé n'est jamais modifié ni supprimé ; un changement produit
 *   une nouvelle version « Avenant à valider » que la DRH doit valider ;
 * - un contrat non signé dont les données ont changé est remplacé par une
 *   nouvelle version, l'ancienne reste archivée (superseded_at) ;
 * - chaque version garde son propre PDF sur le disque privé.
 */
class ContractService
{
    public const DISK = 'local';

    private const VIEWS = [
        'contrat-cdi' => 'admin.drh.documents.contrat_cdi',
        'contrat-cdd' => 'admin.drh.documents.contrat_cdd',
    ];

    private const LABELS = [
        'contrat-cdi' => 'Contrat CDI',
        'contrat-cdd' => 'Contrat CDD',
    ];

    public function typeFor(Personnel $agent): string
    {
        return strtoupper((string) $agent->type_contrat) === 'CDD' ? 'contrat-cdd' : 'contrat-cdi';
    }

    /** Données figées dans le contrat (sans objet Eloquent, sérialisables en JSON). */
    public function dataFor(Personnel $agent): array
    {
        $date = fn ($v) => $v ? Carbon::parse($v)->format('Y-m-d') : null;

        return [
            'date_embauche' => $date($agent->date_recrutement_csar),
            'date_debut' => $date($agent->date_debut_contrat ?? $agent->date_recrutement_csar),
            'date_fin' => $this->typeFor($agent) === 'contrat-cdd'
                ? ($date($agent->date_fin_contrat) ?? now()->endOfYear()->format('Y-m-d'))
                : null,
            'salaire_base' => $agent->salaire_base,
            'sursalaire' => $agent->sursalaire,
            'indemnite_transport' => $agent->indemnite_transport,
            'indemnite_fonction' => $agent->indemnite_fonction,
            'salaire_brut' => $agent->salaire_brut,
            'poste' => $agent->poste_actuel,
            'direction' => $agent->direction_service,
            'categorie' => $agent->categorie,
            'diplome' => $agent->formations_professionnelles ?? $agent->diplome_academique,
            'filiation' => $agent->filiation,
            'numero_identification' => $agent->numero_cni,
            'date_delivrance_id' => rescue(fn () => $date($agent->date_delivrance_id), null, false),
            'domicile_actuel' => $agent->adresse_complete,
            'situation_famille' => $agent->situation_matrimoniale,
            'nombre_epouses' => $agent->nombre_epouses,
        ];
    }

    public function render(Personnel $agent, string $type, array $data): string
    {
        return Pdf::loadView(self::VIEWS[$type], array_merge($data, ['personnel' => $agent, 'data' => $data]))
            ->setPaper('a4', 'portrait')
            ->output();
    }

    public function preview(Personnel $agent): string
    {
        return $this->render($agent, $this->typeFor($agent), $this->dataFor($agent));
    }

    public function current(Personnel $agent): ?RhDocument
    {
        return RhDocument::contracts()->current()->where('personnel_id', $agent->id)
            ->orderByDesc('version')->orderByDesc('id')->first();
    }

    /**
     * @param 'all'|'missing'|'selection' $mode
     * @return array{created: int, avenants: int, unchanged: int, skipped: int, errors: list<string>}
     */
    public function generateMany(iterable $agents, string $mode, ?int $userId): array
    {
        $stats = ['created' => 0, 'avenants' => 0, 'unchanged' => 0, 'skipped' => 0, 'errors' => []];
        foreach ($agents as $agent) {
            try {
                $result = $this->generate($agent, $userId, $mode === 'missing');
                $stats[$result]++;
            } catch (\Throwable $e) {
                report($e);
                $stats['errors'][] = $agent->prenoms_nom . ' : génération impossible';
            }
        }

        return $stats;
    }

    /**
     * @return 'created'|'avenants'|'unchanged'|'skipped'
     */
    public function generate(Personnel $agent, ?int $userId, bool $onlyIfMissing = false): string
    {
        return DB::transaction(function () use ($agent, $userId, $onlyIfMissing) {
            $type = $this->typeFor($agent);
            $data = $this->dataFor($agent);
            $current = RhDocument::contracts()->current()->where('personnel_id', $agent->id)
                ->lockForUpdate()->orderByDesc('version')->orderByDesc('id')->get();

            if ($onlyIfMissing && $current->isNotEmpty()) {
                return 'skipped';
            }

            $active = $current->first(fn ($d) => !$d->isPendingValidation()) ?? $current->first();
            if ($active && $active->type === $type && $this->sameData($active->data, $data)) {
                return 'unchanged';
            }

            // Un avenant déjà en attente identique : rien à refaire.
            $pending = $current->first(fn ($d) => $d->isPendingValidation());
            if ($pending && $pending->type === $type && $this->sameData($pending->data, $data)) {
                return 'unchanged';
            }

            $signed = $current->first(fn ($d) => $d->isSigned());
            $version = (int) RhDocument::contracts()->where('personnel_id', $agent->id)->max('version') + 1;

            if ($signed) {
                // Contrat signé : nouvelle version soumise à validation, le signé reste en vigueur.
                $pending?->forceFill(['superseded_at' => now(), 'statut' => RhDocument::STATUT_REMPLACE])->save();
                $this->store($agent, $type, $data, $version, RhDocument::STATUT_A_VALIDER, $userId);

                return 'avenants';
            }

            foreach ($current as $old) {
                $old->forceFill(['superseded_at' => now(), 'statut' => RhDocument::STATUT_REMPLACE])->save();
            }
            $this->store($agent, $type, $data, $version, 'Généré', $userId);

            return 'created';
        });
    }

    /** Validation DRH d'un avenant : il devient le contrat en vigueur, l'ancien est archivé. */
    public function validateAvenant(RhDocument $doc): void
    {
        if (!$doc->isContract() || !$doc->isPendingValidation() || $doc->superseded_at) {
            throw new RuntimeException('Ce document n\'est pas un avenant en attente de validation.');
        }

        DB::transaction(function () use ($doc) {
            RhDocument::contracts()->current()->where('personnel_id', $doc->personnel_id)
                ->whereKeyNot($doc->id)->lockForUpdate()->get()
                ->each(fn ($old) => $old->forceFill(['superseded_at' => now(), 'statut' => RhDocument::STATUT_REMPLACE])->save());
            $doc->forceFill(['statut' => 'Généré'])->save();
        });
    }

    public function markSigned(RhDocument $doc, ?int $userId): void
    {
        if (!$doc->isContract() || $doc->superseded_at || $doc->isPendingValidation()) {
            throw new RuntimeException('Seul le contrat en vigueur peut être marqué comme signé.');
        }
        if ($doc->isSigned()) {
            return;
        }
        $doc->forceFill(['signed_at' => now(), 'signed_by' => $userId, 'statut' => RhDocument::STATUT_SIGNE])->save();
    }

    /** PDF d'une version : fichier archivé, ou rendu depuis les données figées (anciens contrats). */
    public function pdfFor(RhDocument $doc): string
    {
        if ($doc->file_path && Storage::disk(self::DISK)->exists($doc->file_path)) {
            return Storage::disk(self::DISK)->get($doc->file_path);
        }
        $data = collect($doc->data ?? [])->except('personnel')->all();

        return $this->render($doc->personnel ?? new Personnel(), $doc->type, $data);
    }

    public function filenameFor(RhDocument $doc): string
    {
        $name = Str::slug($doc->personnel?->prenoms_nom ?? 'agent');

        return "{$name}-{$doc->type}-v{$doc->version}.pdf";
    }

    /** Archive ZIP des contrats en vigueur ; renvoie le chemin du fichier temporaire. */
    public function zip(Collection $documents): string
    {
        $path = tempnam(sys_get_temp_dir(), 'csar-contrats-');
        $zip = new ZipArchive();
        if ($zip->open($path, ZipArchive::OVERWRITE) !== true) {
            throw new RuntimeException('Impossible de créer l\'archive ZIP.');
        }
        $used = [];
        foreach ($documents as $doc) {
            $name = $this->filenameFor($doc);
            $name = isset($used[$name]) ? str_replace('.pdf', "-{$doc->id}.pdf", $name) : $name;
            $used[$name] = true;
            $zip->addFromString($name, $this->pdfFor($doc));
        }
        $zip->close();

        return $path;
    }

    private function store(Personnel $agent, string $type, array $data, int $version, string $statut, ?int $userId): RhDocument
    {
        $path = sprintf('rh/contrats/%d/%s-v%d-%s.pdf', $agent->id, $type, $version, now()->format('YmdHis'));
        Storage::disk(self::DISK)->put($path, $this->render($agent, $type, $data));

        try {
            return RhDocument::create([
                'personnel_id' => $agent->id,
                'type' => $type,
                'label' => self::LABELS[$type],
                'data' => $data,
                'statut' => $statut,
                'version' => $version,
                'file_path' => $path,
                'created_by' => $userId,
            ]);
        } catch (\Throwable $e) {
            Storage::disk(self::DISK)->delete($path);
            throw $e;
        }
    }

    private function sameData(?array $a, array $b): bool
    {
        $norm = fn ($arr) => collect($arr ?? [])->except('personnel')
            ->map(fn ($v) => is_numeric($v) ? round((float) $v, 2) : ($v === '' ? null : $v))
            ->sortKeys()->all();

        return $norm($a) == $norm($b);
    }
}
