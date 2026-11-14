<?php

function paypal_config(): array
{
    static $fileVariables = null;
    if ($fileVariables === null) {
        $fileVariables = [];
        $envPath = dirname(__DIR__, 3) . '/.env';

        if (is_file($envPath) && is_readable($envPath)) {
            $lines = file($envPath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
            foreach ($lines as $line) {
                if (preg_match('/^\s*(?:export\s+)?([A-Za-z_][A-Za-z0-9_]*)\s*=\s*(.*?)\s*$/', $line, $matches)) {
                    $value = trim($matches[2]);
                    if (strlen($value) >= 2
                        && (($value[0] === '"' && $value[strlen($value) - 1] === '"')
                            || ($value[0] === "'" && $value[strlen($value) - 1] === "'"))) {
                        $value = substr($value, 1, -1);
                    }
                    $fileVariables[$matches[1]] = $value;
                }
            }
        }
    }

    $read = static function (string $key, string $default = '') use (&$fileVariables): string {
        $value = getenv($key);
        if (($value === false || trim((string) $value) === '') && isset($_ENV[$key])) {
            $value = $_ENV[$key];
        }
        if (($value === false || trim((string) $value) === '') && isset($_SERVER[$key])) {
            $value = $_SERVER[$key];
        }
        if (($value === false || trim((string) $value) === '') && function_exists('env')) {
            $value = env($key, $default);
        }
        if (($value === false || trim((string) $value) === '') && isset($fileVariables[$key])) {
            $value = $fileVariables[$key];
        }

        return is_string($value) && trim($value) !== '' ? trim($value) : $default;
    };

    $mode = strtolower($read('PAYPAL_MODE', 'sandbox'));
    if (!in_array($mode, ['sandbox', 'live'], true)) {
        throw new RuntimeException('PAYPAL_MODE debe ser sandbox o live.');
    }

    return [
        'mode' => $mode,
        'client_id' => $read('PAYPAL_CLIENT_ID'),
        'client_secret' => $read('PAYPAL_CLIENT_SECRET'),
        'api_url' => $mode === 'live'
            ? 'https://api-m.paypal.com'
            : 'https://api-m.sandbox.paypal.com',
    ];
}

function paypal_json_response(array $payload, int $status = 200): void
{
    http_response_code($status);
    echo json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit;
}

function paypal_ensure_orders_table(PDO $conn): void
{
    $conn->exec(
        'CREATE TABLE IF NOT EXISTS paypal_pedidos (
            paypal_order_id VARCHAR(64) NOT NULL PRIMARY KEY,
            id_pedido INTEGER NOT NULL UNIQUE,
            id_usuario INTEGER NOT NULL,
            total DECIMAL(10, 2) NOT NULL,
            fecha_registro TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )'
    );
}

function paypal_http_request(
    string $url,
    string $method,
    array $headers,
    ?string $body = null,
    ?string $username = null,
    ?string $password = null
): array {
    if (!function_exists('curl_init')) {
        throw new RuntimeException('La extensión cURL de PHP es necesaria para PayPal.');
    }

    $handle = curl_init($url);
    if ($handle === false) {
        throw new RuntimeException('No se pudo iniciar la conexión con PayPal.');
    }

    $options = [
        CURLOPT_CUSTOMREQUEST => $method,
        CURLOPT_HTTPHEADER => $headers,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_CONNECTTIMEOUT => 10,
        CURLOPT_TIMEOUT => 30,
        CURLOPT_SSL_VERIFYPEER => true,
        CURLOPT_SSL_VERIFYHOST => 2,
    ];

    if ($body !== null) {
        $options[CURLOPT_POSTFIELDS] = $body;
    }
    if ($username !== null && $password !== null) {
        $options[CURLOPT_HTTPAUTH] = CURLAUTH_BASIC;
        $options[CURLOPT_USERPWD] = $username . ':' . $password;
    }

    curl_setopt_array($handle, $options);
    $responseBody = curl_exec($handle);
    $curlError = curl_error($handle);
    $status = (int) curl_getinfo($handle, CURLINFO_RESPONSE_CODE);
    curl_close($handle);

    if ($responseBody === false) {
        error_log('PayPal cURL error: ' . $curlError);
        throw new RuntimeException('No fue posible comunicarse con PayPal. Intenta nuevamente.');
    }

    $decoded = json_decode($responseBody, true);
    if (!is_array($decoded)) {
        error_log('PayPal devolvió una respuesta JSON no válida (HTTP ' . $status . ').');
        throw new RuntimeException('PayPal devolvió una respuesta no válida.');
    }

    return ['status' => $status, 'body' => $decoded];
}

function paypal_access_token(): string
{
    $config = paypal_config();
    if ($config['client_id'] === '' || $config['client_secret'] === '') {
        throw new RuntimeException('PayPal no está configurado. Define PAYPAL_CLIENT_ID y PAYPAL_CLIENT_SECRET.');
    }

    $response = paypal_http_request(
        $config['api_url'] . '/v1/oauth2/token',
        'POST',
        ['Accept: application/json', 'Accept-Language: en_US', 'Content-Type: application/x-www-form-urlencoded'],
        'grant_type=client_credentials',
        $config['client_id'],
        $config['client_secret']
    );

    if ($response['status'] !== 200 || empty($response['body']['access_token'])) {
        error_log('PayPal OAuth rechazó las credenciales (HTTP ' . $response['status'] . ').');
        throw new RuntimeException('PayPal rechazó la configuración de credenciales.');
    }

    return $response['body']['access_token'];
}

function paypal_api_request(string $method, string $path, array|object|null $payload = null): array
{
    $config = paypal_config();
    $body = $payload === null
        ? null
        : json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

    if ($payload !== null && $body === false) {
        throw new RuntimeException('No se pudo preparar la solicitud de pago.');
    }

    $response = paypal_http_request(
        $config['api_url'] . $path,
        $method,
        ['Accept: application/json', 'Content-Type: application/json', 'Authorization: Bearer ' . paypal_access_token()],
        $body
    );

    if ($response['status'] < 200 || $response['status'] >= 300) {
        $apiName = preg_replace('/[^A-Z0-9_]/i', '', (string) ($response['body']['name'] ?? 'API_ERROR'));
        $issue = preg_replace(
            '/[^A-Z0-9_]/i',
            '',
            (string) ($response['body']['details'][0]['issue'] ?? '')
        );
        $debugId = preg_replace('/[^A-Z0-9-]/i', '', (string) ($response['body']['debug_id'] ?? ''));
        error_log(
            'PayPal API HTTP ' . $response['status'] . ': ' . $apiName
            . ($issue !== '' ? '/' . $issue : '')
            . ($debugId !== '' ? ' debug_id=' . $debugId : '')
        );
        throw new RuntimeException(
            'PayPal respondió HTTP ' . $response['status'] . ' (' . $apiName
            . ($issue !== '' ? ': ' . $issue : '') . ').'
            . ($debugId !== '' ? ' Referencia PayPal: ' . $debugId . '.' : '')
        );
    }

    return $response['body'];
}
