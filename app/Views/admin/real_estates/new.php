<?= $this->extend('template') ?>

<?= $this->section('title') ?>
Nuevo Inmueble - Tony Inmobiliaria
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                <div>
                    <h1 class="h2"><i class="fas fa-plus-circle text-primary"></i> Nuevo Inmueble</h1>
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="<?= base_url('dashboard') ?>">Dashboard</a></li>
                            <li class="breadcrumb-item"><a href="<?= base_url('admin/real_estates') ?>">Inmuebles</a></li>
                            <li class="breadcrumb-item active">Nuevo</li>
                        </ol>
                    </nav>
                </div>
                <div class="btn-toolbar mb-2 mb-md-0">
                    <a href="<?= base_url('admin/real_estates') ?>" class="btn btn-outline-secondary">
                        <i class="fas fa-arrow-left"></i> Volver al Listado
                    </a>
                </div>
            </div>

            <?php if (isset($validation)): ?>
                <div class="alert alert-danger" role="alert">
                    <i class="fas fa-exclamation-triangle"></i> <strong>Errores de validación:</strong>
                    <?= $validation->listErrors() ?>
                </div>
            <?php endif; ?>
            
            <?= form_open_multipart('admin/real_estates/create', ['method' => 'post', 'class' => 'needs-validation', 'novalidate' => true]); ?>
                <?= csrf_field() ?>
                
                <div class="row">
                    <div class="col-xl-8">
                        <div class="card shadow-sm mb-4" style="border-radius: 15px; overflow: hidden; border: 0;">
                            <div class="card-header bg-gradient-primary text-white" style="background: linear-gradient(45deg, #007bff, #0056b3);">
                                <h5 class="mb-0"><i class="fas fa-info-circle"></i> Información Básica del Inmueble</h5>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label for="clave" class="font-weight-bold">Clave del Inmueble <span class="text-danger">*</span>:</label>
                                            <input type="text" name="clave" class="form-control" placeholder="Ej: INM-001" value="<?= old('clave') ?>" required>
                                        </div>
                                    </div>
                                    <div class="col-md-8">
                                        <div class="form-group">
                                            <label for="name" class="font-weight-bold">Nombre del Inmueble <span class="text-danger">*</span>:</label>
                                            <input type="text" name="name" class="form-control" placeholder="Ej: Casa en Fraccionamiento Los Pinos" value="<?= old('name') ?>" required>
                                        </div>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label for="description_general" class="font-weight-bold">Descripción General:</label>
                                    <textarea name="description_general" class="form-control" rows="4" placeholder="Describe las características..."><?= old('description_general') ?></textarea>
                                </div>
                            </div>
                        </div>

                        <div class="card shadow-sm mb-4" style="border-radius: 15px; overflow: hidden; border: 0;">
                            <div class="card-header bg-gradient-success text-white" style="background: linear-gradient(45deg, #28a745, #1e7e34);">
                                <h5 class="mb-0"><i class="fas fa-dollar-sign"></i> Información de Precios</h5>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-lg-6">
                                        <div class="form-group">
                                            <label for="price_display" class="font-weight-bold">Precio de Venta <span class="text-danger">*</span>:</label>
                                            <div class="input-group input-group-lg">
                                                <div class="input-group-prepend">
                                                    <span class="input-group-text bg-success text-white"><i class="fas fa-dollar-sign"></i></span>
                                                </div>
                                                <input type="text" id="price_display" class="form-control form-control-lg" placeholder="1,500,000.00" value="<?= old('price') ? number_format(old('price'), 2) : '' ?>">
                                                <input type="hidden" name="price" id="price" value="<?= old('price') ?>" required>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-lg-6">
                                        <div class="form-group">
                                            <div class="d-flex justify-content-between align-items-center mb-1">
                                                <label class="font-weight-bold mb-0">Métodos de Pago Aceptados:</label>
                                                <button type="button" class="btn btn-sm btn-outline-success py-0 px-2" onclick="addNewPaymentMethod()">
                                                    <i class="fas fa-plus"></i>
                                                </button>
                                            </div>
                                            <div id="payment-methods-container" class="p-2 border rounded" style="height: 120px; overflow-y: auto;">
                                                <?php if (!empty($payment_methods_catalog)): ?>
                                                    <?php foreach ($payment_methods_catalog as $method): ?>
                                                        <div class="form-check">
                                                            <input class="form-check-input" type="checkbox" name="payment_methods[]" value="<?= esc($method) ?>" id="pm_new_<?= esc(url_title($method, '-', true)) ?>" <?= in_array($method, old('payment_methods', [])) ? 'checked' : '' ?>>
                                                            <label class="form-check-label" for="pm_new_<?= esc(url_title($method, '-', true)) ?>"><?= esc($method) ?></label>
                                                        </div>
                                                    <?php endforeach; ?>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="row mt-2">
                                    <div class="col-md-4">
                                        <label class="font-weight-bold">Condición:</label>
                                        <select name="is_new" class="form-control" style="border-radius: 10px;">
                                            <option value="1" <?= old('is_new') == '1' ? 'selected' : '' ?>>Nuevo</option>
                                            <option value="0" <?= old('is_new') == '0' ? 'selected' : '' ?>>Usado</option>
                                        </select>
                                    </div>
                                    <div class="col-md-4">
                                        <label class="font-weight-bold">Estado:</label>
                                        <select name="status" class="form-control" style="border-radius: 10px;">
                                            <option value="Disponible">Disponible</option>
                                            <option value="En apartado">En apartado</option>
                                            <option value="Vendido">Vendido</option>
                                        </select>
                                    </div>
                                    <div class="col-md-4">
                                        <label class="font-weight-bold">Proyecto:</label>
                                        <div class="input-group">
                                            <select class="form-control" id="project_id" name="project_id" style="border-radius: 10px 0 0 10px;">
                                                <option value="">-- Seleccionar Proyecto --</option>
                                                <?php foreach ($proyectos as $proyecto): ?>
                                                    <option value="<?= esc($proyecto['id']); ?>"><?= esc($proyecto['name']); ?></option>
                                                <?php endforeach; ?>
                                            </select>
                                            <button class="btn btn-outline-primary" type="button" data-toggle="modal" data-target="#newProjectModal" style="border-radius: 0 10px 10px 0;">+</button>
                                        </div>
                                    </div>
                                </div>
                                <div class="row mt-2">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="zone_id" class="font-weight-bold">Zona:</label>
                                            <div class="input-group">
                                                <select class="form-control" name="zone_id" id="zone_id" style="border-radius: 10px 0 0 10px;">
                                                    <option value="">-- Seleccionar Zona --</option>
                                                    <?php foreach ($zones as $zone): ?>
                                                        <option value="<?= esc($zone['id']); ?>"><?= esc($zone['name']); ?></option>
                                                    <?php endforeach; ?>
                                                </select>
                                                <button class="btn btn-outline-success" type="button" onclick="addNewZone(this)" style="border-radius: 0 10px 10px 0;">+</button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="card shadow-sm mb-4" style="border-radius: 15px; overflow: hidden; border: 0;">
                            <div class="card-header text-white" style="background: linear-gradient(45deg, #6c757d, #495057);">
                                <h5 class="mb-0"><i class="fas fa-map-marker-alt"></i> Ubicación del Inmueble</h5>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-lg-6">
                                        <div class="form-group">
                                            <label class="font-weight-bold">Dirección Completa:</label>
                                            <textarea class="form-control" id="address" name="address" rows="2"><?= old('address') ?></textarea>
                                        </div>
                                    </div>
                                    <div class="col-lg-6">
                                        <div class="form-group">
                                            <label class="font-weight-bold">URL de Google Maps:</label>
                                            <textarea class="form-control" id="google_maps_link" name="google_maps_link" rows="2"><?= old('google_maps_link') ?></textarea>
                                        </div>
                                    </div>
                                </div>
                                <div class="row mt-2">
                                    <div class="col-6">
                                        <button type="button" class="btn btn-success" onclick="generateMapsFromAddress()">
                                            <i class="fas fa-search"></i> Buscar en Maps
                                        </button>
                                    </div>
                                    <div class="col-6 text-right">
                                        <button type="button" class="btn btn-warning" onclick="openMapsPreview()" id="preview-btn" style="display:none;">
                                            <i class="fas fa-eye"></i> Vista Previa
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-xl-4">
                        <div class="card shadow-sm mb-4" style="border-radius: 15px; overflow: hidden; border: 0;">
                            <div class="card-header bg-gradient-info text-white" style="background: linear-gradient(45deg, #17a2b8, #117a8b);">
                                <h5 class="mb-0"><i class="fas fa-percent"></i> Apartado</h5>
                            </div>
                            <div class="card-body">
                                <div class="form-group">
                                    <label class="font-weight-bold small">Tipo de Apartado:</label>
                                    <select class="form-control" id="apartado_type" name="apartado_type" onchange="toggleApartadoFields()" style="border-radius: 10px;">
                                        <option value="not_applicable">No requiere</option>
                                        <option value="percentage">Porcentaje</option>
                                        <option value="fixed_amount">Monto fijo</option>
                                    </select>
                                </div>
                                <div id="percentage_field" style="display: none;" class="mb-2">
                                    <div class="input-group">
                                        <input type="number" class="form-control" id="apartado_percentage" name="apartado_percentage" step="0.01" placeholder="Ej: 1.0">
                                        <div class="input-group-append"><span class="input-group-text bg-info text-white">%</span></div>
                                    </div>
                                </div>
                                <div id="fixed_amount_field" style="display: none;" class="mb-2">
                                    <div class="input-group">
                                        <div class="input-group-prepend"><span class="input-group-text bg-info text-white">$</span></div>
                                        <input type="number" class="form-control" id="apartado_fixed_amount" name="apartado_fixed_amount" step="0.01">
                                    </div>
                                </div>
                                <div class="form-group mt-3">
                                    <label class="font-weight-bold small">Términos:</label>
                                    <textarea class="form-control" name="apartado_description" rows="3" placeholder="Descripción..."><?= old('apartado_description') ?></textarea>
                                </div>
                            </div>
                        </div>

                        <div class="card shadow-sm mb-4" style="border-radius: 15px; overflow: hidden; border: 0;">
                            <div class="card-header text-white" style="background: linear-gradient(45deg, #17a2b8, #138496);">
                                <h5 class="mb-0"><i class="fas fa-swimming-pool"></i> Amenidades</h5>
                            </div>
                            <div class="card-body">
                                <div id="amenities-container"></div>
                                <button type="button" id="add-amenity" class="btn btn-info btn-block mt-3" style="border-radius: 10px;">
                                    <i class="fas fa-plus"></i> Agregar Amenidad
                                </button>
                            </div>
                        </div>

                        <div class="card shadow-sm mb-4" style="border-radius: 15px; overflow: hidden; border: 0;">
                            <div class="card-header bg-gradient-warning text-white" style="background: linear-gradient(45deg, #ffc107, #d39e00);">
                                <h5 class="mb-0"><i class="fas fa-users"></i> Responsable y Comisiones</h5>
                            </div>
                            <div class="card-body">
                                <div class="form-group">
                                    <label class="font-weight-bold small">¿Quién trajo el inmueble?</label>
                                    <select class="form-control" name="brought_by_user_id" style="border-radius: 10px;">
                                        <option value="">Seleccionar asesor...</option>
                                        <?php foreach ($users as $user): ?>
                                            <option value="<?= $user['id'] ?>"><?= esc($user['full_name']) ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <hr>
                                <div class="form-group">
                                    <label class="font-weight-bold small text-primary">Comisión Venta (%)</label>
                                    <input type="number" step="0.01" class="form-control" name="sales_commission_percentage" placeholder="3.5" style="border-radius: 10px;">
                                </div>
                            </div>
                        </div>

                        <div class="card shadow-sm mb-4" style="border-radius: 15px; overflow: hidden; border: 0;">
                            <div class="card-header text-white" style="background: linear-gradient(45deg, #fd7e14, #e63946);">
                                <h5 class="mb-0"><i class="fas fa-camera"></i> Fotografías</h5>
                            </div>
                            <div class="card-body">
                                <input type="file" name="photos[]" id="photos" class="form-control-file" multiple="multiple" accept="image/*" style="border: 2px dashed #dee2e6; padding: 15px; border-radius: 10px; width: 100%;">
                                <input type="hidden" name="cover_photo_index" id="cover_photo_index" value="">
                                <div id="cover-photo-selector" class="mt-3"></div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card border-0 mt-2 mb-5 shadow" style="border-radius: 15px; background: linear-gradient(45deg, #007bff, #20c997);">
                    <div class="card-body text-center py-4">
                        <button type="submit" class="btn btn-light btn-lg px-5 py-3 text-dark" style="border-radius: 25px; font-weight: bold; box-shadow: 0 4px 15px rgba(0,0,0,0.2);">
                            <i class="fas fa-home mr-2 text-primary"></i>
                            Guardar Inmueble
                        </button>
                    </div>
                </div>

            <?= form_close(); ?>
        </div>
    </div>
</div>

<div class="modal fade" id="newProjectModal" tabindex="-1" role="dialog" aria-labelledby="newProjectModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="newProjectModalLabel">Nuevo Proyecto / Desarrollo</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="form-group">
                    <label for="new_project_name" class="font-weight-bold">Nombre del proyecto <span class="text-danger">*</span></label>
                    <input type="text" id="new_project_name" class="form-control" placeholder="Ej: Residencial Los Encinos">
                </div>
                <div class="form-group">
                    <label for="new_project_location" class="font-weight-bold">Ubicación</label>
                    <input type="text" id="new_project_location" class="form-control" placeholder="Ej: Zapopan, Jalisco">
                </div>
                <div class="form-group mb-0">
                    <label for="new_project_description" class="font-weight-bold">Descripción</label>
                    <textarea id="new_project_description" class="form-control" rows="3" placeholder="Descripción breve del desarrollo"></textarea>
                </div>
                <div id="new-project-feedback" class="text-danger small mt-2" style="display:none;"></div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-primary" id="save_new_project_btn">Guardar Proyecto</button>
            </div>
        </div>
    </div>
</div>

<script>
    // Se mantienen todas tus funciones originales intactas
    function formatCurrency(input) {
        let value = input.value.replace(/[^\d.]/g, '');
        if (!value) { input.value = ''; document.getElementById('price').value = ''; return; }
        let number = parseFloat(value);
        if (!isNaN(number)) {
            document.getElementById('price').value = number;
            input.value = number.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
        }
    }

    function toggleApartadoFields() {
        const type = document.getElementById('apartado_type').value;
        document.getElementById('percentage_field').style.display = (type === 'percentage') ? 'block' : 'none';
        document.getElementById('fixed_amount_field').style.display = (type === 'fixed_amount') ? 'block' : 'none';
    }

    function generateMapsFromAddress() {
        const address = document.getElementById('address').value.trim();
        if (address) {
            const mapsUrl = `https://www.google.com/maps/search/${encodeURIComponent(address)}`;
            document.getElementById('google_maps_link').value = mapsUrl;
            document.getElementById('preview-btn').style.display = 'inline-block';
            if (confirm('¿Abrir Google Maps para verificar?')) window.open(mapsUrl, '_blank');
        } else { alert('Ingresa una dirección'); }
    }

    function openMapsPreview() {
        const link = document.getElementById('google_maps_link').value;
        if (link) window.open(link, '_blank');
    }

    document.addEventListener('DOMContentLoaded', function () {
        toggleApartadoFields();
        const priceDisplay = document.getElementById('price_display');
        priceDisplay.addEventListener('blur', function() { formatCurrency(this); });
        
        const addAmenityBtn = document.getElementById('add-amenity');
        const amenitiesContainer = document.getElementById('amenities-container');

        addAmenityBtn.addEventListener('click', function () {
            const html = `
                <div class="amenity-item form-row mb-2">
                    <div class="col"><input type="text" name="amenity_type[]" class="form-control form-control-sm" placeholder="Ej. Baños"></div>
                    <div class="col"><input type="text" name="amenity_value[]" class="form-control form-control-sm" placeholder="Ej. 2"></div>
                    <div class="col-auto"><button type="button" class="btn btn-danger btn-sm remove-amenity">X</button></div>
                </div>`;
            amenitiesContainer.insertAdjacentHTML('beforeend', html);
        });

        amenitiesContainer.addEventListener('click', function (e) {
            if (e.target.classList.contains('remove-amenity')) e.target.closest('.amenity-item').remove();
        });

        const photosInput = document.getElementById('photos');
        const coverPhotoIndexInput = document.getElementById('cover_photo_index');
        const coverPhotoSelector = document.getElementById('cover-photo-selector');
        const saveNewProjectBtn = document.getElementById('save_new_project_btn');
        const newProjectFeedback = document.getElementById('new-project-feedback');
        const projectSelect = document.getElementById('project_id');

        function escapeHtml(text) {
            const map = {
                '&': '&amp;',
                '<': '&lt;',
                '>': '&gt;',
                '"': '&quot;',
                "'": '&#039;'
            };
            return String(text).replace(/[&<>"']/g, function(m) { return map[m]; });
        }

        function renderCoverPhotoSelector() {
            const files = photosInput.files;
            if (!files || files.length === 0) {
                coverPhotoSelector.innerHTML = '';
                coverPhotoIndexInput.value = '';
                return;
            }

            let selectedIndex = parseInt(coverPhotoIndexInput.value, 10);
            if (isNaN(selectedIndex) || selectedIndex < 0 || selectedIndex >= files.length) {
                selectedIndex = 0;
            }
            coverPhotoIndexInput.value = String(selectedIndex);

            let html = '<label class="font-weight-bold mb-2">Foto de portada:</label>';
            html += '<div class="small text-muted mb-2">Si no eliges, se usa la primera foto.</div>';

            for (let i = 0; i < files.length; i++) {
                const safeName = escapeHtml(files[i].name || ('Foto ' + (i + 1)));
                html += `
                    <div class="form-check mb-1">
                        <input class="form-check-input" type="radio" name="cover_photo_choice" id="cover_photo_${i}" value="${i}" ${i === selectedIndex ? 'checked' : ''}>
                        <label class="form-check-label" for="cover_photo_${i}">${i + 1}. ${safeName}</label>
                    </div>`;
            }

            coverPhotoSelector.innerHTML = html;

            const radios = coverPhotoSelector.querySelectorAll('input[name="cover_photo_choice"]');
            radios.forEach(function(radio) {
                radio.addEventListener('change', function() {
                    coverPhotoIndexInput.value = this.value;
                });
            });
        }

        photosInput.addEventListener('change', renderCoverPhotoSelector);

        if (saveNewProjectBtn) {
            saveNewProjectBtn.addEventListener('click', function() {
                const nameInput = document.getElementById('new_project_name');
                const locationInput = document.getElementById('new_project_location');
                const descriptionInput = document.getElementById('new_project_description');

                const name = nameInput.value.trim();
                const location = locationInput.value.trim();
                const description = descriptionInput.value.trim();

                if (!name) {
                    newProjectFeedback.textContent = 'El nombre del proyecto es obligatorio.';
                    newProjectFeedback.style.display = 'block';
                    nameInput.focus();
                    return;
                }

                newProjectFeedback.style.display = 'none';
                saveNewProjectBtn.disabled = true;

                fetch('<?= site_url('admin/proyectos/store-ajax') ?>', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                        'X-Requested-With': 'XMLHttpRequest',
                        '<?= csrf_header() ?>': '<?= csrf_hash() ?>'
                    },
                    body: new URLSearchParams({
                        name: name,
                        location: location,
                        description: description
                    })
                })
                .then(function(response) {
                    return response.json().then(function(data) {
                        return { ok: response.ok, data: data };
                    });
                })
                .then(function(result) {
                    if (!result.ok || !result.data.success) {
                        throw new Error(result.data.message || (result.data.messages && result.data.messages.error) || 'No se pudo crear el proyecto.');
                    }

                    const project = result.data.project;
                    const option = new Option(project.name, project.id, true, true);
                    projectSelect.add(option);
                    projectSelect.value = String(project.id);

                    nameInput.value = '';
                    locationInput.value = '';
                    descriptionInput.value = '';

                    if (window.jQuery) {
                        window.jQuery('#newProjectModal').modal('hide');
                    }

                    Swal.fire({
                        icon: 'success',
                        title: 'Proyecto agregado',
                        text: 'Se creó y seleccionó el nuevo proyecto.'
                    });
                })
                .catch(function(error) {
                    newProjectFeedback.textContent = error.message;
                    newProjectFeedback.style.display = 'block';
                })
                .finally(function() {
                    saveNewProjectBtn.disabled = false;
                });
            });
        }
    });
</script>

<!-- Script para agregar nueva zona dinámicamente -->
<script>
function addNewZone(button) {
    Swal.fire({
        title: 'Agregar Nueva Zona',
        input: 'text',
        inputLabel: 'Nombre de la nueva zona',
        inputPlaceholder: 'Ej: Zona Centro',
        showCancelButton: true,
        confirmButtonText: 'Guardar',
        cancelButtonText: 'Cancelar',
        showLoaderOnConfirm: true,
        preConfirm: (zoneName) => {
            if (!zoneName) {
                Swal.showValidationMessage('El nombre de la zona es obligatorio');
                return false;
            }
            return fetch('<?= site_url('admin/zones/store') ?>', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                    'X-Requested-With': 'XMLHttpRequest',
                    '<?= csrf_header() ?>': '<?= csrf_hash() ?>'
                },
                body: new URLSearchParams({ 'name': zoneName })
            })
            .then(response => response.json().then(data => ({ok: response.ok, data})))
            .then(({ok, data}) => {
                if (!ok || !data.success) { throw new Error(data.message || 'Error del servidor'); }
                return data;
            })
            .catch(error => { Swal.showValidationMessage(`La solicitud falló: ${error.message}`); });
        },
        allowOutsideClick: () => !Swal.isLoading()
    }).then((result) => {
        if (result.isConfirmed && result.value.success) {
            const newZone = result.value.zone;
            const zoneSelects = document.querySelectorAll('select[name="zone_id"]');
            zoneSelects.forEach(select => {
                const option = new Option(newZone.name, newZone.id, true, true);
                select.add(option);
                select.dispatchEvent(new Event('change'));
            });
            Swal.fire({ title: '¡Éxito!', text: 'La nueva zona ha sido agregada y seleccionada.', icon: 'success', timer: 1500, showConfirmButton: false });
        } else if (result.isConfirmed) {
            Swal.fire({ title: 'Error', text: result.value.message || 'No se pudo guardar la zona.', icon: 'error' });
        }
    });
}
</script>
<!-- Script para agregar nuevo método de pago dinámicamente -->
<script>
function addNewPaymentMethod() {
    Swal.fire({
        title: 'Agregar Nuevo Método de Pago',
        input: 'text',
        inputLabel: 'Nombre del nuevo método',
        inputPlaceholder: 'Ej: Crédito Hipotecario',
        showCancelButton: true,
        confirmButtonText: 'Guardar',
        cancelButtonText: 'Cancelar',
        showLoaderOnConfirm: true,
        preConfirm: (methodName) => {
            if (!methodName) {
                Swal.showValidationMessage('El nombre es obligatorio');
                return false;
            }
            return fetch('<?= site_url('admin/client_document_catalog/add-credit-type') ?>', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                    'X-Requested-With': 'XMLHttpRequest',
                    '<?= csrf_header() ?>': '<?= csrf_hash() ?>'
                },
                body: new URLSearchParams({ 'credit_type_name': methodName })
            })
            .then(response => response.json().then(data => ({ok: response.ok, data})))
            .then(({ok, data}) => {
                if (!ok || !data.success) { throw new Error(data.message || 'Error del servidor'); }
                return data;
            })
            .catch(error => { Swal.showValidationMessage(`La solicitud falló: ${error.message}`); });
        },
        allowOutsideClick: () => !Swal.isLoading()
    }).then((result) => {
        if (result.isConfirmed && result.value.success) {
            const newMethodName = result.value.credit_type;
            const container = document.getElementById('payment-methods-container');
            const newCheckboxHtml = `
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" name="payment_methods[]" value="${newMethodName}" id="pm_new_${newMethodName.replace(/\s+/g, '-').toLowerCase()}" checked>
                    <label class="form-check-label" for="pm_new_${newMethodName.replace(/\s+/g, '-').toLowerCase()}">${newMethodName}</label>
                </div>`;
            container.insertAdjacentHTML('beforeend', newCheckboxHtml);
            
            Swal.fire({ title: '¡Éxito!', text: 'El tipo de crédito ha sido agregado y seleccionado.', icon: 'success', timer: 1500, showConfirmButton: false });
        } else if (result.isConfirmed) {
            Swal.fire({ title: 'Error', text: result.value.message || 'No se pudo guardar el método de pago.', icon: 'error' });
        }
    });
}
</script>

<?= $this->endSection() ?>