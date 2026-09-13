<?= $this->extend('template') ?>

<?= $this->section('title') ?>
Reporte de Seguimiento
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="card">
    <div class="card-header bg-primary text-white">
        <h4 class="mb-0">
            <i class="fa fa-chart-line me-2"></i>
            Reporte de Seguimiento de Prospectos
        </h4>
    </div>
    
    <div class="card-body">
        <div class="alert alert-info">
            <i class="fa fa-info-circle me-2"></i>
            Este reporte muestra la última actividad de cada prospecto para identificar cuáles requieren seguimiento.
        </div>

        <!-- Filtros -->
        <div class="row mb-4">
            <div class="col-md-12">
                <div class="card border-secondary">
                    <div class="card-header bg-light">
                        <h6 class="mb-0">
                            <i class="fa fa-filter me-2"></i>
                            Filtros de Búsqueda
                        </h6>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-3">
                                <label for="filter_start_date" class="form-label">
                                    <i class="fa fa-calendar me-1"></i> Fecha Inicio (Opcional)
                                </label>
                                <input type="date" id="filter_start_date" class="form-control" placeholder="Sin filtro">
                            </div>
                            <div class="col-md-3">
                                <label for="filter_end_date" class="form-label">
                                    <i class="fa fa-calendar me-1"></i> Fecha Fin (Opcional)
                                </label>
                                <input type="date" id="filter_end_date" class="form-control" placeholder="Sin filtro">
                            </div>
                            <div class="col-md-3">
                                <label for="filter_user" class="form-label">
                                    <i class="fa fa-user me-1"></i> Asesor
                                </label>
                                <select id="filter_user" class="form-select">
                                    <option value="all">Todos los asesores</option>
                                    <?php foreach ($users as $user): ?>
                                        <option value="<?= $user['id'] ?>"><?= esc($user['full_name']) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label for="filter_status" class="form-label">
                                    <i class="fa fa-flag me-1"></i> Estado
                                </label>
                                <select id="filter_status" class="form-select">
                                    <option value="all">Todos los estados</option>
                                    <option value="Nuevo">Nuevo</option>
                                    <option value="Seguimiento">Seguimiento</option>
                                    <option value="Cierre">Cierre</option>
                                    <option value="Cancelado">Cancelado</option>
                                    <option value="Descartado">Cancelado (histórico)</option>
                                </select>
                            </div>
                        </div>
                        <div class="row mt-3">
                            <div class="col-md-12 text-end">
                                <button type="button" class="btn btn-primary" onclick="loadReport()">
                                    <i class="fa fa-search me-1"></i> Buscar
                                </button>
                                <button type="button" class="btn btn-success" onclick="exportReport()">
                                    <i class="fa fa-file-excel me-1"></i> Exportar Excel
                                </button>
                                <button type="button" class="btn btn-secondary" onclick="resetFilters()">
                                    <i class="fa fa-redo me-1"></i> Limpiar
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Estadísticas rápidas -->
        <div class="row mb-4" id="statsContainer" style="display: none;">
            <div class="col-md-3">
                <div class="card border-primary">
                    <div class="card-body text-center">
                        <h2 class="text-primary mb-0" id="stat_total">0</h2>
                        <small class="text-muted">Total Prospectos</small>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card border-success">
                    <div class="card-body text-center">
                        <h2 class="text-success mb-0" id="stat_recent">0</h2>
                        <small class="text-muted">Seguimiento Reciente (≤7 días)</small>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card border-warning">
                    <div class="card-body text-center">
                        <h2 class="text-warning mb-0" id="stat_moderate">0</h2>
                        <small class="text-muted">Requieren Atención (8-30 días)</small>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card border-danger">
                    <div class="card-body text-center">
                        <h2 class="text-danger mb-0" id="stat_critical">0</h2>
                        <small class="text-muted">Sin Seguimiento (>30 días)</small>
                    </div>
                </div>
            </div>
        </div>

        <!-- Loading -->
        <div id="loadingContainer" class="text-center py-5" style="display: none;">
            <div class="spinner-border text-primary" role="status">
                <span class="sr-only">Cargando...</span>
            </div>
            <p class="mt-2 text-muted">Generando reporte...</p>
        </div>

        <!-- Tabla de resultados -->
        <div id="resultsContainer" style="display: none;">
            <div class="table-responsive">
                <table class="table table-striped table-hover" id="prospectsReportTable" style="width:100%">
                    <thead class="thead-dark">
                        <tr>
                            <th>Prospecto</th>
                            <th>Estado</th>
                            <th>Asesor</th>
                            <th>Última Actividad</th>
                            <th>Días sin Seguimiento</th>
                            <th>Última Nota</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody id="prospectsReportTableBody">
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Sin resultados -->
        <div id="noResultsContainer" class="alert alert-warning text-center" style="display: none;">
            <i class="fa fa-exclamation-triangle fa-2x mb-2"></i>
            <p class="mb-0">No se encontraron prospectos con los filtros seleccionados.</p>
        </div>
    </div>
</div>

<!-- Offcanvas para detalle del prospecto -->
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

<script>
// Cargar el reporte al iniciar la página
document.addEventListener('DOMContentLoaded', function() {
    loadReport();
});

function loadReport() {
    // Mostrar loading
    $('#loadingContainer').show();
    $('#resultsContainer').hide();
    $('#noResultsContainer').hide();
    $('#statsContainer').hide();

    // Obtener valores de filtros
    const startDate = $('#filter_start_date').val();
    const endDate = $('#filter_end_date').val();
    const userId = $('#filter_user').val();
    const status = $('#filter_status').val();

    // Validar fechas solo si se proporcionó una de ellas
    if ((startDate && !endDate) || (!startDate && endDate)) {
        Swal.fire({
            icon: 'error',
            title: 'Error',
            text: 'Si filtras por fecha, debes seleccionar fecha inicio Y fecha fin',
            confirmButtonColor: '#dc3545'
        });
        $('#loadingContainer').hide();
        return;
    }

    if (startDate && endDate && new Date(startDate) > new Date(endDate)) {
        Swal.fire({
            icon: 'error',
            title: 'Error',
            text: 'La fecha de inicio no puede ser mayor a la fecha fin',
            confirmButtonColor: '#dc3545'
        });
        $('#loadingContainer').hide();
        return;
    }

    // Hacer petición AJAX
    $.ajax({
        url: '<?= base_url('admin/reports/getProspectsFollowUpData') ?>',
        type: 'GET',
        data: {
            start_date: startDate,
            end_date: endDate,
            user_id: userId,
            status: status
        },
        dataType: 'json',
        success: function(response) {
            $('#loadingContainer').hide();

            if (response.success && response.data && response.data.length > 0) {
                displayResults(response.data);
                updateStats(response.data);
                $('#resultsContainer').show();
                $('#statsContainer').show();
            } else {
                $('#noResultsContainer').show();
            }
        },
        error: function(xhr, status, error) {
            $('#loadingContainer').hide();
            console.error('Error:', error);
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: 'Error al cargar el reporte. Por favor intente nuevamente.',
                confirmButtonColor: '#dc3545'
            });
        }
    });
}

function displayResults(data) {
    // Destruir DataTable existente si existe
    if ($.fn.DataTable.isDataTable('#prospectsReportTable')) {
        $('#prospectsReportTable').DataTable().destroy();
    }
    
    const tbody = $('#prospectsReportTableBody');
    tbody.empty();

    data.forEach(function(prospect) {
        // Badge de estado
        let statusBadge = '';
        switch(prospect.status) {
            case 'Nuevo':
                statusBadge = '<span class="badge bg-primary">Nuevo</span>';
                break;
            case 'Seguimiento':
                statusBadge = '<span class="badge bg-warning text-dark">Seguimiento</span>';
                break;
            case 'Cierre':
                statusBadge = '<span class="badge bg-success">Cierre</span>';
                break;
            case 'Cancelado':
            case 'Descartado':
                statusBadge = '<span class="badge bg-danger">Cancelado</span>';
                break;
            default:
                statusBadge = '<span class="badge bg-secondary">' + prospect.status + '</span>';
        }

        // Badge de días sin actividad
        let daysClass = '';
        let daysIcon = '';
        if (prospect.activity_status === 'recent') {
            daysClass = 'success';
            daysIcon = 'check-circle';
        } else if (prospect.activity_status === 'moderate') {
            daysClass = 'warning';
            daysIcon = 'exclamation-triangle';
        } else if (prospect.activity_status === 'old') {
            daysClass = 'orange';
            daysIcon = 'clock';
        } else {
            daysClass = 'danger';
            daysIcon = 'times-circle';
        }

        // Formatear fecha
        const lastActivityDate = new Date(prospect.last_activity_date);
        const formattedDate = lastActivityDate.toLocaleDateString('es-MX', {
            year: 'numeric',
            month: 'short',
            day: 'numeric',
            hour: '2-digit',
            minute: '2-digit'
        });

        const row = `
            <tr>
                <td>
                    <strong>${prospect.full_name}</strong><br>
                    <small class="text-muted">
                        <i class="fa fa-phone me-1"></i>${prospect.phone || 'N/A'}<br>
                        <i class="fa fa-envelope me-1"></i>${prospect.email || 'N/A'}
                    </small>
                </td>
                <td>${statusBadge}</td>
                <td>
                    <i class="fa fa-user-tie me-1"></i>
                    ${prospect.asesor_name || '<span class="text-muted">Sin asignar</span>'}
                </td>
                <td>
                    <small>${formattedDate}</small>
                </td>
                <td>
                    <span class="badge bg-${daysClass}">
                        <i class="fa fa-${daysIcon} me-1"></i>
                        ${prospect.days_without_activity} días
                    </span>
                </td>
                <td>
                    <small class="text-muted" title="${prospect.last_notes || 'Sin notas'}">
                        ${prospect.last_notes_short}
                    </small>
                </td>
                <td>
                    <button type="button"
                        data-id="${prospect.id}"
                        class="btn btn-sm btn-info text-white rounded-pill px-3 py-1 btn-offcanvas-detail"
                        title="Ver Detalle y Seguimiento">
                        <i class="fa fa-eye"></i>
                    </button>
                </td>
            </tr>
        `;
        tbody.append(row);
    });
    
    // Inicializar DataTable con responsive
    new DataTable('#prospectsReportTable', {
        responsive: true,
        scrollX: false,
        autoWidth: false,
        language: {
            url: '//cdn.datatables.net/plug-ins/1.13.6/i18n/Spanish.json'
        },
        pageLength: 25,
        order: [[3, 'desc']], // Ordenar por última actividad
        columnDefs: [
            { responsivePriority: 1, targets: 0 }, // Prospecto siempre visible
            { responsivePriority: 2, targets: 6 }, // Acciones SIEMPRE visible
            { responsivePriority: 3, targets: 4 }, // Días sin seguimiento
            { responsivePriority: 10001, targets: [1, 2, 3, 5] }, // Ocultar resto en móvil
            { width: "100px", targets: 6 }
        ]
    });
}

function updateStats(data) {
    let total = data.length;
    let recent = 0;
    let moderate = 0;
    let critical = 0;

    data.forEach(function(prospect) {
        if (prospect.activity_status === 'recent') {
            recent++;
        } else if (prospect.activity_status === 'moderate') {
            moderate++;
        } else if (prospect.activity_status === 'old' || prospect.activity_status === 'critical') {
            critical++;
        }
    });

    $('#stat_total').text(total);
    $('#stat_recent').text(recent);
    $('#stat_moderate').text(moderate);
    $('#stat_critical').text(critical);
}

function exportReport() {
    const startDate = $('#filter_start_date').val();
    const endDate = $('#filter_end_date').val();
    const userId = $('#filter_user').val();
    const status = $('#filter_status').val();

    if (!startDate || !endDate) {
        Swal.fire({
            icon: 'error',
            title: 'Error',
            text: 'Debes seleccionar ambas fechas',
            confirmButtonColor: '#dc3545'
        });
        return;
    }

    // Construir URL con parámetros
    const url = '<?= base_url('admin/reports/exportProspectsFollowUp') ?>' +
        '?start_date=' + encodeURIComponent(startDate) +
        '&end_date=' + encodeURIComponent(endDate) +
        '&user_id=' + encodeURIComponent(userId) +
        '&status=' + encodeURIComponent(status);

    // Abrir en nueva ventana para descargar
    window.open(url, '_blank');
}

function resetFilters() {
    $('#filter_start_date').val('');
    $('#filter_end_date').val('');
    $('#filter_user').val('all');
    $('#filter_status').val('all');
    
    loadReport();
}

// Manejo del offcanvas para ver detalle del prospecto
$(document).on('click', '.btn-offcanvas-detail', function() {
    const prospectId = $(this).data('id');
    const offcanvas = $('#prospectOffcanvas');
    
    // Mostrar offcanvas
    offcanvas.addClass('show-offcanvas');
    
    // Mostrar loading
    $('#prospectOffcanvasLoading').show();
    $('#prospectOffcanvasContent').hide();
    
    // Cargar el contenido del prospecto
    $.ajax({
        url: '<?= base_url('prospects/view/') ?>' + prospectId,
        type: 'GET',
        success: function(response) {
            $('#prospectOffcanvasLoading').hide();
            $('#prospectOffcanvasContent').html(response).show();
        },
        error: function() {
            $('#prospectOffcanvasLoading').hide();
            $('#prospectOffcanvasContent').html('<div class="alert alert-danger">Error al cargar el detalle</div>').show();
        }
    });
});

// Cerrar offcanvas
$(document).on('click', '[data-dismiss="offcanvas"]', function() {
    $('#prospectOffcanvas').removeClass('show-offcanvas');
});

// Cerrar offcanvas al hacer clic fuera
$(document).on('click', function(e) {
    if ($(e.target).closest('.offcanvas').length === 0 && $(e.target).closest('.btn-offcanvas-detail').length === 0) {
        $('#prospectOffcanvas').removeClass('show-offcanvas');
    }
});
</script>

<style>
.bg-orange {
    background-color: #fd7e14 !important;
}

/* Estilos para el offcanvas */
#prospectOffcanvas {
    position: fixed;
    top: 0;
    right: -100%;
    width: 400px;
    height: 100vh;
    background: white;
    box-shadow: -2px 0 10px rgba(0,0,0,0.1);
    transition: right 0.3s ease-in-out;
    z-index: 1050;
    overflow-y: auto;
}
#prospectOffcanvas.show-offcanvas {
    right: 0;
}
#prospectOffcanvas .offcanvas-header { 
    display: flex; 
    align-items: center; 
    justify-content: space-between; 
    padding: 1rem; 
    border-bottom: 1px solid #eee; 
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
    padding: 0;
    margin: 0;
}
#prospectOffcanvas .offcanvas-header .close:hover {
    opacity: 0.75;
}
#prospectOffcanvas .offcanvas-body { 
    padding: 1rem; 
}

@media (max-width: 768px) {
    #prospectOffcanvas {
        width: 100%;
        right: -100%;
    }
}
</style>

<?= $this->endSection() ?>
