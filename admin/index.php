<?php
require_once 'includes/session_config.php';
require_once 'includes/security_functions.php';
require_once 'includes/functions/2fa_functions.php'; // Incluimos las funciones de 2FA
require_once dirname(__DIR__) . '/config/env_reader.php'; // Corrección de la ruta con barra separadora
$APP_NAME = env('APP_NAME');

$isDebug = env('APP_DEBUG') === 'true';
error_reporting($isDebug ? E_ALL : E_ERROR | E_PARSE | E_CORE_ERROR);
ini_set('display_errors', $isDebug ? 1 : 0);
ini_set('display_startup_errors', $isDebug ? 1 : 0);
ini_set('log_errors', 1);

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
  <title><?php echo $APP_NAME; ?> | Iniciar sesión</title>
  <link rel="icon" href="../images/favicon.png">
  <link rel="stylesheet" href="../plugins/sweetalert2/sweetalert2.css">
  <script src="../dist/js/config.js"></script>
  <link href="../dist/css/app.css" rel="stylesheet" type="text/css" id="app-style" />
  <link rel="stylesheet" href="../dist/css/icons.css">
</head>

<body class="authentication-bg position-relative">

  <div class="account-pages pt-2 pt-sm-5 pb-4 pb-sm-5 position-relative">
    <div class="container">
      <div class="row justify-content-center">
        <div class="col-xxl-4 col-lg-5">
          <div class="card">

            <!-- Logo -->
            <div class="card-header text-center bg-dark">
              <span><img src="../images/logo2.png" height="70px"></span>
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
                      <input class="form-control" type="email" id="emailaddress" required placeholder="ejemplo@email.com" name="username" inputmode="email" autocomplete="email" autofocus>
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

                <form method="post" class="needs-validation" novalidate id="passwordLoginForm">
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
                        placeholder="Código de 6 dígitos" autocomplete="off" required inputmode="numeric">
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

              <!-- NUEVO: Contenedor para configuración 2FA -->
              <div id="setup-2fa-container" style="display: none;">
                <div class="text-center w-75 m-auto">
                  <h4 class="text-dark-50 text-center pb-0 fw-bold">Configuración de 2FA</h4>
                  <p class="text-muted mb-4">Es necesario configurar la autenticación de dos factores para continuar</p>
                </div>

                <form id="setup-2fa-form" class="needs-validation" novalidate>
                  <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
                  <input type="hidden" name="setup_2fa_submit" value="1">
                  <input type="hidden" id="setup-user-id" name="user_id" value="">

                  <div class="mb-3">
                    <p class="text-center">Por favor escanea este código QR con tu aplicación de autenticación:</p>
                    <div id="qrcode-container" class="text-center mb-3"></div>

                    <p class="text-center">O ingresa este código manualmente:</p>
                    <div class="text-center mb-3">
                      <code id="secret-key" class="p-2"></code>
                    </div>
                  </div>

                  <div class="mb-3">
                    <label for="otp-code" class="form-label">Código de verificación</label>
                    <div class="input-group input-group-merge">
                      <input type="text" id="otp-code" class="form-control" name="otp_code"
                        placeholder="Ingresa el código de 6 dígitos" autocomplete="off" required>
                      <div class="input-group-text">
                        <i class="fa-duotone fa-solid fa-shield-alt fa-lg"></i>
                      </div>
                    </div>
                    <div class="form-text">Introduce el código generado por la aplicación para verificar la configuración</div>
                  </div>

                  <div class="mb-3 text-center">
                    <button type="submit" class="btn btn-secondary" id="verify-setup-button">
                      <i class="fa-duotone fa-solid fa-check fa-lg me-1"></i> Verificar y activar
                    </button>
                  </div>
                </form>

                <div class="mt-3 text-center">
                  <p>Aplicaciones recomendadas: Google Authenticator, Microsoft Authenticator, Authy</p>
                  <button type="button" class="btn btn-link" id="cancel-setup-button">Cancelar</button>
                </div>
              </div>

              <!-- NUEVO: Modal para mostrar códigos de respaldo tras la configuración -->
              <div class="modal fade" id="backup-codes-modal" tabindex="-1" aria-labelledby="backup-codes-label" aria-hidden="true">
                <div class="modal-dialog">
                  <div class="modal-content">
                    <div class="modal-header">
                      <h5 class="modal-title" id="backup-codes-label">Códigos de respaldo</h5>
                      <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                      <p>Guarde estos códigos de respaldo en un lugar seguro. Si pierde acceso a su aplicación de autenticación, puede usar uno de estos códigos para iniciar sesión.</p>
                      <div class="alert alert-warning">
                        <ul id="backup-codes-list" class="mb-0"></ul>
                      </div>
                    </div>
                    <div class="modal-footer">
                      <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Continuar</button>
                    </div>
                  </div>
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
  <script src="https://cdn.rawgit.com/davidshimjs/qrcodejs/gh-pages/qrcode.min.js"></script>
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
              if (response.require_2fa_setup) {
                // NUEVO: Mostrar el formulario de configuración 2FA
                $('#password-login-container').hide();
                $('#setup-2fa-container').show();
                $('#setup-user-id').val(response.user_id);

                // Generar QR y secreto
                generateSetup2FAQR(response.username || $('#display-email').text());

                $button.prop('disabled', false).html('<i class="fa-duotone fa-solid fa-right-to-bracket fa-lg me-1"></i> Iniciar sesión');
              } else if (response.require_2fa) {
                // Mostrar formulario de verificación 2FA
                $('#password-login-container').hide();
                $('#tfa-verification-container').show();
                $('#tfa-user-id').val(response.user_id);
                $('#tfa-username').val($('#password-username').val());
                $('#tfa-display-email').text($('#display-email').text());
                $('#tfa-code').focus();
                $button.prop('disabled', false).html('<i class="fa-duotone fa-solid fa-right-to-bracket fa-lg me-1"></i> Iniciar sesión');
              } else if (response.redirect) {
                // Crear cookie persistente después de login exitoso
                if (typeof createPersistentCookie === 'function') {
                  createPersistentCookie(response.user_id || '');
                }

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

      // NUEVO: Función para generar QR y secreto para configuración 2FA
      function generateSetup2FAQR(username) {
        $.ajax({
          url: 'includes/system/setup_2fa_ajax.php',
          type: 'POST',
          data: {
            action: 'get_qrcode',
            user_id: $('#setup-user-id').val(), // Aseguramos que se envíe el user_id
            username: username,
            csrf_token: $('input[name="csrf_token"]').val()
          },
          dataType: 'json',
          success: function(response) {
            if (response.status) {
              // Mostrar el secreto
              $('#secret-key').text(response.secret);

              // Preparar el contenedor para el QR code con estilos flex
              $('#qrcode-container').empty();
              const qrcodeContainer = document.getElementById("qrcode-container");
              qrcodeContainer.style.display = "flex";
              qrcodeContainer.style.alignItems = "center";
              qrcodeContainer.style.justifyContent = "center";

              // Generar el código QR
              new QRCode(qrcodeContainer, {
                text: response.qr_uri,
                width: 200,
                height: 200,
                colorDark: "#000000",
                colorLight: "#ffffff",
                correctLevel: QRCode.CorrectLevel.H
              });
            } else {
              Swal.fire({
                icon: 'error',
                title: 'Error',
                text: 'No se pudo generar el código QR: ' + (response.message || 'Error desconocido')
              });
            }
          },
          error: function(xhr, status, error) {
            console.error('Error AJAX:', status, error, xhr.responseText);
            Swal.fire({
              icon: 'error',
              title: 'Error',
              text: 'Error de conexión al generar el código QR'
            });
          }
        });
      }

      // NUEVO: Manejar el envío del formulario de configuración 2FA
      $('#setup-2fa-form').on('submit', function(e) {
        e.preventDefault();
        var $form = $(this);
        var $button = $('#verify-setup-button');
        var code = $('#otp-code').val().trim();

        if (!code) {
          Swal.fire({
            icon: 'error',
            title: 'Error',
            text: 'Por favor ingresa el código de verificación'
          });
          return;
        }

        $button.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1" role="status" aria-hidden="true"></span> Verificando...');

        $.ajax({
          url: 'login.php',
          type: 'POST',
          data: $form.serialize(),
          dataType: 'json',
          success: function(response) {
            if (response.status) {
              // Mostrar los códigos de respaldo
              if (response.backup_codes && response.backup_codes.length > 0) {
                $('#backup-codes-list').empty();
                $.each(response.backup_codes, function(i, code) {
                  $('#backup-codes-list').append('<li>' + code + '</li>');
                });

                // Mostrar modal con código de confirmación para evitar que el sistema muestre mensaje de error
                var backupModal = new bootstrap.Modal(document.getElementById('backup-codes-modal'));
                backupModal.show();

                // Al cerrar el modal, redirigir
                $('#backup-codes-modal').on('hidden.bs.modal', function() {
                  window.location.href = response.redirect_url || 'home.php';
                });
              } else {
                // Si no hay códigos de respaldo, redirigir directamente
                Swal.fire({
                  icon: 'success',
                  title: 'Configuración completada',
                  text: '2FA configurado correctamente.',
                  timer: 1500,
                  showConfirmButton: false
                }).then(function() {
                  window.location.href = response.redirect_url || 'home.php';
                });
              }
            } else {
              $button.prop('disabled', false).html('<i class="fa-duotone fa-solid fa-check fa-lg me-1"></i> Verificar y activar');

              // Mostrar información de depuración si existe
              let errorMessage = response.message || 'Error al verificar el código';
              if (response.debug) {
                console.error('Error de depuración:', response.debug);
              }

              Swal.fire({
                icon: 'error',
                title: 'Error',
                text: errorMessage
              });
            }
          },
          error: function(xhr, status, error) {
            $button.prop('disabled', false).html('<i class="fa-duotone fa-solid fa-check fa-lg me-1"></i> Verificar y activar');

            console.error('Error AJAX:', status, error, xhr.responseText);

            Swal.fire({
              icon: 'error',
              title: 'Error',
              text: 'Error de conexión al verificar el código'
            });
          }
        });
      });

      // NUEVO: Cancelar configuración 2FA
      $('#cancel-setup-button').on('click', function() {
        Swal.fire({
          title: '¿Cancelar configuración?',
          text: 'La configuración de 2FA es obligatoria. Si cancelas, serás redirigido al inicio de sesión.',
          icon: 'warning',
          showCancelButton: true,
          confirmButtonText: 'Sí, cancelar',
          cancelButtonText: 'No, continuar configurando'
        }).then((result) => {
          if (result.isConfirmed) {
            $.ajax({
              url: 'includes/system/setup_2fa_ajax.php',
              type: 'POST',
              data: {
                action: 'cancel_setup',
                csrf_token: $('input[name="csrf_token"]').val()
              },
              dataType: 'json',
              success: function() {
                // Mostrar el formulario de verificación de correo
                $('#setup-2fa-container').hide();
                $('#email-check-container').show();
              }
            });
          }
        });
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

      // AÑADIR: Manejar el envío del formulario de verificación 2FA
      $('#tfa-form').on('submit', function(e) {
        e.preventDefault();
        var $form = $(this);
        var $button = $('#verify-tfa-button');
        var code = $('#tfa-code').val().trim();

        if (!code) {
          Swal.fire({
            icon: 'error',
            title: 'Error',
            text: 'Por favor ingresa el código de verificación'
          });
          return;
        }

        $button.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1" role="status" aria-hidden="true"></span> Verificando...');

        $.ajax({
          url: 'verify_2fa_ajax.php',
          type: 'POST',
          data: $form.serialize(),
          dataType: 'json',
          success: function(response) {
            if (response.status) {
              // Crear cookie persistente después de login exitoso con 2FA
              if (typeof createPersistentCookie === 'function') {
                createPersistentCookie(response.user_id || '');
              }

              Swal.fire({
                icon: 'success',
                title: 'Éxito',
                text: response.message || 'Verificación exitosa',
                timer: 1500,
                showConfirmButton: false
              }).then(function() {
                window.location.href = response.redirect_url || 'home.php';
              });
            } else {
              $button.prop('disabled', false).html('<i class="fa-duotone fa-solid fa-check fa-lg me-1"></i> Verificar');
              Swal.fire({
                icon: 'error',
                title: 'Error',
                text: response.message || 'Error de verificación'
              });
            }
          },
          error: function() {
            $button.prop('disabled', false).html('<i class="fa-duotone fa-solid fa-check fa-lg me-1"></i> Verificar');
            Swal.fire({
              icon: 'error',
              title: 'Error',
              text: 'Error de conexión'
            });
          }
        });
      });

      // AÑADIR: Manejar el evento de cambio al modo de código de respaldo
      $('#toggle-backup-code').on('click', function(e) {
        e.preventDefault();
        if ($('#tfa-backup-mode').val() === '0') {
          $('#tfa-backup-mode').val('1');
          $('#tfa-prompt-message').text('Ingresa un código de respaldo');
          $('#tfa-code').attr('placeholder', 'Código de respaldo');
          $(this).text('Volver al código de verificación');
        } else {
          $('#tfa-backup-mode').val('0');
          $('#tfa-prompt-message').text('Ingresa el código de verificación');
          $('#tfa-code').attr('placeholder', 'Código de 6 dígitos');
          $(this).text('¿Problemas con tu autenticador? Usar código de respaldo');
        }
        $('#tfa-code').val('').focus();
      });

      // AÑADIR: Manejar botón de cambio de correo en la vista 2FA
      $('#tfa-change-email-button').on('click', function() {
        $('#tfa-verification-container').hide();
        $('#email-check-container').show();
        $('#emailaddress').val('').focus();
      });

      // AÑADIR: Manejar botón de cambio de correo en la vista de contraseña
      $('#change-email-button').on('click', function() {
        $('#password-login-container').hide();
        $('#email-check-container').show();
        $('#emailaddress').val('').focus();
      });

      // AÑADIR: Auto-submit cuando se ingresen 6 dígitos en los campos de código
      $('#tfa-code, #otp-code').on('input', function() {
        const code = $(this).val().trim();
        if (code.length === 6) {
          // Pequeño delay para asegurar que el valor se haya establecido completamente
          setTimeout(() => {
            $(this).closest('form').submit();
          }, 100);
        }
      });

      // Detectar parámetros de URL para mostrar mensajes
      const urlParams = new URLSearchParams(window.location.search);

      // Mensaje para configuración de 2FA requerida
      if (urlParams.get('setup_2fa') === 'required') {
        Swal.fire({
          icon: 'warning',
          title: 'Configuración 2FA Requerida',
          text: 'La autenticación de dos factores es obligatoria para acceder al sistema. Por favor inicie sesión y configure su 2FA para continuar.',
          confirmButtonText: 'Entendido'
        });
      }
    });

    // Función auxiliar para crear cookie persistente
    function createPersistentCookie(userId) {
      if (!userId) return;

      // Calcular fecha de expiración (30 días)
      const expirationDate = new Date();
      expirationDate.setTime(expirationDate.getTime() + (30 * 24 * 60 * 60 * 1000));

      // Crear cookie
      document.cookie = "persistent_session=" + userId +
        "; expires=" + expirationDate.toUTCString() +
        "; path=/; SameSite=Lax" +
        (window.location.protocol === "https:" ? "; Secure" : "");
    }
  </script>
</body>

</html>