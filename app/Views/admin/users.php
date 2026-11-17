<?= $this->extend('template') ?>

<?= $this->section('title') ?>
Panel de Admin
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="card">
    <h1 class="text-center">Gestión de Usuarios</h1>
    <hr>
    <table class="table table-striped table-hover" id="usersTable" style="width:100%">
        <thead>
            <tr>
                <th>ID</th>
                <th>Nombre Completo</th>
                <th>Correo Electrónico</th>
                <th>Rol</th>
                <th>Estado</th>
                <th>Creado en</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody>
            <?php if (! empty($users) && is_array($users)): ?>
                <?php foreach ($users as $user): ?>
                    <tr>
                        <td><?= esc($user['id']) ?></td>
                        <td><?= esc($user['full_name']) ?></td>
                        <td><?= esc($user['email']) ?></td>
                        <td><?= esc($user['role_id']) == 1 ? 'Admin' : 'Asesor' ?></td>
                        <td><?= (int) $user['is_active'] === 1 ? '<span class="badge badge-success">Activo</span>' : '<span class="badge badge-warning">Pendiente / inactivo</span>' ?></td>
                        <td><?= esc($user['created_at']) ?></td>
                        <td>
                            <a href="<?= base_url('admin/users/edit/' . $user['id']) ?>" class="btn btn-warning">Editar</a>
                            <a href="#" class="btn btn-danger" onclick="confirmDeleteUser('<?= base_url('admin/users/delete/' . $user['id']) ?>', '<?= esc($user['full_name']) ?>')">Eliminar</a>
                        </td>
                    </tr>
                <?php endforeach ?>
            <?php else: ?>
                <tr>
                    <td colspan="7" class="text-center">No hay usuarios registrados.</td>
                </tr>
            <?php endif ?>
        </tbody>
    </table>
</div>

<script>
$(document).ready(function() {
    new DataTable('#usersTable', {
        responsive: true,
        scrollX: false,
        autoWidth: false,
        language: {
            url: '//cdn.datatables.net/plug-ins/1.13.6/i18n/Spanish.json'
        },
        pageLength: 25,
        order: [[0, 'desc']],
        columnDefs: [
            { orderable: false, targets: -1 },
            { responsivePriority: 1, targets: 1 }, // Nombre
            { responsivePriority: 2, targets: -1 }, // Acciones SIEMPRE
            { responsivePriority: 10001, targets: [0, 2, 3, 4, 5] }, // Ocultar resto en móvil
            { width: "150px", targets: -1 }
        ]
    });
});
</script>

<?= $this->endSection() ?>