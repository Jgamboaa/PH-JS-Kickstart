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
                    <a id="addnew" class="btn btn-sm btn-primary"><i class="fa fa-duotone fa-solid fa-plus fa-lg"></i></a>
                    <a class="btn btn-sm btn-warning"><i class="fa fa-duotone fa-solid fa-server fa-lg"></i></a>
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
                        <th>Última conexión</th>
                        <th>Acciones</th>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>
        </div>
    </section>


    <div class="modal fade" id="admin_modal">
        <div class="modal-dialog modal-lg" role="document">
            <form id="admin_form" enctype="multipart/form-data">
                <div class="modal-content">
                    <div class="modal-header">
                        <b class="modal-title" id="admin_modal_label">Agregar/Editar Usuario</b>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Cerrar">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
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
                                    <label for="usuario" class="control-label">Usuario</label>
                                    <input type="text" class="form-control" id="usuario" name="usuario" required>
                                </div>
                            </div>
                            <div class="col-lg-6">
                                <div class="form-group">
                                    <label for="password" class="control-label">Contraseña</label>
                                    <input type="password" class="form-control" id="password" name="password" required>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-lg-6">
                                <div class="form-group">
                                    <label for="firstname" class="control-label">Nombre</label>
                                    <input type="text" class="form-control" id="firstname" name="firstname" required>
                                </div>
                            </div>
                            <div class="col-lg-6">
                                <div class="form-group">
                                    <label for="lastname" class="control-label">Apellido</label>
                                    <input type="text" class="form-control" id="lastname" name="lastname" required>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-lg-6">
                                <div class="form-group">
                                    <label for="photo" class="control-label">Foto</label>
                                    <div class="custom-file">
                                        <input type="file" class="custom-file-input" id="photo" name="photo">
                                        <label class="custom-file-label" for="photo"></label>
                                    </div>
                                </div>
                            </div>
                            <div class="col-lg-6">
                                <div class="form-group">
                                    <label for="gender" class="control-label">Género</label>
                                    <select class="form-control" name="gender" id="gender" required>
                                        <option value=""></option>
                                        <option value="0">Masculino</option>
                                        <option value="1">Femenino</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="roles_ids" class="control-label">Roles</label>
                            <select class="form-control" multiple="" name="roles_ids[]" id="roles_ids" required>
                                <?php
                                $sql = "SELECT * FROM roles";
                                $query = $conn->query($sql);
                                while ($rrow = $query->fetch())
                                {
                                    echo "<option value='" . $rrow['id'] . "'>" . $rrow['nombre'] . "</option>";
                                }
                                ?>
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer justify-content-between">
                        <button type="button" class="btn btn-sm btn-default" data-dismiss="modal"><i class="fa fa-times"></i>
                            Cerrar</button>
                        <button type="submit" class="btn btn-sm btn-primary"><i class="fa fa-save"></i> Guardar</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
<?php
}
