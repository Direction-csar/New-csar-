<?php
$file = '/var/www/csar/app/Http/Controllers/Auth/TwoFactorController.php';
$content = file_get_contents($file);

$old = "        return redirect()->intended(route(\$this->guardRoute(\$guard, 'dashboard')))
            ->with('success', 'Authentification réussie.');";

$new = "        try {
            \$dashboardRoute = route(\$this->guardRoute(\$guard, 'dashboard'));
        } catch (\\Symfony\\Component\\Routing\\Exception\\RouteNotFoundException \$e) {
            \$dashboardRoute = '/admin';
        }
        return redirect()->intended(\$dashboardRoute)
            ->with('success', 'Authentification réussie.');";

if (strpos($content, $old) !== false) {
    $content = str_replace($old, $new, $content);
    file_put_contents($file, $content);
    echo "Fixed\n";
} else {
    echo "Pattern not found\n";
}
