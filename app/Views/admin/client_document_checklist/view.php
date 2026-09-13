<?= $this->extend('template') ?>
<?= $this->section('title') ?>
Checklist de Documentos - <?= esc($account['nombre_inmobiliaria']) ?>
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="container-fluid">
    <div class="row">
        <?= $this->include('sidebar') ?>
        
        <div class="col-md-9 ml-sm-auto col-lg-10 px-4">
            <!-- Header con información de la cuenta -->
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

            <!-- Card con información del cliente y proyecto -->
            <div class="row mb-4">
                <div class="col-md-12">
                    <div class="card shadow-sm">
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-4">
                                    <h6 class="text-muted">Cliente:</h6>
                                    <p class="mb-0"><strong><?= esc($account['nombre_cliente']) ?> <?= esc($account['apellido_cliente']) ?></strong></p>
                                    <small class="text-muted">Email: <?= esc($account['email_cliente']) ?></small>
                                </div>
                                <div class="col-md-4">
                                    <h6 class="text-muted">Proyecto:</h6>
                                    <p class="mb-0"><strong><?= esc($account['nombre_inmobiliaria']) ?></strong></p>
                                    <small class="text-muted">Zona: <?= esc($account['nombre_zona']) ?></small>
                                </div>
                                <div class="col-md-4">
                                    <h6 class="text-muted">Método de Pago:</h6>
                                    <span class="badge badge-<?= 
                                        $account['payment_method'] === 'Infonavit' ? 'success' : 
                                        ($account['payment_method'] === 'Fovissste' ? 'info' : 
                                        ($account['payment_method'] === 'Bancario' ? 'warning' : 'primary'))
                                    ?> p-2">
                                        <?= esc($account['payment_method'] ?? 'No especificado') ?>
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Estadísticas del checklist -->
            <div class="row mb-4">
                <div class="col-md-3">
                    <div class="card text-white bg-info">
                        <div class="card-body">
                            <div class="d-flex justify-content-between">
                                <div>
                                    <h4 class="card-title"><?= $stats['total'] ?></h4>
                                    <p class="card-text">Total Documentos</p>
                                </div>
                                <div class="align-self-center">
                                    <i class="fas fa-file-alt fa-2x"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card text-white bg-success">
                        <div class="card-body">
                            <div class="d-flex justify-content-between">
                                <div>
                                    <h4 class="card-title"><?= $stats['completed'] ?></h4>
                                    <p class="card-text">Completados</p>
                                </div>
                                <div class="align-self-center">
                                    <i class="fas fa-check-circle fa-2x"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card text-white bg-warning">
                        <div class="card-body">
                            <div class="d-flex justify-content-between">
                                <div>
                                    <h4 class="card-title"><?= $stats['pending'] ?></h4>
                                    <p class="card-text">Pendientes</p>
                                </div>
                                <div class="align-self-center">
                                    <i class="fas fa-clock fa-2x"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card text-white bg-danger">
                        <div class="card-body">
                            <div class="d-flex justify-content-between">
                                <div>
                                    <h4 class="card-title"><?= $stats['overdue'] ?></h4>
                                    <p class="card-text">Vencidos</p>
                                </div>
                                <div class="align-self-center">
                                    <i class="fas fa-exclamation-triangle fa-2x"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Barra de progreso -->
            <?php 
                $progressPercentage = $stats['total'] > 0 ? round(($stats['completed'] / $stats['total']) * 100, 1) : 0;
            ?>
            <div class="row mb-4">
                <div class="col-md-12">
                    <div class="card">
                        <div class="card-body">
                            <h6>Progreso General: <?= $progressPercentage ?>%</h6>
                            <div class="progress">
                                <div class="progress-bar bg-success" role="progressbar" 
                                     style="width: <?= $progressPercentage ?>%" 
                                     aria-valuenow="<?= $progressPercentage ?>" 
                                     aria-valuemin="0" aria-valuemax="100">
                                    <?= $progressPercentage ?>%
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Documentos organizados por etapa -->
            <?php if (!empty($documentsByStage)): ?>
                <?php foreach ($documentsByStage as $stage => $documents): ?>
                    <div class="card mb-4">
                        <div class="card-header bg-light">
                            <h5 class="mb-0">
                                <i class="fas fa-folder-open text-primary"></i>
                                Etapa: <?= esc($stage) ?>
                                <span class="badge badge-secondary ml-2"><?= count($documents) ?> documentos</span>
                            </h5>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-hover">
                                    <thead>
                                        <tr>
                                            <th>Documento</th>
                                            <th>Estado</th>
                                            <th>Fecha Límite</th>
                                            <th>Archivo</th>
                                            <th>Costo</th>
                                            <th>Acciones</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($documents as $doc): ?>
                                            <tr class="document-row" data-doc-id="<?= $doc['id'] ?>">
                                                <td>
                                                    <strong><?= esc($doc['document_name']) ?></strong>
                                                    <?php if (!empty($doc['description'])): ?>
                                                        <br><small class="text-muted"><?= esc($doc['description']) ?></small>
                                                    <?php endif; ?>
                                                    <?php if (!empty($doc['notes'])): ?>
                                                        <br><small class="text-info"><strong>Notas:</strong> <?= esc($doc['notes']) ?></small>
                                                    <?php endif; ?>
                                                </td>
                                                <td>
                                                    <?php
                                                        $statusClass = match($doc['status']) {
                                                            'Completado' => 'success',
                                                            'En Proceso' => 'info',
                                                            'Vencido' => 'danger',
                                                            'No Aplica' => 'secondary',
                                                            default => 'warning'
                                                        };
                                                    ?>
                                                    <span class="badge badge-<?= $statusClass ?> p-2">
                                                        <?= esc($doc['status']) ?>
                                                    </span>
                                                </td>
                                                <td>
                                                    <?php if ($doc['due_date']): ?>
                                                        <?= date('d/m/Y', strtotime($doc['due_date'])) ?>
                                                        <?php
                                                            $daysUntilDue = round((strtotime($doc['due_date']) - time()) / (60 * 60 * 24));
                                                            if ($daysUntilDue < 0): ?>
                                                                <br><small class="text-danger"><strong>Vencido hace <?= abs($daysUntilDue) ?> días</strong></small>
                                                            <?php elseif ($daysUntilDue <= 7): ?>
                                                                <br><small class="text-warning"><strong>Vence en <?= $daysUntilDue ?> días</strong></small>
                                                            <?php endif; ?>
                                                    <?php else: ?>
                                                        <span class="text-muted">Sin fecha límite</span>
                                                    <?php endif; ?>
                                                </td>
                                                <td>
                                                    <?php if ($doc['file_path']): ?>
                                                        <a href="<?= base_url($doc['file_path']) ?>" target="_blank" class="btn btn-sm btn-outline-primary">
                                                            <i class="fas fa-file-download"></i> Ver archivo
                                                        </a>
                                                    <?php else: ?>
                                                        <span class="text-muted">Sin archivo</span>
                                                    <?php endif; ?>
                                                </td>
                                                <td>
                                                    <?php if ($doc['estimated_cost'] || $doc['actual_cost']): ?>
                                                        <?php if ($doc['actual_cost']): ?>
                                                            <strong>$<?= number_format($doc['actual_cost'], 2) ?></strong>
                                                        <?php else: ?>
                                                            <span class="text-muted">~$<?= number_format($doc['estimated_cost'], 2) ?></span>
                                                        <?php endif; ?>
                                                    <?php else: ?>
                                                        <span class="text-muted">-</span>
                                                    <?php endif; ?>
                                                </td>
                                                <td>
                                                    <div class="btn-group" role="group">
                                                        <button type="button" class="btn btn-sm btn-outline-primary" 
                                                                onclick="editDocument(<?= $doc['id'] ?>)">
                                                            <i class="fas fa-edit"></i>
                                                        </button>
                                                        <button type="button" class="btn btn-sm btn-outline-secondary" 
                                                                onclick="uploadFile(<?= $doc['id'] ?>)">
                                                            <i class="fas fa-upload"></i>
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
                <?php endforeach; ?>
            <?php else: ?>
                <div class="alert alert-info text-center">
                    <i class="fas fa-info-circle fa-2x mb-3"></i>
                    <h5>No hay documentos en el checklist</h5>
                    <p>El checklist de documentos se creará automáticamente cuando el cliente realice un apartado.</p>
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
                <h5 class="modal-title">Editar Estado del Documento</h5>
                <button type="button" class="close" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <form id="editDocumentForm">
                <div class="modal-body">
                    <input type="hidden" id="edit_checklist_id" name="checklist_id">
                    
                    <div class="form-group">
                        <label for="edit_status">Estado:</label>
                        <select class="form-control" id="edit_status" name="status" required>
                            <option value="Pendiente">Pendiente</option>
                            <option value="En Proceso">En Proceso</option>
                            <option value="Completado">Completado</option>
                            <option value="No Aplica">No Aplica</option>
                            <option value="Vencido">Vencido</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label for="edit_notes">Notas:</label>
                        <textarea class="form-control" id="edit_notes" name="notes" rows="3" 
                                  placeholder="Notas adicionales sobre el documento..."></textarea>
                    </div>
                    
                    <div class="form-group">
                        <label for="edit_actual_cost">Costo Real:</label>
                        <input type="number" class="form-control" id="edit_actual_cost" name="actual_cost" 
                               step="0.01" placeholder="0.00">
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
                        <label for="document_file">Archivo:</label>
                        <input type="file" class="form-control-file" id="document_file" name="document_file" 
                               accept=".jpg,.jpeg,.png,.pdf,.docx" required>
                        <small class="form-text text-muted">
                            Formatos permitidos: JPG, JPEG, PNG, PDF, DOCX. Tamaño máximo: 10MB
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

<script>
// Editar documento
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

// Subir archivo
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

// Auto-refresh cada 30 segundos
setInterval(() => {
    // Actualizar solo las estadísticas sin recargar la página
    fetch(`<?= base_url('admin/client-document-checklist/api-get-checklist/' . $account['id']) ?>`)
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Actualizar contadores en las cards de estadísticas
            // (Implementar según necesidad)
        }
    })
    .catch(error => console.error('Error updating stats:', error));
}, 30000);
</script>

<?= $this->endSection() ?>