<script>
    $(document).ready(function() {
        loadTable();

        $('#add').on('click', function() {
            Swal.fire({
                title: 'Añadir Nuevo Rol',
                html: '<input type="text" id="nombre" class="form-control" placeholder="Nombre">',
                confirmButtonText: 'Guardar',
                showCancelButton: true,
                preConfirm: () => {
                    const nombre = Swal.getPopup().querySelector('#nombre').value;
                    if (!nombre) {
                        Swal.showValidationMessage('Por favor, completa el campo');
                    }
                    return {
                        nombre
                    };
                }
            }).then((result) => {
                if (result.isConfirmed) {
                    manageRole('create', result.value);
                }
            });
        });

        $('#roles').on('click', '.edit-btn', function() {
            const id = $(this).data('id');
            $.post('includes/system/roles_crud.php', {
                crud: 'get',
                id
            }, function(data) {
                const rol = JSON.parse(data);
                Swal.fire({
                    title: 'Editar Rol',
                    html: `
                        <input type="hidden" id="edit-id" value="${rol.id}">
                        <input type="text" id="edit-nombre" class="form-control" value="${rol.nombre}">
                    `,
                    confirmButtonText: 'Actualizar',
                    showCancelButton: true,
                    preConfirm: () => {
                        const id = Swal.getPopup().querySelector('#edit-id').value;
                        const nombre = Swal.getPopup().querySelector('#edit-nombre').value;
                        if (!nombre) {
                            Swal.showValidationMessage('Por favor, completa el campo');
                        }
                        return {
                            id,
                            nombre
                        };
                    }
                }).then((result) => {
                    if (result.isConfirmed) {
                        manageRole('edit', result.value);
                    }
                });
            });
        });
    });

    function loadTable() {
        const table = $('#roles').DataTable({
            ajax: 'includes/system/roles_crud.php?crud=fetch',
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

        $.post('includes/system/roles_crud.php', {
            crud,
            ...data
        }, function(response) {
            Swal.fire(response.message, '', response.status ? 'success' : 'error');
            table.ajax.reload(null, false); // Recarga la tabla sin cambiar de página
        }, 'json');
    }
</script>
</div>