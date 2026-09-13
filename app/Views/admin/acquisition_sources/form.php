<?= $this->extend('template') ?>
<?= $this->section('content') ?>
<?php helper(['form']); $isEdit = ! empty($source); ?>
<div class="container-fluid mt-4">
    <h1 class="h3 mb-3"><?= $isEdit ? 'Editar' : 'Nuevo' ?> canal de origen</h1>
    <?php if (isset($validation)): ?><div class="alert alert-danger"><?= $validation->listErrors() ?></div><?php endif; ?>
    <?= $isEdit ? form_open('admin/acquisition-sources/update/' . $source['id']) : form_open('admin/acquisition-sources/store') ?>
    <div class="row">
        <div class="col-md-6 mb-3">
            <label class="form-label">Nombre *</label>
            <input type="text" name="name" class="form-control" value="<?= esc(old('name', $source['name'] ?? '')) ?>" required>
        </div>
        <div class="col-md-6 mb-3">
            <label class="form-label">Tipo *</label>
            <select name="channel_type" class="form-control" required>
                <?php foreach ($channelLabels as $key => $label): ?>
                    <option value="<?= esc($key) ?>" <?= old('channel_type', $source['channel_type'] ?? 'social') === $key ? 'selected' : '' ?>><?= esc($label) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="col-md-6 mb-3">
            <label class="form-label">Clase icono Font Awesome</label>
            <input type="text" name="icon_class" class="form-control" placeholder="fab fa-instagram" value="<?= esc(old('icon_class', $source['icon_class'] ?? '')) ?>">
        </div>
        <div class="col-md-3 mb-3">
            <label class="form-label">Orden</label>
            <input type="number" name="sort_order" class="form-control" value="<?= esc(old('sort_order', $source['sort_order'] ?? 0)) ?>">
        </div>
        <div class="col-md-3 mb-3 d-flex align-items-end">
            <div class="form-check">
                <input class="form-check-input" type="checkbox" name="is_active" value="1" id="is_active" <?= old('is_active', $source['is_active'] ?? 1) ? 'checked' : '' ?>>
                <label class="form-check-label" for="is_active">Activo</label>
            </div>
        </div>
    </div>
    <button type="submit" class="btn btn-primary">Guardar</button>
    <a href="<?= base_url('admin/acquisition-sources') ?>" class="btn btn-secondary">Cancelar</a>
    <?= form_close() ?>
</div>
<?= $this->endSection() ?>
