<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class SetTempDir
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next)
    {
        // Définir le répertoire temporaire pour contourner le problème PHP-FPM
        $tmpDir = storage_path('tmp');
        if (!is_dir($tmpDir)) {
            @mkdir($tmpDir, 0777, true);
        }
        
        putenv('TMPDIR=' . $tmpDir);
        putenv('TMP=' . $tmpDir);
        putenv('TEMP=' . $tmpDir);
        
        // Définir les variables superglobales
        $_ENV['TMPDIR'] = $tmpDir;
        $_ENV['TMP'] = $tmpDir;
        $_ENV['TEMP'] = $tmpDir;
        
        return $next($request);
    }
}
