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
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                </div>
            </div>
        </div>

        <div class="container-fluid">
            <form id="company_form" method="POST">
                <div class="card">

                    <!-- /.card-header -->
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="edit_company_name_short">Nombre de la empresa</label>
                                    <div class="input-group">
                                        <div class="input-group-prepend">
                                            <span class="input-group-text"><i class="fa-solid fa-duotone fa-building fa-lg"></i></span>
                                        </div>
                                        <input type="text" name="company_name_short" class="form-control" value="<?php echo $company_name_short; ?>">
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="edit_company_name">Razón Social</label>
                                    <div class="input-group">
                                        <div class="input-group-prepend">
                                            <span class="input-group-text"><i class="fa-solid fa-duotone fa-building fa-lg"></i></span>
                                        </div>
                                        <input type="text" name="company_name" class="form-control" value="<?php echo $company_name; ?>">
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="row mt-4">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="edit_app_name">Nombre de la Aplicación</label>
                                    <div class="input-group">
                                        <div class="input-group-prepend">
                                            <span class="input-group-text"><i class="fa-solid fa-duotone fa-code fa-lg"></i></span>
                                        </div>
                                        <input type="text" name="app_name" class="form-control" value="<?php echo $app_name; ?>">
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="edit_app_version">Versión</label>
                                    <div class="input-group">
                                        <div class="input-group-prepend">
                                            <span class="input-group-text"><i class="fa-solid fa-duotone fa-code-branch fa-lg"></i></span>
                                        </div>
                                        <input type="text" name="app_version" class="form-control" value="<?php echo $app_version; ?>">
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="edit_developer_name">Desarrollador</label>
                                    <div class="input-group">
                                        <div class="input-group-prepend">
                                            <span class="input-group-text"><i class="fa-solid fa-duotone fa-laptop-code fa-lg"></i></span>
                                        </div>
                                        <input type="text" name="developer_name" class="form-control" value="<?php echo $developer_name; ?>">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="card-footer clearfix">
                        <button type="submit" class="btn btn-sm btn-primary float-right"><i class="fa-solid fa-duotone fa-save"></i> Guardar</button>
                    </div>
                </div>
            </form>
        </div>
    </section>

<?php
}
?>