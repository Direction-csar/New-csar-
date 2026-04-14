<?php
ini_set('memory_limit', '2048M');
/**
 * Script de compression des images hero
 * Redimensionne à 1920px de large, qualité JPEG 75%
 * Crée les versions optimisées dans un sous-dossier 'optimized'
 */

$sourceDir = __DIR__ . '/public/images/arriere plan';
$outputDir = $sourceDir . '/optimized';

if (!is_dir($outputDir)) {
    mkdir($outputDir, 0755, true);
}

$maxWidth = 1920;
$quality = 75;

$files = ['N1.jpg', 'N2.jpg', 'N3.jpg', 'N5.jpg', 'N8.jpg'];

foreach ($files as $file) {
    $src = $sourceDir . '/' . $file;
    $dst = $outputDir . '/' . $file;
    
    if (!file_exists($src)) {
        echo "SKIP: $file not found\n";
        continue;
    }
    
    $info = getimagesize($src);
    if (!$info) {
        echo "SKIP: $file not a valid image\n";
        continue;
    }
    
    $origWidth = $info[0];
    $origHeight = $info[1];
    $mime = $info['mime'];
    
    $sizeMB = round(filesize($src) / 1024 / 1024, 2);
    echo "Processing $file ({$origWidth}x{$origHeight}, {$sizeMB}MB)... ";
    
    // Load image
    if ($mime === 'image/jpeg') {
        $image = imagecreatefromjpeg($src);
    } elseif ($mime === 'image/png') {
        $image = imagecreatefrompng($src);
    } else {
        echo "SKIP: unsupported format $mime\n";
        continue;
    }
    
    if (!$image) {
        echo "FAIL: could not load\n";
        continue;
    }
    
    // Calculate new dimensions
    if ($origWidth > $maxWidth) {
        $newWidth = $maxWidth;
        $newHeight = (int) round($origHeight * ($maxWidth / $origWidth));
    } else {
        $newWidth = $origWidth;
        $newHeight = $origHeight;
    }
    
    // Resize
    $resized = imagecreatetruecolor($newWidth, $newHeight);
    imagecopyresampled($resized, $image, 0, 0, 0, 0, $newWidth, $newHeight, $origWidth, $origHeight);
    
    // Save as JPEG
    imagejpeg($resized, $dst, $quality);
    
    $newSizeMB = round(filesize($dst) / 1024 / 1024, 2);
    $newSizeKB = round(filesize($dst) / 1024);
    echo "OK -> {$newWidth}x{$newHeight}, {$newSizeKB}KB (was {$sizeMB}MB)\n";
    
    imagedestroy($image);
    imagedestroy($resized);
}

echo "\nDone! Optimized images in: $outputDir\n";
echo "Now moving optimized images to replace originals...\n";

foreach ($files as $file) {
    $optimized = $outputDir . '/' . $file;
    $original = $sourceDir . '/' . $file;
    $backup = $sourceDir . '/' . pathinfo($file, PATHINFO_FILENAME) . '_original.' . pathinfo($file, PATHINFO_EXTENSION);
    
    if (file_exists($optimized)) {
        // Backup original
        copy($original, $backup);
        // Replace with optimized
        copy($optimized, $original);
        echo "Replaced $file (backup: " . basename($backup) . ")\n";
    }
}

echo "\nAll done! Original backups saved with _original suffix.\n";
