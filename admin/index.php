<?php
// Verificar si existe el archivo .env, si no, redirigir al inicializador
if (!file_exists(__DIR__ . '/../.env'))
{
  header('location:../init.php');
  exit();
}

require_once 'includes/session_config.php';
require_once 'includes/security_functions.php';
require_once 'includes/functions/2fa_functions.php'; // Incluimos las funciones de 2FA
require_once dirname(__DIR__) . '../config/env_reader.php';
$APP_NAME = env('APP_NAME');

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

              <!-- Paso 1: Formulario de verificación de correo electrónico -->
              <div id="email-check-container">
                <div class="text-center w-75 m-auto">
                  <h4 class="text-dark-50 text-center pb-0 fw-bold">Inicia Sesión</h4>
                  <p class="text-muted mb-4">Ingresa tu correo electrónico para continuar</p>
                </div>

                <form id="emailCheckForm" class="needs-validation" novalidate>
                  <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">

                  <div class="mb-3">
                    <label for="emailaddress" class="form-label">Correo</label>
                    <div class="input-group input-group-merge">
                      <input class="form-control" type="email" id="emailaddress" required placeholder="ejemplo@email.com" name="username">
                      <div class="input-group-text" data-password="false">
                        <i class="fa-duotone fa-solid fa-envelope fa-lg"></i>
                      </div>
                    </div>
                    <div class="invalid-feedback">
                      Por favor ingrese un correo válido
                    </div>
                  </div>

                  <div class="mb-3 mb-0 text-center">
                    <button type="submit" class="btn btn-secondary" id="checkEmailButton">
                      <i class="fa-duotone fa-solid fa-arrow-right fa-lg me-1"></i> Continuar
                    </button>
                  </div>
                </form>
              </div>

              <!-- Paso 2: Formulario de Login con contraseña (inicialmente oculto) -->
              <div id="password-login-container" style="display: none;">
                <div class="text-center w-75 m-auto">
                  <h4 class="text-dark-50 text-center pb-0 fw-bold">Inicia Sesión</h4>
                  <p class="text-muted mb-4">Ingresa tu contraseña para acceder</p>
                </div>

                <form action="login.php" method="post" class="needs-validation" novalidate id="passwordLoginForm">
                  <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
                  <input type="hidden" name="login_password" value="1">
                  <input type="hidden" id="password-username" name="username">

                  <div class="mb-3">
                    <label class="form-label">Correo</label>
                    <p class="form-control-static" id="display-email"></p>
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
                </form>

                <div class="text-center mt-3">
                  <button type="button" class="btn btn-link" id="change-email-button">Cambiar correo</button>
                </div>
              </div>

              <!-- Formulario de verificación 2FA (inicialmente oculto) -->
              <div id="tfa-verification-container" style="display: none;">
                <div class="text-center w-75 m-auto">
                  <h4 class="text-dark-50 text-center pb-0 fw-bold">Verificación 2FA</h4>
                  <p class="text-muted mb-4" id="tfa-prompt-message">Ingresa el código de verificación</p>
                </div>

                <form id="tfa-form" class="needs-validation" novalidate>
                  <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
                  <input type="hidden" name="login_2fa" value="1">
                  <input type="hidden" id="tfa-user-id" name="user_id" value="">
                  <input type="hidden" id="tfa-username" name="username">
                  <input type="hidden" id="tfa-backup-mode" name="backup_mode" value="0">

                  <div class="mb-3">
                    <label class="form-label">Correo</label>
                    <p class="form-control-static" id="tfa-display-email"></p>
                  </div>

                  <div class="mb-3">
                    <label for="tfa-code" class="form-label">Código</label>
                    <div class="input-group input-group-merge">
                      <input type="text" id="tfa-code" class="form-control" name="code"
                        placeholder="Código de 6 dígitos" autocomplete="off" required>
                      <div class="input-group-text">
                        <i class="fa-duotone fa-solid fa-shield-alt fa-lg"></i>
                      </div>
                    </div>
                  </div>

                  <div class="mb-3 text-center">
                    <button type="submit" class="btn btn-secondary" id="verify-tfa-button">
                      <i class="fa-duotone fa-solid fa-check fa-lg me-1"></i> Verificar
                    </button>
                  </div>
                </form>

                <div class="mt-3 text-center">
                  <p><a href="#" id="toggle-backup-code">¿Problemas con tu autenticador? Usar código de respaldo</a></p>
                  <button type="button" class="btn btn-link" id="tfa-change-email-button">Cambiar correo</button>
                </div>
              </div>

            </div>
          </div>
          <!-- end card -->
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
      </script> - <?php echo $APP_NAME; ?>
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

    $(document).ready(function() {
      // Evento para verificar el correo electrónico
      $('#emailCheckForm').on('submit', function(e) {
        e.preventDefault();
        const email = $('#emailaddress').val().trim();

        if (!validateEmail(email)) {
          Swal.fire({
            icon: 'error',
            title: 'Error',
            text: 'Por favor ingresa un correo electrónico válido'
          });
          return;
        }

        var $button = $('#checkEmailButton');
        $button.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1" role="status" aria-hidden="true"></span> Verificando...');

        // Llamar al nuevo endpoint para verificar el tipo de autenticación
        $.ajax({
          url: 'check_user_auth_type.php',
          type: 'POST',
          data: {
            username: email,
            csrf_token: $('input[name="csrf_token"]').val()
          },
          dataType: 'json',
          success: function(response) {
            $button.prop('disabled', false).html('<i class="fa-duotone fa-solid fa-arrow-right fa-lg me-1"></i> Continuar');

            if (response.status) {
              // Mostrar el formulario correspondiente según el tipo de autenticación
              $('#email-check-container').hide();

              if (response.auth_type === 'password') {
                // Mostrar formulario de contraseña
                $('#password-username').val(email);
                $('#display-email').text(email);
                $('#password-login-container').show();
                $('#password').focus();
              } else if (response.auth_type === '2fa') {
                // Mostrar formulario 2FA
                $('#tfa-user-id').val(response.user_id);
                $('#tfa-username').val(email);
                $('#tfa-display-email').text(email);
                $('#tfa-verification-container').show();
                $('#tfa-code').focus();
              }
            } else {
              Swal.fire({
                icon: 'error',
                title: 'Error',
                text: response.message
              });

              if (response.blocked) {
                $('#emailaddress').prop('disabled', true);
                $button.prop('disabled', true);
                setTimeout(function() {
                  $('#emailaddress').prop('disabled', false);
                  $button.prop('disabled', false);
                }, 180000); // 3 minutos
              }
            }
          },
          error: function() {
            $button.prop('disabled', false).html('<i class="fa-duotone fa-solid fa-arrow-right fa-lg me-1"></i> Continuar');
            Swal.fire({
              icon: 'error',
              title: 'Error',
              text: 'Error en la conexión'
            });
          }
        });
      });

      // Manejar el envío del formulario de contraseña
      $('#passwordLoginForm').on('submit', function(e) {
        e.preventDefault();
        var $form = $(this);
        var $button = $('#loginButton');
        var password = $('#password').val();

        if (!validatePassword(password)) {
          Swal.fire({
            icon: 'error',
            title: 'Error',
            text: 'La contraseña debe tener al menos 6 caracteres'
          });
          return;
        }

        $button.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1" role="status" aria-hidden="true"></span> Cargando...');

        $.ajax({
          url: 'login.php',
          type: 'POST',
          data: $form.serialize(),
          dataType: 'json',
          success: function(response) {
            if (response.status) {
              if (response.require_2fa) {
                // Mostrar formulario de verificación 2FA
                $('#password-login-container').hide();
                $('#tfa-verification-container').show();
                $('#tfa-user-id').val(response.user_id);
                $('#tfa-code').focus();
                $button.prop('disabled', false).html('<i class="fa-duotone fa-solid fa-right-to-bracket fa-lg me-1"></i> Iniciar sesión');
              } else if (response.redirect) {
                // Redirección normal
                Swal.fire({
                  icon: 'success',
                  title: 'Éxito',
                  text: response.message,
                  timer: 1500,
                  showConfirmButton: false
                }).then(function() {
                  window.location.href = response.redirect_url || 'home.php';
                });
              }
            } else {
              $button.prop('disabled', false).html('<i class="fa-duotone fa-solid fa-right-to-bracket fa-lg me-1"></i> Iniciar sesión');
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
            $button.prop('disabled', false).html('<i class="fa-duotone fa-solid fa-right-to-bracket fa-lg me-1"></i> Iniciar sesión');
            Swal.fire({
              icon: 'error',
              title: 'Error',
              text: 'Error en la conexión'
            });
          }
        });
      });

      // Manejar envío del formulario 2FA
      $('#tfa-form').on('submit', function(e) {
        e.preventDefault();
        var $button = $('#verify-tfa-button');
        var code = $('#tfa-code').val().trim();

        if (!code) {
          Swal.fire({
            icon: 'error',
            title: 'Error',
            text: 'Por favor ingresa un código'
          });
          return;
        }

        $button.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1" role="status" aria-hidden="true"></span> Verificando...');

        // Realizar verificación 2FA mediante AJAX
        $.ajax({
          url: 'login.php',
          type: 'POST',
          data: $(this).serialize(),
          dataType: 'json',
          success: function(response) {
            if (response.status) {
              // Autenticación exitosa
              Swal.fire({
                icon: 'success',
                title: 'Verificación exitosa',
                text: response.message,
                timer: 1500,
                showConfirmButton: false
              }).then(function() {
                window.location.href = response.redirect_url || 'home.php';
              });
            } else {
              // Error de verificación
              $button.prop('disabled', false).html('<i class="fa-duotone fa-solid fa-check fa-lg me-1"></i> Verificar');
              Swal.fire({
                icon: 'error',
                title: 'Error',
                text: response.message
              });
            }
          },
          error: function() {
            $button.prop('disabled', false).html('<i class="fa-duotone fa-solid fa-check fa-lg me-1"></i> Verificar');
            Swal.fire({
              icon: 'error',
              title: 'Error',
              text: 'Error en la conexión. Inténtalo de nuevo.'
            });
          }
        });
      });

      // Cambiar entre código normal y código de respaldo
      $('#toggle-backup-code').on('click', function(e) {
        e.preventDefault();
        var useBackupMode = $('#tfa-backup-mode').val() === '0';
        $('#tfa-backup-mode').val(useBackupMode ? '1' : '0');

        if (useBackupMode) {
          // Cambiar a modo de código de respaldo
          $('#tfa-prompt-message').text('Ingresa un código de respaldo');
          $('#tfa-code').attr('placeholder', 'Código de respaldo');
          $(this).text('Usar código de aplicación en su lugar');
        } else {
          // Cambiar a modo de código normal
          $('#tfa-prompt-message').text('Ingresa el código de verificación de tu aplicación de autenticación');
          $('#tfa-code').attr('placeholder', 'Código de 6 dígitos');
          $(this).text('¿Problemas con tu autenticador? Usar código de respaldo');
        }

        $('#tfa-code').focus();
      });

      // Botones para cambiar de correo
      $('#change-email-button, #tfa-change-email-button').on('click', function() {
        $('#email-check-container').show();
        $('#password-login-container, #tfa-verification-container').hide();
        $('#emailaddress').focus();
        $('#tfa-form, #passwordLoginForm')[0].reset();
      });

      // Toggle de visibilidad para la contraseña
      $('.input-group-text[data-password]').on('click', function() {
        const isVisible = $(this).data('password') === true;
        const passwordField = $(this).siblings('input');

        if (isVisible) {
          $(this).data('password', false);
          $(this).html('<i class="fa-duotone fa-solid fa-eye fa-lg"></i>');
          passwordField.attr('type', 'password');
        } else {
          $(this).data('password', true);
          $(this).html('<i class="fa-duotone fa-solid fa-eye-slash fa-lg"></i>');
          passwordField.attr('type', 'text');
        }
      });
    });
  </script>
</body>

</html>