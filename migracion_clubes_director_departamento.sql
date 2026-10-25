ALTER TABLE `clubes`
  ADD COLUMN `director_tecnico` varchar(43) DEFAULT NULL AFTER `ciudad`,
  ADD COLUMN `departamento` varchar(30) DEFAULT NULL AFTER `director_tecnico`;
