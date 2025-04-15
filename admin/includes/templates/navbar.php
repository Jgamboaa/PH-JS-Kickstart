<!-- Navbar -->
<?php
$navbarClass = $user['color_mode'] == "dark" ? "navbar-dark" : "navbar-white navbar-light";
echo "<nav class='main-header navbar navbar-expand $navbarClass'>";
?>
<!-- Left navbar links -->
<ul class="navbar-nav">
  <li class="nav-item">
    <a class="nav-link toggle-sidebar" data-widget="pushmenu" href="#" role="button"><i class="fa fa-duotone fa-bars"></i></a>
  </li>
  <li class="nav-item">
    <a class="nav-link" data-widget="fullscreen" role="button">
      <i class="fa fa-duotone fa-expand-arrows-alt"></i>
    </a>
  </li>
  <li class="nav-item">
    <a class="nav-link recargar">
      <i class="fa-duotone fa-solid fa-arrows-rotate-reverse"></i>
    </a>
  </li>
</ul>



<!-- Right navbar links -->
<ul class="navbar-nav ml-auto">
  <li class="nav-item dropdown user-menu">
    <a class="nav-link dropdown-toggle" data-toggle="dropdown">
      <img src="<?php echo $photoSrc; ?>" class="user-image img-circle" alt="User Image">
    </a>
    <ul class="dropdown-menu dropdown-menu-md dropdown-menu-right">
      <!-- User image -->
      <li class="user-header">
        <img src="<?php echo $photoSrc; ?>" class="img-circle">
        <p>
          <?php echo $user['user_firstname'] . ' ' . $user['user_lastname']; ?>

          <small>
            Usuario
          </small>
        </p>
      </li>
      <!-- Menu Footer-->
      <li class="user-footer">
        <div class="row">
          <div class="col-lg-6">
            <a href="#profile" data-toggle="modal" class="btn btn-primary btn-sm btn-block" id="admin_profile">Perfil</a>
          </div>
          <div class="col-lg-6">
            <a href="logout.php" class="btn btn-primary btn-sm btn-block">Cerrar Sesión</a>
          </div>
        </div>
      </li>
    </ul>
  </li>
</ul>
</nav>
<!-- /.navbar -->