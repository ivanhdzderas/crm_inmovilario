<?= $this->extend('template') ?>

<?= $this->section('content') ?>
<div class="container mt-5">
    <h1 class="text-center mb-4">Crear Nuevo Tipo de Documento</h1>
    <hr>
    <?php if (isset($validation)): ?>
        <div class="alert alert-danger">
            <?= $validation->listErrors() ?>
        </div>
    <?php endif; ?>
    
    <form action="<?= base_url('admin/document_types/store') ?>" method="post">
        <div class="form-group">
            <label for="name">Nombre del documento:</label>
            <input type="text" class="form-control" name="name" id="name" value="<?= old('name') ?>">
        </div>
        <div class="form-group">
            <label for="description">Descripción:</label>
            <textarea class="form-control" name="description" id="description" rows="3"><?= old('description') ?></textarea>
        </div>
        <div class="form-group form-check">
            <input type="checkbox" class="form-check-input" name="is_required" id="is_required" value="1" <?= old('is_required') ? 'checked' : '' ?>>
            <label class="form-check-label" for="is_required">¿Es un documento requerido?</label>
        </div>
        <button type="submit" class="btn btn-primary">Guardar Documento</button>
        <a href="<?= base_url('admin/document_types') ?>" class="btn btn-secondary">Cancelar</a>
    </form>
</div>
<?= $this->endSection() ?>
