<!-- app/Views/admin/zones/form.php -->

<?= $this->extend('template') ?>

<?= $this->section('title') ?>
<?= $title ?? ($zone ? 'Editar Zona' : 'Nueva Zona') ?>
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="container-fluid py-4">
    <div class="row justify-content-center">
        <div class="col-md-8 col-lg-6">
            <div class="card">
                <div class="card-header pb-0">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <h6 class="mb-0">
                                <i class="fa <?= $zone ? 'fa-edit' : 'fa-plus' ?> me-2 text-primary"></i>
                                <?= $zone ? 'Editar Zona' : 'Nueva Zona' ?>
                            </h6>
                            <p class="text-sm mb-0">
                                <?= $zone ? 'Modificar los datos de la zona' : 'Crear una nueva zona geográfica' ?>
                            </p>
                        </div>
                        <div>
                            <a href="<?= site_url('admin/zones') ?>" class="btn btn-outline-secondary btn-sm">
                                <i class="fa fa-arrow-left me-1"></i>
                                Volver
                            </a>
                        </div>
                    </div>
                </div>
                
                <div class="card-body">
                    
                    <!-- Mostrar errores de validación -->
                    <?php if ($validation && $validation->getErrors()): ?>
                        <div class="alert alert-danger">
                            <i class="fa fa-exclamation-triangle me-2"></i>
                            <strong>Por favor corrige los siguientes errores:</strong>
                            <ul class="mb-0 mt-2">
                                <?php foreach ($validation->getErrors() as $error): ?>
                                    <li><?= esc($error) ?></li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    <?php endif; ?>

                    <!-- Formulario -->
                    <?= form_open($zone ? site_url('admin/zones/update/' . $zone['id']) : site_url('admin/zones/store'), [
                        'id' => 'zoneForm',
                        'class' => 'needs-validation',
                        'novalidate' => true
                    ]) ?>
                    
                        <div class="mb-4">
                            <label for="name" class="form-label">
                                <i class="fa fa-map-marker-alt text-primary me-1"></i>
                                Nombre de la Zona <span class="text-danger">*</span>
                            </label>
                            <input type="text" 
                                   class="form-control <?= ($validation && $validation->hasError('name')) ? 'is-invalid' : '' ?>"
                                   id="name" 
                                   name="name" 
                                   value="<?= old('name', $zone['name'] ?? '') ?>"
                                   placeholder="Ej: Norte, Sur, Centro, Zona Industrial..."
                                   required
                                   maxlength="100"
                                   autocomplete="off">
                            
                            <!-- Feedback de validación -->
                            <?php if ($validation && $validation->hasError('name')): ?>
                                <div class="invalid-feedback">
                                    <?= $validation->getError('name') ?>
                                </div>
                            <?php else: ?>
                                <div class="invalid-feedback">
                                    Por favor ingresa un nombre válido para la zona (mínimo 3 caracteres).
                                </div>
                                <div class="valid-feedback">
                                    ¡Perfecto! Nombre de zona válido.
                                </div>
                            <?php endif; ?>
                            
                            <small class="form-text text-muted">
                                <i class="fa fa-info-circle me-1"></i>
                                El nombre debe ser único y descriptivo. Se usará para organizar clientes e inmuebles.
                            </small>
                        </div>

                        <!-- Vista previa (solo en edición) -->
                        <?php if ($zone): ?>
                            <div class="alert alert-info">
                                <h6 class="mb-2">
                                    <i class="fa fa-eye me-1"></i>
                                    Vista Previa
                                </h6>
                                <div class="d-flex align-items-center">
                                    <div class="flex-grow-1">
                                        <p class="mb-1">
                                            <strong>ID:</strong> #<?= $zone['id'] ?>
                                        </p>
                                        <p class="mb-1">
                                            <strong>Nombre Actual:</strong> <?= esc($zone['name']) ?>
                                        </p>
                                        <p class="mb-0 text-muted small">
                                            <i class="fa fa-clock me-1"></i>
                                            Esta zona está actualmente en uso en el sistema
                                        </p>
                                    </div>
                                    <div class="ms-3">
                                        <div class="avatar avatar-xl bg-gradient-primary rounded-circle">
                                            <i class="fa fa-map-marker-alt fa-2x text-white"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endif; ?>

                        <!-- Botones de acción -->
                        <div class="row">
                            <div class="col-md-6">
                                <button type="button" 
                                        class="btn btn-outline-secondary w-100" 
                                        onclick="window.history.back()">
                                    <i class="fa fa-times me-1"></i>
                                    Cancelar
                                </button>
                            </div>
                            <div class="col-md-6">
                                <button type="submit" 
                                        class="btn btn-primary w-100" 
                                        id="submitBtn">
                                    <i class="fa <?= $zone ? 'fa-save' : 'fa-plus' ?> me-1"></i>
                                    <?= $zone ? 'Actualizar Zona' : 'Crear Zona' ?>
                                </button>
                            </div>
                        </div>

                    <?= form_close() ?>

                    <!-- Información adicional -->
                    <div class="mt-4 pt-3 border-top">
                        <h6 class="text-muted mb-2">
                            <i class="fa fa-lightbulb me-1"></i>
                            Consejos para nombrar zonas
                        </h6>
                        <div class="row">
                            <div class="col-md-6">
                                <ul class="list-unstyled small text-muted">
                                    <li><i class="fa fa-check text-success me-1"></i> Norte, Sur, Este, Oeste</li>
                                    <li><i class="fa fa-check text-success me-1"></i> Centro, Periferia</li>
                                    <li><i class="fa fa-check text-success me-1"></i> Zona Industrial, Residencial</li>
                                </ul>
                            </div>
                            <div class="col-md-6">
                                <ul class="list-unstyled small text-muted">
                                    <li><i class="fa fa-check text-success me-1"></i> Por colonias principales</li>
                                    <li><i class="fa fa-check text-success me-1"></i> Por municipios</li>
                                    <li><i class="fa fa-check text-success me-1"></i> Por códigos postales</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Script para validación y UX mejorado -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('zoneForm');
    const nameInput = document.getElementById('name');
    const submitBtn = document.getElementById('submitBtn');
    
    // Configurar validación de Bootstrap
    form.addEventListener('submit', function(e) {
        if (!form.checkValidity()) {
            e.preventDefault();
            e.stopPropagation();
        } else {
            // Deshabilitar botón y mostrar loading
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<i class="fa fa-spinner fa-spin me-1"></i> <?= $zone ? "Actualizando..." : "Creando..." ?>';
        }
        
        form.classList.add('was-validated');
    });

    // Validación en tiempo real del nombre
    nameInput.addEventListener('input', function() {
        const value = this.value.trim();
        
        // Limpiar clases previas
        this.classList.remove('is-valid', 'is-invalid');
        
        if (value.length >= 3 && value.length <= 100) {
            this.classList.add('is-valid');
        } else if (value.length > 0) {
            this.classList.add('is-invalid');
        }
    });

    // Auto-focus en el campo nombre
    nameInput.focus();
    
    // Seleccionar texto si está editando
    <?php if ($zone): ?>
    nameInput.select();
    <?php endif; ?>

    // Atajos de teclado
    document.addEventListener('keydown', function(e) {
        // Ctrl+S para guardar
        if (e.ctrlKey && e.key === 's') {
            e.preventDefault();
            form.dispatchEvent(new Event('submit'));
        }
        
        // Escape para cancelar
        if (e.key === 'Escape') {
            window.history.back();
        }
    });

    // Prevenir pérdida de datos accidental
    let formChanged = false;
    nameInput.addEventListener('input', () => formChanged = true);
    
    window.addEventListener('beforeunload', function(e) {
        if (formChanged && nameInput.value.trim() !== '<?= addslashes($zone['name'] ?? '') ?>') {
            e.preventDefault();
            e.returnValue = '';
        }
    });
});
</script>

<?= $this->endSection() ?>