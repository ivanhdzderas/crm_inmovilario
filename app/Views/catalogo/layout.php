<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <title><?= esc($title ?? 'Catálogo | Pacific Inmobiliaria') ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600&family=Playfair+Display:wght@700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="<?= base_url('tony-theme.css?v=2') ?>">
</head>
<body class="catalogo-body">
    <header class="catalogo-header">
        <a href="<?= base_url('catalogo') ?>" class="catalogo-brand">
            <img src="<?= base_url('assets/img/logo-pacific-simbolo.png') ?>" alt="" style="height:30px;width:auto;margin-right:.55rem;">Pacific Inmobiliaria
        </a>
        <a href="<?= base_url('/') ?>" class="catalogo-exit">Salir</a>
    </header>
    <main class="catalogo-main">
        <?= $content ?>
    </main>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="<?= base_url('assets/js/catalogo.js?v=2') ?>"></script>
</body>
</html>
