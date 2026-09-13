<?= $this->extend('public_layout') ?>

<?= $this->section('content') ?>
<section class="py-5 bg-light">
    <div class="container py-5">
        <div class="row align-items-center">
            <div class="col-lg-6 mb-4">
                <h2 class="section-title">Sobre Pacific Inmobiliaria</h2>
                <p class="lead">Desde nuestra fundación, nos hemos dedicado a redefinir el mercado inmobiliario a través de la exclusividad y la transparencia.</p>
                <p>En **Pacific Inmobiliaria**, no solo vendemos propiedades; entregamos patrimonios. Nuestra paleta de colores azul y dorado refleja nuestra filosofía: la profundidad y seguridad del océano (Azul Pacífico) combinada con la excelencia y el valor de los resultados (Dorado).</p>
                <div class="row mt-4">
                    <div class="col-6">
                        <h4 class="text-pacific font-weight-bold">+10 Años</h4>
                        <small class="text-muted">De experiencia</small>
                    </div>
                    <div class="col-6">
                        <h4 class="text-pacific font-weight-bold">500+</h4>
                        <small class="text-muted">Familias felices</small>
                    </div>
                </div>
            </div>
            <div class="col-lg-6">
                <img src="https://images.unsplash.com/photo-1497366216548-37526070297c?auto=format&fit=crop&w=1000&q=80" class="img-fluid rounded shadow-lg" alt="Oficinas Pacific">
            </div>
        </div>
    </div>
</section>
<?= $this->endSection() ?>