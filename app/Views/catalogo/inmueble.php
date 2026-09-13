<div class="catalogo-detail-page">
    <div class="container-fluid catalogo-detail-container px-3 px-lg-4 py-3">
        <div class="catalogo-nav d-flex justify-content-between align-items-center mb-3">
            <?php if ($anterior): ?>
                <a href="<?= url_catalogo_inmueble($anterior) ?>" class="btn btn-outline-light btn-sm catalogo-nav-btn">
                    <i class="fa-solid fa-chevron-left me-1"></i> Anterior
                </a>
            <?php else: ?>
                <span class="catalogo-nav-spacer"></span>
            <?php endif; ?>
            <a href="<?= base_url('catalogo') ?>" class="btn btn-gold btn-sm rounded-pill">Ver todos</a>
            <?php if ($siguiente): ?>
                <a href="<?= url_catalogo_inmueble($siguiente) ?>" class="btn btn-outline-light btn-sm catalogo-nav-btn">
                    Siguiente <i class="fa-solid fa-chevron-right ms-1"></i>
                </a>
            <?php else: ?>
                <span class="catalogo-nav-spacer"></span>
            <?php endif; ?>
        </div>

        <div class="catalogo-detail-layout">
            <section class="catalogo-media-panel">
                <?= view('partials/catalogo_carousel', [
                    'photos'      => $realEstate['photos'] ?? [],
                    'alt'         => $realEstate['name'] ?? 'Inmueble',
                    'carousel_id' => 'catalogoInmuebleCarousel',
                ]) ?>
            </section>

            <section class="catalogo-info-panel catalogo-info-panel-tablet">
                <div class="catalogo-info-header">
                    <?php if (! empty($realEstate['project_name'])): ?>
                        <p class="catalogo-project-label mb-1">
                            <i class="fa-solid fa-building me-1"></i><?= esc($realEstate['project_name']) ?>
                        </p>
                    <?php endif; ?>
                    <h1 class="catalogo-property-title"><?= esc($realEstate['name']) ?></h1>

                    <div class="catalogo-meta-chips">
                        <?php if (! empty($realEstate['zone_name'])): ?>
                            <span class="catalogo-chip"><i class="fa-solid fa-location-dot me-1"></i><?= esc($realEstate['zone_name']) ?></span>
                        <?php endif; ?>
                        <?php if (! empty($realEstate['clave'])): ?>
                            <span class="catalogo-chip"><i class="fa-solid fa-key me-1"></i><?= esc($realEstate['clave']) ?></span>
                        <?php endif; ?>
                        <span class="catalogo-chip catalogo-chip-status"><?= esc($realEstate['status'] ?? 'Disponible') ?></span>
                    </div>
                </div>

                <div class="catalogo-price-block">
                    <span class="catalogo-price-label">Precio</span>
                    <p class="catalogo-price-value"><?= formato_precio_mxn($realEstate['price']) ?></p>
                </div>

                <?= view('partials/payment_badges', ['payment_methods_list' => $realEstate['payment_methods_list'] ?? []]) ?>

                <?php if (! empty($realEstate['description_general'])): ?>
                    <div class="catalogo-description">
                        <h3 class="catalogo-section-title">Descripción</h3>
                        <div class="catalogo-description-text"><?= nl2br(esc($realEstate['description_general'])) ?></div>
                    </div>
                <?php endif; ?>

                <?php if (! empty($realEstate['amenities'])): ?>
                    <div class="catalogo-amenities">
                        <h3 class="catalogo-section-title">Características</h3>
                        <div class="catalogo-amenities-grid">
                            <?php foreach ($realEstate['amenities'] as $a): ?>
                                <div class="catalogo-amenity-item">
                                    <span class="catalogo-amenity-label"><?= esc($a['amenity_type']) ?></span>
                                    <span class="catalogo-amenity-value"><?= esc($a['value']) ?></span>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php endif; ?>

                <?php if (! empty($realEstate['address'])): ?>
                    <div class="catalogo-extra-info">
                        <h3 class="catalogo-section-title">Ubicación</h3>
                        <p class="mb-0 text-white-50"><?= esc($realEstate['address']) ?></p>
                    </div>
                <?php endif; ?>
            </section>
        </div>
    </div>

    <?php if ($anterior || $siguiente): ?>
        <nav class="catalogo-bottom-nav d-lg-none">
            <?php if ($anterior): ?>
                <a href="<?= url_catalogo_inmueble($anterior) ?>" class="catalogo-bottom-link">
                    <i class="fa-solid fa-chevron-left"></i>
                    <span><?= esc($anterior['name']) ?></span>
                </a>
            <?php else: ?>
                <span class="catalogo-bottom-link disabled"></span>
            <?php endif; ?>
            <?php if ($siguiente): ?>
                <a href="<?= url_catalogo_inmueble($siguiente) ?>" class="catalogo-bottom-link text-end">
                    <span><?= esc($siguiente['name']) ?></span>
                    <i class="fa-solid fa-chevron-right"></i>
                </a>
            <?php else: ?>
                <span class="catalogo-bottom-link disabled"></span>
            <?php endif; ?>
        </nav>
    <?php endif; ?>
</div>
