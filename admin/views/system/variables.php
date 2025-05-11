<?php
include '../../includes/session.php';

$admin_id = $user['id'];
$roles_ids = explode(',', $user['roles_ids']);

if (!in_array(1, $roles_ids))
{
    include '403.php';
}
else
{

?>

    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                </div>
                <div class="col-sm-6">
                </div>
            </div>
        </div>
    </div>

    <section class="content">
        <div class="container-fluid">
            <div class="card">
                <div class="card-header">
                    <button type="button" class="btn btn-primary btn-sm" data-toggle="modal" data-target="#variableModal" id="addVariableBtn">
                        <i class="fa fa-solid fa-duotone fa-plus fa-lg"></i>
                    </button>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table id="variablesTable" class="table table-bordered table-striped table-sm">
                            <thead>
                                <tr>
                                    <th>Nombre</th>
                                    <th>Valor</th>
                                    <th>Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                <!-- Los datos se cargan por AJAX -->
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-header">
                            <b class="card-title">Ayuda</b>
                        </div>
                        <div class="card-body">
                            <b>Notas importantes</b>
                            <ul>
                                <li>Las variables de base de datos (DB_* y DATABASE_*) no se pueden modificar desde esta interfaz.</li>
                                <li>Las variables esenciales del sistema (APP_* y MAIL_*) no se pueden eliminar.</li>
                                <li>Los valores de variables que contienen palabras como PASSWORD, SECRET, KEY o TOKEN se mostrarán ocultos por seguridad.</li>
                                <li>Los cambios en las variables de entorno se aplican inmediatamente, pero algunos pueden requerir reiniciar ciertos servicios para surtir efecto.</li>
                            </ul>
                        </div>
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="card">
                        <div class="card-header">
                            <b class="card-title">Configuración de Correo</b>
                        </div>
                        <div class="card-body">
                            <p>Para configurar el envío de correos, asegúrate de establecer las siguientes variables:</p>
                            <ul>
                                <li><strong>MAIL_HOST</strong> - Servidor SMTP (ej. smtp.gmail.com)</li>
                                <li><strong>MAIL_PORT</strong> - Puerto SMTP (ej. 587)</li>
                                <li><strong>MAIL_USERNAME</strong> - Usuario/correo</li>
                                <li><strong>MAIL_PASSWORD</strong> - Contraseña</li>
                                <li><strong>MAIL_ENCRYPTION</strong> - Tipo de cifrado (smtp o tls unicamente)</li>
                                <li><strong>MAIL_NAME</strong> - Nombre del remitente</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Modal unificado para Agregar/Editar Variable -->
    <div class="modal fade" id="variableModal" tabindex="-1" role="dialog" aria-labelledby="variableModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <b class="modal-title" id="variableModalLabel">Agregar Variable de Entorno</b>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <form id="variableForm">
                    <div class="modal-body">
                        <input type="hidden" id="modalOperation" value="create">
                        <div class="form-group">
                            <label for="variableName">Nombre de la Variable</label>
                            <input type="text" class="form-control" id="variableName" placeholder="Ej: API_TOKEN" required>
                            <small class="form-text text-muted">Use prefijos como MAIL_ para variables de correo o API_ para tokens de API.</small>
                        </div>
                        <div class="form-group">
                            <label for="variableValue">Valor</label>
                            <input type="text" class="form-control" id="variableValue" placeholder="Valor de la variable">
                        </div>
                    </div>
                    <div class="modal-footer justify-content-between">
                        <button type="button" class="btn btn-secondary btn-sm" data-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-primary btn-sm" id="saveVariableBtn">Guardar</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

<?php
}
?>