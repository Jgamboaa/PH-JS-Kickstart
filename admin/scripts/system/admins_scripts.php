<script>
    $(document).ready(function() {
        var table = $('#admins').DataTable({
            ajax: {
                url: 'includes/system/users_crud.php',
                type: 'GET',
                data: function(d) {
                    d.crud = 'fetch';
                }
            },
            columns: [{
                    data: 'foto',
                    className: 'text-center'
                },
                {
                    data: 'nombre'
                },
                {
                    data: 'correo'
                },
                {
                    data: 'roles'
                },
                {
                    data: 'ultimo_login',
                    className: 'text-center'
                },
                {
                    data: 'acciones',
                    className: 'text-center'
                }
            ],
            autoWidth: false,
            ordering: false,
            stateSave: true,
            language: {
                url: '../dist/js/spanish.json'
            }
        });

        $('#addnew').on('click', function() {
            $('#admin_form')[0].reset();
            $('#admin_crud').val('create');
            $('#admin_id').val('');
            $('#admin_modal_label').text('Crear Usuario');
            $('#current_photo').hide();
            $('#admin_photo').attr('src', '');
            $('#admin_modal').modal('show');
            $('#admin_form select').select2({
                theme: 'bootstrap4',
                placeholder: 'Seleccione',
                dropdownParent: $('#admin_modal')
            });
        });

        $('#admins').on('click', '.edit', function() {
            var id = $(this).data('id');
            $('#admin_form')[0].reset();
            $('#admin_crud').val('edit');
            $('#admin_modal_label').text('Editar Usuario');
            $('#admin_id').val(id);

            $.ajax({
                type: 'POST',
                url: 'includes/system/users_crud.php',
                data: {
                    crud: 'get',
                    id: id
                },
                dataType: 'json',
                success: function(response) {
                    // Rellenar los campos del formulario con los datos del empleado
                    $('#usuario').val(response.username);
                    $('#password').val(response.password);
                    $('#firstname').val(response.user_firstname);
                    $('#lastname').val(response.user_lastname);
                    $('#roles_ids').val(response.roles_ids.split(','));
                    $('#gender').val(response.admin_gender);
                    if (response.photo) {
                        var photoPath = '../images/admins/' + response.photo;
                        $('#admin_modal img').attr('src', photoPath);
                        $('#current_photo').show();
                    } else {
                        $('#admin_modal img').attr('src', '');
                        $('#current_photo').hide();
                    }
                    $('#admin_modal').modal('show');
                    $('#admin_form select').select2({
                        theme: 'bootstrap4',
                        dropdownParent: $('#admin_modal')
                    });
                },
                error: function(jqXHR, textStatus, errorThrown) {
                    console.log('Error AJAX:', textStatus, errorThrown);
                    console.log('Respuesta del servidor:', jqXHR.responseText);
                }
            });
        });

        $('#admin_form').on('submit', function(e) {
            e.preventDefault();
            var formData = new FormData(this);
            $.ajax({
                type: 'POST',
                url: 'includes/system/users_crud.php',
                data: formData,
                contentType: false,
                processData: false,
                dataType: 'json',
                success: function(response) {
                    $('#admin_modal').modal('hide');
                    Swal.fire(response.message, '', response.status ? 'success' : 'error');
                    $('#admins').DataTable().ajax.reload(null, false);
                },
                error: function(jqXHR, textStatus, errorThrown) {
                    console.log('Error AJAX:', textStatus, errorThrown);
                    Swal.fire('Error en la solicitud AJAX', '', 'error');
                }
            });
        });

        $('#admins').on('click', '.delete', function() {
            var id = $(this).data('id');
            Swal.fire({
                title: '¿Estás seguro?',
                text: 'Este usuario será dado de baja.',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Sí, dar de baja',
                cancelButtonText: 'Cancelar'
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        type: 'POST',
                        url: 'includes/system/users_crud.php',
                        data: {
                            crud: 'delete',
                            id: id
                        },
                        dataType: 'json',
                        success: function(response) {
                            Swal.fire(response.message, '', response.status ? 'success' : 'error');
                            $('#admins').DataTable().ajax.reload(null, false);
                        }
                    });
                }
            });
        });

        $('.btn-warning').click(function(e) {
            e.preventDefault();

            Swal.fire({
                title: 'Generando respaldo',
                text: 'Por favor espere...',
                allowOutsideClick: false,
                allowEscapeKey: false,
                showConfirmButton: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });

            $.ajax({
                type: 'POST',
                url: 'includes/system/dump.php',
                xhrFields: {
                    responseType: 'blob'
                },
                success: function(response) {
                    const blob = new Blob([response], {
                        type: 'application/octet-stream'
                    });
                    const url = window.URL.createObjectURL(blob);
                    const a = document.createElement('a');
                    a.href = url;
                    a.download = 'DB_BACKUP_' + moment().format('YYYY-MM-DD-HH-mm-ss') + '.sql';
                    document.body.appendChild(a);
                    a.click();
                    window.URL.revokeObjectURL(url);
                    document.body.removeChild(a);

                    Swal.close();
                },
                error: function(xhr, status, error) {
                    Swal.fire({
                        title: 'Error',
                        text: 'Ocurrió un error al generar el respaldo',
                        icon: 'error'
                    });
                }
            });
        });

        // Nueva funcionalidad para ejecutar el respaldo por email
        $('.btn-email-backup').click(function(e) {
            e.preventDefault();

            Swal.fire({
                title: 'Enviar respaldo por correo',
                input: 'email',
                inputLabel: 'Dirección de correo electrónico',
                inputPlaceholder: 'Ingrese una dirección de correo',
                inputValue: 'ejemplo@ejemplo.com',
                showCancelButton: true,
                confirmButtonText: 'Enviar',
                cancelButtonText: 'Cancelar',
                inputValidator: (value) => {
                    if (!value) {
                        return 'Debe ingresar una dirección de correo';
                    }
                    // Validación básica de formato de email
                    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
                    if (!emailRegex.test(value)) {
                        return 'Por favor ingrese un correo válido';
                    }
                }
            }).then((result) => {
                if (result.isConfirmed) {
                    const email = result.value;

                    Swal.fire({
                        title: 'Generando respaldo y enviando por correo',
                        text: 'Por favor espere...',
                        allowOutsideClick: false,
                        allowEscapeKey: false,
                        showConfirmButton: false,
                        didOpen: () => {
                            Swal.showLoading();
                        }
                    });

                    $.ajax({
                        type: 'POST',
                        url: 'includes/system/dump.php',
                        data: {
                            mode: 'email',
                            email: email
                        },
                        dataType: 'text',
                        success: function(response) {
                            Swal.fire({
                                title: 'Operación Completada',
                                text: response || 'El respaldo fue generado y enviado por correo electrónico.',
                                icon: 'success'
                            });
                        },
                        error: function(xhr, status, error) {
                            Swal.fire({
                                title: 'Error',
                                text: 'Ocurrió un error al generar o enviar el respaldo: ' + error,
                                icon: 'error'
                            });
                        }
                    });
                }
            });
        });
    });
</script>