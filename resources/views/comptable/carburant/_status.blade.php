@php
    $labels = [
        'draft' => ['Brouillon', 'secondary'],
        'validated' => ['Validé', 'success'],
        'rejected' => ['Rejeté', 'danger'],
        'cancelled' => ['Annulé', 'dark'],
        'authorized' => ['Autorisé', 'info'],
        'submitted' => ['Soumis', 'primary'],
        'in_review' => ['En instruction', 'warning'],
        'approved' => ['Approuvé', 'success'],
        'paid' => ['Payé', 'success'],
        'closed' => ['Soldé', 'dark'],
    ];
    [$text, $color] = $labels[$status] ?? [$status, 'secondary'];
@endphp
<span class="badge bg-{{ $color }}">{{ $text }}</span>
