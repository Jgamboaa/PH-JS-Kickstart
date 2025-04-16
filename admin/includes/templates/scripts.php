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
        url: 'includes/system/users_crud.php',
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
    });

    // Cargar contenido inicial
    let route = window.location.hash.slice(1);
    if (route) {
      loadContent(route);
    } else {
      // Si no hay hash, cargar home por defecto
      loadContent('home');
    }
  });
</script>