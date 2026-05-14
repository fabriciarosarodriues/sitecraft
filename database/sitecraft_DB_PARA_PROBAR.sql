-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1
-- Tiempo de generación: 08-05-2026 a las 23:30:40
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
-- Base de datos: `sitecraft`
--

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `payments`
--

CREATE TABLE `payments` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `enrollment_id` bigint(20) UNSIGNED NOT NULL,
  `amount` decimal(10,2) NOT NULL,
  `currency` char(3) NOT NULL DEFAULT 'EUR',
  `method` enum('card','transfer','paypal','bizum','cash','other') NOT NULL DEFAULT 'card',
  `payment_status` enum('pending','paid','failed','refunded','expired') NOT NULL DEFAULT 'pending',
  `external_reference` varchar(190) DEFAULT NULL,
  `due_at` datetime DEFAULT NULL,
  `paid_at` datetime DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `payments`
--

INSERT INTO `payments` (`id`, `enrollment_id`, `amount`, `currency`, `method`, `payment_status`, `external_reference`, `due_at`, `paid_at`, `created_at`, `updated_at`) VALUES
(1, 9, 29.99, 'EUR', 'card', 'pending', NULL, '2026-05-11 17:48:10', NULL, '2026-05-08 15:48:10', '2026-05-08 15:48:10'),
(2, 10, 34.99, 'EUR', 'card', 'pending', NULL, '2026-05-11 18:31:38', NULL, '2026-05-08 16:31:38', '2026-05-08 16:31:38');

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `payments`
--
ALTER TABLE `payments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_payments_enrollment_status` (`enrollment_id`,`payment_status`),
  ADD KEY `idx_payments_due_at` (`due_at`),
  ADD KEY `idx_payments_external_ref` (`external_reference`);

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `payments`
--
ALTER TABLE `payments`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- Restricciones para tablas volcadas
--

--
-- Filtros para la tabla `payments`
--
ALTER TABLE `payments`
  ADD CONSTRAINT `fk_payments_enrollment` FOREIGN KEY (`enrollment_id`) REFERENCES `enrollments` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
