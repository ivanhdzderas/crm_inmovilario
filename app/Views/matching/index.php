<?= $this->extend('template') ?>

<?= $this->section('title') ?>¿Qué le ofrezco? · Matching<?= $this->endSection() ?>

<?= $this->section('content') ?>
<style>
    .mt-kpi { background: #fff; border: 1px solid #e6e9ef; border-radius: 12px; padding: 16px 18px; height: 100%; }
    .mt-kpi b { display: block; font-size: 1.9rem; line-height: 1.1; }
    .mt-kpi span { color: #6b7280; font-size: .88rem; }
    .mt-pts { display: inline-block; min-width: 46px; text-align: center; font-weight: 700; border-radius: 999px; padding: 2px 8px; font-size: .8rem; }
    .mt-pts.alto { background: #dcfce7; color: #166534; }
    .mt-pts.medio { background: #fef3c7; color: #92400e; }
    .mt-falta { font-size: .75rem; background: #fee2e2; color: #991b1b; border-radius: 6px; padding: 1px 6px; margin-right: 4px; white-space: nowrap; }
    .mt-opcion { font-size: .85rem; }
    .mt-tabla td { vertical-align: middle; }
</style>
<?php
$clasePts = static fn (int $p) => $p >= 80 ? 'alto' : 'medio';
$fecha = static fn (?string $f) => $f ? date('d/m/Y', strtotime($f)) : '—';
?>
<div class="container-fluid">
    <div class="d-flex flex-wrap justify-content-between align-items-center pt-3 pb-2 mb-3 border-bottom">
        <div>
            <h1 class="h3 mb-1"><i class="fas fa-magic text-primary"></i> ¿Qué le ofrezco?</h1>
            <p class="text-muted mb-0">Clientes que aún no compran y los inmuebles disponibles que mejor les quedan (zona, presupuesto y forma de pago).</p>
        </div>
    </div>

    <div class="row mb-3">
        <div class="col-6 col-lg-3 mb-2"><div class="mt-kpi"><b><?= $resumen['clientes'] ?></b><span>Clientes pendientes de comprar</span></div></div>
        <div class="col-6 col-lg-3 mb-2"><div class="mt-kpi"><b class="text-success"><?= $resumen['con_opciones'] ?></b><span>Tienen al menos una opción</span></div></div>
        <div class="col-6 col-lg-3 mb-2"><div class="mt-kpi"><b class="text-danger"><?= $resumen['sin_datos'] ?></b><span>Les faltan datos para recomendar</span></div></div>
        <div class="col-6 col-lg-3 mb-2"><div class="mt-kpi"><b><?= $totalInmuebles ?></b><span>Inmuebles disponibles</span></div></div>
    </div>

    <form class="card card-body mb-3" method="get">
        <div class="form-row align-items-end">
            <?php if ($esAdmin): ?>
            <div class="col-md-4 mb-2">
                <label class="small font-weight-bold mb-1" for="asesor">Asesor</label>
                <select class="form-control" id="asesor" name="asesor" onchange="this.form.submit()">
                    <option value="">Todos</option>
                    <?php foreach ($asesores as $a): ?>
                        <option value="<?= (int) $a['id'] ?>" <?= (int) $asesorSel === (int) $a['id'] ? 'selected' : '' ?>><?= esc($a['full_name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <?php endif; ?>
            <div class="col-md-4 mb-2">
                <label class="small font-weight-bold mb-1" for="orden">Ordenar</label>
                <select class="form-control" id="orden" name="orden" onchange="this.form.submit()">
                    <option value="">Más opciones primero</option>
                    <option value="contacto" <?= $orden === 'contacto' ? 'selected' : '' ?>>Sin contactar hace más tiempo</option>
                    <option value="recientes" <?= $orden === 'recientes' ? 'selected' : '' ?>>Clientes más recientes</option>
                </select>
            </div>
        </div>
    </form>

    <?php if (empty($filas)): ?>
        <div class="alert alert-info">No hay clientes compradores pendientes<?= $asesorSel ? ' para este asesor' : '' ?>.</div>
    <?php else: ?>
    <div class="card">
        <div class="table-responsive">
            <table class="table table-hover mb-0 mt-tabla">
                <thead class="thead-light">
                    <tr><th>Cliente</th><th>Busca</th><th>Último contacto</th><th>Mejores opciones</th><th class="text-right"></th></tr>
                </thead>
                <tbody>
                <?php foreach ($filas as $f): ?>
                    <tr>
                        <td>
                            <strong><?= esc($f['full_name']) ?></strong><br>
                            <small class="text-muted"><?= esc($f['phone'] ?: ($f['email'] ?: 'Sin contacto')) ?><?= $esAdmin && $f['asesor_name'] ? ' · ' . esc($f['asesor_name']) : '' ?></small>
                        </td>
                        <td class="small">
                            <?= (float) $f['credit_amount'] > 0 ? '<b>' . precio_corto($f['credit_amount']) . '</b>' : '' ?>
                            <?= $f['zone_name'] ? ' · ' . esc(ucfirst($f['zone_name'])) : '' ?>
                            <?= $f['forma_pago'] ? ' · ' . esc($f['forma_pago']) : '' ?>
                            <?php if ($f['faltan']): ?><div class="mt-1"><?php foreach ($f['faltan'] as $x): ?><span class="mt-falta">sin <?= esc($x) ?></span><?php endforeach; ?></div><?php endif; ?>
                        </td>
                        <td class="small"><?= $fecha($f['ultimo_contacto']) ?></td>
                        <td>
                            <?php if (! $f['opciones']): ?>
                                <span class="text-muted small">Sin opciones por ahora</span>
                            <?php else: ?>
                                <?php foreach (array_slice($f['opciones'], 0, 3) as $o): ?>
                                    <div class="mt-opcion mb-1"><span class="mt-pts <?= $clasePts($o['match']['puntos']) ?>"><?= $o['match']['puntos'] ?></span> <?= esc($o['name']) ?> <span class="text-muted">· <?= precio_corto($o['price']) ?></span><?= $o['ya_interesado'] ? ' <span class="badge badge-info">ya interesado</span>' : '' ?></div>
                                <?php endforeach; ?>
                                <?php if (count($f['opciones']) > 3): ?><small class="text-muted">y <?= count($f['opciones']) - 3 ?> más</small><?php endif; ?>
                            <?php endif; ?>
                        </td>
                        <td class="text-right text-nowrap">
                            <a class="btn btn-sm btn-primary" href="<?= site_url('matching/cliente/' . $f['id']) ?>"><i class="fas fa-search"></i> Ver opciones</a>
                            <a class="btn btn-sm btn-outline-secondary" href="<?= site_url('reportes/prospecto/' . $f['id']) ?>" title="Reporte de seguimiento"><i class="fas fa-file-alt"></i></a>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
    <p class="small text-muted mt-2">Puntaje: zona (30) + presupuesto (40) + forma de pago (30). Se muestran opciones con 50 puntos o más. Si al cliente le faltan datos, complétalos en Prospectos para mejores recomendaciones.</p>
    <?php endif; ?>
</div>
<?= $this->endSection() ?>
