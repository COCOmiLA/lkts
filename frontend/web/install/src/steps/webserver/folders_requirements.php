<?php

function _fileperms($path)
{
    if (PHP_OS == 'WINNT' || PHP_OS == 'WIN32' || PHP_OS == 'Windows') {
        $path = str_replace("/", "\\", $path);
        $path = str_replace("\\", "\\\\", $path);
    }

    return is_writable($path);
}

function getFolders()
{
    if (PHP_OS == 'WINNT' || PHP_OS == 'WIN32' || PHP_OS == 'Windows') {
        $base_dir = str_replace('\\frontend\\web', '', getcwd());
    } else {
        $base_dir = str_replace('/frontend/web', '', getcwd());
    }

    return [
        '/frontend/web/assets' => _fileperms($base_dir . '/frontend/web/assets'),
        '/frontend/runtime' => _fileperms($base_dir . '/frontend/runtime'),

        '/backend/web/assets' => _fileperms($base_dir . '/backend/web/assets'),
        '/backend/runtime' => _fileperms($base_dir . '/backend/runtime'),
        '/backend/backups' => _fileperms($base_dir . '/backend/backups'),

        '/common/runtime' => _fileperms($base_dir . '/common/runtime'),

        '/storage/web/aa-scans' => _fileperms($base_dir . '/storage/web/aa-scans'),
        '/storage/web/ia' => _fileperms($base_dir . '/storage/web/ia'),
        '/storage/web/scans' => _fileperms($base_dir . '/storage/web/scans'),
        '/storage/web/source' => _fileperms($base_dir . '/storage/web/source'),

        '/console/runtime' => _fileperms($base_dir . '/console/runtime'),
        '/frontend/web/img' => _fileperms($base_dir . '/frontend/web/img'),
    ];
}
