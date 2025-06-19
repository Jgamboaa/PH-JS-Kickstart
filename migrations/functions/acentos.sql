DELIMITER //

DROP FUNCTION IF EXISTS `fn_remove_accents`//
CREATE FUNCTION `fn_remove_accents`(txt VARCHAR(255)) RETURNS varchar(255) CHARSET latin1 COLLATE latin1_swedish_ci
    NO SQL
    DETERMINISTIC
    COMMENT 'Elimina acentos y símbolos especiales'
BEGIN
  SET txt = REPLACE(txt, 'á', 'a');
  SET txt = REPLACE(txt, 'Á', 'A');
  SET txt = REPLACE(txt, 'é', 'e');
  SET txt = REPLACE(txt, 'É', 'E');
  SET txt = REPLACE(txt, 'í', 'i');
  SET txt = REPLACE(txt, 'Í', 'I');
  SET txt = REPLACE(txt, 'ó', 'o');
  SET txt = REPLACE(txt, 'Ó', 'O');
  SET txt = REPLACE(txt, 'ú', 'u');
  SET txt = REPLACE(txt, 'Ú', 'U');
  SET txt = REPLACE(txt, 'ñ', 'n');
  SET txt = REPLACE(txt, 'Ñ', 'N');
  SET txt = REPLACE(txt, 'ü', 'u');
  SET txt = REPLACE(txt, 'Ü', 'U');

  -- Asegúrate de tener MariaDB 10.0+ para usar REGEXP_REPLACE
  SET txt = REGEXP_REPLACE(txt, '[^[:alnum:] ]', '');

  RETURN txt;
END//

DELIMITER ;