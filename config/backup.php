<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Configuration du système de backup CSAR
    |--------------------------------------------------------------------------
    */

    // Stockage cloud pour les backups distants
    'cloud_disk' => env('BACKUP_CLOUD_DISK', 's3'), // s3, google, ftp

    // Rétention des backups (en jours)
    'retention_days' => env('BACKUP_RETENTION_DAYS', 30),

    // Notification par email
    'notification_emails' => env('BACKUP_NOTIFICATION_EMAILS', ''),

    // Compression
    'compression' => [
        'enabled' => true,
        'level' => 9, // 0-9 (9 = maximum)
    ],

    // AWS S3 Configuration
    's3' => [
        'bucket' => env('AWS_BACKUP_BUCKET', 'csar-backups'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
        'path' => 'backups/csar/',
    ],

    // Google Cloud Storage
    'google' => [
        'bucket' => env('GOOGLE_BACKUP_BUCKET', 'csar-backups'),
        'path' => 'backups/csar/',
    ],

    // FTP/SFTP
    'ftp' => [
        'host' => env('BACKUP_FTP_HOST'),
        'username' => env('BACKUP_FTP_USERNAME'),
        'password' => env('BACKUP_FTP_PASSWORD'),
        'root' => env('BACKUP_FTP_ROOT', '/backups'),
        'port' => env('BACKUP_FTP_PORT', 21),
        'ssl' => env('BACKUP_FTP_SSL', false),
    ],
];






































