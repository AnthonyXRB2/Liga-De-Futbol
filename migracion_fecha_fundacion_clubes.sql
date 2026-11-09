-- Añade la fecha de fundación al registro de cada club.
ALTER TABLE `clubes`
  ADD COLUMN `fecha_fundacion` date DEFAULT NULL AFTER `nombre`;
