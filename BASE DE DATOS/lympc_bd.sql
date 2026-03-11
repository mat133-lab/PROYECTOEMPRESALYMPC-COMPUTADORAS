-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1
-- Tiempo de generación: 11-03-2026 a las 03:06:33
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
-- Estructura de tabla para la tabla `categorias`
--

CREATE TABLE `categorias` (
  `id_categoria` int(11) NOT NULL,
  `nombre_categoria` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

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
  `fecha` date NOT NULL,
  `telefono` varchar(25) NOT NULL,
  `motivo` varchar(500) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `citas`
--

INSERT INTO `citas` (`id_cita`, `id_usuario`, `nombre`, `apellido`, `correo`, `fecha`, `telefono`, `motivo`) VALUES
(1, NULL, 'Mateo', 'Cisneros', 'mateo.cisneros@gmail.com', '2026-03-05', '09843734645', 'Arreglo'),
(6, NULL, 'mateo1', 'Cisneros', 'mateo.cisneros12@gmail.com', '2026-02-09', '0984373472', 'Mi equipo esta mojado'),
(7, NULL, 'juan', 'Xavier', 'xavi.beer@gmail.com', '2026-02-20', '0984373472', 'Reparo Tecnico de componentes'),
(8, NULL, 'mateo1', 'Cisneros', 'mateo.cisneros12@gmail.com', '2026-02-11', '0984373472', 'arreglo'),
(9, NULL, 'Mateo', 'Cisneros', 'mateo.cisneros12@gmail.com', '2026-02-06', '0984373472', 'ff'),
(10, NULL, 'mateo1', 'jaramillo', 'mateo.cisneros12@gmail.com', '2026-02-12', '0984373472', 'Arreglo y Mantenimiento Preventivo'),
(11, NULL, 'Mateo', 'Montoya', 'mateo.cisneros12@gmail.com', '2026-02-21', '0984373472', 'Cita Tecnica para el equipo'),
(13, NULL, 'Mateo3', 'Cisneros', 'xavi2.beer@gmail.com', '2026-02-19', '098437347225', 'Otros');

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
-- Estructura de tabla para la tabla `detalles_pedido`
--

CREATE TABLE `detalles_pedido` (
  `id_detalle` int(11) NOT NULL,
  `id_pedido` int(11) NOT NULL,
  `id_producto` int(11) NOT NULL,
  `cantidad` int(11) NOT NULL,
  `precio_unitario` decimal(10,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `detalles_pedido`
--

INSERT INTO `detalles_pedido` (`id_detalle`, `id_pedido`, `id_producto`, `cantidad`, `precio_unitario`) VALUES
(1, 1, 6, 2, 720.50),
(2, 1, 8, 2, 1400.50),
(3, 2, 16, 1, 734.00),
(4, 3, 2, 7, 798.50),
(5, 4, 5, 1, 40.00);

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

--
-- Volcado de datos para la tabla `pedidos`
--

INSERT INTO `pedidos` (`id_pedido`, `id_usuario`, `fecha_pedido`, `total`) VALUES
(1, 5, '2026-03-10 17:51:20', 4242.00),
(2, 5, '2026-03-10 20:47:13', 734.00),
(3, 5, '2026-03-10 21:02:49', 5589.50),
(4, 5, '2026-03-10 21:03:13', 40.00);

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
  `id_categoria` int(11) DEFAULT NULL,
  `id_soporte` int(11) DEFAULT NULL,
  `categoria` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `productos`
--

INSERT INTO `productos` (`id_producto`, `nombre`, `serie`, `fecha`, `unidades`, `precio`, `imagen`, `id_categoria`, `id_soporte`, `categoria`) VALUES
(1, 'NOTEBOOK ASUS RP058 CORE 7', 'Serie Ultra Core', '2026-02-19', 0, 1516.00, '../uploads/asus.jpeg', NULL, NULL, 'ASUS'),
(2, 'PC DELL', 'SERIE 7000', '2026-02-19', 18, 798.50, '../uploads/pcdell.webp', NULL, NULL, 'PC'),
(3, 'Tablet Ipad', 'Ipad', '2026-02-19', 0, 698.50, '../uploads/tablets.webp', NULL, NULL, 'Tablets'),
(4, 'Tinta EPSON 1000 ML', 'Tinta', '2026-02-19', 0, 300.00, '../uploads/tinta.webp', NULL, NULL, 'Tintas'),
(5, 'Unidad duplicadora de disco USB 3.0 Tipo-C Grabadora de CD para computadora portátil Mac Pro MacBook', ' 120 mm/4,72 pulgadas, 80 mm/3,15 pulgadas', '2026-02-19', 6, 40.00, '../uploads/duplicadora.webp', NULL, NULL, 'DUPLICADORACD'),
(6, 'HP 2009', 'i7 14 900 K', '2026-02-19', 82, 720.50, '../uploads/pchp.jpg', NULL, NULL, 'PCHP'),
(7, 'LENOVO', 'i7 13 900 F', '2025-02-19', 93, 1420.50, '../uploads/lenovo.jpg', NULL, NULL, 'lenovo'),
(8, 'MSI GAMER 2024', 'i9 13 900 K', '2026-12-19', 90, 1400.50, '../uploads/msi.jpeg', NULL, NULL, 'msi'),
(9, 'Omnibook', 'Core i7 13 va Generacion', '2026-11-19', 96, 1200.00, '../uploads/ominibook.jpeg', NULL, NULL, 'ominibook'),
(10, 'Dell Inspiron 16 5630', 'Ryzen 6000', '2025-11-12', 22, 1100.00, '../uploads/dell.jpeg', NULL, NULL, 'DELL'),
(11, 'Copystars Dvd Duplicator 24x Cd-dvd-burner 1 A 3 Copiadora S', 'SYS-1-3-ASUS/LG-CST', '2023-12-24', 39, 31.90, '../uploads/duplicadoradvd.webp', NULL, NULL, 'DUPLICADORADVD'),
(12, 'Blu-ray / CD / DVD\r\nDuplicator', 'B01-SSP16XL', '2025-09-12', 11, 100.00, '../uploads/duplicadorablu.jpg', NULL, NULL, 'DUPLICADORABLU'),
(13, 'Impresora EPSON L8050 EcoTank Tinta Continua Fotografica PVC', 'EcoTank L8050', '2021-12-12', 93, 300.00, '../uploads/epson.webp', NULL, NULL, 'EPSON'),
(14, 'Impresora Canon G1110 Sistema Continuo + Tintas Incluidas', 'SKU 31650', '2025-10-08', 6, 300.40, '../uploads/canon.webp', NULL, NULL, 'CANON'),
(15, '100ml Tinta Dye Para Impresoras Epson La Mejor Calidad', 'EDYE', '2026-03-08', 12, 12.00, '../uploads/tinta.webp', NULL, NULL, 'TINTA100'),
(16, 'Tinta Dye Premium Para Impresoras Epson Ecotank 1000 ml', 'U0079', '2024-12-24', 9, 734.00, '../uploads/tinta2.webp', NULL, NULL, 'TINTA1000');

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
(8, 'Aron', 'mateo.cisneros12@istvidanueva.edu.ec', '$2y$10$83eyW5/CyglqqVZwM9VnjeQFSCvNA.M.vAmQzGqLBJOR3aejOQtsS', 'usuario', '1234567', '2026-04-09 15:49:19'),
(10, 'ADMIN', 'mateo.cisneros13@gmail.com', '$2y$10$XdwmPhfCyZDfh36vKY2bAeFZWlT2uxDa2JxGlGNoW.ifgv11LOmde', 'admin', NULL, NULL);

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `categorias`
--
ALTER TABLE `categorias`
  ADD PRIMARY KEY (`id_categoria`);

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
  ADD PRIMARY KEY (`id_soporte`);

--
-- Indices de la tabla `detalles_pedido`
--
ALTER TABLE `detalles_pedido`
  ADD PRIMARY KEY (`id_detalle`),
  ADD KEY `fk_detalles_pedido` (`id_pedido`),
  ADD KEY `fk_detalles_producto` (`id_producto`);

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
  ADD KEY `fk_productos_categorias` (`id_categoria`),
  ADD KEY `fk_productos_soporte` (`id_soporte`);

--
-- Indices de la tabla `usuarios`
--
ALTER TABLE `usuarios`
  ADD PRIMARY KEY (`id_usuario`);

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `categorias`
--
ALTER TABLE `categorias`
  MODIFY `id_categoria` int(11) NOT NULL AUTO_INCREMENT;

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
-- AUTO_INCREMENT de la tabla `detalles_pedido`
--
ALTER TABLE `detalles_pedido`
  MODIFY `id_detalle` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT de la tabla `pedidos`
--
ALTER TABLE `pedidos`
  MODIFY `id_pedido` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT de la tabla `productos`
--
ALTER TABLE `productos`
  MODIFY `id_producto` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- AUTO_INCREMENT de la tabla `usuarios`
--
ALTER TABLE `usuarios`
  MODIFY `id_usuario` int(100) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- Restricciones para tablas volcadas
--

--
-- Filtros para la tabla `citas`
--
ALTER TABLE `citas`
  ADD CONSTRAINT `fk_citas_usuarios` FOREIGN KEY (`id_usuario`) REFERENCES `usuarios` (`id_usuario`) ON DELETE CASCADE ON UPDATE CASCADE;

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
  ADD CONSTRAINT `fk_productos_categorias` FOREIGN KEY (`id_categoria`) REFERENCES `categorias` (`id_categoria`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_productos_soporte` FOREIGN KEY (`id_soporte`) REFERENCES `contacto` (`id_soporte`) ON DELETE SET NULL ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
