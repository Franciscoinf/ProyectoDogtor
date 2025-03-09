-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1
-- Tiempo de generación: 09-03-2025 a las 15:52:10
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
-- Base de datos: `veterinaria`
--

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `citas`
--

CREATE TABLE `citas` (
  `id_cita` int(11) NOT NULL,
  `id_mascota` int(11) DEFAULT NULL,
  `id_cliente` int(11) DEFAULT NULL,
  `servicio` varchar(50) DEFAULT NULL,
  `fecha` datetime DEFAULT NULL,
  `hora` varchar(50) NOT NULL,
  `estado` enum('pendiente','confirmada','cancelada') DEFAULT 'pendiente'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `citas`
--

INSERT INTO `citas` (`id_cita`, `id_mascota`, `id_cliente`, `servicio`, `fecha`, `hora`, `estado`) VALUES
(6, 14, 6, 'Consulta General', '2025-03-20 00:00:00', '10:00', 'pendiente'),
(9, 14, 6, 'Urgencia respiratoria', '2024-03-28 09:15:00', '09:15', 'pendiente');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `clientes`
--

CREATE TABLE `clientes` (
  `id_cliente` int(11) NOT NULL,
  `nombre` varchar(100) NOT NULL,
  `apellidos` varchar(50) DEFAULT NULL,
  `email` varchar(100) NOT NULL,
  `telefono` varchar(15) DEFAULT NULL,
  `direccion` varchar(200) DEFAULT NULL,
  `fecha_registro` datetime DEFAULT current_timestamp(),
  `password` varchar(255) NOT NULL,
  `dni` varchar(9) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `clientes`
--

INSERT INTO `clientes` (`id_cliente`, `nombre`, `apellidos`, `email`, `telefono`, `direccion`, `fecha_registro`, `password`, `dni`) VALUES
(6, 'pata', NULL, 'pata@gmail.com', NULL, NULL, '2025-03-07 13:30:56', '$2y$10$wdA3j2Ia4ZXwmdW9hkEfl.bAwa4OnEnizW/z4Vi19yOADeLqvwo.K', ''),
(10, 'Laura', 'González Pérez', 'laura.gonzalez@example.com', '611223344', 'Calle Alcalá 45, Madrid', '2024-01-15 09:30:00', '', '12345678A'),
(11, 'Pedro', 'Sánchez Ruiz', 'pedro.sanchez@example.com', '655443322', 'Gran Vía 22, Barcelona', '2023-11-20 16:45:00', '', '87654321B'),
(12, 'Ana', 'Martín Blanco', 'ana.martin@example.com', '699887766', 'Plaza España 3, Sevilla', '2024-02-10 11:20:00', '', '11223344C'),
(13, 'Sofía', 'Díaz Castro', 'sofia.diaz@example.com', '633112233', 'Avenida Libertad 78, Valencia', '2024-03-01 08:00:00', '', '55667788D'),
(14, 'Miguel', 'Álvarez Molina', 'miguel.alvarez@example.com', '677665544', 'Calle Sierpes 12, Bilbao', '2023-12-25 19:15:00', '', '99887766E'),
(15, 'Nuevo Dueño', NULL, 'nuevo@example.com', NULL, NULL, '2025-03-09 13:39:14', 'hash_seguro', '12345678Z');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `guarderia`
--

CREATE TABLE `guarderia` (
  `id` int(11) NOT NULL,
  `id_mascota` int(11) NOT NULL,
  `fecha_entrada` date NOT NULL,
  `hora_entrada` time NOT NULL,
  `fecha_salida` date NOT NULL,
  `hora_salida` time NOT NULL,
  `precio` decimal(10,2) NOT NULL,
  `fecha_registro` timestamp NOT NULL DEFAULT current_timestamp(),
  `id_cliente` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `guarderia`
--

INSERT INTO `guarderia` (`id`, `id_mascota`, `fecha_entrada`, `hora_entrada`, `fecha_salida`, `hora_salida`, `precio`, `fecha_registro`, `id_cliente`) VALUES
(1, 14, '2025-03-13', '00:00:00', '2025-03-21', '00:00:00', 192.00, '2025-03-08 18:02:17', 0),
(2, 19, '2025-03-20', '08:00:00', '2025-03-22', '19:00:00', 59.00, '2025-03-08 20:53:15', 6),
(3, 14, '2025-03-13', '08:00:00', '2025-03-15', '09:00:00', 49.00, '2025-03-08 21:06:34', 6),
(4, 14, '2025-03-12', '08:00:00', '2025-03-22', '09:00:00', 241.00, '2025-03-08 21:06:51', 6);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `mascotas`
--

CREATE TABLE `mascotas` (
  `id_mascota` int(11) NOT NULL,
  `id_cliente` int(11) DEFAULT NULL,
  `nombre` varchar(100) NOT NULL,
  `especie` varchar(50) DEFAULT NULL,
  `raza` varchar(100) DEFAULT NULL,
  `fecha_nacimiento` date DEFAULT NULL,
  `fecha_seguro` date DEFAULT NULL,
  `fecha_expiracion` date DEFAULT NULL,
  `id_seguro` int(11) DEFAULT NULL,
  `edad` varchar(50) GENERATED ALWAYS AS (concat(timestampdiff(YEAR,`fecha_nacimiento`,curdate()),' años, ',timestampdiff(MONTH,`fecha_nacimiento`,curdate()) MOD 12,' meses')) VIRTUAL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `mascotas`
--

INSERT INTO `mascotas` (`id_mascota`, `id_cliente`, `nombre`, `especie`, `raza`, `fecha_nacimiento`, `fecha_seguro`, `fecha_expiracion`, `id_seguro`) VALUES
(14, 6, 'Hammy', 'Hámster', 'Ruso', '2024-02-10', '2025-03-06', '2026-03-06', 3),
(19, 6, 'Jorger', 'Gato', 'Persa', '2025-03-13', NULL, NULL, 2),
(24, 15, 'Nombre Mascota', 'Perro', NULL, NULL, NULL, NULL, NULL),
(25, 6, 'awa', 'Hurón', '', '2024-11-13', NULL, NULL, 1);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `seguros`
--

CREATE TABLE `seguros` (
  `id_seguro` int(11) NOT NULL,
  `tipo_seguro` varchar(100) NOT NULL,
  `precio` decimal(10,2) NOT NULL,
  `descripcion` text DEFAULT NULL,
  `duracion` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `seguros`
--

INSERT INTO `seguros` (`id_seguro`, `tipo_seguro`, `precio`, `descripcion`, `duracion`) VALUES
(1, 'Básico', 50.00, 'Seguro básico para mascotas pequeñas, médicas y accidentes cubiertos.', 365),
(2, 'Medio', 100.00, 'Seguro medio cubre más opciones como consultas más frecuentes y urgencias.', 365),
(3, 'Deluxe', 150.00, 'Seguro completo con cobertura de consultas regulares, urgencias y más beneficios.', 365);

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `citas`
--
ALTER TABLE `citas`
  ADD PRIMARY KEY (`id_cita`),
  ADD KEY `id_mascota` (`id_mascota`),
  ADD KEY `id_cliente` (`id_cliente`);

--
-- Indices de la tabla `clientes`
--
ALTER TABLE `clientes`
  ADD PRIMARY KEY (`id_cliente`),
  ADD UNIQUE KEY `email` (`email`),
  ADD UNIQUE KEY `email_2` (`email`);

--
-- Indices de la tabla `guarderia`
--
ALTER TABLE `guarderia`
  ADD PRIMARY KEY (`id`),
  ADD KEY `mascota_id` (`id_mascota`),
  ADD KEY `idx_fechas` (`fecha_entrada`,`fecha_salida`);

--
-- Indices de la tabla `mascotas`
--
ALTER TABLE `mascotas`
  ADD PRIMARY KEY (`id_mascota`),
  ADD KEY `id_cliente` (`id_cliente`),
  ADD KEY `id_seguro` (`id_seguro`);

--
-- Indices de la tabla `seguros`
--
ALTER TABLE `seguros`
  ADD PRIMARY KEY (`id_seguro`);

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `citas`
--
ALTER TABLE `citas`
  MODIFY `id_cita` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT de la tabla `clientes`
--
ALTER TABLE `clientes`
  MODIFY `id_cliente` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT de la tabla `guarderia`
--
ALTER TABLE `guarderia`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT de la tabla `mascotas`
--
ALTER TABLE `mascotas`
  MODIFY `id_mascota` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=26;

--
-- AUTO_INCREMENT de la tabla `seguros`
--
ALTER TABLE `seguros`
  MODIFY `id_seguro` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- Restricciones para tablas volcadas
--

--
-- Filtros para la tabla `citas`
--
ALTER TABLE `citas`
  ADD CONSTRAINT `citas_ibfk_1` FOREIGN KEY (`id_mascota`) REFERENCES `mascotas` (`id_mascota`) ON DELETE CASCADE,
  ADD CONSTRAINT `citas_ibfk_2` FOREIGN KEY (`id_cliente`) REFERENCES `clientes` (`id_cliente`) ON DELETE CASCADE;

--
-- Filtros para la tabla `guarderia`
--
ALTER TABLE `guarderia`
  ADD CONSTRAINT `guarderia_ibfk_1` FOREIGN KEY (`id_mascota`) REFERENCES `mascotas` (`id_mascota`);

--
-- Filtros para la tabla `mascotas`
--
ALTER TABLE `mascotas`
  ADD CONSTRAINT `mascotas_ibfk_1` FOREIGN KEY (`id_cliente`) REFERENCES `clientes` (`id_cliente`) ON DELETE CASCADE,
  ADD CONSTRAINT `mascotas_ibfk_2` FOREIGN KEY (`id_seguro`) REFERENCES `seguros` (`id_seguro`),
  ADD CONSTRAINT `mascotas_ibfk_seguro` FOREIGN KEY (`id_seguro`) REFERENCES `seguros` (`id_seguro`) ON DELETE SET NULL;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
