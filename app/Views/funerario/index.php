<?= $this->extend('public_layout') ?>
<?= $this->section('content') ?>
<section class="hero-home py-5">
    <div class="container text-center">
        <span class="hero-subtitle">PREVISIÓN Y DESCANSO</span>
        <h1 class="hero-title h2">Catálogo funerario</h1>
        <p class="lead text-white-50">Paquetes de criptas, nichos y planes con financiamiento por funeraria y lugar de descanso.</p>
    </div>
</section>

<section class="py-4 bg-white sticky-top border-bottom" style="top:70px;z-index:50;">
    <div class="container">
        <form method="get" class="row g-2 align-items-end">
            <div class="col-md-3">
                <label class="form-label small">Lugar</label>
                <select name="location_id" class="form-select form-select-sm">
                    <option value="">Todos</option>
                    <?php foreach ($locations as $loc): ?>
                        <option value="<?= $loc['id'] ?>" <?= ($filters['location_id'] ?? '') == $loc['id'] ? 'selected' : '' ?>><?= esc($loc['name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label small">Funeraria</label>
                <select name="home_id" class="form-select form-select-sm">
                    <option value="">Todas</option>
                    <?php foreach ($homes as $h): ?>
                        <option value="<?= $h['id'] ?>" <?= ($filters['home_id'] ?? '') == $h['id'] ? 'selected' : '' ?>><?= esc($h['name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label small">Tipo</label>
                <select name="type_id" class="form-select form-select-sm">
                    <option value="">Todos</option>
                    <?php foreach ($types as $t): ?>
                        <option value="<?= $t['id'] ?>" <?= ($filters['product_type_id'] ?? '') == $t['id'] ? 'selected' : '' ?>><?= esc($t['name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-3"><button class="btn btn-main w-100 btn-sm">Filtrar</button></div>
        </form>
    </div>
</section>

<section class="py-5">
    <div class="container">
        <?php if (empty($productos)): ?>
            <div class="text-center text-muted py-5">No hay paquetes disponibles con esos filtros.</div>
        <?php else: ?>
            <div class="row">
                <?php foreach ($productos as $p): ?>
                    <div class="col-md-6 col-lg-4 mb-4">
                        <div class="card h-100 shadow-sm border-0">
                            <div class="card-body">
                                <p class="small text-gold mb-1"><?= esc($p['home_name']) ?> · <?= esc($p['location_name']) ?></p>
                                <h5 class="text-pacific"><?= esc($p['name']) ?></h5>
                                <?php if (! empty($p['type_name'])): ?><span class="badge bg-secondary mb-2"><?= esc($p['type_name']) ?></span><?php endif; ?>
                                <p class="property-price mb-2"><?= formato_precio_funerario($p['base_price']) ?></p>
                                <?php if (! empty($p['plans'])): ?>
                                    <p class="small text-muted mb-2"><?= count($p['plans']) ?> plan(es) de financiamiento</p>
                                    <?php if ($p['min_mensualidad']): ?>
                                        <p class="small">Desde <strong><?= formato_precio_funerario($p['min_mensualidad']) ?></strong> / mes</p>
                                    <?php endif; ?>
                                <?php endif; ?>
                                <p class="card-text small text-truncate"><?= esc($p['description']) ?></p>
                            </div>
                            <div class="card-footer bg-white border-0">
                                <a href="<?= url_funerario_producto($p) ?>" class="btn btn-main w-100 rounded-pill">Ver planes</a>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</section>
<?= $this->endSection() ?>
