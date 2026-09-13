<?php helper('funerario'); ?>
<?= $this->extend('template') ?>
<?= $this->section('content') ?>
<div class="container-fluid mt-4">
    <div class="d-flex justify-content-between mb-3">
        <h1 class="h3">Paquetes funerarios</h1>
        <a href="<?= base_url('admin/funeral-products/create') ?>" class="btn btn-success">Nuevo paquete</a>
    </div>
    <div class="table-responsive">
        <table class="table table-striped">
            <thead>
                <tr>
                    <th>Clave</th><th>Paquete</th><th>Lugar</th><th>Funeraria</th><th>Tipo</th><th>Precio base</th><th>Estado</th><th></th>
                </tr>
            </thead>
            <tbody>
            <?php foreach ($products as $p): ?>
                <tr>
                    <td><?= esc($p['clave']) ?></td>
                    <td><?= esc($p['name']) ?></td>
                    <td><?= esc($p['location_name']) ?></td>
                    <td><?= esc($p['home_name']) ?></td>
                    <td><?= esc($p['type_name'] ?? '—') ?></td>
                    <td><?= formato_precio_funerario($p['base_price']) ?></td>
                    <td><?= esc($p['status']) ?></td>
                    <td><a href="<?= base_url('admin/funeral-products/edit/' . $p['id']) ?>" class="btn btn-sm btn-warning">Editar</a></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
<?= $this->endSection() ?>
