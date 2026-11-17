<?php helper(['form', 'funerario']); ?>
<?= $this->extend('template') ?>
<?= $this->section('content') ?>
<div class="container-fluid mt-4 mb-5">
    <h1><?= $product ? 'Editar paquete' : 'Nuevo paquete funerario' ?></h1>
    <?= form_open_multipart($product ? 'admin/funeral-products/update/' . $product['id'] : 'admin/funeral-products/store') ?>
    <?= csrf_field() ?>
    <div class="row">
        <div class="col-md-3 mb-3">
            <label class="form-label">Clave *</label>
            <input type="text" name="clave" class="form-control" required value="<?= esc(old('clave', $product['clave'] ?? '')) ?>">
        </div>
        <div class="col-md-5 mb-3">
            <label class="form-label">Nombre del paquete *</label>
            <input type="text" name="name" class="form-control" required value="<?= esc(old('name', $product['name'] ?? '')) ?>">
        </div>
        <div class="col-md-4 mb-3">
            <label class="form-label">Tipo</label>
            <select name="product_type_id" class="form-select">
                <option value="">—</option>
                <?php foreach ($productTypes as $t): ?>
                    <option value="<?= $t['id'] ?>" <?= (old('product_type_id', $product['product_type_id'] ?? '') == $t['id']) ? 'selected' : '' ?>><?= esc($t['name']) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="col-md-4 mb-3">
            <label class="form-label">Lugar de descanso *</label>
            <select name="funeral_location_id" id="funeral_location_id" class="form-select" required>
                <option value="">Selecciona...</option>
                <?php foreach ($locations as $loc): ?>
                    <option value="<?= $loc['id'] ?>" <?= (old('funeral_location_id', $product['funeral_location_id'] ?? '') == $loc['id']) ? 'selected' : '' ?>><?= esc($loc['name']) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="col-md-4 mb-3">
            <label class="form-label">Funeraria *</label>
            <select name="funeral_home_id" id="funeral_home_id" class="form-select" required>
                <option value="">Selecciona lugar primero...</option>
                <?php foreach ($homes as $h): ?>
                    <option value="<?= $h['id'] ?>" data-all="1" <?= (old('funeral_home_id', $product['funeral_home_id'] ?? '') == $h['id']) ? 'selected' : '' ?>><?= esc($h['name']) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="col-md-2 mb-3">
            <label class="form-label">Precio base *</label>
            <input type="number" step="0.01" min="0" name="base_price" class="form-control" required value="<?= esc(old('base_price', $product['base_price'] ?? '0')) ?>">
        </div>
        <div class="col-md-2 mb-3">
            <label class="form-label">Estado</label>
            <select name="status" class="form-select">
                <option value="Disponible" <?= old('status', $product['status'] ?? '') === 'Disponible' ? 'selected' : '' ?>>Disponible</option>
                <option value="No disponible" <?= old('status', $product['status'] ?? '') === 'No disponible' ? 'selected' : '' ?>>No disponible</option>
            </select>
        </div>
        <div class="col-12 mb-3">
            <label class="form-label">Descripción</label>
            <textarea name="description" class="form-control" rows="3"><?= esc(old('description', $product['description'] ?? '')) ?></textarea>
        </div>
        <div class="col-12 mb-3">
            <label class="form-label">Foto para el sitio público (opcional)</label>
            <?php if (! empty($product['image_path'])): ?>
                <div class="d-flex align-items-center mb-2" style="gap: 12px;">
                    <img src="<?= base_url($product['image_path']) ?>" alt="" style="height: 90px; border-radius: 8px; object-fit: cover;">
                    <label class="mb-0"><input type="checkbox" name="quitar_imagen" value="1"> Quitar foto</label>
                </div>
            <?php endif; ?>
            <input type="file" name="image" class="form-control-file" accept="image/jpeg,image/png,image/webp">
            <small class="text-muted">JPG, PNG o WEBP, máximo 8 MB. Se muestra en el catálogo funerario.</small>
        </div>
    </div>

    <div class="card mb-4">
        <div class="card-header d-flex justify-content-between align-items-center">
            <strong>Planes de financiamiento</strong>
            <button type="button" class="btn btn-sm btn-outline-primary" id="addPlanBtn"><i class="fa fa-plus"></i> Agregar plan</button>
        </div>
        <div class="card-body">
            <p class="text-muted small">Cada plan define su <strong>enganche</strong>, <strong>mensualidad</strong> y número de <strong>meses</strong>.</p>
            <div class="table-responsive">
                <table class="table" id="plansTable">
                    <thead>
                        <tr>
                            <th>Nombre del plan</th>
                            <th>Enganche ($)</th>
                            <th>Mensualidad ($)</th>
                            <th>Meses</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody id="plansBody">
                        <?php
                        $planRows = old('plan_name') ? [] : ($plans ?: [['name' => '12 meses', 'enganche' => '', 'mensualidad' => '', 'months' => 12]]);
                        if (empty($planRows) && empty($plans)) {
                            $planRows = [['name' => '', 'enganche' => '', 'mensualidad' => '', 'months' => 12]];
                        } elseif (! empty($plans)) {
                            $planRows = $plans;
                        }
                        foreach ($planRows as $plan):
                        ?>
                        <tr class="plan-row">
                            <td><input type="text" name="plan_name[]" class="form-control" value="<?= esc($plan['name'] ?? '') ?>" placeholder="Ej. Plan 24 meses"></td>
                            <td><input type="number" step="0.01" min="0" name="plan_enganche[]" class="form-control" value="<?= esc($plan['enganche'] ?? '') ?>"></td>
                            <td><input type="number" step="0.01" min="0" name="plan_mensualidad[]" class="form-control" value="<?= esc($plan['mensualidad'] ?? '') ?>"></td>
                            <td><input type="number" min="1" name="plan_months[]" class="form-control" value="<?= esc($plan['months'] ?? 12) ?>"></td>
                            <td><button type="button" class="btn btn-sm btn-outline-danger remove-plan">×</button></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <button type="submit" class="btn btn-primary">Guardar paquete</button>
    <a href="<?= base_url('admin/funeral-products') ?>" class="btn btn-secondary">Cancelar</a>
    <?= form_close() ?>
</div>

<template id="planRowTemplate">
    <tr class="plan-row">
        <td><input type="text" name="plan_name[]" class="form-control" placeholder="Ej. Plan 36 meses"></td>
        <td><input type="number" step="0.01" min="0" name="plan_enganche[]" class="form-control"></td>
        <td><input type="number" step="0.01" min="0" name="plan_mensualidad[]" class="form-control"></td>
        <td><input type="number" min="1" name="plan_months[]" class="form-control" value="12"></td>
        <td><button type="button" class="btn btn-sm btn-outline-danger remove-plan">×</button></td>
    </tr>
</template>

<script>
document.getElementById('addPlanBtn')?.addEventListener('click', () => {
    const tpl = document.getElementById('planRowTemplate');
    document.getElementById('plansBody').appendChild(tpl.content.cloneNode(true));
});
document.getElementById('plansBody')?.addEventListener('click', e => {
    if (e.target.closest('.remove-plan')) {
        const rows = document.querySelectorAll('#plansBody .plan-row');
        if (rows.length > 1) e.target.closest('tr').remove();
    }
});

const locationSelect = document.getElementById('funeral_location_id');
const homeSelect = document.getElementById('funeral_home_id');
const selectedHome = homeSelect?.value;

async function filterHomesByLocation() {
    const locationId = locationSelect.value;
    if (!locationId) return;
    const resp = await fetch('<?= base_url('admin/funeral-products/homes-by-location/') ?>' + locationId);
    const json = await resp.json();
    homeSelect.innerHTML = '<option value="">Selecciona funeraria...</option>';
    (json.homes || []).forEach(h => {
        const opt = document.createElement('option');
        opt.value = h.id;
        opt.textContent = h.name;
        if (String(h.id) === String(selectedHome)) opt.selected = true;
        homeSelect.appendChild(opt);
    });
}
locationSelect?.addEventListener('change', filterHomesByLocation);
if (locationSelect?.value) filterHomesByLocation();
</script>
<?= $this->endSection() ?>
