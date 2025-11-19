<?php
include '../../includes/session.php';

$admin_id  = $user['id'];
$roles_ids = explode(',', $user['roles_ids']);

// Obtener listado de roles usando PDO para el formulario
global $pdo;
$stmtRoles = $pdo->prepare('SELECT id, nombre FROM roles');
$stmtRoles->execute();
$rolesData = $stmtRoles->fetchAll(PDO::FETCH_OBJ);

if (!in_array(1, $roles_ids))
{
    include '403.php';
}
else
{
?>
    <section class="content">
        <div class="container-fluid content-header">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <!-- Crear usuario -->
                    <a id="addnew" class="btn btn-sm btn-primary">
                        <i class="fa fa-duotone fa-solid fa-plus fa-lg" data-toggle="tooltip" title="Agregar Usuario"></i>
                    </a>

                    <!-- Respaldo local de la BD -->
                    <a class="btn btn-sm btn-warning btn-backup">
                        <i class="fa fa-duotone fa-solid fa-server fa-lg" data-toggle="tooltip" title="Generar respaldo"></i>
                    </a>

                    <!-- Enviar respaldo por correo -->
                    <a class="btn btn-sm btn-info btn-email-backup">
                        <i class="fa fa-duotone fa-solid fa-paper-plane-top fa-lg" data-toggle="tooltip" title="Enviar respaldo por correo"></i>
                    </a>

                    <!-- Datos generales de la empresa -->
                    <a class="btn btn-sm btn-success btn-company-data">
                        <i class="fa fa-duotone fa-solid fa-building fa-lg" data-toggle="tooltip" title="Datos de la empresa"></i>
                    </a>

                    <!-- Branding / apariencia -->
                    <a class="btn btn-sm btn-dark btn-branding">
                        <i class="fa fa-duotone fa-solid fa-image fa-lg" data-toggle="tooltip" title="Branding (logos, íconos)"></i>
                    </a>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-body">
                <table id="admins" class="table table-bordered table-striped table-sm responsive">
                    <thead class="text-center thead-dark">
                        <tr>
                            <th>Foto</th>
                            <th>Nombre</th>
                            <th>Correo</th>
                            <th>Roles</th>
                            <th>MFA Estado</th>
                            <th>MFA Requerido</th>
                            <th>Última conexión</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>
        </div>
    </section>

    <!-- Modal Crear/Editar Usuario -->
    <div class="modal fade" id="admin_modal" tabindex="-1" role="dialog" aria-labelledby="admin_modalLabel" aria-hidden="true" aria-modal="true" data-backdrop="static">
        <div class="modal-dialog modal-lg modal-dialog-scrollable" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="admin_modalLabel">Agregar/Editar Usuario</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <form id="admin_form" enctype="multipart/form-data">
                        <!-- Campos ocultos para controlar la acción y el ID del empleado -->
                        <input type="hidden" id="admin_crud" name="crud">
                        <input type="hidden" id="admin_id" name="id">

                        <!-- Mostrar la foto actual del empleado en edición -->
                        <div class="form-group text-center" id="current_photo">
                            <img src="" width="200px" class="img-circle">
                        </div>

                        <div class="row">
                            <div class="col-lg-6">
                                <div class="form-group">
                                    <label for="usuario">Usuario</label>
                                    <input
                                        type="text"
                                        name="usuario"
                                        id="usuario"
                                        class="form-control"
                                        required>
                                </div>
                            </div>
                            <div class="col-lg-6">
                                <div class="form-group">
                                    <label for="password">Contraseña</label>
                                    <input
                                        type="password"
                                        name="password"
                                        id="password"
                                        class="form-control"
                                        required>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-lg-6">
                                <div class="form-group">
                                    <label for="firstname">Nombre</label>
                                    <input
                                        type="text"
                                        name="firstname"
                                        id="firstname"
                                        class="form-control"
                                        required>
                                </div>
                            </div>
                            <div class="col-lg-6">
                                <div class="form-group">
                                    <label for="lastname">Apellido</label>
                                    <input
                                        type="text"
                                        name="lastname"
                                        id="lastname"
                                        class="form-control"
                                        required>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-lg-6">
                                <div class="form-group">
                                    <label for="photo">Foto</label>
                                    <div class="custom-file">
                                        <input
                                            type="file"
                                            class="custom-file-input"
                                            id="photo"
                                            name="photo">
                                        <label class="custom-file-label" for="photo"></label>
                                    </div>
                                </div>
                            </div>
                            <div class="col-lg-6">
                                <div class="form-group">
                                    <label for="gender">Género</label>
                                    <select
                                        name="gender"
                                        id="gender"
                                        class="form-control"
                                        required>
                                        <option value=""></option>
                                        <option value="0">Masculino</option>
                                        <option value="1">Femenino</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="roles_ids">Roles</label>
                            <select
                                name="roles_ids[]"
                                id="roles_ids"
                                class="form-control"
                                multiple
                                required>
                                <?php foreach ($rolesData as $rol): ?>
                                    <option value="<?php echo $rol->id; ?>">
                                        <?php echo htmlspecialchars($rol->nombre, ENT_QUOTES, 'UTF-8'); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="form-group">
                            <label for="tfa_required">2FA requerido</label>
                            <select
                                name="tfa_required"
                                id="tfa_required"
                                class="form-control">
                                <option value="0">Opcional</option>
                                <option value="1">Obligatorio</option>
                            </select>
                            <small class="form-text text-muted">
                                Si es obligatorio, el usuario deberá configurar 2FA en su primer inicio de sesión
                            </small>
                        </div>

                        <!-- Sección avanzada de 2FA - Solo visible en edición -->
                        <div id="mfa_advanced_section" class="d-none">
                            <hr>
                            <h5>Configuración avanzada de Autenticación de Dos Factores (2FA)</h5>

                            <div class="row mt-3">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Estado actual de 2FA</label>
                                        <div class="mt-2">
                                            <span id="mfa_status_badge" class="badge badge-pill"></span>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Acciones de administración</label>
                                        <div class="row mt-2">
                                            <div class="col-md-6">
                                                <button
                                                    type="button"
                                                    class="btn btn-info btn-sm btn-block"
                                                    id="btn_reset_mfa">
                                                    <i class="fa-duotone fa-solid fa-shield-check"></i> Restablecer
                                                </button>
                                            </div>
                                            <div class="col-md-6">
                                                <button
                                                    type="button"
                                                    class="btn btn-warning btn-sm btn-block"
                                                    id="btn_generate_codes">
                                                    <i class="fa-duotone fa-solid fa-key"></i> Generar códigos
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
                <div class="modal-footer justify-content-between">
                    <button type="button" class="btn btn-sm btn-default" data-dismiss="modal">
                        <i class="fa fa-times"></i> Cerrar
                    </button>
                    <button type="submit" class="btn btn-sm btn-primary" form="admin_form">
                        <i class="fa fa-save"></i> Guardar
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Branding -->
    <div class="modal fade" id="branding_modal" tabindex="-1" role="dialog" aria-labelledby="branding_modalLabel" aria-hidden="true" aria-modal="true" data-backdrop="static">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="branding_modalLabel">Branding de la aplicación</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <form id="branding_form" enctype="multipart/form-data">
                        <div class="row">
                            <div class="col">
                                <div class="row">
                                    <div class="col-lg-6">
                                        <label for="logo">Logo (452×354 px)</label>
                                        <div class="input-group">
                                            <div class="custom-file">
                                                <input
                                                    type="file"
                                                    class="custom-file-input"
                                                    name="logo"
                                                    id="logo"
                                                    accept="image/png">
                                                <label class="custom-file-label" for="logo">Seleccionar</label>
                                            </div>
                                        </div>
                                        <small class="form-text text-muted">Se guardará como <strong>images/logo.png</strong></small>
                                    </div>
                                    <div class="col-lg-6 text-center">
                                        <img src="../images/logo.png" alt="Logo" class="brand-image" width="88.5px">
                                    </div>
                                </div>

                                <hr>

                                <div class="row">
                                    <div class="col-lg-6">
                                        <label for="logo_w">Logo color blanco (452×354 px)</label>
                                        <div class="input-group">
                                            <div class="custom-file">
                                                <input
                                                    type="file"
                                                    class="custom-file-input"
                                                    name="logo2"
                                                    id="logo_w"
                                                    accept="image/png">
                                                <label class="custom-file-label" for="logo_w">Seleccionar</label>
                                            </div>
                                        </div>
                                        <small class="form-text text-muted">Se guardará como <strong>images/logo2.png</strong></small>
                                    </div>
                                    <div class="col-lg-6 text-center">
                                        <div class="bg-primary p-2">
                                            <img src="../images/logo2.png" alt="Logo Blanco" class="brand-image" width="88.5px">
                                        </div>
                                    </div>
                                </div>

                                <hr>

                                <div class="row">
                                    <div class="col-lg-6">
                                        <label for="logo3">Logo sin letras (400×400 px)</label>
                                        <div class="input-group">
                                            <div class="custom-file">
                                                <input
                                                    type="file"
                                                    class="custom-file-input"
                                                    name="logo_circulo"
                                                    id="logo3"
                                                    accept="image/png">
                                                <label class="custom-file-label" for="logo3">Seleccionar</label>
                                            </div>
                                        </div>
                                        <small class="form-text text-muted">Se guardará como <strong>images/logo_circulo.png</strong></small>
                                    </div>
                                    <div class="col-lg-6 text-center">
                                        <div class="bg-primary p-2">
                                            <img src="../images/logo_circulo.png" alt="Logo sin letras" width="80px">
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="col">
                                <div class="row">
                                    <div class="col-lg-6">
                                        <label for="favicon">Favicon (160×160 px)</label>
                                        <div class="input-group">
                                            <div class="custom-file">
                                                <input
                                                    type="file"
                                                    class="custom-file-input"
                                                    name="favicon"
                                                    id="favicon"
                                                    accept="image/png">
                                                <label class="custom-file-label" for="favicon">Seleccionar</label>
                                            </div>
                                        </div>
                                        <small class="form-text text-muted">Se guardará como <strong>images/favicon.png</strong></small>
                                    </div>
                                    <div class="col-lg-6 text-center">
                                        <div class="bg-primary p-2">
                                            <img src="../images/favicon.png" alt="Favicon" width="80px">
                                        </div>
                                    </div>
                                </div>

                                <hr>

                                <div class="row">
                                    <div class="col-lg-6">
                                        <label for="avatar">Avatar (400×400 px)</label>
                                        <div class="input-group">
                                            <div class="custom-file">
                                                <input
                                                    type="file"
                                                    class="custom-file-input"
                                                    name="avatar"
                                                    id="avatar"
                                                    accept="image/png">
                                                <label class="custom-file-label" for="avatar">Seleccionar</label>
                                            </div>
                                        </div>
                                        <small class="form-text text-muted">Se guardará como <strong>images/avatar.png</strong></small>
                                    </div>
                                    <div class="col-lg-6 text-center">
                                        <img src="../images/avatar.png" alt="Avatar" width="80px">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
                <div class="modal-footer justify-content-between">
                    <button type="button" class="btn btn-sm btn-default" data-dismiss="modal">
                        <i class="fa fa-times"></i> Cerrar
                    </button>
                    <button type="submit" class="btn btn-sm btn-primary" form="branding_form">
                        <i class="fa fa-save"></i> Guardar
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Datos de la Empresa -->
    <div class="modal fade" id="company_modal" tabindex="-1" role="dialog" aria-labelledby="company_modalLabel" aria-hidden="true" aria-modal="true" data-backdrop="static">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="company_modalLabel">Datos de la empresa</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <form id="company_form">
                        <div class="form-group">
                            <label for="company_name">Nombre de la empresa</label>
                            <input
                                type="text"
                                class="form-control"
                                id="company_name"
                                name="company_name"
                                required>
                        </div>

                        <div class="form-group">
                            <label for="company_name_short">Nombre corto</label>
                            <input
                                type="text"
                                class="form-control"
                                id="company_name_short"
                                name="company_name_short"
                                required>
                        </div>

                        <div class="form-group">
                            <label for="app_name">Nombre de la aplicación</label>
                            <input
                                type="text"
                                class="form-control"
                                id="app_name"
                                name="app_name"
                                required>
                        </div>

                        <div class="form-group">
                            <label for="app_version">Versión de la aplicación</label>
                            <input
                                type="text"
                                class="form-control"
                                id="app_version"
                                name="app_version"
                                required>
                        </div>

                        <div class="form-group">
                            <label for="developer_name">Desarrollador</label>
                            <input
                                type="text"
                                class="form-control"
                                id="developer_name"
                                name="developer_name"
                                required>
                        </div>
                    </form>
                </div>
                <div class="modal-footer justify-content-between">
                    <button type="button" class="btn btn-sm btn-default" data-dismiss="modal">
                        <i class="fa fa-times"></i> Cerrar
                    </button>
                    <button type="submit" class="btn btn-sm btn-primary" form="company_form">
                        <i class="fa fa-save"></i> Guardar
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Códigos de respaldo -->
    <div class="modal fade" id="backup_codes_modal" tabindex="-1" role="dialog" aria-labelledby="backup_codes_modalLabel" aria-hidden="true" aria-modal="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="backup_codes_modalLabel">Códigos de respaldo</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <p>Guarde estos códigos de respaldo en un lugar seguro. Cada código se puede usar una sola vez:</p>
                    <div class="alert alert-warning">
                        <ul id="backup_codes_list" class="mb-0"></ul>
                    </div>
                </div>
                <div class="modal-footer justify-content-between">
                    <button type="button" class="btn btn-sm btn-default" data-dismiss="modal">Cerrar</button>
                    <button type="button" class="btn btn-sm btn-primary" id="copy_backup_codes">Copiar códigos</button>
                </div>
            </div>
        </div>
    </div>
<?php
}
