<?php
// Verificar si existe el archivo .env, si no, redirigir al inicializador
if (!file_exists(__DIR__ . '/../.env'))
{
  header('location:../init.php');
  exit();
}

require_once 'includes/session_config.php';
require_once 'includes/security_functions.php';

if (isset($_SESSION['admin']))
{
  header('location:home.php');
  exit();
}

$csrf_token = generateCSRFToken();
?>
<!DOCTYPE html>
<html lang="es">

<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Login</title>
  <link rel="icon" href="../images/favicon.png">
  <link rel="stylesheet" href="../plugins/sweetalert2/sweetalert2.css">
  <script src="../dist/js/config.js"></script>
  <link href="../dist/css/app.css" rel="stylesheet" type="text/css" id="app-style" />
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
  <link rel="stylesheet" href="https://site-assets.fontawesome.com/releases/v6.5.1/css/all.css">
  <link rel="stylesheet" href="https://site-assets.fontawesome.com/releases/v6.5.1/css/sharp-thin.css">
  <link rel="stylesheet" href="https://site-assets.fontawesome.com/releases/v6.5.1/css/sharp-solid.css">
  <link rel="stylesheet" href="https://site-assets.fontawesome.com/releases/v6.5.1/css/sharp-regular.css">
  <link rel="stylesheet" href="https://site-assets.fontawesome.com/releases/v6.5.1/css/sharp-light.css">
</head>

<body class="authentication-bg position-relative">

  <div class="account-pages pt-2 pt-sm-5 pb-4 pb-sm-5 position-relative">
    <div class="container">
      <div class="row justify-content-center">
        <div class="col-xxl-4 col-lg-5">
          <div class="card">

            <!-- Logo -->
            <div class="card-header text-center bg-dark">
              <span><img src="../images/logo2.png" height="100"></span>
            </div>

            <div class="card-body p-4">

              <div class="text-center w-75 m-auto">
                <h4 class="text-dark-50 text-center pb-0 fw-bold">Inicia Sesión</h4>
                <p class="text-muted mb-4">Ingresa tu usuario y contraseña para accederder al sistema</p>
              </div>

              <form action="login.php" method="post" class="needs-validation" novalidate id="loginForm">
                <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
                <input type="hidden" name="login" value="1">


                <?php if (isset($_SESSION['error'])): ?>
                  <div class='alert alert-warning alert-dismissible fade show text-center' role='alert'>
                    <strong><?php echo htmlspecialchars($_SESSION['error']); ?></strong>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                  </div>
                  <?php unset($_SESSION['error']); ?>
                <?php endif; ?>

                <div class="mb-3">
                  <label for="emailaddress" class="form-label">Correo</label>
                  <div class="input-group input-group-merge">

                    <input class="form-control" type="email" id="emailaddress"
                      required placeholder="ejemplo@email.com" name="username"
                      value="<?php echo isset($_SESSION['username']) ? htmlspecialchars($_SESSION['username']) : ''; ?>">
                    <div class="input-group-text" data-password="false">
                      <i class="fa-duotone fa-solid fa-envelope fa-lg"></i>
                    </div>
                  </div>
                  <div class="invalid-feedback">
                    Por favor ingrese un correo válido
                  </div>
                </div>

                <div class="mb-3">
                  <label for="password" class="form-label">Contraseña</label>
                  <div class="input-group input-group-merge">
                    <input type="password" id="password" class="form-control" placeholder="Pass123#*" name="password" required>
                    <div class="input-group-text" data-password="false">
                      <i class="fa-duotone fa-solid fa-eye fa-lg"></i>
                    </div>
                  </div>
                </div>

                <div class="mb-3 mb-0 text-center">
                  <button type="submit" class="btn btn-secondary" id="loginButton">
                    <i class="fa-duotone fa-solid fa-right-to-bracket fa-lg me-1"></i> Iniciar sesión
                  </button>
                </div>

                <script>
                  (function() {
                    'use strict'
                    var forms = document.querySelectorAll('.needs-validation')
                    Array.prototype.slice.call(forms).forEach(function(form) {
                      form.addEventListener('submit', function(event) {
                        if (!form.checkValidity()) {
                          event.preventDefault()
                          event.stopPropagation()
                        }
                        form.classList.add('was-validated')
                      }, false)
                    })
                  })()
                </script>

              </form>
            </div>
          </div>
          <!-- end card -->
          <!-- end row -->

        </div> <!-- end col -->
      </div>
      <!-- end row -->
    </div>
    <!-- end container -->
  </div>
  <!-- end page -->

  <footer class="footer footer-alt fw-medium">
    <span>
      <script>
        document.write(new Date().getFullYear())
      </script> - Core
    </span>
  </footer>
  <script src="../dist/js/vendor.min.js"></script>
  <script src="../dist/js/app.js"></script>
  <script src="../plugins/sweetalert2/sweetalert2.min.js"></script>
  <script>
    // Funciones de validación
    function validateEmail(email) {
      const emailPattern = /^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/;
      return emailPattern.test(email);
    }

    function validatePassword(password) {
      return password.length >= 6; // Mínimo 6 caracteres
    }

    // Elementos del formulario
    const emailInput = document.getElementById('emailaddress');
    const passwordInput = document.getElementById('password');
    const form = document.querySelector('.needs-validation');

    // Validación en tiempo real para email
    emailInput.addEventListener('input', function() {
      if (validateEmail(this.value)) {
        this.classList.remove('is-invalid');
        this.classList.add('is-valid');
      } else {
        this.classList.remove('is-valid');
        this.classList.add('is-invalid');
      }
    });

    // Validación en tiempo real para password
    passwordInput.addEventListener('input', function() {
      if (validatePassword(this.value)) {
        this.classList.remove('is-invalid');
        this.classList.add('is-valid');
      } else {
        this.classList.remove('is-valid');
        this.classList.add('is-invalid');
      }
    });

    // Mantener la validación del submit
    form.addEventListener('submit', function(event) {
      if (!form.checkValidity() || !validateEmail(emailInput.value) || !validatePassword(passwordInput.value)) {
        event.preventDefault();
        event.stopPropagation();
      }
      form.classList.add('was-validated');
    });

    $(document).ready(function() {
      $('#loginForm').on('submit', function(e) {
        e.preventDefault();
        var $form = $(this);
        var $button = $('#loginButton');

        // Validar correo antes de intentar el login
        const email = $('#emailaddress').val();
        if (!validateEmail(email)) {
          Swal.fire({
            icon: 'error',
            title: 'Error',
            text: 'Por favor ingrese un correo electrónico válido'
          });
          return false;
        }

        $button.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1" role="status" aria-hidden="true"></span> Cargando...');

        $.ajax({
          url: $form.attr('action'),
          type: 'POST',
          data: $form.serialize(),
          dataType: 'json',
          success: function(response) {
            if (response.status && response.redirect) {
              Swal.fire({
                icon: 'success',
                title: 'Éxito',
                text: response.message,
                timer: 1500,
                showConfirmButton: false
              }).then(function() {
                window.location.href = 'home.php';
              });
            } else {
              $button.prop('disabled', false).html('<i class="bi bi-box-arrow-in-right me-1"></i> Iniciar sesión');
              if (response.blocked) {
                setTimeout(function() {
                  $button.prop('disabled', false);
                }, 180000); // 3 minutos
              }
              Swal.fire({
                icon: 'error',
                title: 'Error',
                text: response.message
              });
            }
          },
          error: function() {
            $button.prop('disabled', false).html('<i class="bi bi-box-arrow-in-right me-1"></i> Iniciar sesión');
            Swal.fire({
              icon: 'error',
              title: 'Error',
              text: 'Error en la conexión'
            });
          }
        });
      });
    });
  </script>
</body>

<?php
unset($_SESSION['username']);
?>

</html>