<?php
$f1 = 'resources/views/admin/drh/documents/contrat_cdi.blade.php';
$f2 = 'resources/views/admin/drh/documents/contrat_cdd.blade.php';
foreach ([$f1, $f2] as $f) {
    $c = file_get_contents($f);
    $c = str_replace('number_format($salaire_brut', 'number_format((float)$salaire_brut', $c);
    file_put_contents($f, $c);
    echo "Fixed: $f\n";
}
