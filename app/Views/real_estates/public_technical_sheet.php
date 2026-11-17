<?= $this->extend('public_layout') ?>

<?= $this->section('content') ?>
<?php
$r = $realEstate;
$pagos = array_values(array_filter($r['payment_methods_list'] ?? [], static fn ($m) => mb_strtolower((string) $m) !== 'todos'));
$apartado = in_array($r['status'] ?? '', ['Apartado', 'En apartado'], true);
$mapa = trim((string) ($r['google_maps_link'] ?? ''));
$mapaValido = $mapa !== '' && preg_match('~^https://(www\.)?(google\.[a-z.]+/maps|maps\.google\.[a-z.]+|maps\.app\.goo\.gl|goo\.gl/maps)~i', $mapa);
?>
<section class="cabecera-pagina">
    <div class="container">
        <nav class="migas" aria-label="Ruta">
            <a href="<?= base_url('/') ?>">Inicio</a><span>/</span>
            <a href="<?= base_url('inmuebles') ?>">Inmuebles</a><span>/</span>
            <?php if (! empty($r['project_name'])): ?>
                <a href="<?= url_desarrollo(['name' => $r['project_name'], 'slug' => $r['project_slug'] ?? catalogo_slug($r['project_name'])]) ?>"><?= esc($r['project_name']) ?></a><span>/</span>
            <?php endif; ?>
            <?= esc($r['name']) ?>
        </nav>
        <div class="d-flex flex-wrap justify-content-between align-items-end gap-3">
            <div>
                <h1><?= esc($r['name']) ?></h1>
                <p>
                    <?php if (! empty($r['zone_name'])): ?><i class="fa-solid fa-location-dot me-1"></i><?= esc(ucfirst($r['zone_name'])) ?> · <?php endif; ?>
                    Clave <strong><?= esc($r['clave']) ?></strong>
                    <?php if ($apartado): ?> · <span class="chip">Apartado</span><?php endif; ?>
                </p>
            </div>
            <div class="text-md-end">
                <div class="ficha-precio"><?= precio_corto($r['price']) ?> <small class="fs-6 text-secondary">MXN</small></div>
            </div>
        </div>
    </div>
</section>

<section class="seccion pt-4">
    <div class="container">
        <div class="row g-4">
            <div class="col-lg-8">
                <div class="mb-4">
                    <?= view('partials/catalogo_carousel', [
                        'photos'      => $r['photos'] ?? [],
                        'alt'         => $r['name'] ?? 'Inmueble',
                        'carousel_id' => 'fichaCarousel',
                    ]) ?>
                </div>

                <?php if (! empty($r['description_general'])): ?>
                    <div class="panel">
                        <h2>Descripción</h2>
                        <p class="mb-0"><?= nl2br(esc($r['description_general'])) ?></p>
                    </div>
                <?php endif; ?>

                <?php if (! empty($r['amenities'])): ?>
                    <div class="panel">
                        <h2>Características</h2>
                        <div class="caracteristicas">
                            <?php foreach ($r['amenities'] as $a): ?>
                                <div class="caracteristica"><small><?= esc($a['amenity_type']) ?></small><b><?= esc($a['value']) ?></b></div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php endif; ?>

                <?php if (! empty($r['address']) || $mapaValido): ?>
                    <div class="panel">
                        <h2>Ubicación</h2>
                        <?php if (! empty($r['address'])): ?><p><i class="fa-solid fa-location-dot me-2" style="color:var(--arena-osc)"></i><?= esc($r['address']) ?></p><?php endif; ?>
                        <?php if ($mapaValido): ?>
                            <a class="btn btn-linea btn-sm" href="<?= esc($mapa) ?>" target="_blank" rel="noopener"><i class="fa-solid fa-map-location-dot me-1"></i> Ver en Google Maps</a>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>
            </div>

            <div class="col-lg-4">
                <div class="ficha-lateral">
                    <div class="panel">
                        <span class="eyebrow">Precio</span>
                        <div class="ficha-precio mb-2"><?= precio_corto($r['price']) ?> <small class="fs-6 text-secondary">MXN</small></div>
                        <?php if ($pagos): ?>
                            <div class="small fw-bold text-secondary text-uppercase mb-2">Formas de pago</div>
                            <div class="chips mb-1">
                                <?php foreach ($pagos as $m): ?><span class="chip"><?= esc($m) ?></span><?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </div>

                    <?= view('partials/form_interes', [
                        'tipo'   => 'inmueble',
                        'ref'    => $r['id'],
                        'titulo' => 'Me interesa este inmueble',
                        'asunto' => 'Hola, me interesa ' . $r['name'] . ' (clave ' . $r['clave'] . ').',
                    ]) ?>

                    <a class="btn btn-linea w-100 mt-3" href="<?= url_catalogo_inmueble($r) ?>"><i class="fa-solid fa-tablet-screen-button me-1"></i> Ver en modo presentación</a>
                </div>
            </div>
        </div>
    </div>
</section>
<?= $this->endSection() ?>
