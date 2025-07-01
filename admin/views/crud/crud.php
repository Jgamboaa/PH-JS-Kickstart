<?php
include '../../includes/session.php';
// Incluir componentes para modales y campos de formulario
include '../../components/modal.php';
include '../../components/form_fields.php';

$admin_id = $user['id'];
$roles_ids = explode(',', $user['roles_ids']);

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
                <table id="table" class="table table-bordered table-striped table-sm responsive">
                    <thead class="text-center">
                        <th>ID</th>
                        <th>Cliente</th>
                        <th>Correo</th>
                        <th>Teléfono</th>
                        <th>Creado</th>
                        <th>Actualizado</th>
                        <th>Acciones</th>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>
        </div>
    </div>
</section>

<?php
// Crear formulario para el modal de administración de clientes
$formContent = '
    <form id="client_form">
        <!-- Campos ocultos para controlar la acción y el ID del cliente -->
        <input type="hidden" id="client_crud" name="crud">
        <input type="hidden" id="client_id" name="id">
        
        <div class="row">
            <div class="col-lg-12">
                ' . renderFormField([
    'type' => 'text',
    'name' => 'name',
    'id' => 'name',
    'label' => 'Nombre completo',
    'required' => true
]) . '
            </div>
        </div>
        
        <div class="row">
            <div class="col-lg-6">
                ' . renderFormField([
    'type' => 'email',
    'name' => 'email',
    'id' => 'email',
    'label' => 'Correo electrónico',
    'required' => true
]) . '
            </div>
            <div class="col-lg-6">
                ' . renderFormField([
    'type' => 'text',
    'name' => 'phone',
    'id' => 'phone',
    'label' => 'Teléfono'
]) . '
            </div>
        </div>
    </form>
';

// Botones para el footer del modal
$footerContent = '
    <button type="button" class="btn btn-sm btn-default" data-dismiss="modal"><i class="fa fa-times"></i> Cerrar</button>
    <button type="submit" class="btn btn-sm btn-primary" form="client_form"><i class="fa fa-save"></i> Guardar</button>
';

// Renderizar el modal principal
echo renderModal([
    'id' => 'client_modal',
    'title' => 'Agregar/Editar Cliente',
    'size' => 'modal-lg',
    'scrollable' => true,
    'body' => $formContent,
    'footer' => $footerContent
]);
?>