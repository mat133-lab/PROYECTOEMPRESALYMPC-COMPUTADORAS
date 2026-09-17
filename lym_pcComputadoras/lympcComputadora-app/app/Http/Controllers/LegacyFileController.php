<?php

namespace App\Http\Controllers;

use Illuminate\Http\Response;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class LegacyFileController extends Controller
{
    public function uploads(string $path): Response|BinaryFileResponse
    {
        return $this->publicFile('uploads/'.$path);
    }

    public function docs(string $path): Response|BinaryFileResponse
    {
        return $this->publicFile('docs/'.$path);
    }

    public function html(string $path): Response|BinaryFileResponse
    {
        return $this->resourceFile('legacy/html/'.$path);
    }

    public function assets(string $path): Response|BinaryFileResponse
    {
        return $this->resourceFile('legacy/assets/'.$path);
    }

    private function publicFile(string $path): Response|BinaryFileResponse
    {
        $path = str_replace('\\', '/', $path);
        $path = ltrim($path, '/');
        $parts = explode('/', $path);

        if ($path === '' || in_array('', $parts, true) || in_array('.', $parts, true) || in_array('..', $parts, true)) {
            abort(404);
        }

        $disk = Storage::disk('public');
        if (! $disk->exists($path)) {
            abort(404);
        }

        $filePath = realpath($disk->path($path));
        $basePath = realpath($disk->path(''));
        if (! $filePath || ! $basePath || ! is_file($filePath) || ! str_starts_with($filePath, $basePath.DIRECTORY_SEPARATOR)) {
            abort(404);
        }

        if (request()->boolean('download')) {
            return response()->download($filePath, basename($filePath), [
                'Content-Type' => mime_content_type($filePath) ?: 'application/octet-stream',
            ]);
        }

        return response()->file($filePath);
    }

    private function resourceFile(string $path): Response|BinaryFileResponse
    {
        $fullPath = resource_path($path);
        $basePath = realpath(resource_path('legacy'));
        $realPath = realpath($fullPath);

        if (! $realPath || ! $basePath || ! str_starts_with($realPath, $basePath) || ! is_file($realPath)) {
            abort(404);
        }

        return response()->file($realPath);
    }
}
