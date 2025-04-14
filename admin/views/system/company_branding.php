<?php
include '../../includes/session.php';

$admin_id = $user['id'];
$roles_ids = explode(',', $user['roles_ids']);

if (!in_array(1, $roles_ids)) {
    include '403.php';
} else {

?>

    <section class="content">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                </div>
            </div>
        </div>
        <div class="container-fluid">
            <form id="branding_form" method="POST" enctype="multipart/form-data">
                <div class="card">
                    <div class="card-body">
                        <div class="row">
                            <div class="col">
                                <div class="row">
                                    <div class="col-lg-6">
                                        <label for="logo">Logo (452*354 px)</label>
                                        <div class="input-group">
                                            <div class="custom-file">
                                                <input type="file" class="custom-file-input" name="logo" id="logo" accept="image/png">
                                                <label class="custom-file-label" for="logo">Seleccionar</label>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-lg-6 text-center">
                                        <img src="../images/logo.png" alt="Logo" class="brand-image" width="88.5px">
                                    </div>
                                </div>
                                <br>
                                <div class="row">
                                    <div class="col-lg-6">
                                        <label for="logo_w">Logo color blanco (452*354 px)</label>
                                        <div class="input-group">
                                            <div class="custom-file">
                                                <input type="file" class="custom-file-input" name="logo2" id="logo_w" accept="image/png">
                                                <label class="custom-file-label" for="logo_w">Seleccionar</label>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-lg-6 text-center">
                                        <div class="bg-primary p-2 ">
                                            <img src=" ../images/logo2.png" alt="Logo Blanco" class="brand-image" width="88.5px">
                                        </div>
                                    </div>
                                </div>
                                <br>
                                <div class="row">
                                    <div class="col-lg-6">
                                        <label for="logo3">Logo sin letras (400*400 px)</label>
                                        <div class="input-group">
                                            <div class="custom-file">
                                                <input type="file" class="custom-file-input" name="logo_circulo" id="logo3" accept="image/png">
                                                <label class="custom-file-label" for="logo3">Seleccionar</label>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="bg-primary col-lg-6 text-center">
                                        <img src="../images/logo_circulo.png" alt="Logo3" width="80px">
                                    </div>
                                </div>
                            </div>
                            <div class="col">
                                <div class="row">
                                    <div class="col-lg-6">
                                        <label for="favicon">Favicon (160*160 px)</label>
                                        <div class="input-group">
                                            <div class="custom-file">
                                                <input type="file" class="custom-file-input" name="favicon" id="favicon" accept="image/png">
                                                <label class="custom-file-label" for="favicon">Seleccionar</label>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="bg-primary col-lg-6 text-center">
                                        <img src="../images/favicon.png" alt="Favicon" width="80px">
                                    </div>
                                </div>
                                <br>
                                <div class="row">
                                    <div class="col-lg-6">
                                        <label for="avatar">Avatar (400*400 px)</label>
                                        <div class="input-group">
                                            <div class="custom-file">
                                                <input type="file" class="custom-file-input" name="avatar" id="avatar" accept="image/png">
                                                <label class="custom-file-label" for="avatar">Seleccionar</label>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-lg-6 text-center">
                                        <img src="../images/avatar.png" alt="Avatar" width="80px">
                                    </div>
                                </div>
                            </div>

                        </div>


                    </div>
                    <div class="card-footer clearfix">
                        <button type="submit" name="save" class="btn btn-sm btn-primary float-right"><i class="fas fa-save"></i> Guardar</button>
                    </div>
                </div>
            </form>

        </div>

    <?php
}
