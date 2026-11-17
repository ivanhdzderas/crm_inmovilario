<?php
$nombreSitio = sitio('site_name');
ob_start();
$this->renderSection('title');
$tituloSeccion = trim(ob_get_clean());
$tituloPagina = $tituloSeccion !== '' ? $tituloSeccion . ' | ' . $nombreSitio : ($title ?? $nombreSitio);
$ruta = trim(uri_string(), '/');
$activo = static function (array $prefijos) use ($ruta): string {
    foreach ($prefijos as $p) {
        if ($p === '' ? $ruta === '' : str_starts_with($ruta, $p)) {
            return 'active';
        }
    }

    return '';
};
$waGeneral = sitio_whatsapp_url('Hola, vi su página y me gustaría recibir información.');
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= esc($tituloPagina) ?></title>
    <meta name="description" content="<?= esc($descripcion ?? ($nombreSitio . ': inmuebles en venta y planes de previsión funeraria en ' . sitio('site_tagline') . '.')) ?>">
    <meta name="theme-color" content="#16222f">
    <meta property="og:title" content="<?= esc($tituloPagina) ?>">
    <meta property="og:site_name" content="<?= esc($nombreSitio) ?>">
    <?php if (! empty($ogImagen)): ?><meta property="og:image" content="<?= esc($ogImagen) ?>"><?php endif; ?>
    <link rel="icon" type="image/png" href="<?= base_url('assets/img/logo-pacific-simbolo.png') ?>">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:wght@500;600;700&family=Manrope:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="<?= base_url('assets/css/pacific.css') ?>?v=2">
    <?= $this->renderSection('styles') ?>
</head>
<body class="sitio">
    <a class="visually-hidden-focusable" href="#contenido">Saltar al contenido</a>

    <header class="sitio-header">
        <nav class="navbar navbar-expand-lg">
            <div class="container">
                <a class="marca" href="<?= base_url('/') ?>" aria-label="<?= esc($nombreSitio) ?> — inicio">
                    <img src="<?= base_url('assets/img/logo-pacific-simbolo.png') ?>" alt="">
                    <span class="marca-texto"><b>PACIFIC</b><small>INMOBILIARIA</small></span>
                </a>
                <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#menuSitio" aria-controls="menuSitio" aria-expanded="false" aria-label="Abrir menú">
                    <i class="fa-solid fa-bars"></i>
                </button>
                <div class="collapse navbar-collapse" id="menuSitio">
                    <ul class="navbar-nav ms-auto align-items-lg-center gap-lg-1">
                        <li class="nav-item"><a class="nav-link <?= $activo(['']) ?>" href="<?= base_url('/') ?>">Inicio</a></li>
                        <li class="nav-item"><a class="nav-link <?= $activo(['inmuebles', 'desarrollo', 'ficha-tecnica']) ?>" href="<?= base_url('inmuebles') ?>">Inmuebles</a></li>
                        <li class="nav-item"><a class="nav-link <?= $activo(['funerario']) ?>" href="<?= base_url('funerario') ?>">Previsión funeraria</a></li>
                        <li class="nav-item"><a class="nav-link <?= $activo(['nosotros']) ?>" href="<?= base_url('nosotros') ?>">Nosotros</a></li>
                        <li class="nav-item ms-lg-2 mt-2 mt-lg-0"><a class="btn btn-arena btn-sm px-3" href="<?= base_url('/') ?>#contacto">Contáctanos</a></li>
                        <?php if (session()->get('isLoggedIn')): ?>
                            <li class="nav-item ms-lg-1 mt-2 mt-lg-0"><a class="nav-link" href="<?= base_url('dashboard') ?>"><i class="fa-solid fa-gauge me-1"></i> Mi panel</a></li>
                        <?php endif; ?>
                    </ul>
                </div>
            </div>
        </nav>
    </header>

    <main id="contenido">
        <?= $this->renderSection('content') ?>
    </main>

    <footer class="sitio-footer">
        <div class="container">
            <div class="row g-4">
                <div class="col-lg-4">
                    <img class="logo-pie" src="<?= base_url('assets/img/logo-pacific-claro.png') ?>" alt="<?= esc($nombreSitio) ?>">
                </div>
                <div class="col-6 col-lg-2">
                    <h3>Explora</h3>
                    <ul>
                        <li><a href="<?= base_url('inmuebles') ?>">Inmuebles</a></li>
                        <li><a href="<?= base_url('funerario') ?>">Previsión funeraria</a></li>
                        <li><a href="<?= base_url('catalogo') ?>">Catálogo para tablet</a></li>
                        <li><a href="<?= base_url('nosotros') ?>">Nosotros</a></li>
                    </ul>
                </div>
                <div class="col-6 col-lg-3">
                    <h3>Legal</h3>
                    <ul>
                        <li><a href="<?= base_url('politicas-de-privacidad') ?>">Aviso de privacidad</a></li>
                        <li><a href="<?= base_url('terminos-y-condiciones') ?>">Términos y condiciones</a></li>
                        <li><a href="<?= base_url('condiciones-de-uso') ?>">Condiciones de uso</a></li>
                        <li><a href="<?= base_url('politica-de-cookies') ?>">Política de cookies</a></li>
                    </ul>
                </div>
                <div class="col-lg-3">
                    <h3>Contacto</h3>
                    <ul>
                        <?php if ($waGeneral): ?><li><a href="<?= esc($waGeneral) ?>" target="_blank" rel="noopener"><i class="fa-brands fa-whatsapp me-2"></i>WhatsApp</a></li><?php endif; ?>
                        <?php if (sitio('site_email')): ?><li><a href="mailto:<?= esc(sitio('site_email')) ?>"><i class="fa-regular fa-envelope me-2"></i><?= esc(sitio('site_email')) ?></a></li><?php endif; ?>
                        <?php if (sitio('site_address')): ?><li><i class="fa-solid fa-location-dot me-2"></i><?= esc(sitio('site_address')) ?></li><?php endif; ?>
                        <li><a href="<?= base_url('/') ?>#contacto"><i class="fa-regular fa-paper-plane me-2"></i>Déjanos tus datos</a></li>
                    </ul>
                </div>
            </div>
            <div class="legal d-flex flex-wrap justify-content-between gap-2">
                <span>&copy; <?= date('Y') ?> <?= esc($nombreSitio) ?> · <?= esc(sitio('site_tagline')) ?></span>
                <a href="<?= base_url('login') ?>">Acceso asesores</a>
            </div>
        </div>
    </footer>

    <?php if ($waGeneral): ?>
        <a class="btn-flotante-wa" href="<?= esc($waGeneral) ?>" target="_blank" rel="noopener" aria-label="Escríbenos por WhatsApp"><i class="fa-brands fa-whatsapp"></i></a>
    <?php endif; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="<?= base_url('assets/js/catalogo.js') ?>?v=2"></script>
    <script>
        // Filtros plegables en celular
        document.querySelectorAll('[data-toggle-filtros]').forEach(function (b) {
            b.addEventListener('click', function () {
                var c = document.querySelector(b.getAttribute('data-toggle-filtros'));
                var abierto = c.classList.toggle('abierto');
                b.setAttribute('aria-expanded', abierto ? 'true' : 'false');
            });
        });
    </script>
    <?= $this->renderSection('scripts') ?>
</body>
</html>
