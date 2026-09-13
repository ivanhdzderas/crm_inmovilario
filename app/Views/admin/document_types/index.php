<?= $this->extend('template') ?>

<?= $this->section('content') ?>
<div class="container mt-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1>Catálogo de Documentos</h1>
        <a href="<?= base_url('admin/document_types/create') ?>" class="btn btn-success">Agregar Nuevo</a>
    </div>
    <hr>
    <?php if (session()->getFlashdata('success')): ?>
        <div class="alert alert-success">
            <?= session()->getFlashdata('success') ?>
        </div>
    <?php endif; ?>

    <div class="table-responsive">
    <table class="table table-striped table-bordered table-hover" id="documentTypesTable" style="width:100%">
        <thead>
            <tr>
                <th>ID</th>
                <th>Nombre</th>
                <th>Descripción</th>
                <th>Requerido</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody>
            <?php if (!empty($document_types) && is_array($document_types)): ?>
                <?php foreach ($document_types as $doc_type): ?>
                    <tr>
                        <td><?= esc($doc_type['id']) ?></td>
                        <td><?= esc($doc_type['name']) ?></td>
                        <td><?= esc($doc_type['description']) ?></td>
                        <td><?= $doc_type['is_required'] ? 'Sí' : 'No' ?></td>
                        <td>
                            <a href="<?= base_url('admin/document_types/edit/' . $doc_type['id']) ?>" class="btn btn-warning btn-sm">Editar</a>
                            <a href="#" class="btn btn-danger btn-sm" onclick="confirmDeleteDocumentType('<?= base_url('admin/document_types/delete/' . $doc_type['id']) ?>', '<?= esc($doc_type['name']) ?>')">Eliminar</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="5" class="text-center">No hay tipos de documentos registrados.</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
    </div>
</div>

<script>
$(document).ready(function() {
    new DataTable('#documentTypesTable', {
        responsive: true,
        scrollX: false,
        autoWidth: false,
        language: {
            url: '//cdn.datatables.net/plug-ins/1.13.6/i18n/Spanish.json'
        },
        pageLength: 25,
        order: [[1, 'asc']],
        columnDefs: [
            { orderable: false, targets: -1 },
            { responsivePriority: 1, targets: 1 }, // Nombre
            { responsivePriority: 2, targets: -1 }, // Acciones SIEMPRE
            { responsivePriority: 10001, targets: [0, 2, 3] }, // Ocultar resto
            { width: "120px", targets: -1 }
        ]
    });
});
</script>

<?= $this->endSection() ?>
