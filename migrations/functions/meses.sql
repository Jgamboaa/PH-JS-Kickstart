DELIMITER //

DROP FUNCTION IF EXISTS `mes_espanol`//
CREATE FUNCTION `mes_espanol`(IN fecha DATE) RETURNS varchar(20) CHARSET latin1 COLLATE latin1_swedish_ci
    DETERMINISTIC
BEGIN RETURN ELT(
    MONTH(fecha),
    'Enero',
    'Febrero',
    'Marzo',
    'Abril',
    'Mayo',
    'Junio',
    'Julio',
    'Agosto',
    'Septiembre',
    'Octubre',
    'Noviembre',
    'Diciembre'
);
END//

DELIMITER ;