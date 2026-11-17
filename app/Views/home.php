<?= $this->extend('public_layout') ?>

<?= $this->section('content') ?>

<section class="hero">
    <div class="container">
        <div class="row align-items-center g-4">
            <div class="col-md-7 order-2 order-md-1">
                <span class="eyebrow"><?= esc(sitio('site_tagline')) ?></span>
                <h1>Tu patrimonio y tu tranquilidad, en buenas manos</h1>
                <p class="lead">Casas, departamentos y terrenos con asesoría personalizada en cada paso, y planes de previsión funeraria con financiamiento para proteger a tu familia.</p>
                <div class="d-flex flex-wrap gap-2 mt-4 justify-content-center justify-content-md-start">
                    <a class="btn btn-arena btn-lg" href="<?= base_url('inmuebles') ?>"><i class="fa-solid fa-house me-2"></i>Ver inmuebles</a>
                    <a class="btn btn-linea-claro btn-lg" href="<?= base_url('funerario') ?>">Previsión funeraria</a>
                </div>
            </div>
            <div class="col-md-5 order-1 order-md-2 text-center">
                <img class="hero-logo" src="<?= base_url('assets/img/logo-pacific-claro.png') ?>" alt="<?= esc(sitio('site_name')) ?>">
            </div>
        </div>
    </div>
</section>

<section class="lineas">
    <div class="container">
        <div class="row g-3">
            <div class="col-md-6">
                <a class="linea-card" href="<?= base_url('inmuebles') ?>">
                    <span class="icono"><i class="fa-solid fa-house-chimney"></i></span>
                    <h2>Inmuebles</h2>
                    <p>Encuentra tu casa, departamento o terreno. Aceptamos créditos Infonavit, Fovissste, bancarios y de contado.</p>
                    <span class="ir"><?= $totalInmuebles ?> <?= $totalInmuebles === 1 ? 'inmueble disponible' : 'inmuebles disponibles' ?> <i class="fa-solid fa-arrow-right ms-1"></i></span>
                </a>
            </div>
            <div class="col-md-6">
                <a class="linea-card funeral" href="<?= base_url('funerario') ?>">
                    <span class="icono"><i class="fa-solid fa-dove"></i></span>
                    <h2>Previsión funeraria</h2>
                    <p>Criptas, nichos y paquetes en los mejores lugares de descanso, con planes de pago a meses.</p>
                    <span class="ir"><?= $totalPaquetes ?> <?= $totalPaquetes === 1 ? 'paquete disponible' : 'paquetes disponibles' ?> <i class="fa-solid fa-arrow-right ms-1"></i></span>
                </a>
            </div>
        </div>
    </div>
</section>

<section class="seccion">
    <div class="container">
        <div class="seccion-titulo">
            <div>
                <span class="eyebrow">Inmuebles</span>
                <h2>Recién publicados</h2>
            </div>
            <a class="btn btn-linea" href="<?= base_url('inmuebles') ?>">Ver todos <i class="fa-solid fa-arrow-right ms-1"></i></a>
        </div>
        <?php if (empty($destacados)): ?>
            <div class="vacio">
                <i class="fa-solid fa-house-circle-exclamation d-block"></i>
                <p class="mb-0">Muy pronto publicaremos nuevos inmuebles. Déjanos tus datos y te avisamos.</p>
            </div>
        <?php else: ?>
            <div class="row g-4">
                <?php foreach ($destacados as $p): ?>
                    <div class="col-sm-6 col-lg-4"><?= view('partials/tarjeta_inmueble', ['p' => $p]) ?></div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</section>

<?php if (! empty($paquetes)): ?>
<section class="seccion seccion-blanca">
    <div class="container">
        <div class="seccion-titulo">
            <div>
                <span class="eyebrow">Previsión funeraria</span>
                <h2>Planes para tu tranquilidad</h2>
                <p>Prevenir hoy es cuidar a los tuyos mañana.</p>
            </div>
            <a class="btn btn-linea" href="<?= base_url('funerario') ?>">Ver paquetes <i class="fa-solid fa-arrow-right ms-1"></i></a>
        </div>
        <div class="row g-4">
            <?php foreach ($paquetes as $p): ?>
                <div class="col-sm-6 col-lg-4"><?= view('partials/tarjeta_paquete', ['p' => $p]) ?></div>
            <?php endforeach; ?>
        </div>
    </div>
</section>
<?php endif; ?>

<section class="seccion">
    <div class="container">
        <div class="row g-4 text-center">
            <div class="col-md-4">
                <div class="panel h-100">
                    <i class="fa-solid fa-handshake-angle fa-2x mb-3" style="color: var(--arena-osc)"></i>
                    <h3 class="h4">Asesoría personal</h3>
                    <p class="text-secondary mb-0">Un asesor te acompaña desde la primera visita hasta la firma.</p>
                </div>
            </div>
            <div class="col-md-4">
                <div class="panel h-100">
                    <i class="fa-solid fa-file-signature fa-2x mb-3" style="color: var(--arena-osc)"></i>
                    <h3 class="h4">Trámites claros</h3>
                    <p class="text-secondary mb-0">Te decimos qué documentos necesitas según tu tipo de crédito.</p>
                </div>
            </div>
            <div class="col-md-4">
                <div class="panel h-100">
                    <i class="fa-solid fa-calendar-check fa-2x mb-3" style="color: var(--arena-osc)"></i>
                    <h3 class="h4">Planes a tu medida</h3>
                    <p class="text-secondary mb-0">Opciones de contado, crédito o pagos a meses según el producto.</p>
                </div>
            </div>
        </div>
    </div>
</section>

<section class="seccion pt-0" id="contacto">
    <div class="container">
        <div class="contacto-bloque">
            <div class="row g-4 align-items-center">
                <div class="col-lg-6">
                    <span class="eyebrow">Contacto</span>
                    <h2>¿Buscas algo en particular?</h2>
                    <p>Cuéntanos qué necesitas: zona, presupuesto, tipo de crédito o el plan funerario que te interesa. Un asesor te contactará.</p>
                    <div class="contacto-punto"><i class="fa-solid fa-user-tie"></i><span>Atención personalizada por un asesor.</span></div>
                    <div class="contacto-punto"><i class="fa-solid fa-lock"></i><span>Tus datos solo se usan para atender tu solicitud.</span></div>
                    <?php if (sitio('site_address')): ?><div class="contacto-punto"><i class="fa-solid fa-location-dot"></i><span><?= esc(sitio('site_address')) ?></span></div><?php endif; ?>
                </div>
                <div class="col-lg-6">
                    <?= view('partials/form_interes', ['tipo' => 'general', 'titulo' => 'Déjanos tus datos']) ?>
                </div>
            </div>
        </div>
    </div>
</section>

<?= $this->endSection() ?>
