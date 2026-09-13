<?= $this->extend('template') ?>

<?= $this->section('content') ?>
<div class="container mt-4">
    <h1>Editar motivo de cancelación</h1>
    <hr>

    <?php if (isset($validation)): ?>
        <div class="alert alert-danger"><?= $validation->listErrors() ?></div>
    <?php endif; ?>

    <?= form_open('admin/cancellation-reasons/update/' . $reason['id']) ?>
        <div class="mb-3">
            <label class="form-label">Motivo</label>
            <input type="text" name="name" class="form-control" value="<?= old('name', $reason['name']) ?>" required>
        </div>
        <div class="mb-3">
            <label class="form-label">Categoría</label>
            <select name="category" class="form-select" required>
                <?php foreach (['both' => 'Ambos', 'prospect' => 'Prospecto', 'apartado' => 'Apartado'] as $value => $label): ?>
                    <option value="<?= $value ?>" <?= old('category', $reason['category']) === $value ? 'selected' : '' ?>><?= $label ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="mb-3">
            <label class="form-label">Orden</label>
            <input type="number" name="sort_order" class="form-control" value="<?= old('sort_order', $reason['sort_order']) ?>">
        </div>
        <div class="form-check mb-3">
            <input class="form-check-input" type="checkbox" name="is_active" value="1" id="is_active" <?= ! empty($reason['is_active']) ? 'checked' : '' ?>>
            <label class="form-check-label" for="is_active">Activo</label>
        </div>
        <button type="submit" class="btn btn-primary">Actualizar</button>
        <a href="<?= base_url('admin/cancellation-reasons') ?>" class="btn btn-secondary">Cancelar</a>
    <?= form_close() ?>
</div>
<?= $this->endSection() ?>
