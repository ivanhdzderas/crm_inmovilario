<?= $this->extend('template') ?>
<?= $this->section('content') ?>
<div class="container-fluid mt-4">
    <div class="d-flex justify-content-between mb-3">
        <h1 class="h3">Funerarias (marcas)</h1>
        <a href="<?= base_url('admin/funeral-homes/create') ?>" class="btn btn-success">Nueva funeraria</a>
    </div>
    <table class="table table-striped">
        <thead><tr><th>Nombre</th><th>Activa</th><th></th></tr></thead>
        <tbody>
        <?php foreach ($homes as $h): ?>
            <tr>
                <td><?= esc($h['name']) ?></td>
                <td><?= ! empty($h['is_active']) ? 'Sí' : 'No' ?></td>
                <td><a href="<?= base_url('admin/funeral-homes/edit/' . $h['id']) ?>" class="btn btn-sm btn-warning">Editar</a></td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</div>
<?= $this->endSection() ?>
