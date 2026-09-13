<?= $this->extend('template') ?>
<?= $this->section('content') ?>
<?php helper(['form']); ?>
<?php $summary = session()->getFlashdata('reset_summary'); ?>

<div class="container-fluid py-4">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card border-danger shadow">
                <div class="card-header bg-danger text-white">
                    <h1 class="h4 mb-0"><i class="fa fa-exclamation-triangle me-2"></i> Reinicio de datos operativos</h1>
                </div>
                <div class="card-body">
                    <?php if (session()->getFlashdata('error')): ?>
                        <div class="alert alert-danger"><?= esc(session()->getFlashdata('error')) ?></div>
                    <?php endif; ?>

                    <?php if (session()->getFlashdata('success')): ?>
                        <div class="alert alert-success"><?= esc(session()->getFlashdata('success')) ?></div>
                    <?php endif; ?>

                    <?php if (is_array($summary)): ?>
                        <div class="alert alert-light border mb-4">
                            <h2 class="h6">Resumen del reinicio</h2>
                            <ul class="small mb-2">
                                <?php foreach ($summary['tables'] ?? [] as $table => $count): ?>
                                    <li><code><?= esc($table) ?></code>: <?= (int) $count ?> registros eliminados</li>
                                <?php endforeach; ?>
                            </ul>
                            <p class="small mb-0">Archivos en disco eliminados: <strong><?= (int) ($summary['files_deleted'] ?? 0) ?></strong></p>
                        </div>
                    <?php endif; ?>

                    <div class="alert alert-warning">
                        <strong>Acción irreversible.</strong> Se vaciarán prospectos, inmuebles, proyectos, fotos, documentos subidos,
                        agenda, cuentas de clientes, intereses funerarios y <strong>todo el catálogo funerario</strong>
                        (lugares, funerarias, paquetes y planes).
                    </div>

                    <div class="row mb-4">
                        <div class="col-md-6">
                            <h2 class="h6 text-danger">Se borrará</h2>
                            <ul class="small">
                                <li>Prospectos / clientes CRM</li>
                                <li>Minutas, agenda y cuentas</li>
                                <li>Inmuebles, proyectos, fotos y documentos de propiedades</li>
                                <li>Intereses funerarios de prospectos</li>
                                <li>Catálogo funerario (lugares, funerarias, paquetes, planes)</li>
                            </ul>
                            <p class="small text-muted mb-0">Tablas: <?= esc(implode(', ', $targetTables)) ?></p>
                        </div>
                        <div class="col-md-6">
                            <h2 class="h6 text-success">Se conservará</h2>
                            <ul class="small mb-0">
                                <?php foreach ($preservedNote as $item): ?>
                                    <li><?= esc($item) ?></li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    </div>

                    <?= form_open('admin/mantenimiento/reinicio/' . esc($token) . '/execute') ?>
                    <?= csrf_field() ?>

                    <div class="form-check mb-3">
                        <input class="form-check-input" type="checkbox" name="acknowledge" value="1" id="acknowledge" required>
                        <label class="form-check-label" for="acknowledge">
                            Entiendo que se perderán todos los datos operativos listados arriba.
                        </label>
                    </div>

                    <div class="mb-3">
                        <label for="confirmation" class="form-label">Escribe <strong>REINICIAR DATOS</strong> para confirmar</label>
                        <input type="text" name="confirmation" id="confirmation" class="form-control" required autocomplete="off" placeholder="REINICIAR DATOS">
                    </div>

                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-danger" onclick="return confirm('¿Seguro? Esta acción no se puede deshacer.');">
                            <i class="fa fa-trash me-1"></i> Ejecutar reinicio
                        </button>
                        <a href="<?= base_url('dashboard') ?>" class="btn btn-secondary">Cancelar</a>
                    </div>
                    <?= form_close() ?>
                </div>
            </div>
        </div>
    </div>
</div>
<?= $this->endSection() ?>
