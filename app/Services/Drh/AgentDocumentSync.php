<?php

namespace App\Services\Drh;

use App\Models\Personnel;
use App\Models\RhDocument;

/**
 * Propage une modification de la fiche agent vers ses documents RH.
 *
 * Seules les valeurs qui correspondaient encore à l'ancienne valeur par
 * défaut sont remplacées : une valeur saisie manuellement dans le
 * document (différente de l'ancienne fiche) est conservée. Les documents
 * signés ou archivés ne sont jamais touchés.
 */
class AgentDocumentSync
{
    public function __construct(
        private AgentDocumentData $defaults,
        private ContractService $contracts,
    ) {}

    /** @return int nombre de documents mis à jour */
    public function sync(Personnel $before, Personnel $after): int
    {
        $oldDefaults = $this->defaults->defaults($before);
        $newDefaults = $this->defaults->defaults($after);
        if (!$oldDefaults || !$newDefaults) {
            return 0;
        }

        $updated = 0;
        RhDocument::query()
            ->where('personnel_id', $after->id)
            ->whereNull('signed_at')
            ->whereNull('superseded_at')
            ->each(function (RhDocument $doc) use ($oldDefaults, $newDefaults, &$updated) {
                $data = $doc->data ?? [];
                $changed = false;

                foreach ($newDefaults as $key => $value) {
                    if (!array_key_exists($key, $data)) {
                        continue;
                    }
                    if ($this->same($data[$key], $oldDefaults[$key] ?? null)) {
                        $data[$key] = $value;
                        $changed = true;
                    }
                }

                if (!$changed) {
                    return;
                }

                $doc->update(['data' => $data]);
                $updated++;

                // Le PDF archivé des contrats doit refléter les nouvelles données.
                if ($doc->isContract() && $doc->file_path) {
                    rescue(fn () => $this->contracts->refreshStoredPdf($doc), report: false);
                }
            });

        return $updated;
    }

    private function same(mixed $a, mixed $b): bool
    {
        if ($a === $b) {
            return true;
        }
        if ($a === null || $b === null) {
            return $a === $b || $a === '' || $b === '';
        }

        return (string) $a === (string) $b;
    }
}
