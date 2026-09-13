<?= $this->extend('public_layout') ?>

<?= $this->section('content') ?>
<div class="property-header">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-md-8">
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="<?= base_url('/') ?>" class="text-decoration-none text-muted">Inicio</a></li>
                        <?php if (! empty($realEstate['project_name'])): ?>
                            <li class="breadcrumb-item">
                                <a href="<?= url_desarrollo(['name' => $realEstate['project_name'], 'slug' => $realEstate['project_slug'] ?? catalogo_slug($realEstate['project_name'])]) ?>" class="text-decoration-none text-muted">
                                    <?= esc($realEstate['project_name']) ?>
                                </a>
                            </li>
                        <?php endif; ?>
                        <li class="breadcrumb-item active"><?= esc($realEstate['name']) ?></li>
                    </ol>
                </nav>
                <h1 class="display-5 font-weight-bold text-pacific"><?= esc($realEstate['name']) ?></h1>
                <p class="text-muted">
                    <i class="fa-solid fa-location-dot me-2"></i>
                    <?= esc($realEstate['zone_name'] ?? $realEstate['address'] ?? '') ?>
                    · Clave: <strong><?= esc($realEstate['clave']) ?></strong>
                </p>
            </div>
            <div class="col-md-4 text-md-end">
                <div class="property-price mb-2"><?= formato_precio_mxn($realEstate['price']) ?></div>
                <span class="badge bg-gold p-2 px-3 rounded-pill"><?= esc($realEstate['status'] ?? 'Disponible') ?></span>
            </div>
        </div>
    </div>
</div>

<div class="container pb-5">
    <div class="row">
        <div class="col-lg-8">
            <div class="mb-4" data-gallery>
                <?php if (! empty($realEstate['photos'])): ?>
                    <div class="gallery-main position-relative mb-3">
                        <?php if (count($realEstate['photos']) > 1): ?>
                            <button type="button" class="gallery-nav gallery-nav-prev" data-gallery-prev aria-label="Anterior">‹</button>
                            <button type="button" class="gallery-nav gallery-nav-next" data-gallery-next aria-label="Siguiente">›</button>
                        <?php endif; ?>
                        <img src="<?= base_url($realEstate['photos'][0]['file_path']) ?>" class="main-property-img img-fluid" alt="Foto principal" id="mainImage" data-gallery-main>
                    </div>
                    <?php if (count($realEstate['photos']) > 1): ?>
                        <div class="row g-2">
                            <?php foreach ($realEstate['photos'] as $i => $photo): ?>
                                <div class="col-3">
                                    <button type="button" class="p-0 border-0 bg-transparent w-100 gallery-thumb<?= $i === 0 ? ' active' : '' ?>" data-gallery-src="<?= base_url($photo['file_path']) ?>">
                                        <img src="<?= base_url($photo['file_path']) ?>" class="thumb-img" alt="Miniatura">
                                    </button>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                <?php else: ?>
                    <img src="<?= base_url('assets/img/placeholder.jpg') ?>" class="main-property-img img-fluid" alt="Sin imagen">
                <?php endif; ?>
            </div>

            <div class="card card-body mb-4 border-0 shadow-sm">
                <h3 class="section-title">Descripción General</h3>
                <p class="lead text-secondary mb-0"><?= nl2br(esc($realEstate['description_general'])) ?></p>
            </div>

            <?php if (! empty($realEstate['amenities'])): ?>
            <div class="card card-body mb-4 border-0 shadow-sm">
                <h3 class="section-title">Características y Amenidades</h3>
                <div class="row">
                    <?php foreach ($realEstate['amenities'] as $amenity): ?>
                        <div class="col-md-4 mb-3">
                            <div class="d-flex align-items-center">
                                <div class="bg-light p-2 rounded me-3 text-secondary">
                                    <i class="fa-solid fa-check text-pacific"></i>
                                </div>
                                <div>
                                    <small class="text-muted d-block"><?= esc($amenity['amenity_type']) ?></small>
                                    <span class="font-weight-bold"><?= esc($amenity['value']) ?></span>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php endif; ?>
        </div>

        <div class="col-lg-4">
            <div class="card border-0 shadow-lg sticky-top" style="top: 100px;">
                <div class="card-body p-4">
                    <h4 class="font-weight-bold mb-3 text-pacific text-center">¿Te interesa esta propiedad?</h4>

                    <?= view('partials/payment_badges', ['payment_methods_list' => $realEstate['payment_methods_list'] ?? []]) ?>

                    <div class="d-grid gap-3">
                        <a href="https://wa.me/520000000000?text=<?= rawurlencode('Hola, me interesa ' . $realEstate['name'] . ' (Clave ' . $realEstate['clave'] . ')') ?>" class="btn btn-cta btn-lg w-100" target="_blank" rel="noopener">
                            <i class="fa-brands fa-whatsapp me-2"></i> Contactar por WhatsApp
                        </a>
                        <a href="<?= url_catalogo_inmueble($realEstate) ?>" class="btn btn-outline-secondary btn-lg w-100">
                            <i class="fa-solid fa-tablet-screen-button me-2"></i> Ver en modo catálogo
                        </a>
                    </div>
                    <hr class="my-4">
                    <p class="small text-muted text-center mb-0">Pacific Inmobiliaria - Exclusividad en cada detalle.</p>
                </div>
            </div>
        </div>
    </div>
</div>
<?= $this->endSection() ?>
