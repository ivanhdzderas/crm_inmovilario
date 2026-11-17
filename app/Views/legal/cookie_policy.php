<?= $this->extend('public_layout') ?>

<?= $this->section('title') ?>Política de cookies<?= $this->endSection() ?>

<?= $this->section('content') ?>
<section class="cabecera-pagina">
    <div class="container">
        <nav class="migas" aria-label="Ruta"><a href="<?= base_url('/') ?>">Inicio</a><span>/</span>Política de cookies</nav>
        <h1>Política de cookies</h1>
        <p>Última actualización: <?= date('d/m/Y') ?></p>
    </div>
</section>

<section class="seccion pt-4">
    <div class="container">
        <div class="texto-legal">
            <h2>¿Qué son las cookies?</h2>
            <p>Son pequeños archivos que el sitio guarda en tu navegador para que funcione correctamente.</p>

            <h2>Cookies que usamos</h2>
            <ul>
                <li><strong>Sesión (<code>ci_session</code>):</strong> mantiene tu sesión si eres asesor y recuerda mensajes entre páginas. Se borra al cerrar sesión o tras un tiempo de inactividad.</li>
                <li><strong>Seguridad (<code>csrf_cookie_name</code>):</strong> protege los formularios contra envíos falsificados.</li>
            </ul>
            <p>No usamos cookies de publicidad ni de rastreo de terceros. Algunos recursos (fuentes tipográficas e íconos) se cargan desde servicios externos que pueden registrar datos técnicos de la visita, como la dirección IP.</p>

            <h2>¿Cómo desactivarlas?</h2>
            <p class="mb-0">Puedes borrarlas o bloquearlas desde la configuración de tu navegador. Si bloqueas las cookies de sesión y seguridad, los formularios del sitio podrían no funcionar.</p>
        </div>
    </div>
</section>
<?= $this->endSection() ?>
