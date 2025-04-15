<?php
include 'includes/session.php';
include 'includes/templates/header.php';
echo "<body class='hold-transition " . ($user['color_mode'] == "dark" ? "dark-mode " : "") . "sidebar-mini layout-fixed layout-navbar-fixed layout-footer-fixed'>";
?>
<div class="wrapper">
  <?php
  include 'includes/templates/navbar.php';
  include 'includes/templates/menubar.php';
  ?>
  <div class="content-wrapper" id="container1"></div>
  <?php
  include 'includes/templates/footer.php';
  include 'includes/templates/scripts.php';
  ?>
  <div id="container2"></div>
</div>
</body>

</html>