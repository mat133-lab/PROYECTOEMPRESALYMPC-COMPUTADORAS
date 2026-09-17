<?php

if (! function_exists('legacy_file_url')) {
    /**
     * Convert a stored upload path into a URL served by Laravel.
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

        return $download ? $url.'?download=1' : $url;
    }
}
