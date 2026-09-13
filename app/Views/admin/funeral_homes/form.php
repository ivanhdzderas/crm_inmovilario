<?php helper('form'); ?>
<?= $this->extend('template') ?>
<?= $this->section('content') ?>
<div class="container mt-4">
    <h1><?= $home ? 'Editar funeraria' : 'Nueva funeraria' ?></h1>
    <?= form_open($home ? 'admin/funeral-homes/update/' . $home['id'] : 'admin/funeral-homes/store') ?>
    <?= csrf_field() ?>
    <div class="mb-3">
        <label class="form-label">Nombre *</label>
        <input type="text" name="name" class="form-control" required value="<?= esc(old('name', $home['name'] ?? '')) ?>" placeholder="Ej. Funeraria Crespo">
    </div>
    <div class="mb-3">
        <label class="form-label">Descripción</label>
        <textarea name="description" class="form-control" rows="3"><?= esc(old('description', $home['description'] ?? '')) ?></textarea>
    </div>
    <div class="form-check mb-3">
        <input type="checkbox" name="is_active" value="1" class="form-check-input" id="is_active" <?= old('is_active', $home['is_active'] ?? 1) ? 'checked' : '' ?>>
        <label for="is_active" class="form-check-label">Activa</label>
    </div>
    <button type="submit" class="btn btn-primary">Guardar</button>
    <a href="<?= base_url('admin/funeral-homes') ?>" class="btn btn-secondary">Cancelar</a>
    <?= form_close() ?>
</div>
<?= $this->endSection() ?>
