-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1
-- Tiempo de generación: 18-09-2026 a las 01:12:46
-- Versión del servidor: 10.4.32-MariaDB
-- Versión de PHP: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de datos: `lympc_bd`
--

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `cache`
--

CREATE TABLE `cache` (
  `key` varchar(255) NOT NULL,
  `value` mediumtext NOT NULL,
  `expiration` bigint(20) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `cache_locks`
--

CREATE TABLE `cache_locks` (
  `key` varchar(255) NOT NULL,
  `owner` varchar(255) NOT NULL,
  `expiration` bigint(20) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `chat_conversaciones`
--

CREATE TABLE `chat_conversaciones` (
  `id_conversacion` int(11) NOT NULL,
  `id_usuario` int(11) DEFAULT NULL,
  `nombre_usuario` varchar(200) NOT NULL,
  `correo_usuario` varchar(255) DEFAULT NULL,
  `estado` varchar(40) NOT NULL DEFAULT 'ia',
  `tema` varchar(180) DEFAULT NULL,
  `fecha_creacion` timestamp NOT NULL DEFAULT current_timestamp(),
  `fecha_actualizacion` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `calificacion` tinyint(4) DEFAULT NULL,
  `comentario_calificacion` text DEFAULT NULL,
  `fecha_cierre` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `chat_conversaciones`
--

INSERT INTO `chat_conversaciones` (`id_conversacion`, `id_usuario`, `nombre_usuario`, `correo_usuario`, `estado`, `tema`, `fecha_creacion`, `fecha_actualizacion`, `calificacion`, `comentario_calificacion`, `fecha_cierre`) VALUES
(1, 20, 'Mateo', 'mateo014esteban@gmail.com', 'finalizado', 'Asistencia personalizada', '2026-06-05 01:23:35', '2026-06-05 01:32:09', 10, '', '2026-06-04 20:32:09'),
(2, 27, 'mateo', 'mateo014esteban@gmail.com', 'finalizado', 'Soporte tecnico', '2026-09-17 18:40:25', '2026-09-17 21:09:54', 10, '', '2026-09-17 16:09:54'),
(3, 27, 'mateo', 'mateo014esteban@gmail.com', 'finalizado', 'Consulta general', '2026-09-17 21:09:57', '2026-09-17 21:12:25', 10, '', '2026-09-17 16:12:25'),
(4, 27, 'mateo', 'mateo014esteban@gmail.com', 'finalizado', 'Consulta de cliente', '2026-09-17 21:10:20', '2026-09-17 21:41:39', 10, '', '2026-09-17 16:41:39'),
(5, 27, 'mateo', 'mateo014esteban@gmail.com', 'finalizado', 'Consulta general', '2026-09-17 21:12:28', '2026-09-17 21:16:01', 10, '', '2026-09-17 16:16:01'),
(6, 27, 'mateo', 'mateo014esteban@gmail.com', 'finalizado', 'Consulta de cliente', '2026-09-17 21:12:44', '2026-09-17 21:41:38', 10, '', '2026-09-17 16:41:38'),
(7, 27, 'mateo', 'mateo014esteban@gmail.com', 'finalizado', 'Direccion y ubicacion', '2026-09-17 21:16:04', '2026-09-17 21:41:34', 10, '', '2026-09-17 16:41:34'),
(8, 27, 'mateo', 'mateo014esteban@gmail.com', 'finalizado', 'Consulta de cliente', '2026-09-17 21:16:10', '2026-09-17 21:41:37', 10, '', '2026-09-17 16:41:37'),
(9, 27, 'mateo', 'mateo014esteban@gmail.com', 'finalizado', 'Consulta de cliente', '2026-09-17 21:41:40', '2026-09-17 21:41:40', 10, '', '2026-09-17 16:41:40'),
(10, 27, 'mateo', 'mateo014esteban@gmail.com', 'finalizado', 'Consulta de cliente', '2026-09-17 21:41:40', '2026-09-17 21:41:40', 10, '', '2026-09-17 16:41:40'),
(11, 27, 'mateo', 'mateo014esteban@gmail.com', 'finalizado', 'Consulta de cliente', '2026-09-17 21:41:41', '2026-09-17 21:41:41', 10, '', '2026-09-17 16:41:41'),
(12, 27, 'mateo', 'mateo014esteban@gmail.com', 'finalizado', 'Consulta de cliente', '2026-09-17 21:41:42', '2026-09-17 21:41:42', 10, '', '2026-09-17 16:41:42'),
(13, 27, 'mateo', 'mateo014esteban@gmail.com', 'finalizado', 'Consulta de cliente', '2026-09-17 21:41:42', '2026-09-17 21:41:42', 10, '', '2026-09-17 16:41:42'),
(14, 27, 'mateo', 'mateo014esteban@gmail.com', 'finalizado', 'Consulta de cliente', '2026-09-17 21:41:43', '2026-09-17 21:41:43', 10, '', '2026-09-17 16:41:43'),
(15, 27, 'mateo', 'mateo014esteban@gmail.com', 'finalizado', 'Asistencia personalizada', '2026-09-17 21:41:44', '2026-09-17 21:51:03', 10, '', '2026-09-17 16:51:03'),
(16, 27, 'mateo', 'mateo014esteban@gmail.com', 'ia', 'Consulta de cliente', '2026-09-17 21:51:08', '2026-09-17 21:51:08', NULL, NULL, NULL);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `chat_mensajes`
--

CREATE TABLE `chat_mensajes` (
  `id_mensaje` int(11) NOT NULL,
  `id_conversacion` int(11) NOT NULL,
  `remitente` varchar(40) NOT NULL,
  `nombre_remitente` varchar(200) NOT NULL,
  `mensaje` text NOT NULL,
  `fecha_envio` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `chat_mensajes`
--

INSERT INTO `chat_mensajes` (`id_mensaje`, `id_conversacion`, `remitente`, `nombre_remitente`, `mensaje`, `fecha_envio`) VALUES
(1, 1, 'ai', 'Asistencia virtual', 'Hola, soy la asistencia virtual de L&M PC Computadoras. Puedo ayudarte con soporte tecnico, direcciones, horarios disponibles e informacion de la empresa. En que se le puede ayudar?\n\nPuedes escribir:\n1. Soporte tecnico\n2. Horarios disponibles\n3. Direccion o ubicacion\n4. Informacion de la empresa\n5. Hablar con asistente', '2026-06-05 01:23:35'),
(2, 1, 'user', 'Mateo', 'Soporte tecnico', '2026-06-05 01:24:42'),
(3, 1, 'ai', 'Asistencia virtual', 'Para soporte tecnico, dime por favor: marca del equipo, modelo, que problema presenta, si enciende correctamente y desde cuando ocurre la falla. Con eso puedo darte una orientacion inicial.\n\nTambien puedes escribir una de estas opciones:\n1. Soporte tecnico\n2. Horarios disponibles\n3. Direccion o ubicacion\n4. Informacion de la empresa\n5. Hablar con asistente', '2026-06-05 01:24:43'),
(4, 1, 'user', 'Mateo', 'Hablar con asistente', '2026-06-05 01:25:49'),
(5, 1, 'ai', 'Asistencia virtual', 'Entiendo. Te voy a pasar con alguien de soporte para que revise tu caso con mas detalle. Por favor deja escrito marca, modelo del equipo, falla principal y un numero de contacto si aplica.', '2026-06-05 01:25:49'),
(6, 1, 'asistente', 'matius', 'hola se comunica con el asistente Matt digame cual es el incoveniente?', '2026-06-05 01:27:06'),
(7, 1, 'user', 'Mateo', 'hola necesito traer un equipo que lo deje desde el dia lunes del otro mes pero no dieron todavia una respuesta', '2026-06-05 01:27:56'),
(8, 1, 'user', 'Mateo', 'no es nada gracias me he confundido pero ya llego el equipo, gracias', '2026-06-05 01:30:50'),
(9, 1, 'asistente', 'matius', 'okey un gusto verlo ayudado, califiquenos al cerrar esta conversacion nos importa mucho su calificacion ????✅', '2026-06-05 01:31:38'),
(10, 2, 'ai', 'Asistencia virtual', 'Hola, soy la asistencia virtual de L&M PC Computadoras. Puedo ayudarte con soporte tecnico, direcciones, horarios disponibles e informacion de la empresa. En que se le puede ayudar?\n\nPuedes escribir:\n1. Soporte tecnico\n2. Horarios disponibles\n3. Direccion o ubicacion\n4. Informacion de la empresa\n5. Hablar con asistente', '2026-09-17 18:40:25'),
(11, 2, 'user', 'mateo', '1', '2026-09-17 19:47:14'),
(12, 2, 'ai', 'Asistencia virtual', 'Para soporte tecnico, dime por favor: marca del equipo, modelo, que problema presenta, si enciende correctamente y desde cuando ocurre la falla. Con eso puedo darte una orientacion inicial.\n\nTambien puedes escribir una de estas opciones:\n1. Soporte tecnico\n2. Horarios disponibles\n3. Direccion o ubicacion\n4. Informacion de la empresa\n5. Hablar con asistente', '2026-09-17 19:47:15'),
(13, 2, 'user', 'mateo', 'soporte tecnico', '2026-09-17 19:47:27'),
(14, 2, 'ai', 'Asistencia virtual', 'Para soporte tecnico, dime por favor: marca del equipo, modelo, que problema presenta, si enciende correctamente y desde cuando ocurre la falla. Con eso puedo darte una orientacion inicial.\n\nTambien puedes escribir una de estas opciones:\n1. Soporte tecnico\n2. Horarios disponibles\n3. Direccion o ubicacion\n4. Informacion de la empresa\n5. Hablar con asistente', '2026-09-17 19:47:27'),
(15, 2, 'user', 'mateo', 'Soporte tecnico', '2026-09-17 19:47:44'),
(16, 2, 'ai', 'Asistencia virtual', 'Para soporte tecnico, dime por favor: marca del equipo, modelo, que problema presenta, si enciende correctamente y desde cuando ocurre la falla. Con eso puedo darte una orientacion inicial.\n\nTambien puedes escribir una de estas opciones:\n1. Soporte tecnico\n2. Horarios disponibles\n3. Direccion o ubicacion\n4. Informacion de la empresa\n5. Hablar con asistente', '2026-09-17 19:47:45'),
(17, 2, 'user', 'mateo', 'es un asus, modelo asus, no enciende y se apaga, y es variado, desde ayer', '2026-09-17 19:49:09'),
(18, 2, 'ai', 'Asistencia virtual', 'Para soporte tecnico, dime por favor: marca del equipo, modelo, que problema presenta, si enciende correctamente y desde cuando ocurre la falla. Con eso puedo darte una orientacion inicial.\n\nTambien puedes escribir una de estas opciones:\n1. Soporte tecnico\n2. Horarios disponibles\n3. Direccion o ubicacion\n4. Informacion de la empresa\n5. Hablar con asistente', '2026-09-17 19:49:10'),
(19, 2, 'user', 'mateo', 'soporte tecnico, asus, modelo asus, no enciende y hace sonidos, desde ayer', '2026-09-17 19:50:17'),
(20, 2, 'ai', 'Asistencia virtual', 'Para soporte tecnico, dime por favor: marca del equipo, modelo, que problema presenta, si enciende correctamente y desde cuando ocurre la falla. Con eso puedo darte una orientacion inicial.\n\nTambien puedes escribir una de estas opciones:\n1. Soporte tecnico\n2. Horarios disponibles\n3. Direccion o ubicacion\n4. Informacion de la empresa\n5. Hablar con asistente', '2026-09-17 19:50:17'),
(21, 2, 'user', 'mateo', '1', '2026-09-17 20:54:05'),
(22, 2, 'ai', 'Asistencia virtual', 'Para soporte tecnico indica marca, modelo, falla principal y si el equipo enciende. Si ya tienes esos datos, escribelos juntos y te dare una orientacion concreta.\n\nTambien puedes escribir una de estas opciones:\n1. Soporte tecnico\n2. Horarios disponibles\n3. Direccion o ubicacion\n4. Informacion de la empresa\n5. Hablar con asistente', '2026-09-17 20:54:06'),
(23, 2, 'user', 'mateo', 'la marca es un dell, el modelo es h131, la falla es que no funciona no enciende, y el equipo antes encendia ahora ya no', '2026-09-17 20:55:07'),
(24, 2, 'ai', 'Asistencia virtual', 'Entiendo: equipo DELL, modelo es h131, no enciende desde el momento indicado. Primero desconecta perifericos, prueba otro tomacorriente y revisa si el cargador o alguna luz muestran actividad. Si es laptop, realiza un reinicio de energia manteniendo el boton de encendido 15 segundos con el cargador desconectado; despues conecta solo el cargador e intenta una vez. No fuerces el equipo si se calienta u huele a quemado. ¿Es laptop o PC de escritorio y se enciende alguna luz?', '2026-09-17 20:55:08'),
(25, 3, 'ai', 'Asistencia virtual', 'Hola, soy la asistencia virtual de L&M PC Computadoras. Puedo ayudarte con soporte tecnico, direcciones, horarios disponibles e informacion de la empresa. En que se le puede ayudar?\n\nPuedes escribir:\n1. Soporte tecnico\n2. Horarios disponibles\n3. Direccion o ubicacion\n4. Informacion de la empresa\n5. Hablar con asistente', '2026-09-17 21:09:57'),
(26, 4, 'ai', 'Asistencia virtual', 'Hola, soy la asistencia virtual de L&M PC Computadoras. Puedo ayudarte con soporte tecnico, direcciones, horarios disponibles e informacion de la empresa. En que se le puede ayudar?\n\nPuedes escribir:\n1. Soporte tecnico\n2. Horarios disponibles\n3. Direccion o ubicacion\n4. Informacion de la empresa\n5. Hablar con asistente', '2026-09-17 21:10:20'),
(27, 3, 'user', 'mateo', 'Horarios disponibles', '2026-09-17 21:10:21'),
(28, 3, 'ai', 'Asistencia virtual', 'Para revisar disponibilidad, indicame que dia te queda mejor y si necesitas revision, mantenimiento, instalacion de software o reparacion. Si necesitas confirmar una hora exacta, puedo pasarte con asistencia.\n\nTambien puedes escribir una de estas opciones:\n1. Soporte tecnico\n2. Horarios disponibles\n3. Direccion o ubicacion\n4. Informacion de la empresa\n5. Hablar con asistente', '2026-09-17 21:10:21'),
(29, 3, 'user', 'mateo', 'voy a quedar para el dia 18/09/2026 o basicamente el dia de mañana, mantenimiento y revision de la laptop', '2026-09-17 21:11:17'),
(30, 3, 'ai', 'Asistencia virtual', 'Para soporte tecnico indica marca, modelo, falla principal y si el equipo enciende. Si ya tienes esos datos, escribelos juntos y te dare una orientacion concreta.\n\nTambien puedes escribir una de estas opciones:\n1. Soporte tecnico\n2. Horarios disponibles\n3. Direccion o ubicacion\n4. Informacion de la empresa\n5. Hablar con asistente', '2026-09-17 21:11:18'),
(31, 3, 'user', 'mateo', '2', '2026-09-17 21:11:43'),
(32, 3, 'ai', 'Asistencia virtual', 'Para revisar disponibilidad, indicame que dia te queda mejor y si necesitas revision, mantenimiento, instalacion de software o reparacion. Si necesitas confirmar una hora exacta, puedo pasarte con asistencia.\n\nTambien puedes escribir una de estas opciones:\n1. Soporte tecnico\n2. Horarios disponibles\n3. Direccion o ubicacion\n4. Informacion de la empresa\n5. Hablar con asistente', '2026-09-17 21:11:43'),
(33, 3, 'user', 'mateo', 'el dia de mañana revision de pc', '2026-09-17 21:11:59'),
(34, 3, 'ai', 'Asistencia virtual', 'Puedo orientarte con informacion general, pero tu consulta necesita mas detalle. Te voy a pasar con alguien de soporte para que revise tu caso de forma personalizada.', '2026-09-17 21:12:00'),
(35, 3, 'user', 'mateo', '2', '2026-09-17 21:12:15'),
(36, 5, 'ai', 'Asistencia virtual', 'Hola, soy la asistencia virtual de L&M PC Computadoras. Puedo ayudarte con soporte tecnico, direcciones, horarios disponibles e informacion de la empresa. En que se le puede ayudar?\n\nPuedes escribir:\n1. Soporte tecnico\n2. Horarios disponibles\n3. Direccion o ubicacion\n4. Informacion de la empresa\n5. Hablar con asistente', '2026-09-17 21:12:28'),
(37, 6, 'ai', 'Asistencia virtual', 'Hola, soy la asistencia virtual de L&M PC Computadoras. Puedo ayudarte con soporte tecnico, direcciones, horarios disponibles e informacion de la empresa. En que se le puede ayudar?\n\nPuedes escribir:\n1. Soporte tecnico\n2. Horarios disponibles\n3. Direccion o ubicacion\n4. Informacion de la empresa\n5. Hablar con asistente', '2026-09-17 21:12:44'),
(38, 5, 'user', 'mateo', 'horarios disponibles', '2026-09-17 21:12:44'),
(39, 5, 'ai', 'Asistencia virtual', 'Para revisar disponibilidad, indicame que dia te queda mejor y si necesitas revision, mantenimiento, instalacion de software o reparacion. Si necesitas confirmar una hora exacta, puedo pasarte con asistencia.\n\nTambien puedes escribir una de estas opciones:\n1. Soporte tecnico\n2. Horarios disponibles\n3. Direccion o ubicacion\n4. Informacion de la empresa\n5. Hablar con asistente', '2026-09-17 21:12:44'),
(40, 5, 'user', 'mateo', 'me queda mejor el dia de mañana', '2026-09-17 21:13:02'),
(41, 5, 'ai', 'Asistencia virtual', 'Puedo orientarte con informacion general, pero tu consulta necesita mas detalle. Te voy a pasar con alguien de soporte para que revise tu caso de forma personalizada.', '2026-09-17 21:13:02'),
(42, 7, 'ai', 'Asistencia virtual', 'Hola, soy la asistencia virtual de L&M PC Computadoras. Puedo ayudarte con soporte tecnico, direcciones, horarios disponibles e informacion de la empresa. En que se le puede ayudar?\n\nPuedes escribir:\n1. Soporte tecnico\n2. Horarios disponibles\n3. Direccion o ubicacion\n4. Informacion de la empresa\n5. Hablar con asistente', '2026-09-17 21:16:04'),
(43, 8, 'ai', 'Asistencia virtual', 'Hola, soy la asistencia virtual de L&M PC Computadoras. Puedo ayudarte con soporte tecnico, direcciones, horarios disponibles e informacion de la empresa. En que se le puede ayudar?\n\nPuedes escribir:\n1. Soporte tecnico\n2. Horarios disponibles\n3. Direccion o ubicacion\n4. Informacion de la empresa\n5. Hablar con asistente', '2026-09-17 21:16:10'),
(44, 7, 'user', 'mateo', '3', '2026-09-17 21:16:10'),
(45, 7, 'ai', 'Asistencia virtual', 'Puedo ayudarte con direccion y referencias del local. Si necesitas indicaciones exactas, envio o coordenadas, te paso con asistencia para darte informacion precisa.\n\nTambien puedes escribir una de estas opciones:\n1. Soporte tecnico\n2. Horarios disponibles\n3. Direccion o ubicacion\n4. Informacion de la empresa\n5. Hablar con asistente', '2026-09-17 21:16:10'),
(46, 7, 'user', 'mateo', 'ayudame con su direccion y referencias del local', '2026-09-17 21:16:34'),
(47, 7, 'ai', 'Asistencia virtual', 'Puedo ayudarte con direccion y referencias del local. Si necesitas indicaciones exactas, envio o coordenadas, te paso con asistencia para darte informacion precisa.\n\nTambien puedes escribir una de estas opciones:\n1. Soporte tecnico\n2. Horarios disponibles\n3. Direccion o ubicacion\n4. Informacion de la empresa\n5. Hablar con asistente', '2026-09-17 21:16:34'),
(48, 7, 'user', 'mateo', '4', '2026-09-17 21:18:24'),
(49, 7, 'ai', 'Asistencia virtual', 'L&M PC Computadoras ofrece venta de equipos, accesorios, mantenimiento y reparacion. Tambien puedes consultar por laptops, PCs, impresoras, tintas y soporte tecnico.\n\nTambien puedes escribir una de estas opciones:\n1. Soporte tecnico\n2. Horarios disponibles\n3. Direccion o ubicacion\n4. Informacion de la empresa\n5. Hablar con asistente', '2026-09-17 21:18:24'),
(50, 7, 'user', 'mateo', '3', '2026-09-17 21:32:18'),
(51, 7, 'ai', 'Asistencia virtual', 'Puedo ayudarte con direccion y referencias del local. Si necesitas indicaciones exactas, envio o coordenadas, te paso con asistencia para darte informacion precisa.\n\nTambien puedes escribir una de estas opciones:\n1. Soporte tecnico\n2. Horarios disponibles\n3. Direccion o ubicacion\n4. Informacion de la empresa\n5. Hablar con asistente', '2026-09-17 21:32:19'),
(52, 7, 'user', 'mateo', 'con la direccion del local', '2026-09-17 21:32:35'),
(53, 7, 'ai', 'Asistencia virtual', 'Puedo ayudarte con direccion y referencias del local. Si necesitas indicaciones exactas, envio o coordenadas, te paso con asistencia para darte informacion precisa.\n\nTambien puedes escribir una de estas opciones:\n1. Soporte tecnico\n2. Horarios disponibles\n3. Direccion o ubicacion\n4. Informacion de la empresa\n5. Hablar con asistente', '2026-09-17 21:32:35'),
(54, 7, 'user', 'mateo', 'direccion', '2026-09-17 21:33:57'),
(55, 7, 'ai', 'Asistencia virtual', 'Puedo ayudarte con direccion y referencias del local. Si necesitas indicaciones exactas, envio o coordenadas, te paso con asistencia para darte informacion precisa.\n\nTambien puedes escribir una de estas opciones:\n1. Soporte tecnico\n2. Horarios disponibles\n3. Direccion o ubicacion\n4. Informacion de la empresa\n5. Hablar con asistente', '2026-09-17 21:33:57'),
(56, 7, 'user', 'mateo', '3', '2026-09-17 21:34:22'),
(57, 7, 'ai', 'Asistencia virtual', 'Puedo ayudarte con direccion y referencias del local. Si necesitas indicaciones exactas, envio o coordenadas, te paso con asistencia para darte informacion precisa.\n\nTambien puedes escribir una de estas opciones:\n1. Soporte tecnico\n2. Horarios disponibles\n3. Direccion o ubicacion\n4. Informacion de la empresa\n5. Hablar con asistente', '2026-09-17 21:34:22'),
(58, 7, 'user', 'mateo', 'ubicacion', '2026-09-17 21:34:39'),
(59, 7, 'ai', 'Asistencia virtual', 'Puedo ayudarte con direccion y referencias del local. Si necesitas indicaciones exactas, envio o coordenadas, te paso con asistencia para darte informacion precisa.\n\nTambien puedes escribir una de estas opciones:\n1. Soporte tecnico\n2. Horarios disponibles\n3. Direccion o ubicacion\n4. Informacion de la empresa\n5. Hablar con asistente', '2026-09-17 21:34:39'),
(60, 7, 'user', 'mateo', 'donde', '2026-09-17 21:35:34'),
(61, 7, 'ai', 'Asistencia virtual', 'Puedo ayudarte con direccion y referencias del local. Si necesitas indicaciones exactas, envio o coordenadas, te paso con asistencia para darte informacion precisa.\n\nTambien puedes escribir una de estas opciones:\n1. Soporte tecnico\n2. Horarios disponibles\n3. Direccion o ubicacion\n4. Informacion de la empresa\n5. Hablar con asistente', '2026-09-17 21:35:34'),
(62, 7, 'user', 'mateo', 'la direccion', '2026-09-17 21:36:06'),
(63, 7, 'ai', 'Asistencia virtual', 'Puedo ayudarte con direccion y referencias del local. Si necesitas indicaciones exactas, envio o coordenadas, te paso con asistencia para darte informacion precisa.\n\nTambien puedes escribir una de estas opciones:\n1. Soporte tecnico\n2. Horarios disponibles\n3. Direccion o ubicacion\n4. Informacion de la empresa\n5. Hablar con asistente', '2026-09-17 21:36:06'),
(64, 7, 'user', 'mateo', '4', '2026-09-17 21:40:51'),
(65, 7, 'ai', 'Asistencia virtual', 'L&M PC Computadoras ofrece venta de equipos, accesorios, mantenimiento y reparacion. Tambien puedes consultar por laptops, PCs, impresoras, tintas y soporte tecnico.\n\nTambien puedes escribir una de estas opciones:\n1. Soporte tecnico\n2. Horarios disponibles\n3. Direccion o ubicacion\n4. Informacion de la empresa\n5. Hablar con asistente', '2026-09-17 21:40:51'),
(66, 7, 'user', 'mateo', '3', '2026-09-17 21:41:03'),
(67, 7, 'ai', 'Asistencia virtual', 'Puedo ayudarte con direccion y referencias del local. Si necesitas indicaciones exactas, envio o coordenadas, te paso con asistencia para darte informacion precisa.\n\nTambien puedes escribir una de estas opciones:\n1. Soporte tecnico\n2. Horarios disponibles\n3. Direccion o ubicacion\n4. Informacion de la empresa\n5. Hablar con asistente', '2026-09-17 21:41:03'),
(68, 7, 'user', 'mateo', 'la ubicacion', '2026-09-17 21:41:13'),
(69, 7, 'ai', 'Asistencia virtual', 'Puedo ayudarte con direccion y referencias del local. Si necesitas indicaciones exactas, envio o coordenadas, te paso con asistencia para darte informacion precisa.\n\nTambien puedes escribir una de estas opciones:\n1. Soporte tecnico\n2. Horarios disponibles\n3. Direccion o ubicacion\n4. Informacion de la empresa\n5. Hablar con asistente', '2026-09-17 21:41:13'),
(70, 9, 'ai', 'Asistencia virtual', 'Hola, soy la asistencia virtual de L&M PC Computadoras. Puedo ayudarte con soporte tecnico, direcciones, horarios disponibles e informacion de la empresa. En que se le puede ayudar?\n\nPuedes escribir:\n1. Soporte tecnico\n2. Horarios disponibles\n3. Direccion o ubicacion\n4. Informacion de la empresa\n5. Hablar con asistente', '2026-09-17 21:41:40'),
(71, 10, 'ai', 'Asistencia virtual', 'Hola, soy la asistencia virtual de L&M PC Computadoras. Puedo ayudarte con soporte tecnico, direcciones, horarios disponibles e informacion de la empresa. En que se le puede ayudar?\n\nPuedes escribir:\n1. Soporte tecnico\n2. Horarios disponibles\n3. Direccion o ubicacion\n4. Informacion de la empresa\n5. Hablar con asistente', '2026-09-17 21:41:40'),
(72, 11, 'ai', 'Asistencia virtual', 'Hola, soy la asistencia virtual de L&M PC Computadoras. Puedo ayudarte con soporte tecnico, direcciones, horarios disponibles e informacion de la empresa. En que se le puede ayudar?\n\nPuedes escribir:\n1. Soporte tecnico\n2. Horarios disponibles\n3. Direccion o ubicacion\n4. Informacion de la empresa\n5. Hablar con asistente', '2026-09-17 21:41:41'),
(73, 12, 'ai', 'Asistencia virtual', 'Hola, soy la asistencia virtual de L&M PC Computadoras. Puedo ayudarte con soporte tecnico, direcciones, horarios disponibles e informacion de la empresa. En que se le puede ayudar?\n\nPuedes escribir:\n1. Soporte tecnico\n2. Horarios disponibles\n3. Direccion o ubicacion\n4. Informacion de la empresa\n5. Hablar con asistente', '2026-09-17 21:41:42'),
(74, 13, 'ai', 'Asistencia virtual', 'Hola, soy la asistencia virtual de L&M PC Computadoras. Puedo ayudarte con soporte tecnico, direcciones, horarios disponibles e informacion de la empresa. En que se le puede ayudar?\n\nPuedes escribir:\n1. Soporte tecnico\n2. Horarios disponibles\n3. Direccion o ubicacion\n4. Informacion de la empresa\n5. Hablar con asistente', '2026-09-17 21:41:42'),
(75, 14, 'ai', 'Asistencia virtual', 'Hola, soy la asistencia virtual de L&M PC Computadoras. Puedo ayudarte con soporte tecnico, direcciones, horarios disponibles e informacion de la empresa. En que se le puede ayudar?\n\nPuedes escribir:\n1. Soporte tecnico\n2. Horarios disponibles\n3. Direccion o ubicacion\n4. Informacion de la empresa\n5. Hablar con asistente', '2026-09-17 21:41:43'),
(76, 15, 'ai', 'Asistencia virtual', 'Hola, soy la asistencia virtual de L&M PC Computadoras. Puedo ayudarte con soporte tecnico, direcciones, horarios disponibles e informacion de la empresa. En que se le puede ayudar?\n\nPuedes escribir:\n1. Soporte tecnico\n2. Horarios disponibles\n3. Direccion o ubicacion\n4. Informacion de la empresa\n5. Hablar con asistente', '2026-09-17 21:41:44'),
(77, 15, 'user', 'mateo', '3', '2026-09-17 21:41:55'),
(78, 15, 'ai', 'Asistencia virtual', 'Puedo ayudarte con direccion y referencias del local. Si necesitas indicaciones exactas, envio o coordenadas, te paso con asistencia para darte informacion precisa.\n\nTambien puedes escribir una de estas opciones:\n1. Soporte tecnico\n2. Horarios disponibles\n3. Direccion o ubicacion\n4. Informacion de la empresa\n5. Hablar con asistente', '2026-09-17 21:41:56'),
(79, 15, 'user', 'mateo', 'la ubicacion', '2026-09-17 21:42:05'),
(80, 15, 'ai', 'Asistencia virtual', 'Puedo ayudarte con direccion y referencias del local. Si necesitas indicaciones exactas, envio o coordenadas, te paso con asistencia para darte informacion precisa.\n\nTambien puedes escribir una de estas opciones:\n1. Soporte tecnico\n2. Horarios disponibles\n3. Direccion o ubicacion\n4. Informacion de la empresa\n5. Hablar con asistente', '2026-09-17 21:42:06'),
(81, 15, 'user', 'mateo', 'la ubicacion', '2026-09-17 21:50:30'),
(82, 15, 'ai', 'Asistencia virtual', 'La ubicacion de L&M PC Computadoras es: Ecuador, Quito, sector La Ecuatoriana, por la Joaquin Jativa. Referencia: Las Orquideas / Oe9 Martha Bucaram / S37-49 / S37A. Puedes abrir la ubicacion en Google Maps: https://maps.app.goo.gl/V8ghM2XY9M4H8v6Z9', '2026-09-17 21:50:31'),
(83, 15, 'user', 'mateo', 'ayudame con el asistente', '2026-09-17 21:50:52'),
(84, 15, 'ai', 'Asistencia virtual', 'Entiendo. Te voy a pasar con alguien de soporte para que revise tu caso con mas detalle. Por favor deja escrito marca, modelo del equipo, falla principal y un numero de contacto si aplica.', '2026-09-17 21:50:52'),
(85, 16, 'ai', 'Asistencia virtual', 'Hola, soy la asistencia virtual de L&M PC Computadoras. Puedo ayudarte con soporte tecnico, direcciones, horarios disponibles e informacion de la empresa. En que se le puede ayudar?\n\nPuedes escribir:\n1. Soporte tecnico\n2. Horarios disponibles\n3. Direccion o ubicacion\n4. Informacion de la empresa\n5. Hablar con asistente', '2026-09-17 21:51:08');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `citas`
--

CREATE TABLE `citas` (
  `id_cita` int(11) NOT NULL,
  `id_usuario` int(100) DEFAULT NULL,
  `nombre` varchar(255) NOT NULL,
  `apellido` varchar(255) NOT NULL,
  `correo` varchar(255) NOT NULL,
  `cedula` varchar(20) DEFAULT NULL,
  `archivo_ruc` varchar(255) DEFAULT NULL,
  `archivo_cedula` varchar(255) DEFAULT NULL,
  `accion_sensible` tinyint(1) DEFAULT 0,
  `fecha` date NOT NULL,
  `telefono` varchar(25) NOT NULL,
  `motivo` varchar(500) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `citas`
--

INSERT INTO `citas` (`id_cita`, `id_usuario`, `nombre`, `apellido`, `correo`, `cedula`, `archivo_ruc`, `archivo_cedula`, `accion_sensible`, `fecha`, `telefono`, `motivo`) VALUES
(35, 27, 'mateo', 'Esteban', 'mateo014esteban@gmail.com', '1728734664', 'docs/1789673438_ruc_selfiegato.jpg', 'docs/1789673438_cedula_selfiegato.jpg', 0, '2026-09-02', '095353121456', 'mantenimiento de la computadora');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `contacto`
--

CREATE TABLE `contacto` (
  `id_mensaje` int(11) NOT NULL,
  `id_usuario` int(100) DEFAULT NULL,
  `nombre` varchar(255) NOT NULL,
  `correo` varchar(255) NOT NULL,
  `compania` varchar(255) NOT NULL,
  `mensaje` text NOT NULL,
  `cedula` varchar(20) DEFAULT NULL,
  `archivo_ruc` varchar(255) DEFAULT NULL,
  `archivo_cedula` varchar(255) DEFAULT NULL,
  `fecha` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `contacto`
--

INSERT INTO `contacto` (`id_mensaje`, `id_usuario`, `nombre`, `correo`, `compania`, `mensaje`, `cedula`, `archivo_ruc`, `archivo_cedula`, `fecha`) VALUES
(2, NULL, 'mateo', 'mateo.cisneros12@gmail.com', 'Eco-Net', 'Convenio y comprobacion empresarial', '1728734665', '../uploads/1774381634_ruc_ejemplo.webp', '../uploads/1774381634_cedula_ejemplo 2.webp', '2026-03-24 19:47:14'),
(3, NULL, 'mateo', 'mateo.cisneros12@gmail.com', 'NET-LIFE', 'Internet', '1728734665', '../docs/1774383075_ruc_ejemplo.webp', '../docs/1774383075_cedula_ejemplo 2.webp', '2026-03-24 20:11:15'),
(4, NULL, 'Mateo Cisneros', 'mateo.cisneros@istvidanueva.edu.ec', 'Ecuavisa', 'Hola', '1728734665', '../docs/1777424459_ruc_ejemplo 1.png', '../docs/1777424459_cedula_ejemplo 2.webp', '2026-04-29 01:00:59'),
(5, NULL, 'juan', 'mateo.cisneros@istvidanueva.edu.ec', 'Telegram', 'Consulta sobre producto', NULL, NULL, NULL, '2026-08-19 21:39:28'),
(6, NULL, 'juan', 'mateo.cisneros@istvidanueva.edu.ec', 'Telegram', 'Otra consulta', NULL, NULL, NULL, '2026-08-19 21:44:12');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `detalles_pedido`
--

CREATE TABLE `detalles_pedido` (
  `id_detalle` int(11) NOT NULL,
  `id_pedido` int(11) NOT NULL,
  `id_producto` int(11) NOT NULL,
  `cantidad` int(11) NOT NULL,
  `precio_unitario` decimal(10,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `failed_jobs`
--

CREATE TABLE `failed_jobs` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `uuid` varchar(255) NOT NULL,
  `connection` varchar(255) NOT NULL,
  `queue` varchar(255) NOT NULL,
  `payload` longtext NOT NULL,
  `exception` longtext NOT NULL,
  `failed_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `jobs`
--

CREATE TABLE `jobs` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `queue` varchar(255) NOT NULL,
  `payload` longtext NOT NULL,
  `attempts` smallint(5) UNSIGNED NOT NULL,
  `reserved_at` int(10) UNSIGNED DEFAULT NULL,
  `available_at` int(10) UNSIGNED NOT NULL,
  `created_at` int(10) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `job_batches`
--

CREATE TABLE `job_batches` (
  `id` varchar(255) NOT NULL,
  `name` varchar(255) NOT NULL,
  `total_jobs` int(11) NOT NULL,
  `pending_jobs` int(11) NOT NULL,
  `failed_jobs` int(11) NOT NULL,
  `failed_job_ids` longtext NOT NULL,
  `options` mediumtext DEFAULT NULL,
  `cancelled_at` int(11) DEFAULT NULL,
  `created_at` int(11) NOT NULL,
  `finished_at` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `migrations`
--

CREATE TABLE `migrations` (
  `id` int(10) UNSIGNED NOT NULL,
  `migration` varchar(255) NOT NULL,
  `batch` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `migrations`
--

INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES
(1, '0001_01_01_000000_create_users_table', 1),
(2, '0001_01_01_000001_create_cache_table', 1),
(3, '0001_01_01_000002_create_jobs_table', 1),
(4, '2026_06_24_000001_create_legacy_business_tables', 1),
(5, '2026_08_14_000001_create_telegram_tables', 2),
(6, '2026_08_14_000002_create_telegram_action_tables', 3),
(7, '2026_08_16_010448_create_telegram_sessions_table', 4);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `notificaciones`
--

CREATE TABLE `notificaciones` (
  `id_notificacion` int(11) NOT NULL,
  `id_remitente` int(11) DEFAULT NULL,
  `id_usuario` int(11) DEFAULT NULL,
  `titulo` varchar(180) NOT NULL,
  `mensaje` text NOT NULL,
  `correo_destino` varchar(255) NOT NULL,
  `estado_correo` varchar(30) NOT NULL DEFAULT 'pendiente',
  `fecha_creacion` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `notificaciones`
--

INSERT INTO `notificaciones` (`id_notificacion`, `id_remitente`, `id_usuario`, `titulo`, `mensaje`, `correo_destino`, `estado_correo`, `fecha_creacion`) VALUES
(2, 19, 20, 'Precios Nuevos', 'Hola Notificacion', 'mateo014esteban@gmail.com', 'enviado', '2026-05-30 00:10:15'),
(3, 27, 27, 'Auditoria', 'Asistencia Mensual', 'mateo014esteban@gmail.com', 'enviado', '2026-09-17 22:55:44');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `password_reset_tokens`
--

CREATE TABLE `password_reset_tokens` (
  `email` varchar(255) NOT NULL,
  `token` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `pedidos`
--

CREATE TABLE `pedidos` (
  `id_pedido` int(11) NOT NULL,
  `id_usuario` int(100) NOT NULL,
  `fecha_pedido` datetime NOT NULL DEFAULT current_timestamp(),
  `total` decimal(10,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `productos`
--

CREATE TABLE `productos` (
  `id_producto` int(11) NOT NULL,
  `nombre` varchar(255) NOT NULL,
  `serie` varchar(100) NOT NULL,
  `fecha` date NOT NULL,
  `unidades` int(255) NOT NULL,
  `precio` decimal(10,2) NOT NULL,
  `imagen` varchar(255) NOT NULL,
  `id_soporte` int(11) DEFAULT NULL,
  `categoria` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `productos`
--

INSERT INTO `productos` (`id_producto`, `nombre`, `serie`, `fecha`, `unidades`, `precio`, `imagen`, `id_soporte`, `categoria`) VALUES
(1, 'NOTEBOOK ASUS RP058 CORE 9', 'Serie Core i9', '2026-02-20', 35, 1516.16, '../uploads/asus.jpeg', 1, 'ASUS'),
(2, 'PC DELL', 'SERIE 7000', '2026-02-19', 16, 798.50, '../uploads/pcdell.webp', 2, 'PC'),
(3, 'Tablet Ipad', 'Ipad', '2026-02-19', 8, 698.50, '../uploads/tablets.webp', 2, 'Tablets'),
(4, 'Tinta EPSON 1000 ML', 'Tinta', '2026-02-19', 19, 300.00, '../uploads/tinta.webp', 4, 'TINTA1000'),
(5, 'Unidad duplicadora de disco USB 3.0 Tipo-C Grabadora de CD para computadora portátil Mac Pro MacBook', ' 120 mm/4,72 pulgadas, 80 mm/3,15 pulgadas', '2026-02-19', 0, 40.00, '../uploads/duplicadora.webp', 2, 'DUPLICADORACD'),
(6, 'HP 2009', 'i7 14 900 K', '2026-02-19', 32, 720.50, '../uploads/pchp.jpg', 2, 'PCHP'),
(7, 'LENOVO', 'i7 13 900 F', '2025-02-19', 75, 1420.50, '../uploads/lenovo.jpg', 1, 'lenovo'),
(8, 'MSI GAMER 2024', 'i9 13 900 K', '2026-12-19', 84, 1400.50, '../uploads/msi.jpeg', 1, 'MSI'),
(10, 'Dell Inspiron 16 5630', 'Ryzen 6000', '2025-11-12', 22, 1100.00, '../uploads/dell.jpeg', 1, 'DELL'),
(11, 'Copystars Dvd Duplicator 24x Cd-dvd-burner 1 A 3 Copiadora S', 'SYS-1-3-ASUS/LG-CST', '2023-12-24', 39, 31.90, '../uploads/duplicadoradvd.webp', 2, 'DUPLICADORADVD'),
(12, 'Blu-ray / CD / DVD\r\nDuplicator', 'B01-SSP16XL', '2025-09-12', 11, 100.00, '../uploads/duplicadorablu.jpg', 2, 'DUPLICADORABLU'),
(13, 'Impresora EPSON L8050 EcoTank Tinta Continua Fotografica PVC', 'EcoTank L8050', '2021-12-12', 91, 300.00, '../uploads/epson.webp', 4, 'EPSON'),
(14, 'Impresora Canon G1110 Sistema Continuo + Tintas Incluidas', 'SKU 31650', '2025-10-08', 5, 300.40, '../uploads/canon.webp', 4, 'CANON'),
(15, '100ml Tinta Dye Para Impresoras Epson La Mejor Calidad', 'EDYE', '2026-03-08', 22, 12.00, '../uploads/tinta.webp', 4, 'TINTA100'),
(16, 'Tinta Dye Premium Para Impresoras Epson Ecotank 1000 ml', 'U0079', '2024-12-24', 5, 734.00, '../uploads/tinta2.webp', 4, 'TINTA1000'),
(19, 'Omnibook Hp RP058 CORE 9', 'Serie Core i9 14900', '2026-02-20', 27, 1516.16, 'img_69cd95cdba168.jpeg', NULL, 'ominibook');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `sessions`
--

CREATE TABLE `sessions` (
  `id` varchar(255) NOT NULL,
  `user_id` int(10) UNSIGNED DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `payload` longtext NOT NULL,
  `last_activity` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `soporte`
--

CREATE TABLE `soporte` (
  `id_soporte` int(11) NOT NULL,
  `Nombre` varchar(255) NOT NULL,
  `Correo` varchar(255) NOT NULL,
  `cedula` varchar(20) DEFAULT NULL,
  `archivo_ruc` varchar(255) DEFAULT NULL,
  `archivo_cedula` varchar(255) DEFAULT NULL,
  `Compania` varchar(255) NOT NULL,
  `Mensaje` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `soporte`
--

INSERT INTO `soporte` (`id_soporte`, `Nombre`, `Correo`, `cedula`, `archivo_ruc`, `archivo_cedula`, `Compania`, `Mensaje`) VALUES
(1, 'mateo', 'mateo.cisneros@gmail.com', NULL, NULL, NULL, 'Vida Nueva', 'Union y convivencia por los estudiantes y pasantes'),
(2, 'kai ', 'kaisuarez89@gmail.com', NULL, NULL, NULL, 'ServiTECH', 'Convenio institucional'),
(3, 'kai ', 'kaisuarez89@gmail.com', NULL, NULL, NULL, 'ServiTECH', 'convenio'),
(4, 'Mateo', 'mateo.cisneros@istvidanueva.edu.ec', NULL, NULL, NULL, 'Eco-Net', 'Convenio Institucional entre la empresas'),
(5, 'Loomis', 'mateo.cisneros13@gmail.com', '1728734665', '../docs/1774390079_ruc_ejemplo 2.webp', '../docs/1774390079_cedula_prov_ejemplo.webp', 'Nvidia', 'Graficas'),
(6, 'kim hangs', 'mateo014esteban@gmail.com', '1728734666', 'docs/1789684359_ruc_Captura_de_pantalla_2026-09-17_143100.png', 'docs/1789684359_cedula_prov_Captura_de_pantalla_2026-09-09_194857.png', 'ARM', 'Convenio de exportacion de componentes electronicos');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `telegram_accounts`
--

CREATE TABLE `telegram_accounts` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `id_usuario` int(10) UNSIGNED NOT NULL,
  `telegram_chat_id` varchar(32) NOT NULL,
  `telegram_user_id` varchar(32) NOT NULL,
  `username` varchar(255) DEFAULT NULL,
  `telefono` varchar(20) DEFAULT NULL,
  `telefono_verificado_en` timestamp NULL DEFAULT NULL,
  `ultima_actividad_en` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `telegram_accounts`
--

INSERT INTO `telegram_accounts` (`id`, `id_usuario`, `telegram_chat_id`, `telegram_user_id`, `username`, `telefono`, `telefono_verificado_en`, `ultima_actividad_en`, `created_at`, `updated_at`) VALUES
(3, 7, '8719614329', '8719614329', 'cesar14568', NULL, NULL, '2026-09-01 00:39:21', '2026-08-16 03:03:08', '2026-09-01 00:39:21');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `telegram_action_audits`
--

CREATE TABLE `telegram_action_audits` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `id_usuario` int(11) DEFAULT NULL,
  `telegram_chat_id` varchar(32) DEFAULT NULL,
  `accion` varchar(80) NOT NULL,
  `estado` varchar(30) NOT NULL,
  `detalle` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`detalle`)),
  `ip_origen` varchar(45) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `telegram_action_requests`
--

CREATE TABLE `telegram_action_requests` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `id_usuario` int(11) NOT NULL,
  `telegram_chat_id` varchar(32) NOT NULL,
  `accion` varchar(80) NOT NULL,
  `datos` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL CHECK (json_valid(`datos`)),
  `resumen` varchar(1000) NOT NULL,
  `confirmacion_hash` varchar(64) NOT NULL,
  `estado` varchar(24) NOT NULL DEFAULT 'pendiente',
  `expira_en` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `confirmada_en` timestamp NULL DEFAULT NULL,
  `error` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `telegram_link_codes`
--

CREATE TABLE `telegram_link_codes` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `id_usuario` int(11) NOT NULL,
  `codigo_hash` varchar(64) NOT NULL,
  `expira_en` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `usado_en` timestamp NULL DEFAULT NULL,
  `ip_solicitud` varchar(45) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `telegram_sessions`
--

CREATE TABLE `telegram_sessions` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `telegram_chat_id` varchar(255) NOT NULL,
  `telegram_user_id` varchar(255) DEFAULT NULL,
  `id_usuario` bigint(20) UNSIGNED DEFAULT NULL,
  `state` varchar(255) DEFAULT NULL,
  `categoria` varchar(255) DEFAULT NULL,
  `cita_fecha` varchar(255) DEFAULT NULL,
  `contacto_tipo` varchar(255) DEFAULT NULL,
  `payload` text DEFAULT NULL,
  `ultima_actividad_en` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `telegram_sessions`
--

INSERT INTO `telegram_sessions` (`id`, `telegram_chat_id`, `telegram_user_id`, `id_usuario`, `state`, `categoria`, `cita_fecha`, `contacto_tipo`, `payload`, `ultima_actividad_en`, `created_at`, `updated_at`) VALUES
(18, '8719614329', NULL, NULL, 'MENU', 'pc', '2026-09-01', NULL, '[]', '2026-09-01 00:39:22', '2026-09-01 00:38:59', '2026-09-01 00:39:22');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `telegram_webhook_updates`
--

CREATE TABLE `telegram_webhook_updates` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `update_id` bigint(20) UNSIGNED NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `telegram_webhook_updates`
--

INSERT INTO `telegram_webhook_updates` (`id`, `update_id`, `created_at`) VALUES
(1, 858395759, '2026-08-15 00:31:21'),
(2, 858395760, '2026-08-15 00:41:13'),
(3, 858395761, '2026-08-15 01:47:26'),
(4, 858395762, '2026-08-15 02:01:09'),
(5, 858395763, '2026-08-15 02:01:13'),
(6, 858395764, '2026-08-15 02:01:43'),
(7, 858395765, '2026-08-15 21:50:23'),
(8, 858395766, '2026-08-15 21:59:31'),
(9, 858395767, '2026-08-15 22:00:08'),
(10, 858395768, '2026-08-15 22:02:04'),
(11, 858395769, '2026-08-15 22:02:36'),
(12, 858395770, '2026-08-15 22:02:42'),
(13, 858395771, '2026-08-15 22:03:08'),
(14, 858395772, '2026-08-15 22:03:12'),
(15, 858395773, '2026-08-15 22:41:03'),
(16, 858395774, '2026-08-15 22:41:31'),
(17, 858395775, '2026-08-15 22:45:53'),
(18, 858395776, '2026-08-15 22:45:58'),
(19, 858395777, '2026-08-15 22:46:03'),
(20, 858395778, '2026-08-15 22:46:08'),
(21, 858395779, '2026-08-15 22:46:11'),
(22, 858395780, '2026-08-15 22:48:11'),
(23, 858395781, '2026-08-15 22:48:15'),
(24, 858395782, '2026-08-15 22:48:18'),
(25, 858395783, '2026-08-16 00:51:16'),
(26, 858395784, '2026-08-16 00:51:19'),
(27, 858395785, '2026-08-16 00:51:27'),
(28, 858395786, '2026-08-16 00:51:31'),
(29, 858395787, '2026-08-16 00:51:34'),
(30, 858395788, '2026-08-16 00:55:20'),
(31, 858395789, '2026-08-16 00:55:25'),
(32, 858395790, '2026-08-16 00:55:54'),
(33, 858395791, '2026-08-16 00:55:56'),
(34, 858395792, '2026-08-16 01:02:12'),
(35, 858395793, '2026-08-16 01:02:16'),
(36, 858395794, '2026-08-16 01:02:20'),
(37, 858395795, '2026-08-16 01:02:23'),
(38, 858395796, '2026-08-16 01:20:59'),
(39, 858395797, '2026-08-16 01:21:16'),
(40, 858395798, '2026-08-16 01:21:23'),
(41, 858395799, '2026-08-16 01:21:31'),
(42, 858395800, '2026-08-16 01:21:35'),
(43, 858395801, '2026-08-16 01:21:43'),
(44, 858395802, '2026-08-16 01:23:08'),
(45, 858395803, '2026-08-16 01:23:12'),
(46, 858395804, '2026-08-16 01:23:45'),
(47, 858395805, '2026-08-16 01:23:51'),
(48, 858395806, '2026-08-16 02:23:33'),
(49, 858395807, '2026-08-16 02:43:03'),
(50, 858395808, '2026-08-16 02:43:07'),
(51, 858395809, '2026-08-16 02:43:10'),
(52, 858395810, '2026-08-16 02:43:13'),
(53, 858395811, '2026-08-16 02:43:17'),
(54, 858395812, '2026-08-16 02:43:19'),
(55, 858395813, '2026-08-16 02:43:22'),
(56, 858395814, '2026-08-16 02:43:27'),
(57, 858395815, '2026-08-16 02:43:37'),
(58, 858395816, '2026-08-16 02:43:43'),
(59, 858395817, '2026-08-16 02:43:47'),
(60, 858395818, '2026-08-16 02:43:50'),
(61, 858395819, '2026-08-16 02:57:12'),
(62, 858395820, '2026-08-16 02:57:25'),
(63, 858395821, '2026-08-16 02:57:48'),
(64, 858395822, '2026-08-16 02:57:57'),
(65, 858395823, '2026-08-16 02:58:04'),
(66, 858395824, '2026-08-16 02:58:08'),
(67, 858395825, '2026-08-16 02:58:27'),
(68, 858395826, '2026-08-16 02:58:49'),
(69, 858395827, '2026-08-16 02:58:57'),
(70, 858395828, '2026-08-16 02:59:16'),
(71, 858395829, '2026-08-16 02:59:22'),
(72, 858395830, '2026-08-16 02:59:28'),
(73, 858395831, '2026-08-16 02:59:54'),
(74, 858395832, '2026-08-16 03:00:11'),
(75, 858395833, '2026-08-19 20:51:25'),
(76, 858395834, '2026-08-19 20:51:30'),
(77, 858395835, '2026-08-19 20:53:53'),
(78, 858395836, '2026-08-19 21:37:46'),
(79, 858395837, '2026-08-19 21:37:57'),
(80, 858395838, '2026-08-19 21:38:36'),
(81, 858395839, '2026-08-19 21:38:43'),
(82, 858395840, '2026-08-19 21:39:20'),
(83, 858395841, '2026-08-19 21:39:27'),
(84, 858395842, '2026-08-19 21:41:19'),
(85, 858395843, '2026-08-19 21:41:22'),
(86, 858395844, '2026-08-19 21:41:31'),
(87, 858395845, '2026-08-19 21:42:31'),
(88, 858395846, '2026-08-19 21:42:41'),
(89, 858395847, '2026-08-19 21:42:45'),
(90, 858395848, '2026-08-19 21:42:57'),
(91, 858395849, '2026-08-19 21:43:09'),
(92, 858395850, '2026-08-19 21:43:15'),
(93, 858395851, '2026-08-19 21:44:10'),
(94, 858395852, '2026-08-21 22:21:20'),
(95, 858395853, '2026-08-31 19:21:57'),
(96, 858395854, '2026-08-31 19:27:54'),
(97, 858395855, '2026-08-31 19:27:59'),
(98, 858395856, '2026-08-31 19:28:04'),
(99, 858395857, '2026-08-31 19:38:02'),
(100, 858395858, '2026-08-31 19:38:07'),
(101, 858395859, '2026-08-31 19:38:14'),
(102, 858395860, '2026-08-31 19:38:57'),
(103, 858395861, '2026-08-31 19:39:03'),
(104, 858395862, '2026-08-31 19:39:20');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `usuarios`
--

CREATE TABLE `usuarios` (
  `id_usuario` int(100) NOT NULL,
  `usuario` varchar(200) NOT NULL,
  `correo` varchar(255) NOT NULL,
  `cedula` varchar(20) DEFAULT NULL,
  `archivo_ruc` varchar(255) DEFAULT NULL,
  `archivo_cedula` varchar(255) DEFAULT NULL,
  `foto_perfil` varchar(255) DEFAULT NULL,
  `contraseña` varchar(150) NOT NULL,
  `rol` varchar(100) NOT NULL,
  `reset_token` varchar(255) DEFAULT NULL,
  `reset_expiration` datetime DEFAULT NULL,
  `email_verified` tinyint(1) NOT NULL DEFAULT 0,
  `email_verification_token` varchar(255) DEFAULT NULL,
  `email_verification_expires` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `usuarios`
--

INSERT INTO `usuarios` (`id_usuario`, `usuario`, `correo`, `cedula`, `archivo_ruc`, `archivo_cedula`, `foto_perfil`, `contraseña`, `rol`, `reset_token`, `reset_expiration`, `email_verified`, `email_verification_token`, `email_verification_expires`) VALUES
(27, 'mateo', 'mateo014esteban@gmail.com', '1728734664', 'C:\\xampp\\htdocs\\lymPCComputadoras\\lym_pcComputadoras\\lympcComputadora-app\\storage\\app/public/uploads/1789670309_ruc_selfiegato.jpg', 'C:\\xampp\\htdocs\\lymPCComputadoras\\lym_pcComputadoras\\lympcComputadora-app\\storage\\app/public/uploads/1789670309_cedula_selfiegato.jpg', 'perfil_27_1789670594.jpg', '$2y$10$kpsbEsT3AZe7625WXUyOLuf8RPwwDJgNi6fchEpwDmvwZ96kYnOmK', 'tecnico', NULL, NULL, 1, NULL, NULL);

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `cache`
--
ALTER TABLE `cache`
  ADD PRIMARY KEY (`key`),
  ADD KEY `cache_expiration_index` (`expiration`);

--
-- Indices de la tabla `cache_locks`
--
ALTER TABLE `cache_locks`
  ADD PRIMARY KEY (`key`),
  ADD KEY `cache_locks_expiration_index` (`expiration`);

--
-- Indices de la tabla `chat_conversaciones`
--
ALTER TABLE `chat_conversaciones`
  ADD PRIMARY KEY (`id_conversacion`);

--
-- Indices de la tabla `chat_mensajes`
--
ALTER TABLE `chat_mensajes`
  ADD PRIMARY KEY (`id_mensaje`),
  ADD KEY `id_conversacion` (`id_conversacion`);

--
-- Indices de la tabla `citas`
--
ALTER TABLE `citas`
  ADD PRIMARY KEY (`id_cita`),
  ADD KEY `fk_citas_usuarios` (`id_usuario`);

--
-- Indices de la tabla `contacto`
--
ALTER TABLE `contacto`
  ADD PRIMARY KEY (`id_mensaje`),
  ADD KEY `fk_mensaje_usuario` (`id_usuario`);

--
-- Indices de la tabla `detalles_pedido`
--
ALTER TABLE `detalles_pedido`
  ADD PRIMARY KEY (`id_detalle`),
  ADD KEY `fk_detalles_pedido` (`id_pedido`),
  ADD KEY `fk_detalles_producto` (`id_producto`);

--
-- Indices de la tabla `failed_jobs`
--
ALTER TABLE `failed_jobs`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `failed_jobs_uuid_unique` (`uuid`),
  ADD KEY `failed_jobs_connection_queue_failed_at_index` (`connection`,`queue`,`failed_at`);

--
-- Indices de la tabla `jobs`
--
ALTER TABLE `jobs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `jobs_queue_index` (`queue`);

--
-- Indices de la tabla `job_batches`
--
ALTER TABLE `job_batches`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `migrations`
--
ALTER TABLE `migrations`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `notificaciones`
--
ALTER TABLE `notificaciones`
  ADD PRIMARY KEY (`id_notificacion`);

--
-- Indices de la tabla `password_reset_tokens`
--
ALTER TABLE `password_reset_tokens`
  ADD PRIMARY KEY (`email`);

--
-- Indices de la tabla `pedidos`
--
ALTER TABLE `pedidos`
  ADD PRIMARY KEY (`id_pedido`),
  ADD KEY `fk_pedidos_usuarios` (`id_usuario`);

--
-- Indices de la tabla `productos`
--
ALTER TABLE `productos`
  ADD PRIMARY KEY (`id_producto`),
  ADD UNIQUE KEY `UNIQUE` (`serie`),
  ADD KEY `fk_productos_soporte` (`id_soporte`);

--
-- Indices de la tabla `sessions`
--
ALTER TABLE `sessions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `sessions_user_id_index` (`user_id`),
  ADD KEY `sessions_last_activity_index` (`last_activity`);

--
-- Indices de la tabla `soporte`
--
ALTER TABLE `soporte`
  ADD PRIMARY KEY (`id_soporte`);

--
-- Indices de la tabla `telegram_accounts`
--
ALTER TABLE `telegram_accounts`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `telegram_action_audits`
--
ALTER TABLE `telegram_action_audits`
  ADD PRIMARY KEY (`id`),
  ADD KEY `id_usuario` (`id_usuario`);

--
-- Indices de la tabla `telegram_action_requests`
--
ALTER TABLE `telegram_action_requests`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `confirmacion_hash` (`confirmacion_hash`),
  ADD KEY `id_usuario` (`id_usuario`),
  ADD KEY `estado` (`estado`);

--
-- Indices de la tabla `telegram_link_codes`
--
ALTER TABLE `telegram_link_codes`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `codigo_hash` (`codigo_hash`),
  ADD KEY `id_usuario` (`id_usuario`);

--
-- Indices de la tabla `telegram_sessions`
--
ALTER TABLE `telegram_sessions`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `telegram_sessions_telegram_chat_id_unique` (`telegram_chat_id`),
  ADD KEY `telegram_sessions_id_usuario_index` (`id_usuario`),
  ADD KEY `telegram_sessions_telegram_user_id_index` (`telegram_user_id`);

--
-- Indices de la tabla `telegram_webhook_updates`
--
ALTER TABLE `telegram_webhook_updates`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `update_id` (`update_id`);

--
-- Indices de la tabla `usuarios`
--
ALTER TABLE `usuarios`
  ADD PRIMARY KEY (`id_usuario`);

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `chat_conversaciones`
--
ALTER TABLE `chat_conversaciones`
  MODIFY `id_conversacion` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- AUTO_INCREMENT de la tabla `chat_mensajes`
--
ALTER TABLE `chat_mensajes`
  MODIFY `id_mensaje` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=86;

--
-- AUTO_INCREMENT de la tabla `citas`
--
ALTER TABLE `citas`
  MODIFY `id_cita` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=36;

--
-- AUTO_INCREMENT de la tabla `contacto`
--
ALTER TABLE `contacto`
  MODIFY `id_mensaje` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT de la tabla `detalles_pedido`
--
ALTER TABLE `detalles_pedido`
  MODIFY `id_detalle` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=24;

--
-- AUTO_INCREMENT de la tabla `failed_jobs`
--
ALTER TABLE `failed_jobs`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `jobs`
--
ALTER TABLE `jobs`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `migrations`
--
ALTER TABLE `migrations`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT de la tabla `notificaciones`
--
ALTER TABLE `notificaciones`
  MODIFY `id_notificacion` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT de la tabla `pedidos`
--
ALTER TABLE `pedidos`
  MODIFY `id_pedido` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT de la tabla `productos`
--
ALTER TABLE `productos`
  MODIFY `id_producto` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=20;

--
-- AUTO_INCREMENT de la tabla `soporte`
--
ALTER TABLE `soporte`
  MODIFY `id_soporte` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT de la tabla `telegram_accounts`
--
ALTER TABLE `telegram_accounts`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT de la tabla `telegram_action_audits`
--
ALTER TABLE `telegram_action_audits`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `telegram_action_requests`
--
ALTER TABLE `telegram_action_requests`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `telegram_link_codes`
--
ALTER TABLE `telegram_link_codes`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT de la tabla `telegram_sessions`
--
ALTER TABLE `telegram_sessions`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=19;

--
-- AUTO_INCREMENT de la tabla `telegram_webhook_updates`
--
ALTER TABLE `telegram_webhook_updates`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=105;

--
-- AUTO_INCREMENT de la tabla `usuarios`
--
ALTER TABLE `usuarios`
  MODIFY `id_usuario` int(100) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=28;

--
-- Restricciones para tablas volcadas
--

--
-- Filtros para la tabla `citas`
--
ALTER TABLE `citas`
  ADD CONSTRAINT `fk_citas_usuarios` FOREIGN KEY (`id_usuario`) REFERENCES `usuarios` (`id_usuario`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Filtros para la tabla `contacto`
--
ALTER TABLE `contacto`
  ADD CONSTRAINT `fk_mensaje_usuario` FOREIGN KEY (`id_usuario`) REFERENCES `usuarios` (`id_usuario`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Filtros para la tabla `detalles_pedido`
--
ALTER TABLE `detalles_pedido`
  ADD CONSTRAINT `fk_detalles_pedido` FOREIGN KEY (`id_pedido`) REFERENCES `pedidos` (`id_pedido`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_detalles_producto` FOREIGN KEY (`id_producto`) REFERENCES `productos` (`id_producto`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Filtros para la tabla `pedidos`
--
ALTER TABLE `pedidos`
  ADD CONSTRAINT `fk_pedidos_usuarios` FOREIGN KEY (`id_usuario`) REFERENCES `usuarios` (`id_usuario`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Filtros para la tabla `productos`
--
ALTER TABLE `productos`
  ADD CONSTRAINT `fk_productos_soporte` FOREIGN KEY (`id_soporte`) REFERENCES `soporte` (`id_soporte`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Filtros para la tabla `telegram_action_requests`
--
ALTER TABLE `telegram_action_requests`
  ADD CONSTRAINT `fk_telegram_action_requests_usuario` FOREIGN KEY (`id_usuario`) REFERENCES `usuarios` (`id_usuario`) ON DELETE CASCADE;

--
-- Filtros para la tabla `telegram_link_codes`
--
ALTER TABLE `telegram_link_codes`
  ADD CONSTRAINT `fk_telegram_link_codes_usuario` FOREIGN KEY (`id_usuario`) REFERENCES `usuarios` (`id_usuario`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
