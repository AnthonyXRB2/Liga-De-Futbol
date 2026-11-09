-- Añade a los partidos el año de categoría al que corresponde cada encuentro.
ALTER TABLE partidos
    ADD COLUMN categoria_edad varchar(50) DEFAULT NULL AFTER categoria;
