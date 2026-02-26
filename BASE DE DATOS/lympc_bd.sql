-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1
-- Tiempo de generación: 26-02-2026 a las 23:24:49
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
-- Estructura de tabla para la tabla `citas`
--

CREATE TABLE `citas` (
  `id_cita` int(11) NOT NULL,
  `nombre` varchar(255) NOT NULL,
  `apellido` varchar(255) NOT NULL,
  `correo` varchar(255) NOT NULL,
  `fecha` date NOT NULL,
  `telefono` varchar(25) NOT NULL,
  `motivo` varchar(500) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `citas`
--

INSERT INTO `citas` (`id_cita`, `nombre`, `apellido`, `correo`, `fecha`, `telefono`, `motivo`) VALUES
(1, 'Mateo', 'Cisneros', 'mateo.cisneros@gmail.com', '2026-03-05', '09843734645', 'Arreglo'),
(6, 'mateo1', 'Cisneros', 'mateo.cisneros12@gmail.com', '2026-02-09', '0984373472', 'Mi equipo esta mojado'),
(7, 'juan', 'Xavier', 'xavi.beer@gmail.com', '2026-02-20', '0984373472', 'Reparo Tecnico de componentes'),
(8, 'mateo1', 'Cisneros', 'mateo.cisneros12@gmail.com', '2026-02-11', '0984373472', 'arreglo'),
(9, 'Mateo', 'Cisneros', 'mateo.cisneros12@gmail.com', '2026-02-06', '0984373472', 'ff'),
(10, 'mateo1', 'jaramillo', 'mateo.cisneros12@gmail.com', '2026-02-12', '0984373472', 'Arreglo y Mantenimiento Preventivo'),
(11, 'Mateo', 'Montoya', 'mateo.cisneros12@gmail.com', '2026-02-21', '0984373472', 'Cita Tecnica para el equipo'),
(13, 'Mateo3', 'Cisneros', 'xavi2.beer@gmail.com', '2026-02-19', '098437347225', 'Otros');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `contacto`
--

CREATE TABLE `contacto` (
  `id_soporte` int(11) NOT NULL,
  `Nombre` varchar(255) NOT NULL,
  `Correo` varchar(255) NOT NULL,
  `Compania` varchar(255) NOT NULL,
  `Mensaje` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `contacto`
--

INSERT INTO `contacto` (`id_soporte`, `Nombre`, `Correo`, `Compania`, `Mensaje`) VALUES
(1, 'mateo', 'mateo.cisneros@gmail.com', 'Vida Nueva', 'Union y convivencia por los estudiantes y pasantes'),
(2, 'kai ', 'kaisuarez89@gmail.com', 'ServiTECH', 'Convenio institucional'),
(3, 'kai ', 'kaisuarez89@gmail.com', 'ServiTECH', 'convenio'),
(4, 'Mateo', 'mateo.cisneros@istvidanueva.edu.ec', 'Eco-Net', 'Convenio Institucional entre la empresas');

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
  `categoria` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `productos`
--

INSERT INTO `productos` (`id_producto`, `nombre`, `serie`, `fecha`, `unidades`, `precio`, `imagen`, `categoria`) VALUES
(1, 'NOTEBOOK ASUS RP058 CORE 7', 'Serie Ultra Core', '2026-02-19', 0, 1516.00, '../uploads/asus.jpeg', 'ASUS'),
(2, 'PC DELL', 'SERIE 7000', '2026-02-19', 32, 798.50, '../uploads/pcdell.webp', 'PC'),
(3, 'Tablet Ipad', 'Ipad', '2026-02-19', 0, 698.50, '../uploads/tablets.webp', 'Tablets'),
(4, 'Tinta EPSON 1000 ML', 'Tinta', '2026-02-19', 0, 300.00, '../uploads/tinta.webp', 'Tintas'),
(5, 'Unidad duplicadora de disco USB 3.0 Tipo-C Grabadora de CD para computadora portátil Mac Pro MacBook', ' 120 mm/4,72 pulgadas, 80 mm/3,15 pulgadas', '2026-02-19', 8, 40.00, '../uploads/duplicadora.webp', 'DUPLICADORACD'),
(6, 'HP 2009', 'i7 14 900 K', '2026-02-19', 86, 720.50, '../uploads/pchp.jpg', 'PCHP'),
(7, 'LENOVO', 'i7 13 900 F', '2025-02-19', 93, 1420.50, '../uploads/lenovo.jpg', 'lenovo'),
(8, 'MSI GAMER 2024', 'i9 13 900 K', '2026-12-19', 94, 1400.50, '../uploads/msi.jpeg', 'msi'),
(9, 'Omnibook', 'Core i7 13 va Generacion', '2026-11-19', 96, 1200.00, '../uploads/ominibook.jpeg', 'ominibook'),
(10, 'Dell Inspiron 16 5630', 'Ryzen 6000', '2025-11-12', 22, 1100.00, '../uploads/dell.jpeg', 'DELL'),
(11, 'Copystars Dvd Duplicator 24x Cd-dvd-burner 1 A 3 Copiadora S', 'SYS-1-3-ASUS/LG-CST', '2023-12-24', 39, 31.90, '../uploads/duplicadoradvd.webp', 'DUPLICADORADVD'),
(12, 'Blu-ray / CD / DVD\r\nDuplicator', 'B01-SSP16XL', '2025-09-12', 11, 100.00, '../uploads/duplicadorablu.jpg', 'DUPLICADORABLU'),
(13, 'Impresora EPSON L8050 EcoTank Tinta Continua Fotografica PVC', 'EcoTank L8050', '2021-12-12', 93, 300.00, '../uploads/epson.webp', 'EPSON'),
(14, 'Impresora Canon G1110 Sistema Continuo + Tintas Incluidas', 'SKU 31650', '2025-10-08', 6, 300.40, '../uploads/canon.webp', 'CANON'),
(15, '100ml Tinta Dye Para Impresoras Epson La Mejor Calidad', 'EDYE', '2026-03-08', 12, 12.00, '../uploads/tinta.webp', 'TINTA100'),
(16, 'Tinta Dye Premium Para Impresoras Epson Ecotank 1000 ml', 'U0079', '2024-12-24', 11, 734.00, '../uploads/tinta2.webp', 'TINTA1000');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `usuarios`
--

CREATE TABLE `usuarios` (
  `id_usuario` int(100) NOT NULL,
  `usuario` varchar(200) NOT NULL,
  `correo` varchar(255) NOT NULL,
  `contraseña` varchar(150) NOT NULL,
  `rol` varchar(100) NOT NULL,
  `reset_token` varchar(255) DEFAULT NULL,
  `reset_expiration` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `usuarios`
--

INSERT INTO `usuarios` (`id_usuario`, `usuario`, `correo`, `contraseña`, `rol`, `reset_token`, `reset_expiration`) VALUES
(4, 'Admin', 'mateo.montoya@gmail.com', '$2y$10$zSEwmXwUVTvCyfL7jzhruOvVQkLNCKpp4Ah/DRUGp1Av4e.UG4yBa', 'admin', '123', '2026-02-04 15:48:28'),
(5, 'mateo1', 'mateo.cisneros12@gmail.com', '$2y$10$1U/9.fNzisdGC7if/.NUuefRL6FdCfPVBRjHVFNw4d9SRctuzvqRy', 'usuario', NULL, '2026-02-24 15:48:40'),
(6, 'ADMIN', 'mateo.guerrero@gmail.com', '$2y$10$vULksLdsYkSxIEtlKiXPy.z1KJyV.Awm2W162Hn59MiB79961452.', 'admin', '12345', '2026-02-25 15:48:55'),
(7, 'juan', 'mateo.cisneros@istvidanueva.edu.ec', '$2y$10$hIhRCE9/CRABqCPQCCdebeyY0hkMb4NwF6/P9WZU1KE2WkWKrt0VS', 'usuario', '123456', '2026-03-10 15:49:08'),
(8, 'Aron', 'mateo.cisneros12@istvidanueva.edu.ec', '$2y$10$83eyW5/CyglqqVZwM9VnjeQFSCvNA.M.vAmQzGqLBJOR3aejOQtsS', 'usuario', '1234567', '2026-04-09 15:49:19');

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `citas`
--
ALTER TABLE `citas`
  ADD PRIMARY KEY (`id_cita`);

--
-- Indices de la tabla `contacto`
--
ALTER TABLE `contacto`
  ADD PRIMARY KEY (`id_soporte`);

--
-- Indices de la tabla `productos`
--
ALTER TABLE `productos`
  ADD PRIMARY KEY (`id_producto`),
  ADD UNIQUE KEY `UNIQUE` (`serie`);

--
-- Indices de la tabla `usuarios`
--
ALTER TABLE `usuarios`
  ADD PRIMARY KEY (`id_usuario`);

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `citas`
--
ALTER TABLE `citas`
  MODIFY `id_cita` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT de la tabla `contacto`
--
ALTER TABLE `contacto`
  MODIFY `id_soporte` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT de la tabla `productos`
--
ALTER TABLE `productos`
  MODIFY `id_producto` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- AUTO_INCREMENT de la tabla `usuarios`
--
ALTER TABLE `usuarios`
  MODIFY `id_usuario` int(100) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
