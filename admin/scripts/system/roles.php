<script>
    $(document).ready(function() {
        loadTable();

        // Abrir modal para crear rol
        $('#add').on('click', function() {
            $('#createRoleForm')[0].reset();
            $('#createRoleModal').modal('show');
        });

        // Guardar nuevo rol
        $('#saveRoleBtn').on('click', function() {
            const nombre = $('#nombre').val();

            if (!nombre) {
                Swal.fire('Error', 'Por favor, completa el campo nombre', 'error');
                return;
            }

            manageRole('create', {
                nombre
            });
            $('#createRoleModal').modal('hide');
        });

        // Abrir modal para editar rol
        $('#roles').on('click', '.edit-btn', function() {
            const id = $(this).data('id');

            $.post('includes/system/roles.php', {
                crud: 'get',
                id
            }, function(data) {
                const rol = JSON.parse(data);

                $('#edit-id').val(rol.id);
                $('#edit-nombre').val(rol.nombre);
                $('#editRoleModal').modal('show');
            });
        });

        // Actualizar rol
        $('#updateRoleBtn').on('click', function() {
            const id = $('#edit-id').val();
            const nombre = $('#edit-nombre').val();

            if (!nombre) {
                Swal.fire('Error', 'Por favor, completa el campo nombre', 'error');
                return;
            }

            manageRole('edit', {
                id,
                nombre
            });
            $('#editRoleModal').modal('hide');
        });

        // Eliminar rol (añadiendo confirmación con SweetAlert)
        $('#roles').on('click', '.delete-btn', function() {
            const id = $(this).data('id');

            Swal.fire({
                title: '¿Estás seguro?',
                text: 'Este rol será eliminado del sistema.',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Sí, eliminar',
                cancelButtonText: 'Cancelar'
            }).then((result) => {
                if (result.isConfirmed) {
                    manageRole('delete', {
                        id
                    });
                }
            });
        });
    });

    function loadTable() {
        const table = $('#roles').DataTable({
            ajax: 'includes/system/roles.php?crud=fetch',
            columns: [{
                    data: 'id'
                },
                {
                    data: 'nombre'
                },
                {
                    data: 'actions',
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

        table.on('xhr', function() {
            const currentPage = table.page();
            table.page(currentPage).draw(false);
        });
    }

    function manageRole(crud, data) {
        const table = $('#roles').DataTable();
        const currentPage = table.page();

        $.post('includes/system/roles.php', {
            crud,
            ...data
        }, function(response) {
            // Usar SweetAlert para mostrar notificaciones, como en users.php
            Swal.fire(response.message, '', response.status ? 'success' : 'error');
            table.ajax.reload(null, false); // Recarga la tabla sin cambiar de página
        }, 'json');
    }
</script>
</div>