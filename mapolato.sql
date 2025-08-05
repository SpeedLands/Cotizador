-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1
-- Tiempo de generación: 01-08-2025 a las 01:09:23
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
-- Base de datos: `mapolato`
--

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `cotizaciones`
--

CREATE TABLE `cotizaciones` (
  `id` int(11) NOT NULL,
  `nombre_completo` varchar(255) NOT NULL,
  `whatsapp` varchar(20) DEFAULT NULL,
  `tipo_evento` varchar(100) DEFAULT NULL,
  `nombre_empresa` varchar(255) DEFAULT NULL,
  `direccion_evento` text DEFAULT NULL,
  `fecha_evento` date DEFAULT NULL,
  `hora_evento` time DEFAULT NULL,
  `horario_consumo` varchar(100) DEFAULT NULL,
  `cantidad_invitados` int(11) DEFAULT 0,
  `servicios_otros` text DEFAULT NULL,
  `mesa_mantel` varchar(50) DEFAULT NULL,
  `mesa_mantel_otro` varchar(255) DEFAULT NULL,
  `personal_servicio` varchar(50) DEFAULT NULL,
  `acceso_enchufe` varchar(50) DEFAULT NULL,
  `dificultad_montaje` varchar(50) DEFAULT NULL,
  `tipo_consumidores` varchar(100) DEFAULT NULL,
  `restricciones` text DEFAULT NULL,
  `requisitos_adicionales` text DEFAULT NULL,
  `presupuesto` varchar(100) DEFAULT NULL,
  `como_supiste` varchar(100) DEFAULT NULL,
  `como_supiste_otro` varchar(255) DEFAULT NULL,
  `total_base` decimal(10,2) DEFAULT 0.00,
  `costo_adicional_ia` decimal(10,2) DEFAULT 0.00,
  `justificacion_ia` text DEFAULT NULL,
  `total_estimado` decimal(10,2) DEFAULT 0.00,
  `status` varchar(50) DEFAULT 'Pendiente',
  `fecha_creacion` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `cotizaciones`
--

INSERT INTO `cotizaciones` (`id`, `nombre_completo`, `whatsapp`, `tipo_evento`, `nombre_empresa`, `direccion_evento`, `fecha_evento`, `hora_evento`, `horario_consumo`, `cantidad_invitados`, `servicios_otros`, `mesa_mantel`, `mesa_mantel_otro`, `personal_servicio`, `acceso_enchufe`, `dificultad_montaje`, `tipo_consumidores`, `restricciones`, `requisitos_adicionales`, `presupuesto`, `como_supiste`, `como_supiste_otro`, `total_base`, `costo_adicional_ia`, `justificacion_ia`, `total_estimado`, `status`, `fecha_creacion`) VALUES
(1, 'Juan de Dios Perez Lopez', '123456778', 'Social', '', 'mi casa', '2025-07-16', '19:51:00', '8:00PM', 10, '', 'Si', '', 'No', 'Si', 'distacia a  caminar', 'Mixto', 'nueces', 'montaje silencioso', '$ 5000 - $ 7000', 'Recomendacion', '', 30.00, 650.00, 'Por incremento de tiempo y esfuerzo en transporte de equipo a distancia y montaje silencioso que requiere mayor precaución.', 680.00, 'Pendiente', '2025-07-17 00:52:41');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `cotizacion_servicios`
--

CREATE TABLE `cotizacion_servicios` (
  `id` int(11) NOT NULL,
  `cotizacion_id` int(11) NOT NULL,
  `servicio_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `cotizacion_servicios`
--

INSERT INTO `cotizacion_servicios` (`id`, `cotizacion_id`, `servicio_id`) VALUES
(4, 1, 2),
(5, 1, 7),
(6, 1, 9);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `notifications`
--

CREATE TABLE `notifications` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `user_id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `body` text NOT NULL,
  `is_read` tinyint(1) NOT NULL DEFAULT 0,
  `action_url` varchar(255) DEFAULT NULL,
  `notification_type` varchar(50) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `servicios`
--

CREATE TABLE `servicios` (
  `id` int(11) NOT NULL,
  `nombre` varchar(255) NOT NULL,
  `descripcion` text DEFAULT NULL,
  `precio_base` decimal(10,2) NOT NULL,
  `tipo_cobro` varchar(50) DEFAULT 'evento',
  `min_personas` int(11) DEFAULT 1,
  `imagen_url` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `servicios`
--

INSERT INTO `servicios` (`id`, `nombre`, `descripcion`, `precio_base`, `tipo_cobro`, `min_personas`, `imagen_url`) VALUES
(1, 'Consumo de Chafers / Baños Maria', NULL, 100.00, 'fijo', 1, NULL),
(2, 'Consumo de cazuelas', NULL, 10.00, 'fijo', 1, NULL),
(3, 'Mesa de Bocadillos / Canapes', NULL, 10.00, 'por_persona', 30, NULL),
(4, 'Mesa de Postres', NULL, 10.00, 'por_persona', 30, NULL),
(5, 'Mesa de Snacks (papitas y dulces)', NULL, 10.00, 'por_persona', 30, NULL),
(6, 'Barra de ensaladas', NULL, 10.00, 'por_persona', 30, NULL),
(7, 'Estación de Cafe', NULL, 10.00, 'fijo', 1, NULL),
(8, 'Lunch Box', NULL, 10.00, 'por_persona', 1, NULL),
(9, 'Tabla de Charcutería', NULL, 10.00, 'fijo', 10, NULL),
(10, 'Mesa de Charcutería', NULL, 10.00, 'por_persona', 50, NULL),
(11, 'Charcutería individual', NULL, 10.00, 'por_persona', 15, NULL),
(12, 'Bebida en dispensador (Aguas de Sabor)', NULL, 10.00, 'por_litro', 1, NULL);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `user_fcm_tokens`
--

CREATE TABLE `user_fcm_tokens` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `user_id` int(11) NOT NULL,
  `token` text NOT NULL,
  `device_name` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `usuarios`
--

CREATE TABLE `usuarios` (
  `id` int(11) NOT NULL,
  `nombre_usuario` varchar(100) NOT NULL,
  `email` varchar(255) NOT NULL,
  `password_hash` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `usuarios`
--

INSERT INTO `usuarios` (`id`, `nombre_usuario`, `email`, `password_hash`) VALUES
(1, 'admin', 'admin@mapolato.com', '$2y$10$MR5ByC.0Xa2Sj.ZEV9eLYOebe91I/Q.2DRRc/4sNtH74Ewidy.qXW');

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `cotizaciones`
--
ALTER TABLE `cotizaciones`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_status` (`status`),
  ADD KEY `idx_fecha_evento` (`fecha_evento`);

--
-- Indices de la tabla `cotizacion_servicios`
--
ALTER TABLE `cotizacion_servicios`
  ADD PRIMARY KEY (`id`),
  ADD KEY `cotizacion_id` (`cotizacion_id`),
  ADD KEY `servicio_id` (`servicio_id`);

--
-- Indices de la tabla `notifications`
--
ALTER TABLE `notifications`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_user_id` (`user_id`);

--
-- Indices de la tabla `servicios`
--
ALTER TABLE `servicios`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `user_fcm_tokens`
--
ALTER TABLE `user_fcm_tokens`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uk_token` (`token`(255)),
  ADD KEY `fk_user_fcm_tokens_user_id` (`user_id`);

--
-- Indices de la tabla `usuarios`
--
ALTER TABLE `usuarios`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `nombre_usuario` (`nombre_usuario`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `cotizaciones`
--
ALTER TABLE `cotizaciones`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT de la tabla `cotizacion_servicios`
--
ALTER TABLE `cotizacion_servicios`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT de la tabla `notifications`
--
ALTER TABLE `notifications`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `servicios`
--
ALTER TABLE `servicios`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT de la tabla `user_fcm_tokens`
--
ALTER TABLE `user_fcm_tokens`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `usuarios`
--
ALTER TABLE `usuarios`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- Restricciones para tablas volcadas
--

--
-- Filtros para la tabla `notifications`
--
ALTER TABLE `notifications`
  ADD CONSTRAINT `fk_notifications_user_id` FOREIGN KEY (`user_id`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `user_fcm_tokens`
--
ALTER TABLE `user_fcm_tokens`
  ADD CONSTRAINT `fk_user_fcm_tokens_user_id` FOREIGN KEY (`user_id`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
