<?= $this->extend('public_layout') ?>
<?= $this->section('content') ?>
<section class="cabecera-pagina">
    <div class="container">
        <nav class="migas" aria-label="Ruta">
            <a href="<?= base_url('/') ?>">Inicio</a><span>/</span>
            <a href="<?= base_url('funerario') ?>">Previsión funeraria</a><span>/</span>
            <?= esc($lugar['name']) ?>
        </nav>
        <span class="eyebrow">Lugar de descanso</span>
        <h1><?= esc($lugar['name']) ?></h1>
        <?php if (! empty($lugar['address'])): ?><p><i class="fa-solid fa-location-dot me-1"></i><?= esc($lugar['address']) ?></p><?php endif; ?>
        <?php if (! empty($lugar['description'])): ?><p class="mt-2" style="max-width: 760px;"><?= esc($lugar['description']) ?></p><?php endif; ?>
        <?php if (! empty($funerarias)): ?>
            <div class="chips mt-3">
                <?php foreach ($funerarias as $f): ?><span class="chip chip-gris"><i class="fa-solid fa-building-columns"></i><?= esc($f['name']) ?></span><?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</section>

<section class="seccion pt-4">
    <div class="container">
        <?php if (empty($productos)): ?>
            <div class="vacio">
                <i class="fa-solid fa-dove d-block"></i>
                <p class="mb-3">Por ahora no hay paquetes publicados en este lugar.</p>
                <a class="btn btn-arena" href="<?= base_url('/') ?>#contacto">Quiero información</a>
            </div>
        <?php else: ?>
            <div class="row g-4">
                <?php foreach ($productos as $p): ?>
                    <div class="col-sm-6 col-lg-4"><?= view('partials/tarjeta_paquete', ['p' => $p]) ?></div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</section>
<?= $this->endSection() ?>
