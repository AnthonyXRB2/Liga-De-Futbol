-- Guarda la fecha y hora de publicación de cada comunicado.
ALTER TABLE `comunicados`
  ADD COLUMN `fecha_publicacion` DATETIME DEFAULT NULL;
