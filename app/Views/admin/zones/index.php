<!-- app/Views/admin/zones/index.php -->

<?= $this->extend('template') ?>

<?= $this->section('title') ?>
<?= $title ?? 'Catálogo de Zonas' ?>
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="container-fluid py-4">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header pb-0 d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="mb-0">
                            <i class="fa fa-map-marker-alt me-2 text-primary"></i>
                            Catálogo de Zonas
                        </h6>
                        <p class="text-sm mb-0">Gestión de zonas geográficas del sistema</p>
                    </div>
                    <div>
                        <a href="<?= site_url('admin/zones/create') ?>" class="btn btn-primary btn-sm">
                            <i class="fa fa-plus me-1"></i>
                            Nueva Zona
                        </a>
                        <a href="<?= site_url('dashboard') ?>" class="btn btn-outline-secondary btn-sm">
                            <i class="fa fa-arrow-left me-1"></i>
                            Volver
                        </a>
                    </div>
                </div>
                <div class="card-body px-0 pt-0 pb-2">
                    
                    <!-- Mostrar mensajes de éxito/error -->
                    <?php if (session()->getFlashdata('success')): ?>
                        <div class="alert alert-success alert-dismissible mx-4 mt-3">
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                            <i class="fa fa-check-circle me-1"></i>
                            <?= session()->getFlashdata('success') ?>
                        </div>
                    <?php endif; ?>

                    <?php if (session()->getFlashdata('error')): ?>
                        <div class="alert alert-danger alert-dismissible mx-4 mt-3">
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                            <i class="fa fa-exclamation-triangle me-1"></i>
                            <?= session()->getFlashdata('error') ?>
                        </div>
                    <?php endif; ?>

                    <?php if (empty($zones)): ?>
                        <div class="text-center py-5">
                            <i class="fa fa-map-marker-alt fa-3x text-muted mb-3"></i>
                            <h5 class="text-muted">No hay zonas registradas</h5>
                            <p class="text-muted mb-3">Comienza creando la primera zona del sistema</p>
                            <a href="<?= site_url('admin/zones/create') ?>" class="btn btn-primary">
                                <i class="fa fa-plus me-1"></i>
                                Crear Primera Zona
                            </a>
                        </div>
                    <?php else: ?>
                        <div class="table-responsive p-0">
                            <table class="table table-hover align-items-center mb-0" id="zonesTable">
                                <thead>
                                    <tr>
                                        <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">
                                            <i class="fa fa-hashtag me-1"></i>
                                            ID
                                        </th>
                                        <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">
                                            <i class="fa fa-map-marker-alt me-1"></i>
                                            Nombre de la Zona
                                        </th>
                                        <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">
                                            <i class="fa fa-users me-1"></i>
                                            Clientes Asignados
                                        </th>
                                        <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 text-center">
                                            <i class="fa fa-cogs me-1"></i>
                                            Acciones
                                        </th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($zones as $zone): ?>
                                        <tr id="zone-row-<?= $zone['id'] ?>">
                                            <td>
                                                <p class="text-sm font-weight-bold mb-0">#<?= $zone['id'] ?></p>
                                            </td>
                                            <td>
                                                <div class="d-flex px-2 py-1">
                                                    <div class="d-flex flex-column justify-content-center">
                                                        <h6 class="mb-0 text-sm font-weight-bold">
                                                            <i class="fa fa-map-marker-alt text-primary me-2"></i>
                                                            <?= esc($zone['name']) ?>
                                                        </h6>
                                                        <p class="text-xs text-secondary mb-0">
                                                            Zona geográfica del sistema
                                                        </p>
                                                    </div>
                                                </div>
                                            </td>
                                            <td>
                                                <span class="badge bg-info text-white" id="clients-count-<?= $zone['id'] ?>">
                                                    <i class="fa fa-spinner fa-spin"></i> Cargando...
                                                </span>
                                            </td>
                                            <td class="text-center">
                                                <div class="btn-group" role="group">
                                                    <a href="<?= site_url('admin/zones/edit/' . $zone['id']) ?>" 
                                                       class="btn btn-outline-warning btn-sm" 
                                                       title="Editar zona">
                                                        <i class="fa fa-edit"></i>
                                                    </a>
                                                    <button type="button" 
                                                            class="btn btn-outline-danger btn-sm btn-delete-zone" 
                                                            data-id="<?= $zone['id'] ?>"
                                                            data-name="<?= esc($zone['name']) ?>"
                                                            title="Eliminar zona">
                                                        <i class="fa fa-trash"></i>
                                                    </button>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>

                        <!-- Información adicional -->
                        <div class="px-4 py-3">
                            <div class="row">
                                <div class="col-md-6">
                                    <p class="text-sm text-muted mb-0">
                                        <i class="fa fa-info-circle me-1"></i>
                                        Total de zonas registradas: <strong><?= count($zones) ?></strong>
                                    </p>
                                </div>
                                <div class="col-md-6 text-end">
                                    <p class="text-sm text-muted mb-0">
                                        <i class="fa fa-shield-alt me-1"></i>
                                        Solo los administradores pueden gestionar las zonas
                                    </p>
                                </div>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Script para manejo de eliminación y contadores -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    
    // Cargar contadores de clientes por zona
    loadClientsCount();
    
    // Configurar DataTable si hay zonas
    <?php if (!empty($zones)): ?>
    new DataTable('#zonesTable', {
        responsive: true,
        scrollX: false,
        autoWidth: false,
        language: {
            url: '//cdn.datatables.net/plug-ins/1.13.6/i18n/Spanish.json'
        },
        columnDefs: [
            { orderable: false, targets: [3] }, // Columna de acciones no ordenable
            { className: 'text-center', targets: [0, 3] },
            { responsivePriority: 1, targets: 1 }, // Nombre siempre visible
            { responsivePriority: 2, targets: 3 }, // Acciones SIEMPRE visible
            { responsivePriority: 10001, targets: [0, 2] }, // Ocultar ID y clientes en móvil
            { width: "120px", targets: 3 } // Ancho fijo para acciones
        ],
        order: [[1, 'asc']], // Ordenar por nombre por defecto
        pageLength: 25
    });
    <?php endif; ?>

    // Manejar eliminación de zonas
    document.querySelectorAll('.btn-delete-zone').forEach(function(btn) {
        btn.addEventListener('click', function() {
            const zoneId = this.dataset.id;
            const zoneName = this.dataset.name;
            
            Swal.fire({
                title: '¿Eliminar zona?',
                html: `¿Estás seguro de que deseas eliminar la zona <strong>"${zoneName}"</strong>?<br><br>
                       <small class="text-muted">Esta acción no se puede deshacer. La zona solo se podrá eliminar si no está siendo utilizada por ningún cliente o inmueble.</small>`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: '<i class="fa fa-trash me-1"></i> Sí, eliminar',
                cancelButtonText: '<i class="fa fa-times me-1"></i> Cancelar',
                showLoaderOnConfirm: true,
                preConfirm: () => {
                    return fetch(`<?= site_url('admin/zones/delete/') ?>${zoneId}`, {
                        method: 'POST',
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest',
                            'Accept': 'application/json'
                        }
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (!data.success) {
                            throw new Error(data.message || 'Error al eliminar la zona');
                        }
                        return data;
                    })
                    .catch(error => {
                        Swal.showValidationMessage(`Error: ${error.message}`);
                    });
                },
                allowOutsideClick: () => !Swal.isLoading()
            }).then((result) => {
                if (result.isConfirmed) {
                    Swal.fire({
                        title: '¡Eliminada!',
                        text: 'La zona ha sido eliminada correctamente.',
                        icon: 'success',
                        timer: 2000,
                        timerProgressBar: true
                    }).then(() => {
                        // Remover la fila de la tabla
                        document.getElementById(`zone-row-${zoneId}`)?.remove();
                        
                        // Si no quedan zonas, recargar la página para mostrar el mensaje vacío
                        if (document.querySelectorAll('tbody tr').length === 0) {
                            window.location.reload();
                        }
                    });
                }
            });
        });
    });
});

// Función para cargar contadores de clientes
function loadClientsCount() {
    <?php foreach ($zones as $zone): ?>
    fetch('<?= site_url('admin/zones/clients-count/' . $zone['id']) ?>', {
        headers: { 'X-Requested-With': 'XMLHttpRequest' }
    })
    .then(response => response.json())
    .then(data => {
        const badge = document.getElementById('clients-count-<?= $zone['id'] ?>');
        if (badge && data.success) {
            const count = data.count;
            badge.innerHTML = `<i class="fa fa-users me-1"></i> ${count} ${count === 1 ? 'cliente' : 'clientes'}`;
            badge.className = count > 0 ? 'badge bg-success text-white' : 'badge bg-secondary text-white';
        }
    })
    .catch(error => {
        console.error('Error cargando contador:', error);
        const badge = document.getElementById('clients-count-<?= $zone['id'] ?>');
        if (badge) {
            badge.innerHTML = '<i class="fa fa-exclamation-triangle me-1"></i> Error';
            badge.className = 'badge bg-danger text-white';
        }
    });
    <?php endforeach; ?>
}
</script>

<?= $this->endSection() ?>