<?php

// Legacy pages can be reached directly during local development, outside the
// Laravel request lifecycle. Keep storage paths consistent in both cases.
if (! function_exists('storage_path')) {
    function storage_path(string $path = ''): string
    {
        $storage = dirname(__DIR__, 3).DIRECTORY_SEPARATOR.'storage';
        $relative = ltrim(str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $path), DIRECTORY_SEPARATOR);

        return $relative === '' ? $storage : $storage.DIRECTORY_SEPARATOR.$relative;
    }
}

if (! function_exists('legacy_asset_url')) {
    /**
     * Resolve public assets from either a bootstrapped Laravel request or a
     * directly executed legacy PHP page.
     */
    function legacy_asset_url(string $path): string
    {
        $path = ltrim($path, '/');

        if (function_exists('url') && function_exists('app')) {
            try {
                $container = app();
                if (is_object($container) && method_exists($container, 'bound') && $container->bound('url')) {
                    return url($path);
                }
            } catch (\Throwable $exception) {
                // Direct legacy execution has no Laravel URL service.
            }
        }

        return '../../../public/'.$path;
    }
}

if (! function_exists('legacy_file_url')) {
    /**
     * Convert stored upload paths into URLs for Laravel or direct legacy PHP requests.
     */
    function legacy_file_url(?string $storedPath, bool $download = false, string $defaultDirectory = 'uploads'): ?string
    {
        if (! is_string($storedPath)) {
            return null;
        }

        $path = trim(str_replace('\\', '/', $storedPath));
        if ($path === '' || strtoupper($path) === 'NULL') {
            return null;
        }

        // Older uploads stored paths relative to resources/legacy/php.
        if (preg_match('#^\.\./(uploads|docs)/(.+)$#i', $path, $matches)) {
            $path = $matches[1].'/'.$matches[2];
        }

        $pathSegments = explode('/', trim($path, '/'));
        if (in_array('.', $pathSegments, true) || in_array('..', $pathSegments, true)) {
            return null;
        }

        $directory = strtolower($defaultDirectory);
        $relativePath = $path;
        if (preg_match('#(?:^|/)(uploads|docs)/(.*)$#i', $path, $matches)) {
            $directory = strtolower($matches[1]);
            $relativePath = $matches[2];
        } elseif (! in_array($directory, ['uploads', 'docs'], true)) {
            return null;
        }

        $parts = explode('/', trim($relativePath, '/'));
        if ($relativePath === '' || in_array('', $parts, true) || in_array('.', $parts, true) || in_array('..', $parts, true)) {
            return null;
        }

        $encodedPath = implode('/', array_map('rawurlencode', $parts));
        $url = '/'.$directory.'/'.$encodedPath;

        if (function_exists('url') && function_exists('app')) {
            try {
                $container = app();
                if (is_object($container) && method_exists($container, 'bound') && $container->bound('url')) {
                    $url = url(ltrim($url, '/'));
                }
            } catch (\Throwable $exception) {
                // Direct legacy execution uses the public storage symlink below.
            }
        } else {
            $url = '../../../public/storage/'.$directory.'/'.$encodedPath;
        }

        return $download ? $url.'?download=1' : $url;
    }
}

if (! function_exists('legacy_store_upload')) {
    /**
     * Validate and store a user supplied file on Laravel's public disk.
     * Returns a relative path such as docs/<random-name>.pdf.
     */
    function legacy_store_upload(array $file, string $directory, string $prefix, array $allowedMimeTypes, int $maxBytes = 10485760): ?string
    {
        $error = (int) ($file['error'] ?? UPLOAD_ERR_NO_FILE);
        if ($error === UPLOAD_ERR_NO_FILE) {
            return null;
        }
        if ($error !== UPLOAD_ERR_OK || ! is_uploaded_file((string) ($file['tmp_name'] ?? ''))) {
            throw new RuntimeException('No se pudo recibir el archivo adjunto.');
        }

        $size = (int) ($file['size'] ?? 0);
        if ($size < 1 || $size > $maxBytes) {
            $limitMb = max(1, (int) floor($maxBytes / 1048576));
            throw new RuntimeException('El archivo debe pesar menos de '.$limitMb.' MB.');
        }

        $mime = (new finfo(FILEINFO_MIME_TYPE))->file((string) $file['tmp_name']);
        if (! is_string($mime) || ! isset($allowedMimeTypes[$mime])) {
            throw new RuntimeException('El tipo de archivo adjunto no está permitido.');
        }

        $directory = trim(str_replace('\\', '/', $directory), '/');
        if ($directory === '' || preg_match('#(?:^|/)\.?(?:\./|$)|\.\.#', $directory)) {
            throw new RuntimeException('La carpeta de destino no es válida.');
        }

        $targetDirectory = storage_path('app/public/'.$directory);
        if (! is_dir($targetDirectory) && ! mkdir($targetDirectory, 0755, true) && ! is_dir($targetDirectory)) {
            throw new RuntimeException('No se pudo preparar el almacenamiento de archivos.');
        }

        $safePrefix = preg_replace('/[^A-Za-z0-9_-]/', '_', $prefix) ?: 'archivo';
        $filename = $safePrefix.'_'.bin2hex(random_bytes(12)).'.'.$allowedMimeTypes[$mime];
        if (! move_uploaded_file((string) $file['tmp_name'], $targetDirectory.DIRECTORY_SEPARATOR.$filename)) {
            throw new RuntimeException('No se pudo guardar el archivo adjunto.');
        }

        return $directory.'/'.$filename;
    }
}
