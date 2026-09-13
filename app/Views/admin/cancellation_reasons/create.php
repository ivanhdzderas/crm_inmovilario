<?= $this->extend('template') ?>

<?= $this->section('content') ?>
<div class="container mt-4">
    <h1>Nuevo motivo de cancelación</h1>
    <hr>

    <?php if (isset($validation)): ?>
        <div class="alert alert-danger"><?= $validation->listErrors() ?></div>
    <?php endif; ?>

    <?= form_open('admin/cancellation-reasons/store') ?>
        <div class="mb-3">
            <label class="form-label">Motivo</label>
            <input type="text" name="name" class="form-control" value="<?= old('name') ?>" required>
        </div>
        <div class="mb-3">
            <label class="form-label">Categoría</label>
            <select name="category" class="form-select" required>
                <option value="both">Ambos (prospecto y apartado)</option>
                <option value="prospect">Solo descarte de prospecto</option>
                <option value="apartado">Solo cancelación de apartado</option>
            </select>
        </div>
        <div class="mb-3">
            <label class="form-label">Orden</label>
            <input type="number" name="sort_order" class="form-control" value="<?= old('sort_order', 0) ?>">
        </div>
        <div class="form-check mb-3">
            <input class="form-check-input" type="checkbox" name="is_active" value="1" id="is_active" checked>
            <label class="form-check-label" for="is_active">Activo</label>
        </div>
        <button type="submit" class="btn btn-primary">Guardar</button>
        <a href="<?= base_url('admin/cancellation-reasons') ?>" class="btn btn-secondary">Cancelar</a>
    <?= form_close() ?>
</div>
<?= $this->endSection() ?>
