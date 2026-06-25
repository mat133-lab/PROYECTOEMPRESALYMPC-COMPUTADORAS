<?php

function chatEnsureTables(PDO $conn) {
    $conn->exec("CREATE TABLE IF NOT EXISTS chat_conversaciones (
        id_conversacion INT AUTO_INCREMENT PRIMARY KEY,
        id_usuario INT NULL,
        nombre_usuario VARCHAR(200) NOT NULL,
        correo_usuario VARCHAR(255) NULL,
        estado VARCHAR(40) NOT NULL DEFAULT 'ia',
        tema VARCHAR(180) NULL,
        calificacion TINYINT NULL,
        comentario_calificacion TEXT NULL,
        fecha_cierre DATETIME NULL,
        fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        fecha_actualizacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

    foreach ([
        "ALTER TABLE chat_conversaciones ADD COLUMN calificacion TINYINT NULL",
        "ALTER TABLE chat_conversaciones ADD COLUMN comentario_calificacion TEXT NULL",
        "ALTER TABLE chat_conversaciones ADD COLUMN fecha_cierre DATETIME NULL"
    ] as $sql) {
        try {
            $conn->exec($sql);
        } catch (PDOException $e) {
        }
    }

    $conn->exec("CREATE TABLE IF NOT EXISTS chat_mensajes (
        id_mensaje INT AUTO_INCREMENT PRIMARY KEY,
        id_conversacion INT NOT NULL,
        remitente VARCHAR(40) NOT NULL,
        nombre_remitente VARCHAR(200) NOT NULL,
        mensaje TEXT NOT NULL,
        fecha_envio TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        INDEX (id_conversacion)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
}

function chatWelcomeMessage() {
    return "Hola, soy la asistencia virtual de L&M PC Computadoras. Puedo ayudarte con soporte tecnico, direcciones, horarios disponibles e informacion de la empresa. En que se le puede ayudar?\n\n"
        . "Puedes escribir:\n"
        . "1. Soporte tecnico\n"
        . "2. Horarios disponibles\n"
        . "3. Direccion o ubicacion\n"
        . "4. Informacion de la empresa\n"
        . "5. Hablar con asistente";
}

function chatFindActiveConversation(PDO $conn, $idUsuario) {
    $stmt = $conn->prepare("SELECT * FROM chat_conversaciones WHERE id_usuario = ? AND estado <> 'finalizado' ORDER BY fecha_actualizacion DESC LIMIT 1");
    $stmt->execute([$idUsuario]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

function chatCreateConversation(PDO $conn, $idUsuario, $nombreUsuario, $correoUsuario) {
    $stmt = $conn->prepare("INSERT INTO chat_conversaciones (id_usuario, nombre_usuario, correo_usuario, estado, tema) VALUES (?, ?, ?, 'ia', 'Consulta de cliente')");
    $stmt->execute([$idUsuario, $nombreUsuario, $correoUsuario]);
    $idConversacion = (int)$conn->lastInsertId();

    chatAddMessage($conn, $idConversacion, 'ai', 'Asistencia virtual', chatWelcomeMessage());
    return $idConversacion;
}

function chatGetOrCreateUserConversation(PDO $conn, $idUsuario, $nombreUsuario, $correoUsuario, $forceNew = false) {
    if (!$forceNew) {
        $conversacion = chatFindActiveConversation($conn, $idUsuario);
        if ($conversacion) {
            return (int)$conversacion['id_conversacion'];
        }
    }

    return chatCreateConversation($conn, $idUsuario, $nombreUsuario, $correoUsuario);
}

function chatGetConversation(PDO $conn, $idConversacion) {
    $stmt = $conn->prepare("SELECT * FROM chat_conversaciones WHERE id_conversacion = ?");
    $stmt->execute([$idConversacion]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

function chatGetMessages(PDO $conn, $idConversacion) {
    $stmt = $conn->prepare("SELECT * FROM chat_mensajes WHERE id_conversacion = ? ORDER BY fecha_envio ASC, id_mensaje ASC");
    $stmt->execute([$idConversacion]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function chatAddMessage(PDO $conn, $idConversacion, $remitente, $nombreRemitente, $mensaje) {
    $stmt = $conn->prepare("INSERT INTO chat_mensajes (id_conversacion, remitente, nombre_remitente, mensaje) VALUES (?, ?, ?, ?)");
    $stmt->execute([$idConversacion, $remitente, $nombreRemitente, $mensaje]);
}

function chatBotFallback($mensaje) {
    $texto = function_exists('mb_strtolower') ? mb_strtolower($mensaje, 'UTF-8') : strtolower($mensaje);
    $necesitaHumano = false;
    $tema = 'Consulta general';

    if (str_contains($texto, 'humano') || str_contains($texto, 'asistente') || str_contains($texto, 'personalizada') || str_contains($texto, 'especifica')) {
        $necesitaHumano = true;
        $tema = 'Asistencia personalizada';
        $respuesta = 'Te voy a pasar con asistencia para revisar tu caso con mas detalle. Un asistente continuara la conversacion aqui.';
    } elseif (str_contains($texto, 'horario') || str_contains($texto, 'disponible') || str_contains($texto, 'dias')) {
        $tema = 'Horarios disponibles';
        $respuesta = 'Atendemos consultas sobre horarios y citas tecnicas. Puedes indicar el dia que prefieres y el tipo de equipo para revisar disponibilidad.';
    } elseif (str_contains($texto, 'direccion') || str_contains($texto, 'ubicacion') || str_contains($texto, 'donde')) {
        $tema = 'Direccion y ubicacion';
        $respuesta = 'L&M PC Computadoras puede ayudarte con ubicacion, rutas y referencias. Si necesitas una direccion exacta o envio, te paso con asistencia.';
    } elseif (str_contains($texto, 'soporte') || str_contains($texto, 'tecnico') || str_contains($texto, 'reparacion') || str_contains($texto, 'arreglo')) {
        $tema = 'Soporte tecnico';
        $respuesta = 'Para soporte tecnico indica marca, modelo, falla principal y si el equipo enciende. Con esos datos se puede orientar el diagnostico inicial.';
    } elseif (str_contains($texto, 'empresa') || str_contains($texto, 'informacion') || str_contains($texto, 'lympc')) {
        $tema = 'Informacion de la empresa';
        $respuesta = 'L&M PC Computadoras ofrece venta de equipos, accesorios, mantenimiento y reparacion. Si deseas informacion comercial concreta, asistencia puede darte mas detalles.';
    } else {
        $necesitaHumano = true;
        $respuesta = 'Puedo ayudarte con horarios, direcciones, soporte tecnico e informacion de la empresa. Como tu consulta necesita mas contexto, te paso con asistencia.';
    }

    return [$respuesta, $necesitaHumano, $tema];
}

function chatBotResponse($mensaje) {
    $script = realpath(__DIR__ . '/../python/chatbot_ia.py');
    if (!$script) {
        return chatBotFallback($mensaje);
    }

    $descriptorSpec = [
        0 => ['pipe', 'r'],
        1 => ['pipe', 'w'],
        2 => ['pipe', 'w'],
    ];

    $pythonCandidates = [];
    $envPython = function_exists('env') ? env('PYTHON_BIN') : getenv('PYTHON_BIN');
    if ($envPython) {
        $pythonCandidates[] = $envPython;
    }
    $pythonCandidates[] = 'python';
    $pythonCandidates[] = 'python3';

    foreach ($pythonCandidates as $pythonBin) {
        $process = proc_open($pythonBin . ' ' . escapeshellarg($script), $descriptorSpec, $pipes);
        if (!is_resource($process)) {
            continue;
        }

        fwrite($pipes[0], json_encode(['mensaje' => $mensaje], JSON_UNESCAPED_UNICODE));
        fclose($pipes[0]);

        $output = stream_get_contents($pipes[1]);
        fclose($pipes[1]);
        fclose($pipes[2]);
        $exitCode = proc_close($process);

        $data = json_decode($output, true);
        if ($exitCode === 0 && is_array($data) && !empty($data['respuesta'])) {
            return [
                $data['respuesta'],
                !empty($data['necesita_humano']),
                $data['tema'] ?? 'Consulta general'
            ];
        }
    }

    return chatBotFallback($mensaje);
}

function chatHandleUserMessage(PDO $conn, $idConversacion, $idUsuario, $nombreUsuario, $mensaje) {
    chatAddMessage($conn, $idConversacion, 'user', $nombreUsuario, $mensaje);

    $stmt = $conn->prepare("SELECT estado FROM chat_conversaciones WHERE id_conversacion = ? AND id_usuario = ?");
    $stmt->execute([$idConversacion, $idUsuario]);
    $estadoActual = $stmt->fetchColumn();

    if ($estadoActual === 'ia') {
        [$respuesta, $necesitaHumano, $tema] = chatBotResponse($mensaje);
        chatAddMessage($conn, $idConversacion, 'ai', 'Asistencia virtual', $respuesta);

        $nuevoEstado = $necesitaHumano ? 'pendiente_asistente' : 'ia';
        $stmt = $conn->prepare("UPDATE chat_conversaciones SET estado = ?, tema = ? WHERE id_conversacion = ?");
        $stmt->execute([$nuevoEstado, $tema, $idConversacion]);
    } elseif ($estadoActual !== 'finalizado') {
        $stmt = $conn->prepare("UPDATE chat_conversaciones SET estado = 'pendiente_asistente' WHERE id_conversacion = ?");
        $stmt->execute([$idConversacion]);
    }
}

function chatRateConversation(PDO $conn, $idConversacion, $idUsuario, $calificacion, $comentario) {
    $calificacion = max(1, min(10, (int)$calificacion));
    $stmt = $conn->prepare("UPDATE chat_conversaciones SET estado = 'finalizado', calificacion = ?, comentario_calificacion = ?, fecha_cierre = NOW() WHERE id_conversacion = ? AND id_usuario = ?");
    $stmt->execute([$calificacion, trim($comentario), $idConversacion, $idUsuario]);
}
