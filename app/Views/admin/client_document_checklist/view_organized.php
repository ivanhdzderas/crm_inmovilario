<?= $this->extend('template') ?>
<?= $this->section('title') ?>
Checklist de Documentos - <?= esc($account['nombre_inmobiliaria']) ?>
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="container-fluid">
    <div class="row">
        <?= $this->include('sidebar') ?>
        
        <div class="col-md-9 ml-sm-auto col-lg-10 px-4">
            <!-- Header -->
            <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                <h2 class="h3">
                    <i class="fas fa-clipboard-check text-primary"></i>
                    Checklist de Documentos
                </h2>
                <div class="btn-toolbar mb-2 mb-md-0">
                    <a href="<?= base_url('admin/prospects') ?>" class="btn btn-outline-secondary">
                        <i class="fas fa-arrow-left"></i> Volver a Prospectos
                    </a>
                </div>
            </div>

            <!-- Información del cliente y proyecto -->
            <div class="card mb-4 shadow-sm">
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-3">
                            <h6 class="text-muted mb-1">Cliente:</h6>
                            <p class="mb-0"><strong><?= esc($account['nombre_cliente']) ?> <?= esc($account['apellido_cliente']) ?></strong></p>
                            <small class="text-muted"><?= esc($account['email_cliente']) ?></small>
                        </div>
                        <div class="col-md-3">
                            <h6 class="text-muted mb-1">Proyecto:</h6>
                            <p class="mb-0"><strong><?= esc($account['nombre_inmobiliaria']) ?></strong></p>
                            <small class="text-muted"><?= esc($account['nombre_zona']) ?></small>
                        </div>
                        <div class="col-md-3">
                            <h6 class="text-muted mb-1">Método de Pago:</h6>
                            <span class="badge badge-<?= 
                                $account['payment_method'] === 'Infonavit' ? 'success' : 
                                ($account['payment_method'] === 'Fovissste' ? 'info' : 
                                ($account['payment_method'] === 'Bancario' ? 'warning' : 'primary'))
                            ?> p-2">
                                <?= esc($account['payment_method'] ?? 'No especificado') ?>
                            </span>
                        </div>
                        <div class="col-md-3">
                            <h6 class="text-muted mb-1">Progreso:</h6>
                            <?php $progressPercentage = $stats['progress_percentage'] ?? 0; ?>
                            <div class="progress mb-1" style="height: 20px;">
                                <div class="progress-bar bg-success" role="progressbar" 
                                     style="width: <?= $progressPercentage ?>%" 
                                     aria-valuenow="<?= $progressPercentage ?>" 
                                     aria-valuemin="0" aria-valuemax="100">
                                    <?= $progressPercentage ?>%
                                </div>
                            </div>
                            <small class="text-muted"><?= $stats['completed'] ?> de <?= $stats['total'] ?> completados</small>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Documentos organizados por método de pago -->
            <?php if (!empty($documentsOrganized)): ?>
                <?php foreach ($documentsOrganized as $paymentMethod => $stages): ?>
                    <div class="card mb-4">
                        <div class="card-header">
                            <h5 class="mb-0">
                                <i class="fas fa-credit-card text-primary"></i>
                                <?php if ($paymentMethod === 'Todos'): ?>
                                    Documentos Generales
                                    <span class="badge badge-secondary">Aplican a todos los tipos de crédito</span>
                                <?php else: ?>
                                    Documentos Específicos: <?= esc($paymentMethod) ?>
                                    <span class="badge badge-<?= 
                                        $paymentMethod === 'Infonavit' ? 'success' : 
                                        ($paymentMethod === 'Fovissste' ? 'info' : 
                                        ($paymentMethod === 'Bancario' ? 'warning' : 'primary'))
                                    ?>">Requerido para este tipo de crédito</span>
                                <?php endif; ?>
                            </h5>
                        </div>
                        <div class="card-body p-0">
                            <!-- Pestañas por etapa del proceso -->
                            <nav>
                                <div class="nav nav-tabs" id="nav-tab-<?= str_replace(' ', '', $paymentMethod) ?>" role="tablist">
                                    <?php $firstStage = true; ?>
                                    <?php foreach ($stages as $stageName => $documents): ?>
                                        <button class="nav-link <?= $firstStage ? 'active' : '' ?>" 
                                                id="nav-<?= str_replace(' ', '', $paymentMethod . $stageName) ?>-tab" 
                                                data-bs-toggle="tab" 
                                                data-bs-target="#nav-<?= str_replace(' ', '', $paymentMethod . $stageName) ?>" 
                                                type="button" role="tab">
                                            <?= esc($stageName) ?>
                                            <span class="badge badge-light ml-1"><?= count($documents) ?></span>
                                        </button>
                                        <?php $firstStage = false; ?>
                                    <?php endforeach; ?>
                                </div>
                            </nav>
                            <div class="tab-content" id="nav-tabContent-<?= str_replace(' ', '', $paymentMethod) ?>">
                                <?php $firstStage = true; ?>
                                <?php foreach ($stages as $stageName => $documents): ?>
                                    <div class="tab-pane fade <?= $firstStage ? 'show active' : '' ?>" 
                                         id="nav-<?= str_replace(' ', '', $paymentMethod . $stageName) ?>" 
                                         role="tabpanel">
                                        <div class="table-responsive">
                                            <table class="table table-hover mb-0">
                                                <thead class="bg-light">
                                                    <tr>
                                                        <th width="40%">Documento</th>
                                                        <th width="15%">Estado</th>
                                                        <th width="15%">Archivo</th>
                                                        <th width="15%">Costo</th>
                                                        <th width="15%">Acciones</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <?php foreach ($documents as $doc): ?>
                                                        <tr class="document-row" data-doc-id="<?= $doc['id'] ?>">
                                                            <td>
                                                                <div class="d-flex align-items-start">
                                                                    <?php if ($doc['document_type'] === 'Obligatorio'): ?>
                                                                        <i class="fas fa-star text-warning me-2 mt-1"></i>
                                                                    <?php elseif ($doc['requires_notary']): ?>
                                                                        <i class="fas fa-certificate text-info me-2 mt-1"></i>
                                                                    <?php else: ?>
                                                                        <i class="fas fa-file-alt text-muted me-2 mt-1"></i>
                                                                    <?php endif; ?>
                                                                    <div>
                                                                        <strong><?= esc($doc['document_name']) ?></strong>
                                                                        <?php if ($doc['document_type'] === 'Obligatorio'): ?>
                                                                            <span class="badge badge-warning badge-sm ml-1">Obligatorio</span>
                                                                        <?php endif; ?>
                                                                        <?php if ($doc['requires_notary']): ?>
                                                                            <span class="badge badge-info badge-sm ml-1">Requiere Notario</span>
                                                                        <?php endif; ?>
                                                                        
                                                                        <?php if (!empty($doc['document_description'])): ?>
                                                                            <br><small class="text-muted"><?= esc($doc['document_description']) ?></small>
                                                                        <?php endif; ?>
                                                                        
                                                                        <?php if (!empty($doc['notes'])): ?>
                                                                            <br><small class="text-info"><strong>Notas:</strong> <?= esc($doc['notes']) ?></small>
                                                                        <?php endif; ?>
                                                                        
                                                                        <br><small class="text-muted">
                                                                            <strong>Categoría:</strong> <?= esc($doc['category']) ?>
                                                                        </small>
                                                                    </div>
                                                                </div>
                                                            </td>
                                                            <td>
                                                                <?php
                                                                    $statusClass = match($doc['status']) {
                                                                        'Completado' => 'success',
                                                                        'En Proceso' => 'info',
                                                                        'No Aplica' => 'secondary',
                                                                        default => 'warning'
                                                                    };
                                                                ?>
                                                                <span class="badge badge-<?= $statusClass ?> p-2">
                                                                    <?= esc($doc['status']) ?>
                                                                </span>
                                                                
                                                                <?php if ($doc['completed_date']): ?>
                                                                    <br><small class="text-success">
                                                                        Completado: <?= date('d/m/Y', strtotime($doc['completed_date'])) ?>
                                                                    </small>
                                                                <?php endif; ?>
                                                            </td>
                                                            <td>
                                                                <?php if ($doc['file_path']): ?>
                                                                    <a href="<?= base_url($doc['file_path']) ?>" target="_blank" 
                                                                       class="btn btn-sm btn-outline-primary">
                                                                        <i class="fas fa-file-download"></i> Ver
                                                                    </a>
                                                                <?php else: ?>
                                                                    <button type="button" class="btn btn-sm btn-outline-secondary" 
                                                                            onclick="uploadFile(<?= $doc['id'] ?>)">
                                                                        <i class="fas fa-upload"></i> Subir
                                                                    </button>
                                                                <?php endif; ?>
                                                            </td>
                                                            <td>
                                                                <?php if ($doc['actual_cost'] && $doc['actual_cost'] > 0): ?>
                                                                    <strong class="text-success">$<?= number_format($doc['actual_cost'], 2) ?></strong>
                                                                    <br><small class="text-muted">Costo real</small>
                                                                <?php elseif ($doc['cost_estimate'] && $doc['cost_estimate'] > 0): ?>
                                                                    <span class="text-muted">~$<?= number_format($doc['cost_estimate'], 2) ?></span>
                                                                    <br><small class="text-muted">Estimado</small>
                                                                <?php else: ?>
                                                                    <span class="text-muted">Sin costo</span>
                                                                <?php endif; ?>
                                                            </td>
                                                            <td>
                                                                <div class="btn-group" role="group">
                                                                    <button type="button" class="btn btn-sm btn-outline-primary" 
                                                                            onclick="editDocument(<?= $doc['id'] ?>)" 
                                                                            title="Editar estado">
                                                                        <i class="fas fa-edit"></i>
                                                                    </button>
                                                                </div>
                                                            </td>
                                                        </tr>
                                                    <?php endforeach; ?>
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                    <?php $firstStage = false; ?>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="alert alert-info text-center">
                    <i class="fas fa-info-circle fa-2x mb-3"></i>
                    <h5>No hay documentos en el checklist</h5>
                    <p>El checklist se creará automáticamente cuando el cliente realice un apartado.</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Modal para editar documento -->
<div class="modal fade" id="editDocumentModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Gestionar Documento</h5>
                <button type="button" class="close" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <form id="editDocumentForm">
                <div class="modal-body">
                    <input type="hidden" id="edit_checklist_id" name="checklist_id">
                    
                    <div class="form-group">
                        <label for="edit_status">Estado del Documento:</label>
                        <select class="form-control" id="edit_status" name="status" required>
                            <option value="Pendiente">Pendiente</option>
                            <option value="En Proceso">En Proceso</option>
                            <option value="Completado">Completado</option>
                            <option value="No Aplica">No Aplica</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label for="edit_notes">Notas y Comentarios:</label>
                        <textarea class="form-control" id="edit_notes" name="notes" rows="3" 
                                  placeholder="Agregar notas sobre el estado del documento..."></textarea>
                    </div>
                    
                    <div class="form-group">
                        <label for="edit_actual_cost">Costo Real:</label>
                        <input type="number" class="form-control" id="edit_actual_cost" name="actual_cost" 
                               step="0.01" placeholder="0.00">
                        <small class="form-text text-muted">Dejar en blanco si no tiene costo</small>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> Guardar Cambios
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal para subir archivo -->
<div class="modal fade" id="uploadFileModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Subir Archivo</h5>
                <button type="button" class="close" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <form id="uploadFileForm" enctype="multipart/form-data" 
                  action="<?= base_url('admin/client-document-checklist/upload-file') ?>" method="POST">
                <div class="modal-body">
                    <input type="hidden" id="upload_checklist_id" name="checklist_id">
                    
                    <div class="form-group">
                        <label for="document_file">Seleccionar Archivo:</label>
                        <input type="file" class="form-control-file" id="document_file" name="document_file" 
                               accept=".jpg,.jpeg,.png,.pdf,.docx" required>
                        <small class="form-text text-muted">
                            <strong>Formatos permitidos:</strong> JPG, JPEG, PNG, PDF, DOCX<br>
                            <strong>Tamaño máximo:</strong> 10MB
                        </small>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-upload"></i> Subir Archivo
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>

<script>
// Funciones para gestionar documentos
function editDocument(checklistId) {
    const row = document.querySelector(`[data-doc-id="${checklistId}"]`);
    const statusBadge = row.querySelector('.badge');
    const notesElement = row.querySelector('.text-info');
    
    document.getElementById('edit_checklist_id').value = checklistId;
    document.getElementById('edit_status').value = statusBadge.textContent.trim();
    
    // Extraer notas si existen
    if (notesElement) {
        const notesText = notesElement.textContent.replace('Notas:', '').trim();
        document.getElementById('edit_notes').value = notesText;
    }
    
    $('#editDocumentModal').modal('show');
}

function uploadFile(checklistId) {
    document.getElementById('upload_checklist_id').value = checklistId;
    $('#uploadFileModal').modal('show');
}

// Manejar envío de formulario de edición
document.getElementById('editDocumentForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    
    fetch('<?= base_url('admin/client-document-checklist/update-status') ?>', {
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
                text: data.message || 'Error al actualizar el documento'
            });
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

// Navegación suave entre pestañas
document.addEventListener('DOMContentLoaded', function() {
    // Activar tooltips si están disponibles
    if (typeof $().tooltip === 'function') {
        $('[title]').tooltip();
    }
});
</script>

<?= $this->endSection() ?>