<?= $this->extend('public_layout') ?>

<?= $this->section('content') ?>

<section class="hero-home">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <span class="hero-subtitle">EXCLUSIVIDAD Y CONFIANZA</span>
                <h1 class="hero-title">Encuentra tu próximo destino</h1>
                <p class="lead mb-4">Explora nuestra selección de propiedades premium diseñadas para los gustos más exigentes.</p>
                <a href="#propiedades" class="btn btn-gold btn-lg px-5 rounded-pill me-2">Ver Catálogo</a>
                <a href="<?= base_url('catalogo') ?>" class="btn btn-outline-light btn-lg px-4 rounded-pill">
                    <i class="fa-solid fa-tablet-screen-button me-1"></i> Modo tablet
                </a>
            </div>
        </div>
    </div>
</section>

<!-- Sección de Filtros -->
<section class="filters-section py-4 shadow-sm bg-white sticky-top" style="top: 70px; z-index: 999;">
    <div class="container">
        <form action="<?= base_url('/') ?>" method="get" class="row g-3 align-items-end">
            <div class="col-md-3">
                <label class="form-label small fw-bold text-pacific text-uppercase">Ubicación</label>
                <select name="zone_id" class="form-select border-gold-focus">
                    <option value="">Todas las zonas</option>
                    <?php foreach ($zones as $zone): ?>
                        <?php 
                            // Manejo robusto: soporta tanto objetos/arreglos como pares llave-valor
                            $zId = is_array($zone) ? ($zone['id'] ?? '') : $zone;
                            $zName = is_array($zone) ? ($zone['name'] ?? '') : $zone;
                        ?>
                        <option value="<?= $zId ?>" <?= ($current_filters['zone_id'] == $zId) ? 'selected' : '' ?>>
                            <?= esc($zName) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-4">
                <label class="form-label small fw-bold text-pacific text-uppercase">Rango de Precio</label>
                <div class="input-group">
                    <input type="number" name="min_price" class="form-control border-gold-focus" placeholder="Min" value="<?= esc($current_filters['min_price']) ?>">
                    <span class="input-group-text bg-light">-</span>
                    <input type="number" name="max_price" class="form-control border-gold-focus" placeholder="Max" value="<?= esc($current_filters['max_price']) ?>">
                </div>
            </div>
            <div class="col-md-3">
                <label class="form-label small fw-bold text-pacific text-uppercase">Forma de Pago</label>
                <select name="payment_method" class="form-select border-gold-focus">
                    <option value="">Cualquier método</option>
                    <?php foreach ($payment_methods as $pmId => $pm): ?>
                        <?php 
                            // Si pm es un arreglo usa 'id', de lo contrario usa la llave del foreach
                            $val = is_array($pm) ? ($pm['id'] ?? $pmId) : $pmId;
                            $text = is_array($pm) ? ($pm['name'] ?? '') : $pm;
                        ?>
                        <option value="<?= $val ?>" <?= ($current_filters['payment_method'] == $val) ? 'selected' : '' ?>>
                            <?= esc($text) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-2 d-flex gap-2">
                <button type="submit" class="btn btn-main w-100 rounded-pill">
                    <i class="fa fa-search me-1"></i> Buscar
                </button>
                <?php if (array_filter($current_filters)): ?>
                    <a href="<?= base_url('/') ?>" class="btn btn-outline-secondary rounded-circle" title="Limpiar filtros">
                        <i class="fa fa-times"></i>
                    </a>
                <?php endif; ?>
            </div>
        </form>
    </div>
</section>

<section id="propiedades" class="py-5">
    <div class="container">
        <div class="text-center mb-5">
            <h2 class="section-title d-inline-block">
                <?= array_filter($current_filters) ? 'Resultados de Búsqueda' : 'Inmuebles Destacados' ?>
            </h2>
            <p class="text-muted"><?= count($realEstates) ?> propiedades encontradas</p>
        </div>
        
        <div class="row">
            <?php if (empty($realEstates)): ?>
                <div class="col-12 text-center py-5">
                    <i class="fa-solid fa-house-circle-exclamation fa-3x text-muted mb-3"></i>
                    <h4>No encontramos propiedades con esos criterios</h4>
                    <p>Intenta ajustando los filtros o navegando por el catálogo completo.</p>
                    <a href="<?= base_url('/') ?>" class="btn btn-gold rounded-pill mt-3">Ver Todo el Catálogo</a>
                </div>
            <?php endif; ?>

            <?php foreach ($realEstates as $property): ?>
                <?php if (!is_array($property)) continue; ?>
                <div class="col-md-4 mb-4">
                    <div class="card h-100 shadow-sm border-0">
                        <div class="position-relative">
                            <a href="<?= url_inmueble($property) ?>">
                                <img src="<?= base_url($property['main_photo_path'] ?? 'assets/img/placeholder.jpg') ?>" class="card-img-top" style="height: 250px; object-fit: cover;">
                            </a>
                            <?php if (! empty($property['project_name'])): ?>
                                <a href="<?= url_desarrollo(['name' => $property['project_name'], 'slug' => $property['project_slug']]) ?>" class="badge bg-gold position-absolute top-0 end-0 m-3 text-decoration-none">
                                    <?= esc($property['project_name']) ?>
                                </a>
                            <?php endif; ?>
                        </div>
                        <div class="card-body">
                            <h5 class="card-title text-pacific font-weight-bold">
                                <a href="<?= url_inmueble($property) ?>" class="text-decoration-none text-pacific"><?= esc($property['name']) ?></a>
                            </h5>
                            <p class="text-muted small mb-2"><i class="fa-solid fa-location-dot me-1"></i> <?= esc($property['zone_name'] ?? '') ?> · Clave: <?= esc($property['clave']) ?></p>
                            <div class="property-price mb-2"><?= formato_precio_mxn($property['price']) ?></div>
                            <?= view('partials/payment_badges', ['payment_methods_list' => $property['payment_methods_list'] ?? []]) ?>
                            <p class="card-text text-truncate mt-2"><?= esc($property['description_general']) ?></p>
                        </div>
                        <div class="card-footer bg-white border-0 pb-3 d-grid gap-2">
                            <a href="<?= url_inmueble($property) ?>" class="btn btn-main w-100 rounded-pill">Ver Detalles</a>
                            <a href="<?= url_catalogo_inmueble($property) ?>" class="btn btn-outline-secondary btn-sm rounded-pill">
                                <i class="fa-solid fa-tablet-screen-button me-1"></i> Modo catálogo
                            </a>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<?= $this->endSection() ?>