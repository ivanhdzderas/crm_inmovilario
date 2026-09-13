<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $title ?? 'Pacific Inmobiliaria' ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600&family=Playfair+Display:wght@700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- El CSS debe estar en public/ para ser accesible -->
    <link rel="stylesheet" href="<?= base_url('tony-theme.css?v=' . time()) ?>">
</head>
<body>

    <nav class="navbar navbar-expand-lg navbar-pacific sticky-top">
        <div class="container">
            <a class="navbar-brand" href="<?= base_url('/') ?>">
                <i class="fa-solid fa-anchor me-2"></i>PACIFIC <span style="font-weight:300">INMOBILIARIA</span>
            </a>
            <button class="navbar-toggler text-white" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <i class="fa-solid fa-bars"></i>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item"><a class="nav-link" href="<?= base_url('/') ?>">Inicio</a></li>
                    <li class="nav-item"><a class="nav-link" href="<?= base_url('catalogo') ?>"><i class="fa-solid fa-tablet-screen-button me-1"></i> Catálogo</a></li>
                    <li class="nav-item"><a class="nav-link" href="<?= base_url('nosotros') ?>">Sobre Nosotros</a></li>
                    <?php if (session()->get('isLoggedIn')): ?>
                        <li class="nav-item"><a class="nav-link" href="<?= base_url('dashboard') ?>"><i class="fa-solid fa-gauge me-1"></i> Mi Panel</a></li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </nav>

    <main>
        <?= $this->renderSection('content') ?>
    </main>

    <footer class="footer-pacific">
        <div class="container">
            <div class="row">
                <div class="col-md-4 mb-4">
                    <h5>PACIFIC INMOBILIARIA</h5>
                    <p>Expertos en encontrar el espacio que define tu estilo de vida. Lujo, exclusividad y confianza en cada operación.</p>
                </div>
                <div class="col-md-4 mb-4 text-md-center">
                    <h5>ENLACES LEGALES</h5>
                    <ul class="list-unstyled">
                        <li><a href="<?= base_url('politicas-de-privacidad') ?>" class="footer-link">Privacidad</a></li>
                        <li><a href="<?= base_url('terminos-y-condiciones') ?>" class="footer-link">Términos y Condiciones</a></li>
                        <li><a href="<?= base_url('condiciones-de-uso') ?>" class="footer-link">Uso del Sitio</a></li>
                    </ul>
                </div>
                <div class="col-md-4 mb-4 text-md-end">
                    <h5>CONTACTO</h5>
                    <p><i class="fa-solid fa-location-dot me-2"></i> Zona Dorada, Pacific City</p>
                    <p><i class="fa-solid fa-phone me-2"></i> +52 (000) 000-0000</p>
                </div>
            </div>
            <hr class="bg-secondary">
            <div class="text-center mt-4">
                <small>&copy; <?= date('Y') ?> Pacific Inmobiliaria. Todos los derechos reservados.</small>
            </div>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="<?= base_url('assets/js/catalogo.js') ?>"></script>
</body>
</html>