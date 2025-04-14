<?php // Convertir la fecha a español
function convertirFechaALetras($fecha)
{
    $fecha = str_replace(
        array(
            'Monday',
            'Tuesday',
            'Wednesday',
            'Thursday',
            'Friday',
            'Saturday',
            'Sunday',
            'January',
            'February',
            'March',
            'April',
            'May',
            'June',
            'July',
            'August',
            'September',
            'October',
            'November',
            'December'
        ),
        array(
            'Lunes',
            'Martes',
            'Miércoles',
            'Jueves',
            'Viernes',
            'Sábado',
            'Domingo',
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
        ),
        $fecha
    );
    return $fecha;
};

function convertirFechaATexto($fecha, $soloAnio = false)
{
    // Definición de arrays para meses y números
    $meses = [
        1 => 'enero',
        2 => 'febrero',
        3 => 'marzo',
        4 => 'abril',
        5 => 'mayo',
        6 => 'junio',
        7 => 'julio',
        8 => 'agosto',
        9 => 'septiembre',
        10 => 'octubre',
        11 => 'noviembre',
        12 => 'diciembre'
    ];

    $numeros = [
        1 => 'uno',
        2 => 'dos',
        3 => 'tres',
        4 => 'cuatro',
        5 => 'cinco',
        6 => 'seis',
        7 => 'siete',
        8 => 'ocho',
        9 => 'nueve',
        10 => 'diez',
        11 => 'once',
        12 => 'doce',
        13 => 'trece',
        14 => 'catorce',
        15 => 'quince',
        16 => 'dieciséis',
        17 => 'diecisiete',
        18 => 'dieciocho',
        19 => 'diecinueve',
        20 => 'veinte',
        21 => 'veintiuno',
        22 => 'veintidós',
        23 => 'veintitrés',
        24 => 'veinticuatro',
        25 => 'veinticinco',
        26 => 'veintiséis',
        27 => 'veintisiete',
        28 => 'veintiocho',
        29 => 'veintinueve',
        30 => 'treinta',
        31 => 'treinta y uno'
    ];

    // Separamos la fecha en partes: año, mes, día
    // Asumiendo que el formato es YYYY-MM-DD.
    $partes = explode('-', $fecha);

    // Convertimos a enteros para trabajar con ellos
    $anio = intval($partes[0]);
    $mesNumero = isset($partes[1]) ? intval($partes[1]) : 0;
    $diaNumero = isset($partes[2]) ? intval($partes[2]) : 0;

    // Calculamos la representación en texto del año
    $miles = floor($anio / 1000);       // 2000 -> 2
    $centenas = floor(($anio % 1000) / 100); // 20 -> 0
    $decenas = $anio % 100;            // 20 -> 20

    // Iniciamos el texto del año con las "miles"
    $textoAnio = '';
    if ($miles > 0) {
        $textoAnio .= $numeros[$miles] . ' mil ';
    }

    // Si hay centenas (1-9), concatenamos "cientos"
    if ($centenas > 0) {
        $textoAnio .= $numeros[$centenas] . 'cientos ';
    }

    // Para las decenas (y unidades)
    if ($decenas > 0) {
        // Evitar doble espacio si no hay miles o centenas
        if (!empty(trim($textoAnio)) && substr($textoAnio, -1) !== ' ') {
            $textoAnio .= ' ';
        }
        $textoAnio .= $numeros[$decenas];
    }

    // Si el parámetro $soloAnio es TRUE, devolvemos únicamente el año en texto
    if ($soloAnio) {
        return trim($textoAnio);
    }

    // Caso contrario, convertimos también día y mes (si existen en la cadena)
    $diaTexto = isset($numeros[$diaNumero]) ? $numeros[$diaNumero] : $diaNumero;
    $mesTexto = isset($meses[$mesNumero]) ? $meses[$mesNumero] : $mesNumero;

    return trim($diaTexto) . ' de ' . $mesTexto . ' de ' . trim($textoAnio);
}


function convertirNumeroALetras($numero, $esRecursivo = false)
{
    // Separa la parte decimal y entera del número
    $decimales = round(($numero - floor($numero)) * 100);
    $entero = floor($numero);

    // Definición de arreglos para representar las unidades, decenas y centenas en palabras
    $unidades = array('cero', 'un', 'dos', 'tres', 'cuatro', 'cinco', 'seis', 'siete', 'ocho', 'nueve', 'diez', 'once', 'doce', 'trece', 'catorce', 'quince', 'dieciséis', 'diecisiete', 'dieciocho', 'diecinueve');
    $decenas = array('', '', 'veinte', 'treinta', 'cuarenta', 'cincuenta', 'sesenta', 'setenta', 'ochenta', 'noventa');
    $centenas = array('', 'ciento', 'doscientos', 'trescientos', 'cuatrocientos', 'quinientos', 'seiscientos', 'setecientos', 'ochocientos', 'novecientos');

    // Variable para almacenar la representación en palabras
    $resultado = '';

    // Comienza la conversión
    if ($entero == 0) {
        $resultado .= $unidades[$entero];
    } elseif ($entero < 20) {
        $resultado .= $unidades[$entero];
    } elseif ($entero < 100) {
        if ($entero < 30 && $entero > 20) {
            $resultado .= 'veinti' . $unidades[$entero % 10];
        } else {
            $resultado .= $decenas[floor($entero / 10)];
            if ($entero % 10 != 0) {
                $resultado .= ' y ' . $unidades[$entero % 10];
            }
        }
    } elseif ($entero < 1000) {
        if ($entero == 100) {
            $resultado .= 'cien';
        } else {
            $resultado .= $centenas[floor($entero / 100)];
            if ($entero % 100 != 0) {
                $resultado .= ' ' . convertirNumeroALetras($entero % 100, true);
            }
        }
    } elseif ($entero < 1000000) {
        $miles = floor($entero / 1000);
        $resto = $entero % 1000;
        if ($miles == 1) {
            $resultado .= 'mil';
        } elseif ($miles > 1) {
            $resultado .= convertirNumeroALetras($miles, true) . ' mil';
        }
        if ($resto != 0) {
            $resultado .= ' ' . convertirNumeroALetras($resto, true);
        }
    } else {
        $resultado .= 'Número no soportado';
    }

    // Agrega "quetzales" al final si la función no se está ejecutando de manera recursiva
    if (!$esRecursivo) {
        $resultado .= ' quetzales';
    }

    if ($decimales > 0 && !$esRecursivo) {
        $resultado .= ' con ' . sprintf("%02d", $decimales) . '/100 centavos';
    }

    return $resultado;
};

function edadEnLetras($fecha_nacimiento)
{
    // Convertir la fecha de nacimiento a un objeto DateTime
    $fecha_nacimiento_obj = new DateTime($fecha_nacimiento);
    // Obtener la fecha actual
    $fecha_actual = new DateTime();
    // Calcular la diferencia entre la fecha de nacimiento y la fecha actual
    $diferencia = $fecha_nacimiento_obj->diff($fecha_actual);
    // Obtener la edad en años
    $edad = $diferencia->y;

    // Arreglo para convertir números a letras
    $numeros_letras = array(
        0 => 'cero',
        1 => 'un',
        2 => 'dos',
        3 => 'tres',
        4 => 'cuatro',
        5 => 'cinco',
        6 => 'seis',
        7 => 'siete',
        8 => 'ocho',
        9 => 'nueve',
        10 => 'diez',
        11 => 'once',
        12 => 'doce',
        13 => 'trece',
        14 => 'catorce',
        15 => 'quince',
        16 => 'dieciséis',
        17 => 'diecisiete',
        18 => 'dieciocho',
        19 => 'diecinueve',
        20 => 'veinte',
        30 => 'treinta',
        40 => 'cuarenta',
        50 => 'cincuenta',
        60 => 'sesenta',
        70 => 'setenta',
        80 => 'ochenta',
        90 => 'noventa'
    );

    if ($edad < 21) {
        // Si la edad es menor que 21, usamos el arreglo para convertir el número en letras
        return $numeros_letras[$edad] . ' años';
    } elseif ($edad < 100) {
        // Si la edad está entre 21 y 99 años, la descomponemos en decenas y unidades
        $decenas = floor($edad / 10) * 10;
        $unidades = $edad % 10;
        // Si las unidades son cero, devolvemos solo la parte de las decenas
        if ($unidades == 0) {
            return $numeros_letras[$decenas] . ' años';
        } else {
            // Si hay unidades, las convertimos a letras también
            if ($decenas == 20) {
                return 'veinti' . $numeros_letras[$unidades] . ' años';
            } else {
                return $numeros_letras[$decenas] . ' y ' . $numeros_letras[$unidades] . ' años';
            }
        }
    } else {
        // Si la edad es mayor o igual a 100, simplemente devolvemos la edad en números
        return $edad . ' años';
    }
};

function convertirNumerodpiletras($numero, $esRecursivo = false)
{
    // Definición de arreglos para representar las unidades, decenas y centenas en palabras
    $unidades = array('cero', 'uno', 'dos', 'tres', 'cuatro', 'cinco', 'seis', 'siete', 'ocho', 'nueve', 'diez', 'once', 'doce', 'trece', 'catorce', 'quince', 'dieciséis', 'diecisiete', 'dieciocho', 'diecinueve');
    $decenas = array('', '', 'veinte', 'treinta', 'cuarenta', 'cincuenta', 'sesenta', 'setenta', 'ochenta', 'noventa');
    $centenas = array('', 'ciento', 'doscientos', 'trescientos', 'cuatrocientos', 'quinientos', 'seiscientos', 'setecientos', 'ochocientos', 'novecientos');

    // Variable para almacenar la representación en palabras
    $resultado = '';

    // Comienza la conversión
    if ($numero < 20) {
        $resultado .= $unidades[$numero];
    } elseif ($numero < 100) {
        if ($numero == 21) {
            $resultado .= 'veintiún';
        } elseif ($numero < 30 && $numero > 20) {
            $resultado .= 'veinti' . $unidades[$numero % 10];
        } else {
            $resultado .= $decenas[floor($numero / 10)];
            if ($numero % 10 != 0) {
                $resultado .= ' y ' . $unidades[$numero % 10];
            }
        }
    } elseif ($numero < 1000) {
        if ($numero == 100) {
            $resultado .= 'cien';
        } else {
            $resultado .= $centenas[floor($numero / 100)];
            if ($numero % 100 != 0) {
                $resultado .= ' ' . convertirNumerodpiletras($numero % 100, true);
            }
        }
    } elseif ($numero < 1000000) {
        $miles = floor($numero / 1000);
        $resto = $numero % 1000;
        if ($miles == 1) {
            $resultado .= 'mil';
        } elseif ($miles < 30) {
            if ($miles == 21 && $esRecursivo) {
                $resultado .= 'veintiún mil';
            } else {
                $resultado .= convertirNumerodpiletras($miles, true) . ' mil';
            }
        } else {
            $resultado .= convertirNumerodpiletras($miles, true) . ' mil';
        }
        if ($resto != 0) {
            $resultado .= ' ' . convertirNumerodpiletras($resto, true);
        }
    } else {
        $resultado .= 'Número no soportado';
    }

    return $resultado;
};

function convertirDpiEnLetras($dpi)
{
    // Reemplazar los espacios vacíos con "-"
    $dpi = str_replace(' ', '-', $dpi);

    // Dividir el dpi en tres partes
    $partes = explode('-', $dpi);

    // Convertir cada parte en palabras
    foreach ($partes as $i => $parte) {
        // Si la parte comienza con cero, agregar 'cero' al resultado
        if (substr($parte, 0, 1) == '0') {
            $partes[$i] = 'cero ' . convertirNumerodpiletras(ltrim($parte, '0'));
        } else {
            $partes[$i] = convertirNumerodpiletras($parte);
        }
    }

    // Unir las partes con comas
    $resultado = implode(', ', $partes);

    return $resultado;
}

function convertirMes($mesIngles)
{
    $meses = [
        'January' => 'Enero',
        'February' => 'Febrero',
        'March' => 'Marzo',
        'April' => 'Abril',
        'May' => 'Mayo',
        'June' => 'Junio',
        'July' => 'Julio',
        'August' => 'Agosto',
        'September' => 'Septiembre',
        'October' => 'Octubre',
        'November' => 'Noviembre',
        'December' => 'Diciembre',
    ];

    return $meses[$mesIngles] ?? $mesIngles;
}
