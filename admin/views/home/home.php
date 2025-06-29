<?php
include '../../includes/session.php';
?>
<section class="content">
    <div class="container-fluid content-header">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h3>Bienvenid@ <strong><?php echo $user['user_firstname']; ?></strong></h3>
            </div>
        </div>

        <!-- Tarjeta de bienvenida -->
        <div class="row">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">
                            <i class="fa fa-solid fa-duotone fa-lg fa-info-circle mr-2"></i>
                            Información del sistema
                        </h3>
                    </div>
                    <div class="card-body">
                        <p>Este es un sistema base desarrollado en PHP Puro con interface AdminLTE y Bootstrap 4, usalo como punto de partida para tus desarrollos personalizados.</p>
                        <hr>
                        <h5><strong>Características principales:</strong></h5>
                        <div class="row">
                            <div class="col-md-6">
                                <ul>
                                    <li><strong>Autenticación segura:</strong> Sistema de login con protección CSRF y seguridad avanzada</li>
                                    <li><strong>Autenticación de dos factores (2FA):</strong> Implementada con códigos TOTP y códigos de respaldo</li>
                                    <li><strong>Panel de administración:</strong> Interfaz moderna basada en AdminLTE y Bootstrap 4</li>
                                    <li><strong>Gestión de variables de entorno:</strong> Interfaz para modificar configuraciones sin editar archivos</li>
                                </ul>
                            </div>
                            <div class="col-md-6">
                                <ul>
                                    <li><strong>Respaldos de base de datos:</strong> Funciones para exportar y enviar respaldos por correo</li>
                                    <li><strong>Migraciones de base de datos:</strong> Sistema para gestionar cambios en la estructura de la BD</li>
                                    <li><strong>Correos transaccionales:</strong> Configuración SMTP para envío de notificaciones</li>
                                    <li><strong>ORM integrado:</strong> RedBeanPHP para mapeo objeto-relacional y manipulación de datos</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Dependencias y tecnologías -->
        <div class="row">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">
                            <i class="fa fa-solid fa-duotone fa-lg fa-plug mr-2"></i>
                            Dependencias del sistema
                        </h3>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <ul class="list-group">
                                    <li class="list-group-item d-flex justify-content-between align-items-center">
                                        PHPMailer
                                        <span class="badge badge-primary badge-pill">v6.10.0</span>
                                    </li>
                                    <li class="list-group-item d-flex justify-content-between align-items-center">
                                        DOMPDF
                                        <span class="badge badge-primary badge-pill">v3.1.0</span>
                                    </li>
                                    <li class="list-group-item d-flex justify-content-between align-items-center">
                                        PHPSpreadsheet
                                        <span class="badge badge-primary badge-pill">v4.2.0</span>
                                    </li>
                                </ul>
                            </div>
                            <div class="col-md-6">
                                <ul class="list-group">
                                    <li class="list-group-item d-flex justify-content-between align-items-center">
                                        TCPDF
                                        <span class="badge badge-primary badge-pill">v6.9.4</span>
                                    </li>
                                    <li class="list-group-item d-flex justify-content-between align-items-center">
                                        OTPHP
                                        <span class="badge badge-primary badge-pill">v11.3.0</span>
                                    </li>
                                    <li class="list-group-item d-flex justify-content-between align-items-center">
                                        DB-Dumper
                                        <span class="badge badge-primary badge-pill">v3.8.0</span>
                                    </li>
                                </ul>
                            </div>
                        </div>
                        <div class="row mt-3">
                            <div class="col-md-12">
                                <ul class="list-group">
                                    <li class="list-group-item d-flex justify-content-between align-items-center">
                                        RedBeanPHP
                                        <span class="badge badge-primary badge-pill">v5.7</span>
                                    </li>
                                    <li class="list-group-item d-flex justify-content-between align-items-center">
                                        Phinx
                                        <span class="badge badge-primary badge-pill">v0.16.9</span>
                                    </li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">
                            <i class="fa fa-solid fa-duotone fa-lg fa-laptop-code mr-2"></i>
                            Tecnologías utilizadas
                        </h3>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="info-box">
                                    <span class="info-box-icon bg-info"><i class="fab fa-html5"></i></span>
                                    <div class="info-box-content">
                                        <span class="info-box-text">Frontend</span>
                                        <span class="info-box-number">HTML, CSS, JS</span>
                                        <span class="text-muted">AdminLTE y Bootstrap 4</span>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="info-box">
                                    <span class="info-box-icon bg-success"><i class="fab fa-php"></i></span>
                                    <div class="info-box-content">
                                        <span class="info-box-text">Backend</span>
                                        <span class="info-box-number">PHP Puro</span>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="info-box">
                                    <span class="info-box-icon bg-warning"><i class="fa fa-solid fa-duotone fa-lg fa-database"></i></span>
                                    <div class="info-box-content">
                                        <span class="info-box-text">Base de datos</span>
                                        <span class="info-box-number">MariaDB</span>
                                        <span class="text-muted">10.11.11 o superior</span>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="info-box">
                                    <span class="info-box-icon bg-danger"><i class="fa fa-solid fa-duotone fa-lg fa-shield-alt"></i></span>
                                    <div class="info-box-content">
                                        <span class="info-box-text">Seguridad</span>
                                        <span class="info-box-number">CSRF Protection</span>
                                        <span class="text-muted">2FA Authentication</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Requisitos del sistema -->
        <div class="row">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">
                            <i class="fa fa-solid fa-duotone fa-lg fa-server mr-2"></i>
                            Requisitos del servidor
                        </h3>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-4">
                                <ul class="list-group">
                                    <li class="list-group-item">PHP 8.3 o superior</li>
                                    <li class="list-group-item">MariaDB 10.11.11 o superior</li>
                                    <li class="list-group-item">Extensión PDO de PHP</li>
                                </ul>
                            </div>
                            <div class="col-md-4">
                                <ul class="list-group">
                                    <li class="list-group-item">Extensión mbstring de PHP</li>
                                    <li class="list-group-item">Extensión zip de PHP</li>
                                    <li class="list-group-item">GD Library</li>
                                </ul>
                            </div>
                            <div class="col-md-4">
                                <ul class="list-group">
                                    <li class="list-group-item">Extensión curl para TCPDF</li>
                                    <li class="list-group-item">Extensión dom</li>
                                    <li class="list-group-item">Extensiones xml, xmlreader, xmlwriter</li>
                                    <li class="list-group-item">Extensión fileinfo</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- ORM - RedBeanPHP -->
        <div class="row">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">
                            <i class="fa fa-solid fa-duotone fa-lg fa-database mr-2"></i>
                            ORM - RedBeanPHP
                        </h3>
                    </div>
                    <div class="card-body">
                        <p>El sistema utiliza <a href="https://redbeanphp.com/" target="_blank">RedBeanPHP</a> como ORM (Object-Relational Mapping) para la interacción con la base de datos. RedBeanPHP es un ORM de configuración cero que facilita el trabajo con bases de datos relacionales.</p>
                        <div class="row">
                            <div class="col-md-6">
                                <h5><strong>Características principales:</strong></h5>
                                <ul>
                                    <li><strong>Configuración cero:</strong> No requiere archivos de configuración complejos</li>
                                    <li><strong>Mapeo automático:</strong> Crea automáticamente tablas y columnas según sea necesario</li>
                                    <li><strong>Sintaxis fluida:</strong> API intuitiva y fácil de usar</li>
                                    <li><strong>Soporte para relaciones:</strong> Manejo sencillo de relaciones entre tablas</li>
                                </ul>
                            </div>
                            <div class="col-md-6">
                                <h5><strong>Ejemplo de uso básico:</strong></h5>
                                <pre><code class="language-php">// Crear y guardar un registro
$usuario = R::dispense('usuario');
$usuario->nombre = 'Juan Pérez';
$usuario->email = 'juan@ejemplo.com';
$id = R::store($usuario);

// Buscar registros
$usuario = R::load('usuario', $id);

// Actualizar registros
$usuario->nombre = 'Juan C. Pérez';
R::store($usuario);

// Eliminar registros
R::trash($usuario);</code></pre>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Migraciones de Base de Datos -->
        <div class="row">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">
                            <i class="fa fa-solid fa-duotone fa-lg fa-code-branch mr-2"></i>
                            Migraciones de Base de Datos
                        </h3>
                    </div>
                    <div class="card-body">
                        <p>El sistema utiliza <a href="https://phinx.org/" target="_blank">Phinx</a> como herramienta de migración para gestionar la estructura de base de datos y datos iniciales. Esto permite una instalación más robusta y la posibilidad de actualizar la estructura de la base de datos de manera controlada.</p>
                        <div class="row">
                            <div class="col-md-6">
                                <h5><strong>Estructura de directorios:</strong></h5>
                                <ul>
                                    <li><strong>/db/migrations/</strong> - Contiene los archivos de migración para crear y modificar tablas</li>
                                    <li><strong>/db/seeds/</strong> - Contiene los archivos de semillas para poblar las tablas con datos iniciales</li>
                                </ul>
                            </div>
                            <div class="col-md-6">
                                <h5><strong>Comandos básicos:</strong></h5>
                                <pre><code class="language-bash"># Ejecutar todas las migraciones pendientes
vendor/bin/phinx migrate

# Ejecutar semillas (datos iniciales)
vendor/bin/phinx seed:run

# Crear una nueva migración
vendor/bin/phinx create NombreDeMigracion</code></pre>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Configuración de variables de entorno -->
        <div class="row">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">
                            <i class="fa fa-solid fa-duotone fa-lg fa-cogs mr-2"></i>
                            Configuración de variables de entorno
                        </h3>
                    </div>
                    <div class="card-body">
                        <p>El sistema utiliza un archivo .env para gestionar la configuración. Las principales variables son:</p>
                        <div class="row">
                            <div class="col-md-6">
                                <ul class="list-group">
                                    <li class="list-group-item"><strong>DB_HOST, DB_USER, DB_PASS, DB_NAME:</strong> Configuración de la base de datos</li>
                                    <li class="list-group-item"><strong>MAIL_HOST, MAIL_PORT, MAIL_USERNAME, MAIL_PASSWORD:</strong> Configuración del servidor de correo</li>
                                    <li class="list-group-item"><strong>MAIL_ENCRYPTION:</strong> Tipo de cifrado para el correo (tls, ssl)</li>
                                </ul>
                            </div>
                            <div class="col-md-6">
                                <ul class="list-group">
                                    <li class="list-group-item"><strong>MAIL_SUPPORT:</strong> Dirección de correo para soporte técnico</li>
                                    <li class="list-group-item"><strong>APP_NAME, APP_TIMEZONE:</strong> Configuración general de la aplicación</li>
                                    <li class="list-group-item">Estas variables pueden ser modificadas desde <strong>Sistema > Variables de Entorno</strong>.</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Enlaces útiles -->
        <div class="row">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">
                            <i class="fa fa-solid fa-duotone fa-lg fa-link mr-2"></i>
                            Enlaces útiles
                        </h3>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-3">
                                <a href="https://github.com/Jgamboaa/PH-JS-Kickstart" class="btn btn-block btn-outline-primary" target="_blank">
                                    <i class="fab fa-github mr-2"></i> Repositorio en GitHub
                                </a>
                            </div>
                            <div class="col-md-3">
                                <a href="https://deepwiki.com/Jgamboaa/PH-JS-Kickstart" class="btn btn-block btn-outline-info" target="_blank">
                                    <i class="fa fa-solid fa-duotone fa-lg fa-book mr-2"></i> Documentación (DeepWiki)
                                </a>
                            </div>
                            <div class="col-md-3">
                                <button class="btn btn-block btn-outline-success" onclick="window.location.href='../migrations/'">
                                    <i class="fa fa-solid fa-duotone fa-lg fa-database mr-2"></i> Gestionar Migraciones
                                </button>
                            </div>
                            <div class="col-md-3">
                                <button class="btn btn-block btn-outline-warning" onclick="window.location.href='../settings/'">
                                    <i class="fa fa-solid fa-duotone fa-lg fa-cogs mr-2"></i> Variables de Entorno
                                </button>
                            </div>
                        </div>
                        <div class="row mt-3">
                            <div class="col-md-4">
                                <a href="https://redbeanphp.com/index.php?p=/crud" class="btn btn-block btn-outline-danger" target="_blank">
                                    <i class="fa fa-solid fa-duotone fa-lg fa-database mr-2"></i> Documentación RedBeanPHP
                                </a>
                            </div>
                            <div class="col-md-4">
                                <a href="https://book.cakephp.org/phinx/0/en/index.html" class="btn btn-block btn-outline-secondary" target="_blank">
                                    <i class="fa fa-solid fa-duotone fa-lg fa-code-branch mr-2"></i> Documentación Phinx
                                </a>
                            </div>
                            <div class="col-md-4">
                                <a href="../users/profile.php" class="btn btn-block btn-outline-dark">
                                    <i class="fa fa-solid fa-duotone fa-lg fa-user-cog mr-2"></i> Mi Perfil
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>