<?php

namespace App\Support\Legacy;

use Illuminate\Http\Response;
use Illuminate\Support\Str;
use RuntimeException;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;

class LegacyRuntime
{
    public function run(string $script): Response
    {
        $script = rawurldecode($script);
        $this->assertValidScript($script);

        $path = resource_path('legacy/php/'.$script);
        if (! is_file($path)) {
            abort(404);
        }

        $cwd = getcwd();
        $scriptDirectory = dirname($path);
        $headersBefore = headers_list();

        http_response_code(200);
        ob_start();

        try {
            chdir($scriptDirectory);
            require $path;
            $content = ob_get_clean();
        } catch (\Throwable $exception) {
            ob_end_clean();
            throw $exception;
        } finally {
            chdir($cwd ?: base_path());
        }

        $status = http_response_code() ?: 200;
        $headers = $this->newHeaders($headersBefore);
        $hasLocation = $this->containsHeader($headers, 'Location');

        if ($hasLocation && ($status < 300 || $status >= 400)) {
            $status = 302;
        }

        $response = response($content, $status);
        $response->headers->set('X-Frame-Options', 'DENY');
        $response->headers->set('X-Content-Type-Options', 'nosniff');
        $response->headers->set('X-XSS-Protection', '1; mode=block');
        $response->headers->set('Referrer-Policy', 'strict-origin-when-cross-origin');
        $this->copyHeadersToResponse($headers, $response->headers);
        $this->clearLegacyHeaders($headers);
        http_response_code(200);

        return $response;
    }

    private function assertValidScript(string $script): void
    {
        if (str_contains($script, '/') || str_contains($script, '\\') || basename($script) !== $script) {
            throw new RuntimeException('Ruta legacy invalida.');
        }

        if (! Str::endsWith($script, '.php') || ! preg_match('/^[\pL\pN_.-]+\.php$/u', $script)) {
            throw new RuntimeException('Script legacy invalido.');
        }
    }

    /**
     * @param array<int, string> $headersBefore
     * @return array<int, string>
     */
    private function newHeaders(array $headersBefore): array
    {
        $currentHeaders = headers_list();
        $newHeaders = array_slice($currentHeaders, count($headersBefore));

        return array_values(array_filter($newHeaders, fn (string $header): bool => $header !== ''));
    }

    /**
     * @param array<int, string> $headers
     */
    private function containsHeader(array $headers, string $name): bool
    {
        foreach ($headers as $header) {
            if (str_starts_with(strtolower($header), strtolower($name).':')) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param array<int, string> $headers
     */
    private function copyHeadersToResponse(array $headers, ResponseHeaderBag $responseHeaders): void
    {
        foreach ($headers as $header) {
            if (! str_contains($header, ':')) {
                continue;
            }

            [$name, $value] = explode(':', $header, 2);
            $replace = strtolower(trim($name)) !== 'set-cookie';
            $responseHeaders->set(trim($name), trim($value), $replace);
        }
    }

    /**
     * @param array<int, string> $headers
     */
    private function clearLegacyHeaders(array $headers): void
    {
        foreach ($headers as $header) {
            if (! str_contains($header, ':')) {
                continue;
            }

            [$name] = explode(':', $header, 2);
            header_remove(trim($name));
        }
    }
}
