<!DOCTYPE html>
<html>
<head>
    <title>Inmuebles</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
</head>
<body>
    <div class="container mt-5">
        <h1>Nuestros Inmuebles</h1>
        <hr>
        <div class="row">
            <?php if (empty($realEstates)): ?>
                <div class="col-12">
                    <p class="text-center">Aún no hay inmuebles disponibles.</p>
                </div>
            <?php else: ?>
                <?php foreach ($realEstates as $realEstate): ?>
                    <div class="col-md-4 mb-4">
                        <div class="card">
                            <?php if (!empty($realEstate['main_photo'])): ?>
                                <img src="<?= base_url($realEstate['main_photo']) ?>" class="card-img-top" alt="<?= esc($realEstate['name']) ?>">
                            <?php else: ?>
                                <img src="https://via.placeholder.com/350x250.png?text=Imagen+No+Disponible" class="card-img-top" alt="Imagen no disponible">
                            <?php endif; ?>
                            <div class="card-body">
                                <h5 class="card-title"><?= esc($realEstate['name']) ?></h5>
                                <p class="card-text">Proyecto: **<?= esc($realEstate['project_name'] ?? 'N/A') ?>**</p>
                                <p class="card-text">Precio: **$<?= esc(number_format($realEstate['price'])) ?>**</p>
                                <a href="#" class="btn btn-primary">Ver Detalles</a>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>