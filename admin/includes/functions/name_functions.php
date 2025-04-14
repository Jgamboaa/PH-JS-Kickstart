<?php
function normalizarCaracteres($texto)
{
    // Mapeo de caracteres especiales comunes
    $caracteres = array(
        'Á' => 'á',
        'É' => 'é',
        'Í' => 'í',
        'Ó' => 'ó',
        'Ú' => 'ú',
        'Ñ' => 'ñ',
        'Ü' => 'ü',
        // Otros caracteres especiales que puedan aparecer
        'À' => 'à',
        'È' => 'è',
        'Ì' => 'ì',
        'Ò' => 'ò',
        'Ù' => 'ù',
        'Ä' => 'ä',
        'Ë' => 'ë',
        'Ï' => 'ï',
        'Ö' => 'ö',
        'Â' => 'â',
        'Ê' => 'ê',
        'Î' => 'î',
        'Ô' => 'ô',
        'Û' => 'û'
    );

    // Primero convertimos todo a minúsculas
    $texto = strtr($texto, $caracteres);

    // Luego aplicamos el formato de título (primera letra de cada palabra en mayúscula)
    return mb_convert_case($texto, MB_CASE_TITLE, 'UTF-8');
}

/**
 * Formatea palabras especiales de apellidos como "de", "del", "la", etc.
 */
function formatearConectores($texto)
{
    $conectores = ['de', 'del', 'la', 'las', 'los', 'y', 'e', 'von', 'van', 'da'];
    $palabras = explode(' ', $texto);

    foreach ($palabras as $key => $palabra) {
        $palabraLower = mb_strtolower($palabra, 'UTF-8');
        if (in_array($palabraLower, $conectores)) {
            $palabras[$key] = $palabraLower;
        }
    }

    return implode(' ', $palabras);
}

/**
 * Detecta si un nombre corresponde a una empresa
 */
function detectarEmpresa($nombreCompleto)
{
    $indicadoresEmpresa = [
        'SOCIEDAD ANONIMA',
        'S.A.',
        'S.A',
        'SA',
        'S DE RL',
        'S. DE R.L.',
        'SOCIEDAD DE RESPONSABILIDAD LIMITADA',
        'SRL',
        'S.R.L.',
        'SOCIEDAD CIVIL',
        'S.C.',
        'SOCIEDAD COOPERATIVA',
        'CORPORACION',
        'COMPANY',
        'COMPAÑIA',
        'LTDA',
        'CIA',
        'CIA.',
        'LIMITADA'
    ];

    foreach ($indicadoresEmpresa as $indicador) {
        if (stripos($nombreCompleto, $indicador) !== false) {
            return true;
        }
    }

    return false;
}

/**
 * Formatea nombres de empresas
 */
function formatearNombreEmpresa($nombreEmpresa)
{
    // Eliminar comas y normalizar el texto
    $nombreSinComas = str_replace(',', ' ', $nombreEmpresa);
    $normalizado = normalizarCaracteres(trim($nombreSinComas));

    // Identificar razón social y nombre comercial
    $indicadoresEmpresa = [
        'SOCIEDAD ANONIMA',
        'S.A.',
        'S.A',
        'SA',
        'S DE RL',
        'S. DE R.L.',
        'SOCIEDAD DE RESPONSABILIDAD LIMITADA',
        'SRL',
        'S.R.L.',
        'SOCIEDAD CIVIL',
        'S.C.',
        'SOCIEDAD COOPERATIVA'
    ];

    $partes = preg_split('/\s+/', $normalizado);
    $nombreComercial = [];
    $razonSocial = [];

    foreach ($partes as $parte) {
        $esPalabraRazonSocial = false;
        foreach ($indicadoresEmpresa as $indicador) {
            if (strcasecmp($parte, normalizarCaracteres($indicador)) === 0) {
                $esPalabraRazonSocial = true;
                $razonSocial[] = $parte;
                break;
            }
        }

        if (!$esPalabraRazonSocial) {
            $nombreComercial[] = $parte;
        }
    }

    // Si no se identificó ninguna parte como razón social, devolver el nombre normalizado
    if (empty($razonSocial)) {
        return $normalizado;
    }

    // Primero el nombre comercial, luego la razón social
    return implode(' ', $nombreComercial) . ' ' . implode(' ', $razonSocial);
}

function formatearNombre($nombreCompleto)
{
    if (empty($nombreCompleto)) {
        return '';
    }

    // Verificar si es nombre de empresa
    if (detectarEmpresa($nombreCompleto)) {
        return formatearNombreEmpresa($nombreCompleto);
    }

    // Debug
    // error_log("Nombre original: " . $nombreCompleto);

    // Detectar formato del nombre
    if (strpos($nombreCompleto, ',,') !== false) {
        // Formato con doble coma (APELLIDOS,,NOMBRES)
        $partes = explode(',,', $nombreCompleto);

        if (count($partes) === 2) {
            // Separar apellidos y nombres
            $apellidos = explode(',', $partes[0]);
            $nombres = explode(',', $partes[1]);

            // Limpiar y formatear cada parte
            $apellidos = array_map(function ($apellido) {
                $normalizado = normalizarCaracteres(trim($apellido));
                return formatearConectores($normalizado);
            }, $apellidos);

            $nombres = array_map(function ($nombre) {
                return normalizarCaracteres(trim($nombre));
            }, $nombres);

            // // Debug
            // error_log("Formato doble coma - Nombres: " . json_encode($nombres));
            // error_log("Formato doble coma - Apellidos: " . json_encode($apellidos));

            // El orden correcto es: primero nombres, luego apellidos
            return implode(' ', array_merge($nombres, $apellidos));
        }
    } else if (strpos($nombreCompleto, ', ') !== false) {
        // Formato tradicional (APELLIDOS, NOMBRES)
        $partes = explode(', ', $nombreCompleto);

        if (count($partes) === 2) {
            $apellidos = $partes[0];
            $nombres = $partes[1];

            // Formatear apellidos (puede tener conectores)
            $apellidosFormateados = formatearConectores(normalizarCaracteres($apellidos));

            // Formatear nombres
            $nombresFormateados = normalizarCaracteres($nombres);

            // // Debug
            // error_log("Formato tradicional - Nombres: " . $nombresFormateados);
            // error_log("Formato tradicional - Apellidos: " . $apellidosFormateados);

            // Invertir para que primero vayan los nombres
            return $nombresFormateados . ' ' . $apellidosFormateados;
        }
    }

    // Si no detecta ningún formato especial, normalizar y formatear el nombre completo
    $normalizado = normalizarCaracteres($nombreCompleto);
    return formatearConectores($normalizado);
}
