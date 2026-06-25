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
        $path = ltrim($path, '/\\');
        if (! Storage::disk('public')->exists($path)) {
            abort(404);
        }

        return response()->file(Storage::disk('public')->path($path));
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
