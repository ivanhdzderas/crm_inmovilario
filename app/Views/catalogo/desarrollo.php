<?= $this->extend('public_layout') ?>

<?= $this->section('content') ?>
<section class="cabecera-pagina">
    <div class="container">
        <nav class="migas" aria-label="Ruta">
            <a href="<?= base_url('/') ?>">Inicio</a><span>/</span>
            <a href="<?= base_url('inmuebles') ?>">Inmuebles</a><span>/</span>
            <?= esc($proyecto['name']) ?>
        </nav>
        <span class="eyebrow">Desarrollo</span>
        <h1><?= esc($proyecto['name']) ?></h1>
        <?php if (! empty($proyecto['location'])): ?>
            <p><i class="fa-solid fa-location-dot me-1"></i><?= esc($proyecto['location']) ?></p>
        <?php endif; ?>
        <?php if (! empty($proyecto['description'])): ?>
            <p class="mt-2" style="max-width: 760px;"><?= esc($proyecto['description']) ?></p>
        <?php endif; ?>
    </div>
</section>

<section class="seccion pt-4">
    <div class="container">
        <div class="resultado-barra mt-0">
            <span><strong class="text-dark"><?= count($propiedades) ?></strong> <?= count($propiedades) === 1 ? 'inmueble disponible' : 'inmuebles disponibles' ?> en este desarrollo</span>
        </div>
        <?php if (empty($propiedades)): ?>
            <div class="vacio">
                <i class="fa-solid fa-building d-block"></i>
                <p class="mb-3">Por ahora no hay inmuebles disponibles en este desarrollo.</p>
                <a class="btn btn-arena" href="<?= base_url('/') ?>#contacto">Quiero que me avisen</a>
            </div>
        <?php else: ?>
            <div class="row g-4">
                <?php foreach ($propiedades as $p): ?>
                    <div class="col-sm-6 col-lg-4"><?= view('partials/tarjeta_inmueble', ['p' => $p]) ?></div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</section>
<?= $this->endSection() ?>
