<?php
$photos   = $photos ?? [];
$alt      = $alt ?? 'Foto del inmueble';
$carouselId = $carousel_id ?? 'catalogoCarousel';
$placeholder = base_url('assets/img/placeholder.jpg');

if (empty($photos)) {
    $photos = [['file_path' => 'assets/img/placeholder.jpg']];
}

$total = count($photos);
?>

<div class="catalogo-carousel-wrap">
    <div id="<?= esc($carouselId) ?>" class="carousel slide catalogo-carousel" data-bs-ride="false" data-bs-touch="true" data-bs-interval="false">
        <?php if ($total > 1): ?>
            <div class="carousel-indicators catalogo-carousel-indicators">
                <?php foreach ($photos as $i => $photo): ?>
                    <button type="button"
                            data-bs-target="#<?= esc($carouselId) ?>"
                            data-bs-slide-to="<?= $i ?>"
                            class="<?= $i === 0 ? 'active' : '' ?>"
                            aria-label="Foto <?= $i + 1 ?>"></button>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <div class="carousel-inner">
            <?php foreach ($photos as $i => $photo): ?>
                <?php $src = base_url($photo['file_path'] ?? $placeholder); ?>
                <div class="carousel-item<?= $i === 0 ? ' active' : '' ?>">
                    <button type="button" class="catalogo-carousel-zoom-btn" data-lightbox-index="<?= $i ?>" aria-label="Ampliar foto <?= $i + 1 ?>">
                        <img src="<?= $src ?>" class="catalogo-carousel-img" alt="<?= esc($alt) ?> — foto <?= $i + 1 ?>" draggable="false">
                        <span class="catalogo-expand-hint"><i class="fa-solid fa-up-right-and-down-left-from-center me-1"></i> Toca para ampliar</span>
                    </button>
                </div>
            <?php endforeach; ?>
        </div>

        <?php if ($total > 1): ?>
            <button class="carousel-control-prev catalogo-carousel-control" type="button" data-bs-target="#<?= esc($carouselId) ?>" data-bs-slide="prev">
                <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                <span class="visually-hidden">Anterior</span>
            </button>
            <button class="carousel-control-next catalogo-carousel-control" type="button" data-bs-target="#<?= esc($carouselId) ?>" data-bs-slide="next">
                <span class="carousel-control-next-icon" aria-hidden="true"></span>
                <span class="visually-hidden">Siguiente</span>
            </button>
            <span class="catalogo-photo-counter" data-carousel-counter>1 / <?= $total ?></span>
        <?php endif; ?>
    </div>

    <?php if ($total > 1): ?>
        <div class="catalogo-thumb-strip" data-carousel-thumbs="#<?= esc($carouselId) ?>">
            <?php foreach ($photos as $i => $photo): ?>
                <button type="button"
                        class="catalogo-thumb-btn<?= $i === 0 ? ' active' : '' ?>"
                        data-bs-target="#<?= esc($carouselId) ?>"
                        data-bs-slide-to="<?= $i ?>"
                        aria-label="Ver foto <?= $i + 1 ?>">
                    <img src="<?= base_url($photo['file_path']) ?>" alt="">
                </button>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>
