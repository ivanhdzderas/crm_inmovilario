<?= $this->extend('template') ?>
<?= $this->section('title') ?>
Gestionar Documentos - <?= esc($creditType) ?>
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="container-fluid">
    <div class="row">
        <div class="col-md-9 ml-sm-auto col-lg-10 px-4">
            <!-- Header -->
            <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                <h2 class="h3">
                    <i class="fas fa-file-alt text-primary"></i>
                    Documentos para <?= esc($creditType) ?>
                </h2>
                <div class="btn-toolbar mb-2 mb-md-0">
                    <a href="<?= base_url('admin/client_document_catalog') ?>" class="btn btn-secondary mr-2">
                        <i class="fas fa-arrow-left"></i> Volver al Catálogo
                    </a>
                    <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#addDocumentModal">
                        <i class="fas fa-plus"></i> Agregar Documento
                    </button>
                </div>
            </div>

            <!-- Información del tipo de crédito -->
            <div class="card mb-4">
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-8">
                            <?php if (!empty($creditTypeIconUrl)): ?>
                                <div class="mb-2">
                                    <img src="<?= esc($creditTypeIconUrl) ?>" alt="Icono <?= esc($creditType) ?>" style="height:48px; width:48px; object-fit:contain;">
                                </div>
                            <?php endif; ?>
                            <h5 class="mb-1">Tipo de Crédito: <span class="badge badge-primary"><?= esc($creditType) ?></span></h5>
                            <p class="text-muted mb-0">
                                Total de documentos configurados: <strong><?= $totalDocuments ?></strong>
                            </p>
                        </div>
                        <div class="col-md-4 text-right">
                            <button type="button" class="btn btn-info btn-sm" onclick="reorderMode()">
                                <i class="fas fa-sort"></i> Reordenar Documentos
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Lista de documentos -->
            <?php if (!empty($documents)): ?>
                <div class="card">
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover mb-0" id="documentsTable">
                                <thead class="bg-light">
                                    <tr>
                                        <th width="5%">#</th>
                                        <th width="30%">Documento</th>
                                        <th width="15%">Categoría</th>
                                        <th width="10%">Tipo</th>
                                        <th width="15%">Etapa</th>
                                        <th width="10%">Costo Est.</th>
                                        <th width="15%">Acciones</th>
                                    </tr>
                                </thead>
                                <tbody id="documentsTableBody">
                                    <?php foreach ($documents as $index => $document): ?>
                                        <tr data-document-id="<?= $document['id'] ?>">
                                            <td>
                                                <span class="drag-handle text-muted" style="cursor: move;">
                                                    <i class="fas fa-grip-vertical"></i>
                                                </span>
                                                <?= $document['sort_order'] ?>
                                            </td>
                                            <td>
                                                <div>
                                                    <strong><?= esc($document['name']) ?></strong>
                                                    <?php if ($document['document_type'] === 'Obligatorio'): ?>
                                                        <span class="badge badge-warning badge-sm ml-1">Obligatorio</span>
                                                    <?php endif; ?>
                                                    <?php if ($document['requires_notary']): ?>
                                                        <span class="badge badge-info badge-sm ml-1">Notario</span>
                                                    <?php endif; ?>
                                                    
                                                    <?php if (!empty($document['description'])): ?>
                                                        <br><small class="text-muted"><?= esc($document['description']) ?></small>
                                                    <?php endif; ?>
                                                </div>
                                            </td>
                                            <td><?= esc($document['category']) ?></td>
                                            <td>
                                                <?php
                                                    $typeClass = match($document['document_type']) {
                                                        'Obligatorio' => 'danger',
                                                        'Opcional' => 'success',
                                                        'Condicional' => 'warning',
                                                        default => 'secondary'
                                                    };
                                                ?>
                                                <span class="badge badge-<?= $typeClass ?>"><?= esc($document['document_type']) ?></span>
                                            </td>
                                            <td><?= esc($document['process_stage']) ?></td>
                                            <td>
                                                <?php if ($document['cost_estimate'] > 0): ?>
                                                    $<?= number_format($document['cost_estimate'], 2) ?>
                                                <?php else: ?>
                                                    <span class="text-muted">Sin costo</span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <div class="btn-group" role="group">
                                                    <button type="button" class="btn btn-sm btn-primary" 
                                                            onclick="editDocument(<?= $document['id'] ?>)"
                                                            title="Editar documento">
                                                        <i class="fas fa-edit"></i>
                                                    </button>
                                                    <button type="button" class="btn btn-sm btn-danger" 
                                                            onclick="deleteDocument(<?= $document['id'] ?>)"
                                                            title="Eliminar documento">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            <?php else: ?>
                <div class="card">
                    <div class="card-body text-center py-5">
                        <i class="fas fa-file-alt fa-4x text-muted mb-3"></i>
                        <h4 class="text-muted">No hay documentos configurados</h4>
                        <p class="text-muted">Agrega el primer documento para este tipo de crédito</p>
                        <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#addDocumentModal">
                            <i class="fas fa-plus"></i> Agregar Primer Documento
                        </button>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Modal para agregar documento -->
<div class="modal fade" id="addDocumentModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Agregar Documento para <?= esc($creditType) ?></h5>
                <button type="button" class="close" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <form id="addDocumentForm">
                <div class="modal-body">
                    <input type="hidden" name="credit_type" value="<?= esc($creditType) ?>">
                    
                    <div class="row">
                        <div class="col-md-8">
                            <div class="form-group">
                                <label for="document_name">Nombre del Documento:</label>
                                <input type="text" class="form-control" id="document_name" name="document_name" 
                                       placeholder="Ej: Comprobante de ingresos" required>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="category">Categoría:</label>
                                <input type="text" class="form-control" id="category" name="category" 
                                       placeholder="Ej: Financiera" required>
                            </div>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="description">Descripción (opcional):</label>
                        <textarea class="form-control" id="description" name="description" rows="2"
                                  placeholder="Descripción detallada del documento..."></textarea>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="document_type">Tipo:</label>
                                <select class="form-control" id="document_type" name="document_type" required>
                                    <option value="">Seleccionar...</option>
                                    <option value="Obligatorio">Obligatorio</option>
                                    <option value="Opcional">Opcional</option>
                                    <option value="Condicional">Condicional</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="process_stage">Etapa del Proceso:</label>
                                <select class="form-control" id="process_stage" name="process_stage" required>
                                    <option value="">Seleccionar...</option>
                                    <option value="Apartado">Apartado</option>
                                    <option value="Escrituración">Escrituración</option>
                                    <option value="Entrega">Entrega</option>
                                    <option value="Post-venta">Post-venta</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="cost_estimate">Costo Estimado:</label>
                                <input type="number" class="form-control" id="cost_estimate" name="cost_estimate" 
                                       step="0.01" placeholder="0.00">
                            </div>
                        </div>
                    </div>
                    
                    <div class="form-check">
                        <input type="checkbox" class="form-check-input" id="requires_notary" name="requires_notary" value="1">
                        <label class="form-check-label" for="requires_notary">
                            Requiere notario o gestión legal
                        </label>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-plus"></i> Agregar Documento
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal para editar documento -->
<div class="modal fade" id="editDocumentModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Editar Documento</h5>
                <button type="button" class="close" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <form id="editDocumentForm">
                <div class="modal-body">
                    <input type="hidden" name="document_id" id="edit_document_id">
                    
                    <div class="row">
                        <div class="col-md-8">
                            <div class="form-group">
                                <label for="edit_document_name">Nombre del Documento:</label>
                                <input type="text" class="form-control" id="edit_document_name" name="document_name" 
                                       placeholder="Ej: Comprobante de ingresos" required>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="edit_category">Categoría:</label>
                                <input type="text" class="form-control" id="edit_category" name="category" 
                                       placeholder="Ej: Financiera" required>
                            </div>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="edit_description">Descripción (opcional):</label>
                        <textarea class="form-control" id="edit_description" name="description" rows="2"
                                  placeholder="Descripción detallada del documento..."></textarea>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="edit_document_type">Tipo:</label>
                                <select class="form-control" id="edit_document_type" name="document_type" required>
                                    <option value="">Seleccionar...</option>
                                    <option value="Obligatorio">Obligatorio</option>
                                    <option value="Opcional">Opcional</option>
                                    <option value="Condicional">Condicional</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="edit_process_stage">Etapa del Proceso:</label>
                                <select class="form-control" id="edit_process_stage" name="process_stage" required>
                                    <option value="">Seleccionar...</option>
                                    <option value="Apartado">Apartado</option>
                                    <option value="Escrituración">Escrituración</option>
                                    <option value="Entrega">Entrega</option>
                                    <option value="Post-venta">Post-venta</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="edit_cost_estimate">Costo Estimado:</label>
                                <input type="number" class="form-control" id="edit_cost_estimate" name="cost_estimate" 
                                       step="0.01" placeholder="0.00">
                            </div>
                        </div>
                    </div>
                    
                    <div class="form-check">
                        <input type="checkbox" class="form-check-input" id="edit_requires_notary" name="requires_notary" value="1">
                        <label class="form-check-label" for="edit_requires_notary">
                            Requiere notario o gestión legal
                        </label>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> Actualizar Documento
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
// Agregar nuevo documento
document.getElementById('addDocumentForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    
    fetch('<?= base_url('admin/client_document_catalog/add-document') ?>', {
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
            if (data.errors) {
                let errorMessage = '';
                for (const [field, message] of Object.entries(data.errors)) {
                    errorMessage += message + '\n';
                }
                Swal.fire({
                    icon: 'error',
                    title: 'Errores de validación',
                    text: errorMessage
                });
            } else {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: data.message
                });
            }
        }
        
        $('#addDocumentModal').modal('hide');
    })
    .catch(error => {
        console.error('Error:', error);
        Swal.fire({
            icon: 'error',
            title: 'Error',
            text: 'Error de conexión'
        });
        $('#addDocumentModal').modal('hide');
    });
});

// Editar documento
function editDocument(documentId) {
    console.log('Editando documento:', documentId); // Debug
    
    // Obtener datos del documento
    fetch(`<?= base_url('admin/client_document_catalog/get-document/') ?>${documentId}`, {
        method: 'GET',
        headers: {
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
    .then(response => {
        console.log('Response status:', response.status); // Debug
        return response.json();
    })
    .then(data => {
        console.log('Data received:', data); // Debug
        
        if (data.success) {
            const doc = data.document;
            
            // Rellenar el modal con los datos del documento
            document.getElementById('edit_document_id').value = doc.id;
            document.getElementById('edit_document_name').value = doc.name;
            document.getElementById('edit_category').value = doc.category;
            document.getElementById('edit_description').value = doc.description || '';
            document.getElementById('edit_document_type').value = doc.document_type;
            document.getElementById('edit_process_stage').value = doc.process_stage;
            document.getElementById('edit_cost_estimate').value = doc.cost_estimate || '';
            document.getElementById('edit_requires_notary').checked = doc.requires_notary == 1;
            
            // Mostrar el modal
            $('#editDocumentModal').modal('show');
        } else {
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: data.message
            });
        }
    })
    .catch(error => {
        console.error('Error:', error);
        Swal.fire({
            icon: 'error',
            title: 'Error',
            text: 'Error al cargar los datos del documento'
        });
    });
}

// Manejar envío del formulario de edición
document.getElementById('editDocumentForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const documentId = document.getElementById('edit_document_id').value;
    const formData = new FormData(this);
    
    fetch(`<?= base_url('admin/client_document_catalog/update-document/') ?>${documentId}`, {
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
            if (data.errors) {
                let errorMessage = '';
                for (const [field, message] of Object.entries(data.errors)) {
                    errorMessage += message + '\n';
                }
                Swal.fire({
                    icon: 'error',
                    title: 'Errores de validación',
                    text: errorMessage
                });
            } else {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: data.message
                });
            }
        }
        
        $('#editDocumentModal').modal('hide');
    })
    .catch(error => {
        console.error('Error:', error);
        Swal.fire({
            icon: 'error',
            title: 'Error',
            text: 'Error de conexión'
        });
        $('#editDocumentModal').modal('hide');
    });
});

// Eliminar documento
function deleteDocument(documentId) {
    Swal.fire({
        title: '¿Estás seguro?',
        text: "Este documento será eliminado permanentemente",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#3085d6',
        confirmButtonText: 'Sí, eliminar',
        cancelButtonText: 'Cancelar'
    }).then((result) => {
        if (result.isConfirmed) {
            fetch(`<?= base_url('admin/client_document_catalog/delete-document/') ?>${documentId}`, {
                method: 'POST',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Eliminado',
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
            })
            .catch(error => {
                console.error('Error:', error);
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'Error de conexión'
                });
            });
        }
    });
}

// Modo de reordenamiento (básico)
function reorderMode() {
    Swal.fire({
        icon: 'info',
        title: 'Modo Reordenamiento',
        text: 'Arrastra los documentos para cambiar su orden (funcionalidad por implementar)',
        confirmButtonText: 'Entendido'
    });
}
</script>

<style>
.drag-handle {
    cursor: move;
}

.table tbody tr:hover {
    background-color: #f8f9fa;
}

.badge-sm {
    font-size: 0.75em;
}

/* Estilos personalizados para botones */
.btn-primary {
    background-color: #007bff !important;
    border-color: #007bff !important;
    color: #ffffff !important;
}

.btn-primary:hover {
    background-color: #0056b3 !important;
    border-color: #004085 !important;
    color: #ffffff !important;
}

.btn-secondary {
    background-color: #6c757d !important;
    border-color: #6c757d !important;
    color: #ffffff !important;
}

.btn-secondary:hover {
    background-color: #545b62 !important;
    border-color: #4e555b !important;
    color: #ffffff !important;
}

.btn-danger {
    background-color: #dc3545 !important;
    border-color: #dc3545 !important;
    color: #ffffff !important;
}

.btn-danger:hover {
    background-color: #c82333 !important;
    border-color: #bd2130 !important;
    color: #ffffff !important;
}

.btn-info {
    background-color: #17a2b8 !important;
    border-color: #17a2b8 !important;
    color: #ffffff !important;
}

.btn-info:hover {
    background-color: #138496 !important;
    border-color: #117a8b !important;
    color: #ffffff !important;
}
</style>

<?= $this->endSection() ?>