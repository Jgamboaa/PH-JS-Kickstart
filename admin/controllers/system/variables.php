<?php
class VariablesController
{
    private $conn;
    private $envFilePath;

    public function __construct($conn)
    {
        $this->conn = $conn;
        $this->envFilePath = __DIR__ . '/../../../.env';
    }

    /**
     * Obtiene todas las variables de entorno excluyendo las relacionadas con la base de datos
     * @return array Variables de entorno
     */
    public function getEnvVariables()
    {
        if (!file_exists($this->envFilePath))
        {
            return [
                'status' => false,
                'message' => 'El archivo .env no existe',
                'variables' => []
            ];
        }

        $lines = file($this->envFilePath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        $variables = [];
        $excludedPrefixes = ['DB_', 'DATABASE_'];

        foreach ($lines as $line)
        {
            // Ignorar comentarios
            if (strpos(trim($line), '#') === 0)
            {
                continue;
            }

            // Analizar asignación de variable
            if (strpos($line, '=') !== false)
            {
                list($name, $value) = explode('=', $line, 2);
                $name = trim($name);
                $value = trim($value);

                // Excluir variables de base de datos
                $exclude = false;
                foreach ($excludedPrefixes as $prefix)
                {
                    if (strpos($name, $prefix) === 0)
                    {
                        $exclude = true;
                        break;
                    }
                }

                if (!$exclude)
                {
                    $variables[] = [
                        'name' => $name,
                        'value' => $value
                    ];
                }
            }
        }

        return [
            'status' => true,
            'message' => 'Variables cargadas correctamente',
            'variables' => $variables
        ];
    }

    /**
     * Guarda o actualiza una variable de entorno
     * @param string $name Nombre de la variable
     * @param string $value Valor de la variable
     * @return array Resultado de la operación
     */
    public function saveEnvVariable($name, $value)
    {
        if (empty($name))
        {
            return [
                'status' => false,
                'message' => 'El nombre de la variable no puede estar vacío'
            ];
        }

        // Verificar que no sea una variable de base de datos
        $excludedPrefixes = ['DB_', 'DATABASE_'];
        foreach ($excludedPrefixes as $prefix)
        {
            if (strpos($name, $prefix) === 0)
            {
                return [
                    'status' => false,
                    'message' => 'No se pueden modificar variables de base de datos'
                ];
            }
        }

        // Leer el archivo .env
        if (!file_exists($this->envFilePath))
        {
            return [
                'status' => false,
                'message' => 'El archivo .env no existe'
            ];
        }

        $lines = file($this->envFilePath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        $newLines = [];
        $updated = false;

        foreach ($lines as $line)
        {
            if (strpos(trim($line), '#') === 0)
            {
                $newLines[] = $line; // Mantener comentarios
                continue;
            }

            if (strpos($line, '=') !== false)
            {
                list($currentName, $currentValue) = explode('=', $line, 2);
                $currentName = trim($currentName);

                if ($currentName === $name)
                {
                    $newLines[] = "$name=$value";
                    $updated = true;
                }
                else
                {
                    $newLines[] = $line;
                }
            }
            else
            {
                $newLines[] = $line;
            }
        }

        // Si la variable no existía, la agregamos al final
        if (!$updated)
        {
            $newLines[] = "$name=$value";
        }

        // Guardar el archivo .env
        if (file_put_contents($this->envFilePath, implode(PHP_EOL, $newLines)) === false)
        {
            return [
                'status' => false,
                'message' => 'Error al guardar el archivo .env'
            ];
        }

        // Actualizar la variable en el entorno actual
        $_ENV[$name] = $value;
        putenv("$name=$value");

        return [
            'status' => true,
            'message' => $updated ? 'Variable actualizada correctamente' : 'Variable creada correctamente'
        ];
    }

    /**
     * Elimina una variable de entorno
     * @param string $name Nombre de la variable
     * @return array Resultado de la operación
     */
    public function deleteEnvVariable($name)
    {
        if (empty($name))
        {
            return [
                'status' => false,
                'message' => 'El nombre de la variable no puede estar vacío'
            ];
        }

        // Verificar que no sea una variable protegida
        $excludedPrefixes = ['DB_', 'DATABASE_', 'APP_', 'MAIL_'];
        foreach ($excludedPrefixes as $prefix)
        {
            if (strpos($name, $prefix) === 0)
            {
                return [
                    'status' => false,
                    'message' => 'No se pueden eliminar variables esenciales del sistema'
                ];
            }
        }

        // Leer el archivo .env
        if (!file_exists($this->envFilePath))
        {
            return [
                'status' => false,
                'message' => 'El archivo .env no existe'
            ];
        }

        $lines = file($this->envFilePath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        $newLines = [];
        $deleted = false;

        foreach ($lines as $line)
        {
            if (strpos(trim($line), '#') === 0)
            {
                $newLines[] = $line; // Mantener comentarios
                continue;
            }

            if (strpos($line, '=') !== false)
            {
                list($currentName, $currentValue) = explode('=', $line, 2);
                $currentName = trim($currentName);

                if ($currentName === $name)
                {
                    $deleted = true;
                    // No incluimos esta línea en $newLines para eliminarla
                }
                else
                {
                    $newLines[] = $line;
                }
            }
            else
            {
                $newLines[] = $line;
            }
        }

        if (!$deleted)
        {
            return [
                'status' => false,
                'message' => 'Variable no encontrada'
            ];
        }

        // Guardar el archivo .env
        if (file_put_contents($this->envFilePath, implode(PHP_EOL, $newLines)) === false)
        {
            return [
                'status' => false,
                'message' => 'Error al guardar el archivo .env'
            ];
        }

        // Eliminar la variable del entorno actual
        putenv($name);
        unset($_ENV[$name]);

        return [
            'status' => true,
            'message' => 'Variable eliminada correctamente'
        ];
    }
}
