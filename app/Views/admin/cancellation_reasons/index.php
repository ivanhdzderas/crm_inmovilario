<?= $this->extend('template') ?>

<?= $this->section('content') ?>
<div class="container-fluid mt-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-1">Catálogo de motivos de cancelación</h1>
            <p class="text-muted mb-0">Lista usada al descartar prospectos o cancelar apartados.</p>
        </div>
        <a href="<?= base_url('admin/cancellation-reasons/create') ?>" class="btn btn-success">
            <i class="fa fa-plus me-1"></i> Agregar motivo
        </a>
    </div>

    <?php if (session()->getFlashdata('success')): ?>
        <div class="alert alert-success"><?= session()->getFlashdata('success') ?></div>
    <?php endif; ?>

    <div class="table-responsive">
        <table class="table table-striped table-bordered" id="cancellationReasonsTable">
            <thead>
                <tr>
                    <th>Orden</th>
                    <th>Motivo</th>
                    <th>Categoría</th>
                    <th>Activo</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($reasons)): ?>
                    <tr><td colspan="5" class="text-center">No hay motivos registrados.</td></tr>
                <?php else: ?>
                    <?php foreach ($reasons as $reason): ?>
                        <tr>
                            <td><?= (int) $reason['sort_order'] ?></td>
                            <td><?= esc($reason['name']) ?></td>
                            <td>
                                <?php
                                $labels = ['prospect' => 'Prospecto', 'apartado' => 'Apartado', 'both' => 'Ambos'];
                                echo esc($labels[$reason['category']] ?? $reason['category']);
                                ?>
                            </td>
                            <td><?= ! empty($reason['is_active']) ? 'Sí' : 'No' ?></td>
                            <td>
                                <a href="<?= base_url('admin/cancellation-reasons/edit/' . $reason['id']) ?>" class="btn btn-warning btn-sm">Editar</a>
                                <a href="<?= base_url('admin/cancellation-reasons/delete/' . $reason['id']) ?>" class="btn btn-danger btn-sm" onclick="return confirm('¿Eliminar este motivo?')">Eliminar</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
<?= $this->endSection() ?>
