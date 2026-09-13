<?php helper('form'); ?>
<?= $this->extend('template') ?>
<?= $this->section('content') ?>
<?php $location = $location ?? null; ?>
<div class="container mt-4">
    <h1><?= $location ? 'Editar lugar' : 'Nuevo lugar de descanso' ?></h1>
    <?= form_open($location ? 'admin/funeral-locations/update/' . $location['id'] : 'admin/funeral-locations/store') ?>
    <?= csrf_field() ?>
    <div class="row">
        <div class="col-md-6 mb-3">
            <label class="form-label">Nombre del lugar *</label>
            <input type="text" name="name" class="form-control" required value="<?= esc(old('name', $location['name'] ?? '')) ?>">
        </div>
        <div class="col-md-6 mb-3">
            <label class="form-label">Zona</label>
            <select name="zone_id" class="form-select">
                <option value="">—</option>
                <?php foreach ($zones as $z): ?>
                    <option value="<?= $z['id'] ?>" <?= (old('zone_id', $location['zone_id'] ?? '') == $z['id']) ? 'selected' : '' ?>><?= esc($z['name']) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="col-12 mb-3">
            <label class="form-label">Dirección</label>
            <input type="text" name="address" class="form-control" value="<?= esc(old('address', $location['address'] ?? '')) ?>">
        </div>
        <div class="col-12 mb-3">
            <label class="form-label">Descripción</label>
            <textarea name="description" class="form-control" rows="3"><?= esc(old('description', $location['description'] ?? '')) ?></textarea>
        </div>
        <div class="col-12 mb-3">
            <label class="form-label">Funerarias que operan en este lugar</label>
            <div class="row">
                <?php foreach ($allHomes as $home): ?>
                    <div class="col-md-4">
                        <label class="form-check">
                            <input type="checkbox" class="form-check-input" name="funeral_home_ids[]" value="<?= $home['id'] ?>"
                                <?= in_array($home['id'], old('funeral_home_ids', $linkedHomes), true) ? 'checked' : '' ?>>
                            <?= esc($home['name']) ?>
                        </label>
                    </div>
                <?php endforeach; ?>
            </div>
            <small class="text-muted">Ej.: Crespo, Fernández y Manzano en el mismo parque.</small>
        </div>
        <div class="col-12 mb-3 form-check">
            <input type="checkbox" name="is_active" value="1" class="form-check-input" id="is_active" <?= old('is_active', $location['is_active'] ?? 1) ? 'checked' : '' ?>>
            <label for="is_active" class="form-check-label">Activo</label>
        </div>
    </div>
    <button type="submit" class="btn btn-primary">Guardar</button>
    <a href="<?= base_url('admin/funeral-locations') ?>" class="btn btn-secondary">Cancelar</a>
    <?= form_close() ?>
</div>
<?= $this->endSection() ?>
