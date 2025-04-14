<?php
include 'includes/session.php';
include 'includes/header.php';
if ($user['color_mode'] == "dark") {
  echo "<body class='hold-transition dark-mode sidebar-mini layout-fixed layout-navbar-fixed layout-footer-fixed'>";
} else {
  echo "<body class='hold-transition sidebar-mini layout-fixed layout-navbar-fixed layout-footer-fixed'>";
}
?>
<div class="wrapper">
  <?php
  include 'includes/navbar.php';
  include 'includes/menubar.php';
  ?>
  <div class="content-wrapper" id="container1"></div>
  <?php
  include 'includes/footer.php';
  include 'includes/scripts.php';
  ?>
  <div id="container2"></div>
</div>
</body>

</html>