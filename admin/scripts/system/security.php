<script>
    // filepath: c:\laragon\www\PH-JS-Kickstart\admin\scripts\system\security.php
    $(document).ready(function() {
        // Inicializar la tabla de usuarios con estado 2FA
        var table = $('#users_2fa_status').DataTable({
            ajax: {
                url: 'includes/system/security.php',
                type: 'GET',
                data: function(d) {
                    d.action = 'get_users_2fa_status';
                }
            },
            columns: [{
                    data: 'username'
                },
                {
                    data: 'nombre'
                },
                {
                    data: 'tfa_enabled',
                    render: function(data, type, row) {
                        if (type === 'display') {
                            if (parseInt(data) === 1) {
                                return '<span class="badge bg-success">Activado</span>';
                            } else {
                                return '<span class="badge bg-danger">Desactivado</span>';
                            }
                        }
                        return data;
                    },
                    className: 'text-center'
                }
            ],
            autoWidth: false,
            responsive: true,
            searching: true,
            ordering: true,
            paging: true,
            info: true,
            language: {
                url: '../dist/js/spanish.json'
            }
        });

        // Cargar configuración de seguridad actual
        $.ajax({
            url: 'includes/system/security.php',
            type: 'GET',
            data: {
                action: 'get_security_settings'
            },
            dataType: 'json',
            success: function(response) {
                if (response.status) {
                    // Establecer valores en los campos
                    if (response.data) {
                        $('#enforce_admin_2fa').prop('checked', response.data.enforce_admin_2fa == 1);
                        $('#login_attempts').val(response.data.login_attempts);
                        $('#session_time').val(response.data.session_time);
                        $('#lock_duration').val(response.data.lock_duration);
                    }
                } else {
                    toastr.error('Error al cargar la configuración: ' + response.message);
                }
            },
            error: function() {
                toastr.error('Error de conexión al cargar la configuración');
            }
        });

        // Manejar guardado de configuración
        $('#save_security_settings').on('click', function() {
            const settings = {
                enforce_admin_2fa: $('#enforce_admin_2fa').is(':checked') ? 1 : 0,
                login_attempts: $('#login_attempts').val(),
                session_time: $('#session_time').val(),
                lock_duration: $('#lock_duration').val()
            };

            Swal.fire({
                title: '¿Guardar configuración?',
                text: 'Se aplicarán los cambios de configuración de seguridad',
                icon: 'question',
                showCancelButton: true,
                confirmButtonText: 'Guardar',
                cancelButtonText: 'Cancelar'
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        url: 'includes/system/security.php',
                        type: 'POST',
                        data: {
                            action: 'save_security_settings',
                            settings: settings
                        },
                        dataType: 'json',
                        success: function(response) {
                            if (response.status) {
                                Swal.fire({
                                    title: 'Éxito',
                                    text: response.message,
                                    icon: 'success',
                                    timer: 2000,
                                    showConfirmButton: false
                                });
                            } else {
                                Swal.fire({
                                    title: 'Error',
                                    text: response.message,
                                    icon: 'error'
                                });
                            }
                        },
                        error: function() {
                            Swal.fire({
                                title: 'Error',
                                text: 'Error de conexión al guardar la configuración',
                                icon: 'error'
                            });
                        }
                    });
                }
            });
        });
    });
</script>