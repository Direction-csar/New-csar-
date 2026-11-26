@php
    $tab = $tab ?? '';
    $link = fn (string $t) => $tab === $t
        ? 'nav-link active fw-bold'
        : 'nav-link';
@endphp
<ul class="nav nav-pills mb-3">
    <li class="nav-item"><a class="{{ $link('dashboard') }}" href="{{ route('comptable.carburant') }}"><i class="fas fa-gas-pump me-1"></i>Soldes</a></li>
    <li class="nav-item"><a class="{{ $link('entries') }}" href="{{ route('comptable.carburant.entries.index') }}"><i class="fas fa-arrow-down me-1"></i>Entrées</a></li>
    <li class="nav-item"><a class="{{ $link('exits') }}" href="{{ route('comptable.carburant.exits.index') }}"><i class="fas fa-arrow-up me-1"></i>Sorties</a></li>
    <li class="nav-item"><a class="{{ $link('reimbs') }}" href="{{ route('comptable.carburant.reimbs.index') }}"><i class="fas fa-receipt me-1"></i>Remboursements</a></li>
</ul>
