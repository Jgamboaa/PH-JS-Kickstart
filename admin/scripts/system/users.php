<script>
    $(document).ready(function() {
        var table = $('#admins').DataTable({
            ajax: {
                url: 'includes/system/users.php',
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
                    data: 'mfa_status',
                    className: 'text-center'
                },
                {
                    data: 'mfa_required',
                    className: 'text-center'
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

            // Ocultar solo la sección avanzada de 2FA, dejando visible el selector de requerido
            $('#mfa_advanced_section').addClass('d-none');

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

            // Mostrar la sección avanzada de 2FA para edición
            $('#mfa_advanced_section').removeClass('d-none');

            $.ajax({
                type: 'POST',
                url: 'includes/system/users.php',
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

                    // Configuración MFA
                    $('#tfa_required').val(response.tfa_required);

                    // Mostrar estado actual de MFA
                    if (response.tfa_enabled == 1) {
                        $('#mfa_status_badge').removeClass('badge-danger').addClass('badge-success').text('Activado');
                    } else {
                        $('#mfa_status_badge').removeClass('badge-success').addClass('badge-danger').text('Desactivado');
                    }

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

        // Al abrir modal para nuevo usuario, ocultar sección MFA
        $('#addnew').on('click', function() {
            $('#mfa_section').addClass('d-none');
        });

        $('#admin_form').on('submit', function(e) {
            e.preventDefault();
            var formData = new FormData(this);
            $.ajax({
                type: 'POST',
                url: 'includes/system/users.php',
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
                        url: 'includes/system/users.php',
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

        $('.btn-backup').click(function(e) {
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

        // Manejo para restablecer el MFA de un usuario
        $('#admins').on('click', '.reset-2fa', function() {
            var id = $(this).data('id');

            Swal.fire({
                title: '¿Restablecer MFA?',
                text: 'Se desactivará la autenticación de dos factores para este usuario y se eliminarán sus códigos de respaldo. El usuario necesitará configurar MFA nuevamente.',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Sí, restablecer',
                cancelButtonText: 'Cancelar'
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        type: 'POST',
                        url: 'includes/system/users.php',
                        data: {
                            crud: 'reset_mfa',
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

        // Manejo para generar nuevos códigos de respaldo
        $('#admins').on('click', '.new-backup-codes', function() {
            var id = $(this).data('id');

            Swal.fire({
                title: '¿Generar nuevos códigos?',
                text: 'Se generarán nuevos códigos de respaldo para este usuario. Los códigos anteriores dejarán de funcionar.',
                icon: 'question',
                showCancelButton: true,
                confirmButtonText: 'Sí, generar',
                cancelButtonText: 'Cancelar'
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        type: 'POST',
                        url: 'includes/system/users.php',
                        data: {
                            crud: 'generate_backup_codes',
                            id: id
                        },
                        dataType: 'json',
                        success: function(response) {
                            if (response.status) {
                                // Mostrar los códigos generados
                                $('#backup_codes_list').empty();
                                $.each(response.backup_codes, function(index, code) {
                                    $('#backup_codes_list').append('<li>' + code + '</li>');
                                });
                                $('#backup_codes_modal').modal('show');
                            } else {
                                Swal.fire(response.message, '', 'error');
                            }
                        }
                    });
                }
            });
        });

        // Copiar códigos de respaldo al portapapeles
        $('#copy_backup_codes').on('click', function() {
            var codes = [];
            $('#backup_codes_list li').each(function() {
                codes.push($(this).text());
            });

            var codesText = codes.join('\n');

            // Crear un elemento de texto temporal
            var $temp = $('<textarea>');
            $('body').append($temp);
            $temp.val(codesText).select();

            // Copiar al portapapeles
            document.execCommand('copy');
            $temp.remove();

            // Notificar al usuario
            Swal.fire({
                title: 'Copiado',
                text: 'Códigos copiados al portapapeles',
                icon: 'success',
                timer: 1500,
                showConfirmButton: false
            });
        });

        // Cambiar el estado de MFA requerido
        $('#admins').on('click', '.mfa-required-badge', function() {
            var id = $(this).data('id');
            var currentRequired = $(this).data('required');
            var newRequired = currentRequired ? 0 : 1;
            var actionText = newRequired ? 'hacer obligatorio' : 'hacer opcional';

            Swal.fire({
                title: '¿Cambiar MFA requerido?',
                text: '¿Está seguro que desea ' + actionText + ' el MFA para este usuario?',
                icon: 'question',
                showCancelButton: true,
                confirmButtonText: 'Sí, cambiar',
                cancelButtonText: 'Cancelar'
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        type: 'POST',
                        url: 'includes/system/users.php',
                        data: {
                            crud: 'update_mfa_required',
                            id: id,
                            required: newRequired
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

        // Eventos para botones de 2FA dentro del modal
        $('#btn_reset_mfa').on('click', function(e) {
            e.preventDefault();
            var userId = $('#admin_id').val();

            Swal.fire({
                title: '¿Restablecer MFA?',
                text: 'Se desactivará la autenticación de dos factores para este usuario y se eliminarán sus códigos de respaldo.',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Sí, restablecer',
                cancelButtonText: 'Cancelar'
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        type: 'POST',
                        url: 'includes/system/users.php',
                        data: {
                            crud: 'reset_mfa',
                            id: userId
                        },
                        dataType: 'json',
                        success: function(response) {
                            if (response.status) {
                                // Actualizar el badge de estado
                                $('#mfa_status_badge').removeClass('badge-success').addClass('badge-danger').text('Desactivado');
                                Swal.fire('¡Éxito!', response.message, 'success');
                            } else {
                                Swal.fire('Error', response.message, 'error');
                            }
                        }
                    });
                }
            });
        });

        $('#btn_generate_codes').on('click', function(e) {
            e.preventDefault();
            var userId = $('#admin_id').val();

            Swal.fire({
                title: '¿Generar nuevos códigos?',
                text: 'Se generarán nuevos códigos de respaldo para este usuario. Los códigos anteriores dejarán de funcionar.',
                icon: 'question',
                showCancelButton: true,
                confirmButtonText: 'Sí, generar',
                cancelButtonText: 'Cancelar'
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        type: 'POST',
                        url: 'includes/system/users.php',
                        data: {
                            crud: 'generate_backup_codes',
                            id: userId
                        },
                        dataType: 'json',
                        success: function(response) {
                            if (response.status) {
                                // Mostrar los códigos generados
                                $('#backup_codes_list').empty();
                                $.each(response.backup_codes, function(index, code) {
                                    $('#backup_codes_list').append('<li>' + code + '</li>');
                                });
                                $('#backup_codes_modal').modal('show');
                            } else {
                                Swal.fire('Error', response.message, 'error');
                            }
                        }
                    });
                }
            });
        });
    });
</script>