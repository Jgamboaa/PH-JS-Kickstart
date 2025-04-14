<!-- Add -->
<div class="modal fade" id="profile">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <p class="modal-title"><b>Perfil de usuario</b></p>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <form id="profileForm" class="form-horizontal" enctype="multipart/form-data">
        <div class="modal-body">
          <div class="row">
            <div class="col-lg-6">
              <div class="form-group">
                <label for="admin_username" class="control-label">Correo</label>
                <input type="text" class="form-control" id="admin_username" name="username" value="<?php echo $user['username']; ?>" disabled>
              </div>
            </div>
            <div class="col-lg-6">
              <div class="form-group">
                <label for="admin_password" class="control-label">Contraseña</label>
                <input type="password" class="form-control" id="admin_password" name="password" value="<?php echo $user['password']; ?>">
              </div>
            </div>
          </div>
          <div class="row">
            <div class="col-lg-6">
              <div class="form-group">
                <label for="admin_firstname" class="control-label">Nombre</label>
                <input type="text" class="form-control" id="admin_firstname" name="firstname" value="<?php echo $user['user_firstname']; ?>" disabled>
              </div>
            </div>
            <div class="col-lg-6">
              <div class="form-group">
                <label for="admin_lastname" class="control-label">Apellido</label>
                <input type="text" class="form-control" id="admin_lastname" name="lastname" value="<?php echo $user['user_lastname']; ?>" disabled>
              </div>
            </div>
          </div>
          <div class="row">
            <div class="col-lg-6">
              <div class="form-group">
                <label for="color_mode" class="control-label">Modo de color</label>
                <select class="form-control" id="color_mode" name="color_mode">
                  <option value="dark" <?php if ($user['color_mode'] == "dark")
                                        {
                                          echo "selected";
                                        } ?>>Oscuro</option>
                  <option value="light" <?php if ($user['color_mode'] == "light")
                                        {
                                          echo "selected";
                                        } ?>>Claro</option>
                </select>
              </div>
            </div>
            <div class="col-lg-6">
              <div class="form-group">
                <label for="admin_photo" class="control-label">Foto</label>
                <div class="custom-file">
                  <input type="file" class="custom-file-input" id="admin_photo" name="photo">
                  <label class="custom-file-label" for="admin_photo">Elegir foto</label>
                </div>
              </div>
            </div>
          </div>
          <hr>
          <div class="form-group">
            <label for="curr_password" class="control-label">Contraseña Actual:</label>
            <input type="password" class="form-control" id="curr_password" name="curr_password" placeholder="Ingrese su contraseña actual para guardar los cambios" required>
          </div>

        </div>
        <div class="modal-footer justify-content-between">
          <button type="button" class="btn btn-sm btn-default" data-dismiss="modal"><span class="fa fa-solid fa-duotone fa-times"></span> Cerrar</button>
          <button type="submit" class="btn btn-sm btn-primary"><span class="fa fa-solid fa-duotone fa-save"> </span> Guardar</button>
        </div>
      </form>
    </div>
  </div>
</div>