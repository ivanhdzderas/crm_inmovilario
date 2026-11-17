<?= $this->extend('public_layout') ?>
<?= $this->section('content') ?>
<?php $p = $producto; ?>
<section class="cabecera-pagina">
    <div class="container">
        <nav class="migas" aria-label="Ruta">
            <a href="<?= base_url('/') ?>">Inicio</a><span>/</span>
            <a href="<?= base_url('funerario') ?>">Previsión funeraria</a><span>/</span>
            <a href="<?= base_url('funerario/lugar/' . rawurlencode($p['location_slug'])) ?>"><?= esc($p['location_name']) ?></a><span>/</span>
            <?= esc($p['name']) ?>
        </nav>
        <div class="d-flex flex-wrap justify-content-between align-items-end gap-3">
            <div>
                <span class="eyebrow"><?= esc($p['type_name'] ?: 'Paquete funerario') ?></span>
                <h1><?= esc($p['name']) ?></h1>
                <p><i class="fa-solid fa-location-dot me-1"></i><?= esc($p['location_name']) ?><?= ! empty($p['location_address']) ? ' — ' . esc($p['location_address']) : '' ?> · <?= esc($p['home_name']) ?></p>
            </div>
            <div class="text-md-end">
                <span class="eyebrow">Precio de contado</span>
                <div class="ficha-precio"><?= precio_corto($p['base_price']) ?> <small class="fs-6 text-secondary">MXN</small></div>
            </div>
        </div>
    </div>
</section>

<section class="seccion pt-4">
    <div class="container">
        <div class="row g-4">
            <div class="col-lg-8">
                <?php if (! empty($p['image_path'])): ?>
                    <div class="catalogo-carousel-wrap mb-4">
                        <img src="<?= base_url($p['image_path']) ?>" alt="<?= esc($p['name']) ?>" style="width:100%; aspect-ratio: 16/10; object-fit: cover; display:block;">
                    </div>
                <?php endif; ?>

                <?php if (! empty($p['description'])): ?>
                    <div class="panel">
                        <h2>Descripción</h2>
                        <p class="mb-0"><?= nl2br(esc($p['description'])) ?></p>
                    </div>
                <?php endif; ?>

                <div class="panel">
                    <h2>Planes de financiamiento</h2>
                    <?php if (empty($planes)): ?>
                        <p class="text-secondary mb-0">Pregunta a tu asesor por las opciones de pago disponibles para este paquete.</p>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table tabla-planes mb-2">
                                <thead><tr><th>Plan</th><th>Enganche</th><th>Mensualidad</th><th>Meses</th><th>Total</th></tr></thead>
                                <tbody>
                                    <?php foreach ($planes as $plan): ?>
                                        <tr>
                                            <td><strong><?= esc($plan['name']) ?></strong></td>
                                            <td><?= precio_corto($plan['enganche']) ?></td>
                                            <td><strong><?= precio_corto($plan['mensualidad']) ?></strong></td>
                                            <td><?= (int) $plan['months'] ?></td>
                                            <td><?= precio_corto($plan['total_estimado']) ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                        <p class="small text-secondary mb-0">Total estimado = enganche + mensualidades. Sujeto a las condiciones del contrato.</p>
                    <?php endif; ?>
                </div>

                <?php if (! empty($p['home_description']) || ! empty($p['location_description'])): ?>
                    <div class="panel">
                        <h2>Sobre el lugar</h2>
                        <?php if (! empty($p['location_description'])): ?><p><?= esc($p['location_description']) ?></p><?php endif; ?>
                        <?php if (! empty($p['home_description'])): ?><p class="mb-0"><strong><?= esc($p['home_name']) ?>:</strong> <?= esc($p['home_description']) ?></p><?php endif; ?>
                    </div>
                <?php endif; ?>
            </div>

            <div class="col-lg-4">
                <div class="ficha-lateral">
                    <?= view('partials/form_interes', [
                        'tipo'   => 'funerario',
                        'ref'    => $p['id'],
                        'titulo' => 'Quiero información de este plan',
                        'asunto' => 'Hola, me interesa el paquete ' . $p['name'] . ' en ' . $p['location_name'] . '.',
                    ]) ?>
                </div>
            </div>
        </div>
    </div>
</section>
<?= $this->endSection() ?>
