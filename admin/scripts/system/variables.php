<script>
    $(function() {
        const variablesTable = $('#variablesTable').DataTable({
            autoWidth: false,
            ordering: false,
            stateSave: true,
            language: {
                url: '../dist/js/spanish.json'
            },
            ajax: {
                url: 'includes/system/variables.php?crud=fetch',
                dataSrc: function(json) {
                    if (json.status && json.variables) {
                        return json.variables;
                    }
                    return [];
                }
            },
            columns: [{
                    data: 'name'
                },
                {
                    data: 'value',
                    render: function(data, type, row) {
                        // Para variables que podrían contener información sensible como API_TOKENS o contraseñas
                        if (row.name.includes('PASSWORD') || row.name.includes('SECRET') || row.name.includes('KEY') || row.name.includes('TOKEN')) {
                            return '********';
                        }
                        return data;
                    }
                },
                {
                    data: null,
                    orderable: false,
                    render: function(data, type, row) {
                        // Verificar si es una variable protegida (no se puede eliminar)
                        const isProtected = row.name.startsWith('APP_') ||
                            row.name.startsWith('MAIL_') ||
                            row.name.startsWith('DB_') ||
                            row.name.startsWith('DATABASE_');

                        const deleteButton = isProtected ?
                            '' :
                            `<button type="button" class="btn btn-sm btn-danger delete-variable" data-name="${row.name}">
                                <i class="fa fa-solid fa-duotone fa-lg fa-trash-xmark"></i>
                            </button>`;

                        return `
                        <div class="btn-group">
                            <button type="button" class="btn btn-sm btn-success edit-variable" data-name="${row.name}" data-value="${row.value}">
                                <i class="fa fa-solid fa-duotone fa-lg fa-pen"></i>
                            </button>
                            ${deleteButton}
                        </div>
                        `;
                    },
                    className: 'text-center'
                }
            ]
        });

        // Botón para agregar nueva variable
        $('#addVariableBtn').on('click', function() {
            $('#modalOperation').val('create');
            $('#variableModalLabel').text('Agregar Variable de Entorno');
            $('#variableName').prop('readonly', false);
            $('#variableForm')[0].reset();
        });

        // Botón para editar variable existente
        $(document).on('click', '.edit-variable', function() {
            const name = $(this).data('name');
            const value = $(this).data('value');

            $('#modalOperation').val('update');
            $('#variableModalLabel').text('Editar Variable de Entorno');
            $('#variableName').val(name).prop('readonly', true);
            $('#variableValue').val(value);
            $('#variableModal').modal('show');
        });

        // Manejo unificado del formulario (crear/editar)
        $('#variableForm').on('submit', function(e) {
            e.preventDefault();

            const operation = $('#modalOperation').val();
            const name = $('#variableName').val().trim();
            const value = $('#variableValue').val().trim();

            if (!name) {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'El nombre de la variable no puede estar vacío'
                });
                return;
            }

            $.ajax({
                url: 'includes/system/variables.php',
                type: 'POST',
                data: {
                    crud: operation, // 'create' o 'update'
                    name: name,
                    value: value
                },
                dataType: 'json',
                success: function(response) {
                    if (response.status) {
                        $('#variableModal').modal('hide');

                        Swal.fire({
                            icon: 'success',
                            title: 'Éxito',
                            text: response.message
                        }).then(() => {
                            variablesTable.ajax.reload(null, false);
                        });
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: response.message
                        });
                    }
                },
                error: function() {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: 'Ocurrió un error durante la operación'
                    });
                }
            });
        });

        // Eliminar variable
        $(document).on('click', '.delete-variable', function() {
            const name = $(this).data('name');

            Swal.fire({
                title: '¿Estás seguro?',
                text: `¿Deseas eliminar la variable ${name}?`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Sí, eliminar',
                cancelButtonText: 'Cancelar'
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        url: 'includes/system/variables.php',
                        type: 'POST',
                        data: {
                            crud: 'delete',
                            name: name
                        },
                        dataType: 'json',
                        success: function(response) {
                            if (response.status) {
                                Swal.fire({
                                    icon: 'success',
                                    title: 'Éxito',
                                    text: response.message
                                }).then(() => {
                                    variablesTable.ajax.reload(null, false);
                                });
                            } else {
                                Swal.fire({
                                    icon: 'error',
                                    title: 'Error',
                                    text: response.message
                                });
                            }
                        },
                        error: function() {
                            Swal.fire({
                                icon: 'error',
                                title: 'Error',
                                text: 'Ocurrió un error durante la operación'
                            });
                        }
                    });
                }
            });
        });

        // Reset formularios al cerrar modal
        $('#variableModal').on('hidden.bs.modal', function() {
            $('#variableForm')[0].reset();
        });
    });
</script>