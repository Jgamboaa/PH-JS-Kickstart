<script>
    $(document).ready(function() {
        loadTable();

        // Abrir modal para crear rol
        $('#add').on('click', function() {
            $('#roleForm')[0].reset();
            $('#role_crud').val('create');
            $('#role-id').val('');
            $('#roleModalLabel').text('Añadir Nuevo Rol');
            $('#roleModal').modal('show');
        });

        // Guardar rol (crear/editar según modo)
        $('#saveRoleBtn').on('click', function() {
            const nombre = $('#role-nombre').val();
            const crud = $('#role_crud').val();
            const id = $('#role-id').val();

            if (!nombre) {
                Swal.fire('Error', 'Por favor, completa el campo nombre', 'error');
                return;
            }

            const data = {
                nombre
            };
            if (crud === 'edit') {
                data.id = id;
            }

            manageRole(crud, data);
            $('#roleModal').modal('hide');
        });

        // Abrir modal para editar rol
        $('#roles').on('click', '.edit-btn', function() {
            const id = $(this).data('id');

            $.post('includes/system/roles.php', {
                crud: 'get',
                id
            }, function(data) {
                const rol = JSON.parse(data);

                $('#role-id').val(rol.id);
                $('#role-nombre').val(rol.nombre);
                $('#role_crud').val('edit');
                $('#roleModalLabel').text('Editar Rol');
                $('#roleModal').modal('show');
            });
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