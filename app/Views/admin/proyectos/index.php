<?= $this->extend('template') ?>

<?= $this->section('content'); ?>
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h1 class="card-title">Proyectos Inmobiliarios</h1>
        <a href="<?= base_url('admin/proyectos/create'); ?>" class="btn btn-primary">Añadir Nuevo Proyecto</a>
    </div>
    <div class="card-body">
        <?php if (session()->getFlashdata('success')): ?>
            <div class="alert alert-success">
                <?= session()->getFlashdata('success'); ?>
            </div>
        <?php endif; ?>

        <div class="table-responsive">
        <table class="table table-striped table-hover" id="projectsTable" style="width:100%">
            <thead>
                <tr>
                    <th>Nombre</th>
                    <th>Ubicación</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($proyectos) && is_array($proyectos)): ?>
                    <?php foreach ($proyectos as $proyecto): ?>
                        <tr>
                            <td><?= esc($proyecto['name']); ?></td>
                            <td><?= esc($proyecto['location']); ?></td>
                            <td>
                                <a href="<?= base_url('admin/proyectos/show/' . esc($proyecto['id'])); ?>" class="btn btn-sm btn-info">Ver</a>
                                <a href="<?= base_url('admin/proyectos/edit/' . esc($proyecto['id'])); ?>" class="btn btn-sm btn-warning">Editar</a>
                                <a href="#" class="btn btn-sm btn-danger" onclick="confirmDeleteProject('<?= base_url('admin/proyectos/delete/' . esc($proyecto['id'])); ?>', '<?= esc($proyecto['name']) ?>')">Eliminar</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="4" class="text-center">No hay proyectos para mostrar.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {
    new DataTable('#projectsTable', {
        responsive: true,
        scrollX: false,
        autoWidth: false,
        language: {
            url: '//cdn.datatables.net/plug-ins/1.13.6/i18n/Spanish.json'
        },
        pageLength: 25,
        order: [[0, 'asc']],
        columnDefs: [
            { orderable: false, targets: -1 },
            { responsivePriority: 1, targets: 0 }, // Nombre
            { responsivePriority: 2, targets: -1 }, // Acciones SIEMPRE
            { responsivePriority: 10001, targets: 1 }, // Ocultar ubicación
            { width: "150px", targets: -1 }
        ]
    });
});
</script>

<?= $this->endSection(); ?>