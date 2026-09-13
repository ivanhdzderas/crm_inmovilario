<?= $this->extend('public_layout') ?>

<?= $this->section('content') ?>

<section class="property-header">
    <div class="container">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-2">
                <li class="breadcrumb-item"><a href="<?= base_url('/') ?>">Inicio</a></li>
                <li class="breadcrumb-item"><a href="<?= url_desarrollo($proyecto) ?>"><?= esc($proyecto['name']) ?></a></li>
                <li class="breadcrumb-item active">Propiedades</li>
            </ol>
        </nav>
        <h1 class="display-6 font-weight-bold text-pacific"><?= esc($proyecto['name']) ?></h1>
        <?php if (! empty($proyecto['location'])): ?>
            <p class="text-muted"><i class="fa-solid fa-location-dot me-2"></i><?= esc($proyecto['location']) ?></p>
        <?php endif; ?>
        <?php if (! empty($proyecto['description'])): ?>
            <p class="lead text-secondary"><?= esc($proyecto['description']) ?></p>
        <?php endif; ?>
    </div>
</section>

<section class="pb-5">
    <div class="container">
        <?php if (empty($propiedades)): ?>
            <p class="text-muted">No hay inmuebles disponibles en este desarrollo.</p>
        <?php else: ?>
            <div class="row">
                <?php foreach ($propiedades as $p): ?>
                    <div class="col-md-4 mb-4">
                        <div class="card h-100 shadow-sm border-0">
                            <a href="<?= url_inmueble($p) ?>">
                                <img src="<?= base_url($p['main_photo_path'] ?? 'assets/img/placeholder.jpg') ?>" class="card-img-top" style="height:220px;object-fit:cover" alt="">
                            </a>
                            <div class="card-body">
                                <h5 class="text-pacific"><?= esc($p['name']) ?></h5>
                                <p class="property-price h5"><?= formato_precio_mxn($p['price']) ?></p>
                                <?= view('partials/payment_badges', ['payment_methods_list' => $p['payment_methods_list'] ?? []]) ?>
                                <a href="<?= url_inmueble($p) ?>" class="btn btn-main w-100 rounded-pill mt-2">Ver ficha</a>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</section>

<?= $this->endSection() ?>
