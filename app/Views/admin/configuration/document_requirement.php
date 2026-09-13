<?= $this->extend('template') ?>

<?= $this->section('content') ?>
<div class="container-fluid py-4">
    <div class="row">
        <div class="col-lg-6 col-md-8 mx-auto">
            <div class="card">
                <div class="card-header p-3">
                    <h5 class="mb-0"><i class="fa fa-cogs me-2"></i> <?= esc($title) ?></h5>
                </div>
                <div class="card-body">
                    <?php if (session()->getFlashdata('success')): ?>
                        <div class="alert alert-success text-white">
                            <?= session()->getFlashdata('success') ?>
                        </div>
                    <?php endif; ?>
                    <?php if (session()->getFlashdata('error')): ?>
                        <div class="alert alert-danger text-white">
                            <?= session()->getFlashdata('error') ?>
                        </div>
                    <?php endif; ?>

                    <?= form_open('admin/config/document_requirement') ?>
                        <div class="mb-3">
                            <label for="requirement_type" class="form-label">¿Cuándo se deben solicitar todos los documentos del inmueble?</label>
                            <p class="text-muted small">
                                Esta configuración determina en qué momento el sistema marcará como "pendientes" todos los documentos necesarios para un inmueble.
                            </p>
                            <select name="requirement_type" id="requirement_type" class="form-select">
                                <option value="all" <?= ($currentValue === 'all') ? 'selected' : '' ?>>
                                    Siempre (para todos los inmuebles, nuevos o usados)
                                </option>
                                <option value="new" <?= ($currentValue === 'new') ? 'selected' : '' ?>>
                                    Solo para inmuebles marcados como "Nuevos"
                                </option>
                                <option value="used" <?= ($currentValue === 'used') ? 'selected' : '' ?>>
                                    Solo para inmuebles marcados como "Usados"
                                </option>
                            </select>
                        </div>
                        <div class="text-end">
                            <button type="submit" class="btn btn-primary">
                                <i class="fa fa-save me-1"></i> Guardar Configuración
                            </button>
                        </div>
                    <?= form_close() ?>
                </div>
            </div>
        </div>
    </div>
</div>
<?= $this->endSection() ?>