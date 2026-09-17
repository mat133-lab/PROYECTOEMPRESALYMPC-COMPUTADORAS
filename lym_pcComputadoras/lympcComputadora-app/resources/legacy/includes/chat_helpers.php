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

function chatNormalizeText($mensaje) {
    $texto = function_exists('mb_strtolower') ? mb_strtolower((string)$mensaje, 'UTF-8') : strtolower((string)$mensaje);
    return strtr($texto, [
        'á' => 'a', 'é' => 'e', 'í' => 'i', 'ó' => 'o', 'ú' => 'u', 'ü' => 'u', 'ñ' => 'n',
    ]);
}

function chatContainsAny($texto, array $palabras) {
    foreach ($palabras as $palabra) {
        if (str_contains($texto, $palabra)) {
            return true;
        }
    }

    return false;
}

function chatContainsOption($texto, $numero) {
    return preg_match('/(?:^|\\s)' . preg_quote((string)$numero, '/') . '(?:[.)\\-:]|\\s|$)/u', $texto) === 1;
}

function chatSupportHasDetails($texto) {
    $marcas = ['asus', 'acer', 'lenovo', 'dell', 'hp', 'hewlett', 'msi', 'apple', 'toshiba', 'samsung', 'huawei'];
    $fallas = [
        'no enciende', 'no prende', 'no arranca', 'se apaga', 'se reinicia', 'pantalla', 'sonido', 'sonidos',
        'ruido', 'pitido', 'pitidos', 'lento', 'calienta', 'sobrecalienta', 'virus', 'error', 'falla', 'problema',
        'no carga', 'carga', 'bateria',
    ];
    $tiempo = ['desde ayer', 'desde hoy', 'desde anoche', 'hace ', 'desde ', 'ayer', 'hoy'];

    return chatContainsAny($texto, $fallas) && (
        chatContainsAny($texto, $marcas) || str_contains($texto, 'modelo') || chatContainsAny($texto, $tiempo)
    );
}

function chatSupportAdvanced($texto) {
    $noEncendido = chatContainsAny($texto, ['no enciende', 'no prende', 'no arranca']);
    $sonidos = chatContainsAny($texto, ['sonido', 'sonidos', 'ruido', 'pitido', 'pitidos']);
    $senalesPeligro = chatContainsAny($texto, ['olor a quemado', 'humo', 'liquido', 'se sobrecalienta', 'sobrecalienta']);
    $comportamientoRepetido = chatContainsAny($texto, ['se apaga', 'se reinicia']);

    return ($noEncendido && $sonidos) || $senalesPeligro || $comportamientoRepetido;
}

function chatSupportResponse($texto) {
    $marcas = ['asus', 'acer', 'lenovo', 'dell', 'hp', 'hewlett', 'msi', 'apple', 'toshiba', 'samsung', 'huawei'];
    $marca = 'no indicada';
    foreach ($marcas as $posibleMarca) {
        if (str_contains($texto, $posibleMarca)) {
            $marca = function_exists('mb_strtoupper') ? mb_strtoupper($posibleMarca, 'UTF-8') : strtoupper($posibleMarca);
            break;
        }
    }

    $modelo = 'no indicado';
    if (preg_match('/\\bmodelo\\s*[:#-]?\\s*([^,.;\\n]+)/u', $texto, $matches) === 1 && trim($matches[1]) !== '') {
        $modelo = trim($matches[1]);
    } elseif ($marca !== 'no indicada') {
        $modelo = $marca;
    }

    if (chatSupportAdvanced($texto)) {
        return "Gracias por la informacion. Registre un equipo {$marca}, modelo {$modelo}, con una falla que requiere revision. Como comprobacion basica, desconecta los perifericos y verifica el cargador una sola vez. No sigas intentando encenderlo si el sonido continua, se calienta o huele a quemado. Tu caso quedara en espera de un asistente de soporte.";
    }

    if (chatContainsAny($texto, ['no enciende', 'no prende', 'no arranca'])) {
        return "Gracias por la informacion del equipo {$marca}, modelo {$modelo}. Como comprobacion basica, revisa el cargador y prueba otro tomacorriente una sola vez. Si continua sin encender, comunicate con un asistente para revisarlo.";
    }

    return "Gracias por la informacion del equipo {$marca}, modelo {$modelo}. Prueba reiniciarlo una vez y verifica que sus conexiones esten firmes. Si la falla continua, comunicate con un asistente de soporte.";
}

function chatBotFallback($mensaje) {
    $texto = chatNormalizeText($mensaje);
    $necesitaHumano = false;
    $tema = 'Consulta general';
    $mostrarOpciones = true;

    if (chatContainsAny($texto, ['humano', 'asistente', 'personalizada', 'especifica'])) {
        $necesitaHumano = true;
        $tema = 'Asistencia personalizada';
        $respuesta = 'Te voy a pasar con asistencia para revisar tu caso con mas detalle. Un asistente continuara la conversacion aqui.';
    } elseif (chatContainsOption($texto, 1) || chatContainsAny($texto, ['soporte', 'tecnico', 'reparacion', 'arreglo', 'mantenimiento', 'formatear']) || chatSupportHasDetails($texto)) {
        $tema = 'Soporte tecnico';
        if (chatSupportHasDetails($texto)) {
            $respuesta = chatSupportResponse($texto);
            $necesitaHumano = chatSupportAdvanced($texto);
            $mostrarOpciones = false;
        } else {
            $respuesta = 'Para soporte tecnico indica marca, modelo, falla principal y si el equipo enciende. Si ya tienes esos datos, escribelos juntos y te dare una orientacion concreta.';
        }
    } elseif (chatContainsAny($texto, ['manana', 'mañana', 'hoy', 'dia', 'fecha', 'cuando', 'disponibilidad']) || chatContainsOption($texto, 2) || chatContainsAny($texto, ['horario', 'disponible', 'dias'])) {
        $tema = 'Horarios disponibles';
        $respuesta = 'Para revisar disponibilidad, indícame si te sirve mañana o en otro día y el tipo de servicio. Si más adelante necesitas una hora exacta, entonces te paso con asistencia.';
    } elseif (chatContainsOption($texto, 3) || chatContainsAny($texto, ['direccion', 'ubicacion', 'donde'])) {
        $tema = 'Direccion y ubicacion';
        $respuesta = 'La ubicacion de L&M PC Computadoras es: Ecuador, Quito, sector La Ecuatoriana, por la Joaquin Jativa. '
            . 'Referencia: Las Orquideas / Oe9 Martha Bucaram / S37-49 / S37A. '
            . 'Puedes abrir la ubicacion en Google Maps: https://maps.app.goo.gl/V8ghM2XY9M4H8v6Z9';
        $mostrarOpciones = false;
    } elseif (chatContainsOption($texto, 4) || chatContainsAny($texto, ['empresa', 'informacion', 'lympc'])) {
        $tema = 'Informacion de la empresa';
        $respuesta = 'L&M PC Computadoras ofrece venta de equipos, accesorios, mantenimiento y reparacion. Si deseas informacion comercial concreta, asistencia puede darte mas detalles.';
    } elseif (chatContainsOption($texto, 5) || chatContainsAny($texto, ['hablar', 'contactar'])) {
        $necesitaHumano = true;
        $tema = 'Solicitud de asistente';
        $respuesta = 'Claro. Te paso con un asistente real para continuar la conversacion y resolver tus dudas especificas.';
    } else {
        $respuesta = 'Puedo ayudarte con horarios, direccion del local o soporte tecnico. Si luego necesitas una hora exacta o detalles específicos, te paso con asistencia.';
    }

    if ($mostrarOpciones && !$necesitaHumano) {
        $respuesta .= "\n\nTambien puedes escribir una de estas opciones:\n1. Soporte tecnico\n2. Horarios disponibles\n3. Direccion o ubicacion\n4. Informacion de la empresa\n5. Hablar con asistente";
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
