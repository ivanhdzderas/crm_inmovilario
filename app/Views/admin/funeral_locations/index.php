<?= $this->extend('template') ?>
<?= $this->section('content') ?>
<div class="container-fluid mt-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h1 class="h3">Lugares de último descanso</h1>
        <a href="<?= base_url('admin/funeral-locations/create') ?>" class="btn btn-success">Nuevo lugar</a>
    </div>
    <?php if (session()->getFlashdata('success')): ?><div class="alert alert-success"><?= session()->getFlashdata('success') ?></div><?php endif; ?>
    <div class="table-responsive">
        <table class="table table-striped">
            <thead><tr><th>Nombre</th><th>Dirección</th><th>Zona</th><th>Activo</th><th></th></tr></thead>
            <tbody>
            <?php foreach ($locations as $loc): ?>
                <tr>
                    <td><?= esc($loc['name']) ?></td>
                    <td><?= esc($loc['address']) ?></td>
                    <td><?= esc($loc['zone_name'] ?? '—') ?></td>
                    <td><?= ! empty($loc['is_active']) ? 'Sí' : 'No' ?></td>
                    <td>
                        <a href="<?= base_url('admin/funeral-locations/edit/' . $loc['id']) ?>" class="btn btn-sm btn-warning">Editar</a>
                        <a href="<?= base_url('funerario/lugar/' . esc($loc['slug'])) ?>" class="btn btn-sm btn-info" target="_blank">Ver público</a>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
<?= $this->endSection() ?>
