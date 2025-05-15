<!-- Modal para configuración 2FA -->
<div class="modal fade" id="modal2FA" tabindex="-1" role="dialog" aria-labelledby="modal2FALabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <b class="modal-title" id="modal2FALabel">Configuración de Autenticación de Dos Factores (2FA)</b>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <!-- Contenido dinámico que se cargará mediante AJAX -->
                <div class="text-center mb-2">
                    <b>
                        Estado actual:
                        <span id="tfa_status_badge" class="badge bg-danger">Cargando...</span>
                    </b>
                </div>

                <div id="tfa_content_container">
                    <!-- El contenido se cargará con AJAX -->
                    <div class="text-center">
                        <div class="spinner-border text-primary" role="status">
                            <span class="sr-only">Cargando...</span>
                        </div>
                        <p class="mt-2">Cargando configuración...</p>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-sm btn-primary" data-dismiss="modal"><span class="fa fa-solid fa-duotone fa-times"> </span> Cerrar</button>
            </div>
        </div>
    </div>
</div>