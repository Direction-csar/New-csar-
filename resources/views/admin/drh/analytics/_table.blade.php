{{-- Tableau générique paginé et triable.
     Variables : $columns, $labels, $rows, $paginator, $filters, $sortBase (URL de base sans sort/dir/page) --}}
@php
    $currentSort = $filters['sort'] ?? null;
    $currentDir  = $filters['dir'] ?? 'asc';
    $sortable    = ['nom','matricule','sexe','date_naissance','age','date_retraite','reste','departement','service','statut','date_recrutement','anciennete','type_contrat','date_debut_contrat','date_fin_contrat','jours_restants','jours_pris','nb_decisions','derniere_decision','jours_recup','nb_demandes','derniere_demande'];
    $qs = collect($filters)->except(['sort','dir','page'])->all();
@endphp

<div class="table-responsive an-table-wrap">
    <table class="table table-hover align-middle an-table mb-0">
        <thead>
            <tr>
                <th style="width:40px">#</th>
                @foreach($columns as $col)
                    @php
                        $isSorted = $currentSort === $col;
                        $nextDir  = $isSorted && $currentDir === 'asc' ? 'desc' : 'asc';
                    @endphp
                    <th class="{{ in_array($col, ['photo']) ? 'text-center' : '' }}">
                        @if(in_array($col, $sortable))
                            <a href="{{ $sortBase . '?' . http_build_query($qs + ['sort' => $col, 'dir' => $nextDir]) }}" class="an-sort {{ $isSorted ? 'active' : '' }}">
                                {{ $labels[$col] ?? $col }}
                                <i class="fas {{ $isSorted ? ($currentDir === 'asc' ? 'fa-sort-up' : 'fa-sort-down') : 'fa-sort' }}"></i>
                            </a>
                        @else
                            {{ $labels[$col] ?? $col }}
                        @endif
                    </th>
                @endforeach
                <th style="width:60px"></th>
            </tr>
        </thead>
        <tbody>
            @forelse($rows as $i => $r)
                <tr>
                    <td class="text-muted small">{{ $paginator->firstItem() + $i }}</td>
                    @foreach($columns as $col)
                        <td data-label="{{ $labels[$col] ?? $col }}">
                            @switch($col)
                                @case('photo')
                                    @if(!empty($r['photo']))
                                        <img src="{{ $r['photo'] }}" alt="" class="an-avatar">
                                    @else
                                        <span class="an-avatar an-avatar-empty"><i class="fas fa-user"></i></span>
                                    @endif
                                    @break
                                @case('nom')
                                    <a href="{{ route('admin.drh.personnel.show', $r['_id']) }}" class="fw-semibold text-decoration-none">{{ $r['nom'] ?? '—' }}</a>
                                    @break
                                @case('sexe')
                                    @php $g = $r['sexe'] ?? 'Non renseigné'; @endphp
                                    <span class="an-pill {{ $g === 'Femme' ? 'an-pill-f' : ($g === 'Homme' ? 'an-pill-m' : 'an-pill-nr') }}">{{ $g }}</span>
                                    @break
                                @case('type_contrat')
                                    @if(!empty($r['type_contrat']))
                                        <span class="an-pill an-pill-contrat">{{ $r['type_contrat'] }}</span>
                                    @else
                                        <span class="text-muted">—</span>
                                    @endif
                                    @break
                                @case('jours_restants')
                                    @if(isset($r['jours_restants']))
                                        <span class="an-pill {{ $r['jours_restants'] <= 30 ? 'an-pill-danger' : ($r['jours_restants'] <= 60 ? 'an-pill-warn' : 'an-pill-ok') }}">{{ $r['jours_restants'] }} j</span>
                                    @else
                                        <span class="text-muted">—</span>
                                    @endif
                                    @break
                                @case('reste')
                                    @if(!empty($r['reste']))
                                        <span class="an-pill {{ str_starts_with($r['reste'], 'Âge atteint') ? 'an-pill-danger' : 'an-pill-warn' }}">{{ $r['reste'] }}</span>
                                    @else
                                        <span class="text-muted">—</span>
                                    @endif
                                    @break
                                @case('departement')
                                    @if(!empty($r['departement']))
                                        <a href="{{ route('admin.drh.analytics.departement', $r['departement']) }}" class="text-decoration-none">{{ $r['departement'] }}</a>
                                    @else
                                        <span class="text-muted">—</span>
                                    @endif
                                    @break
                                @case('service')
                                    @if(!empty($r['service']))
                                        <a href="{{ route('admin.drh.analytics.service', $r['service']) }}" class="text-decoration-none">{{ $r['service'] }}</a>
                                    @else
                                        <span class="text-muted">—</span>
                                    @endif
                                    @break
                                @default
                                    {{ $r[$col] ?? '—' }}
                            @endswitch
                        </td>
                    @endforeach
                    <td class="text-end">
                        <a href="{{ route('admin.drh.personnel.show', $r['_id']) }}" class="btn btn-sm btn-light border" title="Voir la fiche agent"><i class="fas fa-eye"></i></a>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="{{ count($columns) + 2 }}">
                        <div class="an-empty">
                            <i class="fas fa-inbox"></i>
                            <div>Aucun agent ne correspond aux critères sélectionnés.</div>
                        </div>
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>

@if($paginator->hasPages())
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mt-3">
        <div class="text-muted small">
            Affichage {{ $paginator->firstItem() }}–{{ $paginator->lastItem() }} sur {{ $paginator->total() }}
        </div>
        {{ $paginator->onEachSide(1)->links('pagination::bootstrap-5') }}
    </div>
@endif
