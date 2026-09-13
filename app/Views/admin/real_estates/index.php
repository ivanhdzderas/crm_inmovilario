<?= $this->extend('template') ?>

<?= $this->section('title') ?>
Gestión de Inmuebles
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="card">
    <h1 class="text-center">Gestión de Inmuebles</h1>
    <hr>
    <a href="<?= base_url('admin/real_estates/new') ?>" class="btn btn-primary mb-3">Agregar Inmueble</a>
    <div class="table-responsive">
    <table class="table table-striped table-hover" id="realEstatesTable" style="width:100%">
        <thead>
            <tr>
                <th>ID</th>
                <th>Clave</th>
                <th>Nombre</th>
                <th>Precio</th>
                <th>Estado</th>
                <th>Proyecto</th>
                <th>Traído por</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody>
           <?php if (! empty($realEstates) && is_array($realEstates)): ?>
                <?php foreach ($realEstates as $inmueble): ?>
                    <tr>
                        <td><?= esc($inmueble['id']) ?></td>
                        <td><?= esc($inmueble['clave']) ?></td>
                        <td><?= esc($inmueble['name']) ?></td>
                        <td>$<?= number_format(esc($inmueble['price']), 2) ?></td>
                        <td><?= esc($inmueble['status']) ?></td>
                        <td><?= esc($inmueble['project_name']); ?></td>
                        <td><?= !empty($inmueble['brought_by_name']) ? esc($inmueble['brought_by_name']) : '<span class="text-muted">Sin asignar</span>' ?></td>
                        <td class="text-center">
                            <div class="btn-group">
                                <button type="button" class="btn btn-primary btn-sm dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                    Acciones
                                </button>
                                <div class="dropdown-menu">
                                    <a class="dropdown-item" href="<?= base_url('admin/real_estates/edit/' . $inmueble['id']) ?>"><i class="fas fa-edit text-warning fa-fw mr-2"></i>Editar</a>
                                    <a class="dropdown-item" href="<?= base_url('admin/real_estates/documents/' . esc($inmueble['id'])); ?>"><i class="fas fa-folder text-secondary fa-fw mr-2"></i>Documentación</a>
                                    <a class="dropdown-item" href="#" onclick="confirmarDescargaPdf(<?= $inmueble['id'] ?>); return false;"><i class="fas fa-file-pdf text-info fa-fw mr-2"></i>Descargar PDF</a>
                                    
                                    <?php if (in_array(strtolower($inmueble['status']), ['disponible', 'nuevo'])): ?>
                                        <div class="dropdown-divider"></div>
                                        <a class="dropdown-item" href="#" onclick="showMatchingProspects(<?= $inmueble['id'] ?>, '<?= esc($inmueble['clave']) ?>')"><i class="fas fa-users text-success fa-fw mr-2"></i>Prospectos</a>
                                    <?php endif; ?>

                                    <div class="dropdown-divider"></div>
                                    <a class="dropdown-item" href="#" onclick="confirmDeleteRealEstate('<?= base_url('admin/real_estates/delete/' . $inmueble['id']) ?>', '<?= esc($inmueble['clave']) ?>')"><i class="fas fa-trash text-danger fa-fw mr-2"></i>Eliminar</a>
                                </div>
                            </div>
                        </td>
                    </tr>
                <?php endforeach ?>
            <?php else: ?>
                <tr>
                    <td colspan="8" class="text-center">No hay inmuebles registrados.</td>
                </tr>
            <?php endif ?>
        </tbody>
    </table>
    </div>
</div>

<script>
$(document).ready(function() {
    new DataTable('#realEstatesTable', {
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
            { responsivePriority: 1, targets: 2 }, // Nombre
            { responsivePriority: 2, targets: -1 }, // Acciones SIEMPRE
            { responsivePriority: 3, targets: 4 }, // Estado
            { responsivePriority: 10001, targets: [0, 1, 3, 5, 6] }, // Ocultar resto
            { width: "200px", targets: -1 }
        ]
    });
});
</script>

<!-- Modal para mostrar prospectos que coinciden -->
<div class="modal fade" id="matchingProspectsModal" tabindex="-1" role="dialog" aria-labelledby="matchingProspectsModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl" role="document">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title" id="matchingProspectsModalLabel">
                    <i class="fas fa-users"></i> Prospectos Interesados - <span id="realEstateClave"></span>
                </h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div id="matchingProspectsLoading" class="text-center py-5">
                    <div class="spinner-border text-success" role="status">
                        <span class="sr-only">Cargando...</span>
                    </div>
                    <p class="mt-2">Buscando prospectos que coinciden...</p>
                </div>
                
                <div id="matchingProspectsContent" style="display: none;">
                    <div class="alert alert-info mb-3">
                        <strong>Criterios de coincidencia:</strong>
                        <ul class="mb-0">
                            <li>Misma zona de interés</li>
                            <li>Monto de crédito compatible (±20% del precio del inmueble)</li>
                            <li>Método de pago compatible</li>
                        </ul>
                    </div>
                    
                    <div id="noProspectsMessage" class="alert alert-warning" style="display: none;">
                        <i class="fas fa-exclamation-triangle"></i> No se encontraron prospectos que coincidan con este inmueble.
                    </div>
                    
                    <div id="prospectsTableContainer" style="display: none;">
                        <p class="text-muted">
                            <strong>Total de prospectos encontrados:</strong> <span id="totalProspects" class="badge badge-success">0</span>
                        </p>
                        
                        <div class="table-responsive">
                            <table class="table table-striped table-hover" id="matchingProspectsTable" style="width:100%">
                                <thead class="thead-dark">
                                    <tr>
                                        <th>Nombre</th>
                                        <th>Teléfono</th>
                                        <th>Email</th>
                                        <th>Crédito</th>
                                        <th>Zona</th>
                                        <th>Asesor</th>
                                        <th>Acciones</th>
                                    </tr>
                                </thead>
                                <tbody id="matchingProspectsTableBody">
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
            </div>
        </div>
    </div>
</div>

<script>
function showMatchingProspects(realEstateId, clave) {
    // Mostrar el modal
    $('#matchingProspectsModal').modal('show');
    $('#realEstateClave').text(clave);
    
    // Mostrar loading y ocultar contenido
    $('#matchingProspectsLoading').show();
    $('#matchingProspectsContent').hide();
    
    // Hacer la petición AJAX
    $.ajax({
        url: '<?= base_url('admin/real_estates/getMatchingProspects/') ?>' + realEstateId,
        type: 'GET',
        dataType: 'json',
        success: function(response) {
            $('#matchingProspectsLoading').hide();
            $('#matchingProspectsContent').show();
            
            if (response.success && response.prospects && response.prospects.length > 0) {
                $('#noProspectsMessage').hide();
                $('#prospectsTableContainer').show();
                $('#totalProspects').text(response.total);
                
                // Limpiar y llenar la tabla
                let tbody = $('#matchingProspectsTableBody');
                tbody.empty();
                
                response.prospects.forEach(function(prospect) {
                    let creditAmount = parseFloat(prospect.credit_amount).toLocaleString('es-MX', {
                        style: 'currency',
                        currency: 'MXN'
                    });
                    
                    let row = `
                        <tr>
                            <td><strong>${prospect.full_name || 'N/A'}</strong></td>
                            <td><a href="tel:${prospect.phone}">${prospect.phone || 'N/A'}</a></td>
                            <td><a href="mailto:${prospect.email}">${prospect.email || 'N/A'}</a></td>
                            <td>${creditAmount}</td>
                            <td><span class="badge badge-primary">${prospect.zone_name || 'N/A'}</span></td>
                            <td>${prospect.asesor_name || 'Sin asignar'}</td>
                            <td>
                                <a href="<?= base_url('prospects/view/') ?>${prospect.id}" 
                                   class="btn btn-sm btn-info" 
                                   target="_blank">
                                    <i class="fas fa-eye"></i> Ver
                                </a>
                            </td>
                        </tr>
                    `;
                    tbody.append(row);
                });
                
                // Inicializar DataTable con responsive
                if ($.fn.DataTable.isDataTable('#matchingProspectsTable')) {
                    $('#matchingProspectsTable').DataTable().destroy();
                }
                new DataTable('#matchingProspectsTable', {
                    responsive: true,
                    scrollX: false,
                    autoWidth: false,
                    language: {
                        url: '//cdn.datatables.net/plug-ins/1.13.6/i18n/Spanish.json'
                    },
                    pageLength: 10,
                    order: [[0, 'asc']],
                    columnDefs: [
                        { responsivePriority: 1, targets: 0 }, // Nombre
                        { responsivePriority: 2, targets: -1 }, // Acciones SIEMPRE
                        { responsivePriority: 10001, targets: [1, 2, 4, 5] }, // Ocultar resto
                        { width: "100px", targets: -1 }
                    ]
                });
            } else {
                $('#noProspectsMessage').show();
                $('#prospectsTableContainer').hide();
            }
        },
        error: function(xhr, status, error) {
            $('#matchingProspectsLoading').hide();
            $('#matchingProspectsContent').show();
            $('#noProspectsMessage').html(
                '<i class="fas fa-exclamation-circle"></i> Error al cargar los prospectos. Por favor intente nuevamente.'
            ).show();
            $('#prospectsTableContainer').hide();
            console.error('Error:', error);
        }
    });
}

function confirmarDescargaPdf(id) {
    Swal.fire({
        title: '¿Incluir encabezado con datos?',
        text: 'Mostrar logo, nombre de la empresa y datos del asesor en la parte superior del PDF.',
        icon: 'question',
        showCancelButton: true,
        showDenyButton: true,
        confirmButtonText: '<i class="fas fa-check"></i> Sí, incluir',
        denyButtonText: '<i class="fas fa-times"></i> No incluir',
        cancelButtonText: 'Cancelar',
        confirmButtonColor: '#28a745',
        denyButtonColor: '#6c757d',
    }).then((result) => {
        if (result.isConfirmed || result.isDenied) {
            const branding = result.isConfirmed ? '1' : '0';
            const url = '<?= base_url('admin/real_estates/downloadTechnicalSheet/') ?>' + id + '?branding=' + branding;
            const a = document.createElement('a');
            a.href = url;
            a.download = '';
            a.target = '_blank';
            document.body.appendChild(a);
            a.click();
            document.body.removeChild(a);
        }
    });
}
</script>

<?= $this->endSection() ?>