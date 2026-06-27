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

        // Evitar que cookies largas/serializadas sean interpretadas como session id inválido.
        $possibleNames = ['LEGACYSESSID', 'PHPSESSID', session_name(), 'laravel_session', 'lm-pc-computadoras-session'];
        foreach (array_unique($possibleNames) as $name) {
            if (! $name) {
                continue;
            }
            if (isset($_COOKIE[$name])) {
                $cookieVal = $_COOKIE[$name];
                if (strlen($cookieVal) > 48 || ! preg_match('/^[A-Za-z0-9,-]+$/', $cookieVal)) {
                    unset($_COOKIE[$name]);
                    setcookie($name, '', time() - 3600, '/');
                }
            }
        }

        // Para evitar colisiones con las cookies de Laravel y otras, asignamos
        // un nombre de sesión separado para los scripts legacy y preconfiguramos
        // un id seguro. No iniciamos la sesión aquí; el script legacy seguirá
        // llamando a `session_start()` normalmente y recibirá la cookie
        // `LEGACYSESSID` en lugar de `PHPSESSID`.
        $legacySessionName = 'LEGACYSESSID';
        $previousSessionName = session_name();

        if (session_status() === PHP_SESSION_ACTIVE) {
            session_write_close();
            setcookie($previousSessionName, '', time() - 3600, '/');
            unset($_COOKIE[$previousSessionName], $_COOKIE[session_name()]);
        }

        session_name($legacySessionName);

        if (session_status() !== PHP_SESSION_ACTIVE) {
            if (isset($_COOKIE[$legacySessionName]) && preg_match('/^[A-Za-z0-9,-]+$/', $_COOKIE[$legacySessionName]) && strlen($_COOKIE[$legacySessionName]) <= 48) {
                session_id($_COOKIE[$legacySessionName]);
            } else {
                try {
                    $safeId = bin2hex(random_bytes(16));
                } catch (\Throwable $e) {
                    $safeId = uniqid('', true);
                }
                session_id($safeId);
            }
        }

        $shutdownRegistered = false;
        $registerShutdown = function () use ($legacySessionName, $previousSessionName, &$shutdownRegistered) {
            if ($shutdownRegistered) {
                return;
            }
            $shutdownRegistered = true;
            if (session_status() === PHP_SESSION_ACTIVE && session_name() === $legacySessionName) {
                session_write_close();
            }
            if ($previousSessionName) {
                session_name($previousSessionName);
            }
        };
        register_shutdown_function($registerShutdown);

        $cleanupShutdown = $registerShutdown;

        http_response_code(200);
        ob_start();

        // Logging: volcar estado de cookies y headers antes de ejecutar el script legacy
        $logFile = storage_path('logs/legacy_debug.log');
        try {
            file_put_contents($logFile, "--- Legacy pre-run: " . date('c') . "\n", FILE_APPEND);
            file_put_contents($logFile, "\
_COOKIE:\n" . print_r($_COOKIE, true) . "\n", FILE_APPEND);
            file_put_contents($logFile, "headers_list before run:\n" . print_r(headers_list(), true) . "\n", FILE_APPEND);
        } catch (\Throwable $e) {
            // noop
        }

        try {
            chdir($scriptDirectory);
            require $path;
            $content = ob_get_clean();

            // Logging: volcar headers y cookies después de ejecutar el script legacy
            try {
                file_put_contents($logFile, "--- Legacy post-run: " . date('c') . "\n", FILE_APPEND);
                file_put_contents($logFile, "headers_list after run:\n" . print_r(headers_list(), true) . "\n", FILE_APPEND);
            } catch (\Throwable $e) {
                // noop
            }
        } catch (\Throwable $exception) {
            ob_end_clean();
            throw $exception;
        } finally {
            $cleanupShutdown();
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
