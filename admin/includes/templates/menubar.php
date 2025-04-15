<!-- Main Sidebar Container -->
<aside class="main-sidebar sidebar-dark-primary elevation-4">
  <!-- Brand Logo -->
  <a class="brand-link recargar">
    <img src="../images/logo_circulo.png" alt="Logo" class="brand-image">
    <span class="brand-text font-weight-bold"><strong><?php echo $company_name_short ?></strong></span>
  </a>
  <!-- Sidebar -->
  <div class="sidebar">
    <!-- Sidebar user panel (optional) -->
    <div class="user-panel mt-3 pb-3 mb-3 d-flex">
      <div class="image">
        <img
          src="<?php echo $photoSrc; ?>"
          class="img-circle" alt="User Image">
      </div>
      <div class="info">
        <a href="#" class="d-block"><?php echo $user['user_firstname'] . ' ' . $user['user_lastname']; ?></a>
      </div>
    </div>
    <!-- Sidebar Menu -->
    <nav class="mt-2">
      <ul class="nav nav-pills nav-sidebar flex-column nav-child-indent" data-widget="treeview" role="menu"
        data-accordion="true">
        <?php
        $roles_ids = explode(',', $user['roles_ids']);
        include('../admin/includes/menubar/home.php');
        //panel de administrador
        if (in_array("1", $roles_ids))
        {
          include('../admin/includes/menubar/system.php');
        }
        ?>
      </ul>
    </nav>
    <!-- /.sidebar-menu -->
  </div>
  <!-- /.sidebar -->
</aside>