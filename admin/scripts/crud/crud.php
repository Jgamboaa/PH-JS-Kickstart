<script>
    $(document).ready(function() {
        // Inicializar Mermaid para diagramas de flujo
        mermaid.initialize({
            startOnLoad: true,
            theme: 'neutral',
            flowchart: {
                useMaxWidth: true,
                htmlLabels: true
            }
        });

        // Inicializar tooltips
        $('[data-toggle="tooltip"]').tooltip();

        // Contador de operaciones realizadas (para fines didácticos)
        let operationCounter = {
            create: 0,
            read: 1, // La carga inicial cuenta como una operación de lectura
            update: 0,
            delete: 0,
            total: function() {
                return this.create + this.read + this.update + this.delete;
            }
        };

        // Actualizar contador visual
        function updateCounter() {
            $('#crud-counter').html(`
                <span class="badge badge-primary">Create: ${operationCounter.create}</span>
                <span class="badge badge-info">Read: ${operationCounter.read}</span>
                <span class="badge badge-success">Update: ${operationCounter.update}</span>
                <span class="badge badge-danger">Delete: ${operationCounter.delete}</span>
                <span class="badge badge-dark">Total: ${operationCounter.total()}</span>
            `);
        }

        // Mostrar/ocultar ayuda de la tabla
        $('#toggle-help').click(function() {
            $('.help-text').toggle();
        });

        // Mostrar/ocultar diagrama de flujo
        $('#show-flow').click(function() {
            $('#data-flow-container').toggle();
            // Re-renderizar diagramas al mostrar
            if ($('#data-flow-container').is(':visible')) {
                mermaid.init(undefined, $('.mermaid'));
            }
        });

        // Mostrar modal informativo
        $('#show-info-modal').click(function() {
            $('#info_modal').modal('show');
        });

        // Inicializar DataTable para mostrar lista de clientes
        // [Explicación] DataTables es una biblioteca que mejora las tablas HTML
        // añadiendo funciones como ordenamiento, búsqueda y paginación
        var table = $('#table').DataTable({
            ajax: {
                url: 'includes/crud/crud.php',
                type: 'GET',
                data: function(d) {
                    d.crud = 'fetch';
                },
                // [Explicación] Usamos este callback para mostrar un mensaje de carga
                beforeSend: function() {
                    $('#table').addClass('loading-data');
                    $('#table_wrapper').append('<div class="overlay-loading"><i class="fas fa-spinner fa-spin"></i> Cargando datos...</div>');
                },
                // [Explicación] Este callback se ejecuta cuando la petición se completa
                complete: function() {
                    $('#table').removeClass('loading-data');
                    $('.overlay-loading').remove();
                    operationCounter.read++;
                    updateCounter();
                }
            },
            columns: [{
                    data: 'id'
                },
                {
                    data: 'name'
                },
                {
                    data: 'email'
                },
                {
                    data: 'phone'
                },
                {
                    data: 'created_at'
                },
                {
                    data: 'updated_at'
                },
                {
                    data: null,
                    render: function(data, type, row) {
                        // [Explicación] Renderizamos los botones de acción con tooltips
                        return `
                            <div class="btn-group">
                                <button class="btn btn-success btn-sm edit-btn" data-id="${row.id}" 
                                    data-toggle="tooltip" title="Editar este cliente">
                                    <i class="fa fa-solid fa-duotone fa-lg fa-pen"></i>
                                </button>
                                <button class="btn btn-danger btn-sm delete-btn" data-id="${row.id}"
                                    data-toggle="tooltip" title="Eliminar este cliente">
                                    <i class="fa fa-solid fa-duotone fa-lg fa-trash"></i>
                                </button>
                                <button class="btn btn-info btn-sm info-btn" data-id="${row.id}"
                                    data-toggle="tooltip" title="Ver flujo de datos">
                                    <i class="fa fa-solid fa-duotone fa-lg fa-info"></i>
                                </button>
                            </div>
                        `;
                    },
                    className: 'text-center'
                }
            ],
            // [Explicación] Personalización de la tabla
            autoWidth: false,
            ordering: false,
            order: [
                [0, 'desc']
            ], // Ordenar por ID descendente
            stateSave: true,
            language: {
                url: '../dist/js/spanish.json'
            },
            // [Explicación] Callback cuando la tabla está completamente inicializada
            initComplete: function() {
                // Activar tooltips en los botones de la tabla
                $('#table [data-toggle="tooltip"]').tooltip();

                // Añadir información sobre la tabla
                let tableInfo = `
                    <div class="mt-3 text-muted">
                        <small><i class="fas fa-info-circle"></i> 
                        La tabla se carga mediante una solicitud AJAX a <code>includes/crud/crud.php?crud=fetch</code> 
                        que devuelve los datos en formato JSON.</small>
                    </div>
                `;
                $(tableInfo).insertAfter('#table_wrapper');
            },
            // [Explicación] Personalización del DOM de la tabla
            dom: '<"row"<"col-sm-12 col-md-6"l><"col-sm-12 col-md-6"f>>' +
                '<"row"<"col-sm-12"tr>>' +
                '<"row"<"col-sm-12 col-md-5"i><"col-sm-12 col-md-7"p>>' +
                '<"row"<"col-sm-12"<"crud-counter text-center mt-2">>>',
            // [Explicación] Callback cuando se dibuja la tabla
            drawCallback: function() {
                // Actualizar tooltips
                $('#table [data-toggle="tooltip"]').tooltip();

                // Actualizar contador de operaciones
                $('#crud-counter').html(`
                    <div id="crud-counter">
                        <span class="badge badge-primary">Create: ${operationCounter.create}</span>
                        <span class="badge badge-info">Read: ${operationCounter.read}</span>
                        <span class="badge badge-success">Update: ${operationCounter.update}</span>
                        <span class="badge badge-danger">Delete: ${operationCounter.delete}</span>
                        <span class="badge badge-dark">Total: ${operationCounter.total()}</span>
                    </div>
                `);
            }
        });

        // [Explicación] Evento para botón de agregar nuevo cliente
        // Prepara el formulario para una operación de creación
        $('#add').on('click', function() {
            // [Explicación] Reseteamos el formulario y preparamos la operación CREATE
            $('#client_form')[0].reset();
            $('#client_crud').val('create');
            $('#client_id').val('');
            $('#client_modalLabel').html('<i class="fas fa-plus-circle"></i> Agregar Cliente <span class="badge badge-primary">CREATE</span>');
            $('#client_modal').modal('show');

            // Destacar visualmente la operación actual
            $('.modal-header').removeClass('bg-success').addClass('bg-primary');
            $('.modal-title').removeClass('text-success').addClass('text-white');
        });

        // [Explicación] Evento para botón de editar cliente
        // Carga los datos del cliente y prepara el formulario para actualización
        $('#table').on('click', '.edit-btn', function() {
            var id = $(this).data('id');

            // [Explicación] Preparamos el formulario para una operación UPDATE
            $('#client_form')[0].reset();
            $('#client_crud').val('edit');
            $('#client_modalLabel').html('<i class="fas fa-edit"></i> Editar Cliente <span class="badge badge-success">UPDATE</span>');
            $('#client_id').val(id);

            // Mostrar indicador de carga
            Swal.fire({
                title: 'Cargando datos...',
                html: 'Realizando solicitud AJAX a <code>includes/crud/crud.php</code>',
                allowOutsideClick: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });

            // [Explicación] Realizamos una solicitud AJAX para obtener los datos del cliente
            $.ajax({
                type: 'POST',
                url: 'includes/crud/crud.php',
                data: {
                    crud: 'get',
                    id: id
                },
                dataType: 'json',
                success: function(response) {
                    Swal.close(); // Cerrar indicador de carga

                    if (response.status) {
                        // [Explicación] Rellenamos el formulario con los datos recibidos
                        $('#name').val(response.data.name);
                        $('#email').val(response.data.email);
                        $('#phone').val(response.data.phone);

                        // Destacar visualmente la operación actual
                        $('.modal-header').removeClass('bg-primary').addClass('bg-success');
                        $('.modal-title').removeClass('text-primary').addClass('text-white');

                        // Añadir una notificación sutil dentro del modal
                        let notificationHtml = `
                            <div class="alert alert-success alert-dismissible fade show" role="alert">
                                <i class="fas fa-check-circle mr-2"></i>Datos cargados correctamente para <strong>${response.data.name}</strong>
                                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                    <span aria-hidden="true">&times;</span>
                                </button>
                            </div>
                        `;

                        // Insertar la notificación al principio del formulario
                        $('#client_form').prepend(notificationHtml);

                        // Programar la desaparición automática de la alerta después de 3 segundos
                        setTimeout(function() {
                            $('.alert').alert('close');
                        }, 3000);

                        // Mostrar el modal inmediatamente
                        $('#client_modal').modal('show');

                    } else {
                        Swal.fire({
                            title: response.message,
                            icon: 'error',
                            text: 'No se pudo cargar la información del cliente'
                        });
                    }
                },
                error: function(jqXHR, textStatus, errorThrown) {
                    Swal.close(); // Cerrar indicador de carga
                    console.log('Error AJAX:', textStatus, errorThrown);
                    console.log('Respuesta del servidor:', jqXHR.responseText);

                    // [Explicación] Mostramos información detallada del error
                    Swal.fire({
                        title: 'Error en la solicitud AJAX',
                        icon: 'error',
                        html: `
                            <div class="text-left">
                                <p><strong>Tipo de error:</strong> ${textStatus}</p>
                                <p><strong>Mensaje:</strong> ${errorThrown}</p>
                                <p><strong>Respuesta:</strong> <pre>${jqXHR.responseText}</pre></p>
                            </div>
                        `
                    });
                }
            });
        });

        // [Explicación] Mostrar información sobre el flujo de datos al hacer clic en el botón info
        $('#table').on('click', '.info-btn', function() {
            var id = $(this).data('id');
            var row = table.row($(this).closest('tr')).data();

            $('#info_modal').modal('show');
        });

        // [Explicación] Evento para enviar formulario (crear o editar)
        $('#client_form').on('submit', function(e) {
            e.preventDefault();

            // Mostrar información antes de enviar
            var operationType = $('#client_crud').val() === 'create' ? 'creación' : 'actualización';

            Swal.fire({
                title: `Procesando ${operationType}...`,
                html: `
                    <div class="text-left">
                        <p>Enviando datos del formulario mediante AJAX:</p>
                        <ul>
                            <li>Nombre: <strong>${$('#name').val()}</strong></li>
                            <li>Email: <strong>${$('#email').val()}</strong></li>
                            <li>Teléfono: <strong>${$('#phone').val() || 'No proporcionado'}</strong></li>
                        </ul>
                    </div>
                `,
                allowOutsideClick: false,
                didOpen: () => {
                    Swal.showLoading();
                },
                timer: 1500,
                timerProgressBar: true
            });

            var formData = $(this).serialize();

            // [Explicación] Realizamos la petición AJAX para guardar los datos
            $.ajax({
                type: 'POST',
                url: 'includes/crud/crud.php',
                data: formData,
                dataType: 'json',
                success: function(response) {
                    $('#client_modal').modal('hide');

                    // Actualizar contadores
                    if ($('#client_crud').val() === 'create' && response.status) {
                        operationCounter.create++;
                    } else if ($('#client_crud').val() === 'edit' && response.status) {
                        operationCounter.update++;
                    }
                    updateCounter();

                    // [Explicación] Mostramos la respuesta del servidor
                    Swal.fire({
                        title: response.message,
                        icon: response.status ? 'success' : 'error',
                        html: `
                            <div class="text-left">
                                <p><strong>Operación:</strong> ${operationType}</p>
                                <p><strong>Estado:</strong> ${response.status ? 'Exitoso' : 'Fallido'}</p>
                                <p><strong>Respuesta JSON:</strong></p>
                                <pre>${JSON.stringify(response, null, 2)}</pre>
                            </div>
                        `
                    });

                    // [Explicación] Recargar la tabla para reflejar los cambios
                    $('#table').DataTable().ajax.reload(null, false);
                },
                error: function(jqXHR, textStatus, errorThrown) {
                    console.log('Error AJAX:', textStatus, errorThrown);

                    // [Explicación] Mostramos información detallada del error
                    Swal.fire({
                        title: 'Error en la solicitud AJAX',
                        icon: 'error',
                        html: `
                            <div class="text-left">
                                <p><strong>Tipo de error:</strong> ${textStatus}</p>
                                <p><strong>Mensaje:</strong> ${errorThrown}</p>
                                <p><strong>Respuesta:</strong></p>
                                <pre>${jqXHR.responseText}</pre>
                            </div>
                        `
                    });
                }
            });
        });

        // [Explicación] Evento para botón de eliminar cliente
        $('#table').on('click', '.delete-btn', function() {
            var id = $(this).data('id');
            var row = table.row($(this).closest('tr')).data();

            // [Explicación] Mostramos un cuadro de diálogo de confirmación
            Swal.fire({
                title: '¿Estás seguro?',
                html: `
                    <div class="text-left">
                        <p>Vas a eliminar el cliente:</p>
                        <ul>
                            <li><strong>ID:</strong> ${row.id}</li>
                            <li><strong>Nombre:</strong> ${row.name}</li>
                            <li><strong>Email:</strong> ${row.email}</li>
                        </ul>
                        <p>Esta acción no se puede deshacer.</p>
                    </div>
                `,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: '<i class="fas fa-trash mr-1"></i> Sí, eliminar',
                cancelButtonText: '<i class="fas fa-times mr-1"></i> Cancelar',
                confirmButtonColor: '#dc3545',
                cancelButtonColor: '#6c757d',
                footer: '<span class="text-muted">Operación DELETE en la base de datos</span>'
            }).then((result) => {
                if (result.isConfirmed) {
                    // [Explicación] Mostrar indicador de progreso
                    Swal.fire({
                        title: 'Eliminando...',
                        html: 'Enviando solicitud DELETE a <code>includes/crud/crud.php</code>',
                        allowOutsideClick: false,
                        didOpen: () => {
                            Swal.showLoading();
                        },
                        timer: 1500,
                        timerProgressBar: true
                    });

                    // [Explicación] Realizamos la petición AJAX para eliminar
                    $.ajax({
                        type: 'POST',
                        url: 'includes/crud/crud.php',
                        data: {
                            crud: 'delete',
                            id: id
                        },
                        dataType: 'json',
                        success: function(response) {
                            // Actualizar contador
                            if (response.status) {
                                operationCounter.delete++;
                                updateCounter();
                            }

                            // [Explicación] Mostramos la respuesta del servidor
                            Swal.fire({
                                title: response.message,
                                icon: response.status ? 'success' : 'error',
                                html: `
                                    <div class="text-left">
                                        <p><strong>Operación:</strong> DELETE</p>
                                        <p><strong>ID:</strong> ${id}</p>
                                        <p><strong>Estado:</strong> ${response.status ? 'Exitoso' : 'Fallido'}</p>
                                    </div>
                                `
                            });

                            // [Explicación] Recargar la tabla para reflejar los cambios
                            $('#table').DataTable().ajax.reload(null, false);
                        },
                        error: function(jqXHR, textStatus, errorThrown) {
                            console.log('Error AJAX:', textStatus, errorThrown);

                            // [Explicación] Mostramos información detallada del error
                            Swal.fire({
                                title: 'Error en la solicitud AJAX',
                                icon: 'error',
                                html: `
                                    <div class="text-left">
                                        <p><strong>Tipo de error:</strong> ${textStatus}</p>
                                        <p><strong>Mensaje:</strong> ${errorThrown}</p>
                                        <p><strong>Respuesta:</strong></p>
                                        <pre>${jqXHR.responseText}</pre>
                                    </div>
                                `
                            });
                        }
                    });
                }
            });
        });
    });
</script>