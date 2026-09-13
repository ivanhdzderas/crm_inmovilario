<?= $this->extend('template') ?>

<?= $this->section('title') ?>Reporte Pipeline Clientes<?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="card">
    <div class="card-header bg-primary text-white">
        <h4 class="mb-0"><i class="fa fa-users me-2"></i>Reporte de Clientes — Pipeline y Cancelaciones</h4>
    </div>
    <div class="card-body">
        <div class="alert alert-info">
            <i class="fa fa-info-circle me-2"></i>
            Consulta quién ya compró, quién está en trámite, quién apartó y quién canceló, con el motivo registrado.
        </div>

        <div class="card border-secondary mb-4">
            <div class="card-header bg-light"><h6 class="mb-0"><i class="fa fa-filter me-2"></i>Filtros</h6></div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-3">
                        <label class="form-label">Asesor</label>
                        <select id="filter_user" class="form-select">
                            <option value="all">Todos</option>
                            <?php foreach ($users as $user): ?>
                                <option value="<?= $user['id'] ?>"><?= esc($user['full_name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Etapa</label>
                        <select id="filter_pipeline" class="form-select">
                            <option value="all">Todas</option>
                            <option value="compro">Compró casa</option>
                            <option value="en_tramite">En trámite</option>
                            <option value="apartado">Apartado</option>
                            <option value="cancelado">Cancelado</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">Desde (opcional)</label>
                        <input type="date" id="filter_start_date" class="form-control">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">Hasta (opcional)</label>
                        <input type="date" id="filter_end_date" class="form-control">
                    </div>
                    <div class="col-md-2 d-flex align-items-end gap-2">
                        <button type="button" class="btn btn-primary w-100" onclick="loadPipelineReport()"><i class="fa fa-search"></i></button>
                        <button type="button" class="btn btn-success" onclick="exportPipelineReport()" title="Exportar CSV"><i class="fa fa-file-csv"></i></button>
                    </div>
                </div>
            </div>
        </div>

        <div class="row mb-4" id="summaryContainer" style="display:none;">
            <div class="col-md-3"><div class="card border-success"><div class="card-body text-center"><h3 class="text-success mb-0" id="stat_compro">0</h3><small>Compró casa</small></div></div></div>
            <div class="col-md-3"><div class="card border-primary"><div class="card-body text-center"><h3 class="text-primary mb-0" id="stat_en_tramite">0</h3><small>En trámite</small></div></div></div>
            <div class="col-md-3"><div class="card border-warning"><div class="card-body text-center"><h3 class="text-warning mb-0" id="stat_apartado">0</h3><small>Apartado</small></div></div></div>
            <div class="col-md-3"><div class="card border-danger"><div class="card-body text-center"><h3 class="text-danger mb-0" id="stat_cancelado">0</h3><small>Cancelado</small></div></div></div>
        </div>

        <div class="card border-danger mb-4" id="cancellationStatsContainer" style="display:none;">
            <div class="card-header bg-danger text-white"><h6 class="mb-0"><i class="fa fa-ban me-2"></i>Cancelaciones por motivo</h6></div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-striped mb-0" id="cancellationStatsTable">
                        <thead><tr><th>Motivo</th><th>Tipo</th><th>Total clientes</th></tr></thead>
                        <tbody id="cancellationStatsBody"></tbody>
                    </table>
                </div>
            </div>
        </div>

        <div id="loadingContainer" class="text-center py-5" style="display:none;">
            <div class="spinner-border text-primary"></div>
            <p class="mt-2 text-muted">Generando reporte...</p>
        </div>

        <div id="resultsContainer" style="display:none;">
            <div class="table-responsive">
                <table class="table table-striped table-hover" id="pipelineReportTable" style="width:100%">
                    <thead class="table-dark">
                        <tr>
                            <th>Cliente</th>
                            <th>Etapa</th>
                            <th>Inmueble</th>
                            <th>Asesor</th>
                            <th>Motivo cancelación</th>
                            <th>Notas</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody id="pipelineReportTableBody"></tbody>
                </table>
            </div>
        </div>

        <div id="noResultsContainer" class="alert alert-warning text-center" style="display:none;">
            No se encontraron registros con los filtros seleccionados.
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', () => loadPipelineReport());

function loadPipelineReport() {
    $('#loadingContainer').show();
    $('#resultsContainer, #noResultsContainer, #summaryContainer, #cancellationStatsContainer').hide();

    $.get('<?= base_url('admin/reports/getClientPipelineData') ?>', {
        user_id: $('#filter_user').val(),
        pipeline_stage: $('#filter_pipeline').val(),
        start_date: $('#filter_start_date').val(),
        end_date: $('#filter_end_date').val()
    }, function(response) {
        $('#loadingContainer').hide();
        if (!response.success) {
            Swal.fire('Error', response.message || 'No se pudo cargar el reporte', 'error');
            return;
        }

        if (response.summary) {
            $('#stat_compro').text(response.summary.compro || 0);
            $('#stat_en_tramite').text(response.summary.en_tramite || 0);
            $('#stat_apartado').text(response.summary.apartado || 0);
            $('#stat_cancelado').text(response.summary.cancelado || 0);
            $('#summaryContainer').show();
        }

        renderCancellationStats(response.cancellation_stats || []);

        if (response.data && response.data.length) {
            renderPipelineRows(response.data);
            $('#resultsContainer').show();
        } else {
            $('#noResultsContainer').show();
        }
    }).fail(function() {
        $('#loadingContainer').hide();
        Swal.fire('Error', 'Error al cargar el reporte', 'error');
    });
}

function renderCancellationStats(stats) {
    const tbody = $('#cancellationStatsBody');
    tbody.empty();
    if (!stats.length) {
        $('#cancellationStatsContainer').hide();
        return;
    }
    stats.forEach(item => {
        tbody.append(`<tr><td>${escapeHtml(item.reason_name)}</td><td>${escapeHtml(item.source_type)}</td><td><strong>${item.total}</strong></td></tr>`);
    });
    $('#cancellationStatsContainer').show();
}

function renderPipelineRows(rows) {
    if ($.fn.DataTable.isDataTable('#pipelineReportTable')) {
        $('#pipelineReportTable').DataTable().destroy();
    }
    const tbody = $('#pipelineReportTableBody');
    tbody.empty();

    rows.forEach(row => {
        const badgeClass = {
            compro: 'success', en_tramite: 'primary', apartado: 'warning text-dark', cancelado: 'danger'
        }[row.pipeline_stage] || 'secondary';

        tbody.append(`
            <tr>
                <td><strong>${escapeHtml(row.full_name)}</strong><br><small class="text-muted">${escapeHtml(row.phone || '')}</small></td>
                <td><span class="badge bg-${badgeClass}">${escapeHtml(row.pipeline_label)}</span></td>
                <td>${escapeHtml(row.property_name || '—')}<br><small>${escapeHtml(row.property_clave || '')}</small></td>
                <td>${escapeHtml(row.asesor_name || '—')}</td>
                <td>${escapeHtml(row.cancellation_reason || '—')}</td>
                <td>${escapeHtml(row.cancellation_notes || '—')}</td>
                <td><a href="<?= base_url('prospects/view/') ?>${row.client_id}" class="btn btn-sm btn-outline-primary">Ver</a></td>
            </tr>
        `);
    });

    $('#pipelineReportTable').DataTable({
        responsive: true,
        language: { url: '//cdn.datatables.net/plug-ins/1.13.6/i18n/Spanish.json' },
        pageLength: 25,
        order: [[0, 'asc']]
    });
}

function exportPipelineReport() {
    const params = new URLSearchParams({
        user_id: $('#filter_user').val(),
        pipeline_stage: $('#filter_pipeline').val(),
        start_date: $('#filter_start_date').val(),
        end_date: $('#filter_end_date').val()
    });
    window.location.href = '<?= base_url('admin/reports/exportClientPipeline') ?>?' + params.toString();
}

function escapeHtml(text) {
    if (!text) return '';
    return String(text).replace(/[&<>"']/g, m => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#039;'}[m]));
}
</script>
<?= $this->endSection() ?>
