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
                            <div class="col-md">
                                <div class="row">
                                    <div class="col-lg-6">
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
                                    <div class="col-lg-6">
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
                                <div class="form-group">
                                    <label for="edit_address">Dirección</label>
                                    <div class="input-group">
                                        <div class="input-group-prepend">
                                            <span class="input-group-text"><i class="fa-solid fa-duotone fa-location-dot fa-lg"></i></span>
                                        </div>
                                        <textarea name="address" class="form-control"><?php echo $company_address; ?></textarea>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-lg-6">
                                        <label for="edit_company_nit">NIT</label>
                                        <div class="input-group">
                                            <div class="input-group-prepend">
                                                <span class="input-group-text">
                                                    <i class="fa-solid fa-duotone fa-hashtag fa-lg"></i>
                                                </span>
                                            </div>
                                            <input type="text" value="<?php echo $company_nit; ?>" name="company_nit" class="form-control">
                                        </div>
                                    </div>
                                    <div class="col-lg-6">
                                        <label for="edit_company_employers_number">Número Patronal</label>
                                        <div class="input-group">
                                            <div class="input-group-prepend">
                                                <span class="input-group-text"><i class="fa-solid fa-duotone fa-hashtag fa-lg"></i></span>
                                            </div>
                                            <input type="text" value="<?php echo $company_employers_number; ?>" name="company_employers_number" class="form-control">
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md">
                                <div class="form-group">
                                    <label for="edit_rep_name">Representante Legal</label>
                                    <div class="input-group">
                                        <div class="input-group-prepend">
                                            <span class="input-group-text"><i class="fa-solid fa-duotone fa-user fa-lg"></i></span>
                                        </div>
                                        <input type="text" value="<?php echo $rep_name; ?>" name="rep_name" class="form-control">
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-lg-6">
                                        <label for="edit_rep_marital_status">Estado Civil</label>
                                        <div class="input-group">
                                            <div class="input-group-prepend">
                                                <span class="input-group-text">
                                                    <i class="fa-solid fa-duotone fa-list-check fa-lg"></i>
                                                </span>
                                            </div>
                                            <input type="text" value="<?php echo $rep_marital_status; ?>" name="rep_marital_status" class="form-control">
                                        </div>
                                    </div>
                                    <div class="col-lg-6">
                                        <label for="edit_rep_nacionality">Nacionalidad</label>

                                        <div class="input-group">
                                            <div class="input-group-prepend">
                                                <span class="input-group-text"><i class="fa-solid fa-duotone fa-earth-americas fa-lg"></i></span>
                                            </div>
                                            <input type="text" value="<?php echo $rep_nacionality; ?>" name="rep_nacionality" class="form-control">
                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-lg-6">
                                        <label for="edit_rep_studies">Ocupación</label>
                                        <div class="input-group">
                                            <div class="input-group-prepend">
                                                <span class="input-group-text">
                                                    <i class="fa-solid fa-duotone fa-graduation-cap fa-lg"></i> </span>
                                            </div>
                                            <input type="text" value="<?php echo $rep_studies; ?>" name="rep_studies" class="form-control">
                                        </div>
                                    </div>
                                    <div class="col-lg-6">
                                        <label for="edit_rep_position">Puesto</label>
                                        <div class="input-group">
                                            <div class="input-group-prepend">
                                                <span class="input-group-text"><i class="fa-solid fa-duotone fa-briefcase fa-lg"></i></span>
                                            </div>
                                            <input type="text" value="<?php echo $rep_position; ?>" name="rep_position" class="form-control">
                                        </div>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-lg-6">
                                        <label for="edit_rep_dpi_number">Número de DPI</label>
                                        <div class="input-group">
                                            <div class="input-group-prepend">
                                                <span class="input-group-text">
                                                    <i class="fa-duotone fa-id-card fa-lg"></i>
                                                </span>
                                            </div>
                                            <input type="text" value="<?php echo $rep_dpi_number; ?>" name="rep_dpi_number" class="form-control">
                                        </div>
                                    </div>
                                    <div class="col-lg-6">
                                        <label for="edit_rep_age_ltr">Edad en letras</label>
                                        <div class="input-group">
                                            <div class="input-group-prepend">
                                                <span class="input-group-text"><i class="fa-solid fa-duotone fa-calendar fa-lg"></i></span>
                                            </div>
                                            <input type="text" value="<?php echo $rep_age_ltr; ?>" name="rep_age_ltr" class="form-control">
                                        </div>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label for="edit_rep_contract" class="control-label">Representación legal</label>
                                    <div class="input-group">
                                        <textarea name="rep_contract" class="form-control" rows="5"><?php echo $rep_contract; ?></textarea>
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