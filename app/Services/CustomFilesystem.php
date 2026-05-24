<?php

namespace App\Services;

use Illuminate\Filesystem\Filesystem as BaseFilesystem;

class CustomFilesystem extends BaseFilesystem
{
    /**
     * Replace the tempnam function to use a custom temp directory
     */
    protected function tempnam($path, $mode = null)
    {
        $tmpDir = storage_path('tmp');
        if (!is_dir($tmpDir)) {
            @mkdir($tmpDir, 0777, true);
        }
        
        $dir = dirname($path);
        $prefix = basename($path);
        
        // Use custom temp directory instead of system temp
        $tempPath = $tmpDir . '/' . $prefix . uniqid(mt_rand(), true);
        
        touch($tempPath);
        
        // Fix permissions
        if (!is_null($mode)) {
            @chmod($tempPath, $mode);
        }
        
        return $tempPath;
    }
}
