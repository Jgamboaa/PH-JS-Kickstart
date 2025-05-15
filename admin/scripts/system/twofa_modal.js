$(document).ready(function () {
  // Variable para controlar el estado de 2FA
  let tfaStatus = {
    enabled: false,
    hasSecret: false,
    username: "",
  };

  // Cargar el modal cuando se haga clic en el botón de configuración 2FA
  $(document).on("click", ".config-2fa-btn", function (e) {
    e.preventDefault();
    loadTfaStatus();
    $("#modal2FA").modal("show");
  });

  // Función para cargar el estado de 2FA
  function loadTfaStatus() {
    $.ajax({
      url: "includes/system/twofa_ajax.php",
      type: "POST",
      data: {
        action: "get_status",
      },
      dataType: "json",
      success: function (response) {
        if (response.status) {
          tfaStatus = {
            enabled: response.tfa_enabled,
            hasSecret: response.has_secret,
            username: response.username,
          };

          // Actualizar la etiqueta de estado
          $("#tfa_status_badge")
            .removeClass("bg-danger bg-success")
            .addClass(tfaStatus.enabled ? "bg-success" : "bg-danger")
            .text(tfaStatus.enabled ? "Activado" : "Desactivado");

          // Cargar el contenido correspondiente
          loadTfaContent();
        } else {
          showErrorMessage("Error al cargar el estado de 2FA");
        }
      },
      error: function () {
        showErrorMessage("Error de conexión al servidor");
      },
    });
  }

  // Función para cargar el contenido del modal según el estado de 2FA
  function loadTfaContent() {
    let content = "";

    if (tfaStatus.enabled) {
      // 2FA está activado - mostrar opciones de gestión
      content = `
                <div class="text-center mb-4">
                    <h5>Gestión de autenticación de dos factores</h5>
                    <p>Tu autenticación de dos factores está <span class="badge bg-success">Activada</span></p>
                </div>

                <div class="row justify-content-center">
                    <div class="col-md-5">
                        <div class="card mb-4">
                            <div class="card-header text-center bg-danger">
                                <h5 class="m-0">Desactivar 2FA</h5>
                            </div>
                            <div class="card-body">
                                <form id="deactivate2FAForm">
                                    <div class="form-group">
                                        <label for="deactivate_otp">Introduce tu código de autenticación actual:</label>
                                        <input type="text" class="form-control" id="deactivate_otp" name="otp" required placeholder="Código de 6 dígitos" pattern="[0-9]{6}" maxlength="6">
                                    </div>
                                    <button type="submit" class="btn btn-danger btn-block">Desactivar 2FA</button>
                                </form>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-5">
                        <div class="card mb-4">
                            <div class="card-header text-center bg-warning">
                                <h5 class="m-0">Códigos de Respaldo</h5>
                            </div>
                            <div class="card-body">
                                <form id="backupCodesForm">
                                    <div class="form-group">
                                        <label for="backup_otp">Introduce tu código de autenticación actual:</label>
                                        <input type="text" class="form-control" id="backup_otp" name="otp" required placeholder="Código de 6 dígitos" pattern="[0-9]{6}" maxlength="6">
                                    </div>
                                    <div class="row">
                                        <div class="col-md-6">
                                            <button type="button" class="btn btn-warning btn-block" id="showBackupBtn">Ver códigos</button>
                                        </div>
                                        <div class="col-md-6">
                                            <button type="button" class="btn btn-info btn-block" id="regenerateBackupBtn">Regenerar códigos</button>
                                        </div>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            `;
    } else if (tfaStatus.hasSecret) {
      // Tiene secreto pero 2FA está desactivado - opción para reactivar
      content = `
                <div class="text-center mb-4">
                    <h5>Reactivar autenticación de dos factores</h5>
                    <p>Tienes 2FA configurado pero está <span class="badge bg-danger">Desactivado</span></p>
                </div>

                <div class="row justify-content-center">
                    <div class="col-md-6">
                        <div class="alert alert-info">
                            <p>Ya tienes 2FA configurado anteriormente. Puedes reactivarlo ingresando un código de verificación generado por tu aplicación de autenticación.</p>
                        </div>
                        <div class="card">
                            <div class="card-body">
                                <form id="reactivate2FAForm">
                                    <div class="form-group">
                                        <label for="reactivate_otp">Introduce un código de autenticación:</label>
                                        <input type="text" class="form-control" id="reactivate_otp" name="otp" required placeholder="Código de 6 dígitos" pattern="[0-9]{6}" maxlength="6">
                                    </div>
                                    <button type="submit" class="btn btn-primary btn-block">Reactivar 2FA</button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            `;
    } else {
      // No tiene 2FA configurado - mostrar configuración inicial
      content = `
                <div class="text-center mb-4">
                    <h5>Configuración inicial de autenticación de dos factores</h5>
                    <p>La autenticación de dos factores proporciona una capa adicional de seguridad para tu cuenta</p>
                </div>

                <div class="row justify-content-center">
                    <div class="col-md-6">
                        <div class="card">
                            <div class="card-body">
                                <button id="setupNewTfaBtn" class="btn btn-primary btn-block mb-3">Configurar 2FA</button>
                            </div>
                        </div>
                    </div>
                </div>
            `;
    }

    $("#tfa_content_container").html(content);

    // Inicializar los eventos después de cargar el contenido
    initTfaEvents();
  }

  // Función para inicializar los eventos de los formularios y botones de 2FA
  function initTfaEvents() {
    // Si el 2FA está activado
    if (tfaStatus.enabled) {
      // Evento para el formulario de desactivación
      $("#deactivate2FAForm").on("submit", function (e) {
        e.preventDefault();
        const otp = $("#deactivate_otp").val().trim();

        if (!otp) {
          showErrorMessage(
            "Por favor introduce un código de autenticación válido"
          );
          return;
        }

        $.ajax({
          url: "includes/system/twofa_ajax.php",
          type: "POST",
          data: {
            action: "deactivate",
            otp: otp,
          },
          dataType: "json",
          success: function (response) {
            if (response.status) {
              Swal.fire({
                icon: "success",
                title: "¡Éxito!",
                text: response.message,
                showConfirmButton: false,
                timer: 1500,
              });
              loadTfaStatus(); // Recargar el contenido
            } else {
              showErrorMessage(response.message);
            }
          },
          error: function () {
            showErrorMessage("Error de conexión al servidor");
          },
        });
      });

      // Evento para mostrar códigos de respaldo
      $("#showBackupBtn").on("click", function () {
        const otp = $("#backup_otp").val().trim();

        if (!otp) {
          showErrorMessage(
            "Por favor introduce un código de autenticación válido"
          );
          return;
        }

        $.ajax({
          url: "includes/system/twofa_ajax.php",
          type: "POST",
          data: {
            action: "show_backup",
            otp: otp,
          },
          dataType: "json",
          success: function (response) {
            if (response.status) {
              showBackupCodes(response.backup_codes, "Tus códigos de respaldo");
            } else {
              showErrorMessage(response.message);
            }
          },
          error: function () {
            showErrorMessage("Error de conexión al servidor");
          },
        });
      });

      // Evento para regenerar códigos de respaldo
      $("#regenerateBackupBtn").on("click", function () {
        const otp = $("#backup_otp").val().trim();

        if (!otp) {
          showErrorMessage(
            "Por favor introduce un código de autenticación válido"
          );
          return;
        }

        Swal.fire({
          title: "¿Regenerar códigos de respaldo?",
          text: "Esto invalidará todos tus códigos de respaldo actuales",
          icon: "warning",
          showCancelButton: true,
          confirmButtonText: "Sí, regenerar",
          cancelButtonText: "Cancelar",
        }).then((result) => {
          if (result.isConfirmed) {
            $.ajax({
              url: "includes/system/twofa_ajax.php",
              type: "POST",
              data: {
                action: "regenerate_backup",
                otp: otp,
              },
              dataType: "json",
              success: function (response) {
                if (response.status) {
                  showBackupCodes(
                    response.backup_codes,
                    "Tus nuevos códigos de respaldo"
                  );
                } else {
                  showErrorMessage(response.message);
                }
              },
              error: function () {
                showErrorMessage("Error de conexión al servidor");
              },
            });
          }
        });
      });
    } else if (tfaStatus.hasSecret) {
      // Evento para reactivar 2FA
      $("#reactivate2FAForm").on("submit", function (e) {
        e.preventDefault();
        const otp = $("#reactivate_otp").val().trim();

        if (!otp) {
          showErrorMessage(
            "Por favor introduce un código de autenticación válido"
          );
          return;
        }

        $.ajax({
          url: "includes/system/twofa_ajax.php",
          type: "POST",
          data: {
            action: "verify_setup",
            otp: otp,
          },
          dataType: "json",
          success: function (response) {
            if (response.status) {
              Swal.fire({
                icon: "success",
                title: "¡Éxito!",
                text: response.message,
                showConfirmButton: false,
                timer: 1500,
              });
              loadTfaStatus(); // Recargar el contenido
            } else {
              showErrorMessage(response.message);
            }
          },
          error: function () {
            showErrorMessage("Error de conexión al servidor");
          },
        });
      });
    } else {
      // Evento para iniciar configuración de 2FA
      $("#setupNewTfaBtn").on("click", function () {
        $.ajax({
          url: "includes/system/twofa_ajax.php",
          type: "POST",
          data: {
            action: "setup",
          },
          dataType: "json",
          success: function (response) {
            if (response.status) {
              showTfaSetup(response.secret, response.qr_uri);
            } else {
              showErrorMessage("Error al generar configuración 2FA");
            }
          },
          error: function () {
            showErrorMessage("Error de conexión al servidor");
          },
        });
      });
    }
  }

  // Función para mostrar la pantalla de configuración de 2FA
  function showTfaSetup(secret, qrUri) {
    const setupHtml = `
            <div class="text-center mb-4">
                <h5>Configura la autenticación de dos factores</h5>
            </div>

            <div class="row justify-content-center">
                <div class="col-md-5">
                    <div class="card mb-3">
                        <div class="card-header bg-info text-white">
                            <h5 class="m-0">Escanea este código QR</h5>
                        </div>
                        <div class="card-body text-center">
                            <div id="qrcode" class="mb-3"></div>
                            <p class="mb-1">o ingresa manualmente esta clave:</p>
                            <div class="input-group">
                                <input type="text" class="form-control" value="${secret}" readonly id="secretKey">
                                <div class="input-group-append">
                                    <button class="btn btn-outline-secondary" type="button" id="copySecretBtn">
                                        <i class="fa fa-duotone fa-solid fa-copy"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-7">
                    <div class="card">
                        <div class="card-header bg-primary text-white">
                            <h5 class="m-0">Instrucciones</h5>
                        </div>
                        <div class="card-body">
                            <ol>
                                <li>Descarga e instala una aplicación de autenticación como Google Authenticator, Microsoft Authenticator o Authy en tu dispositivo móvil.</li>
                                <li>Escanea el código QR con la app o ingresa manualmente la clave secreta.</li>
                                <li>Ingresa el código de 6 dígitos generado por la app para verificar.</li>
                            </ol>
                            
                            <form id="verify2FAForm" class="mt-4">
                                <div class="form-group">
                                    <label for="verify_otp">Código de verificación:</label>
                                    <input type="text" class="form-control" id="verify_otp" name="otp" required placeholder="Código de 6 dígitos" pattern="[0-9]{6}" maxlength="6">
                                </div>
                                <button type="submit" class="btn btn-primary btn-block">Activar 2FA</button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        `;

    $("#tfa_content_container").html(setupHtml);

    // Generar código QR
    new QRCode(document.getElementById("qrcode"), {
      text: qrUri,
      width: 200,
      height: 200,
      colorDark: "#000000",
      colorLight: "#ffffff",
      correctLevel: QRCode.CorrectLevel.H,
    });

    // Evento para copiar la clave secreta
    $("#copySecretBtn").on("click", function () {
      const secretInput = document.getElementById("secretKey");
      secretInput.select();
      document.execCommand("copy");

      $(this)
        .tooltip({
          title: "¡Copiado!",
          trigger: "manual",
        })
        .tooltip("show");

      setTimeout(() => {
        $(this).tooltip("hide");
      }, 2000);
    });

    // Evento para verificar y activar 2FA
    $("#verify2FAForm").on("submit", function (e) {
      e.preventDefault();
      const otp = $("#verify_otp").val().trim();

      if (!otp) {
        showErrorMessage(
          "Por favor introduce un código de verificación válido"
        );
        return;
      }

      $.ajax({
        url: "includes/system/twofa_ajax.php",
        type: "POST",
        data: {
          action: "verify_setup",
          otp: otp,
        },
        dataType: "json",
        success: function (response) {
          if (response.status) {
            showBackupCodes(
              response.backup_codes,
              "¡2FA activado correctamente!",
              true
            );
          } else {
            showErrorMessage(response.message);
          }
        },
        error: function () {
          showErrorMessage("Error de conexión al servidor");
        },
      });
    });
  }

  // Función para mostrar códigos de respaldo
  function showBackupCodes(codes, title, reload = false) {
    let codesHtml = '<div class="alert alert-warning mb-3">';
    codesHtml +=
      "<strong>IMPORTANTE:</strong> Guarda estos códigos en un lugar seguro. Cada código solo puede usarse una vez.";
    codesHtml += "</div>";
    codesHtml += '<div class="row mb-3">';

    codes.forEach(function (code) {
      codesHtml += `<div class="col-md-4 mb-2"><code>${code}</code></div>`;
    });

    codesHtml += "</div>";

    Swal.fire({
      title: title,
      html: codesHtml,
      confirmButtonText: "Entendido",
      showClass: {
        popup: "animate__animated animate__fadeInDown",
      },
      hideClass: {
        popup: "animate__animated animate__fadeOutUp",
      },
      width: "50em",
    }).then(() => {
      if (reload) {
        loadTfaStatus(); // Recargar el contenido después de activar 2FA
      }
    });
  }

  // Función para mostrar mensajes de error
  function showErrorMessage(message) {
    Swal.fire({
      icon: "error",
      title: "Error",
      text: message,
    });
  }
});
