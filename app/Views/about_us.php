<?= $this->extend('public_layout') ?>

<?= $this->section('content') ?>
<section class="cabecera-pagina">
    <div class="container">
        <nav class="migas" aria-label="Ruta"><a href="<?= base_url('/') ?>">Inicio</a><span>/</span>Nosotros</nav>
        <span class="eyebrow"><?= esc(sitio('site_tagline')) ?></span>
        <h1>Sobre <?= esc(sitio('site_name')) ?></h1>
    </div>
</section>

<section class="seccion pt-4">
    <div class="container">
        <div class="row g-4 align-items-center">
            <div class="col-lg-7">
                <div class="panel">
                    <h2>Quiénes somos</h2>
                    <p>En <?= esc(sitio('site_name')) ?> te ayudamos a encontrar el inmueble adecuado para ti y tu familia, y a proteger su futuro con planes de previsión funeraria.</p>
                    <p>Trabajamos con asesores que te acompañan en todo el proceso: te mostramos opciones según tu presupuesto y tipo de crédito, te explicamos los documentos que necesitas y damos seguimiento hasta la entrega.</p>
                    <h2 class="mt-4">Qué ofrecemos</h2>
                    <ul class="mb-0">
                        <li>Casas, departamentos y terrenos en venta, con créditos Infonavit, Fovissste, bancarios o de contado.</li>
                        <li>Criptas, nichos y paquetes funerarios con planes de pago a meses.</li>
                        <li>Asesoría personalizada y seguimiento de cada trámite.</li>
                    </ul>
                </div>
            </div>
            <div class="col-lg-5 text-center">
                <img src="<?= base_url('assets/img/logo-pacific.png') ?>" alt="<?= esc(sitio('site_name')) ?>" style="max-width: 320px; width: 100%;">
            </div>
        </div>
        <div class="text-center mt-4">
            <a class="btn btn-arena btn-lg" href="<?= base_url('/') ?>#contacto">Habla con un asesor</a>
        </div>
    </div>
</section>
<?= $this->endSection() ?>
