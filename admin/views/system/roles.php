<?php
include '../../includes/session.php';
// Incluir componentes para modales y campos de formulario
include '../../components/modal.php';
include '../../components/form_fields.php';

$admin_id = $user['id'];
$roles_ids = explode(',', $user['roles_ids']);

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
                    <a id="add" class="btn btn-sm btn-primary">
                        <i class="fa-solid fa-plus"></i>
                    </a>
                </div>
            </div>
        </div>
        <div class="container-fluid">
            <div class="card">
                <div class="card-body">
                    <table id="roles" class="table table-bordered table-striped table-sm responsive">
                        <thead class="text-center">
                            <th>ID</th>
                            <th>Rol</th>
                            <th>Acciones</th>
                        </thead>
                        <tbody></tbody>
                    </table>
                </div>
            </div>
        </div>
    </section>

    <?php

    // Modal para crear rol
    echo renderModal([
        'id' => 'createRoleModal',
        'title' => 'Añadir Nuevo Rol',
        'body' => '
            <form id="createRoleForm">
                ' . renderFormField([
            'type' => 'text',
            'name' => 'nombre',
            'id' => 'nombre',
            'label' => 'Nombre del Rol',
            'placeholder' => 'Ingrese el nombre del rol',
            'required' => true
        ]) . '
            </form>
        ',
        'footer' => '
            <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
            <button type="button" class="btn btn-primary" id="saveRoleBtn">Guardar</button>
        '
    ]);

    // Modal para editar rol
    echo renderModal([
        'id' => 'editRoleModal',
        'title' => 'Editar Rol',
        'body' => '
            <form id="editRoleForm">
                <input type="hidden" id="edit-id" name="id">
                ' . renderFormField([
            'type' => 'text',
            'name' => 'nombre',
            'id' => 'edit-nombre',
            'label' => 'Nombre del Rol',
            'placeholder' => 'Ingrese el nombre del rol',
            'required' => true
        ]) . '
            </form>
        ',
        'footer' => '
            <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
            <button type="button" class="btn btn-primary" id="updateRoleBtn">Actualizar</button>
        '
    ]);
    ?>
<?php
}
