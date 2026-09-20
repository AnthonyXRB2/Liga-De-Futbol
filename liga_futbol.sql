-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1
-- Tiempo de generación: 18-09-2026 a las 03:52:40
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
-- Base de datos: `liga_futbol`
--

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `clubes`
--

CREATE TABLE `clubes` (
  `id` int(11) NOT NULL,
  `nombre` varchar(100) DEFAULT NULL,
  `ciudad` varchar(100) DEFAULT NULL,
  `activo` tinyint(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `clubes`
--

INSERT INTO `clubes` (`id`, `nombre`, `ciudad`, `activo`) VALUES
(3, 'Nacional', 'Rivera', 1),
(4, 'Peñarol', 'Montevideo', 1),
(5, 'Lavalleja', 'Rivera', 1);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `comunicados`
--

CREATE TABLE `comunicados` (
  `id` int(11) NOT NULL,
  `titulo` varchar(100) DEFAULT NULL,
  `mensaje` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `comunicados`
--

INSERT INTO `comunicados` (`id`, `titulo`, `mensaje`) VALUES
(1, 'Partido El Lunes', 'A las 7PM en la casa del franco'),
(2, 'dfaf', 'asdawd');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `documentos`
--

CREATE TABLE `documentos` (
  `id` int(11) NOT NULL,
  `jugador_id` int(11) NOT NULL,
  `ficha_medica` varchar(255) DEFAULT NULL,
  `fecha_subida_ficha` datetime DEFAULT NULL,
  `carnet_salud` varchar(255) DEFAULT NULL,
  `vencimiento_carnet` date DEFAULT NULL,
  `fecha_subida_carnet` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `documentos`
--

INSERT INTO `documentos` (`id`, `jugador_id`, `ficha_medica`, `fecha_subida_ficha`, `carnet_salud`, `vencimiento_carnet`, `fecha_subida_carnet`) VALUES
(1, 1, 'uploads/Captura de pantalla 2026-06-12 131338.png', '2026-06-17 00:51:22', 'uploads/kajksda.png', '2029-07-21', '2026-06-17 00:51:22'),
(2, 8, 'uploads/2025_12_01_ice-and-fire-boy-23686333.png', '2026-07-13 01:07:38', 'uploads/Captura de pantalla 2026-06-12 131338.png', '2027-07-25', '2026-07-13 01:07:38'),
(3, 10, 'uploads/Captura de pantalla 2026-06-14 155230.png', '2026-07-13 01:09:46', 'uploads/Captura de pantalla 2026-06-12 131600.png', '2027-02-02', '2026-07-13 01:09:46'),
(4, 13, 'uploads/2025_12_01_ice-and-fire-boy-23686333.png', '2026-07-13 13:16:37', 'uploads/hhjfg.png', '2027-04-02', '2026-07-13 13:16:37'),
(5, 13, 'uploads/2025_12_01_ice-and-fire-boy-23686333.png', '2026-07-13 13:16:50', 'uploads/hhjfg.png', '2027-04-02', '2026-07-13 13:16:50'),
(6, 13, 'uploads/WhatsApp Image 2026-06-09 at 8.44.50 PM.jpeg', '2026-07-13 13:17:40', 'uploads/photo-1508402476522-c77c2fa4479d.avif', '2027-04-02', '2026-07-13 13:17:40'),
(7, 13, 'uploads/Captura de pantalla 2026-06-12 131600.png', '2026-07-13 13:18:03', 'uploads/skin_ea8330b85fb8a7f4f3f862e07348cbc8e8b222fe04d1e46544bb7be0b4a2fb59.png', '2027-04-02', '2026-07-13 13:18:03'),
(8, 13, 'uploads/Captura de pantalla 2026-06-12 131600.png', '2026-07-13 13:18:42', 'uploads/skin_ea8330b85fb8a7f4f3f862e07348cbc8e8b222fe04d1e46544bb7be0b4a2fb59.png', '2027-04-02', '2026-07-13 13:18:42'),
(9, 13, 'uploads/Captura de pantalla 2026-06-12 131600.png', '2026-07-13 13:18:46', 'uploads/skin_ea8330b85fb8a7f4f3f862e07348cbc8e8b222fe04d1e46544bb7be0b4a2fb59.png', '2027-04-02', '2026-07-13 13:18:46'),
(10, 13, 'uploads/Captura de pantalla 2026-06-12 131600.png', '2026-07-13 13:18:50', 'uploads/skin_ea8330b85fb8a7f4f3f862e07348cbc8e8b222fe04d1e46544bb7be0b4a2fb59.png', '2027-04-02', '2026-07-13 13:18:50'),
(11, 13, 'uploads/Captura de pantalla 2026-06-12 131600.png', '2026-07-13 13:19:05', 'uploads/skin_ea8330b85fb8a7f4f3f862e07348cbc8e8b222fe04d1e46544bb7be0b4a2fb59.png', '2027-08-02', '2026-07-13 13:19:05'),
(12, 13, 'uploads/Captura de pantalla 2026-06-12 131600.png', '2026-07-13 13:19:22', 'uploads/skin_ea8330b85fb8a7f4f3f862e07348cbc8e8b222fe04d1e46544bb7be0b4a2fb59.png', '2027-08-02', '2026-07-13 13:19:22'),
(13, 13, 'uploads/Captura de pantalla 2026-06-12 131600.png', '2026-07-13 13:19:41', 'uploads/skin_ea8330b85fb8a7f4f3f862e07348cbc8e8b222fe04d1e46544bb7be0b4a2fb59.png', '2027-08-02', '2026-07-13 13:19:41'),
(14, 13, 'uploads/dsaaaaaw.png', '2026-07-13 13:21:13', 'uploads/eaaaa.png', '2027-08-02', '2026-07-13 13:21:13'),
(15, 13, 'uploads/dsaaaaaw.png', '2026-07-13 13:21:46', 'uploads/eaaaa.png', '2027-08-02', '2026-07-13 13:21:46');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `jugadores`
--

CREATE TABLE `jugadores` (
  `id` int(11) NOT NULL,
  `nombre` varchar(100) DEFAULT NULL,
  `edad` int(11) DEFAULT NULL,
  `posicion` varchar(50) DEFAULT NULL,
  `club_id` int(11) DEFAULT NULL,
  `ci` varchar(20) DEFAULT NULL,
  `categoria` varchar(50) DEFAULT NULL,
  `altura` float NOT NULL,
  `masa` float NOT NULL,
  `fuerza_peso` float NOT NULL,
  `velocidad` float NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `jugadores`
--

INSERT INTO `jugadores` (`id`, `nombre`, `edad`, `posicion`, `club_id`, `ci`, `categoria`, `altura`, `masa`, `fuerza_peso`, `velocidad`) VALUES
(1, 'Luis do Santos', 12, 'Defensa', 3, '54743642', '2014', 0, 0, 0, 0),
(2, 'Carlos Mendez', 13, 'Delantero', 3, '54743537', '2013', 0, 0, 0, 0),
(3, 'Matias Lopez', 13, 'Defensa', 5, '54743542', '2013', 0, 0, 0, 0),
(4, 'Samuel Oliveira', 13, 'Defensa', 4, '54743542', '2013', 0, 0, 0, 0),
(5, 'Thiago Da Silva', 12, 'Defensa', 3, '54743537', '2014', 0, 0, 0, 0),
(6, 'Nahuel Diaz', 12, 'Arquero', 4, '54743537', '2014', 0, 0, 0, 0),
(8, 'Juan Pérez', 13, 'Arquero', 3, '58154575', '2013', 0, 0, 0, 0),
(9, 'Juan Lopez', 12, 'Arquero', 4, '58525455', '2014', 0, 0, 0, 0),
(10, 'Carlos Oliveira', 13, 'Arquero', 4, '51254596', '2013', 0, 0, 0, 0),
(13, 'Leonard Sanchez', 12, 'Delantero', 3, '53575957', '2014', 0, 0, 0, 0),
(14, 'Antonio Perez', 14, 'Delantero', 3, '57635960', '2012', 1.65, 60.5, 592.9, 0),
(15, 'Jonathan Alvez', 14, 'Delantero', 4, '57637960', '2012', 1.68, 61, 597.8, 0),
(16, 'Juan Ramos', 13, 'Arquero', 3, '57535960', '2013', 1.61, 56, 548.8, 7),
(17, 'Felipe Xavier', 14, 'Defensa', 4, '57635970', '2012', 1.62, 59, 578.2, 6);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `partidos`
--

CREATE TABLE `partidos` (
  `id` int(11) NOT NULL,
  `local_id` int(11) DEFAULT NULL,
  `visitante_id` int(11) DEFAULT NULL,
  `fecha` date DEFAULT NULL,
  `goles_local` int(11) DEFAULT NULL,
  `goles_visitante` int(11) DEFAULT NULL,
  `estado` varchar(30) DEFAULT 'Programado',
  `motivo_suspension` varchar(255) DEFAULT NULL,
  `categoria` varchar(50) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `partidos`
--

INSERT INTO `partidos` (`id`, `local_id`, `visitante_id`, `fecha`, `goles_local`, `goles_visitante`, `estado`, `motivo_suspension`, `categoria`) VALUES
(1, 3, 4, '2026-06-18', 2, 3, 'Programado', NULL, NULL),
(2, 3, 4, '2026-06-19', 5, 3, 'Programado', NULL, NULL),
(3, 3, 4, '2026-06-18', 3, 1, 'Jugado', '', NULL);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `tarjetas`
--

CREATE TABLE `tarjetas` (
  `id` int(11) NOT NULL,
  `jugador_id` int(11) NOT NULL,
  `partido_id` int(11) NOT NULL,
  `tipo` varchar(20) NOT NULL,
  `motivo` varchar(255) DEFAULT NULL,
  `fecha_registro` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `tarjetas`
--

INSERT INTO `tarjetas` (`id`, `jugador_id`, `partido_id`, `tipo`, `motivo`, `fecha_registro`) VALUES
(1, 1, 1, 'Amarilla', '', '2026-06-16 20:26:37'),
(2, 1, 1, 'Roja', 'porque si', '2026-06-16 20:37:47');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `usuarios`
--

CREATE TABLE `usuarios` (
  `id` int(11) NOT NULL,
  `usuario` varchar(50) NOT NULL,
  `password` varchar(100) NOT NULL,
  `rol` varchar(20) NOT NULL,
  `club_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `usuarios`
--

INSERT INTO `usuarios` (`id`, `usuario`, `password`, `rol`, `club_id`) VALUES
(1, 'Administrador', 'admin@54743537', 'admin', NULL),
(5, 'Peñarol', '1234', 'club', 4);

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `clubes`
--
ALTER TABLE `clubes`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `comunicados`
--
ALTER TABLE `comunicados`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `documentos`
--
ALTER TABLE `documentos`
  ADD PRIMARY KEY (`id`),
  ADD KEY `jugador_id` (`jugador_id`);

--
-- Indices de la tabla `jugadores`
--
ALTER TABLE `jugadores`
  ADD PRIMARY KEY (`id`),
  ADD KEY `club_id` (`club_id`);

--
-- Indices de la tabla `partidos`
--
ALTER TABLE `partidos`
  ADD PRIMARY KEY (`id`),
  ADD KEY `local_id` (`local_id`),
  ADD KEY `visitante_id` (`visitante_id`);

--
-- Indices de la tabla `tarjetas`
--
ALTER TABLE `tarjetas`
  ADD PRIMARY KEY (`id`),
  ADD KEY `jugador_id` (`jugador_id`),
  ADD KEY `partido_id` (`partido_id`);

--
-- Indices de la tabla `usuarios`
--
ALTER TABLE `usuarios`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `clubes`
--
ALTER TABLE `clubes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT de la tabla `comunicados`
--
ALTER TABLE `comunicados`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT de la tabla `documentos`
--
ALTER TABLE `documentos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT de la tabla `jugadores`
--
ALTER TABLE `jugadores`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=18;

--
-- AUTO_INCREMENT de la tabla `partidos`
--
ALTER TABLE `partidos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT de la tabla `tarjetas`
--
ALTER TABLE `tarjetas`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT de la tabla `usuarios`
--
ALTER TABLE `usuarios`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- Restricciones para tablas volcadas
--

--
-- Filtros para la tabla `documentos`
--
ALTER TABLE `documentos`
  ADD CONSTRAINT `documentos_ibfk_1` FOREIGN KEY (`jugador_id`) REFERENCES `jugadores` (`id`);

--
-- Filtros para la tabla `jugadores`
--
ALTER TABLE `jugadores`
  ADD CONSTRAINT `jugadores_ibfk_1` FOREIGN KEY (`club_id`) REFERENCES `clubes` (`id`);

--
-- Filtros para la tabla `partidos`
--
ALTER TABLE `partidos`
  ADD CONSTRAINT `partidos_ibfk_1` FOREIGN KEY (`local_id`) REFERENCES `clubes` (`id`),
  ADD CONSTRAINT `partidos_ibfk_2` FOREIGN KEY (`visitante_id`) REFERENCES `clubes` (`id`);

--
-- Filtros para la tabla `tarjetas`
--
ALTER TABLE `tarjetas`
  ADD CONSTRAINT `tarjetas_ibfk_1` FOREIGN KEY (`jugador_id`) REFERENCES `jugadores` (`id`),
  ADD CONSTRAINT `tarjetas_ibfk_2` FOREIGN KEY (`partido_id`) REFERENCES `partidos` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
