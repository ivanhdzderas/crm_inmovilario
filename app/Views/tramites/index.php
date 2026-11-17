<?= $this->extend('template') ?>

<?= $this->section('title') ?>Trámites de compraventa<?= $this->endSection() ?>

<?= $this->section('content') ?>
<?php use App\Libraries\Tramite; ?>
<style>
    .tr-etapas { display: flex; flex-wrap: wrap; gap: 8px; }
    .tr-etapa-kpi { flex: 1 1 140px; background: #fff; border: 1px solid #e6e9ef; border-radius: 12px; padding: 12px 14px; }
    .tr-etapa-kpi b { display: block; font-size: 1.6rem; line-height: 1.1; }
    .tr-etapa-kpi span { font-size: .8rem; color: #6b7280; }
    .tr-barra { height: 8px; background: #eef1f4; border-radius: 4px; overflow: hidden; min-width: 110px; }
    .tr-barra span { display: block; height: 100%; background: #2563eb; border-radius: 4px; }
    .tr-barra.docs span { background: #16a34a; }
    .tr-tabla td { vertical-align: middle; }
</style>
<div class="container-fluid">
    <div class="d-flex flex-wrap justify-content-between align-items-center pt-3 pb-2 mb-3 border-bottom">
        <div>
            <h1 class="h3 mb-1"><i class="fas fa-route text-primary"></i> Trámites de compraventa</h1>
            <p class="text-muted mb-0">En qué paso va cada compra, desde el apartado hasta la entrega. Los trámites se crean al registrar un apartado en el prospecto.</p>
        </div>
    </div>

    <div class="tr-etapas mb-3">
        <?php foreach (Tramite::ETAPAS as $clave => [$nombre]): if ($clave === 'entregado' && ! $concluidos) continue; ?>
            <div class="tr-etapa-kpi"><b><?= $porEtapa[$clave] ?></b><span><?= esc($nombre) ?></span></div>
        <?php endforeach; ?>
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
                <div class="custom-control custom-checkbox">
                    <input type="checkbox" class="custom-control-input" id="concluidos" name="concluidos" value="1" <?= $concluidos ? 'checked' : '' ?> onchange="this.form.submit()">
                    <label class="custom-control-label" for="concluidos">Incluir ventas concluidas</label>
                </div>
            </div>
        </div>
    </form>

    <?php if (empty($filas)): ?>
        <div class="alert alert-info">No hay trámites en curso. Cuando registres un apartado en un prospecto, aparecerá aquí.</div>
    <?php else: ?>
    <div class="card">
        <div class="table-responsive">
            <table class="table table-hover mb-0 tr-tabla">
                <thead class="thead-light"><tr><th>Cliente</th><th>Inmueble</th><th>Etapa</th><th>Documentos</th><th>Saldo</th><th></th></tr></thead>
                <tbody>
                <?php foreach ($filas as $f): $pct = Tramite::porcentaje($f['tramite_etapa']); ?>
                    <tr>
                        <td><strong><?= esc($f['full_name']) ?></strong><?php if ($esAdmin && $f['asesor_name']): ?><br><small class="text-muted"><?= esc($f['asesor_name']) ?></small><?php endif; ?></td>
                        <td><?= esc($f['inmueble'] ?? '—') ?><br><small class="text-muted">Clave <?= esc($f['clave'] ?? '') ?> · <?= precio_corto($f['price'] ?? 0) ?></small></td>
                        <td>
                            <strong><?= esc(Tramite::nombre($f['tramite_etapa'])) ?></strong>
                            <div class="tr-barra my-1"><span style="width: <?= $pct ?>%"></span></div>
                            <?php if ($f['dias_en_etapa'] !== null && $f['tramite_etapa'] !== 'entregado'): ?>
                                <small class="<?= $f['dias_en_etapa'] > 30 ? 'text-danger font-weight-bold' : 'text-muted' ?>"><?= $f['dias_en_etapa'] ?> días en esta etapa</small>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php if ($f['docs_pct'] === null): ?>
                                <small class="text-muted">Sin checklist</small>
                            <?php else: ?>
                                <small><?= (int) $f['docs_ok'] ?> de <?= (int) $f['docs_total'] ?></small>
                                <div class="tr-barra docs my-1"><span style="width: <?= $f['docs_pct'] ?>%"></span></div>
                                <?php if ((int) $f['docs_vencidos'] > 0): ?><small class="text-danger"><?= (int) $f['docs_vencidos'] ?> vencido(s)</small><?php endif; ?>
                            <?php endif; ?>
                        </td>
                        <td><?= $f['current_balance'] !== null ? dinero($f['current_balance']) : '—' ?></td>
                        <td class="text-right text-nowrap"><a class="btn btn-sm btn-primary" href="<?= site_url('tramites/' . $f['id']) ?>">Ver trámite</a></td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
    <?php endif; ?>
</div>
<?= $this->endSection() ?>
