<?php
/** @var array $interest */
helper('funerario');

$levelClass = match ($interest['interest_level']) {
    'Alto'  => 'bg-danger',
    'Medio' => 'bg-warning text-dark',
    default => 'bg-secondary',
};
$statusClass = match ($interest['status']) {
    'Vendido'     => 'bg-success',
    'Apartado'    => 'bg-warning text-dark',
    'Definido'    => 'bg-success',
    'En análisis' => 'bg-info text-white',
    'Cancelado'   => 'bg-danger',
    default       => 'bg-secondary',
};

$apartadoAmount = (float) ($interest['apartado_amount'] ?? 0);
if ($apartadoAmount <= 0 && ! empty($interest['selected_enganche'])) {
    $apartadoAmount = (float) $interest['selected_enganche'];
}
$canEdit = ! in_array($interest['status'], ['Apartado', 'Vendido', 'Cancelado'], true);
?>
<div class="card mb-2 funeral-interest-item" data-interest-id="<?= (int) $interest['id'] ?>">
    <div class="card-body p-2">
        <div class="d-flex justify-content-between align-items-start">
            <div class="flex-grow-1">
                <h6 class="mb-1"><?= esc($interest['product_name']) ?> <small class="text-muted">(<?= esc($interest['clave']) ?>)</small></h6>
                <p class="mb-1 text-sm">
                    <i class="fa fa-map-marker-alt text-primary me-1"></i>
                    <?= esc($interest['location_name']) ?> · <?= esc($interest['home_name']) ?>
                    <?php if (! empty($interest['type_name'])): ?> · <?= esc($interest['type_name']) ?><?php endif; ?>
                </p>
                <p class="mb-1">
                    <strong>Precio base:</strong> $<?= number_format($interest['base_price'] ?? 0, 2) ?>
                    | <strong>Interés:</strong> <span class="badge <?= $levelClass ?>"><?= esc($interest['interest_level']) ?></span>
                    | <span class="badge <?= $statusClass ?>"><?= esc($interest['status']) ?></span>
                </p>
                <?php if (! empty($interest['selected_plan_name'])): ?>
                    <p class="mb-1 text-sm">
                        <strong>Plan elegido:</strong> <?= esc($interest['selected_plan_name']) ?>
                        — Eng. $<?= number_format($interest['selected_enganche'] ?? 0, 2) ?>
                        + $<?= number_format((float) ($interest['selected_mensualidad'] ?? 0), 2) ?> mensual
                        × <?= (int) ($interest['selected_months'] ?? 0) ?> meses
                    </p>
                <?php endif; ?>
                <?php if (! empty($interest['notes'])): ?>
                    <p class="mb-1 text-sm text-muted"><?= esc($interest['notes']) ?></p>
                <?php endif; ?>
                <?php if (! empty($interest['considered_plans']) && ($interest['status'] ?? '') !== 'Apartado'): ?>
                    <div class="mt-2">
                        <small class="text-muted d-block mb-1"><strong>Planes en análisis:</strong></small>
                        <ul class="list-unstyled mb-0 small">
                            <?php foreach ($interest['considered_plans'] as $plan): ?>
                                <?php $isSelected = (int) ($interest['selected_financing_plan_id'] ?? 0) === (int) $plan['id']; ?>
                                <li class="mb-1">
                                    <?= esc($plan['name']) ?> —
                                    Eng. $<?= number_format($plan['enganche'], 2) ?>
                                    + $<?= number_format((float) $plan['mensualidad'], 2) ?> mensual
                                    × <?= (int) $plan['months'] ?> meses
                                    <?php if ($isSelected): ?><span class="badge bg-success">Elegido</span><?php endif; ?>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                <?php endif; ?>
                <?php if (! empty($interest['apartado_amount'])): ?>
                    <div class="mt-1">
                        <small class="text-success">
                            <i class="fa fa-check-circle"></i> Enganche registrado: $<?= number_format($interest['apartado_amount'], 2) ?>
                            <?php if (! empty($interest['apartado_date'])): ?>
                                el <?= date('d/m/Y', strtotime($interest['apartado_date'])) ?>
                            <?php endif; ?>
                            <?php if (! empty($interest['apartado_receipt'])): ?>
                                (Recibo: <?= esc($interest['apartado_receipt']) ?>)
                            <?php endif; ?>
                        </small>
                    </div>
                <?php endif; ?>
            </div>
            <div class="ms-2 text-nowrap">
                <?php if ($canEdit): ?>
                    <button type="button" class="btn btn-sm btn-outline-danger btn-remove-funeral-interest" data-interest-id="<?= (int) $interest['id'] ?>" title="Quitar paquete">
                        <i class="fa fa-times"></i>
                    </button>
                <?php endif; ?>
            </div>
        </div>

        <?php if (in_array($interest['status'], ['Definido', 'En análisis'], true) && empty($interest['apartado_amount']) && ! empty($interest['selected_financing_plan_id'])): ?>
            <div class="mt-2">
                <button type="button" class="btn btn-sm btn-warning btn-register-funeral-apartado"
                    data-interest-id="<?= (int) $interest['id'] ?>"
                    data-apartado="<?= number_format($apartadoAmount, 2, '.', '') ?>">
                    <i class="fa fa-hand-holding-usd me-1"></i> Registrar enganche ($<?= number_format($apartadoAmount, 2) ?>)
                </button>
            </div>
        <?php elseif ($interest['status'] === 'Apartado'): ?>
            <div class="mt-2 d-flex gap-1 flex-wrap">
                <button type="button" class="btn btn-sm btn-outline-danger btn-cancel-funeral-apartado" data-interest-id="<?= (int) $interest['id'] ?>">
                    <i class="fa fa-ban me-1"></i> Cancelar enganche
                </button>
                <button type="button" class="btn btn-sm btn-success btn-mark-funeral-sold" data-interest-id="<?= (int) $interest['id'] ?>">
                    <i class="fa fa-check me-1"></i> Marcar vendido
                </button>
            </div>
        <?php endif; ?>
    </div>
</div>
