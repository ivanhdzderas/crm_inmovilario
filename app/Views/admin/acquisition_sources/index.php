<?= $this->extend('template') ?>
<?= $this->section('content') ?>
<div class="container-fluid mt-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-1">Cómo llegó el cliente</h1>
            <p class="text-muted mb-0">Catálogo de orígenes: redes sociales, referidos, web, etc.</p>
        </div>
        <a href="<?= base_url('admin/acquisition-sources/create') ?>" class="btn btn-success"><i class="fa fa-plus me-1"></i> Agregar canal</a>
    </div>
    <?php if (session()->getFlashdata('success')): ?><div class="alert alert-success"><?= session()->getFlashdata('success') ?></div><?php endif; ?>
    <div class="table-responsive">
        <table class="table table-striped table-bordered">
            <thead><tr><th>Orden</th><th>Canal</th><th>Tipo</th><th>Icono</th><th>Activo</th><th></th></tr></thead>
            <tbody>
                <?php foreach ($sources as $s): ?>
                <tr>
                    <td><?= (int) $s['sort_order'] ?></td>
                    <td><?php if (! empty($s['icon_class'])): ?><i class="<?= esc($s['icon_class']) ?> me-1"></i><?php endif; ?><?= esc($s['name']) ?></td>
                    <td><?= esc($channelLabels[$s['channel_type']] ?? $s['channel_type']) ?></td>
                    <td><code><?= esc($s['icon_class'] ?? '') ?></code></td>
                    <td><?= ! empty($s['is_active']) ? 'Sí' : 'No' ?></td>
                    <td>
                        <a href="<?= base_url('admin/acquisition-sources/edit/' . $s['id']) ?>" class="btn btn-warning btn-sm">Editar</a>
                        <a href="<?= base_url('admin/acquisition-sources/delete/' . $s['id']) ?>" class="btn btn-danger btn-sm" onclick="return confirm('¿Eliminar?')">Eliminar</a>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
<?= $this->endSection() ?>
