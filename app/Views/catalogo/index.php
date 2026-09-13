<section class="catalogo-hero">
    <div class="container text-center">
        <p class="hero-subtitle mb-2">Modo presentación</p>
        <h1 class="hero-title h2">Catálogo de inmuebles</h1>
        <p class="text-white-50 mb-0">Toca un inmueble para ver el carrusel de fotos, precio, descripción y créditos aceptados.</p>
    </div>
</section>

<section class="py-4 pb-5">
    <div class="container-fluid px-3 px-lg-4">
        <?php if (empty($propiedades)): ?>
            <div class="text-center py-5 text-muted">
                <i class="fa-solid fa-house-circle-xmark fa-3x mb-3"></i>
                <p>No hay inmuebles disponibles en este momento.</p>
            </div>
        <?php else: ?>
            <div class="catalogo-grid">
                <?php foreach ($propiedades as $p): ?>
                    <a href="<?= url_catalogo_inmueble($p) ?>" class="catalogo-card">
                        <div class="catalogo-card-media">
                            <img src="<?= base_url($p['main_photo_path'] ?? 'assets/img/placeholder.jpg') ?>" alt="<?= esc($p['name']) ?>">
                            <div class="catalogo-card-price"><?= formato_precio_mxn($p['price']) ?></div>
                        </div>
                        <div class="catalogo-card-body">
                            <?php if (! empty($p['project_name'])): ?>
                                <p class="catalogo-card-project"><?= esc($p['project_name']) ?></p>
                            <?php endif; ?>
                            <h2><?= esc($p['name']) ?></h2>
                            <p class="catalogo-card-meta">
                                <?php if (! empty($p['zone_name'])): ?>
                                    <i class="fa-solid fa-location-dot me-1"></i><?= esc($p['zone_name']) ?>
                                <?php endif; ?>
                                <?php if (! empty($p['clave'])): ?>
                                    · Clave: <?= esc($p['clave']) ?>
                                <?php endif; ?>
                            </p>
                            <?php if (! empty($p['description_general'])): ?>
                                <p class="catalogo-card-desc"><?= esc(character_limiter(strip_tags($p['description_general']), 140)) ?></p>
                            <?php endif; ?>
                            <?php if (! empty($p['payment_methods_list'])): ?>
                                <div class="catalogo-card-payments">
                                    <?php foreach ($p['payment_methods_list'] as $m): ?>
                                        <span class="badge payment-badge-sm"><?= esc($m) ?></span>
                                    <?php endforeach; ?>
                                </div>
                            <?php endif; ?>
                            <span class="catalogo-card-cta">Ver ficha completa <i class="fa-solid fa-arrow-right ms-1"></i></span>
                        </div>
                    </a>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</section>
