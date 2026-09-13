<?= $this->extend('public_layout') ?>

<?= $this->section('content') ?>
<div class="container py-5">
    <h1 class="section-title">Términos y Condiciones para Clientes</h1>
    <div class="card card-body shadow-sm border-0">
        <p class="text-muted small">Última actualización: <?= date('d/m/Y') ?></p>
        
        <h4 class="text-pacific mt-4">1. Relación con el Usuario</h4>
        <p>Al navegar por el catálogo de Pacific Inmobiliaria, usted acepta el uso del sitio para la consulta de información inmobiliaria con fines personales.</p>

        <h4 class="text-pacific mt-4">2. Veracidad del Catálogo</h4>
        <p>Los precios, amenidades y disponibilidad de los inmuebles son informativos y pueden variar sin previo aviso hasta la formalización del apartado.</p>

        <h4 class="text-pacific mt-4">3. Proceso de Interés</h4>
        <p>El registro de interés o solicitud de información no constituye una reserva de la propiedad. La exclusividad de un inmueble solo se garantiza mediante el depósito de apartado oficial.</p>

        <h4 class="text-pacific mt-4">4. Privacidad</h4>
        <p>Sus datos compartidos mediante formularios serán tratados conforme a nuestra Política de Privacidad para brindarle seguimiento personalizado.</p>
    </div>
</div>
<?= $this->endSection() ?>