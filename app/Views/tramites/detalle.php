<?= $this->extend('template') ?>

<?= $this->section('title') ?>Trámite · <?= esc($t['full_name']) ?><?= $this->endSection() ?>

<?= $this->section('content') ?>
<?php use App\Libraries\Tramite; $actual = Tramite::indice($t['tramite_etapa']); $concluido = $t['tramite_etapa'] === 'entregado'; ?>
<style>
    .tr-pasos { display: flex; gap: 0; margin: 10px 0 4px; }
    .tr-paso { flex: 1; text-align: center; position: relative; padding-top: 34px; font-size: .82rem; color: #6b7280; }
    .tr-paso::before { content: ""; position: absolute; top: 13px; left: -50%; width: 100%; height: 3px; background: #e5e7eb; z-index: 0; }
    .tr-paso:first-child::before { display: none; }
    .tr-paso .bola { position: absolute; top: 0; left: 50%; transform: translateX(-50%); width: 28px; height: 28px; border-radius: 50%; background: #e5e7eb; color: #6b7280; display: grid; place-items: center; font-weight: 700; z-index: 1; font-size: .8rem; }
    .tr-paso.hecho { color: #111827; }
    .tr-paso.hecho .bola { background: #16a34a; color: #fff; }
    .tr-paso.hecho::before, .tr-paso.actual::before { background: #16a34a; }
    .tr-paso.actual { color: #1d4ed8; font-weight: 700; }
    .tr-paso.actual .bola { background: #2563eb; color: #fff; box-shadow: 0 0 0 4px #dbeafe; }
    .tr-hist { border-left: 3px solid #e5e7eb; padding-left: 14px; }
    .tr-hist-item { position: relative; margin-bottom: 14px; }
    .tr-hist-item::before { content: ""; position: absolute; left: -21px; top: 5px; width: 11px; height: 11px; border-radius: 50%; background: #2563eb; }
    .doc-st { font-size: .75rem; border-radius: 6px; padding: 2px 7px; font-weight: 600; }
    @media (max-width: 767px) { .tr-paso span.txt { display: none; } }
</style>
<div class="container-fluid">
    <div class="d-flex flex-wrap justify-content-between align-items-center pt-3 pb-2 mb-3 border-bottom">
        <div>
            <a href="<?= site_url('tramites') ?>" class="small"><i class="fas fa-arrow-left"></i> Todos los trámites</a>
            <h1 class="h3 mb-1 mt-1"><?= esc($t['full_name']) ?> · <?= esc($t['inmueble'] ?? 'Inmueble') ?></h1>
            <p class="text-muted mb-0">Clave <?= esc($t['clave'] ?? '') ?> · <?= precio_corto($t['price'] ?? 0) ?><?= $t['asesor_name'] ? ' · Asesor: ' . esc($t['asesor_name']) : '' ?><?= $t['apartado_date'] ? ' · Apartado el ' . date('d/m/Y', strtotime($t['apartado_date'])) : '' ?></p>
        </div>
        <a class="btn btn-outline-secondary mt-2" href="<?= site_url('reportes/prospecto/' . $t['client_id']) ?>"><i class="fas fa-file-alt"></i> Reporte del cliente</a>
    </div>

    <?php if (session('success')): ?><div class="alert alert-success"><?= esc(session('success')) ?></div><?php endif; ?>
    <?php if (session('error')): ?><div class="alert alert-danger"><?= esc(session('error')) ?></div><?php endif; ?>

    <div class="card mb-3">
        <div class="card-body">
            <div class="tr-pasos">
                <?php $n = 0; foreach (Tramite::ETAPAS as $clave => [$nombre]): $clase = $n < $actual ? 'hecho' : ($n === $actual ? ($concluido ? 'hecho' : 'actual') : ''); ?>
                    <div class="tr-paso <?= $clase ?>"><span class="bola"><?= $n < $actual || $concluido ? '✓' : $n + 1 ?></span><span class="txt"><?= esc($nombre) ?></span></div>
                <?php $n++; endforeach; ?>
            </div>
            <p class="text-center mt-3 mb-0">
                <strong>Etapa actual: <?= esc(Tramite::nombre($t['tramite_etapa'])) ?></strong>
                <?php if (! $concluido && $t['dias_en_etapa'] !== null): ?> · <span class="<?= $t['dias_en_etapa'] > 30 ? 'text-danger' : 'text-muted' ?>"><?= $t['dias_en_etapa'] ?> días en esta etapa</span><?php endif; ?>
            </p>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-7">
            <?php if (! $concluido): ?>
            <div class="card mb-3">
                <div class="card-header"><strong>Actualizar trámite</strong></div>
                <div class="card-body">
                    <form method="post" action="<?= site_url('tramites/' . $t['id'] . '/etapa') ?>" onsubmit="return this.etapa.value !== 'entregado' || confirm('¿Confirmas que el inmueble ya se entregó? La venta quedará concluida y el inmueble como vendido.');">
                        <?= csrf_field() ?>
                        <div class="form-group">
                            <label for="etapa">Etapa</label>
                            <select class="form-control" id="etapa" name="etapa">
                                <?php foreach (Tramite::ETAPAS as $clave => [$nombre, $desc]): ?>
                                    <option value="<?= $clave ?>" <?= $clave === $t['tramite_etapa'] ? 'selected' : '' ?>><?= esc($nombre) ?> — <?= esc($desc) ?></option>
                                <?php endforeach; ?>
                            </select>
                            <small class="text-muted">Si una etapa no aplica (por ejemplo, crédito en una compra de contado), pasa directo a la siguiente.</small>
                        </div>
                        <div class="form-group">
                            <label for="nota">Nota (opcional)</label>
                            <textarea class="form-control" id="nota" name="nota" rows="3" maxlength="2000" placeholder="Ej.: Infonavit autorizó el crédito; cita con notario el 20 de diciembre."></textarea>
                        </div>
                        <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Guardar</button>
                    </form>
                </div>
            </div>
            <?php endif; ?>

            <div class="card mb-3">
                <div class="card-header"><strong>Historial</strong></div>
                <div class="card-body">
                    <?php if (empty($historial)): ?>
                        <p class="text-muted mb-0">Aún no hay cambios registrados.</p>
                    <?php else: ?>
                        <div class="tr-hist">
                            <?php foreach ($historial as $h): ?>
                                <div class="tr-hist-item">
                                    <strong><?= esc(Tramite::nombre($h['etapa'])) ?></strong>
                                    <small class="text-muted"> · <?= date('d/m/Y H:i', strtotime($h['created_at'])) ?><?= $h['usuario'] ? ' · ' . esc($h['usuario']) : '' ?></small>
                                    <?php if ($h['nota']): ?><div><?= nl2br(esc($h['nota'])) ?></div><?php endif; ?>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <div class="col-lg-5">
            <div class="card mb-3">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <strong>Documentos</strong>
                    <?php if ($t['cuenta_id']): ?><a class="small" href="<?= site_url('client-document-checklist/view/' . $t['cuenta_id']) ?>">Gestionar documentos</a><?php endif; ?>
                </div>
                <div class="card-body">
                    <?php if (empty($docs)): ?>
                        <p class="text-muted mb-0">Este trámite no tiene checklist de documentos.</p>
                    <?php else: ?>
                        <p class="mb-2"><strong><?= (int) $t['docs_ok'] ?> de <?= (int) $t['docs_total'] ?></strong> completados<?= (int) $t['docs_vencidos'] ? ' · <span class="text-danger">' . (int) $t['docs_vencidos'] . ' vencido(s)</span>' : '' ?></p>
                        <ul class="list-unstyled mb-0">
                            <?php foreach ($docs as $d):
                                $color = ['Completado' => 'background:#dcfce7;color:#166534', 'En Proceso' => 'background:#dbeafe;color:#1e40af', 'Vencido' => 'background:#fee2e2;color:#991b1b', 'No Aplica' => 'background:#f1f5f9;color:#64748b'][$d['status']] ?? 'background:#fef3c7;color:#92400e'; ?>
                                <li class="d-flex justify-content-between align-items-center py-1 border-bottom">
                                    <span class="small"><?= esc($d['document_name'] ?? 'Documento') ?></span>
                                    <span class="doc-st" style="<?= $color ?>"><?= esc($d['status']) ?></span>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    <?php endif; ?>
                </div>
            </div>
            <div class="card mb-3">
                <div class="card-header"><strong>Estado de cuenta</strong></div>
                <div class="card-body">
                    <p class="mb-1">Apartado: <strong><?= dinero($t['apartado_amount'] ?? 0) ?></strong></p>
                    <p class="mb-0">Saldo actual: <strong><?= $t['current_balance'] !== null ? dinero($t['current_balance']) : '—' ?></strong></p>
                </div>
            </div>
        </div>
    </div>
</div>
<?= $this->endSection() ?>
