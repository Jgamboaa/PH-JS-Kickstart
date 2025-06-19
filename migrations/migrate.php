<?php

require_once dirname(__DIR__) . '/config/db_conn.php';

// Verificar si se proporcionó un argumento
if ($argc < 2)
{
    echo "Uso: php migrate.php [functions|triggers|procedures|etc]\n";
    exit(1);
}

$type = $argv[1];
$validTypes = ['functions', 'triggers', 'procedures'];

// Validar el tipo proporcionado
if (!in_array($type, $validTypes))
{
    echo "Tipo no válido. Usar uno de: " . implode(', ', $validTypes) . "\n";
    exit(1);
}

// Directorio que contiene los archivos SQL para el tipo especificado
$directory = __DIR__ . '/' . $type;

// Verificar si el directorio existe
if (!is_dir($directory))
{
    echo "Error: El directorio '{$directory}' no existe.\n";
    exit(1);
}

// Obtener todos los archivos SQL del directorio
$sqlFiles = glob($directory . '/*.sql');

if (empty($sqlFiles))
{
    echo "No se encontraron archivos SQL en '{$directory}'.\n";
    exit(1);
}

// Contador de migraciones exitosas
$successCount = 0;
$errorCount = 0;

// Procesar cada archivo SQL
foreach ($sqlFiles as $sqlFile)
{
    $filename = basename($sqlFile);
    echo "Migrando {$filename}...\n";

    // Leer el contenido del archivo
    $sqlContent = file_get_contents($sqlFile);
    if ($sqlContent === false)
    {
        echo "Error: No se pudo leer el archivo '{$filename}'.\n";
        $errorCount++;
        continue;
    }

    try
    {
        // Preprocesar el contenido SQL para manejar DELIMITER
        $queries = preprocessSqlContent($sqlContent);
        $hasError = false;

        // Ejecutar cada consulta individualmente
        foreach ($queries as $query)
        {
            $query = trim($query);
            if (empty($query)) continue;

            try
            {
                $stmt = $conn->prepare($query);
                $stmt->execute();
            }
            catch (PDOException $e)
            {
                echo "❌ Error en consulta: " . $e->getMessage() . "\n";
                echo "Consulta problemática: " . substr($query, 0, 150) . "...\n";
                $hasError = true;
                break;
            }
        }

        if (!$hasError)
        {
            echo "✅ Migración exitosa: '{$filename}'.\n";
            $successCount++;
        }
        else
        {
            $errorCount++;
        }
    }
    catch (Exception $e)
    {
        echo "❌ Excepción: " . $e->getMessage() . "\n";
        $errorCount++;
    }
}

/**
 * Preprocesa el contenido SQL dividiendo por delimitadores personalizados
 * @param string $content Contenido del archivo SQL
 * @return array Array de consultas SQL
 */
function preprocessSqlContent($content)
{
    // Eliminar comentarios de una línea
    $content = preg_replace('/--.*$/m', '', $content);

    // Buscar cambios de delimitador
    $pattern = '/DELIMITER\s+([^\s]+)/i';
    preg_match_all($pattern, $content, $matches, PREG_OFFSET_CAPTURE);

    // Si no hay DELIMITER, dividir por punto y coma y retornar
    if (empty($matches[0]))
    {
        return array_filter(array_map('trim', explode(';', $content)));
    }

    // Procesar delimitadores personalizados
    $queries = [];
    $lastPos = 0;
    $delimiter = ';';

    foreach ($matches[0] as $index => $match)
    {
        $delimiterPos = $match[1];
        $newDelimiter = $matches[1][$index][0];

        // Extraer consultas con el delimitador actual antes del cambio
        $segmentContent = substr($content, $lastPos, $delimiterPos - $lastPos);
        $segmentQueries = array_filter(array_map('trim', explode($delimiter, $segmentContent)));
        $queries = array_merge($queries, $segmentQueries);

        // Actualizar posición y delimitador
        $lastPos = $delimiterPos + strlen($match[0]);
        $delimiter = $newDelimiter;
    }

    // Procesar la parte final con el último delimitador
    $segmentContent = substr($content, $lastPos);
    $segmentQueries = array_filter(array_map('trim', explode($delimiter, $segmentContent)));
    $queries = array_merge($queries, $segmentQueries);

    // Filtrar líneas DELIMITER y consultas vacías
    return array_filter($queries, function ($query)
    {
        $query = trim($query);
        return !empty($query) && !preg_match('/^DELIMITER\s/i', $query);
    });
}

// Mostrar resumen
echo "\nResumen de la migración:\n";
echo "- Archivos procesados: " . count($sqlFiles) . "\n";
echo "- Migraciones exitosas: {$successCount}\n";
echo "- Errores: {$errorCount}\n";

// Cerrar la conexión (en PDO se hace asignando null)
$conn = null;
