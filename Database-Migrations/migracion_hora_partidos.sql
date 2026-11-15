-- Incorpora la hora programada de inicio de cada partido.
ALTER TABLE partidos
ADD COLUMN hora TIME DEFAULT NULL AFTER fecha;
