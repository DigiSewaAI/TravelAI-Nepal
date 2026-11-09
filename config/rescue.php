<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Rescue Documents Storage Disk
    |--------------------------------------------------------------------------
    |
    | Which filesystem disk stores uploaded rescue documents (CAAN licenses,
    | insurance policies, safety certificates, etc.). Defaults to 'local'
    | (private, not publicly accessible). In production, switch to an
    | S3-compatible disk via RESCUE_DOCUMENTS_DISK env variable.
    |
    */
    'documents_disk' => env('RESCUE_DOCUMENTS_DISK', 'local'),

    /*
    |--------------------------------------------------------------------------
    | Maximum Upload Size (KB)
    |--------------------------------------------------------------------------
    |
    | 10240 KB = 10 MB. Larger files rejected by validation.
    |
    */
    'documents_max_kb' => env('RESCUE_DOCUMENTS_MAX_KB', 10240),
];