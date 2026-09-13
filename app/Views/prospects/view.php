<!-- app/Views/prospects/view.php -->
<?php helper('prospect'); ?>
<?php $isFuneralProspect = ! empty($prospect['is_funeral_prospect']); ?>

<!-- Enmarcado para uso dentro del Offcanvas -->
<div class="offcanvas-inner p-2">
    <h5 class="mb-3 text-primary">Detalle y Seguimiento</h5>
    <div class="d-flex mb-2">
        <a href="<?= site_url('prospects') ?>" class="btn btn-sm btn-secondary me-2" data-offcanvas-close="1">
            <i class="fa fa-arrow-left"></i> Volver
        </a>
        <button type="button" class="btn btn-sm btn-primary btn-offcanvas-edit" data-url="<?= site_url('prospects/edit/' . $prospect['id']) ?>">
            <i class="fa fa-edit me-1"></i> Modificar
        </button>
        <?php if (! $isFuneralProspect): ?>
        <a href="<?= site_url('tools/isrCalculator') ?>" target="_blank" class="btn btn-sm btn-outline-info ms-2" title="Abrir Calculadora de ISR">
            <i class="fa fa-calculator me-1"></i> Calc. ISR
        </a>
        <?php endif; ?>
        
        <?php if (! prospect_is_cancelled($prospect['status']) && ! in_array(ucfirst(strtolower($prospect['status'])), ['Cierre'])): ?>
            <button type="button" class="btn btn-sm btn-danger ms-2" onclick="showDeactivateProspectModal(<?= $prospect['id'] ?>)" title="Cancelar prospecto">
                <i class="fa fa-times-circle me-1"></i> Cancelar
            </button>
        <?php endif; ?>
    </div>

    <!-- Mostrar mensajes de éxito o error -->
    <?php if (session()->getFlashdata('success')): ?>
        <script>
        document.addEventListener('DOMContentLoaded', function() {
            Swal.fire({
                icon: 'success',
                title: '¡Éxito!',
                text: '<?= addslashes(session()->getFlashdata('success')) ?>',
                confirmButtonColor: '#28a745',
                timer: 3000,
                timerProgressBar: true
            });
        });
        </script>
    <?php endif; ?>
    <?php if (session()->getFlashdata('error')): ?>
        <script>
        document.addEventListener('DOMContentLoaded', function() {
            <?php 
                $errors = session()->getFlashdata('error');
                if (is_array($errors)) {
                    $errorText = implode('\\n• ', $errors);
                    $errorText = '• ' . $errorText;
                } else {
                    $errorText = $errors;
                }
            ?>
            Swal.fire({
                icon: 'error',
                title: 'Error al Guardar',
                html: '<?= addslashes($errorText) ?>',
                confirmButtonColor: '#dc3545'
            });
        });
        </script>
    <?php endif; ?>

    <!-- Datos del prospecto arriba (ocupar todo el ancho) -->
    <div class="mb-3">
        <div class="card border-info rounded-3">
            <div class="card-header bg-info text-white p-2">
                <h6 class="mb-0">Datos del Cliente: <?= esc($prospect['full_name']) ?></h6>
            </div>
            <div class="card-body p-2">
                <p class="mb-1"><strong>Estatus:</strong>
                    <?php 
                        $badge_color = 'secondary';
                        if ($prospect['status'] == 'Nuevo') { $badge_color = 'primary'; }
                        if ($prospect['status'] == 'Cierre') { $badge_color = 'success'; }
                        if (prospect_is_cancelled($prospect['status'])) { $badge_color = 'danger'; }
                        if ($prospect['status'] == 'Seguimiento') { $badge_color = 'warning text-dark'; }
                    ?>
                    <span class="badge bg-<?= $badge_color ?>"><?= esc(prospect_status_label($prospect['status'])) ?></span>
                    <?php if (prospect_is_cancelled($prospect['status']) && ! empty($prospect['deactivation_reason_name'])): ?>
                        <button type="button" class="btn btn-link btn-sm p-0 ms-1 align-baseline"
                            <?= prospect_cancellation_popover_attrs(
                                $prospect['deactivation_reason_name'] ?? null,
                                $prospect['deactivation_notes'] ?? null,
                                $prospect['deactivated_at'] ?? null
                            ) ?>>
                            <i class="fa fa-info-circle text-danger"></i>
                        </button>
                    <?php endif; ?>
                </p>
                <?php if (prospect_is_cancelled($prospect['status'])): ?>
                    <div class="alert alert-light border-danger py-2 px-2 mb-2 small">
                        <i class="fa fa-ban text-danger me-1"></i>
                        <strong>Cancelado</strong>
                        <?php if (! empty($prospect['deactivation_reason_name'])): ?>
                            — <?= esc($prospect['deactivation_reason_name']) ?>
                        <?php endif; ?>
                        <?php if (! empty($prospect['deactivation_notes'])): ?>
                            <br><span class="text-muted"><?= esc($prospect['deactivation_notes']) ?></span>
                        <?php endif; ?>
                        <?php if (! empty($prospect['deactivated_at'])): ?>
                            <br><small class="text-muted"><?= date('d/m/Y H:i', strtotime($prospect['deactivated_at'])) ?></small>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>
                <p class="mb-1"><strong>Asesor:</strong> <?= esc($advisor_name) ?></p>
                <p class="mb-1"><strong>Tipo:</strong> <?= esc(ucfirst($prospect['client_type'] ?? 'comprador')) ?></p>
                <p class="mb-1"><strong>Zona:</strong> <?= esc($zone_name) ?></p>
                <p class="mb-1"><i class="fa fa-phone me-2 text-primary"></i> <?= esc($prospect['phone']) ?></p>
                <p class="mb-1"><i class="fa fa-envelope me-2 text-primary"></i> <?= esc($prospect['email']) ?></p>
                <p class="mb-1"><i class="fa fa-money-bill-wave me-2 text-success"></i> Crédito Estimado: <strong>$<?= esc(number_format($prospect['credit_amount'] ?? 0, 2)) ?></strong></p>
                <p class="mb-1"><i class="fa fa-credit-card me-2 text-warning"></i> Método de Pago: <?= esc($prospect['payment_method'] ?? 'N/A') ?></p>
                <?php if (! empty($prospect['acquisition_source_name'])): ?>
                    <p class="mb-1">
                        <i class="<?= esc($prospect['acquisition_source_icon'] ?? 'fa fa-bullhorn') ?> me-2 text-info"></i>
                        <strong>¿Cómo llegó?</strong>
                        <?= esc($prospect['acquisition_source_name']) ?>
                        <?php if (! empty($prospect['acquisition_detail'])): ?>
                            <br><small class="text-muted ms-4"><?= esc($prospect['acquisition_detail']) ?></small>
                        <?php endif; ?>
                    </p>
                <?php else: ?>
                    <p class="mb-1 text-muted"><i class="fa fa-bullhorn me-2"></i> Origen: <em>Sin registrar</em></p>
                <?php endif; ?>
                
                <?php if ($prospect['status'] === 'Cliente' && $clientAccount): ?>
                    <!-- Información del inmueble apartado -->
                    <hr class="my-2">
                    <h6 class="text-success mb-2"><i class="fa fa-home me-1"></i> Inmueble Apartado</h6>
                    <p class="mb-1"><strong>Propiedad:</strong> <?= esc($clientAccount['property_name']) ?></p>
                    <p class="mb-1"><strong>Clave:</strong> <?= esc($clientAccount['property_clave']) ?></p>
                    <p class="mb-1"><strong>Precio:</strong> $<?= number_format($clientAccount['property_price'] ?? 0, 2) ?></p>
                    <p class="mb-1"><strong>Apartado:</strong> $<?= number_format($clientAccount['apartado_amount'], 2) ?></p>
                    <p class="mb-1">
                        <strong>Estado de Cuenta:</strong> 
                        <span class="badge <?= $clientAccount['current_balance'] >= 0 ? 'bg-success' : 'bg-danger' ?>">
                            $<?= number_format($clientAccount['current_balance'], 2) ?>
                        </span>
                    </p>
                <?php endif; ?>
                
                <p class="mt-2 text-muted text-xs">Registrado: <?= esc($prospect['created_at']) ?></p>
            </div>
        </div>
    </div>

    <!-- Pestañas abajo: Minuta / Historial -->
    <div>
        <ul class="nav nav-tabs" id="followUpTabs" role="tablist">
            <li class="nav-item" role="presentation">
                <a class="nav-link active" id="seguimiento-tab" data-toggle="tab" href="#seguimiento" role="tab" aria-controls="seguimiento" aria-selected="true">
                    <i class="fa fa-clipboard-list"></i> Seguimiento
                    <?php if (! $isFuneralProspect): ?>
                    <span class="badge badge-primary ms-1" id="badgeIntereses"><?= count($propertyInterests) ?></span>
                    <?php endif; ?>
                    <span class="badge badge-secondary ms-1" id="badgeMinutas"><?= count($followUps) ?></span>
                </a>
            </li>
            <?php if (! empty($funerarioModuleEnabled)): ?>
            <li class="nav-item" role="presentation">
                <a class="nav-link" id="comprador-funerario-tab" data-toggle="tab" href="#compradorFunerario" role="tab" aria-controls="compradorFunerario" aria-selected="false">
                    <i class="fa fa-monument"></i> Comprador funerario
                    <span class="badge badge-secondary ms-1" id="badgeFunerario"><?= count($funeralInterests ?? []) ?></span>
                </a>
            </li>
            <?php endif; ?>
            <?php if ($prospect['status'] === 'Cliente' && $clientAccount): ?>
            <li class="nav-item" role="presentation">
                <a class="nav-link" id="cuenta-tab" data-toggle="tab" href="#cuenta" role="tab" aria-controls="cuenta" aria-selected="false">
                    <i class="fa fa-wallet"></i> Estado de Cuenta 
                    <span class="badge <?= $clientAccount['current_balance'] >= 0 ? 'badge-success' : 'badge-danger' ?>">
                        $<?= number_format($clientAccount['current_balance'], 2) ?>
                    </span>
                </a>
            </li>
            <?php endif; ?>
        </ul>

        <div class="tab-content border border-top-0 p-2 bg-white rounded-bottom">
            <div class="tab-pane fade show active" id="seguimiento" role="tabpanel" aria-labelledby="seguimiento-tab">

            <?= view('prospects/partials/seguimiento_unified_form', [
                'prospect' => $prospect,
                'isFuneralProspect' => $isFuneralProspect,
                'contact_methods' => $contact_methods,
                'activity_types' => $activity_types,
                'availableProperties' => $availableProperties,
                'propertyInterests' => $propertyInterests,
            ]) ?>

            <?php if (! $isFuneralProspect): ?>
            <!-- Inmuebles de interés -->
            <div id="seccionIntereses" class="mb-3">
                <h6 class="mb-2"><i class="fa fa-home me-1"></i> Inmuebles de interés</h6>
                <?php if ($prospect['status'] === 'Cliente'): ?>
                    <div class="alert alert-info py-2 small">
                        <i class="fa fa-info-circle me-1"></i> Cliente con inmueble apartado. Los nuevos intereses inmobiliarios ya no aplican; puede seguir registrando minutas.
                    </div>
                <?php endif; ?>

                <div id="propertyInterestsList" class="mb-2">
                    <?php if (empty($propertyInterests)): ?>
                        <div class="text-center p-4 text-muted" id="noInterestsMessage">
                            <i class="fa fa-home fa-2x mb-2 d-block"></i>
                            <p>Aún no hay inmuebles de interés registrados.</p>
                        </div>
                    <?php else: ?>
                        <?php foreach ($propertyInterests as $interest): ?>
                            <?php 
                                $apartadoAmount = $interest['apartado_amount'] ?? 0;
                                if ($apartadoAmount <= 0) {
                                    $apartadoAmount = round(($interest['price'] ?? 0) * 0.01, 2);
                                }
                            ?>
                            <div class="card mb-2 interest-item" data-interest-id="<?= $interest['id'] ?>">
                                <div class="card-body p-2">
                                    <div class="d-flex justify-content-between align-items-start">
                                        <div class="flex-grow-1">
                                            <h6 class="mb-1"><?= esc($interest['property_name']) ?> <small class="text-muted">(<?= esc($interest['clave']) ?>)</small></h6>
                                            <p class="mb-1 text-sm"><i class="fa fa-map-marker-alt text-primary me-1"></i><?= esc($interest['address']) ?></p>
                                            <p class="mb-1"><strong>Precio:</strong> $<?= number_format($interest['price'] ?? 0, 2) ?> | 
                                            <strong>Interés:</strong> 
                                            <?php 
                                                $levelClass = $interest['interest_level'] === 'Alto' ? 'bg-danger' : ($interest['interest_level'] === 'Medio' ? 'bg-warning text-dark' : 'bg-secondary');
                                            ?>
                                            <span class="badge <?= $levelClass ?>"><?= $interest['interest_level'] ?></span> | 
                                            <?php 
                                                $statusClass = $interest['status'] === 'Apartado' ? 'bg-warning text-dark' : ($interest['status'] === 'Vendido' ? 'bg-success' : ($interest['status'] === 'Cancelado' ? 'bg-danger' : 'bg-info text-white'));
                                            ?>
                                            <span class="badge <?= $statusClass ?>"><?= $interest['status'] ?></span>
                                            </p>
                                            <?php if ($interest['notes']): ?>
                                                <p class="mb-1 text-sm text-muted"><?= esc($interest['notes']) ?></p>
                                            <?php endif; ?>
                                            <?php if (!empty($interest['apartado_amount'])): ?>
                                                <div class="mt-1">
                                                    <small class="text-success">
                                                        <i class="fa fa-check-circle"></i> Apartado: $<?= number_format($interest['apartado_amount'], 2) ?> 
                                                        el <?= date('d/m/Y', strtotime($interest['apartado_date'])) ?>
                                                        <?php if ($interest['apartado_receipt']): ?>
                                                            (Recibo: <?= esc($interest['apartado_receipt']) ?>)
                                                        <?php endif; ?>
                                                    </small>
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                        <div class="ms-2">
                                            <?php if ($interest['status'] === 'Interesado' && empty($interest['apartado_amount'])): ?>
                                                <button class="btn btn-sm btn-outline-danger btn-remove-interest" data-interest-id="<?= $interest['id'] ?>" title="Quitar interés">
                                                    <i class="fa fa-times"></i>
                                                </button>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                    <?php if ($interest['status'] === 'Interesado' && empty($interest['apartado_amount'])): ?>
                                        <div class="mt-2">
                                            <button class="btn btn-sm btn-warning btn-register-apartado" data-interest-id="<?= $interest['id'] ?>" data-apartado="<?= $apartadoAmount ?>">
                                                <i class="fa fa-hand-holding-usd me-1"></i> Apartar ($<?= number_format($apartadoAmount, 2) ?>)
                                            </button>
                                        </div>
                                    <?php elseif ($interest['status'] === 'Apartado'): ?>
                                        <div class="mt-2">
                                            <button class="btn btn-sm btn-outline-danger btn-cancel-apartado" data-interest-id="<?= $interest['id'] ?>">
                                                <i class="fa fa-ban me-1"></i> Cancelar apartado
                                            </button>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>

            <hr>

            <?php endif; ?>

            <!-- Historial de minutas -->
            <div id="seccionHistorial" class="mb-3">
                <h6 class="mb-2"><i class="fa fa-history me-1"></i> Historial de Minutas</h6>
                <div id="historial">
                    <?php if (empty($followUps)): ?>
                        <p class="text-muted text-center p-3">Aún no hay seguimientos registrados para este prospecto. Empieza con una nueva minuta.</p>
                    <?php else: ?>
                        <div class="timeline">
                            <?php foreach ($followUps as $followUp): ?>
                                <div class="timeline-item mb-3">
                                    <div class="timeline-badge bg-primary"><i class="fa fa-comment-dots"></i></div>
                                    <div class="timeline-panel card">
                                        <div class="card-header p-2 bg-light border-bottom border-warning d-flex justify-content-between align-items-center">
                                            <?php 
                                            $contact_date = $followUp['contact_datetime'] ?? $followUp['created_at'];
                                            $display_date = date('d/m/Y H:i', strtotime($contact_date));
                                            ?>
                                            <div>
                                                <p class="mb-0 text-dark"><span class="badge bg-warning text-dark me-1"><?= esc($followUp['contact_method']) ?></span> por <?= esc($followUp['advisor_name'] ?? 'Asesor Desconocido') ?></p>
                                            </div>
                                            <div class="d-flex align-items-center">
                                                <small class="text-muted me-2"><?= $display_date ?></small>
                                                <div class="btn-group btn-group-sm" role="group">
                                                    <button type="button" class="btn btn-outline-primary btn-edit-minuta" 
                                                            data-id="<?= $followUp['id'] ?>" 
                                                            data-contact-datetime="<?= date('Y-m-d\TH:i', strtotime($contact_date)) ?>"
                                                            data-contact-method="<?= esc($followUp['contact_method']) ?>"
                                                            data-discussion="<?= esc($followUp['discussion_detail']) ?>"
                                                            data-personal-notes="<?= esc($followUp['personal_notes'] ?? '') ?>"
                                                            title="Editar minuta">
                                                        <i class="fas fa-edit"></i>
                                                    </button>
                                                    <button type="button" class="btn btn-outline-danger btn-delete-minuta" 
                                                            data-id="<?= $followUp['id'] ?>" 
                                                            data-date="<?= $display_date ?>"
                                                            title="Eliminar minuta">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="card-body p-2">
                                            <p class="mb-1 text-secondary"><?= nl2br(esc($followUp['discussion_detail'])) ?></p>
                                            
                                            <!-- Observaciones personales si existen -->
                                            <?php if (!empty($followUp['personal_notes'])): ?>
                                                <?php $uniqueId = 'personal_notes_' . $followUp['id']; ?>
                                                <div class="mt-2">
                                                    <a data-toggle="collapse" href="#<?= $uniqueId ?>" role="button" aria-expanded="false" class="text-decoration-none personal-notes-toggle">
                                                        <small class="text-muted">
                                                            <i class="fas fa-user-secret mr-1"></i>
                                                            <strong>Ver observaciones personales</strong> 
                                                            <i class="fas fa-chevron-down ml-1"></i>
                                                        </small>
                                                    </a>
                                                    <div class="collapse mt-2" id="<?= $uniqueId ?>">
                                                        <div class="p-2 personal-notes-content rounded-sm">
                                                            <small class="text-dark">
                                                                <i class="fas fa-lock mr-1 text-warning"></i>
                                                                <?= nl2br(esc($followUp['personal_notes'])) ?>
                                                            </small>
                                                        </div>
                                                    </div>
                                                </div>
                                            <?php endif; ?>
                                            
                                            <?php if ($followUp['activity_id']): ?>
                                                <div class="mt-2 p-2 bg-info bg-opacity-10 border border-info rounded-sm">
                                                    <small class="text-dark font-weight-bold">
                                                        <i class="fa fa-calendar-check me-1"></i>
                                                        Siguiente seguimiento:
                                                        <?= esc($followUp['activity_title'] ?? 'Cita agendada') ?>
                                                        <?php if (! empty($followUp['activity_start_time'])): ?>
                                                            (<?= date('d/m/Y H:i', strtotime($followUp['activity_start_time'])) ?>)
                                                        <?php endif; ?>
                                                    </small>
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            </div>

            <?php if (! empty($funerarioModuleEnabled)): ?>
            <div class="tab-pane fade" id="compradorFunerario" role="tabpanel" aria-labelledby="comprador-funerario-tab">
                <?= view('prospects/partials/comprador_funerario_tab', [
                    'prospect' => $prospect,
                    'funeralInterests' => $funeralInterests ?? [],
                    'availableFuneralProducts' => $availableFuneralProducts ?? [],
                    'funeralAccount' => $funeralAccount ?? null,
                    'funeralAccountMovements' => $funeralAccountMovements ?? [],
                ]) ?>
            </div>
            <?php endif; ?>

            <?php if ($prospect['status'] === 'Cliente' && $clientAccount): ?>
            <div class="tab-pane fade" id="cuenta" role="tabpanel" aria-labelledby="cuenta-tab">
                <!-- Resumen de cuenta -->
                <div class="card mb-3">
                    <div class="card-header <?= $clientAccount['current_balance'] >= 0 ? 'bg-success text-white' : 'bg-danger text-white' ?> p-2">
                        <div class="row align-items-center">
                            <div class="col-md-6">
                                <h6 class="mb-0">
                                    <i class="fa fa-wallet me-1"></i> Estado de Cuenta
                                    <small class="d-block"><?= esc($clientAccount['property_name']) ?> (<?= esc($clientAccount['property_clave']) ?>)</small>
                                </h6>
                            </div>
                            <div class="col-md-6 text-md-end">
                                <h4 class="mb-0">
                                    Balance: $<?= number_format($clientAccount['current_balance'], 2) ?>
                                    <?php if ($clientAccount['current_balance'] < 0): ?>
                                        <small class="d-block text-warning"><i class="fa fa-exclamation-triangle"></i> En números rojos</small>
                                    <?php else: ?>
                                        <small class="d-block"><i class="fa fa-check-circle"></i> Balance positivo</small>
                                    <?php endif; ?>
                                </h4>
                            </div>
                        </div>
                    </div>
                    <div class="card-body p-2">
                        <!-- Botón para acceder al checklist de documentos -->
                        <div class="row mb-3">
                            <div class="col-12">
                                <a href="<?= base_url('admin/client-document-checklist/view/' . $clientAccount['id']) ?>" 
                                   class="btn btn-outline-primary btn-sm w-100" target="_blank">
                                    <i class="fas fa-clipboard-check me-1"></i>
                                    Ver Checklist de Documentos
                                    <i class="fas fa-external-link-alt ms-1"></i>
                                </a>
                            </div>
                        </div>
                        
                        <div class="row text-center">
                            <div class="col-4">
                                <div class="border-end">
                                    <h5 class="text-success mb-0">$<?= number_format($clientAccount['apartado_amount'], 2) ?></h5>
                                    <small class="text-muted">Apartado Recibido</small>
                                </div>
                            </div>
                            <div class="col-4">
                                <div class="border-end">
                                    <h5 class="text-danger mb-0">$<?= number_format($clientAccount['total_expenses'], 2) ?></h5>
                                    <small class="text-muted">Gastos Totales</small>
                                </div>
                            </div>
                            <div class="col-4">
                                <h5 class="<?= $clientAccount['current_balance'] >= 0 ? 'text-success' : 'text-danger' ?> mb-0">
                                    $<?= number_format($clientAccount['current_balance'], 2) ?>
                                </h5>
                                <small class="text-muted">Balance Actual</small>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Lista de movimientos -->
                <div class="card">
                    <div class="card-header bg-light p-2">
                        <h6 class="mb-0"><i class="fa fa-list me-1"></i> Movimientos de la Cuenta</h6>
                    </div>
                    <div class="card-body p-0">
                        <?php if (empty($accountMovements)): ?>
                            <div class="text-center p-4 text-muted">
                                <i class="fa fa-receipt fa-2x mb-2 d-block"></i>
                                <p>No hay movimientos registrados aún.</p>
                            </div>
                        <?php else: ?>
                            <div class="table-responsive">
                                <table class="table table-sm mb-0">
                                    <thead class="bg-light">
                                        <tr>
                                            <th>Fecha</th>
                                            <th>Concepto</th>
                                            <th>Tipo</th>
                                            <th class="text-end">Monto</th>
                                            <th>Estado</th>
                                            <th>Vence</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($accountMovements as $movement): ?>
                                            <tr>
                                                <td>
                                                    <small><?= date('d/m/Y', strtotime($movement['created_at'])) ?></small>
                                                </td>
                                                <td>
                                                    <strong><?= esc($movement['description']) ?></strong>
                                                    <?php if ($movement['document_name']): ?>
                                                        <br><small class="text-muted"><?= esc($movement['document_name']) ?></small>
                                                    <?php endif; ?>
                                                    <?php if ($movement['provider']): ?>
                                                        <br><small class="text-info">Proveedor: <?= esc($movement['provider']) ?></small>
                                                    <?php endif; ?>
                                                    <?php if ($movement['receipt_number']): ?>
                                                        <br><small class="text-success">Recibo: <?= esc($movement['receipt_number']) ?></small>
                                                    <?php endif; ?>
                                                </td>
                                                <td>
                                                    <span class="badge <?= $movement['type'] === 'Ingreso' ? 'badge-success' : 'badge-danger' ?>">
                                                        <?= $movement['type'] ?>
                                                    </span>
                                                    <?php if ($movement['document_category']): ?>
                                                        <br><small class="text-muted"><?= esc($movement['document_category']) ?></small>
                                                    <?php endif; ?>
                                                </td>
                                                <td class="text-end">
                                                    <strong class="<?= $movement['type'] === 'Ingreso' ? 'text-success' : 'text-danger' ?>">
                                                        <?= $movement['type'] === 'Ingreso' ? '+' : '-' ?>$<?= number_format($movement['amount'], 2) ?>
                                                    </strong>
                                                </td>
                                                <td>
                                                    <?php
                                                        $statusClass = 'badge-secondary';
                                                        $statusIcon = 'fa-clock';
                                                        switch ($movement['status']) {
                                                            case 'Completado':
                                                                $statusClass = 'badge-success';
                                                                $statusIcon = 'fa-check-circle';
                                                                break;
                                                            case 'En Proceso':
                                                                $statusClass = 'badge-warning';
                                                                $statusIcon = 'fa-spinner';
                                                                break;
                                                            case 'Cancelado':
                                                                $statusClass = 'badge-danger';
                                                                $statusIcon = 'fa-times-circle';
                                                                break;
                                                        }
                                                    ?>
                                                    <span class="badge <?= $statusClass ?>">
                                                        <i class="fa <?= $statusIcon ?> me-1"></i><?= $movement['status'] ?>
                                                    </span>
                                                    <?php if ($movement['completed_date']): ?>
                                                        <br><small class="text-success"><?= date('d/m/Y', strtotime($movement['completed_date'])) ?></small>
                                                    <?php endif; ?>
                                                </td>
                                                <td>
                                                    <?php if ($movement['due_date']): ?>
                                                        <?php
                                                            $dueDate = strtotime($movement['due_date']);
                                                            $today = strtotime(date('Y-m-d'));
                                                            $isOverdue = $dueDate < $today && $movement['status'] === 'Pendiente';
                                                        ?>
                                                        <small class="<?= $isOverdue ? 'text-danger fw-bold' : 'text-muted' ?>">
                                                            <?php if ($isOverdue): ?>
                                                                <i class="fa fa-exclamation-triangle me-1"></i>
                                                            <?php endif; ?>
                                                            <?= date('d/m/Y', $dueDate) ?>
                                                        </small>
                                                    <?php else: ?>
                                                        <small class="text-muted">-</small>
                                                    <?php endif; ?>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Scripts JavaScript para la interactividad -->
<script>
    // AJAX submit para guardar Minuta sin recargar
    (function() {
        const form = document.getElementById('followUpForm');
        if (!form) return;

        form.addEventListener('submit', function(e) {
            e.preventDefault();
            const submitBtn = form.querySelector('button[type="submit"]');
            if (submitBtn) submitBtn.disabled = true;

            const resultBox = document.getElementById('followUpResult');
            if (resultBox) resultBox.innerHTML = '';

            const fd = new FormData(form);
            ['activity_start_time', 'activity_end_time'].forEach(function(key) {
                const val = fd.get(key);
                if (val) {
                    fd.set(key, String(val).replace('T', ' '));
                }
            });

            fetch(form.action, {
                method: 'POST',
                body: fd,
                headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
                credentials: 'same-origin'
            }).then(function(resp) {
                // Read response as text first (server may return HTML on error). Then try to parse JSON.
                return resp.text().then(function(text) {
                    // Helper para escapar HTML en la vista de error
                    function escapeHtml(str) {
                        return String(str).replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;');
                    }

                    if (!text) return null;

                    // Si el servidor devolvió JSON content-type, intentar parsear
                    var contentType = resp.headers.get('Content-Type') || '';
                    if (contentType.indexOf('application/json') !== -1) {
                        try {
                            return JSON.parse(text);
                        } catch (err) {
                            // caímos aquí si JSON está mal formado
                            const resultBox = document.getElementById('followUpResult');
                            if (resultBox) resultBox.innerHTML = '<div class="alert alert-danger">Respuesta JSON inválida del servidor.</div>';
                            console.error('Invalid JSON response:', text);
                            throw new Error('invalid-json');
                        }
                    }

                    // Si no es JSON, probablemente es una página de error HTML (stacktrace, login, etc.). Mostrar un resumen.
                    const resultBox = document.getElementById('followUpResult');
                    if (resultBox) {
                        let snippet = text.substring(0, 2000); // limitar tamaño
                        resultBox.innerHTML = '<div class="alert alert-danger"><strong>Error de servidor:</strong> La respuesta no es JSON.<br/><pre style="white-space:pre-wrap;max-height:200px;overflow:auto;background:#f8f9fa;padding:8px;border-radius:4px;">' + escapeHtml(snippet) + '</pre></div>';
                    }
                    console.error('Non-JSON response:', text);
                    throw new Error('non-json-response');
                });
            }).then(function(json) {
                if (submitBtn) submitBtn.disabled = false;

                if (!json || !json.success) {
                    // Mostrar errores
                    let html = '<div class="alert alert-danger">';
                    if (json && json.errors) {
                        html += '<ul class="mb-0">';
                        for (const key in json.errors) {
                            if (!Object.prototype.hasOwnProperty.call(json.errors, key)) continue;
                            const val = json.errors[key];
                            // val puede ser array o string
                            if (Array.isArray(val)) {
                                val.forEach(function(v) { html += '<li>' + v + '</li>'; });
                            } else {
                                html += '<li>' + val + '</li>';
                            }
                        }
                        html += '</ul>';
                    } else if (json && json.exception) {
                        html += '<p>' + json.exception + '</p>';
                    } else {
                        html += '<p>Ocurrió un error. Intenta de nuevo.</p>';
                    }
                    html += '</div>';
                    if (resultBox) resultBox.innerHTML = html;
                    return;
                }

                // Éxito: insertar HTML en el timeline (si existe) o crear la sección
                const historialPane = document.getElementById('historial');
                let timeline = historialPane ? historialPane.querySelector('.timeline') : null;
                if (!timeline && historialPane) {
                    historialPane.innerHTML = '<div class="timeline">' + (json.followUpHtml || '') + '</div>';
                } else if (timeline) {
                    // Insertar al inicio
                    timeline.insertAdjacentHTML('afterbegin', json.followUpHtml || '');
                }

                // Actualizar contador del badge si existe
                const badge = document.getElementById('badgeMinutas');
                if (badge) {
                    const current = parseInt(badge.textContent || '0', 10);
                    badge.textContent = (isNaN(current) ? 1 : current + 1);
                }

                // Agregar nuevos intereses registrados en el mismo guardado
                if (json.newPropertyHtml && json.newPropertyHtml.length) {
                    const interestsList = document.getElementById('propertyInterestsList');
                    const noMessage = document.getElementById('noInterestsMessage');
                    if (noMessage) noMessage.remove();
                    if (interestsList) {
                        json.newPropertyHtml.forEach(function(html) {
                            interestsList.insertAdjacentHTML('beforeend', html);
                        });
                    }
                }
                if (json.newFuneralHtml && json.newFuneralHtml.length) {
                    const funeralList = document.getElementById('funeralInterestsList');
                    const noFuneral = document.getElementById('noFuneralInterestsMessage');
                    if (noFuneral) noFuneral.remove();
                    if (funeralList) {
                        json.newFuneralHtml.forEach(function(html) {
                            funeralList.insertAdjacentHTML('afterbegin', html);
                        });
                    }
                    document.getElementById('comprador-funerario-tab')?.click();
                }
                // Quitar del formulario los paquetes/inmuebles ya registrados
                form.querySelectorAll('input[name="real_estate_ids[]"]:checked, input[name="funeral_product_ids[]"]:checked').forEach(function(cb) {
                    const row = cb.closest('.col-12');
                    if (row) row.remove();
                });
                if (typeof json.totalPropertyInterests !== 'undefined') {
                    const badgeIntereses = document.getElementById('badgeIntereses');
                    if (badgeIntereses) badgeIntereses.textContent = json.totalPropertyInterests;
                }
                if (typeof json.totalFuneralInterests !== 'undefined') {
                    const badgeFunerario = document.getElementById('badgeFunerario');
                    if (badgeFunerario) badgeFunerario.textContent = json.totalFuneralInterests;
                }

                // Mostrar mensaje de éxito y limpiar formulario
                let successMsg = 'Seguimiento guardado correctamente.';
                if (json.activityScheduled) {
                    successMsg += ' Cita agendada en tu agenda.';
                }
                if (json.propertiesAdded) {
                    successMsg += ' ' + json.propertiesAdded + ' inmueble(s) registrados.';
                }
                if (json.funeralsAdded) {
                    successMsg += ' ' + json.funeralsAdded + ' paquete(s) funerario(s) registrados.';
                }
                if (resultBox) resultBox.innerHTML = '<div class="alert alert-success">' + successMsg + '</div>';

                // Desplazar al historial dentro de la misma pestaña
                const seccionHistorial = document.getElementById('seccionHistorial');
                if (seccionHistorial) {
                    seccionHistorial.scrollIntoView({ behavior: 'smooth', block: 'start' });
                }
                form.reset();
                const contactDt = document.getElementById('contact_datetime');
                if (contactDt && !contactDt.value) {
                    const now = new Date();
                    now.setMinutes(now.getMinutes() - now.getTimezoneOffset());
                    contactDt.value = now.toISOString().slice(0, 16);
                }

                const scheduleCb = document.getElementById('schedule_next_meeting');
                const nextSection = document.getElementById('nextMeetingSection');
                if (scheduleCb) scheduleCb.checked = false;
                if (nextSection) nextSection.classList.remove('show');
                ['activity_type', 'activity_title', 'activity_start_time'].forEach(function(id) {
                    const el = document.getElementById(id);
                    if (el) el.required = false;
                });

            }).catch(function(err) {
                if (submitBtn) submitBtn.disabled = false;
                const resultBox = document.getElementById('followUpResult');
                if (resultBox) resultBox.innerHTML = '<div class="alert alert-danger">Error de red: ' + (err.message || '') + '</div>';
            });
        });
    })();
</script>

<!-- Estilos CSS necesarios para el timeline -->
<style>
    /* Estilos para mejorar visibilidad de las tabs */
    .nav-tabs .nav-link {
        color: #495057 !important;
        background: transparent !important;
        border: 1px solid transparent !important;
        padding: 10px 15px !important;
        margin-right: 2px !important;
        border-radius: 8px 8px 0 0 !important;
        transition: all 0.3s ease !important;
        font-weight: 500 !important;
        font-size: 0.9rem !important;
        white-space: nowrap !important;
        text-decoration: none !important;
    }

    .nav-tabs .nav-link:hover {
        color: #007bff !important;
        background: rgba(0, 123, 255, 0.1) !important;
        border-color: rgba(0, 123, 255, 0.3) !important;
        text-decoration: none !important;
    }

    .nav-tabs .nav-link.active {
        color: #007bff !important;
        background: #fff !important;
        border-color: #dee2e6 #dee2e6 #fff !important;
        font-weight: 600 !important;
        text-decoration: none !important;
    }

    .nav-tabs .nav-link i {
        margin-right: 6px !important;
        font-size: 0.85rem !important;
    }

    .nav-tabs .badge {
        margin-left: 4px !important;
        font-size: 0.7rem !important;
    }

    /* Asegurar que el contenido de las tabs sea visible */
    .tab-content {
        background: #fff !important;
        border: 1px solid #dee2e6 !important;
        border-top: none !important;
        border-radius: 0 0 8px 8px !important;
        padding: 15px !important;
    }

    /* Estilos básicos para el timeline de minutas */
    /* Ajustes cuando la vista se inyecta dentro del offcanvas */
    .offcanvas-inner { padding: 0.5rem; }
    .offcanvas-inner .card { box-shadow: none; border: 1px solid #eee; }
    .offcanvas-inner .timeline:before { left: 12px; }
    .offcanvas-inner .timeline-item { padding-left: 48px; }
    .timeline {
        position: relative;
        padding: 20px 0;
        list-style: none;
    }
    .timeline:before {
        content: '';
        position: absolute;
        top: 0;
        bottom: 0;
        width: 4px;
        background: #dee2e6; /* Línea gris clara */
        left: 30px;
        margin-left: -2px;
        border-radius: 5px;
    }
    .timeline-item {
        position: relative;
        padding-left: 60px;
    }
    .timeline-badge {
        color: #fff;
        width: 36px;
        height: 36px;
        line-height: 36px;
        font-size: 1.2em;
        text-align: center;
        position: absolute;
        top: 0;
        left: 12px;
        border-radius: 50%;
        z-index: 100;
        /* Estilo Post-it */
        box-shadow: 0 2px 5px rgba(0,0,0,0.2);
    }
    .timeline-panel {
        background: #fffbe6; /* Tono Post-it */
        border-left: 5px solid #ffc107; /* Borde de color (Amarillo/Warning) */
        border-radius: .5rem;
    }
    .timeline-panel .card-header {
        border-bottom: 1px solid #ffc107;
        background-color: #fff8e1 !important; /* Tono más claro para el header */
    }
    .text-xs { font-size: 0.75rem; }
    .rounded-sm { border-radius: 0.25rem !important; }

    /* Estilos para botón de eliminar interés */
    .btn-remove-interest {
        color: #dc3545 !important; /* Cruz roja por defecto */
        border-color: #dc3545 !important;
        background-color: transparent !important;
    }
    
    .btn-remove-interest:hover {
        color: #fff !important; /* Cruz blanca al hover */
        background-color: #dc3545 !important; /* Fondo rojo al hover */
        border-color: #dc3545 !important;
        transform: scale(1.05); /* Pequeño efecto de crecimiento */
    }

    .btn-remove-interest:focus {
        box-shadow: 0 0 0 0.2rem rgba(220, 53, 69, 0.25);
        color: #fff !important;
        background-color: #dc3545 !important;
    }

    /* Estilos para observaciones personales */
    .personal-notes-section {
        transition: all 0.3s ease;
    }
    
    .personal-notes-section:hover {
        background-color: #e9ecef !important;
    }
    
    .personal-notes-toggle {
        transition: all 0.2s ease;
    }
    
    .personal-notes-toggle:hover {
        color: #495057 !important;
    }
    
    .personal-notes-content {
        border-left: 4px solid #ffc107 !important;
        background: linear-gradient(135deg, #fff3cd 0%, #ffeaa7 100%);
        transition: all 0.3s ease;
    }
    
    .personal-notes-content:hover {
        box-shadow: 0 2px 8px rgba(255, 193, 7, 0.3);
    }
</style>

<!-- JavaScript para gestión de inmuebles de interés -->
<script>
const prospectCancellationReasons = <?= json_encode($prospectCancellationReasons ?? [], JSON_UNESCAPED_UNICODE) ?>;
const apartadoCancellationReasons = <?= json_encode($apartadoCancellationReasons ?? [], JSON_UNESCAPED_UNICODE) ?>;

function buildReasonOptions(reasons) {
    let html = '<option value="">Selecciona un motivo...</option>';
    reasons.forEach(reason => {
        html += `<option value="${reason.id}">${reason.name}</option>`;
    });
    return html;
}

    (function() {
        // Quitar interés (delegated event)
        document.addEventListener('click', function(e) {
            if (e.target.closest('.btn-remove-interest')) {
                e.preventDefault();
                const btn = e.target.closest('.btn-remove-interest');
                const interestId = btn.dataset.interestId;
                const card = btn.closest('.interest-item');

                Swal.fire({
                    title: '¿Quitar Interés?',
                    text: 'Esta acción no se puede deshacer.',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#dc3545',
                    cancelButtonColor: '#6c757d',
                    confirmButtonText: 'Sí, quitar',
                    cancelButtonText: 'Cancelar'
                }).then((result) => {
                    if (!result.isConfirmed) return;

                    btn.disabled = true;

                fetch('<?= site_url('prospects/removePropertyInterest/' . $prospect['id']) ?>/' + interestId, {
                    method: 'POST',
                    headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
                    credentials: 'same-origin'
                }).then(resp => resp.json()).then(json => {
                    if (json.success) {
                        // Remover la card
                        if (card) card.remove();

                        // Actualizar contador del badge
                        const badge = document.getElementById('badgeIntereses');
                        if (badge) {
                            const current = parseInt(badge.textContent || '0', 10);
                            badge.textContent = Math.max(0, current - 1);
                        }

                        // Mostrar mensaje si no hay más intereses
                        const remaining = document.querySelectorAll('.interest-item').length;
                        if (remaining === 0) {
                            const interestsList = document.getElementById('propertyInterestsList');
                            if (interestsList) {
                                interestsList.innerHTML = '<div class="text-center p-4 text-muted" id="noInterestsMessage"><i class="fa fa-home fa-2x mb-2 d-block"></i><p>Aún no hay inmuebles de interés registrados.</p></div>';
                            }
                        }
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: json.errors?.error || 'Error al quitar el interés.',
                            confirmButtonColor: '#dc3545'
                        });
                        btn.disabled = false;
                    }
                }).catch(err => {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error de Conexión',
                        text: 'No se pudo conectar con el servidor. Intente nuevamente.',
                        confirmButtonColor: '#dc3545'
                    });
                    btn.disabled = false;
                });
                }); // Cerrar el .then() de SweetAlert2
            }

            // Registrar apartado
            if (e.target.closest('.btn-register-apartado')) {
                e.preventDefault();
                const btn = e.target.closest('.btn-register-apartado');
                const interestId = btn.dataset.interestId;
                const apartadoAmount = btn.dataset.apartado;
                const card = btn.closest('.interest-item');

                // Solicitar recibo con SweetAlert2
                Swal.fire({
                    title: 'Registrar Apartado',
                    html: 'Ingrese el número de recibo o referencia del apartado <span class="text-muted">(opcional)</span>:',
                    input: 'text',
                    inputPlaceholder: 'Ej: RECIBO-001',
                    showCancelButton: true,
                    confirmButtonColor: '#ffc107',
                    cancelButtonColor: '#6c757d',
                    confirmButtonText: 'Continuar',
                    cancelButtonText: 'Cancelar',
                    inputValidator: (value) => {
                        // Permitir valores vacíos o con contenido válido
                        if (value && value.length > 255) {
                            return 'El recibo no puede exceder 255 caracteres';
                        }
                    }
                }).then((receiptResult) => {
                    if (!receiptResult.isConfirmed) return;

                    const receipt = receiptResult.value || '';

                // Función para procesar el apartado
                const processApartado = (acceptedDifference = false) => {
                    btn.disabled = true;
                    btn.innerHTML = '<i class="fa fa-spinner fa-spin me-1"></i> Procesando...';

                    const fd = new FormData();
                    if (receipt) fd.append('apartado_receipt', receipt);
                    if (acceptedDifference) fd.append('accepted_difference', '1');

                    fetch('<?= site_url('prospects/registerApartado/' . $prospect['id']) ?>/' + interestId, {
                        method: 'POST',
                        body: fd,
                        headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
                        credentials: 'same-origin'
                    }).then(resp => resp.json()).then(json => {
                        // Si requiere confirmación de diferencia
                        if (json.requires_confirmation && json.difference_data) {
                            btn.disabled = false;
                            btn.innerHTML = '<i class="fa fa-hand-holding-usd me-1"></i> Apartar ($' + parseFloat(apartadoAmount).toLocaleString('es-MX', { minimumFractionDigits: 2, maximumFractionDigits: 2 }) + ')';
                            
                            const data = json.difference_data;
                            
                            Swal.fire({
                                title: '🚨 Diferencia de Crédito Detectada',
                                html: `
                                    <div class="text-left">
                                        <p><strong>Precio del inmueble:</strong> ${data.formatted_price}</p>
                                        <p><strong>Crédito autorizado:</strong> ${data.formatted_credit}</p>
                                        <p class="text-danger"><strong>DIFERENCIA:</strong> ${data.formatted_difference}</p>
                                        <hr>
                                        <p class="text-muted small">Esta diferencia se registrará como cargo adicional en el estado de cuenta del cliente.</p>
                                    </div>
                                `,
                                icon: 'warning',
                                showCancelButton: true,
                                confirmButtonColor: '#ffc107',
                                cancelButtonColor: '#6c757d',
                                confirmButtonText: 'Acepto la diferencia',
                                cancelButtonText: 'Cancelar apartado',
                                customClass: {
                                    popup: 'swal-wide'
                                }
                            }).then((confirmResult) => {
                                if (confirmResult.isConfirmed) {
                                    // Procesar con diferencia aceptada
                                    processApartado(true);
                                }
                            });
                            return;
                        }

                        if (json.success && json.html) {
                            // Reemplazar la card con la versión actualizada
                            if (card) {
                                card.outerHTML = json.html;
                            }

                            // Si se eliminaron otros intereses, quitar todas las demás cards
                            if (json.removeOthers) {
                                document.querySelectorAll('.interest-item').forEach(item => {
                                    if (item.dataset.interestId != interestId) {
                                        item.remove();
                                    }
                                });

                                // Actualizar contador del badge (solo quedará 1 - el apartado)
                                const badge = document.getElementById('badgeIntereses');
                                if (badge) {
                                    badge.textContent = '1';
                                }

                                // Ocultar checkboxes de inmuebles (ya es cliente)
                                document.querySelectorAll('#followUpForm .border.rounded.p-2.mb-2.bg-light').forEach(function(el) {
                                    if (el.querySelector('input[name="real_estate_ids[]"]')) el.style.display = 'none';
                                });
                                const seccionInmuebles = document.getElementById('seccionIntereses');
                                if (seccionInmuebles && !seccionInmuebles.querySelector('.alert-info')) {
                                    const alertDiv = document.createElement('div');
                                    alertDiv.className = 'alert alert-info mb-3';
                                    alertDiv.innerHTML = '<i class="fa fa-info-circle me-1"></i><strong>Cliente con inmueble apartado.</strong> Consulte el estado de cuenta para ver el progreso de trámites.';
                                    seccionInmuebles.insertBefore(alertDiv, seccionInmuebles.querySelector('#propertyInterestsList'));
                                }
                            }

                            // Mostrar mensaje de éxito con información de diferencia si aplica
                            let successMsg = json.message || 'Apartado registrado correctamente.';
                            let extraInfo = '';
                            if (acceptedDifference) {
                                extraInfo = 'La diferencia de crédito ha sido registrada como cargo adicional.';
                            }

                            Swal.fire({
                                icon: 'success',
                                title: '¡Apartado Registrado!',
                                text: successMsg,
                                footer: extraInfo,
                                confirmButtonColor: '#28a745',
                                timer: 3000
                            });

                        } else {
                            Swal.fire({
                                icon: 'error',
                                title: 'Error al Registrar Apartado',
                                text: json.errors?.error || 'Error al registrar el apartado.',
                                confirmButtonColor: '#dc3545'
                            });
                            btn.disabled = false;
                            btn.innerHTML = '<i class="fa fa-hand-holding-usd me-1"></i> Apartar ($' + parseFloat(apartadoAmount).toLocaleString('es-MX', { minimumFractionDigits: 2, maximumFractionDigits: 2 }) + ')';
                        }
                    }).catch(err => {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error de Conexión',
                            text: 'No se pudo conectar con el servidor. Intente nuevamente.',
                            confirmButtonColor: '#dc3545'
                        });
                        btn.disabled = false;
                        btn.innerHTML = '<i class="fa fa-hand-holding-usd me-1"></i> Apartar ($' + parseFloat(apartadoAmount).toLocaleString('es-MX', { minimumFractionDigits: 2, maximumFractionDigits: 2 }) + ')';
                    });
                };

                // Iniciar proceso
                processApartado(false);
                }); // Cerrar el .then() del prompt de recibo
            }

            if (e.target.closest('.btn-cancel-apartado')) {
                e.preventDefault();
                const btn = e.target.closest('.btn-cancel-apartado');
                const interestId = btn.dataset.interestId;
                const card = btn.closest('.interest-item');

                Swal.fire({
                    title: 'Cancelar apartado',
                    html: `
                        <div class="text-start">
                            <p class="mb-3">Selecciona el motivo de cancelación:</p>
                            <select id="cancel_apartado_reason" class="form-select mb-2">
                                ${buildReasonOptions(apartadoCancellationReasons)}
                            </select>
                            <textarea id="cancel_apartado_notes" class="form-control mt-2" rows="3" placeholder="Notas adicionales (opcional)..."></textarea>
                        </div>
                    `,
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#dc3545',
                    cancelButtonText: 'Cerrar',
                    confirmButtonText: 'Sí, cancelar apartado',
                    preConfirm: () => {
                        const reasonId = document.getElementById('cancel_apartado_reason').value;
                        const notes = document.getElementById('cancel_apartado_notes').value;
                        if (!reasonId) {
                            Swal.showValidationMessage('Debes seleccionar un motivo');
                            return false;
                        }
                        return { reason_id: parseInt(reasonId, 10), notes };
                    }
                }).then((result) => {
                    if (!result.isConfirmed) return;

                    btn.disabled = true;
                    fetch('<?= site_url('prospects/cancelApartado/' . $prospect['id']) ?>/' + interestId, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-Requested-With': 'XMLHttpRequest'
                        },
                        body: JSON.stringify(result.value)
                    }).then(resp => resp.json()).then(json => {
                        if (json.success) {
                            Swal.fire('Cancelado', json.message, 'success').then(() => window.location.reload());
                        } else {
                            Swal.fire('Error', json.message || 'No se pudo cancelar', 'error');
                            btn.disabled = false;
                        }
                    }).catch(() => {
                        Swal.fire('Error', 'Error de conexión', 'error');
                        btn.disabled = false;
                    });
                });
            }
        });
    })();

    <?php if (! empty($funerarioModuleEnabled) && ! empty($availableFuneralProducts)): ?>
    (function() {
        const productSelect = document.getElementById('funeral_product_id');
        const plansSection = document.getElementById('funeralPlansSection');
        const plansList = document.getElementById('funeralPlansList');
        const form = document.getElementById('addFuneralInterestForm');
        const prospectClientId = <?= (int) ($prospect['id'] ?? 0) ?>;

        function syncFuneralListPackages(json) {
            if (json && json.funeralPackagesHtml && typeof window.updateProspectFuneralPackagesCell === 'function') {
                window.updateProspectFuneralPackagesCell(json.clientId || prospectClientId, json.funeralPackagesHtml);
            }
        }

        function renderPlans(plans, selectedPlanIds, preferredPlanId) {
            if (!plansList) return;
            if (!plans.length) {
                plansList.innerHTML = '<p class="text-muted small mb-0">Este paquete no tiene planes de financiamiento configurados.</p>';
                if (plansSection) plansSection.style.display = 'block';
                return;
            }
            let html = '';
            plans.forEach(plan => {
                const checked = selectedPlanIds.includes(String(plan.id)) ? 'checked' : '';
                html += `<div class="form-check mb-2">
                    <input class="form-check-input funeral-plan-check" type="checkbox" name="plan_ids[]" value="${plan.id}" id="plan_${plan.id}" ${checked}>
                    <label class="form-check-label" for="plan_${plan.id}">
                        <strong>${plan.name}</strong> — Eng. $${Number(plan.enganche).toLocaleString('es-MX', {minimumFractionDigits:2})}
                        + $${Number(plan.mensualidad).toLocaleString('es-MX', {minimumFractionDigits:2})} mensual × ${plan.months} meses
                    </label>
                    <div class="form-check ms-3 mt-1">
                        <input class="form-check-input funeral-plan-prefer" type="radio" name="selected_plan_id" value="${plan.id}" id="prefer_${plan.id}" ${String(preferredPlanId) === String(plan.id) ? 'checked' : ''}>
                        <label class="form-check-label small text-muted" for="prefer_${plan.id}">Plan preferido</label>
                    </div>
                </div>`;
            });
            plansList.innerHTML = html;
            if (plansSection) plansSection.style.display = 'block';
            plansList.querySelectorAll('.funeral-plan-check').forEach(cb => {
                cb.addEventListener('change', function() {
                    const checked = Array.from(document.querySelectorAll('.funeral-plan-check:checked')).map(el => el.value);
                    if (checked.length === 0 && plansSection) plansSection.style.display = 'block';
                });
            });
        }

        function loadPlans(productId, selectedPlanIds, preferredPlanId) {
            if (!productId) return;
            fetch('<?= site_url('prospects/getFuneralProductPlans') ?>/' + productId, {
                headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
                credentials: 'same-origin'
            }).then(r => r.json()).then(json => {
                if (json.success && json.plans) {
                    renderPlans(json.plans, selectedPlanIds || [], preferredPlanId || '');
                }
            });
        }

        function resetFuneralForm() {
            if (form) form.reset();
            if (productSelect) productSelect.disabled = false;
            if (plansSection) plansSection.style.display = 'none';
        }

        if (productSelect) {
            productSelect.addEventListener('change', function() {
                loadPlans(this.value, [], '');
            });
        }

        if (form) {
            form.addEventListener('submit', function(e) {
                e.preventDefault();
                const fd = new FormData(this);

                fetch('<?= site_url('prospects/addFuneralInterest/' . $prospect['id']) ?>', {
                    method: 'POST',
                    body: fd,
                    headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
                    credentials: 'same-origin'
                }).then(r => r.json()).then(json => {
                    const resultBox = document.getElementById('addFuneralInterestResult');
                    if (!json.success) {
                        let html = '<div class="alert alert-danger alert-sm">';
                        if (json.errors) Object.values(json.errors).forEach(msg => html += '<div>' + msg + '</div>');
                        html += '</div>';
                        if (resultBox) resultBox.innerHTML = html;
                        return;
                    }
                    if (resultBox) resultBox.innerHTML = '';
                    const list = document.getElementById('funeralInterestsList');
                    const noMsg = document.getElementById('noFuneralInterestsMessage');
                    if (noMsg) noMsg.remove();
                    if (list && json.html) {
                        list.insertAdjacentHTML('afterbegin', json.html);
                        const badge = document.getElementById('badgeFunerario');
                        if (badge) {
                            badge.textContent = String(parseInt(badge.textContent || '0', 10) + 1);
                        }
                    }
                    resetFuneralForm();
                    syncFuneralListPackages(json);
                });
            });
        }

        document.addEventListener('click', function(e) {
            const removeBtn = e.target.closest('.btn-remove-funeral-interest');
            if (removeBtn) {
                e.preventDefault();
                const interestId = removeBtn.dataset.interestId;
                const card = removeBtn.closest('.funeral-interest-item');
                Swal.fire({
                    title: '¿Quitar interés funerario?',
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonText: 'Sí, quitar'
                }).then(result => {
                    if (!result.isConfirmed) return;
                    fetch('<?= site_url('prospects/removeFuneralInterest/' . $prospect['id']) ?>/' + interestId, {
                        method: 'POST',
                        headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
                        credentials: 'same-origin'
                    }).then(r => r.json()).then(json => {
                        if (json.success) {
                            if (card) card.remove();
                            const badge = document.getElementById('badgeFunerario');
                            if (badge) badge.textContent = String(Math.max(0, parseInt(badge.textContent || '0', 10) - 1));
                            if (!document.querySelector('.funeral-interest-item')) {
                                const list = document.getElementById('funeralInterestsList');
                                if (list) list.innerHTML = '<div class="text-center p-4 text-muted" id="noFuneralInterestsMessage"><i class="fa fa-monument fa-2x mb-2 d-block"></i><p>Aún no hay paquetes funerarios de interés.</p></div>';
                            }
                            syncFuneralListPackages(json);
                        } else {
                            Swal.fire('Error', json.errors?.error || 'No se pudo eliminar', 'error');
                        }
                    });
                });
            }

            const apartadoBtn = e.target.closest('.btn-register-funeral-apartado');
            if (apartadoBtn) {
                e.preventDefault();
                const interestId = apartadoBtn.dataset.interestId;
                const apartadoAmount = apartadoBtn.dataset.apartado;
                Swal.fire({
                    title: 'Registrar enganche / apartado',
                    html: 'Recibo o referencia <span class="text-muted">(opcional)</span>:',
                    input: 'text',
                    showCancelButton: true,
                    confirmButtonText: 'Registrar',
                    cancelButtonText: 'Cancelar'
                }).then(result => {
                    if (!result.isConfirmed) return;
                    const fd = new FormData();
                    if (result.value) fd.append('apartado_receipt', result.value);
                    apartadoBtn.disabled = true;
                    fetch('<?= site_url('prospects/registerFuneralApartado/' . $prospect['id']) ?>/' + interestId, {
                        method: 'POST',
                        body: fd,
                        headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
                        credentials: 'same-origin'
                    }).then(r => r.json()).then(json => {
                        if (json.success) {
                            Swal.fire('Listo', json.message, 'success').then(() => {
                                if (json.reload) window.location.reload();
                            });
                        } else {
                            Swal.fire('Error', json.errors?.error || 'No se pudo registrar', 'error');
                            apartadoBtn.disabled = false;
                        }
                    });
                });
            }

            const cancelFuneralBtn = e.target.closest('.btn-cancel-funeral-apartado');
            if (cancelFuneralBtn) {
                e.preventDefault();
                const interestId = cancelFuneralBtn.dataset.interestId;
                Swal.fire({
                    title: 'Cancelar apartado funerario',
                    html: `<div class="text-start"><select id="cancel_funeral_reason" class="form-select mb-2">${buildReasonOptions(apartadoCancellationReasons)}</select><textarea id="cancel_funeral_notes" class="form-control" rows="2" placeholder="Notas..."></textarea></div>`,
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonText: 'Sí, cancelar',
                    preConfirm: () => {
                        const reasonId = document.getElementById('cancel_funeral_reason').value;
                        if (!reasonId) { Swal.showValidationMessage('Selecciona un motivo'); return false; }
                        return { reason_id: parseInt(reasonId, 10), notes: document.getElementById('cancel_funeral_notes').value };
                    }
                }).then(result => {
                    if (!result.isConfirmed) return;
                    fetch('<?= site_url('prospects/cancelFuneralApartado/' . $prospect['id']) ?>/' + interestId, {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
                        body: JSON.stringify(result.value)
                    }).then(r => r.json()).then(json => {
                        if (json.success) {
                            Swal.fire('Cancelado', json.message, 'success').then(() => window.location.reload());
                        } else {
                            Swal.fire('Error', json.message || 'Error', 'error');
                        }
                    });
                });
            }

            const soldBtn = e.target.closest('.btn-mark-funeral-sold');
            if (soldBtn) {
                e.preventDefault();
                const interestId = soldBtn.dataset.interestId;
                Swal.fire({
                    title: '¿Marcar como vendido?',
                    text: 'Cierra la cuenta funeraria activa.',
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonText: 'Sí, vendido'
                }).then(result => {
                    if (!result.isConfirmed) return;
                    fetch('<?= site_url('prospects/markFuneralSold/' . $prospect['id']) ?>/' + interestId, {
                        method: 'POST',
                        headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
                    }).then(r => r.json()).then(json => {
                        if (json.success) {
                            const card = soldBtn.closest('.funeral-interest-item');
                            if (card && json.html) card.outerHTML = json.html;
                            Swal.fire('Vendido', json.message, 'success');
                        } else {
                            Swal.fire('Error', json.errors?.error || 'Error', 'error');
                        }
                    });
                });
            }
        });
    })();
    <?php endif; ?>
</script>

<!-- Modal para Editar Minuta -->
<div class="modal fade" id="editMinutaModal" tabindex="-1" role="dialog" aria-labelledby="editMinutaModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title" id="editMinutaModalLabel">
                    <i class="fas fa-edit me-2"></i>Editar Minuta
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <?= form_open('', ['id' => 'editMinutaForm']) ?>
            <div class="modal-body">
                <input type="hidden" id="edit_minuta_id" name="minuta_id">
                
                <div class="mb-3">
                    <label for="edit_discussion_detail" class="form-label">Detalle de la Conversación (Minuta) *</label>
                    <textarea name="discussion_detail" id="edit_discussion_detail" class="form-control" rows="3" required></textarea>
                    <small class="text-muted">Máximo 500 caracteres.</small>
                </div>

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="edit_contact_method" class="form-label">Método de Contacto *</label>
                        <select name="contact_method" id="edit_contact_method" class="form-control" required>
                            <?php foreach ($contact_methods as $method): ?>
                                <option value="<?= esc($method) ?>"><?= esc($method) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="edit_contact_datetime" class="form-label">
                            <i class="fas fa-calendar-alt me-1"></i>
                            Fecha y Hora del Contacto *
                        </label>
                        <input type="datetime-local" name="contact_datetime" id="edit_contact_datetime" class="form-control" required>
                    </div>
                </div>

                <div class="mb-3">
                    <div class="card border-0" style="background-color: #f8f9fa;">
                        <div class="card-body p-2">
                            <a data-toggle="collapse" href="#editPersonalNotesSection" role="button" aria-expanded="false" 
                               aria-controls="editPersonalNotesSection" class="text-decoration-none">
                                <small class="text-muted">
                                    <i class="fas fa-user-secret me-1"></i>
                                    <strong>Observaciones Personales</strong> 
                                    <i class="fas fa-chevron-down ms-1"></i>
                                </small>
                            </a>
                            <div class="collapse mt-2" id="editPersonalNotesSection">
                                <textarea name="personal_notes" id="edit_personal_notes" class="form-control form-control-sm" 
                                          rows="2" placeholder="Ej: Se fue de vacaciones a París, cumpleaños el 15 de marzo, le gusta el fútbol..."></textarea>
                                <small class="text-muted">
                                    Información confidencial que solo tú puedes ver. Úsala para recordar detalles personales del prospecto.
                                </small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <i class="fas fa-times me-1"></i>Cancelar
                </button>
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save me-1"></i>Actualizar Minuta
                </button>
            </div>
            <?= form_close() ?>
        </div>
    </div>
</div>

<!-- Scripts para Editar/Eliminar Minutas -->
<script>
$(document).ready(function() {
    // Editar minuta
    $(document).on('click', '.btn-edit-minuta', function() {
        const minutaId = $(this).data('id');
        const contactDatetime = $(this).data('contact-datetime');
        const contactMethod = $(this).data('contact-method');
        const discussion = $(this).data('discussion');
        const personalNotes = $(this).data('personal-notes');

        // Llenar el modal con los datos
        $('#edit_minuta_id').val(minutaId);
        $('#edit_discussion_detail').val(discussion);
        $('#edit_contact_method').val(contactMethod);
        $('#edit_contact_datetime').val(contactDatetime);
        $('#edit_personal_notes').val(personalNotes);

        // Mostrar el modal
        $('#editMinutaModal').modal('show');
    });

    // Enviar formulario de edición
    $('#editMinutaForm').on('submit', function(e) {
        e.preventDefault();
        
        const minutaId = $('#edit_minuta_id').val();
        const formData = $(this).serialize();
        
        $.ajax({
            url: '<?= site_url('prospects/updateFollowUp') ?>/' + minutaId,
            type: 'POST',
            data: formData,
            dataType: 'json',
            beforeSend: function() {
                $('#editMinutaForm button[type="submit"]').prop('disabled', true).html('<i class="fas fa-spinner fa-spin me-1"></i>Actualizando...');
            },
            success: function(response) {
                if (response.success) {
                    $('#editMinutaModal').modal('hide');
                    
                    // Recargar la página para mostrar los cambios
                    location.reload();
                    
                    Swal.fire({
                        icon: 'success',
                        title: '¡Actualizado!',
                        text: 'La minuta ha sido actualizada correctamente.',
                        timer: 2000,
                        showConfirmButton: false
                    });
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: response.message || 'Error al actualizar la minuta.'
                    });
                }
            },
            error: function() {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'Error de conexión. Intente nuevamente.'
                });
            },
            complete: function() {
                $('#editMinutaForm button[type="submit"]').prop('disabled', false).html('<i class="fas fa-save me-1"></i>Actualizar Minuta');
            }
        });
    });

    // Eliminar minuta
    $(document).on('click', '.btn-delete-minuta', function() {
        const minutaId = $(this).data('id');
        const minuteDate = $(this).data('date');
        
        Swal.fire({
            title: '¿Eliminar minuta?',
            text: `¿Estás seguro de que deseas eliminar la minuta del ${minuteDate}? Esta acción no se puede deshacer.`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#dc3545',
            cancelButtonColor: '#6c757d',
            confirmButtonText: '<i class="fas fa-trash me-1"></i>Sí, eliminar',
            cancelButtonText: '<i class="fas fa-times me-1"></i>Cancelar'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: '<?= site_url('prospects/deleteFollowUp') ?>/' + minutaId,
                    type: 'DELETE',
                    dataType: 'json',
                    success: function(response) {
                        if (response.success) {
                            // Recargar la página para mostrar los cambios
                            location.reload();
                            
                            Swal.fire({
                                icon: 'success',
                                title: '¡Eliminada!',
                                text: 'La minuta ha sido eliminada correctamente.',
                                timer: 2000,
                                showConfirmButton: false
                            });
                        } else {
                            Swal.fire({
                                icon: 'error',
                                title: 'Error',
                                text: response.message || 'Error al eliminar la minuta.'
                            });
                        }
                    },
                    error: function() {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: 'Error de conexión. Intente nuevamente.'
                        });
                    }
                });
            }
        });
    });
});

// Seguimiento opcional dentro de la minuta
$(document).ready(function() {
    const scheduleCheckbox = document.getElementById('schedule_next_meeting');
    const nextMeetingSection = document.getElementById('nextMeetingSection');
    const activityTypeSelect = document.getElementById('activity_type');
    const activityTitleInput = document.getElementById('activity_title');
    const clienteName = <?= json_encode($prospect['full_name'] ?? '') ?>;

    function setNextMeetingRequired(enabled) {
        ['activity_type', 'activity_title', 'activity_start_time'].forEach(function(id) {
            const el = document.getElementById(id);
            if (el) el.required = enabled;
        });
    }

    function actualizarTituloActividad() {
        if (!activityTypeSelect || !activityTitleInput) return;
        const tipo = activityTypeSelect.value;
        const map = {
            'Llamada': 'Llamada',
            'Reunión': 'Reunión',
            'Cita Infonavit': 'Cita Infonavit',
            'Envío Documentos': 'Envío Documentos',
            'Otro': 'Actividad'
        };
        activityTitleInput.value = (map[tipo] || tipo) + ' - ' + clienteName;
    }

    if (scheduleCheckbox && nextMeetingSection) {
        scheduleCheckbox.addEventListener('change', function() {
            if (this.checked) {
                nextMeetingSection.classList.add('show');
                setNextMeetingRequired(true);
                actualizarTituloActividad();
            } else {
                nextMeetingSection.classList.remove('show');
                setNextMeetingRequired(false);
            }
        });
    }

    if (activityTypeSelect) {
        activityTypeSelect.addEventListener('change', actualizarTituloActividad);
    }
});

// Función para mostrar modal de desactivación de prospecto
function showDeactivateProspectModal(prospectId) {
    Swal.fire({
        title: 'Cancelar prospecto',
        html: `
            <div class="text-start">
                <p class="mb-3">Selecciona el motivo de cancelación:</p>
                <select id="deactivation_reason" class="form-select mb-2">
                    ${buildReasonOptions(prospectCancellationReasons)}
                </select>
                <textarea id="deactivation_notes" class="form-control mt-2" rows="3" placeholder="Detalle adicional (opcional)..."></textarea>
            </div>
        `,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#dc3545',
        cancelButtonColor: '#6c757d',
        confirmButtonText: 'Sí, cancelar',
        cancelButtonText: 'Cerrar',
        preConfirm: () => {
            const reasonId = document.getElementById('deactivation_reason').value;
            const notes = document.getElementById('deactivation_notes').value;
            
            if (!reasonId) {
                Swal.showValidationMessage('Debes seleccionar un motivo');
                return false;
            }
            
            return { reason_id: parseInt(reasonId, 10), notes: notes };
        }
    }).then((result) => {
        if (result.isConfirmed) {
            fetch('<?= site_url('prospects/deactivate/') ?>' + prospectId, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: JSON.stringify({
                    reason_id: result.value.reason_id,
                    notes: result.value.notes
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Prospecto cancelado',
                        text: data.message,
                        confirmButtonColor: '#28a745'
                    }).then(() => {
                        window.location.reload();
                    });
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: data.message || 'No se pudo cancelar el prospecto',
                        confirmButtonColor: '#dc3545'
                    });
                }
            })
            .catch(error => {
                console.error('Error:', error);
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'Ocurrió un error al procesar la solicitud',
                    confirmButtonColor: '#dc3545'
                });
            });
        }
    });
}
</script>