<?= $this->extend('template') ?>
<?= $this->section('title') ?>Origen de clientes<?= $this->endSection() ?>
<?= $this->section('content') ?>
<div class="card">
    <div class="card-header bg-primary text-white">
        <h4 class="mb-0"><i class="fa fa-bullhorn me-2"></i>¿De dónde llegan los clientes?</h4>
    </div>
    <div class="card-body">
        <p class="text-muted">Compara redes sociales, referidos, web y otros canales para ver cuál genera más prospectos y conversiones a cliente.</p>
        <div class="row g-3 mb-4">
            <div class="col-md-3">
                <label class="form-label">Asesor</label>
                <select id="filter_user" class="form-select">
                    <option value="all">Todos</option>
                    <?php foreach ($users as $user): ?>
                        <option value="<?= $user['id'] ?>"><?= esc($user['full_name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-2"><label class="form-label">Desde</label><input type="date" id="filter_start" class="form-control"></div>
            <div class="col-md-2"><label class="form-label">Hasta</label><input type="date" id="filter_end" class="form-control"></div>
            <div class="col-md-2 d-flex align-items-end"><button type="button" class="btn btn-primary w-100" onclick="loadReport()"><i class="fa fa-search"></i> Buscar</button></div>
        </div>

        <div class="row mb-4 d-none" id="summaryRow">
            <div class="col-md-3"><div class="card border-primary"><div class="card-body text-center"><h3 class="text-primary mb-0" id="stat_total">0</h3><small>Con origen registrado</small></div></div></div>
            <div class="col-md-3"><div class="card border-success"><div class="card-body text-center"><h3 class="text-success mb-0" id="stat_clients">0</h3><small>Ya son clientes</small></div></div></div>
            <div class="col-md-3"><div class="card border-warning"><div class="card-body text-center"><h3 class="text-warning mb-0" id="stat_missing">0</h3><small>Sin origen</small></div></div></div>
            <div class="col-md-3"><div class="card border-info"><div class="card-body text-center"><h3 class="text-info mb-0" id="stat_top">—</h3><small>Mejor canal</small></div></div></div>
        </div>

        <div class="row">
            <div class="col-lg-5 mb-4">
                <h6>Por tipo de canal</h6>
                <div class="table-responsive">
                    <table class="table table-sm table-striped"><thead><tr><th>Tipo</th><th>Prospectos</th><th>Clientes</th></tr></thead><tbody id="channelBody"></tbody></table>
                </div>
            </div>
            <div class="col-lg-7 mb-4">
                <h6>Detalle por origen</h6>
                <div class="table-responsive">
                    <table class="table table-striped"><thead><tr><th>Origen</th><th>Tipo</th><th>Prospectos</th><th>Clientes</th><th>% conv.</th></tr></thead><tbody id="sourceBody"></tbody></table>
                </div>
            </div>
        </div>
    </div>
</div>
<script>
const channelLabels = <?= json_encode(\App\Models\AcquisitionSourceModel::CHANNEL_LABELS, JSON_UNESCAPED_UNICODE) ?>;
function loadReport() {
    const params = new URLSearchParams({
        user_id: document.getElementById('filter_user').value,
        start_date: document.getElementById('filter_start').value,
        end_date: document.getElementById('filter_end').value,
    });
    fetch('<?= site_url('admin/reports/getAcquisitionSourcesData') ?>?' + params.toString(), {
        headers: { 'X-Requested-With': 'XMLHttpRequest' }
    }).then(r => r.json()).then(data => {
        if (!data.success) return;
        document.getElementById('summaryRow').classList.remove('d-none');
        const t = data.totals;
        document.getElementById('stat_total').textContent = t.prospects - t.without_source;
        document.getElementById('stat_clients').textContent = t.clients;
        document.getElementById('stat_missing').textContent = t.without_source;
        document.getElementById('stat_top').textContent = data.top_source || '—';

        const channelBody = document.getElementById('channelBody');
        channelBody.innerHTML = '';
        (data.by_channel || []).forEach(row => {
            channelBody.innerHTML += `<tr><td>${channelLabels[row.channel_type] || row.channel_type}</td><td><strong>${row.total_prospects}</strong></td><td>${row.total_clients}</td></tr>`;
        });

        const sourceBody = document.getElementById('sourceBody');
        sourceBody.innerHTML = '';
        (data.by_source || []).forEach(row => {
            const conv = row.total_prospects > 0 ? Math.round((row.total_clients / row.total_prospects) * 100) : 0;
            const icon = row.icon_class ? `<i class="${row.icon_class} me-1"></i>` : '';
            sourceBody.innerHTML += `<tr><td>${icon}${row.source_name}</td><td>${channelLabels[row.channel_type] || row.channel_type}</td><td><strong>${row.total_prospects}</strong></td><td>${row.total_clients}</td><td>${conv}%</td></tr>`;
        });
    });
}
document.addEventListener('DOMContentLoaded', loadReport);
</script>
<?= $this->endSection() ?>
