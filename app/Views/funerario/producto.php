<?= $this->extend('public_layout') ?>
<?= $this->section('content') ?>
<div class="property-header">
    <div class="container">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="<?= base_url('funerario') ?>">Funerario</a></li>
                <li class="breadcrumb-item"><a href="<?= base_url('funerario/lugar/' . esc($producto['location_slug'])) ?>"><?= esc($producto['location_name']) ?></a></li>
                <li class="breadcrumb-item active"><?= esc($producto['name']) ?></li>
            </ol>
        </nav>
        <div class="row align-items-center">
            <div class="col-md-8">
                <p class="text-gold small mb-1"><?= esc($producto['home_name']) ?> · <?= esc($producto['type_name'] ?? '') ?></p>
                <h1 class="display-5 text-pacific"><?= esc($producto['name']) ?></h1>
                <p class="text-muted"><i class="fa fa-location-dot"></i> <?= esc($producto['location_name']) ?> — <?= esc($producto['location_address'] ?? '') ?></p>
            </div>
            <div class="col-md-4 text-md-end">
                <div class="property-price">Precio base: <?= formato_precio_funerario($producto['base_price']) ?></div>
            </div>
        </div>
    </div>
</div>

<div class="container pb-5">
    <?php if (! empty($producto['description'])): ?>
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-body">
                <h3 class="section-title">Descripción</h3>
                <p class="mb-0"><?= nl2br(esc($producto['description'])) ?></p>
            </div>
        </div>
    <?php endif; ?>

    <div class="card border-0 shadow-sm">
        <div class="card-header bg-pacific text-white">
            <h3 class="h5 mb-0"><i class="fa fa-calculator me-2"></i>Opciones de financiamiento</h3>
        </div>
        <div class="card-body p-0">
            <?php if (empty($planes)): ?>
                <p class="p-4 text-muted mb-0">Consulte con su asesor los planes disponibles.</p>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Plan</th>
                                <th>Enganche</th>
                                <th>Mensualidad</th>
                                <th>Meses</th>
                                <th>Total estimado</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($planes as $plan): ?>
                                <tr>
                                    <td><strong><?= esc($plan['name']) ?></strong></td>
                                    <td><?= formato_precio_funerario($plan['enganche']) ?></td>
                                    <td><?= formato_precio_funerario($plan['mensualidad']) ?></td>
                                    <td><?= (int) $plan['months'] ?></td>
                                    <td class="text-pacific fw-bold"><?= formato_precio_funerario($plan['total_estimado']) ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <p class="small text-muted p-3 mb-0">* Total estimado = enganche + (mensualidad × meses). Sujeto a condiciones del contrato.</p>
            <?php endif; ?>
        </div>
    </div>
</div>
<?= $this->endSection() ?>
