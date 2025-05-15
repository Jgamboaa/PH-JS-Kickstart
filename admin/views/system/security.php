<?php
// filepath: c:\laragon\www\PH-JS-Kickstart\admin\views\system\security.php
include '../../includes/session.php';

$admin_id = $user['id'];
$roles_ids = explode(',', $user['roles_ids']);

if (!in_array(1, $roles_ids))
{
    include '403.php';
}
else
{
?>
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1>Configuración de Seguridad</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="#home">Inicio</a></li>
                        <li class="breadcrumb-item active">Seguridad</li>
                    </ol>
                </div>
            </div>
        </div>
    </section>

    <section class="content">
        <div class="container-fluid">
            <div class="row">
                <div class="col-md-6">
                    <!-- Configuración de Autenticación de Dos Factores -->
                    <div class="card card-primary">
                        <div class="card-header">
                            <h3 class="card-title">Autenticación de Dos Factores (2FA)</h3>
                        </div>
                        <div class="card-body">
                            <p>La autenticación de dos factores (2FA) proporciona una capa adicional de seguridad al requerir dos formas de verificación antes de permitir el acceso.</p>

                            <div class="form-group">
                                <label>Estado actual:</label>
                                <div>
                                    <span class="badge bg-success">Activado</span>
                                    <small class="text-muted ml-2">El sistema permite a los usuarios configurar 2FA</small>
                                </div>
                            </div>

                            <div class="alert alert-info">
                                <p><strong>Beneficios de la autenticación de dos factores:</strong></p>
                                <ul>
                                    <li>Mayor seguridad para cuentas de usuario</li>
                                    <li>Protección contra el robo de contraseñas</li>
                                    <li>Reducción de riesgos de accesos no autorizados</li>
                                </ul>
                            </div>

                            <p>Cada usuario puede configurar su autenticación de dos factores desde su perfil o utilizando el botón de "Config 2FA" en el menú de usuario.</p>

                            <a href="configurar_2fa.php" class="btn btn-primary">Configurar mi 2FA</a>
                        </div>
                    </div>
                </div>

                <div class="col-md-6">
                    <!-- Estado de Usuarios con 2FA -->
                    <div class="card card-success">
                        <div class="card-header">
                            <h3 class="card-title">Estado 2FA de Usuarios</h3>
                        </div>
                        <div class="card-body">
                            <table id="users_2fa_status" class="table table-bordered table-striped">
                                <thead>
                                    <tr>
                                        <th>Usuario</th>
                                        <th>Nombre</th>
                                        <th>Estado 2FA</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <!-- Se cargará mediante AJAX -->
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row mt-4">
                <div class="col-md-12">
                    <!-- Políticas de Seguridad -->
                    <div class="card card-info">
                        <div class="card-header">
                            <h3 class="card-title">Políticas de Seguridad</h3>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Forzar 2FA para usuarios administradores</label>
                                        <div class="custom-control custom-switch">
                                            <input type="checkbox" class="custom-control-input" id="enforce_admin_2fa">
                                            <label class="custom-control-label" for="enforce_admin_2fa">Activar</label>
                                        </div>
                                        <small class="text-muted">Si se activa, todos los usuarios administradores deberán configurar 2FA obligatoriamente.</small>
                                    </div>

                                    <div class="form-group">
                                        <label>Bloqueo de cuenta después de intentos fallidos</label>
                                        <div class="input-group">
                                            <input type="number" class="form-control" id="login_attempts" value="3" min="1" max="10">
                                            <div class="input-group-append">
                                                <span class="input-group-text">intentos</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Tiempo de sesión</label>
                                        <div class="input-group">
                                            <input type="number" class="form-control" id="session_time" value="30" min="5" max="120">
                                            <div class="input-group-append">
                                                <span class="input-group-text">minutos</span>
                                            </div>
                                        </div>
                                        <small class="text-muted">Tiempo de inactividad antes de cerrar sesión automáticamente.</small>
                                    </div>

                                    <div class="form-group">
                                        <label>Duración del bloqueo</label>
                                        <div class="input-group">
                                            <input type="number" class="form-control" id="lock_duration" value="15" min="1" max="60">
                                            <div class="input-group-append">
                                                <span class="input-group-text">minutos</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <button type="button" id="save_security_settings" class="btn btn-primary">Guardar Configuración</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

<?php
}
?>