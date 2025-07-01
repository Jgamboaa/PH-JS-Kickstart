<?php
include '../../includes/session.php';
// Incluir componentes para modales y campos de formulario
include '../../components/modal.php';
include '../../components/form_fields.php';

$admin_id = $user['id'];
$roles_ids = explode(',', $user['roles_ids']);

?>

<section class="content">
    <!-- Panel informativo sobre el módulo CRUD -->
    <div class="container-fluid content-header">
        <div class="card card-info">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-info-circle mr-2"></i>Módulo CRUD de Ejemplo</h3>
                <div class="card-tools">
                    <button type="button" class="btn btn-tool" data-card-widget="collapse">
                        <i class="fas fa-minus"></i>
                    </button>
                </div>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-4">
                        <div class="info-box bg-light">
                            <span class="info-box-icon bg-primary"><i class="fas fa-database"></i></span>
                            <div class="info-box-content">
                                <span class="info-box-text">Modelo</span>
                                <span class="info-box-number">RedBeanPHP</span>
                                <div class="progress">
                                    <div class="progress-bar bg-primary" style="width: 100%"></div>
                                </div>
                                <span class="progress-description">
                                    Gestión de datos con RedBeanPHP
                                </span>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="info-box bg-light">
                            <span class="info-box-icon bg-success"><i class="fas fa-cogs"></i></span>
                            <div class="info-box-content">
                                <span class="info-box-text">Controlador</span>
                                <span class="info-box-number">CrudCrudController</span>
                                <div class="progress">
                                    <div class="progress-bar bg-success" style="width: 100%"></div>
                                </div>
                                <span class="progress-description">
                                    Lógica de negocio en PHP
                                </span>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="info-box bg-light">
                            <span class="info-box-icon bg-warning"><i class="fas fa-desktop"></i></span>
                            <div class="info-box-content">
                                <span class="info-box-text">Vista</span>
                                <span class="info-box-number">DataTables + Bootstrap</span>
                                <div class="progress">
                                    <div class="progress-bar bg-warning" style="width: 100%"></div>
                                </div>
                                <span class="progress-description">
                                    Interfaz de usuario interactiva
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="callout callout-info mt-3">
                    <h5><i class="fas fa-lightbulb mr-2"></i>¿Cómo funciona este módulo?</h5>
                    <p>Este es un ejemplo didáctico de un CRUD (Create, Read, Update, Delete) implementado con PHP y JavaScript.
                        Utiliza AJAX para comunicarse con el servidor sin recargar la página y muestra los resultados en una tabla interactiva.</p>
                    <p>Observe las siguientes operaciones y cómo fluyen los datos:</p>
                    <ol>
                        <li><strong>Create:</strong> Al hacer clic en "+" se abre un modal para crear un nuevo registro</li>
                        <li><strong>Read:</strong> La tabla muestra los datos cargados desde la base de datos</li>
                        <li><strong>Update:</strong> Al hacer clic en el botón editar, se cargan los datos en el formulario</li>
                        <li><strong>Delete:</strong> Al hacer clic en eliminar, se muestra una confirmación antes de proceder</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>

    <div class="container-fluid content-header">
        <div class="row mb-2">
            <div class="col-sm-6">
                <a id="add" class="btn btn-primary" data-toggle="tooltip" title="Crear nuevo registro">
                    <i class="fa-solid fa-plus mr-1"></i> Nuevo Cliente
                </a>
                <button type="button" class="btn btn-info" id="show-flow">
                    <i class="fas fa-project-diagram mr-1"></i> Ver Flujo de Datos
                </button>
            </div>
            <div class="col-sm-6">
                <div class="float-sm-right">
                    <span class="badge badge-primary">Create</span>
                    <span class="badge badge-info">Read</span>
                    <span class="badge badge-success">Update</span>
                    <span class="badge badge-danger">Delete</span>
                </div>
            </div>
        </div>
    </div>

    <div class="container-fluid">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Administración de Clientes</h3>
                <div class="card-tools">
                    <button type="button" class="btn btn-sm btn-default" id="toggle-help">
                        <i class="fas fa-question-circle"></i> Mostrar/Ocultar Ayuda
                    </button>
                </div>
            </div>
            <div class="card-body">
                <div class="alert alert-info help-text" style="display:none;">
                    <h5><i class="icon fas fa-info"></i> Ayuda de la Tabla</h5>
                    <p>Esta tabla muestra todos los clientes registrados. Puede:</p>
                    <ul>
                        <li>Ordenar por columnas haciendo clic en los encabezados</li>
                        <li>Buscar usando el campo de búsqueda</li>
                        <li>Editar un registro haciendo clic en el botón <button class="btn btn-success btn-xs"><i class="fa-solid fa-pen"></i></button></li>
                        <li>Eliminar un registro haciendo clic en el botón <button class="btn btn-danger btn-xs"><i class="fa-solid fa-trash"></i></button></li>
                    </ul>
                </div>
                <table id="table" class="table table-bordered table-striped table-sm responsive">
                    <thead class="text-center">
                        <th>ID</th>
                        <th>Cliente</th>
                        <th>Correo</th>
                        <th>Teléfono</th>
                        <th>Creado</th>
                        <th>Actualizado</th>
                        <th>Acciones</th>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Diagrama de flujo de datos (oculto por defecto) -->
    <div class="container-fluid" id="data-flow-container" style="display:none;">
        <div class="card card-success">
            <div class="card-header">
                <h3 class="card-title">Flujo de Datos CRUD</h3>
                <div class="card-tools">
                    <button type="button" class="btn btn-tool" data-card-widget="collapse">
                        <i class="fas fa-minus"></i>
                    </button>
                </div>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-12 text-center">
                        <div class="mermaid">
                            graph LR
                            A[Vista - HTML/JS] -->|AJAX Request| B[includes/crud.php]
                            B -->|Método| C[CrudController]
                            C -->|RedBeanPHP| D[(Database)]
                            D -->|Data| C
                            C -->|JSON Response| B
                            B -->|JSON| A
                        </div>
                    </div>
                </div>
                <div class="row mt-4">
                    <div class="col-md-3">
                        <div class="card bg-light">
                            <div class="card-header">
                                <h3 class="card-title">1. Vista (Frontend)</h3>
                            </div>
                            <div class="card-body">
                                <p>HTML y JavaScript que genera la interfaz y maneja eventos.</p>
                                <code>views/crud/crud.php</code><br>
                                <code>scripts/crud/crud.php</code>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card bg-light">
                            <div class="card-header">
                                <h3 class="card-title">2. Punto de Entrada</h3>
                            </div>
                            <div class="card-body">
                                <p>Recibe las peticiones AJAX y llama al controlador.</p>
                                <code>includes/crud/crud.php</code>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card bg-light">
                            <div class="card-header">
                                <h3 class="card-title">3. Controlador</h3>
                            </div>
                            <div class="card-body">
                                <p>Contiene la lógica de negocio del CRUD.</p>
                                <code>controllers/crud/crud.php</code>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card bg-light">
                            <div class="card-header">
                                <h3 class="card-title">4. Base de Datos</h3>
                            </div>
                            <div class="card-body">
                                <p>Almacena los datos mediante RedBeanPHP.</p>
                                <code>Tabla: clients</code>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<?php
// Crear formulario para el modal de administración de clientes
$formContent = '
    <div class="alert alert-light border-info">
        <i class="fas fa-info-circle text-info"></i> Complete el formulario con los datos del cliente. Los campos marcados con * son obligatorios.
    </div>
    <form id="client_form">
        <!-- Campos ocultos para controlar la acción y el ID del cliente -->
        <input type="hidden" id="client_crud" name="crud">
        <input type="hidden" id="client_id" name="id">
        
        <div class="row">
            <div class="col-lg-12">
                ' . renderFormField([
    'type' => 'text',
    'name' => 'name',
    'id' => 'name',
    'label' => 'Nombre completo *',
    'required' => true,
    'help_text' => 'Ingrese el nombre completo del cliente',
    'icon' => 'fas fa-user'
]) . '
            </div>
        </div>
        
        <div class="row">
            <div class="col-lg-6">
                ' . renderFormField([
    'type' => 'email',
    'name' => 'email',
    'id' => 'email',
    'label' => 'Correo electrónico *',
    'required' => true,
    'help_text' => 'Formato: ejemplo@dominio.com',
    'icon' => 'fas fa-envelope'
]) . '
            </div>
            <div class="col-lg-6">
                ' . renderFormField([
    'type' => 'text',
    'name' => 'phone',
    'id' => 'phone',
    'label' => 'Teléfono',
    'help_text' => 'Formato recomendado: +00 000 000 000',
    'icon' => 'fas fa-phone'
]) . '
            </div>
        </div>
        
        <div class="card bg-light mt-3">
            <div class="card-body">
                <h6><i class="fas fa-code mr-2"></i>¿Qué sucede al guardar?</h6>
                <ol class="pl-3 mb-0">
                    <li>El formulario se envía mediante AJAX a <code>includes/crud/crud.php</code></li>
                    <li>Se determina si es una operación de creación o actualización</li>
                    <li>Se llama al método correspondiente en el controlador</li>
                    <li>El controlador guarda o actualiza los datos usando RedBeanPHP</li>
                    <li>Se devuelve una respuesta JSON con el resultado</li>
                </ol>
            </div>
        </div>
    </form>
';

// Botones para el footer del modal
$footerContent = '
    <button type="button" class="btn btn-default" data-dismiss="modal"><i class="fa fa-times mr-1"></i> Cerrar</button>
    <button type="submit" class="btn btn-primary" form="client_form"><i class="fa fa-save mr-1"></i> Guardar</button>
';

// Renderizar el modal principal
echo renderModal([
    'id' => 'client_modal',
    'title' => 'Agregar/Editar Cliente',
    'size' => 'modal-lg',
    'scrollable' => true,
    'body' => $formContent,
    'footer' => $footerContent
]);

// Modal informativo del proceso CRUD
echo renderModal([
    'id' => 'info_modal',
    'title' => 'Flujo de Datos en el Proceso CRUD',
    'size' => 'modal-xl',
    'scrollable' => true,
    'body' => '
        <div class="timeline">
            <div class="time-label">
                <span class="bg-primary">Inicio del Proceso</span>
            </div>
            <div>
                <i class="fas fa-desktop bg-blue"></i>
                <div class="timeline-item">
                    <h3 class="timeline-header"><strong>Vista (Frontend)</strong></h3>
                    <div class="timeline-body">
                        El usuario interactúa con la interfaz, haciendo clic en botones como "Nuevo", "Editar" o "Eliminar".
                        <pre class="bg-light p-2 mt-2"><code>// Ejemplo de código en scripts/crud/crud.php
$("#add").on("click", function() {
    // Preparar el formulario para crear un nuevo registro
    $("#client_form")[0].reset();
    $("#client_crud").val("create");
    $("#client_modal").modal("show");
});</code></pre>
                    </div>
                </div>
            </div>
            <div>
                <i class="fas fa-paper-plane bg-green"></i>
                <div class="timeline-item">
                    <h3 class="timeline-header"><strong>Solicitud AJAX</strong></h3>
                    <div class="timeline-body">
                        JavaScript envía una solicitud AJAX al servidor con los datos necesarios.
                        <pre class="bg-light p-2 mt-2"><code>$.ajax({
    type: "POST",
    url: "includes/crud/crud.php",
    data: formData,
    dataType: "json",
    success: function(response) {
        // Procesar respuesta
    }
});</code></pre>
                    </div>
                </div>
            </div>
            <div>
                <i class="fas fa-filter bg-yellow"></i>
                <div class="timeline-item">
                    <h3 class="timeline-header"><strong>Punto de Entrada (includes/crud/crud.php)</strong></h3>
                    <div class="timeline-body">
                        Este archivo recibe la solicitud y determina qué acción realizar.
                        <pre class="bg-light p-2 mt-2"><code>if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $crud = $_POST["crud"];
    switch ($crud) {
        case "create":
            $result = $crudController->CreateCrud($_POST);
            echo json_encode($result);
            break;
        // Otros casos...
    }
}</code></pre>
                    </div>
                </div>
            </div>
            <div>
                <i class="fas fa-cogs bg-purple"></i>
                <div class="timeline-item">
                    <h3 class="timeline-header"><strong>Controlador (controllers/crud/crud.php)</strong></h3>
                    <div class="timeline-body">
                        El controlador aplica la lógica de negocio y manipula los datos.
                        <pre class="bg-light p-2 mt-2"><code>public function CreateCrud($data) {
    try {
        $client = R::dispense("clients");
        $client->name = $data["name"];
        $client->email = $data["email"];
        $client->phone = $data["phone"] ?? null;
        
        $id = R::store($client);
        // Devolver resultado
    } catch (Exception $e) {
        // Manejar error
    }
}</code></pre>
                    </div>
                </div>
            </div>
            <div>
                <i class="fas fa-database bg-red"></i>
                <div class="timeline-item">
                    <h3 class="timeline-header"><strong>Base de Datos (RedBeanPHP)</strong></h3>
                    <div class="timeline-body">
                        RedBeanPHP gestiona la interacción con la base de datos.
                        <pre class="bg-light p-2 mt-2"><code>// Operaciones comunes:
$bean = R::dispense("tabla");  // Crear
R::store($bean);               // Guardar
$bean = R::load("tabla", $id); // Cargar
R::trash($bean);               // Eliminar</code></pre>
                    </div>
                </div>
            </div>
            <div>
                <i class="fas fa-reply bg-teal"></i>
                <div class="timeline-item">
                    <h3 class="timeline-header"><strong>Respuesta JSON</strong></h3>
                    <div class="timeline-body">
                        El servidor devuelve una respuesta en formato JSON.
                        <pre class="bg-light p-2 mt-2"><code>// Ejemplo de respuesta
{
  "status": true,
  "message": "Cliente añadido con éxito"
}</code></pre>
                    </div>
                </div>
            </div>
            <div>
                <i class="fas fa-check-circle bg-info"></i>
                <div class="timeline-item">
                    <h3 class="timeline-header"><strong>Actualización de la Interfaz</strong></h3>
                    <div class="timeline-body">
                        JavaScript recibe la respuesta y actualiza la interfaz.
                        <pre class="bg-light p-2 mt-2"><code>success: function(response) {
    $("#client_modal").modal("hide");
    Swal.fire(response.message, "", response.status ? "success" : "error");
    $("#table").DataTable().ajax.reload(null, false);
}</code></pre>
                    </div>
                </div>
            </div>
            <div class="time-label">
                <span class="bg-success">Fin del Proceso</span>
            </div>
        </div>
    ',
    'footer' => '<button type="button" class="btn btn-default" data-dismiss="modal">Cerrar</button>'
]);
?>

<!-- Incluir Mermaid.js para diagramas de flujo -->
<script src="https://cdn.jsdelivr.net/npm/mermaid/dist/mermaid.min.js"></script>