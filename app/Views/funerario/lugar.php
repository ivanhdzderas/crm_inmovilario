<?= $this->extend('public_layout') ?>
<?= $this->section('content') ?>
<div class="property-header">
    <div class="container">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="<?= base_url('funerario') ?>">Funerario</a></li>
                <li class="breadcrumb-item active"><?= esc($lugar['name']) ?></li>
            </ol>
        </nav>
        <h1 class="display-6 text-pacific"><?= esc($lugar['name']) ?></h1>
        <p class="text-muted"><i class="fa fa-location-dot me-1"></i><?= esc($lugar['address'] ?? '') ?></p>
    </div>
</div>

<div class="container pb-5">
    <?php if (! empty($funerarias)): ?>
        <div class="mb-4">
            <h3 class="section-title">Funerarias en este lugar</h3>
            <div class="d-flex flex-wrap gap-2">
                <?php foreach ($funerarias as $f): ?>
                    <span class="badge payment-badge"><?= esc($f['name']) ?></span>
                <?php endforeach; ?>
            </div>
        </div>
    <?php endif; ?>

    <h3 class="section-title">Paquetes disponibles</h3>
    <div class="row">
        <?php foreach ($productos as $p): ?>
            <div class="col-md-6 mb-3">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body">
                        <p class="small text-muted mb-1"><?= esc($p['home_name']) ?> · <?= esc($p['type_name'] ?? '') ?></p>
                        <h5><?= esc($p['name']) ?></h5>
                        <p class="property-price"><?= formato_precio_funerario($p['base_price']) ?></p>
                        <a href="<?= url_funerario_producto($p) ?>" class="btn btn-main btn-sm rounded-pill">Ver financiamiento</a>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</div>
<?= $this->endSection() ?>
