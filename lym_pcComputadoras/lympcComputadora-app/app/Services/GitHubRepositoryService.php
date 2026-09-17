<?php

namespace App\Services;

use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;
use InvalidArgumentException;
use RuntimeException;

class GitHubRepositoryService
{
    /**
     * @return array{owner: string, repository: string}
     */
    public function parseRepositoryUrl(string $repositoryUrl): array
    {
        $repositoryUrl = trim($repositoryUrl);
        $parts = parse_url($repositoryUrl);

        if (
            $parts === false
            || strtolower($parts['scheme'] ?? '') !== 'https'
            || ! in_array(strtolower($parts['host'] ?? ''), ['github.com', 'www.github.com'], true)
            || isset($parts['user'], $parts['pass'], $parts['port'], $parts['query'], $parts['fragment'])
        ) {
            throw new InvalidArgumentException('Indica una URL HTTPS válida de un repositorio de GitHub.');
        }

        $path = trim((string) ($parts['path'] ?? ''), '/');
        $segments = explode('/', $path);
        if (count($segments) !== 2) {
            throw new InvalidArgumentException('La URL debe tener el formato https://github.com/usuario/repositorio.');
        }

        [$owner, $repository] = $segments;
        $repository = preg_replace('/\.git$/i', '', $repository) ?? $repository;

        if (! $this->isGitHubName($owner) || ! $this->isGitHubName($repository)) {
            throw new InvalidArgumentException('La URL del repositorio de GitHub no es válida.');
        }

        return [
            'owner' => $owner,
            'repository' => $repository,
        ];
    }

    public function ensureConfigured(): void
    {
        if (trim((string) config('services.github.token')) === '') {
            throw new RuntimeException('GitHub no está configurado en el servidor.');
        }
    }

    /**
     * @return array{owner: string, repository: string, branch: string, path: string, html_url: string, commit_sha: ?string}
     */
    public function publish(
        string $repositoryUrl,
        string $localPath,
        string $fileName,
        ?string $branch = null,
    ): array {
        $this->ensureConfigured();

        if (! is_file($localPath) || ! is_readable($localPath)) {
            throw new RuntimeException('El archivo del proyecto no está disponible para publicarlo.');
        }

        $size = filesize($localPath);
        $maxFileBytes = max(1, (int) config('services.github.max_file_bytes', 52428800));
        if ($size === false || $size > $maxFileBytes) {
            throw new InvalidArgumentException('El archivo supera el tamaño máximo permitido para GitHub.');
        }

        $repository = $this->parseRepositoryUrl($repositoryUrl);
        $branch = $this->validateBranch($branch ?: (string) config('services.github.branch', 'main'));
        $path = $this->buildRepositoryPath($fileName);
        $contents = file_get_contents($localPath);

        if ($contents === false) {
            throw new RuntimeException('No se pudo leer el archivo del proyecto.');
        }

        try {
            $client = $this->client();
            $repositoryResponse = $client->get($this->endpoint(sprintf(
                'repos/%s/%s',
                rawurlencode($repository['owner']),
                rawurlencode($repository['repository']),
            )));
            $this->ensureSuccessful($repositoryResponse, 'validar el repositorio');

            $response = $client->put($this->endpoint(sprintf(
                'repos/%s/%s/contents/%s',
                rawurlencode($repository['owner']),
                rawurlencode($repository['repository']),
                implode('/', array_map('rawurlencode', explode('/', $path))),
            )), [
                'message' => sprintf('Subir proyecto de pasante: %s', $fileName),
                'content' => base64_encode($contents),
                'branch' => $branch,
            ]);
            $this->ensureSuccessful($response, 'publicar el archivo');
        } catch (ConnectionException $exception) {
            throw new RuntimeException('No fue posible conectar con GitHub.', 0, $exception);
        }

        $htmlUrl = $response->json('content.html_url');
        if (! is_string($htmlUrl) || $htmlUrl === '') {
            $htmlUrl = sprintf(
                'https://github.com/%s/%s/blob/%s/%s',
                $repository['owner'],
                $repository['repository'],
                rawurlencode($branch),
                implode('/', array_map('rawurlencode', explode('/', $path))),
            );
        }

        return [
            ...$repository,
            'branch' => $branch,
            'path' => $path,
            'html_url' => $htmlUrl,
            'commit_sha' => is_string($response->json('commit.sha')) ? $response->json('commit.sha') : null,
        ];
    }

    private function client(): \Illuminate\Http\Client\PendingRequest
    {
        return Http::withToken((string) config('services.github.token'))
            ->acceptJson()
            ->withHeaders([
                'X-GitHub-Api-Version' => '2022-11-28',
                'User-Agent' => (string) config('services.github.user_agent', 'L&M-PC-Computadoras'),
            ])
            ->timeout(max(1, (int) config('services.github.timeout', 20)));
    }

    private function endpoint(string $path): string
    {
        return rtrim((string) config('services.github.api_url', 'https://api.github.com'), '/') . '/' . ltrim($path, '/');
    }

    private function buildRepositoryPath(string $fileName): string
    {
        $fileName = basename($fileName);
        if ($fileName !== trim($fileName) || ! preg_match('/^[A-Za-z0-9][A-Za-z0-9._-]{0,180}$/', $fileName)) {
            throw new InvalidArgumentException('El nombre del archivo no es válido para GitHub.');
        }

        $directory = trim((string) config('services.github.directory', 'uploads/pasantes'), '/');
        $segments = explode('/', $directory);
        if ($directory === '' || count($segments) > 8) {
            throw new InvalidArgumentException('El directorio de destino de GitHub no es válido.');
        }

        foreach ($segments as $segment) {
            if ($segment === '' || $segment === '.' || $segment === '..' || ! preg_match('/^[A-Za-z0-9._-]{1,80}$/', $segment)) {
                throw new InvalidArgumentException('El directorio de destino de GitHub no es válido.');
            }
        }

        return $directory . '/' . $fileName;
    }

    private function validateBranch(string $branch): string
    {
        $branch = trim($branch);
        if (
            $branch === ''
            || strlen($branch) > 255
            || str_contains($branch, '..')
            || str_contains($branch, '//')
            || str_starts_with($branch, '/')
            || str_ends_with($branch, '/')
            || ! preg_match('/^[A-Za-z0-9._\/-]+$/', $branch)
        ) {
            throw new InvalidArgumentException('La rama de GitHub no es válida.');
        }

        return $branch;
    }

    private function isGitHubName(string $name): bool
    {
        return strlen($name) <= 100 && preg_match('/^[A-Za-z0-9][A-Za-z0-9._-]*$/', $name) === 1;
    }

    private function ensureSuccessful(Response $response, string $action): void
    {
        if ($response->successful()) {
            return;
        }

        $message = match ($response->status()) {
            401 => 'El token de GitHub no es válido.',
            403 => 'El token de GitHub no tiene permisos suficientes o se alcanzó el límite de solicitudes.',
            404 => 'El repositorio no existe o el token no tiene acceso.',
            409 => 'GitHub rechazó la operación por un conflicto con la rama.',
            422 => 'GitHub rechazó los datos del archivo o el archivo ya existe.',
            default => sprintf('GitHub no pudo %s (HTTP %d).', $action, $response->status()),
        };

        throw new RuntimeException($message);
    }
}
