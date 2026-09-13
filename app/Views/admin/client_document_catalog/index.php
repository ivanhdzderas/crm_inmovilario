<?= $this->extend('template') ?>
<?= $this->section('title') ?>
Catálogo de Tipos de Crédito
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="container-fluid">
    <div class="row">
        <div class="col-md-9 ml-sm-auto col-lg-10 px-4">
            <!-- Header -->
            <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                <h2 class="h3">
                    <i class="fas fa-credit-card text-primary"></i>
                    Catálogo de Tipos de Crédito
                </h2>
                <div class="btn-toolbar mb-2 mb-md-0">
                    <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#addCreditTypeModal">
                        <i class="fas fa-plus"></i> Agregar Tipo de Crédito
                    </button>
                </div>
            </div>

            <div class="alert alert-info">
                <i class="fas fa-info-circle"></i>
                <strong>Instrucciones:</strong> Haz clic en cualquier tipo de crédito para gestionar los documentos específicos que requiere.
            </div>

            <!-- Grid de tipos de crédito -->
            <div class="row" id="creditTypesGrid">
                <?php if (!empty($creditTypes)): ?>
                    <?php foreach ($creditTypes as $creditType): ?>
                        <div class="col-md-4 col-lg-3 mb-4">
                            <div class="card credit-type-card shadow-sm h-100">
                                <div class="card-body text-center">
                                    <div class="mb-3">
                                        <?php if (!empty($creditType['icon_url'])): ?>
                                            <img src="<?= esc($creditType['icon_url']) ?>" alt="Icono <?= esc($creditType['name']) ?>" style="height:56px; width:56px; object-fit:contain;">
                                        <?php else: ?>
                                            <i class="fas fa-folder-open fa-3x text-primary"></i>
                                        <?php endif; ?>
                                    </div>
                                    <h5 class="card-title"><?= esc($creditType['name']) ?></h5>
                                    <?php if (!empty($creditType['icon_url'])): ?>
                                        <div class="mb-2">
                                            <span class="badge badge-success">Icono cargado</span>
                                        </div>
                                    <?php endif; ?>
                                    <p class="text-muted mb-3">
                                        <?= $creditType['document_count'] ?> documento(s) configurado(s)
                                    </p>
                                    <div class="btn-group d-flex" role="group">
                                        <a href="<?= base_url('admin/client_document_catalog/manage/' . urlencode($creditType['name'])) ?>" 
                                           class="btn btn-primary btn-sm">
                                            <i class="fas fa-cog"></i> Gestionar
                                        </a>
                                        <button type="button" class="btn btn-danger btn-sm" 
                                                onclick="deleteCreditType('<?= esc($creditType['name']) ?>')">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="col-12">
                        <div class="text-center py-5">
                            <i class="fas fa-credit-card fa-4x text-muted mb-3"></i>
                            <h4 class="text-muted">No hay tipos de crédito configurados</h4>
                            <p class="text-muted">Comienza agregando tu primer tipo de crédito</p>
                            <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#addCreditTypeModal">
                                <i class="fas fa-plus"></i> Agregar Primer Tipo de Crédito
                            </button>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- Modal para agregar tipo de crédito -->
<div class="modal fade" id="addCreditTypeModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Agregar Nuevo Tipo de Crédito</h5>
                <button type="button" class="close" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <form id="addCreditTypeForm">
                <div class="modal-body">
                    <div class="form-group">
                        <label for="credit_type_name">Nombre del Tipo de Crédito:</label>
                        <input type="text" class="form-control" id="credit_type_name" name="credit_type_name" 
                               placeholder="Ej: Infonavit, Bancario, Contado..." required>
                        <small class="form-text text-muted">
                            Este será el nombre que identifique al tipo de crédito en todo el sistema.
                        </small>
                    </div>

                    <div class="form-group">
                        <label for="credit_type_icon">Icono de institución (opcional):</label>
                        <input type="file" class="form-control-file" id="credit_type_icon" name="credit_type_icon" accept=".png,.jpg,.jpeg,.webp,image/png,image/jpeg,image/webp">
                        <small class="form-text text-muted">
                            Se guardará con el nombre del tipo de crédito para usarlo en la ficha PDF.
                        </small>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-plus"></i> Crear Tipo de Crédito
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal de confirmación para eliminar -->
<div class="modal fade" id="deleteCreditTypeModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title">Confirmar Eliminación</h5>
                <button type="button" class="close text-white" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="text-center">
                    <i class="fas fa-exclamation-triangle fa-4x text-warning mb-3"></i>
                    <h5>¿Estás seguro?</h5>
                    <p>Se eliminará el tipo de crédito <strong id="creditTypeToDelete"></strong> y <strong>todos</strong> sus documentos asociados.</p>
                    <div class="alert alert-warning">
                        <small><i class="fas fa-warning"></i> Esta acción no se puede deshacer.</small>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-danger" id="confirmDeleteBtn">
                    <i class="fas fa-trash"></i> Eliminar Definitivamente
                </button>
            </div>
        </div>
    </div>
</div>

<script>
let creditTypeToDelete = '';

// Agregar nuevo tipo de crédito
document.getElementById('addCreditTypeForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    
    fetch('<?= base_url('admin/client_document_catalog/add-credit-type') ?>', {
        method: 'POST',
        body: formData,
        headers: {
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            Swal.fire({
                icon: 'success',
                title: '¡Éxito!',
                text: data.message,
                timer: 1500,
                showConfirmButton: false
            }).then(() => {
                location.reload();
            });
        } else {
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: data.message
            });
        }
        
        $('#addCreditTypeModal').modal('hide');
        this.reset();
    })
    .catch(error => {
        console.error('Error:', error);
        Swal.fire({
            icon: 'error',
            title: 'Error',
            text: 'Error de conexión'
        });
        $('#addCreditTypeModal').modal('hide');
    });
});

// Eliminar tipo de crédito
function deleteCreditType(creditType) {
    creditTypeToDelete = creditType;
    document.getElementById('creditTypeToDelete').textContent = creditType;
    $('#deleteCreditTypeModal').modal('show');
}

// Confirmar eliminación
document.getElementById('confirmDeleteBtn').addEventListener('click', function() {
    if (!creditTypeToDelete) return;
    
    const formData = new FormData();
    formData.append('credit_type', creditTypeToDelete);
    
    fetch('<?= base_url('admin/client_document_catalog/delete-credit-type') ?>', {
        method: 'POST',
        body: formData,
        headers: {
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            Swal.fire({
                icon: 'success',
                title: '¡Eliminado!',
                text: data.message,
                timer: 1500,
                showConfirmButton: false
            }).then(() => {
                location.reload();
            });
        } else {
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: data.message
            });
        }
        
        $('#deleteCreditTypeModal').modal('hide');
        creditTypeToDelete = '';
    })
    .catch(error => {
        console.error('Error:', error);
        Swal.fire({
            icon: 'error',
            title: 'Error',
            text: 'Error de conexión'
        });
        $('#deleteCreditTypeModal').modal('hide');
    });
});

// Estilo para las cards
document.addEventListener('DOMContentLoaded', function() {
    // Agregar efectos hover a las cards
    const cards = document.querySelectorAll('.credit-type-card');
    cards.forEach(card => {
        card.addEventListener('mouseenter', function() {
            this.style.transform = 'translateY(-2px)';
            this.style.transition = 'transform 0.2s ease-in-out';
        });
        
        card.addEventListener('mouseleave', function() {
            this.style.transform = 'translateY(0)';
        });
    });
});
</script>

<style>
.credit-type-card {
    transition: transform 0.2s ease-in-out, box-shadow 0.2s ease-in-out;
    cursor: pointer;
}

.credit-type-card:hover {
    box-shadow: 0 4px 8px rgba(0,0,0,0.1) !important;
}

.credit-type-card .fas.fa-folder-open {
    color: #007bff;
    margin-bottom: 1rem;
}

.btn-group.d-flex .btn {
    flex: 1;
}
</style>

<?= $this->endSection() ?>