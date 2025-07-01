<script>
    $(document).ready(function() {
        // Inicializar DataTable para mostrar lista de clientes
        var table = $('#table').DataTable({
            ajax: {
                url: 'includes/crud/crud.php',
                type: 'GET',
                data: function(d) {
                    d.crud = 'fetch';
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
                    data: 'actions',
                    orderable: false
                }
            ],
            autoWidth: false,
            ordering: false,
            stateSave: true,
            language: {
                url: '../dist/js/spanish.json'
            }
        });

        // Evento para botón de agregar nuevo cliente
        $('#add').on('click', function() {
            $('#client_form')[0].reset();
            $('#client_crud').val('create');
            $('#client_id').val('');
            $('#client_modalLabel').text('Agregar Cliente');
            $('#client_modal').modal('show');
        });

        // Evento para botón de editar cliente
        $('#table').on('click', '.edit-btn', function() {
            var id = $(this).data('id');
            $('#client_form')[0].reset();
            $('#client_crud').val('edit');
            $('#client_modalLabel').text('Editar Cliente');
            $('#client_id').val(id);

            $.ajax({
                type: 'POST',
                url: 'includes/crud/crud.php',
                data: {
                    crud: 'get',
                    id: id
                },
                dataType: 'json',
                success: function(response) {
                    if (response.status) {
                        // Rellenar los campos del formulario con los datos del cliente
                        $('#name').val(response.data.name);
                        $('#email').val(response.data.email);
                        $('#phone').val(response.data.phone);
                        $('#client_modal').modal('show');
                    } else {
                        Swal.fire(response.message, '', 'error');
                    }
                },
                error: function(jqXHR, textStatus, errorThrown) {
                    console.log('Error AJAX:', textStatus, errorThrown);
                    console.log('Respuesta del servidor:', jqXHR.responseText);
                    Swal.fire('Error en la solicitud AJAX', '', 'error');
                }
            });
        });

        // Evento para enviar formulario (crear o editar)
        $('#client_form').on('submit', function(e) {
            e.preventDefault();
            var formData = $(this).serialize();
            $.ajax({
                type: 'POST',
                url: 'includes/crud/crud.php',
                data: formData,
                dataType: 'json',
                success: function(response) {
                    $('#client_modal').modal('hide');
                    Swal.fire(response.message, '', response.status ? 'success' : 'error');
                    $('#table').DataTable().ajax.reload(null, false);
                },
                error: function(jqXHR, textStatus, errorThrown) {
                    console.log('Error AJAX:', textStatus, errorThrown);
                    Swal.fire('Error en la solicitud AJAX', '', 'error');
                }
            });
        });

        // Evento para botón de eliminar cliente
        $('#table').on('click', '.delete-btn', function() {
            var id = $(this).data('id');
            Swal.fire({
                title: '¿Estás seguro?',
                text: 'Este cliente será eliminado permanentemente.',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Sí, eliminar',
                cancelButtonText: 'Cancelar'
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        type: 'POST',
                        url: 'includes/crud/crud.php',
                        data: {
                            crud: 'delete',
                            id: id
                        },
                        dataType: 'json',
                        success: function(response) {
                            Swal.fire(response.message, '', response.status ? 'success' : 'error');
                            $('#table').DataTable().ajax.reload(null, false);
                        },
                        error: function(jqXHR, textStatus, errorThrown) {
                            console.log('Error AJAX:', textStatus, errorThrown);
                            Swal.fire('Error en la solicitud AJAX', '', 'error');
                        }
                    });
                }
            });
        });
    });
</script>