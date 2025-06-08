<script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
<script src="https://code.jquery.com/ui/1.13.3/jquery-ui.min.js"></script>
<script src="../plugins/sweetalert2/sweetalert2.min.js"></script>
<script src="../dist/js/utils.js"></script>
<script src="../plugins/bootstrap/js/bootstrap.bundle.min.js"></script>
<script src="../plugins/chart.js/Chart.min.js"></script>
<script src="../plugins/moment/moment.min.js"></script>
<script src="../plugins/daterangepicker/daterangepicker.js"></script>
<script src="../plugins/timepicker/bootstrap-timepicker.min.js"></script>
<script src="../plugins/tempusdominus-bootstrap-4/js/tempusdominus-bootstrap-4.min.js"></script>
<script src="../dist/js/adminlte.js"></script>
<script src="../plugins/datatables/jquery.dataTables.min.js"></script>
<script src="../plugins/datatables-bs4/js/dataTables.bootstrap4.min.js"></script>
<script src="../plugins/datatables-responsive/js/dataTables.responsive.min.js"></script>
<script src="../plugins/datatables-responsive/js/responsive.bootstrap4.min.js"></script>
<script src="../plugins/datatables-buttons/js/dataTables.buttons.min.js"></script>
<script src="../plugins/datatables-buttons/js/buttons.bootstrap4.min.js"></script>
<script src="https://cdn.datatables.net/plug-ins/1.13.7/filtering/type-based/accent-neutralise.js"></script>
<script src="../plugins/select2/js/select2.full.min.js"></script>
<script src="../plugins/dropzone/min/dropzone.min.js"></script>
<script src="../plugins/toastr/toastr.min.js"></script>
<script src="../plugins/bs-custom-file-input/bs-custom-file-input.min.js"></script>
<script src="../dist/js/general_scripts.js"></script>
<?php
if (!isset($range_from))
{
  $range_from = date('Y-m-01');
}
if (!isset($range_to))
{
  $range_to = date('Y-m-t');
}
?>
<script>
  $(function() {
    $('#profileForm').on('submit', function(e) {
      e.preventDefault();
      let formData = new FormData(this);
      // Añadir el parámetro crud para identificar la operación
      formData.append('crud', 'profile');

      $.ajax({
        url: 'includes/system/users.php',
        type: 'POST',
        data: formData,
        processData: false,
        contentType: false,
        success: function(response) {
          let res = JSON.parse(response);
          if (res.status) {
            Swal.fire({
              icon: 'success',
              title: '¡Éxito!',
              text: res.message,
              showConfirmButton: false,
              timer: 1500
            }).then(function() {
              location.reload();
            });
          } else {
            Swal.fire({
              icon: 'error',
              title: 'Error',
              text: res.message
            });
          }
        }
      });
    });
  });
</script>
<script>
  $(document).ready(function() {
    // Añadir el manejador para el botón de recarga
    $(document).on('click', '.recargar', function(e) {
      e.preventDefault();
      let route = window.location.hash.slice(1) || 'home';
      loadContent(route);
    });

    // Función para reinicializar plugins
    function initializePlugins() {

      // Reinicializar datepickers
      $('input[name="date_range"], input[name="date_range_overtime"]').daterangepicker({
        opens: "center",
        locale: {
          format: 'DD/MM/YYYY',
          applyLabel: 'Aplicar',
          cancelLabel: 'Cancelar',
          fromLabel: 'Desde',
          toLabel: 'Hasta',
          customRangeLabel: 'Personalizado',
          daysOfWeek: ['Do', 'Lu', 'Ma', 'Mi', 'Ju', 'Vi', 'Sa'],
          monthNames: ['Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio', 'Julio', 'Agosto', 'Septiembre', 'Octubre', 'Noviembre', 'Diciembre'],
          firstDay: 1
        }
      });

      // Reinicializar timepickers
      $(".timepicker").timepicker({
        showInputs: false
      });

      $("#time_in, #time_out").datetimepicker({
        format: "LT"
      });

      // Reinicializar select2
      $(".select2bs4").select2({
        theme: "bootstrap4"
      });

      // Reinicializar custom file input
      bsCustomFileInput.init();

      // Agregar funcionalidad de conversión de fechas
      function convertDateFormat(dateStr) {
        const parts = dateStr.split('/');
        return `${parts[1]}/${parts[0]}/${parts[2]}`;
      }

      function updateDateFormats() {
        const dateRangeOvertime = document.getElementById('date_range_overtime');
        const reservation = document.getElementById('reservation');
        const reservation2 = document.getElementById('reservation2');
        const dateRangeB14 = document.getElementById('date_rangeb14');

        if (dateRangeOvertime) {
          dateRangeOvertime.value = dateRangeOvertime.value.split(' - ').map(convertDateFormat).join(' - ');
        }
        if (reservation) {
          reservation.value = reservation.value.split(' - ').map(convertDateFormat).join(' - ');
        }
        if (reservation2) {
          reservation2.value = reservation2.value.split(' - ').map(convertDateFormat).join(' - ');
        }
        if (dateRangeB14) {
          dateRangeB14.value = convertDateFormat(dateRangeB14.value);
        }
      }

      // Agregar los event listeners para los formularios
      $('#payForm').off('submit').on('submit', function(e) {
        updateDateFormats();
      });
      $('#analitics_form').off('submit').on('submit', function(e) {
        updateDateFormats();
      });
      $('#overtimeForm').off('submit').on('submit', function(e) {
        updateDateFormats();
      });
    }

    // Función para cargar contenido
    function loadContent(route) {
      $.ajax({
        url: 'includes/rutas.php',
        type: 'POST',
        data: {
          ruta: route
        },
        dataType: 'json',
        success: function(response) {
          // Cargar vista
          $.get(response.vista, function(data) {
            $('#container1').html(data);
            // Inicializar plugins después de cargar el contenido
            initializePlugins();

            // Cargar scripts
            if (response.scripts && response.scripts.length > 0) {
              $('#container2').empty();
              response.scripts.forEach(function(script) {
                $.get(script, function(scriptData) {
                  $('#container2').append(scriptData);
                  // Reinicializar plugins después de cargar scripts adicionales
                  initializePlugins();
                });
              });
            }
          });
        },
        error: function() {
          console.error('Error al cargar la ruta');
        }
      });
    }

    // Manejar cambios en el hash de la URL
    $(window).on('hashchange', function() {
      let route = window.location.hash.slice(1); // Eliminar el # del inicio
      if (route) {
        loadContent(route);
      } else {
        // Si no hay hash, cargar home por defecto
        loadContent('home');
      }
    }); // Cargar contenido inicial
    let route = window.location.hash.slice(1);
    if (route) {
      loadContent(route);
    } else {
      // Si no hay hash, cargar home por defecto
      loadContent('home');
    }
  });
</script>
<!-- Script para la funcionalidad del modal de 2FA -->
<script>
  $(document).ready(function() {
    // Variable para controlar el estado de 2FA
    let tfaStatus = {
      enabled: false,
      hasSecret: false,
      username: "",
    };

    // Cargar el modal cuando se haga clic en el botón de configuración 2FA
    $(document).on("click", ".config-2fa-btn", function(e) {
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
        success: function(response) {
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
        error: function() {
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
                <div class="row">
                    <div class="col-md-6">
                        <div class="card">
                            <div class="card-header text-center bg-primary">
                                <b>Añadir Dispositivo Adicional</b>
                            </div>
                            <div class="card-body">
                                <form id="addDeviceForm">
                                    <div class="form-group">
                                        <label for="add_device_otp">Introduce tu código de autenticación actual:</label>
                                        <input type="text" class="form-control form-control-sm" id="add_device_otp" name="otp" required placeholder="Código de 6 dígitos" pattern="[0-9]{6}" maxlength="6">
                                    </div>
                                    <button type="submit" class="btn btn-primary btn-block">Configurar Nuevo Dispositivo</button>
                                </form>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="card">
                            <div class="card-header text-center bg-warning">
                                <b>Códigos de Respaldo</b>
                            </div>
                            <div class="card-body">
                                <form id="backupCodesForm">
                                    <div class="form-group">
                                        <label for="backup_otp">Introduce tu código de autenticación actual:</label>
                                        <input type="text" class="form-control form-control-sm" id="backup_otp" name="otp" required placeholder="Código de 6 dígitos" pattern="[0-9]{6}" maxlength="6">
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
                <div class="row justify-content-center">
                    <div class="col">
                        <div class="alert alert-info">
                            <p>Ya tienes 2FA configurado anteriormente, pero esta <span class="badge bg-danger">desactivado</span>. Puedes reactivarlo ingresando un código de verificación generado por tu aplicación de autenticación.</p>
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
        // Evento para el formulario de añadir dispositivo
        $("#addDeviceForm").on("submit", function(e) {
          e.preventDefault();
          const otp = $("#add_device_otp").val().trim();

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
              action: "setup_additional_device",
              otp: otp,
            },
            dataType: "json",
            success: function(response) {
              if (response.status) {
                showDeviceSetup(response.secret, response.qr_uri);
              } else {
                showErrorMessage(response.message);
              }
            },
            error: function() {
              showErrorMessage("Error de conexión al servidor");
            },
          });
        });

        // Evento para mostrar códigos de respaldo
        $("#showBackupBtn").on("click", function() {
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
            success: function(response) {
              if (response.status) {
                showBackupCodes(response.backup_codes, "Tus códigos de respaldo");
              } else {
                showErrorMessage(response.message);
              }
            },
            error: function() {
              showErrorMessage("Error de conexión al servidor");
            },
          });
        });

        // Evento para regenerar códigos de respaldo
        $("#regenerateBackupBtn").on("click", function() {
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
                success: function(response) {
                  if (response.status) {
                    showBackupCodes(
                      response.backup_codes,
                      "Tus nuevos códigos de respaldo"
                    );
                  } else {
                    showErrorMessage(response.message);
                  }
                },
                error: function() {
                  showErrorMessage("Error de conexión al servidor");
                },
              });
            }
          });
        });
      } else if (tfaStatus.hasSecret) {
        // Evento para reactivar 2FA
        $("#reactivate2FAForm").on("submit", function(e) {
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
            success: function(response) {
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
            error: function() {
              showErrorMessage("Error de conexión al servidor");
            },
          });
        });
      } else {
        // Evento para iniciar configuración de 2FA
        $("#setupNewTfaBtn").on("click", function() {
          $.ajax({
            url: "includes/system/twofa_ajax.php",
            type: "POST",
            data: {
              action: "setup",
            },
            dataType: "json",
            success: function(response) {
              if (response.status) {
                showTfaSetup(response.secret, response.qr_uri);
              } else {
                showErrorMessage("Error al generar configuración 2FA");
              }
            },
            error: function() {
              showErrorMessage("Error de conexión al servidor");
            },
          });
        });
      }
    }

    // Función para mostrar la pantalla de configuración de dispositivo adicional
    function showDeviceSetup(secret, qrUri) {
      const setupHtml = `
            <div class="text-center mb-4">
                <h5>Configura un dispositivo adicional</h5>
            </div>

            <div class="row justify-content-center">
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-header bg-info text-white">
                            <b>Escanea este código QR en tu nuevo dispositivo</b>
                        </div>
                        <div class="card-body">
                            <div id="qrcode"></div>
                            <p class="mt-2">o ingresa manualmente esta clave:</p>
                            <div class="input-group">
                                <input type="text" class="form-control form-cotrol-sm" value="${secret}" readonly id="secretKey">
                                <div class="input-group-append">
                                    <button class="btn btn-outline-secondary" type="button" id="copySecretBtn">
                                        <i class="fa fa-duotone fa-solid fa-copy"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-header bg-primary text-white">
                            <b>Instrucciones</b>
                        </div>
                        <div class="card-body">
                            <ol>
                                <li>Abre la aplicación de autenticación en tu nuevo dispositivo.</li>
                                <li>Escanea el código QR con la app o ingresa manualmente la clave secreta.</li>
                                <li>Ingresa el código de 6 dígitos generado por la app para verificar.</li>
                            </ol>
                            
                            <form id="verifyAdditionalDeviceForm" class="mt-4">
                                <div class="form-group">
                                    <label for="verify_additional_otp">Código de verificación:</label>
                                    <input type="text" class="form-control" id="verify_additional_otp" name="otp" required placeholder="Código de 6 dígitos" pattern="[0-9]{6}" maxlength="6">
                                </div>
                                <button type="submit" class="btn btn-primary btn-block">Confirmar Dispositivo</button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        `;

      $("#tfa_content_container").html(setupHtml);

      // Centrando el contenedor del código QR
      const qrcodeContainer = document.getElementById("qrcode");
      qrcodeContainer.style.display = "flex";
      qrcodeContainer.style.alignItems = "center";
      qrcodeContainer.style.justifyContent = "center";

      // Generar código QR
      new QRCode(qrcodeContainer, {
        text: qrUri,
        width: 200,
        height: 200,
        colorDark: "#000000",
        colorLight: "#ffffff",
        correctLevel: QRCode.CorrectLevel.H,
      });

      // Evento para copiar la clave secreta
      $("#copySecretBtn").on("click", function() {
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

      // Evento para verificar y activar dispositivo adicional
      $("#verifyAdditionalDeviceForm").on("submit", function(e) {
        e.preventDefault();
        const otp = $("#verify_additional_otp").val().trim();

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
            action: "verify_additional_device",
            otp: otp,
          },
          dataType: "json",
          success: function(response) {
            if (response.status) {
              Swal.fire({
                icon: "success",
                title: "¡Éxito!",
                text: response.message,
                showConfirmButton: false,
                timer: 1500,
              }).then(() => {
                loadTfaStatus(); // Recargar el contenido
              });
            } else {
              showErrorMessage(response.message);
            }
          },
          error: function() {
            showErrorMessage("Error de conexión al servidor");
          },
        });
      });
    }

    // Función para mostrar la pantalla de configuración de 2FA
    function showTfaSetup(secret, qrUri) {
      const setupHtml = `
            <div class="text-center mb-4">
                <h5>Configura la autenticación de dos factores</h5>
            </div>

            <div class="row justify-content-center">
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-header bg-info text-white">
                            <b>Escanea este código QR</b>
                        </div>
                        <div class="card-body">
                            <div id="qrcode"></div>
                            <p class="mt-2">o ingresa manualmente esta clave:</p>
                            <div class="input-group">
                                <input type="text" class="form-control form-cotrol-sm" value="${secret}" readonly id="secretKey">
                                <div class="input-group-append">
                                    <button class="btn btn-outline-secondary" type="button" id="copySecretBtn">
                                        <i class="fa fa-duotone fa-solid fa-copy"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-header bg-primary text-white">
                            <b>Instrucciones</b>
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

      // Centrando el contenedor del código QR
      const qrcodeContainer = document.getElementById("qrcode");
      qrcodeContainer.style.display = "flex";
      qrcodeContainer.style.alignItems = "center";
      qrcodeContainer.style.justifyContent = "center";

      // Generar código QR
      new QRCode(qrcodeContainer, {
        text: qrUri,
        width: 200,
        height: 200,
        colorDark: "#000000",
        colorLight: "#ffffff",
        correctLevel: QRCode.CorrectLevel.H,
      });

      // Evento para copiar la clave secreta
      $("#copySecretBtn").on("click", function() {
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
      $("#verify2FAForm").on("submit", function(e) {
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
          success: function(response) {
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
          error: function() {
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

      codes.forEach(function(code) {
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
</script>