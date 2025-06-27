<?php

/**
 * Setup Class - Core Installer
 * 
 * Contiene la lógica para la instalación y configuración inicial del sistema
 */

class Setup
{
    // Rutas de archivos
    private $envFile;
    private $envExampleFile;
    private $sqlFile;
    private $adminSqlFile;

    // Variables de estado
    public $installed = false;
    public $error = '';
    public $message = '';
    public $dbImported = false;
    public $isCli = false;
    public $composerOutput = '';
    public $composerInstalled = false;

    // Variables de estado adicionales para migraciones
    public $migrationsRun = false;
    public $seedsRun = false;
    public $migrationOutput = '';
    public $seedOutput = '';

    // Datos del formulario
    private $dbHost;
    private $dbUser;
    private $dbPass;
    private $dbName;
    private $mailHost;
    private $mailUsername;
    private $mailName;
    private $mailPassword;
    private $mailPort;
    private $mailEncryption; // nuevo
    private $mailSupport;    // nuevo
    private $adminEmail;
    private $adminPassword;
    private $adminFirstname;
    private $adminLastname;
    private $adminGender;

    /**
     * Constructor de la clase
     */
    public function __construct()
    {
        // Detectar si se está ejecutando en modo CLI
        $this->isCli = defined('CLI_MODE') || php_sapi_name() === 'cli';

        // Inicializar rutas de archivos
        $this->envFile = dirname(__DIR__) . '/.env';
        $this->envExampleFile = dirname(__DIR__) . '/.env_example';
        $this->sqlFile = dirname(__DIR__) . '/config/core.sql';
        $this->adminSqlFile = dirname(__DIR__) . '/config/usuario_admin.sql';

        // Verificar si ya existe el .env
        if (file_exists($this->envFile))
        {
            $this->installed = true;
            $this->message = "El sistema ya está configurado. Si deseas reinstalarlo, elimina el archivo .env y vuelve a cargar esta página.";
        }
    }

    /**
     * Establece la configuración de base de datos
     * 
     * @param string $host Servidor de base de datos
     * @param string $user Usuario de base de datos
     * @param string $pass Contraseña de base de datos
     * @param string $name Nombre de base de datos
     */
    public function setDbConfig($host, $user, $pass, $name)
    {
        $this->dbHost = $host;
        $this->dbUser = $user;
        $this->dbPass = $pass;
        $this->dbName = $name;
    }

    /**
     * Establece la configuración de correo
     * 
     * @param string $host Servidor SMTP
     * @param string $username Correo electrónico
     * @param string $name Nombre del remitente
     * @param string $password Contraseña
     * @param string $port Puerto SMTP
     * @param string $encryption Tipo de encriptación
     * @param string $support Correo de soporte
     */
    public function setMailConfig($host, $username, $name, $password, $port, $encryption, $support)
    {
        $this->mailHost = $host;
        $this->mailUsername = $username;
        $this->mailName = $name;
        $this->mailPassword = $password;
        $this->mailPort = $port;
        $this->mailEncryption = $encryption;
        $this->mailSupport = $support;
    }

    /**
     * Establece la configuración del usuario administrador
     * 
     * @param string $email Correo electrónico
     * @param string $password Contraseña
     * @param string $firstname Nombre
     * @param string $lastname Apellido
     * @param int $gender Género (0=masculino, 1=femenino, 2=otro)
     */
    public function setAdminConfig($email, $password, $firstname, $lastname, $gender)
    {
        $this->adminEmail = $email;
        $this->adminPassword = $password;
        $this->adminFirstname = $firstname;
        $this->adminLastname = $lastname;
        $this->adminGender = $gender;
    }

    /**
     * Ejecuta el proceso de instalación con la configuración establecida
     */
    public function runInstallation()
    {
        try
        {
            // Ejecutar los pasos de instalación (orden modificado)
            $this->checkDatabaseConnection();
            $this->createDatabaseIfNeeded();
            $this->checkWritePermissions();
            $this->createEnvFile();

            // Instalar dependencias primero
            $this->installComposerDependencies();

            // Ejecutar migraciones y semillas solo si las dependencias están instaladas
            if ($this->composerInstalled)
            {
                $this->runMigrations();
                $this->runSeeds();
                // No necesitamos importar la base de datos ni crear admin
                // manualmente ya que esto lo hacen las migraciones y semillas
            }
            else
            {
                // Fallback al método antiguo si no se pueden instalar dependencias
                $this->importDatabase();
                $this->createAdminUser();
            }

            $this->installed = true;
            $this->message = "¡Configuración completada! El sistema ha sido inicializado correctamente.";

            if ($this->composerInstalled)
            {
                $this->message .= " Las dependencias de Composer han sido instaladas.";
            }

            if ($this->migrationsRun)
            {
                $this->message .= " Las migraciones se han ejecutado correctamente.";
            }
            else if ($this->dbImported)
            {
                $this->message .= " La estructura de la base de datos ha sido importada.";
            }

            if ($this->seedsRun || $this->dbImported)
            {
                $this->message .= " El usuario administrador ha sido creado.";
            }
        }
        catch (Exception $e)
        {
            $this->error = $e->getMessage();
        }
    }

    /**
     * Procesa el formulario de configuración
     */
    public function processSetupForm()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['setup']))
        {
            try
            {
                // Obtener datos del formulario
                $this->dbHost = filter_input(INPUT_POST, 'db_host', FILTER_SANITIZE_SPECIAL_CHARS);
                $this->dbUser = filter_input(INPUT_POST, 'db_user', FILTER_SANITIZE_SPECIAL_CHARS);
                $this->dbPass = filter_input(INPUT_POST, 'db_pass', FILTER_UNSAFE_RAW);
                $this->dbName = filter_input(INPUT_POST, 'db_name', FILTER_SANITIZE_SPECIAL_CHARS);
                $this->mailHost = filter_input(INPUT_POST, 'mail_host', FILTER_SANITIZE_SPECIAL_CHARS);
                $this->mailUsername = filter_input(INPUT_POST, 'mail_username', FILTER_SANITIZE_EMAIL);
                $this->mailName = filter_input(INPUT_POST, 'mail_name', FILTER_SANITIZE_SPECIAL_CHARS);
                $this->mailPassword = filter_input(INPUT_POST, 'mail_password', FILTER_UNSAFE_RAW);
                $this->mailPort = filter_input(INPUT_POST, 'mail_port', FILTER_SANITIZE_NUMBER_INT);
                $this->mailEncryption = filter_input(INPUT_POST, 'mail_encryption', FILTER_SANITIZE_SPECIAL_CHARS); // nuevo
                $this->mailSupport = filter_input(INPUT_POST, 'mail_support', FILTER_SANITIZE_EMAIL); // nuevo

                // Obtener datos del usuario administrador
                $this->adminEmail = filter_input(INPUT_POST, 'admin_email', FILTER_SANITIZE_EMAIL);
                $this->adminPassword = filter_input(INPUT_POST, 'admin_password', FILTER_UNSAFE_RAW);
                $this->adminFirstname = filter_input(INPUT_POST, 'admin_firstname', FILTER_SANITIZE_SPECIAL_CHARS);
                $this->adminLastname = filter_input(INPUT_POST, 'admin_lastname', FILTER_SANITIZE_SPECIAL_CHARS);
                $this->adminGender = filter_input(INPUT_POST, 'admin_gender', FILTER_SANITIZE_NUMBER_INT);

                // Ejecutar la instalación
                $this->runInstallation();
            }
            catch (Exception $e)
            {
                $this->error = $e->getMessage();
            }
        }
    }

    /**
     * Verifica la conexión a la base de datos
     */
    private function checkDatabaseConnection()
    {
        // Verificamos que los valores de conexión estén establecidos
        if (empty($this->dbHost) || empty($this->dbUser))
        {
            throw new Exception("Los datos de conexión a la base de datos son inválidos");
        }

        $conn = new mysqli($this->dbHost, $this->dbUser, $this->dbPass);
        if ($conn->connect_error)
        {
            throw new Exception("Error de conexión a la base de datos: " . $conn->connect_error);
        }
        $conn->set_charset("utf8");
        $conn->close();
    }

    // Resto de los métodos permanecen igual
    private function createDatabaseIfNeeded()
    {
        $conn = new mysqli($this->dbHost, $this->dbUser, $this->dbPass);
        $conn->set_charset("utf8");

        $result = $conn->query("SELECT SCHEMA_NAME FROM INFORMATION_SCHEMA.SCHEMATA WHERE SCHEMA_NAME = '{$this->dbName}'");
        if ($result->num_rows == 0)
        {
            if (!$conn->query("CREATE DATABASE {$this->dbName}"))
            {
                throw new Exception("Error al crear la base de datos: " . $conn->error);
            }
        }
        $conn->close();
    }

    /**
     * Verifica permisos de escritura en el directorio
     */
    private function checkWritePermissions()
    {
        if (!is_writable(dirname($this->envFile)))
        {
            throw new Exception("El directorio no tiene permisos de escritura");
        }

        if (!file_exists($this->envExampleFile))
        {
            throw new Exception("El archivo .env_example no existe");
        }
    }

    /**
     * Crea el archivo .env con las configuraciones
     */
    private function createEnvFile()
    {
        $envContent = file_get_contents($this->envExampleFile);

        // Reemplazar valores
        $replacements = [
            'DB_HOST=' => "DB_HOST={$this->dbHost}",
            'DB_USER=' => "DB_USER={$this->dbUser}",
            'DB_PASS=' => "DB_PASS={$this->dbPass}",
            'DB_NAME=' => "DB_NAME={$this->dbName}",
            'MAIL_HOST=' => "MAIL_HOST={$this->mailHost}",
            'MAIL_USERNAME=' => "MAIL_USERNAME={$this->mailUsername}",
            'MAIL_NAME=' => "MAIL_NAME={$this->mailName}",
            'MAIL_PASSWORD=' => "MAIL_PASSWORD={$this->mailPassword}",
            'MAIL_ENCRYPTION=' => "MAIL_ENCRYPTION={$this->mailEncryption}", // nuevo
            'MAIL_PORT=' => "MAIL_PORT={$this->mailPort}",
            'MAIL_SUPPORT=' => "MAIL_SUPPORT={$this->mailSupport}" // nuevo
        ];

        foreach ($replacements as $search => $replace)
        {
            $envContent = preg_replace('/^' . preg_quote($search, '/') . '.*$/m', $replace, $envContent);
        }

        // Guardar el nuevo archivo .env
        if (file_put_contents($this->envFile, $envContent) === false)
        {
            throw new Exception("No se pudo escribir el archivo .env");
        }
    }

    /**
     * Importa la estructura de la base de datos
     */
    private function importDatabase()
    {
        if (file_exists($this->sqlFile))
        {
            $conn = new mysqli($this->dbHost, $this->dbUser, $this->dbPass, $this->dbName);
            if ($conn->connect_error)
            {
                throw new Exception("Error al conectar a la base de datos después de la configuración: " . $conn->connect_error);
            }

            $sql = file_get_contents($this->sqlFile);
            if (!$conn->multi_query($sql))
            {
                throw new Exception("Error al importar la estructura de la base de datos: " . $conn->error);
            }

            // Procesar todos los resultados para liberar la conexión
            do
            {
                if ($result = $conn->store_result())
                {
                    $result->free();
                }
            } while ($conn->more_results() && $conn->next_result());

            $conn->close();
            $this->dbImported = true;
        }
        else
        {
            throw new Exception("No se encontró el archivo SQL con la estructura de la base de datos.");
        }
    }

    /**
     * Crea el usuario administrador
     */
    private function createAdminUser()
    {
        if ($this->dbImported)
        {
            $conn = new mysqli($this->dbHost, $this->dbUser, $this->dbPass, $this->dbName);
            $conn->set_charset("utf8");
            if ($conn->connect_error)
            {
                throw new Exception("Error al conectar a la base de datos después de la configuración: " . $conn->connect_error);
            }

            // Verificar si el rol de administrador existe
            $checkRoleQuery = "SELECT id FROM roles WHERE nombre = 'Administrador' OR id = 1 LIMIT 1";
            $roleResult = $conn->query($checkRoleQuery);
            $roleId = 1; // Por defecto

            if ($roleResult && $roleResult->num_rows > 0)
            {
                $roleRow = $roleResult->fetch_assoc();
                $roleId = $roleRow['id'];
            }

            // Crear usuario administrador
            $passwordHashed = password_hash($this->adminPassword, PASSWORD_DEFAULT);
            $today = date("Y-m-d");

            $adminSql = "INSERT INTO admin (username, password, user_firstname, user_lastname, photo, created_on, roles_ids, admin_gender, tfa_required) 
                VALUES ('{$this->adminEmail}', '{$passwordHashed}', '{$this->adminFirstname}', '{$this->adminLastname}', '', '{$today}', '{$roleId}', '{$this->adminGender}', 1)";

            if (!$conn->query($adminSql))
            {
                throw new Exception("Error al crear el usuario administrador: " . $conn->error);
            }

            $conn->close();
        }
    }

    /**
     * Verifica si composer está disponible e instala las dependencias
     */
    public function installComposerDependencies()
    {
        // Verificar si las funciones para ejecutar comandos están disponibles
        if (!function_exists('exec') && !function_exists('shell_exec') && !function_exists('system'))
        {
            return false;
        }

        $rootPath = dirname(__DIR__);
        $composerPath = $this->findComposerPath();

        if (!$composerPath)
        {
            return false;
        }

        // Ejecutar composer install
        $command = "$composerPath install --no-interaction --no-dev --optimize-autoloader";
        if ($this->isCli)
        {
            // En modo CLI, ejecutamos directamente
            if (function_exists('passthru'))
            {
                echo "Ejecutando composer install...\n";
                passthru($command, $returnCode);
            }
            else
            {
                echo "Ejecutando composer install...\n";
                $this->composerOutput = shell_exec($command);
                echo $this->composerOutput;
                $returnCode = 0; // Asumimos éxito si no podemos capturar el código de retorno
            }
        }
        else
        {
            // En modo web, capturamos la salida para mostrarla después
            if (function_exists('exec'))
            {
                exec($command . " 2>&1", $output, $returnCode);
                $this->composerOutput = implode("\n", $output);
            }
            else
            {
                $this->composerOutput = shell_exec($command . " 2>&1");
                $returnCode = 0; // Asumimos éxito si no podemos capturar el código de retorno
            }
        }

        $this->composerInstalled = ($returnCode === 0);
        return $this->composerInstalled;
    }

    /**
     * Encuentra la ruta de composer (global o local)
     */
    private function findComposerPath()
    {
        $rootPath = dirname(__DIR__);

        // Verificar si hay un composer.phar local
        if (file_exists($rootPath . '/composer.phar'))
        {
            return 'php ' . $rootPath . '/composer.phar';
        }

        // Verificar si composer está disponible globalmente
        $output = [];
        $returnCode = 1;

        if (function_exists('exec'))
        {
            exec('composer --version 2>&1', $output, $returnCode);
        }
        else if (function_exists('shell_exec'))
        {
            $result = shell_exec('composer --version 2>&1');
            if (strpos($result, 'Composer version') !== false)
            {
                $returnCode = 0;
            }
        }

        if ($returnCode === 0)
        {
            return 'composer';
        }

        return null;
    }

    /**
     * Verifica si las funciones para ejecutar comandos del sistema están disponibles
     */
    public function canExecuteSystemCommands()
    {
        return function_exists('exec') || function_exists('shell_exec') || function_exists('system') || function_exists('passthru');
    }

    /**
     * Genera una cadena aleatoria para claves
     * 
     * @param int $length Longitud de la cadena
     * @return string Cadena aleatoria
     */
    public function generateRandomString($length = 32)
    {
        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ!@#$%^&*()_+';
        $randomString = '';

        for ($i = 0; $length > $i; $i++)
        {
            $randomString .= $characters[rand(0, strlen($characters) - 1)];
        }

        return $randomString;
    }

    /**
     * Ejecuta las migraciones de Phinx
     */
    private function runMigrations()
    {
        $rootPath = dirname(__DIR__);
        $phinxPath = $this->findPhinxPath();

        if (!$phinxPath)
        {
            throw new Exception("No se encontró Phinx en la carpeta vendor/bin. Asegúrate de que se hayan instalado las dependencias correctamente.");
        }

        // Intentar ejecutar las migraciones a través de setup_db.php
        if (file_exists($rootPath . '/setup_db.php'))
        {
            // Definir la constante para indicar que se está ejecutando desde el instalador
            define('RUNNING_FROM_INSTALLER', true);

            try
            {
                // Incluir el archivo setup_db.php que ejecutará las migraciones
                require_once $rootPath . '/setup_db.php';
                $this->migrationsRun = true;
                $this->seedsRun = true;
                return true;
            }
            catch (Exception $e)
            {
                throw new Exception("Error al ejecutar las migraciones: " . $e->getMessage());
            }
        }

        // Si llegamos aquí, usar el método anterior (directo)
        $command = "$phinxPath migrate";

        if ($this->isCli)
        {
            // En modo CLI, mostramos el progreso directamente
            echo "Ejecutando migraciones...\n";
            echo "Comando: $command\n";
            if (function_exists('passthru'))
            {
                passthru($command, $returnCode);
            }
            else
            {
                $this->migrationOutput = shell_exec($command);
                echo $this->migrationOutput;
                $returnCode = 0; // Asumimos éxito si no podemos capturar el código de retorno
            }
        }
        else
        {
            // En modo web, capturamos la salida
            if (function_exists('exec'))
            {
                exec($command . " 2>&1", $output, $returnCode);
                $this->migrationOutput = implode("\n", $output);
            }
            else if (function_exists('shell_exec'))
            {
                $this->migrationOutput = shell_exec($command . " 2>&1");
                $returnCode = 0; // Asumimos éxito
            }
            else
            {
                throw new Exception("No se pueden ejecutar comandos del sistema para las migraciones.");
            }
        }

        $this->migrationsRun = ($returnCode === 0);

        if (!$this->migrationsRun)
        {
            throw new Exception("Error al ejecutar las migraciones. Por favor revisa los logs para más detalles.");
        }

        return $this->migrationsRun;
    }

    /**
     * Ejecuta las semillas de Phinx
     */
    private function runSeeds()
    {
        // Si ya se ejecutaron las semillas junto con las migraciones, no hacer nada
        if ($this->seedsRun)
        {
            return true;
        }

        $rootPath = dirname(__DIR__);
        $phinxPath = $this->findPhinxPath();

        if (!$phinxPath)
        {
            throw new Exception("No se encontró Phinx en la carpeta vendor/bin.");
        }

        // Ejecutar semillas
        $command = "$phinxPath seed:run";

        if ($this->isCli)
        {
            // En modo CLI, mostramos el progreso directamente
            echo "Ejecutando semillas...\n";
            echo "Comando: $command\n";
            if (function_exists('passthru'))
            {
                passthru($command, $returnCode);
            }
            else
            {
                $this->seedOutput = shell_exec($command);
                echo $this->seedOutput;
                $returnCode = 0; // Asumimos éxito si no podemos capturar el código de retorno
            }
        }
        else
        {
            // En modo web, capturamos la salida
            if (function_exists('exec'))
            {
                exec($command . " 2>&1", $output, $returnCode);
                $this->seedOutput = implode("\n", $output);
            }
            else if (function_exists('shell_exec'))
            {
                $this->seedOutput = shell_exec($command . " 2>&1");
                $returnCode = 0; // Asumimos éxito
            }
            else
            {
                throw new Exception("No se pueden ejecutar comandos del sistema para las semillas.");
            }
        }

        $this->seedsRun = ($returnCode === 0);

        if (!$this->seedsRun)
        {
            throw new Exception("Error al ejecutar las semillas. Por favor revisa los logs para más detalles.");
        }

        return $this->seedsRun;
    }

    /**
     * Encuentra la ruta de Phinx
     */
    private function findPhinxPath()
    {
        $rootPath = dirname(__DIR__);
        $isWindows = PHP_OS === 'WINNT';

        // Verificar si Phinx está disponible
        $phinxBin = $isWindows ? 'vendor/bin/phinx.bat' : 'vendor/bin/phinx';
        $phinxPath = $rootPath . '/' . $phinxBin;

        if (file_exists($phinxPath))
        {
            return 'php "' . $phinxPath . '"';
        }

        // Verificar ubicación alternativa
        $phinxBin = 'vendor/bin/phinx';
        $phinxPath = $rootPath . '/' . $phinxBin;

        if (file_exists($phinxPath))
        {
            return 'php "' . $phinxPath . '"';
        }

        return null;
    }
}
