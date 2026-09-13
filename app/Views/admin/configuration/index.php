<?= $this->extend('template') ?>

<?= $this->section('title') ?>
Configuración del Sistema
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center pt-3 pb-2 mb-3 border-bottom">
        <h1 class="h2"><i class="fas fa-cogs text-primary"></i> Configuración del Sistema</h1>
    </div>

    <?php if (session()->getFlashdata('success')): ?>
        <div class="alert alert-success"><?= session()->getFlashdata('success') ?></div>
    <?php endif; ?>

    <div class="card">
        <div class="card-body">
            <?= form_open('admin/settings/update') ?>
                
                <h5 class="card-title">Configuración General</h5>
                <p class="card-text">Ajustes para la información de la empresa que aparece en los documentos.</p>
                
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="company_name" class="form-label">Nombre de la Inmobiliaria</label>
                        <input type="text" class="form-control" id="company_name" name="company_name" value="<?= esc($settings['company_name'] ?? '') ?>">
                        <small class="text-muted">Este nombre aparecerá en las fichas PDF.</small>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="company_website" class="form-label">Sitio Web de la Inmobiliaria</label>
                        <input type="text" class="form-control" id="company_website" name="company_website" value="<?= esc($settings['company_website'] ?? '') ?>">
                        <small class="text-muted">Esta URL aparecerá en el pie de página de las fichas PDF.</small>
                    </div>
                </div>

                <hr class="my-4">

                <h5 class="card-title">Configuración de Documentos</h5>
                <p class="card-text">Define qué documentos se deben solicitar según el tipo de inmueble.</p>

                <div class="form-group">
                    <label for="document_requirement_type">Requerir todos los documentos para:</label>
                    <select class="form-control" id="document_requirement_type" name="document_requirement_type">
                        <option value="all" <?= ($settings['document_requirement_type'] ?? 'all') === 'all' ? 'selected' : '' ?>>Todos los inmuebles</option>
                        <option value="new" <?= ($settings['document_requirement_type'] ?? '') === 'new' ? 'selected' : '' ?>>Solo inmuebles nuevos</option>
                        <option value="used" <?= ($settings['document_requirement_type'] ?? '') === 'used' ? 'selected' : '' ?>>Solo inmuebles usados</option>
                    </select>
                </div>

                <div class="mt-4">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> Guardar Configuración
                    </button>
                </div>
            <?= form_close() ?>
        </div>
    </div>
</div>
<?= $this->endSection() ?>