<?php
use App\Libraries\Tramite;

$f = static fn (?string $d, string $fmt = 'd/m/Y') => $d ? date($fmt, strtotime($d)) : '—';
$semaforo = $diasSinContacto === null ? ['Sin contactos registrados', '#991b1b', '#fee2e2']
    : ($diasSinContacto <= 7 ? ['Al día', '#166534', '#dcfce7'] : ($diasSinContacto <= 21 ? ['Requiere seguimiento', '#92400e', '#fef3c7'] : ['Sin seguimiento', '#991b1b', '#fee2e2']));
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Seguimiento · <?= esc($c['full_name']) ?></title>
    <link href="https://fonts.googleapis.com/css2?family=Manrope:wght@400;600;700;800&display=swap" rel="stylesheet">
    <style>
        * { box-sizing: border-box; }
        body { margin: 0; font-family: 'Manrope', system-ui, sans-serif; color: #1d2731; background: #eef1f4; font-size: 14px; line-height: 1.45; }
        .hoja { max-width: 900px; margin: 24px auto; background: #fff; padding: 32px 36px; border-radius: 12px; box-shadow: 0 8px 30px rgba(0,0,0,.08); }
        .barra { max-width: 900px; margin: 20px auto 0; display: flex; justify-content: space-between; gap: 10px; flex-wrap: wrap; padding: 0 4px; }
        .btn { display: inline-flex; gap: 6px; align-items: center; padding: 9px 16px; border-radius: 8px; border: 1px solid #cbd5e1; background: #fff; color: #1d2731; text-decoration: none; font-weight: 700; font-size: 13px; cursor: pointer; }
        .btn-p { background: #16222f; color: #fff; border-color: #16222f; }
        header { display: flex; justify-content: space-between; align-items: flex-start; gap: 20px; border-bottom: 3px solid #a38a5e; padding-bottom: 14px; margin-bottom: 18px; }
        header img { height: 54px; }
        h1 { font-size: 22px; margin: 0 0 2px; }
        h2 { font-size: 15px; text-transform: uppercase; letter-spacing: .08em; color: #7a6440; margin: 24px 0 10px; border-bottom: 1px solid #e5dfd3; padding-bottom: 4px; }
        .muted { color: #5d6874; }
        .grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(180px, 1fr)); gap: 8px 18px; }
        .dato small { display: block; color: #5d6874; font-size: 11px; text-transform: uppercase; letter-spacing: .05em; }
        .dato b { font-size: 14px; }
        .kpis { display: grid; grid-template-columns: repeat(4, 1fr); gap: 10px; margin: 14px 0 6px; }
        .kpi { border: 1px solid #e5e7eb; border-radius: 10px; padding: 10px 12px; }
        .kpi b { display: block; font-size: 20px; }
        .kpi span { font-size: 11px; color: #5d6874; }
        .sello { display: inline-block; padding: 3px 10px; border-radius: 999px; font-weight: 700; font-size: 12px; }
        table { width: 100%; border-collapse: collapse; }
        th { text-align: left; font-size: 11px; text-transform: uppercase; letter-spacing: .05em; color: #5d6874; border-bottom: 1px solid #e5e7eb; padding: 6px 6px; }
        td { border-bottom: 1px solid #f0f2f4; padding: 7px 6px; vertical-align: top; }
        .vacio { color: #5d6874; font-style: italic; }
        .pasos { display: flex; gap: 4px; margin-top: 6px; }
        .pasos span { flex: 1; height: 7px; border-radius: 4px; background: #e5e7eb; }
        .pasos span.on { background: #16a34a; }
        footer { margin-top: 26px; font-size: 11px; color: #5d6874; border-top: 1px solid #e5e7eb; padding-top: 8px; display: flex; justify-content: space-between; }
        @media (max-width: 640px) { .hoja { padding: 20px; margin: 12px; } .kpis { grid-template-columns: repeat(2, 1fr); } header { flex-direction: column; } }
        @media print {
            body { background: #fff; font-size: 12px; }
            .barra { display: none; }
            .hoja { box-shadow: none; margin: 0; max-width: none; padding: 0; border-radius: 0; }
            h2 { break-after: avoid; }
            tr { break-inside: avoid; }
            @page { margin: 14mm; }
        }
    </style>
</head>
<body>
    <div class="barra">
        <a class="btn" href="javascript:history.back()">← Volver</a>
        <span>
            <a class="btn" href="<?= site_url('matching/cliente/' . $c['id']) ?>">¿Qué le ofrezco?</a>
            <button class="btn btn-p" onclick="window.print()">Imprimir / Guardar PDF</button>
        </span>
    </div>

    <div class="hoja">
        <header>
            <div>
                <div class="muted" style="font-size:12px; font-weight:700; letter-spacing:.08em;">REPORTE DE SEGUIMIENTO</div>
                <h1><?= esc($c['full_name']) ?></h1>
                <div class="muted">
                    <?= esc($c['phone'] ?: '') ?><?= $c['phone'] && $c['email'] ? ' · ' : '' ?><?= esc($c['email'] ?: '') ?>
                </div>
                <div style="margin-top:6px;"><span class="sello" style="color:<?= $semaforo[1] ?>;background:<?= $semaforo[2] ?>"><?= $semaforo[0] ?></span></div>
            </div>
            <img src="<?= base_url('assets/img/logo-pacific.png') ?>" alt="<?= esc(sitio('site_name')) ?>">
        </header>

        <div class="kpis">
            <div class="kpi"><b><?= $diasAlta ?></b><span>días como prospecto</span></div>
            <div class="kpi"><b><?= count($contactos) ?></b><span>contactos registrados</span></div>
            <div class="kpi"><b><?= $diasSinContacto === null ? '—' : $diasSinContacto ?></b><span>días desde el último contacto</span></div>
            <div class="kpi"><b><?= count($intereses) + count($funerarios) ?></b><span>productos de interés</span></div>
        </div>

        <h2>Datos del prospecto</h2>
        <div class="grid">
            <div class="dato"><small>Estado</small><b><?= esc($c['status']) ?></b></div>
            <div class="dato"><small>Tipo</small><b><?= esc(ucfirst((string) ($c['client_type'] ?: 'comprador'))) ?><?= ! empty($c['is_funeral_prospect']) ? ' · funerario' : '' ?></b></div>
            <div class="dato"><small>Asesor</small><b><?= esc($c['asesor_name'] ?: 'Sin asignar') ?></b></div>
            <div class="dato"><small>Origen</small><b><?= esc($c['origen'] ?: '—') ?></b></div>
            <div class="dato"><small>Presupuesto / crédito</small><b><?= (float) $c['credit_amount'] > 0 ? dinero($c['credit_amount']) : '—' ?></b></div>
            <div class="dato"><small>Forma de pago</small><b><?= esc($formaPago ?: '—') ?></b></div>
            <div class="dato"><small>Zona de interés</small><b><?= esc($c['zone_name'] ? ucfirst($c['zone_name']) : '—') ?></b></div>
            <div class="dato"><small>Alta</small><b><?= $f($c['created_at']) ?></b></div>
            <?php if (! empty($c['acquisition_detail'])): ?><div class="dato" style="grid-column: 1 / -1;"><small>Detalle de origen</small><b style="font-weight:600"><?= esc($c['acquisition_detail']) ?></b></div><?php endif; ?>
            <?php if (! empty($c['motivo_baja'])): ?><div class="dato" style="grid-column: 1 / -1;"><small>Motivo de baja</small><b><?= esc($c['motivo_baja']) ?><?= $c['deactivation_notes'] ? ' — ' . esc($c['deactivation_notes']) : '' ?></b></div><?php endif; ?>
        </div>

        <?php if ($proxima): ?>
            <h2>Próxima actividad</h2>
            <p style="margin:0"><b><?= esc($proxima['title']) ?></b> · <?= $f($proxima['start_time'], 'd/m/Y H:i') ?><?= $proxima['type'] ? ' · ' . esc($proxima['type']) : '' ?></p>
        <?php endif; ?>

        <?php if ($tramites): ?>
            <h2>Trámite de compraventa</h2>
            <?php foreach ($tramites as $t): $ix = Tramite::indice($t['tramite_etapa']); ?>
                <div style="margin-bottom: 12px;">
                    <b><?= esc($t['inmueble']) ?></b> <span class="muted">(clave <?= esc($t['clave']) ?>)</span> — etapa: <b><?= esc(Tramite::nombre($t['tramite_etapa'])) ?></b>
                    <?php if ($t['tramite_etapa'] !== 'entregado' && $t['dias_en_etapa'] !== null): ?><span class="muted"> · <?= $t['dias_en_etapa'] ?> días en la etapa</span><?php endif; ?>
                    <div class="pasos"><?php $n = 0; foreach (Tramite::ETAPAS as $k => $e): ?><span class="<?= $n <= $ix ? 'on' : '' ?>" title="<?= esc($e[0]) ?>"></span><?php $n++; endforeach; ?></div>
                    <div class="muted" style="margin-top:4px;">
                        Documentos: <?= $t['docs_pct'] === null ? 'sin checklist' : (int) $t['docs_ok'] . ' de ' . (int) $t['docs_total'] . ((int) $t['docs_vencidos'] ? ' (' . (int) $t['docs_vencidos'] . ' vencidos)' : '') ?>
                        · Saldo: <?= $t['current_balance'] !== null ? dinero($t['current_balance']) : '—' ?>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>

        <h2>Inmuebles de interés</h2>
        <?php if (! $intereses): ?>
            <p class="vacio">Sin inmuebles registrados.</p>
        <?php else: ?>
            <table>
                <thead><tr><th>Inmueble</th><th>Precio</th><th>Interés</th><th>Estado</th><th>Desde</th></tr></thead>
                <tbody>
                <?php foreach ($intereses as $i): ?>
                    <tr>
                        <td><b><?= esc($i['name'] ?? 'Inmueble eliminado') ?></b><br><span class="muted"><?= esc($i['clave'] ?? '') ?></span><?= $i['notes'] ? '<br><span class="muted">' . esc($i['notes']) . '</span>' : '' ?></td>
                        <td><?= isset($i['price']) ? dinero($i['price']) : '—' ?></td>
                        <td><?= esc($i['interest_level']) ?></td>
                        <td><?= esc($i['status']) ?><?= $i['cancellation_notes'] ? '<br><span class="muted">' . esc($i['cancellation_notes']) . '</span>' : '' ?></td>
                        <td><?= $f($i['created_at']) ?></td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>

        <?php if ($funerarios): ?>
            <h2>Previsión funeraria</h2>
            <table>
                <thead><tr><th>Paquete</th><th>Precio</th><th>Interés</th><th>Estado</th><th>Desde</th></tr></thead>
                <tbody>
                <?php foreach ($funerarios as $i): ?>
                    <tr><td><b><?= esc($i['name'] ?? '—') ?></b><br><span class="muted"><?= esc($i['clave'] ?? '') ?></span></td><td><?= dinero($i['base_price'] ?? 0) ?></td><td><?= esc($i['interest_level']) ?></td><td><?= esc($i['status']) ?></td><td><?= $f($i['created_at']) ?></td></tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>

        <?php if ($sugerencias): ?>
            <h2>Opciones sugeridas</h2>
            <table>
                <thead><tr><th>Inmueble</th><th>Precio</th><th>Compatibilidad</th></tr></thead>
                <tbody>
                <?php foreach ($sugerencias as $s): ?>
                    <tr><td><b><?= esc($s['name']) ?></b><br><span class="muted"><?= esc($s['clave']) ?></span></td><td><?= dinero($s['price']) ?></td><td><?= $s['match']['puntos'] ?>/100 — <span class="muted"><?= esc(implode('; ', array_column($s['match']['razones'], 'texto'))) ?></span></td></tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>

        <h2>Historial de contactos</h2>
        <?php if (! $contactos): ?>
            <p class="vacio">Aún no hay contactos registrados. Registra cada llamada, mensaje o visita en el seguimiento del prospecto.</p>
        <?php else: ?>
            <table>
                <thead><tr><th style="width:110px">Fecha</th><th style="width:100px">Medio</th><th>Detalle</th><th style="width:130px">Registró</th></tr></thead>
                <tbody>
                <?php foreach ($contactos as $k): ?>
                    <tr><td><?= $f($k['contact_datetime'], 'd/m/Y H:i') ?></td><td><?= esc($k['contact_method'] ?: '—') ?></td><td><?= nl2br(esc($k['discussion_detail'] ?: '')) ?></td><td><?= esc($k['usuario'] ?: '—') ?></td></tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>

        <?php if ($actividades): ?>
            <h2>Actividades de agenda</h2>
            <table>
                <thead><tr><th style="width:120px">Fecha</th><th>Actividad</th><th style="width:110px">Estado</th></tr></thead>
                <tbody>
                <?php foreach ($actividades as $a): ?>
                    <tr><td><?= $f($a['start_time'], 'd/m/Y H:i') ?></td><td><b><?= esc($a['title']) ?></b><?= $a['description'] ? '<br><span class="muted">' . esc(mb_strimwidth(strip_tags($a['description']), 0, 160, '…')) . '</span>' : '' ?></td><td><?= esc($a['status'] ?: '—') ?></td></tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>

        <footer>
            <span><?= esc(sitio('site_name')) ?> · Documento interno, contiene datos personales.</span>
            <span>Generado el <?= date('d/m/Y H:i') ?></span>
        </footer>
    </div>
</body>
</html>
