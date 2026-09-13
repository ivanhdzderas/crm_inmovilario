<?php
/** @var array $prospect */
/** @var array $funeralInterests */
/** @var array $availableFuneralProducts */
/** @var array|null $funeralAccount */
/** @var array $funeralAccountMovements */
?>
<div class="alert alert-secondary py-2 small mb-3">
    <i class="fa fa-info-circle me-1"></i>
    <strong>Comprador funerario:</strong> registra paquete, plan de mensualidades y enganche.
    El cobro se canaliza con la funeraria — aquí no se captura método de pago del cliente.
</div>

<?php if (! empty($funeralAccount)): ?>
<div class="card mb-3">
    <div class="card-header <?= ($funeralAccount['current_balance'] ?? 0) >= 0 ? 'bg-success text-white' : 'bg-danger text-white' ?> p-2">
        <h6 class="mb-0">
            <i class="fa fa-wallet me-1"></i> Estado de cuenta funerario
            <small class="d-block"><?= esc($funeralAccount['product_name']) ?> (<?= esc($funeralAccount['product_clave']) ?>)</small>
        </h6>
    </div>
    <div class="card-body p-2">
        <p class="mb-1"><strong>Lugar:</strong> <?= esc($funeralAccount['location_name']) ?> · <?= esc($funeralAccount['home_name']) ?></p>
        <?php if (! empty($funeralAccount['plan_name'])): ?>
            <p class="mb-1"><strong>Plan:</strong> <?= esc($funeralAccount['plan_name']) ?> (<?= (int) ($funeralAccount['plan_months'] ?? 0) ?> meses)</p>
        <?php endif; ?>
        <div class="row text-center mt-2">
            <div class="col-4">
                <h6 class="text-success mb-0">$<?= number_format($funeralAccount['apartado_amount'], 2) ?></h6>
                <small class="text-muted">Enganche</small>
            </div>
            <div class="col-4">
                <h6 class="text-danger mb-0">$<?= number_format($funeralAccount['total_expenses'], 2) ?></h6>
                <small class="text-muted">Saldo pendiente</small>
            </div>
            <div class="col-4">
                <h6 class="<?= ($funeralAccount['current_balance'] ?? 0) >= 0 ? 'text-success' : 'text-danger' ?> mb-0">$<?= number_format($funeralAccount['current_balance'], 2) ?></h6>
                <small class="text-muted">Balance</small>
            </div>
        </div>
        <?php if (! empty($funeralAccountMovements)): ?>
        <div class="table-responsive mt-3">
            <table class="table table-sm mb-0">
                <thead class="bg-light"><tr><th>Fecha</th><th>Concepto</th><th>Tipo</th><th class="text-end">Monto</th><th>Estado</th></tr></thead>
                <tbody>
                    <?php foreach ($funeralAccountMovements as $mov): ?>
                    <tr>
                        <td><small><?= date('d/m/Y', strtotime($mov['created_at'])) ?></small></td>
                        <td><small><?= esc($mov['description']) ?></small></td>
                        <td><span class="badge <?= $mov['type'] === 'Ingreso' ? 'badge-success' : 'badge-danger' ?>"><?= esc($mov['type']) ?></span></td>
                        <td class="text-end"><small><?= $mov['type'] === 'Ingreso' ? '+' : '-' ?>$<?= number_format($mov['amount'], 2) ?></small></td>
                        <td><small><?= esc($mov['status']) ?></small></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php endif; ?>
    </div>
</div>
<?php endif; ?>

<?php if (! empty($availableFuneralProducts)): ?>
<div class="card mb-3 border-secondary" id="funeralInterestFormCard">
    <div class="card-header bg-secondary text-white p-2">
        <h6 class="mb-0"><i class="fa fa-monument me-1"></i> Registrar paquete de interés</h6>
    </div>
    <div class="card-body p-2">
        <?= form_open('', ['id' => 'addFuneralInterestForm']) ?>
        <div id="addFuneralInterestResult"></div>
        <div class="row">
            <div class="col-md-7 mb-2">
                <label for="funeral_product_id" class="form-label">Paquete del catálogo *</label>
                <select name="funeral_product_id" id="funeral_product_id" class="form-control" required>
                    <option value="">— Seleccionar paquete —</option>
                    <?php foreach ($availableFuneralProducts as $fp): ?>
                        <option value="<?= (int) $fp['id'] ?>">
                            <?= esc($fp['name']) ?> (<?= esc($fp['clave']) ?>) — $<?= number_format($fp['base_price'] ?? 0, 0) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-5 mb-2">
                <label for="funeral_interest_level" class="form-label">Nivel de interés</label>
                <select name="interest_level" id="funeral_interest_level" class="form-control">
                    <option value="Medio" selected>Medio</option>
                    <option value="Alto">Alto</option>
                    <option value="Bajo">Bajo</option>
                </select>
            </div>
        </div>
        <div id="funeralPlansSection" class="mb-2" style="display:none;">
            <label class="form-label">Plan de mensualidades <small class="text-muted">(marca opciones y elige el preferido)</small></label>
            <div id="funeralPlansList" class="border rounded p-2 bg-light"></div>
        </div>
        <div class="mb-2">
            <label for="funeral_notes" class="form-label">Notas <small class="text-muted">(opcional)</small></label>
            <textarea name="notes" id="funeral_notes" class="form-control form-control-sm" rows="2" placeholder="Ej. compara plan 12 vs 18 meses"></textarea>
        </div>
        <div class="text-end">
            <button type="submit" class="btn btn-secondary btn-sm" id="btnSubmitFuneralInterest">
                <i class="fa fa-save me-1"></i> Guardar paquete
            </button>
        </div>
        <?= form_close() ?>
    </div>
</div>
<?php else: ?>
<div class="alert alert-warning small">No hay paquetes funerarios disponibles en el catálogo. Configúralos en Admin → Funerario.</div>
<?php endif; ?>

<h6 class="mb-2"><i class="fa fa-list me-1"></i> Paquetes registrados</h6>
<div id="funeralInterestsList">
    <?php if (empty($funeralInterests)): ?>
        <div class="text-center p-4 text-muted" id="noFuneralInterestsMessage">
            <i class="fa fa-monument fa-2x mb-2 d-block"></i>
            <p class="mb-0">Sin paquetes funerarios de interés.</p>
            <small>Registra el paquete con el formulario de arriba.</small>
        </div>
    <?php else: ?>
        <?php foreach ($funeralInterests as $fInterest): ?>
            <?= view('prospects/partials/funeral_interest_card', ['interest' => $fInterest]) ?>
        <?php endforeach; ?>
    <?php endif; ?>
</div>
