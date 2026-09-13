<?= $this->extend('template') ?>
<?= $this->section('title') ?>
Documentos Vencidos y Próximos a Vencer
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="container-fluid">
    <div class="row">
        <?= $this->include('sidebar') ?>
        
        <div class="col-md-9 ml-sm-auto col-lg-10 px-4">
            <!-- Header -->
            <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                <h2 class="h3">
                    <i class="fas fa-exclamation-triangle text-warning"></i>
                    Dashboard de Documentos
                </h2>
                <div class="btn-toolbar mb-2 mb-md-0">
                    <a href="<?= base_url('admin/client_document_catalog') ?>" class="btn btn-outline-primary">
                        <i class="fas fa-clipboard-list"></i> Catálogo de Documentos
                    </a>
                </div>
            </div>

            <!-- Estadísticas generales -->
            <div class="row mb-4">
                <div class="col-md-6">
                    <div class="card text-white bg-danger">
                        <div class="card-body">
                            <div class="d-flex justify-content-between">
                                <div>
                                    <h4 class="card-title"><?= count($overdueDocuments) ?></h4>
                                    <p class="card-text">Documentos Vencidos</p>
                                </div>
                                <div class="align-self-center">
                                    <i class="fas fa-exclamation-circle fa-2x"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="card text-white bg-warning">
                        <div class="card-body">
                            <div class="d-flex justify-content-between">
                                <div>
                                    <h4 class="card-title"><?= count($upcomingDocuments) ?></h4>
                                    <p class="card-text">Próximos a Vencer (7 días)</p>
                                </div>
                                <div class="align-self-center">
                                    <i class="fas fa-clock fa-2x"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Documentos Vencidos -->
            <div class="card mb-4">
                <div class="card-header bg-danger text-white">
                    <h5 class="mb-0">
                        <i class="fas fa-exclamation-circle"></i>
                        Documentos Vencidos
                        <span class="badge badge-light text-danger ml-2"><?= count($overdueDocuments) ?></span>
                    </h5>
                </div>
                <div class="card-body p-0">
                    <?php if (empty($overdueDocuments)): ?>
                        <div class="text-center p-4 text-muted">
                            <i class="fas fa-check-circle fa-3x text-success mb-3"></i>
                            <h5>¡Excelente!</h5>
                            <p>No hay documentos vencidos en este momento.</p>
                        </div>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead class="bg-light">
                                    <tr>
                                        <th>Cliente</th>
                                        <th>Documento</th>
                                        <th>Proyecto</th>
                                        <th>Fecha Límite</th>
                                        <th>Días Vencido</th>
                                        <th>Estado</th>
                                        <th>Acciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($overdueDocuments as $doc): ?>
                                        <?php
                                            $daysOverdue = round((time() - strtotime($doc['due_date'])) / (60 * 60 * 24));
                                        ?>
                                        <tr class="table-danger">
                                            <td>
                                                <strong><?= esc($doc['client_name']) ?></strong>
                                                <br><small class="text-muted"><?= esc($doc['client_email']) ?></small>
                                            </td>
                                            <td>
                                                <strong><?= esc($doc['document_name']) ?></strong>
                                                <br><small class="text-muted"><?= esc($doc['process_stage']) ?></small>
                                                <?php if ($doc['payment_method']): ?>
                                                    <br><span class="badge badge-secondary"><?= esc($doc['payment_method']) ?></span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <?= esc($doc['property_name']) ?>
                                                <br><small class="text-muted"><?= esc($doc['zone_name']) ?></small>
                                            </td>
                                            <td>
                                                <?= date('d/m/Y', strtotime($doc['due_date'])) ?>
                                            </td>
                                            <td>
                                                <span class="badge badge-danger">
                                                    <?= $daysOverdue ?> días
                                                </span>
                                            </td>
                                            <td>
                                                <span class="badge badge-secondary"><?= esc($doc['status']) ?></span>
                                            </td>
                                            <td>
                                                <a href="<?= base_url('admin/client-document-checklist/view/' . $doc['account_id']) ?>" 
                                                   class="btn btn-sm btn-primary" target="_blank">
                                                    <i class="fas fa-eye"></i> Ver Checklist
                                                </a>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Documentos Próximos a Vencer -->
            <div class="card mb-4">
                <div class="card-header bg-warning text-dark">
                    <h5 class="mb-0">
                        <i class="fas fa-clock"></i>
                        Documentos Próximos a Vencer (7 días)
                        <span class="badge badge-dark ml-2"><?= count($upcomingDocuments) ?></span>
                    </h5>
                </div>
                <div class="card-body p-0">
                    <?php if (empty($upcomingDocuments)): ?>
                        <div class="text-center p-4 text-muted">
                            <i class="fas fa-calendar-check fa-3x text-info mb-3"></i>
                            <h5>Todo en orden</h5>
                            <p>No hay documentos próximos a vencer en los próximos 7 días.</p>
                        </div>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead class="bg-light">
                                    <tr>
                                        <th>Cliente</th>
                                        <th>Documento</th>
                                        <th>Proyecto</th>
                                        <th>Fecha Límite</th>
                                        <th>Días Restantes</th>
                                        <th>Estado</th>
                                        <th>Acciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($upcomingDocuments as $doc): ?>
                                        <?php
                                            $daysRemaining = round((strtotime($doc['due_date']) - time()) / (60 * 60 * 24));
                                            $urgencyClass = $daysRemaining <= 3 ? 'table-warning' : '';
                                        ?>
                                        <tr class="<?= $urgencyClass ?>">
                                            <td>
                                                <strong><?= esc($doc['client_name']) ?></strong>
                                                <br><small class="text-muted"><?= esc($doc['client_email']) ?></small>
                                            </td>
                                            <td>
                                                <strong><?= esc($doc['document_name']) ?></strong>
                                                <br><small class="text-muted"><?= esc($doc['process_stage']) ?></small>
                                                <?php if ($doc['payment_method']): ?>
                                                    <br><span class="badge badge-secondary"><?= esc($doc['payment_method']) ?></span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <?= esc($doc['property_name']) ?>
                                                <br><small class="text-muted"><?= esc($doc['zone_name']) ?></small>
                                            </td>
                                            <td>
                                                <?= date('d/m/Y', strtotime($doc['due_date'])) ?>
                                            </td>
                                            <td>
                                                <?php
                                                    $badgeClass = $daysRemaining <= 1 ? 'badge-danger' : 
                                                                 ($daysRemaining <= 3 ? 'badge-warning' : 'badge-info');
                                                ?>
                                                <span class="badge <?= $badgeClass ?>">
                                                    <?= $daysRemaining ?> días
                                                </span>
                                            </td>
                                            <td>
                                                <span class="badge badge-secondary"><?= esc($doc['status']) ?></span>
                                            </td>
                                            <td>
                                                <a href="<?= base_url('admin/client-document-checklist/view/' . $doc['account_id']) ?>" 
                                                   class="btn btn-sm btn-primary" target="_blank">
                                                    <i class="fas fa-eye"></i> Ver Checklist
                                                </a>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Resumen por etapa de proceso -->
            <div class="card">
                <div class="card-header bg-info text-white">
                    <h5 class="mb-0">
                        <i class="fas fa-chart-pie"></i>
                        Resumen por Etapa del Proceso
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <?php
                        // Agrupar documentos por etapa
                        $stageStats = [];
                        foreach (array_merge($overdueDocuments, $upcomingDocuments) as $doc) {
                            $stage = $doc['process_stage'];
                            if (!isset($stageStats[$stage])) {
                                $stageStats[$stage] = ['overdue' => 0, 'upcoming' => 0];
                            }
                            
                            $daysUntilDue = round((strtotime($doc['due_date']) - time()) / (60 * 60 * 24));
                            if ($daysUntilDue < 0) {
                                $stageStats[$stage]['overdue']++;
                            } else {
                                $stageStats[$stage]['upcoming']++;
                            }
                        }
                        ?>
                        
                        <?php if (empty($stageStats)): ?>
                            <div class="col-12 text-center text-muted">
                                <p>No hay documentos pendientes o próximos a vencer.</p>
                            </div>
                        <?php else: ?>
                            <?php foreach ($stageStats as $stage => $stats): ?>
                                <div class="col-md-3 mb-3">
                                    <div class="card border">
                                        <div class="card-body text-center">
                                            <h6 class="card-title"><?= esc($stage) ?></h6>
                                            <div class="row">
                                                <div class="col-6">
                                                    <small class="text-danger">Vencidos</small>
                                                    <div class="h5 text-danger"><?= $stats['overdue'] ?></div>
                                                </div>
                                                <div class="col-6">
                                                    <small class="text-warning">Por vencer</small>
                                                    <div class="h5 text-warning"><?= $stats['upcoming'] ?></div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Auto-refresh cada 5 minutos para mantener los datos actualizados
setInterval(() => {
    location.reload();
}, 300000);

// Mostrar alertas si hay documentos críticos
<?php if (!empty($overdueDocuments)): ?>
document.addEventListener('DOMContentLoaded', function() {
    const criticalOverdue = <?= count(array_filter($overdueDocuments, function($doc) {
        return round((time() - strtotime($doc['due_date'])) / (60 * 60 * 24)) > 7;
    })) ?>;
    
    if (criticalOverdue > 0) {
        Swal.fire({
            icon: 'warning',
            title: '¡Atención!',
            html: `Hay <strong>${criticalOverdue}</strong> documento(s) vencido(s) por más de 7 días.<br>Es urgente gestionar estos documentos.`,
            confirmButtonColor: '#dc3545',
            confirmButtonText: 'Entendido'
        });
    }
});
<?php endif; ?>
</script>

<?= $this->endSection() ?>