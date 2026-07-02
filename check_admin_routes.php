<?php
$content = file_get_contents('/var/www/csar/routes/web.php');

// Trouver le groupe admin
if (preg_match('/Route::prefix\(\'admin\'\)->name\(\'admin\.\'\)->middleware\(\[(.+?)\]\)->group/', $content, $m)) {
    echo "Middleware admin: " . $m[1] . "\n";
}

// Vérifier que users est dans le groupe admin
$lines = explode("\n", $content);
$inAdminGroup = false;
$usersLine = 0;
foreach ($lines as $i => $line) {
    if (strpos($line, "prefix('admin')") !== false) {
        $inAdminGroup = true;
    }
    if (strpos($line, "Route::resource('users'") !== false) {
        $usersLine = $i + 1;
        break;
    }
}
echo "Route users à la ligne: $usersLine\n";
echo "Dans le groupe admin: " . ($inAdminGroup && $usersLine > 0 ? 'OUI' : 'NON') . "\n";
