<?php
include '../../includes/session.php';
// Incluir componentes reutilizables
include '../../components/form_fields.php';
include '../../components/modal.php';

$admin_id = $user['id'];
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
                    <a id="addnew" class="btn btn-sm btn-primary"><i class="fa fa-duotone fa-solid fa-plus fa-lg"></i></a>
                    <a class="btn btn-sm btn-warning btn-backup"><i class="fa fa-duotone fa-solid fa-server fa-lg"></i></a>
                    <a class="btn btn-sm btn-info btn-email-backup"><i class="fa fa-duotone fa-solid fa-paper-plane-top fa-lg"></i></a>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-body">
                <table id="admins" class="table table-bordered table-striped table-sm responsive">
                    <thead class='text-center'>
                        <th>Foto</th>
                        <th>Nombre</th>
                        <th>Correo</th>
                        <th>Roles</th>
                        <th>MFA Estado</th>
                        <th>MFA Requerido</th>
                        <th>Última conexión</th>
                        <th>Acciones</th>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>
        </div>
    </section>

    <?php
    // Crear formulario para el modal de administración de usuarios
    $formContent = '
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
                    ' . renderFormField([
        'type' => 'text',
        'name' => 'usuario',
        'id' => 'usuario',
        'label' => 'Usuario',
        'required' => true
    ]) . '
                </div>
                <div class="col-lg-6">
                    ' . renderFormField([
        'type' => 'password',
        'name' => 'password',
        'id' => 'password',
        'label' => 'Contraseña',
        'required' => true
    ]) . '
                </div>
            </div>
            
            <div class="row">
                <div class="col-lg-6">
                    ' . renderFormField([
        'type' => 'text',
        'name' => 'firstname',
        'id' => 'firstname',
        'label' => 'Nombre',
        'required' => true
    ]) . '
                </div>
                <div class="col-lg-6">
                    ' . renderFormField([
        'type' => 'text',
        'name' => 'lastname',
        'id' => 'lastname',
        'label' => 'Apellido',
        'required' => true
    ]) . '
                </div>
            </div>
            
            <div class="row">
                <div class="col-lg-6">
                    ' . renderFormField([
        'type' => 'file',
        'name' => 'photo',
        'id' => 'photo',
        'label' => 'Foto',
        'placeholder' => ''
    ]) . '
                </div>
                <div class="col-lg-6">
                    ' . renderFormField([
        'type' => 'select',
        'name' => 'gender',
        'id' => 'gender',
        'label' => 'Género',
        'required' => true,
        'options' => [
            '' => '',
            '0' => 'Masculino',
            '1' => 'Femenino'
        ]
    ]) . '
                </div>
            </div>
            
            ' . renderFormField([
        'type' => 'select',
        'name' => 'roles_ids',
        'id' => 'roles_ids',
        'label' => 'Roles',
        'required' => true,
        'multiple' => true,
        'data_source' => $rolesData,
        'value_field' => 'id',
        'text_field' => 'nombre'
    ]) . '
            
            ' . renderFormField([
        'type' => 'select',
        'name' => 'tfa_required',
        'id' => 'tfa_required',
        'label' => '2FA requerido',
        'options' => [
            '0' => 'Opcional',
            '1' => 'Obligatorio'
        ],
        'help_text' => 'Si es obligatorio, el usuario deberá configurar 2FA en su primer inicio de sesión'
    ]) . '
            
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
                                    <button type="button" class="btn btn-info btn-sm btn-block" id="btn_reset_mfa">
                                        <i class="fa-duotone fa-solid fa-shield-check"></i> Restablecer
                                    </button>
                                </div>
                                <div class="col-md-6">
                                    <button type="button" class="btn btn-warning btn-sm btn-block" id="btn_generate_codes">
                                        <i class="fa-duotone fa-solid fa-key"></i> Generar códigos
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    ';

    // Botones para el footer del modal
    $footerContent = '
        <button type="button" class="btn btn-sm btn-default" data-dismiss="modal"><i class="fa fa-times"></i> Cerrar</button>
        <button type="submit" class="btn btn-sm btn-primary" form="admin_form"><i class="fa fa-save"></i> Guardar</button>
    ';

    // Renderizar el modal principal
    echo renderModal([
        'id' => 'admin_modal',
        'title' => 'Agregar/Editar Usuario',
        'size' => 'modal-lg',
        'scrollable' => true,
        'body' => $formContent,
        'footer' => $footerContent
    ]);

    // Contenido para el modal de códigos de respaldo
    $backupCodesBody = '
        <p>Guarde estos códigos de respaldo en un lugar seguro. Cada código se puede usar una sola vez:</p>
        <div class="alert alert-warning">
            <ul id="backup_codes_list" class="mb-0"></ul>
        </div>
    ';

    $backupCodesFooter = '
        <button type="button" class="btn btn-sm btn-default" data-dismiss="modal">Cerrar</button>
        <button type="button" class="btn btn-sm btn-primary" id="copy_backup_codes">Copiar códigos</button>
    ';

    // Renderizar el modal de códigos de respaldo
    echo renderModal([
        'id' => 'backup_codes_modal',
        'title' => 'Códigos de respaldo',
        'scrollable' => true,
        'body' => $backupCodesBody,
        'footer' => $backupCodesFooter
    ]);
    ?>
<?php
}
