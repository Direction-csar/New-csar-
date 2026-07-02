<?php
$file = '/var/www/csar/app/Http/Controllers/Auth/TwoFactorController.php';
$content = file_get_contents($file);

// Fix admin guard routes
$content = str_replace("'admin.login'", "'login'", $content);
$content = str_replace("'admin.dashboard'", "'dashboard'", $content);
$content = str_replace("'admin.2fa.challenge'", "'2fa.challenge'", $content);

file_put_contents($file, $content);
echo "Fixed guard routes\n";
