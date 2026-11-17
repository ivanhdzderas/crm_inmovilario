<?php
/** @var array $p inmueble enriquecido (slug, project_slug, main_photo_path, payment_methods_list) */
$foto  = base_url($p['main_photo_path'] ?? 'assets/img/placeholder.jpg');
$url   = url_inmueble($p);
$pagos = array_values(array_filter($p['payment_methods_list'] ?? [], static fn ($m) => mb_strtolower((string) $m) !== 'todos'));
$apartado = in_array($p['status'] ?? '', ['Apartado', 'En apartado'], true);
?>
<article class="tarjeta">
    <a class="tarjeta-media" href="<?= $url ?>">
        <img src="<?= $foto ?>" alt="<?= esc($p['name']) ?>" loading="lazy">
        <?php if (! empty($p['project_name'])): ?>
            <span class="tarjeta-etiqueta"><i class="fa-solid fa-building me-1"></i><?= esc($p['project_name']) ?></span>
        <?php endif; ?>
        <?php if ($apartado): ?>
            <span class="tarjeta-estado">Apartado</span>
        <?php elseif (! empty($p['is_new'])): ?>
            <span class="tarjeta-estado">Nuevo</span>
        <?php endif; ?>
    </a>
    <div class="tarjeta-cuerpo">
        <h3><a href="<?= $url ?>"><?= esc($p['name']) ?></a></h3>
        <div class="tarjeta-meta">
            <?php if (! empty($p['zone_name'])): ?><i class="fa-solid fa-location-dot me-1"></i><?= esc(ucfirst($p['zone_name'])) ?> · <?php endif; ?>
            Clave <?= esc($p['clave']) ?>
        </div>
        <div class="precio"><?= precio_corto($p['price']) ?><small>MXN</small></div>
        <?php if ($pagos): ?>
            <div class="chips mt-2" aria-label="Formas de pago">
                <?php foreach ($pagos as $m): ?><span class="chip"><?= esc($m) ?></span><?php endforeach; ?>
            </div>
        <?php endif; ?>
        <?php if (! empty($p['description_general'])): ?>
            <p class="tarjeta-desc"><?= esc(strip_tags($p['description_general'])) ?></p>
        <?php endif; ?>
        <div class="tarjeta-pie">
            <a class="btn btn-pac" href="<?= $url ?>">Ver detalles</a>
        </div>
    </div>
</article>
