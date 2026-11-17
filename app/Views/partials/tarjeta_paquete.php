<?php
/** @var array $p paquete funerario (location_name, home_name, type_name, plans opcional) */
$url  = url_funerario_producto($p);
$foto = ! empty($p['image_path']) ? base_url($p['image_path']) : base_url('assets/img/placeholder.jpg');
$minMensual = null;
foreach ($p['plans'] ?? [] as $plan) {
    if ($minMensual === null || (float) $plan['mensualidad'] < $minMensual) {
        $minMensual = (float) $plan['mensualidad'];
    }
}
?>
<article class="tarjeta">
    <a class="tarjeta-media" href="<?= $url ?>">
        <img src="<?= $foto ?>" alt="<?= esc($p['name']) ?>" loading="lazy">
        <?php if (! empty($p['type_name'])): ?><span class="tarjeta-etiqueta"><?= esc($p['type_name']) ?></span><?php endif; ?>
    </a>
    <div class="tarjeta-cuerpo">
        <h3><a href="<?= $url ?>"><?= esc($p['name']) ?></a></h3>
        <div class="tarjeta-meta"><i class="fa-solid fa-location-dot me-1"></i><?= esc($p['location_name']) ?> · <?= esc($p['home_name']) ?></div>
        <div class="precio"><?= precio_corto($p['base_price']) ?><small>MXN</small></div>
        <?php if ($minMensual): ?>
            <div class="chips mt-2"><span class="chip"><i class="fa-regular fa-calendar"></i> Desde <?= precio_corto($minMensual) ?> al mes</span></div>
        <?php endif; ?>
        <?php if (! empty($p['description'])): ?><p class="tarjeta-desc"><?= esc($p['description']) ?></p><?php endif; ?>
        <div class="tarjeta-pie">
            <a class="btn btn-pac" href="<?= $url ?>">Ver planes</a>
        </div>
    </div>
</article>
