<?= $this->extend('template') ?>

<?= $this->section('title') ?><?= $title ?? 'Mi Agenda' ?><?= $this->endSection() ?>

<?= $this->section('styles') ?>
<!-- FullCalendar CSS -->
<link href='https://cdn.jsdelivr.net/npm/fullcalendar@6.1.8/index.global.min.css' rel='stylesheet' />
<style>
    /* Asegurar que el sidebar se muestre correctamente */
    .main-container {
        display: flex !important;
        min-height: 100vh !important;
    }
    
    .sidebar {
        display: flex !important;
        width: 280px !important;
        flex-shrink: 0;
    }
    
    .content-area {
        flex: 1 !important;
        overflow-x: hidden;
    }

    .fc-toolbar-title {
        font-size: 1.5rem !important;
        font-weight: bold;
    }
    
    .fc-event {
        border-radius: 3px;
        border: 1px solid;
        font-size: 0.85rem;
    }
    
    .fc-event-title {
        font-weight: 500;
    }
    
    .activity-card {
        border-left: 4px solid;
    }
    
    .activity-card.pendiente {
        border-left-color: #17a2b8;
    }
    
    .activity-card.completada {
        border-left-color: #28a745;
    }
    
    .activity-card.cancelada {
        border-left-color: #6c757d;
    }
    
    .calendar-container {
        background: white;
        border-radius: 8px;
        padding: 20px;
        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    }
    
    .activity-status {
        display: inline-block;
        padding: 2px 8px;
        border-radius: 12px;
        font-size: 0.75rem;
        font-weight: 500;
        text-transform: uppercase;
    }
    
    .status-pendiente {
        background-color: #b8daff;
        color: #004085;
    }
    
    .status-completada {
        background-color: #d4edda;
        color: #155724;
    }
    
    .status-cancelada {
        background-color: #f8d7da;
        color: #721c24;
    }
    
    /* Indicadores de estado Google Calendar */
    .google-connected-badge {
        position: relative;
        overflow: visible;
    }
    
    .google-sync-indicator {
        position: absolute;
        top: -8px;
        right: -8px;
        width: 16px;
        height: 16px;
        border-radius: 50%;
        font-size: 8px;
        display: flex;
        align-items: center;
        justify-content: center;
    }
    
    .sync-success {
        background-color: #28a745;
        color: white;
    }
    
    .sync-pending {
        background-color: #ffc107;
        color: black;
    }
    
    /* Animación para indicador de sincronización */
    @keyframes pulse-sync {
        0% { opacity: 1; transform: scale(1); }
        50% { opacity: 0.7; transform: scale(1.1); }
        100% { opacity: 1; transform: scale(1); }
    }
    
    .syncing {
        animation: pulse-sync 1.5s infinite;
    }
    
    /* Toast notifications */
    .toast-container {
        position: fixed;
        top: 20px;
        right: 20px;
        z-index: 9999;
    }

    .swal-wide {
        width: 600px !important;
    }

    .hover-bg-light:hover {
        background-color: #f8f9fa !important;
    }

    .fc-event {
        cursor: pointer;
    }

    #prospectOffcanvas {
        position: fixed;
        top: 0;
        right: 0;
        height: 100%;
        width: 560px;
        max-width: 90%;
        background: #fff;
        box-shadow: -10px 0 30px rgba(0,0,0,0.2);
        transform: translateX(100%);
        transition: transform 0.3s ease-in-out;
        z-index: 1060;
        overflow-y: auto;
        display: none;
    }
    #prospectOffcanvas.show-offcanvas {
        transform: translateX(0);
    }
    #prospectOffcanvas .offcanvas-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 1rem;
        border-bottom: 1px solid #eee;
        position: sticky;
        top: 0;
        background: #fff;
        z-index: 2;
    }
    #prospectOffcanvas .offcanvas-header .close {
        font-size: 2rem;
        font-weight: 300;
        line-height: 1;
        color: #000;
        opacity: 0.5;
        background: transparent;
        border: none;
        cursor: pointer;
    }
    #prospectOffcanvas .offcanvas-body { padding: 1rem; }
</style>
<?= $this->endSection() ?>

<?= $this->section('content') ?>

<!-- Toast Container -->
<div class="toast-container">
    <div id="syncToast" class="toast align-items-center border-0" role="alert" aria-live="assertive" aria-atomic="true">
        <div class="d-flex">
            <div class="toast-body">
                <i class="fas fa-sync-alt me-2"></i>
                <span id="toastMessage">Sincronizando con Google Calendar...</span>
            </div>
            <button type="button" class="btn-close me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
        </div>
    </div>
</div>
<div class="py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 text-gray-800">
            <i class="fas fa-calendar-alt me-2"></i>
            Mi Agenda
        </h1>
        <div class="d-flex gap-2">
            <button type="button" class="btn btn-primary" id="nuevaActividadBtn">
                <i class="fas fa-plus me-2"></i>
                Nueva Actividad
            </button>
            
            <!-- Estado de Google Calendar -->
            <?php 
            $userModel = model('UserModel');
            $currentUser = $userModel->find(session()->get('user_id'));
            $isGoogleConnected = isset($currentUser['google_calendar_connected']) && $currentUser['google_calendar_connected'] == 1;
            $lastSync = $currentUser['google_calendar_last_sync'] ?? null;
            ?>
            
            <?php if ($isGoogleConnected): ?>
                <div class="btn-group">
                    <button type="button" class="btn btn-success" id="syncGoogleBtn">
                        <i class="fab fa-google me-2"></i>
                        <span class="d-none d-md-inline">Conectado</span>
                        <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-white text-success border">
                            <i class="fas fa-check" style="font-size: 0.6rem;"></i>
                        </span>
                    </button>
                    <button type="button" class="btn btn-outline-secondary btn-sm" id="disconnectGoogleBtn" title="Desconectar Google Calendar">
                        <i class="fas fa-unlink"></i>
                    </button>
                </div>
            <?php else: ?>
                <a href="<?= base_url('agenda/google/connect') ?>" class="btn btn-outline-warning position-relative">
                    <i class="fab fa-google me-2"></i>
                    <span class="d-none d-md-inline">Conectar </span>Google Calendar
                    <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-warning text-dark">
                        <i class="fas fa-exclamation" style="font-size: 0.6rem;"></i>
                    </span>
                </a>
            <?php endif; ?>
            
            <span class="badge bg-info text-dark">
                <i class="fa fa-clock me-1"></i>
                <?= date('l, j \d\e F \d\e Y') ?>
            </span>
        </div>
    </div>

    <!-- Estadísticas rápidas -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card bg-primary text-white">
                <div class="card-body p-3">
                    <div class="d-flex align-items-center">
                        <div class="flex-grow-1">
                            <h6 class="mb-0">Citas Pendientes</h6>
                            <h3 class="mb-0"><?= $stats['pending'] ?? 0 ?></h3>
                        </div>
                        <i class="fa fa-clock fa-2x opacity-50"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-success text-white">
                <div class="card-body p-3">
                    <div class="d-flex align-items-center">
                        <div class="flex-grow-1">
                            <h6 class="mb-0">Completadas</h6>
                            <h3 class="mb-0"><?= $stats['completed'] ?? 0 ?></h3>
                        </div>
                        <i class="fa fa-check-circle fa-2x opacity-50"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-secondary text-white">
                <div class="card-body p-3">
                    <div class="d-flex align-items-center">
                        <div class="flex-grow-1">
                            <h6 class="mb-0">Canceladas</h6>
                            <h3 class="mb-0"><?= $stats['cancelled'] ?? 0 ?></h3>
                        </div>
                        <i class="fa fa-times-circle fa-2x opacity-50"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-warning text-dark">
                <div class="card-body p-3">
                    <div class="d-flex align-items-center">
                        <div class="flex-grow-1">
                            <h6 class="mb-0">Total del Mes</h6>
                            <h3 class="mb-0"><?= $stats['total'] ?? 0 ?></h3>
                        </div>
                        <i class="fa fa-calendar fa-2x opacity-50"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Calendario -->
        <div class="col-lg-8">
            <div class="calendar-container">
                <div id="calendar"></div>
            </div>
        </div>

        <!-- Lista de actividades del día -->
        <div class="col-lg-4">
            <div class="card">
                <div class="card-header bg-light">
                    <h5 class="mb-0">
                        <i class="fa fa-list me-2"></i>
                        Actividades de Hoy
                    </h5>
                </div>
                <div class="card-body p-0" style="max-height: 500px; overflow-y: auto;" id="todayActivities">
                    <div class="text-center text-muted p-4">
                        <i class="fa fa-calendar fa-3x mb-2 d-block"></i>
                        <p>No hay actividades programadas para hoy</p>
                    </div>
                </div>
            </div>

            <!-- Leyenda de estados -->
            <div class="card mt-3">
                <div class="card-header bg-light">
                    <h6 class="mb-0">
                        <i class="fa fa-info-circle me-2"></i>
                        Estados de Actividades
                    </h6>
                </div>
                <div class="card-body p-3">
                    <div class="d-flex align-items-center mb-2">
                        <div class="w-4 h-4" style="width: 16px; height: 16px; background-color: #17a2b8; margin-right: 8px; border-radius: 2px;"></div>
                        <small>Pendiente</small>
                    </div>
                    <div class="d-flex align-items-center mb-2">
                        <div class="w-4 h-4" style="width: 16px; height: 16px; background-color: #28a745; margin-right: 8px; border-radius: 2px;"></div>
                        <small>Completada</small>
                    </div>
                    <div class="d-flex align-items-center">
                        <div class="w-4 h-4" style="width: 16px; height: 16px; background-color: #6c757d; margin-right: 8px; border-radius: 2px;"></div>
                        <small>Cancelada</small>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Offcanvas: detalle del prospecto (mismo panel que en la lista CRM) -->
<div class="offcanvas offcanvas-end" tabindex="-1" id="prospectOffcanvas" aria-labelledby="prospectOffcanvasLabel">
    <div class="offcanvas-header">
        <h5 class="offcanvas-title" id="prospectOffcanvasLabel">Detalle del Prospecto</h5>
        <button type="button" class="close" data-dismiss="offcanvas" aria-label="Close">
            <span aria-hidden="true">&times;</span>
        </button>
    </div>
    <div class="offcanvas-body p-3" id="prospectOffcanvasBody">
        <div id="prospectOffcanvasLoading" class="text-center my-5">
            <div class="spinner-border text-primary" role="status"><span class="visually-hidden">Cargando...</span></div>
            <p class="mt-3 text-muted">Cargando detalle...</p>
        </div>
        <div id="prospectOffcanvasContent" style="display:none;"></div>
    </div>
</div>

<!-- Modal para Nueva Actividad -->
<div class="modal fade" id="newActivityModal" tabindex="-1" aria-labelledby="newActivityModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title" id="newActivityModalLabel">
                    <i class="fas fa-calendar-plus me-2"></i>
                    Crear Nueva Actividad
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="newActivityForm">
                <div class="modal-body">
                    <div class="row">
                        <div class="col-12 mb-3">
                            <label for="activity_title" class="form-label">Título de la Actividad *</label>
                            <input type="text" name="title" id="activity_title" class="form-control" required placeholder="Ej: Reunión con cliente">
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="activity_start_time" class="form-label">Fecha y Hora de Inicio *</label>
                            <input type="datetime-local" name="start_time" id="activity_start_time" class="form-control" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="activity_end_time" class="form-label">Fecha y Hora de Fin (Opcional)</label>
                            <input type="datetime-local" name="end_time" id="activity_end_time" class="form-control">
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="activity_type" class="form-label">Tipo de Actividad *</label>
                            <select name="type" id="activity_type" class="form-control" required>
                                <option value="">-- Seleccionar --</option>
                                <option value="Llamada">Llamada</option>
                                <option value="Reunión">Reunión</option>
                                <option value="Cita Infonavit">Cita Infonavit</option>
                                <option value="Envío Documentos">Envío Documentos</option>
                                <option value="Otro">Otro</option>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="activity_client" class="form-label">Cliente Asociado (Opcional)</label>
                            <select name="client_id" id="activity_client" class="form-control">
                                <option value="">-- Sin cliente específico --</option>
                                <!-- Los clientes se cargarán dinámicamente -->
                            </select>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-12 mb-3">
                            <label for="activity_description" class="form-label">Descripción</label>
                            <textarea name="description" id="activity_description" class="form-control" rows="3" placeholder="Detalles adicionales de la actividad..."></textarea>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save me-2"></i>
                        Crear Actividad
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal para Motivo de Reprogramación -->
<div class="modal fade" id="rescheduleModal" tabindex="-1" aria-labelledby="rescheduleModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="rescheduleModalLabel">
                    <i class="fas fa-clock me-2"></i>
                    Reprogramar Actividad
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="rescheduleForm">
                <div class="modal-body">
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle me-2"></i>
                        Se creará una minuta de seguimiento con el motivo de la reprogramación.
                    </div>
                    <div id="rescheduleDetails" class="mb-3">
                        <!-- Se llenará dinámicamente con los detalles del cambio -->
                    </div>
                    <div class="mb-3">
                        <label for="rescheduleReason" class="form-label">Motivo de la Reprogramación *</label>
                        <textarea name="reason" id="rescheduleReason" class="form-control" rows="4" 
                                placeholder="Explica por qué se reprograma esta actividad..." required></textarea>
                    </div>
                    <input type="hidden" id="rescheduleEventId">
                    <input type="hidden" id="rescheduleNewStart">
                    <input type="hidden" id="rescheduleNewEnd">
                    <input type="hidden" id="rescheduleOldStart">
                    <input type="hidden" id="rescheduleOldEnd">
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-warning">
                        <i class="fas fa-check me-2"></i>
                        Confirmar Reprogramación
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<!-- Debug y carga de FullCalendar -->
<script>
console.log('=== INICIANDO CARGA DE AGENDA ===');
console.log('jQuery disponible:', typeof jQuery !== 'undefined');
</script>

<!-- FullCalendar JS - Version estable -->
<script src='https://cdn.jsdelivr.net/npm/fullcalendar@6.1.15/index.global.min.js'></script>
<script src='https://cdn.jsdelivr.net/npm/@fullcalendar/core@6.1.15/locales/es.global.min.js'></script>

<!-- Moment.js para manejo de fechas -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.29.4/moment.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.29.4/locale/es.min.js"></script>

<script>
console.log('FullCalendar cargado:', typeof FullCalendar !== 'undefined');

document.addEventListener('DOMContentLoaded', function() {
    console.log('=== DOM CARGADO, INICIALIZANDO CALENDARIO ===');

    // --- Offcanvas detalle prospecto (reutilizado desde CRM) ---
    const offcanvasEl = document.getElementById('prospectOffcanvas');

    function showProspectOffcanvas() {
        if (!offcanvasEl) return;
        offcanvasEl.style.display = 'block';
        offcanvasEl.classList.add('show-offcanvas');
        offcanvasEl.setAttribute('aria-hidden', 'false');
        document.body.style.overflow = 'hidden';
    }

    function hideProspectOffcanvas() {
        if (!offcanvasEl) return;
        offcanvasEl.classList.remove('show-offcanvas');
        offcanvasEl.setAttribute('aria-hidden', 'true');
        document.body.style.overflow = '';
        setTimeout(function() { offcanvasEl.style.display = 'none'; }, 310);
    }

    window.showProspectOffcanvas = showProspectOffcanvas;
    window.hideProspectOffcanvas = hideProspectOffcanvas;

    if (offcanvasEl) {
        offcanvasEl.addEventListener('click', function(e) {
            if (e.target.closest('[data-dismiss="offcanvas"], [data-bs-dismiss]')) {
                e.preventDefault();
                hideProspectOffcanvas();
            }
        });
        document.addEventListener('click', function(e) {
            if (!offcanvasEl.classList.contains('show-offcanvas')) return;
            if (e.target.closest('#prospectOffcanvas .offcanvas-body') || e.target.closest('.fc-event')) return;
            hideProspectOffcanvas();
        });
    }

    function initProspectViewBehaviors(root) {
        if (!root) return;
        if (window.initCurrencyFormatting) {
            try { window.initCurrencyFormatting(root); } catch (err) { console.warn(err); }
        }
        if (window.initPhoneFormatting) {
            try { window.initPhoneFormatting(root); } catch (err) { console.warn(err); }
        }
        root.querySelectorAll('[data-offcanvas-close]').forEach(function(el) {
            el.addEventListener('click', function(e) {
                e.preventDefault();
                hideProspectOffcanvas();
            });
        });
        if (typeof $ !== 'undefined' && $.fn.popover) {
            $(root).find('[data-toggle="popover"]').popover({ container: 'body' });
        }
    }

    function openProspectDetail(clientId, isFunerario) {
        if (!clientId) return;
        $('#prospectOffcanvasLabel').text('Detalle del Prospecto #' + clientId);
        $('#prospectOffcanvasLoading').show();
        $('#prospectOffcanvasContent').hide().html('');
        showProspectOffcanvas();

        const url = '<?= site_url('prospects/view') ?>/' + clientId + (isFunerario ? '?tab=funerario' : '');
        $.get(url)
            .done(function(html) {
                $('#prospectOffcanvasContent').html(html);
                $('#prospectOffcanvasLoading').hide();
                $('#prospectOffcanvasContent').show();
                const root = document.getElementById('prospectOffcanvasContent');
                initProspectViewBehaviors(root);
                if (isFunerario) {
                    setTimeout(function() {
                        document.getElementById('comprador-funerario-tab')?.click();
                    }, 50);
                }
            })
            .fail(function() {
                $('#prospectOffcanvasLoading').hide();
                $('#prospectOffcanvasContent').html('<div class="alert alert-danger">No se pudo cargar el detalle del prospecto.</div>').show();
            });
    }

    function showActivitySummary(info) {
        const props = info.event.extendedProps || {};
        const start = moment(info.event.start).format('DD/MM/YYYY HH:mm');
        const end = info.event.end ? moment(info.event.end).format('DD/MM/YYYY HH:mm') : '—';
        const syncNote = props.google_synced ? '<p class="mb-0 text-success"><i class="fab fa-google me-1"></i> Sincronizado con Google Calendar</p>' : '';

        Swal.fire({
            title: info.event.title,
            html: `
                <div class="text-start small">
                    <p class="mb-1"><strong>Tipo:</strong> ${props.type || '—'}</p>
                    <p class="mb-1"><strong>Estado:</strong> ${props.status || '—'}</p>
                    <p class="mb-1"><strong>Inicio:</strong> ${start}</p>
                    <p class="mb-1"><strong>Fin:</strong> ${end}</p>
                    ${props.description ? '<p class="mb-1"><strong>Notas:</strong> ' + props.description + '</p>' : ''}
                    ${syncNote}
                    <p class="text-muted mb-0 mt-2">Esta actividad no tiene un cliente vinculado.</p>
                </div>
            `,
            icon: 'info',
            confirmButtonText: 'Cerrar'
        });
    }
    
    // Verificar que tenemos todo lo necesario
    if (typeof FullCalendar === 'undefined') {
        console.error('FullCalendar no se cargó correctamente');
        alert('Error: No se pudo cargar el calendario. Intente recargar la página.');
        return;
    }
    
    const calendarEl = document.getElementById('calendar');
    if (!calendarEl) {
        console.error('No se encontró el elemento #calendar');
        return;
    }
    
    // Crear calendario con configuración mínima
    const calendar = new FullCalendar.Calendar(calendarEl, {
        initialView: 'dayGridMonth',
        locale: 'es',
        headerToolbar: {
            left: 'prev,next today',
            center: 'title',
            right: 'dayGridMonth,timeGridWeek,timeGridDay'
        },
        height: 'auto',
        // Habilitar drag & drop
        editable: true,
        droppable: true,
        eventDurationEditable: true,
        eventStartEditable: true,
        events: function(fetchInfo, successCallback, failureCallback) {
            console.log('Cargando eventos del servidor para el rango:', fetchInfo.startStr, 'a', fetchInfo.endStr);
            $.ajax({
                url: '<?= base_url('agenda/getActivities') ?>',
                method: 'GET',
                dataType: 'json',
                data: {
                    start: fetchInfo.startStr,
                    end: fetchInfo.endStr
                },
                success: function(data) {
                    console.log('Respuesta del servidor:', data);
                    // El controlador ahora devuelve un array de eventos directamente.
                    if (Array.isArray(data)) {
                        console.log('✓ Eventos cargados:', data.length);
                        successCallback(data);
                    } else {
                        console.warn('⚠️ La respuesta del servidor no es un array válido.');
                        successCallback([]);
                    }
                },
                error: function(xhr, status, error) {
                    console.error('✗ Error cargando eventos:', error);
                    console.error('Response:', xhr.responseText);
                    failureCallback(error);
                }
            });
        },
        eventClick: function(info) {
            info.jsEvent.preventDefault();
            const props = info.event.extendedProps || {};
            const clientId = props.client_id;
            if (clientId) {
                openProspectDetail(clientId, !!props.is_funeral_prospect);
                return;
            }
            showActivitySummary(info);
        },
        eventDrop: function(info) {
            console.log('Evento arrastrado:', info.event.title);
            
            // Obtener información del evento y los cambios
            const eventId = info.event.id;
            const oldStart = info.oldEvent.start;
            const newStart = info.event.start;
            const newEnd = info.event.end;
            
            // Formatear fechas
            const oldStartFormatted = moment(oldStart).format('DD/MM/YYYY HH:mm');
            const newStartFormatted = moment(newStart).format('DD/MM/YYYY HH:mm');
            const newEndFormatted = newEnd ? moment(newEnd).format('DD/MM/YYYY HH:mm') : 'Sin hora de fin';
            
            // Mostrar modal para pedir motivo
            $('#rescheduleEventId').val(eventId);
            $('#rescheduleNewStart').val(moment(newStart).format('YYYY-MM-DD HH:mm:ss'));
            $('#rescheduleNewEnd').val(newEnd ? moment(newEnd).format('YYYY-MM-DD HH:mm:ss') : '');
            $('#rescheduleOldStart').val(moment(oldStart).format('YYYY-MM-DD HH:mm:ss'));
            
            // Mostrar detalles del cambio
            const changeDetails = `
                <div class="card">
                    <div class="card-body">
                        <h6 class="card-title">${info.event.title}</h6>
                        <div class="row">
                            <div class="col-6">
                                <small class="text-muted">Fecha anterior:</small><br>
                                <span class="text-danger">${oldStartFormatted}</span>
                            </div>
                            <div class="col-6">
                                <small class="text-muted">Nueva fecha:</small><br>
                                <span class="text-success">${newStartFormatted}</span>
                            </div>
                        </div>
                    </div>
                </div>
            `;
            
            $('#rescheduleDetails').html(changeDetails);
            $('#rescheduleModal').modal('show');
            
            // Si el usuario cancela, revertir el cambio
            $('#rescheduleModal').off('hidden.bs.modal').on('hidden.bs.modal', function() {
                if (!$(this).data('confirmed')) {
                    console.log('Revirtiendo cambio de evento');
                    info.revert();
                }
            });
        },
        eventDidMount: function(info) {
            console.log('Evento renderizado:', info.event.title);
            
            // Agregar indicador visual para eventos sincronizados con Google
            if (info.event.extendedProps.google_synced) {
                const googleIcon = document.createElement('span');
                googleIcon.innerHTML = ' 🔄';
                googleIcon.title = 'Sincronizado con Google Calendar';
                googleIcon.style.fontSize = '0.8em';
                info.el.querySelector('.fc-event-title').appendChild(googleIcon);
            }
        }
    });
    
    // Renderizar el calendario
    try {
        calendar.render();
        console.log('✓ Calendario renderizado exitosamente');
    } catch (error) {
        console.error('✗ Error al renderizar calendario:', error);
        alert('Error al mostrar el calendario: ' + error.message);
        return;
    }
    
    // Configurar botón para nueva actividad
    $('#nuevaActividadBtn').on('click', function() {
        console.log('Abriendo modal de nueva actividad');
        $('#newActivityModal').modal('show');
        cargarClientesActivos();
    });
    
    // Configurar formulario de nueva actividad
    $('#newActivityForm').on('submit', function(e) {
        e.preventDefault();
        console.log('Enviando nueva actividad...');
        
        const formData = $(this).serialize();
        console.log('Datos del formulario:', formData);
        
        $.ajax({
            url: '<?= base_url('agenda/createActivity') ?>',
            method: 'POST',
            data: formData,
            dataType: 'json',
            beforeSend: function() {
                console.log('Enviando petición AJAX...');
            },
            success: function(response) {
                console.log('Respuesta del servidor:', response);
                
                if (response && response.success) {
                    $('#newActivityModal').modal('hide');
                    $('#newActivityForm')[0].reset();
                    showToast('✓ Actividad creada exitosamente', 'success');
                    calendar.refetchEvents(); // Recargar eventos del calendario
                } else {
                    let errorMsg = 'Error al crear actividad';
                    if (response.errors) {
                        errorMsg = Object.values(response.errors).join(', ');
                    }
                    showToast(errorMsg, 'error');
                }
            },
            error: function(xhr, status, error) {
                console.error('Error AJAX:', {
                    status: status,
                    error: error,
                    responseText: xhr.responseText
                });
                alert('Error de comunicación: ' + error);
            }
        });
    });
    
    // Función para cargar clientes activos
    function cargarClientesActivos() {
        const select = $('#activity_client');
        if (!select.length) return;

        select.prop('disabled', true).html('<option value="">Cargando clientes...</option>');

        $.ajax({
            url: '<?= base_url('prospects/getActiveClients') ?>',
            method: 'GET',
            dataType: 'json',
            headers: { 'X-Requested-With': 'XMLHttpRequest' },
            success: function(data) {
                select.empty().append('<option value="">-- Sin cliente específico --</option>');

                if (data && data.success && Array.isArray(data.clients) && data.clients.length) {
                    data.clients.forEach(function(client) {
                        const name = client.full_name || client.name || ('Cliente #' + client.id);
                        const status = client.status ? ' (' + client.status + ')' : '';
                        select.append('<option value="' + client.id + '">' + name + status + '</option>');
                    });
                } else {
                    select.append('<option value="" disabled>No hay clientes activos en tu cartera</option>');
                }
            },
            error: function(xhr) {
                console.error('Error cargando clientes:', xhr.responseText);
                select.empty()
                    .append('<option value="">-- Sin cliente específico --</option>')
                    .append('<option value="" disabled>Error al cargar clientes</option>');
            },
            complete: function() {
                select.prop('disabled', false);
            }
        });
    }
    
    // Función para mostrar Toast de notificación
    function showToast(message, type = 'info') {
        const toast = document.getElementById('syncToast');
        const toastMessage = document.getElementById('toastMessage');
        const toastBody = toast.querySelector('.toast-body');
        
        // Configurar color según el tipo
        switch(type) {
            case 'success':
                toast.className = 'toast align-items-center border-0 text-white bg-success';
                toastBody.innerHTML = '<i class="fas fa-check me-2"></i><span>' + message + '</span>';
                break;
            case 'error':
                toast.className = 'toast align-items-center border-0 text-white bg-danger';
                toastBody.innerHTML = '<i class="fas fa-exclamation-triangle me-2"></i><span>' + message + '</span>';
                break;
            case 'warning':
                toast.className = 'toast align-items-center border-0 text-dark bg-warning';
                toastBody.innerHTML = '<i class="fas fa-exclamation me-2"></i><span>' + message + '</span>';
                break;
            default:
                toast.className = 'toast align-items-center border-0 text-white bg-info';
                toastBody.innerHTML = '<i class="fas fa-info me-2"></i><span>' + message + '</span>';
        }
        
        const bsToast = new bootstrap.Toast(toast);
        bsToast.show();
    }

    // Eventos de Google Calendar
    $('#syncGoogleBtn, #syncGoogleLink').on('click', function(e) {
        e.preventDefault();
        console.log('Iniciando sincronización con Google Calendar...');
        
        const button = $(this);
        button.prop('disabled', true).addClass('syncing');
        const originalText = button.html();
        button.html('<i class="fa fa-spinner fa-spin me-2"></i>Sincronizando...');
        
        showToast('Iniciando sincronización con Google Calendar...', 'info');
        
        $.ajax({
            url: '<?= base_url('agenda/google/sync') ?>',
            method: 'POST',
            dataType: 'json',
            success: function(response) {
                console.log('Respuesta sincronización:', response);
                if (response && response.status === 'success') {
                    showToast('✓ ' + response.message, 'success');
                    calendar.refetchEvents(); // Recargar eventos
                    
                    // Actualizar indicador de última sincronización
                    setTimeout(function() {
                        location.reload();
                    }, 1500);
                } else {
                    showToast('Error: ' + (response.message || 'Error desconocido'), 'error');
                }
            },
            error: function(xhr, status, error) {
                console.error('Error sincronizando:', xhr.responseText);
                showToast('Error de conexión: ' + error, 'error');
            },
            complete: function() {
                button.prop('disabled', false).removeClass('syncing').html(originalText);
            }
        });
    });
    
    $('#disconnectGoogleBtn').on('click', function(e) {
        e.preventDefault();
        if (confirm('¿Estás seguro de que deseas desconectar Google Calendar?\n\nEsto eliminará la sincronización automática, pero tus eventos locales se mantendrán.')) {
            showToast('Desconectando de Google Calendar...', 'warning');
            
            $.ajax({
                url: '<?= base_url('agenda/google/disconnect') ?>',
                method: 'POST',
                success: function() {
                    showToast('Google Calendar desconectado exitosamente', 'success');
                    setTimeout(function() {
                        location.reload();
                    }, 1500);
                },
                error: function(xhr, status, error) {
                    showToast('Error al desconectar: ' + error, 'error');
                }
            });
        }
    });

    // Manejar formulario de reprogramación
    $('#rescheduleForm').on('submit', function(e) {
        e.preventDefault();
        
        const eventId = $('#rescheduleEventId').val();
        const newStart = $('#rescheduleNewStart').val();
        const newEnd = $('#rescheduleNewEnd').val();
        const reason = $('#rescheduleReason').val();
        
        if (!reason.trim()) {
            alert('Por favor, ingresa el motivo de la reprogramación.');
            return;
        }
        
        // Marcar como confirmado para que no se revierta
        $('#rescheduleModal').data('confirmed', true);
        
        $.ajax({
            url: '<?= base_url('agenda/moveActivity') ?>/' + eventId,
            method: 'POST',
            data: {
                new_start: newStart,
                new_end: newEnd,
                reason: reason
            },
            dataType: 'json',
            success: function(response) {
                if (response && response.success) {
                    showToast('Actividad reprogramada y minuta creada exitosamente', 'success');
                    $('#rescheduleModal').modal('hide');
                    $('#rescheduleReason').val(''); // Limpiar formulario
                    calendar.refetchEvents(); // Recargar eventos
                } else {
                    showToast('Error: ' + (response.message || 'Error desconocido'), 'error');
                }
            },
            error: function(xhr, status, error) {
                showToast('Error al reprogramar: ' + error, 'error');
                console.error('Error:', xhr.responseText);
            }
        });
    });
    
    console.log('=== INICIALIZACIÓN COMPLETA ===');
});
</script>
<?= $this->endSection() ?>