<?= $this->extend('template') ?>

<?= $this->section('title') ?>Opciones para <?= esc($cliente['full_name']) ?><?= $this->endSection() ?>

<?= $this->section('content') ?>
<style>
    .mt-card { background: #fff; border: 1px solid #e6e9ef; border-radius: 12px; overflow: hidden; height: 100%; display: flex; flex-direction: column; }
    .mt-card img { width: 100%; aspect-ratio: 4/3; object-fit: cover; background: #eef1f4; }
    .mt-card .cuerpo { padding: 14px 16px; flex: 1; display: flex; flex-direction: column; }
    .mt-pts { font-weight: 700; border-radius: 999px; padding: 3px 10px; font-size: .85rem; }
    .mt-pts.alto { background: #dcfce7; color: #166534; }
    .mt-pts.medio { background: #fef3c7; color: #92400e; }
    .mt-pts.bajo { background: #f1f5f9; color: #475569; }
    .mt-razon { font-size: .85rem; margin-bottom: 3px; }
    .mt-razon i { width: 16px; }
</style>
<?php
$tel = preg_replace('/\D/', '', (string) $cliente['phone']);
if (strlen($tel) === 10) { $tel = '52' . $tel; }
$primerNombre = mb_convert_case(mb_strtolower(explode(' ', trim($cliente['full_name']))[0] ?? ''), MB_CASE_TITLE);
?>
<div class="container-fluid">
    <div class="d-flex flex-wrap justify-content-between align-items-center pt-3 pb-2 mb-3 border-bottom">
        <div>
            <a href="<?= site_url('matching') ?>" class="small"><i class="fas fa-arrow-left"></i> Todos los clientes</a>
            <h1 class="h3 mb-1 mt-1">Opciones para <?= esc($cliente['full_name']) ?></h1>
            <p class="text-muted mb-0">
                Busca: <?= (float) $cliente['credit_amount'] > 0 ? '<b>' . precio_corto($cliente['credit_amount']) . '</b>' : '<span class="text-danger">sin presupuesto</span>' ?>
                · <?= $cliente['zone_name'] ? esc(ucfirst($cliente['zone_name'])) : '<span class="text-danger">sin zona</span>' ?>
                · <?= $formaPago ? esc($formaPago) : '<span class="text-danger">sin forma de pago</span>' ?>
                <?= $cliente['asesor_name'] ? ' · Asesor: ' . esc($cliente['asesor_name']) : '' ?>
            </p>
        </div>
        <div class="mt-2">
            <a class="btn btn-outline-secondary" href="<?= site_url('reportes/prospecto/' . $cliente['id']) ?>"><i class="fas fa-file-alt"></i> Reporte de seguimiento</a>
            <?php if ($todos): ?>
                <a class="btn btn-outline-primary" href="<?= site_url('matching/cliente/' . $cliente['id']) ?>">Solo recomendados</a>
            <?php else: ?>
                <a class="btn btn-outline-primary" href="<?= site_url('matching/cliente/' . $cliente['id']) ?>?todos=1">Ver todos los disponibles</a>
            <?php endif; ?>
        </div>
    </div>

    <div id="mtAviso" class="alert d-none"></div>

    <?php if (empty($opciones)): ?>
        <div class="alert alert-info">
            No encontramos inmuebles que le queden bien con sus datos actuales.
            <a href="<?= site_url('matching/cliente/' . $cliente['id']) ?>?todos=1">Ver todos los disponibles</a> o completa su presupuesto, zona y forma de pago en Prospectos.
        </div>
    <?php else: ?>
        <div class="row">
            <?php foreach ($opciones as $o):
                $pts = $o['match']['puntos'];
                $url = url_inmueble($o);
                $msg = "Hola {$primerNombre}, te comparto esta opción que puede interesarte: {$o['name']} por " . precio_corto($o['price']) . ". Puedes ver fotos y detalles aquí: {$url}";
            ?>
                <div class="col-md-6 col-xl-4 mb-4">
                    <div class="mt-card">
                        <img src="<?= base_url($o['main_photo_path'] ?? 'assets/img/placeholder.jpg') ?>" alt="">
                        <div class="cuerpo">
                            <div class="d-flex justify-content-between align-items-start mb-1">
                                <h5 class="mb-0"><?= esc($o['name']) ?></h5>
                                <span class="mt-pts <?= $pts >= 80 ? 'alto' : ($pts >= 50 ? 'medio' : 'bajo') ?>" title="Compatibilidad"><?= $pts ?></span>
                            </div>
                            <div class="small text-muted mb-2">Clave <?= esc($o['clave']) ?><?= $o['project_name'] ? ' · ' . esc($o['project_name']) : '' ?></div>
                            <div class="h5 mb-2"><?= precio_corto($o['price']) ?> <small class="text-muted">MXN</small></div>
                            <?php foreach ($o['match']['razones'] as $r): ?>
                                <div class="mt-razon">
                                    <?php if ($r['ok'] === true): ?><i class="fas fa-check text-success"></i>
                                    <?php elseif ($r['ok'] === false): ?><i class="fas fa-times text-danger"></i>
                                    <?php else: ?><i class="fas fa-minus text-warning"></i><?php endif; ?>
                                    <?= esc($r['texto']) ?>
                                </div>
                            <?php endforeach; ?>
                            <div class="mt-auto pt-3 d-flex flex-wrap" style="gap: 6px;">
                                <?php if ($o['ya_interesado']): ?>
                                    <span class="btn btn-sm btn-light disabled"><i class="fas fa-check"></i> Ya en sus intereses</span>
                                <?php else: ?>
                                    <button type="button" class="btn btn-sm btn-primary js-agregar" data-id="<?= (int) $o['id'] ?>"><i class="fas fa-plus"></i> Agregar a sus intereses</button>
                                <?php endif; ?>
                                <?php if (strlen($tel) >= 12): ?>
                                    <a class="btn btn-sm btn-success" target="_blank" rel="noopener" href="https://wa.me/<?= $tel ?>?text=<?= rawurlencode($msg) ?>"><i class="fab fa-whatsapp"></i> Enviar</a>
                                <?php endif; ?>
                                <a class="btn btn-sm btn-outline-secondary" target="_blank" href="<?= $url ?>">Ver ficha</a>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>
<script>
document.querySelectorAll('.js-agregar').forEach(function (b) {
    b.addEventListener('click', function () {
        b.disabled = true;
        var datos = new FormData();
        datos.append('real_estate_id', b.dataset.id);
        datos.append('interest_level', 'Medio');
        datos.append('notes', 'Sugerido desde "¿Qué le ofrezco?"');
        fetch('<?= site_url('prospects/addPropertyInterest/' . (int) $cliente['id']) ?>', { method: 'POST', body: datos, headers: { 'X-Requested-With': 'XMLHttpRequest' } })
            .then(function (r) { return r.json(); })
            .then(function (r) {
                if (r.success) {
                    b.outerHTML = '<span class="btn btn-sm btn-light disabled"><i class="fas fa-check"></i> Agregado</span>';
                } else {
                    b.disabled = false;
                    var aviso = document.getElementById('mtAviso');
                    aviso.className = 'alert alert-danger';
                    aviso.textContent = Object.values(r.errors || { e: 'No se pudo agregar.' }).join(' ');
                }
            })
            .catch(function () { b.disabled = false; alert('No se pudo agregar. Intenta de nuevo.'); });
    });
});
</script>
<?= $this->endSection() ?>
