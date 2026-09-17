<?php

require_once __DIR__.'/file_helpers.php';

if (!function_exists('env')) {
    function env($key, $default = null) {
        static $vars = null;

        if ($vars === null) {
            $vars = [];
            $envPath = dirname(__DIR__, 2) . '/.env';

            if (is_file($envPath)) {
                $lines = file($envPath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
                foreach ($lines as $line) {
                    $trimmed = trim($line);
                    if ($trimmed === '' || str_starts_with($trimmed, '#')) {
                        continue;
                    }

                    if (preg_match('/^([A-Za-z_][A-Za-z0-9_]*)=(.*)$/', $trimmed, $matches)) {
                        $value = trim($matches[2]);
                        if ((str_starts_with($value, '"') && str_ends_with($value, '"')) || (str_starts_with($value, "'") && str_ends_with($value, "'"))) {
                            $value = substr($value, 1, -1);
                        }
                        $vars[$matches[1]] = $value;
                    }
                }
            }
        }

        return array_key_exists($key, $vars) ? $vars[$key] : $default;
    }
}

try {
    if (class_exists('Illuminate\\Support\\Facades\\DB')) {
        $conn = \Illuminate\Support\Facades\DB::connection()->getPdo();
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    } else {
        throw new RuntimeException('Laravel DB facade not available.');
    }
} catch (Throwable $e) {
    $host = env('DB_HOST', '127.0.0.1');
    $db_name = env('DB_DATABASE', 'lympc_bd');
    $username = env('DB_USERNAME', 'root');
    $password = env('DB_PASSWORD', '');
    $ports = array_unique([env('DB_PORT', '3306'), env('DB_FALLBACK_PORT', '3307')]);
    $lastError = $e;

    foreach ($ports as $port) {
        try {
            $conn = new PDO("mysql:host=$host;port=$port;dbname=$db_name;charset=utf8mb4", $username, $password);
            $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            break;
        } catch (PDOException $pdoException) {
            $lastError = $pdoException;
        }
    }

    if (!isset($conn)) {
        throw new RuntimeException('No se pudo conectar a la base de datos configurada en Laravel: ' . $lastError->getMessage());
    }
}

try {
    $checkColumns = $conn->query("SHOW COLUMNS FROM usuarios LIKE 'email_verified'");
    if ($checkColumns->rowCount() === 0) {
        $conn->exec("ALTER TABLE usuarios ADD COLUMN email_verified TINYINT(1) NOT NULL DEFAULT 0");
        $conn->exec("ALTER TABLE usuarios ADD COLUMN email_verification_token VARCHAR(255) DEFAULT NULL");
        $conn->exec("ALTER TABLE usuarios ADD COLUMN email_verification_expires DATETIME DEFAULT NULL");
    }

    $checkProfilePhoto = $conn->query("SHOW COLUMNS FROM usuarios LIKE 'foto_perfil'");
    if ($checkProfilePhoto->rowCount() === 0) {
        $conn->exec("ALTER TABLE usuarios ADD COLUMN foto_perfil VARCHAR(255) DEFAULT NULL");
    }

    $conn->exec("UPDATE usuarios SET email_verified = 1 WHERE email_verified IS NULL OR (email_verified = 0 AND email_verification_token IS NULL AND email_verification_expires IS NULL)");
} catch (PDOException $e) {
    error_log('Error al preparar compatibilidad de usuarios: ' . $e->getMessage());
}
