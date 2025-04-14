<?php
include '../../includes/session.php';

$admin_id = $user['id'];
$roles_ids = explode(',', $user['roles_ids']);

if (!in_array(1, $roles_ids)) {
    include '403.php';
} else {

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
}
