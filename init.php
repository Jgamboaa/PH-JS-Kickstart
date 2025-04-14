<?php
// Incluir la lógica de instalación
require_once __DIR__ . '/installer/setup.php';

// Inicializar la clase Setup
$setup = new Setup();

// Procesar el formulario si se ha enviado
$setup->processSetupForm();

// Obtener las variables de estado
$installed = $setup->installed;
$error = $setup->error;
$message = $setup->message;
$dbImported = $setup->dbImported;
$canInstallComposer = $setup->canExecuteSystemCommands();
$composerOutput = $setup->composerOutput;
$composerInstalled = $setup->composerInstalled;
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inicialización del Sistema</title>
    <link rel="icon" href="images/favicon.png">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <!-- Incluir bs-stepper CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bs-stepper/dist/css/bs-stepper.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <style>
        body {
            background-color: #f5f5f5;
        }

        .setup-container {
            max-width: 700px;
            margin: 50px auto;
        }

        .card {
            border: none;
            box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15);
        }

        .card-header {
            background-color: #212529;
            color: white;
            text-align: center;
            padding: 20px;
        }

        .form-section {
            margin-bottom: 30px;
            padding-bottom: 20px;
            border-bottom: 1px solid #eee;
        }

        .form-section:last-child {
            border-bottom: none;
        }

        .form-section h4 {
            margin-bottom: 20px;
            color: #333;
        }

        .alert-info-custom {
            background-color: #e8f4fd;
            border-color: #b8daff;
            color: #0c5460;
        }

        .composer-output {
            background-color: #f8f9fa;
            border: 1px solid #dee2e6;
            border-radius: 4px;
            padding: 10px;
            font-family: monospace;
            max-height: 200px;
            overflow-y: auto;
            font-size: 0.85rem;
            margin-top: 10px;
        }

        /* Nuevo estilos para el overlay de carga */
        #loadingOverlay {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(255, 255, 255, 0.8);
            z-index: 9999;
            align-items: center;
            justify-content: center;
        }
    </style>
</head>

<body>
    <div class="setup-container">
        <div class="card">
            <div class="card-header">
                <h2><i class="bi bi-gear-fill me-2"></i> Inicialización del Sistema</h2>
            </div>
            <div class="card-body p-4">

                <?php if ($error): ?>
                    <div class="alert alert-danger" role="alert">
                        <i class="bi bi-exclamation-triangle-fill me-2"></i> <?php echo htmlspecialchars($error); ?>
                    </div>
                <?php endif; ?>

                <?php if ($message): ?>
                    <div class="alert alert-success" role="alert">
                        <i class="bi bi-check-circle-fill me-2"></i> <?php echo htmlspecialchars($message); ?>
                    </div>

                    <?php if ($composerOutput): ?>
                        <div class="alert alert-info" role="alert">
                            <i class="bi bi-info-circle-fill me-2"></i> Resultado de la instalación de dependencias:
                            <div class="composer-output"><?php echo nl2br(htmlspecialchars($composerOutput)); ?></div>
                        </div>
                    <?php elseif ($installed && !$composerInstalled && $canInstallComposer): ?>
                        <div class="alert alert-warning" role="alert">
                            <i class="bi bi-exclamation-triangle-fill me-2"></i> No se pudieron instalar las dependencias de Composer automáticamente.
                            <p>Por favor, ejecuta manualmente el siguiente comando en la terminal:</p>
                            <code>composer install</code>
                        </div>
                    <?php endif; ?>
                <?php endif; ?>

                <?php if ($installed): ?>
                    <div class="text-center mt-4">
                        <a href="index.php" class="btn btn-primary btn-lg">
                            <i class="bi bi-box-arrow-in-right me-2"></i> Ir al Sistema
                        </a>
                    </div>
                <?php else: ?>
                    <!-- Información sobre la opción de instalación CLI -->
                    <div class="alert alert-info mb-4" role="alert">
                        <i class="bi bi-terminal-fill me-2"></i> <strong>¿Prefieres usar la línea de comandos?</strong>
                        <p class="mb-0 mt-2">También puedes instalar el sistema mediante la línea de comandos ejecutando:</p>
                        <code>php installer/cli-setup.php</code>
                    </div>

                    <?php if (!$canInstallComposer): ?>
                        <div class="alert alert-warning mb-4" role="alert">
                            <i class="bi bi-exclamation-triangle-fill me-2"></i> <strong>Nota sobre Composer</strong>
                            <p class="mb-0 mt-2">No se detectaron permisos para instalar dependencias automáticamente. Después de la instalación, deberás ejecutar manualmente:</p>
                            <code>composer install</code>
                        </div>
                    <?php else: ?>
                        <div class="alert alert-info mb-4" role="alert">
                            <i class="bi bi-box-fill me-2"></i> <strong>Instalación de Dependencias</strong>
                            <p class="mb-0 mt-2">El sistema intentará instalar automáticamente las dependencias de Composer.</p>
                        </div>
                    <?php endif; ?>

                    <!-- Estructura de Bootstrap 5 Stepper -->
                    <div id="stepper" class="bs-stepper">
                        <div class="bs-stepper-header" role="tablist">
                            <div class="step" data-target="#step-1">
                                <button type="button" class="step-trigger" role="tab" id="step-1-trigger" aria-controls="step-1">
                                    <span class="bs-stepper-circle">1</span>
                                    <span class="bs-stepper-label">Base de Datos</span>
                                </button>
                            </div>
                            <div class="line"></div>
                            <div class="step" data-target="#step-2">
                                <button type="button" class="step-trigger" role="tab" id="step-2-trigger" aria-controls="step-2">
                                    <span class="bs-stepper-circle">2</span>
                                    <span class="bs-stepper-label">Correo</span>
                                </button>
                            </div>
                            <div class="line"></div>
                            <!-- Nuevo paso para usuario administrador -->
                            <div class="step" data-target="#step-3">
                                <button type="button" class="step-trigger" role="tab" id="step-3-trigger" aria-controls="step-3">
                                    <span class="bs-stepper-circle">3</span>
                                    <span class="bs-stepper-label">Administrador</span>
                                </button>
                            </div>
                            <div class="line"></div>
                            <div class="step" data-target="#step-4">
                                <button type="button" class="step-trigger" role="tab" id="step-4-trigger" aria-controls="step-4">
                                    <span class="bs-stepper-circle">4</span>
                                    <span class="bs-stepper-label">Finalizar</span>
                                </button>
                            </div>
                        </div>
                        <div class="bs-stepper-content">
                            <!-- Se añadió novalidate al formulario para evitar errores de validación en campos no visibles -->
                            <form id="setupForm" method="POST" action="" novalidate>
                                <div id="step-1" class="content" role="tabpanel" aria-labelledby="step-1-trigger">
                                    <div class="alert alert-info-custom mb-4" role="alert">
                                        <h5><i class="bi bi-info-circle-fill me-2"></i> Información Importante</h5>
                                        <p>Este asistente configurará automáticamente su sistema:</p>
                                        <ul>
                                            <li>Creará el archivo <code>.env</code> con sus configuraciones</li>
                                            <li>Verificará la conexión a la base de datos</li>
                                            <li>Importará automáticamente la estructura de la base de datos desde <code>config/core.sql</code></li>
                                        </ul>
                                    </div>
                                    <div class="form-section">
                                        <h4><i class="bi bi-database me-2"></i> Configuración de Base de Datos</h4>
                                        <div class="mb-3">
                                            <label for="db_host" class="form-label">Servidor de Base de Datos</label>
                                            <input type="text" class="form-control" id="db_host" name="db_host" value="localhost" required>
                                        </div>
                                        <div class="mb-3">
                                            <label for="db_user" class="form-label">Usuario</label>
                                            <input type="text" class="form-control" id="db_user" name="db_user" value="root" required>
                                        </div>
                                        <div class="mb-3">
                                            <label for="db_pass" class="form-label">Contraseña</label>
                                            <input type="password" class="form-control" id="db_pass" name="db_pass">
                                        </div>
                                        <div class="mb-3">
                                            <label for="db_name" class="form-label">Nombre de la Base de Datos</label>
                                            <input type="text" class="form-control" id="db_name" name="db_name" value="core" required>
                                            <div class="form-text text-muted">
                                                Si la base de datos no existe, se creará automáticamente y se importará la estructura desde core.sql
                                            </div>
                                        </div>
                                    </div>
                                    <button class="btn btn-primary" type="button" onclick="stepper.next()">Siguiente</button>
                                </div>

                                <div id="step-2" class="content" role="tabpanel" aria-labelledby="step-2-trigger">
                                    <div class="form-section">
                                        <h4><i class="bi bi-envelope me-2"></i> Configuración de Correo</h4>
                                        <div class="mb-3">
                                            <label for="mail_host" class="form-label">Servidor SMTP</label>
                                            <input type="text" class="form-control" id="mail_host" name="mail_host" placeholder="smtp.example.com">
                                        </div>
                                        <div class="mb-3">
                                            <label for="mail_username" class="form-label">Correo Electrónico</label>
                                            <input type="email" class="form-control" id="mail_username" name="mail_username" placeholder="tu@email.com">
                                        </div>
                                        <div class="mb-3">
                                            <label for="mail_name" class="form-label">Nombre del Remitente</label>
                                            <input type="text" class="form-control" id="mail_name" name="mail_name" placeholder="Sistema Core">
                                        </div>
                                        <div class="mb-3">
                                            <label for="mail_password" class="form-label">Contraseña</label>
                                            <input type="password" class="form-control" id="mail_password" name="mail_password">
                                        </div>
                                        <div class="mb-3">
                                            <label for="mail_port" class="form-label">Puerto</label>
                                            <input type="number" class="form-control" id="mail_port" name="mail_port">
                                        </div>
                                        <div class="mb-3">
                                            <label for="mail_encryption" class="form-label">Método de Encriptación (tls/smtp)</label>
                                            <select class="form-select" id="mail_encryption" name="mail_encryption" required>
                                                <option value="tls">TLS</option>
                                                <option value="smtp">SMTP</option>
                                            </select>
                                        </div>
                                        <div class="mb-3">
                                            <label for="mail_support" class="form-label">Correo de Soporte</label>
                                            <input type="email" class="form-control" id="mail_support" name="mail_support" placeholder="soporte@example.com" required>
                                        </div>
                                    </div>
                                    <button class="btn btn-secondary" type="button" onclick="stepper.previous()">Anterior</button>
                                    <button class="btn btn-primary" type="button" onclick="stepper.next()">Siguiente</button>
                                </div>

                                <div id="step-3" class="content" role="tabpanel" aria-labelledby="step-3-trigger">
                                    <div class="form-section">
                                        <h4><i class="bi bi-person-fill-lock me-2"></i> Configuración de Usuario Administrador</h4>
                                        <div class="alert alert-info-custom mb-3">
                                            <p>Este será el usuario principal con acceso total al sistema.</p>
                                        </div>
                                        <div class="mb-3">
                                            <label for="admin_email" class="form-label">Correo Electrónico</label>
                                            <input type="email" class="form-control" id="admin_email" name="admin_email" value="admin@admin.com" placeholder="admin@admin.com" required>
                                        </div>
                                        <div class="row">
                                            <div class="col-md-6 mb-3">
                                                <label for="admin_firstname" class="form-label">Nombre</label>
                                                <input type="text" class="form-control" id="admin_firstname" name="admin_firstname" value="Usuario" placeholder="Usuario" required>
                                            </div>
                                            <div class="col-md-6 mb-3">
                                                <label for="admin_lastname" class="form-label">Apellido</label>
                                                <input type="text" class="form-control" id="admin_lastname" name="admin_lastname" value="Administrador" placeholder="Administrador" required>
                                            </div>
                                        </div>
                                        <div class="mb-3">
                                            <label for="admin_password" class="form-label">Contraseña</label>
                                            <input type="password" class="form-control" id="admin_password" name="admin_password" required>
                                            <div class="form-text text-muted">
                                                Por seguridad, cambia esta contraseña después del primer inicio de sesión.
                                            </div>
                                        </div>
                                        <div class="mb-3">
                                            <label for="admin_gender" class="form-label">Género</label>
                                            <select class="form-select" id="admin_gender" name="admin_gender">
                                                <option value="0">Masculino</option>
                                                <option value="1">Femenino</option>
                                                <option value="2">Otro</option>
                                            </select>
                                        </div>
                                    </div>
                                    <button class="btn btn-secondary" type="button" onclick="stepper.previous()">Anterior</button>
                                    <button class="btn btn-primary" type="button" onclick="stepper.next()">Siguiente</button>
                                </div>

                                <div id="step-4" class="content" role="tabpanel" aria-labelledby="step-4-trigger">
                                    <!-- Resumen de datos ingresados -->
                                    <div class="summary-section mb-4">
                                        <h4>Revisa tus datos</h4>
                                        <ul>
                                            <li><strong>Servidor DB:</strong> <span id="summary_db_host"></span></li>
                                            <li><strong>Usuario DB:</strong> <span id="summary_db_user"></span></li>
                                            <li><strong>Nombre DB:</strong> <span id="summary_db_name"></span></li>
                                            <li><strong>Servidor SMTP:</strong> <span id="summary_mail_host"></span></li>
                                            <li><strong>Correo:</strong> <span id="summary_mail_username"></span></li>
                                            <li><strong>Nombre Remitente:</strong> <span id="summary_mail_name"></span></li>
                                            <li><strong>Puerto SMTP:</strong> <span id="summary_mail_port"></span></li>
                                        </ul>
                                        <hr>
                                        <h5>Usuario Administrador</h5>
                                        <ul>
                                            <li><strong>Correo Admin:</strong> <span id="summary_admin_email"></span></li>
                                            <li><strong>Nombre Admin:</strong> <span id="summary_admin_name"></span></li>
                                        </ul>
                                    </div>
                                    <div class="text-center mb-4">
                                        <h4>Revisa y Finaliza</h4>
                                        <p>Verifica que todos los datos sean correctos antes de inicializar el sistema.</p>
                                    </div>
                                    <button class="btn btn-secondary" type="button" onclick="stepper.previous()">Anterior</button>
                                    <button class="btn btn-success" type="submit" name="setup">Inicializar Sistema</button>
                                </div>
                            </form>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
        <div class="text-center mt-3 text-muted">
            <small>© <?php echo date('Y'); ?> - Core</small>
        </div>
    </div>

    <!-- Overlay de carga que se muestra al enviar el formulario -->
    <div id="loadingOverlay">
        <div class="spinner-border text-primary" role="status">
            <span class="visually-hidden">Cargando...</span>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <!-- Incluir bs-stepper JS e inicialización -->
    <script src="https://cdn.jsdelivr.net/npm/bs-stepper/dist/js/bs-stepper.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const stepperElement = document.querySelector('#stepper');
            if (stepperElement) {
                window.stepper = new Stepper(stepperElement);
                stepperElement.addEventListener('shown.bs-stepper', function(event) {
                    if (event.detail.indexStep === 3) {
                        // Función para actualizar los datos de resumen
                        document.getElementById('summary_db_host').textContent = document.getElementById('db_host').value;
                        document.getElementById('summary_db_user').textContent = document.getElementById('db_user').value;
                        document.getElementById('summary_db_name').textContent = document.getElementById('db_name').value;
                        document.getElementById('summary_mail_host').textContent = document.getElementById('mail_host').value;
                        document.getElementById('summary_mail_username').textContent = document.getElementById('mail_username').value;
                        document.getElementById('summary_mail_name').textContent = document.getElementById('mail_name').value;
                        document.getElementById('summary_mail_port').textContent = document.getElementById('mail_port').value;

                        // Actualizar datos del administrador
                        document.getElementById('summary_admin_email').textContent = document.getElementById('admin_email').value;
                        document.getElementById('summary_admin_name').textContent =
                            document.getElementById('admin_firstname').value + ' ' +
                            document.getElementById('admin_lastname').value;
                    }
                });
            } else {
                console.error('Errores: Elemento #stepper no encontrado.');
            }
            // Agregar event listener que muestra el overlay al enviar el formulario
            const setupForm = document.getElementById('setupForm');
            if (setupForm) {
                setupForm.addEventListener('submit', function() {
                    document.getElementById('loadingOverlay').style.display = 'flex';
                });
            }
        });
    </script>
</body>

</html>