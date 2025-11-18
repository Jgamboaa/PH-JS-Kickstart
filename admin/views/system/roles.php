<?php
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

    <section class="content">
        <div class="container-fluid content-header">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <a id="add" class="btn btn-sm btn-primary"><i class="fa fa-duotone fa-solid fa-plus fa-lg" data-toggle="tooltip" title="Agregar Rol"></i></a>
                </div>
            </div>
        </div>
        <div class="container-fluid">
            <div class="card">
                <div class="card-body">
                    <table id="roles" class="table table-bordered table-striped table-sm responsive">
                        <thead class="text-center thead-dark">
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

    <!-- Modal único Crear/Editar Rol -->
    <div class="modal fade" id="roleModal" tabindex="-1" role="dialog" aria-labelledby="roleModalLabel" aria-hidden="true" aria-modal="true" data-backdrop="static">
        <div class="modal-dialog modal-dialog-scrollable" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="roleModalLabel">Añadir Nuevo Rol</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <form id="roleForm">
                        <input type="hidden" id="role_crud" name="crud" value="create">
                        <input type="hidden" id="role-id" name="id">

                        <div class="form-group">
                            <label for="role-nombre">Nombre del Rol</label>
                            <input
                                type="text"
                                name="nombre"
                                id="role-nombre"
                                class="form-control"
                                placeholder="Ingrese el nombre del rol"
                                required>
                        </div>
                    </form>
                </div>
                <div class="modal-footer justify-content-between">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                    <button type="button" class="btn btn-primary" id="saveRoleBtn">Guardar</button>
                </div>
            </div>
        </div>
    </div>
<?php
}
