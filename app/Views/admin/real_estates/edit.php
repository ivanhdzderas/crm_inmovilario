<?= $this->extend('template') ?>

<?= $this->section('title') ?>
Editar Inmueble
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="container-fluid">
    <!-- Breadcrumb Navigation -->
    <nav aria-label="breadcrumb" class="mb-4">
        <ol class="breadcrumb" style="background: linear-gradient(45deg, #f8f9fa, #e9ecef); border-radius: 10px; padding: 12px 20px;">
            <li class="breadcrumb-item">
                <a href="<?= site_url('admin/dashboard') ?>" class="text-decoration-none">
                    <i class="fas fa-home mr-1"></i>Dashboard
                </a>
            </li>
            <li class="breadcrumb-item">
                <a href="<?= site_url('admin/real_estates') ?>" class="text-decoration-none">
                    <i class="fas fa-building mr-1"></i>Inmuebles
                </a>
            </li>
            <li class="breadcrumb-item active" aria-current="page">
                <i class="fas fa-edit mr-1"></i>Editar Inmueble
            </li>
        </ol>
    </nav>

    <!-- Header Principal -->
    <div class="text-center mb-5">
        <h1 class="display-4 font-weight-bold" style="color: #2c3e50;">
            <i class="fas fa-edit text-primary mr-3"></i>
            Editar Inmueble
        </h1>
        <p class="lead text-muted">Actualiza la información del inmueble existente</p>
        <hr style="width: 100px; border: 2px solid #007bff; margin: 20px auto;">
    </div>

    <?= form_open_multipart('admin/real_estates/update/' . $realEstate['id'], [
        'method' => 'post', 
        'class' => 'needs-validation',
        'novalidate' => '',
        'style' => 'max-width: 1200px; margin: 0 auto;'
    ]); ?>
    <?= csrf_field() ?>
    <input type="hidden" name="id" value="<?= esc($realEstate['id']) ?>">
    
    <!-- Información Básica -->
    <div class="card border-0 mb-4" style="box-shadow: 0 4px 15px rgba(0,0,0,0.1); border-radius: 15px;">
        <div class="card-header text-white" style="background: linear-gradient(45deg, #007bff, #0056b3); border-radius: 15px 15px 0 0;">
            <h5 class="mb-0">
                <i class="fas fa-info-circle mr-2"></i>
                Información Básica
            </h5>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-12 col-md-6 mb-3">
                    <label for="clave" class="form-label font-weight-bold">
                        <i class="fas fa-key text-warning mr-1"></i>
                        Clave
                    </label>
                    <input type="text" name="clave" id="clave" class="form-control form-control-lg" 
                           style="border-radius: 10px;" value="<?= esc($realEstate['clave']) ?>" 
                           placeholder="Ej: INM-001" required>
                    <div class="invalid-feedback">
                        Por favor ingresa una clave válida.
                    </div>
                </div>
                <div class="col-12 col-md-6 mb-3">
                    <label for="name" class="form-label font-weight-bold">
                        <i class="fas fa-home text-primary mr-1"></i>
                        Nombre / Título
                    </label>
                    <input type="text" name="name" id="name" class="form-control form-control-lg" 
                           style="border-radius: 10px;" value="<?= esc($realEstate['name']) ?>" 
                           placeholder="Ej: Casa en Residencial Las Flores" required>
                    <div class="invalid-feedback">
                        Por favor ingresa un nombre para el inmueble.
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-12 mb-3">
                    <label for="description_general" class="form-label font-weight-bold">
                        <i class="fas fa-file-alt text-info mr-1"></i>
                        Descripción General
                    </label>
                    <textarea name="description_general" id="description_general" class="form-control" 
                              style="border-radius: 10px;" rows="4" 
                              placeholder="Describe las características principales del inmueble..."><?= esc($realEstate['description_general']) ?></textarea>
                </div>
            </div>
            <div class="row mt-3">
                <div class="col-12 col-md-4 mb-3">
                    <label for="is_new" class="form-label font-weight-bold">
                        <i class="fas fa-star text-warning mr-1"></i>
                        Condición
                    </label>
                    <select name="is_new" id="is_new" class="form-control form-control-lg" style="border-radius: 10px;">
                        <option value="1" <?= ($realEstate['is_new'] == 1) ? 'selected' : '' ?>>Nuevo</option>
                        <option value="0" <?= ($realEstate['is_new'] == 0) ? 'selected' : '' ?>>Usado</option>
                    </select>
                </div>
                <div class="col-12 col-md-4 mb-3">
                    <label for="status" class="form-label font-weight-bold">
                        <i class="fas fa-tag text-info mr-1"></i>
                        Estado
                    </label>
                    <select name="status" id="status" class="form-control form-control-lg" style="border-radius: 10px;">
                        <option value="Disponible" <?= ($realEstate['status'] == 'Disponible') ? 'selected' : '' ?>>Disponible</option>
                        <option value="En apartado" <?= ($realEstate['status'] == 'En apartado') ? 'selected' : '' ?>>En apartado</option>
                        <option value="Vendido" <?= ($realEstate['status'] == 'Vendido') ? 'selected' : '' ?>>Vendido</option>
                    </select>
                </div>
                <div class="col-12 col-md-4 mb-3">
                    <label for="project_id" class="form-label font-weight-bold">
                        <i class="fas fa-building text-secondary mr-1"></i>
                        Proyecto Inmobiliario
                    </label>
                    <div class="input-group">
                        <select class="form-control form-control-lg" id="project_id" name="project_id" style="border-radius: 10px 0 0 10px;">
                            <option value="">-- Seleccionar Proyecto --</option>
                            <?php foreach ($proyectos as $proyecto): ?>
                                <option value="<?= esc($proyecto['id']); ?>" <?= ($realEstate['project_id'] == $proyecto['id']) ? 'selected' : ''; ?>><?= esc($proyecto['name']); ?></option>
                            <?php endforeach; ?>
                        </select>
                        <button class="btn btn-outline-primary" type="button" data-toggle="modal" data-target="#newProjectModal" style="border-radius: 0 10px 10px 0;">+</button>
                    </div>
                </div>
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
    <!-- Precio y Valor -->
    <div class="card border-0 mb-4" style="box-shadow: 0 4px 15px rgba(0,0,0,0.1); border-radius: 15px;">
        <div class="card-header text-white" style="background: linear-gradient(45deg, #28a745, #20c997); border-radius: 15px 15px 0 0;">
            <h5 class="mb-0">
                <i class="fas fa-dollar-sign mr-2"></i>
                Precio y Valor
            </h5>
        </div>
        <div class="card-body">
            <div class="row justify-content-center">
                <div class="col-12 col-md-8">
                    <label for="price_display" class="form-label font-weight-bold text-center d-block">
                        <i class="fas fa-money-bill-wave text-success mr-1"></i>
                        Precio del Inmueble
                    </label>
                    <div class="input-group input-group-lg mb-3" style="border-radius: 10px;">
                        <div class="input-group-prepend">
                            <span class="input-group-text" style="background: #f8f9fa; border-radius: 10px 0 0 10px; font-size: 1.5rem; font-weight: bold;">
                                <i class="fas fa-dollar-sign text-success"></i>
                            </span>
                        </div>
                        <input type="text" id="price_display" class="form-control text-right" 
                               style="font-size: 1.5rem; font-weight: bold; border-radius: 0 10px 10px 0;" 
                               placeholder="1,000,000.00" 
                               value="<?= !empty($realEstate['price']) ? number_format($realEstate['price'], 2) : '' ?>">
                        <input type="hidden" name="price" id="price" value="<?= esc($realEstate['price']) ?>" required>
                    </div>
                    <small class="form-text text-muted text-center">
                        <i class="fas fa-info-circle mr-1"></i>
                        Ejemplo: 1,500,000.00 (Un millón quinientos mil pesos)
                    </small>
                </div>
                <div class="col-12 col-md-4 mt-3 mt-md-0">
                    <div class="d-flex justify-content-between align-items-center mb-1">
                        <label class="form-label font-weight-bold text-center d-block mb-0">
                            <i class="fas fa-credit-card text-info mr-1"></i>
                            Métodos de Pago
                        </label>
                        <button type="button" class="btn btn-sm btn-outline-success py-0 px-2" onclick="addNewPaymentMethod()">
                            <i class="fas fa-plus"></i>
                        </button>
                    </div>
                    <div id="payment-methods-container" class="p-2 border rounded" style="height: 150px; overflow-y: auto;">
                        <?php
                            $selected_methods = json_decode($realEstate['payment_methods'] ?? '[]', true);
                            if (!is_array($selected_methods)) {
                                $selected_methods = [];
                            }
                        ?>
                        <?php if (!empty($payment_methods_catalog)): ?>
                            <?php foreach ($payment_methods_catalog as $method): ?>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="payment_methods[]" value="<?= esc($method) ?>" id="pm_edit_<?= esc(url_title($method, '-', true)) ?>" <?= in_array($method, $selected_methods) ? 'checked' : '' ?>>
                                    <label class="form-check-label" for="pm_edit_<?= esc(url_title($method, '-', true)) ?>"><?= esc($method) ?></label>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- Configuración de Apartado -->
    <div class="card border-0 mb-4" style="box-shadow: 0 4px 15px rgba(0,0,0,0.1); border-radius: 15px;">
        <div class="card-header text-white" style="background: linear-gradient(45deg, #ffc107, #fd7e14); border-radius: 15px 15px 0 0;">
            <h5 class="mb-0">
                <i class="fas fa-percent mr-2"></i>
                Configuración de Apartado
            </h5>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-12 mb-3">
                    <label for="apartado_type" class="form-label font-weight-bold">
                        <i class="fas fa-cogs text-warning mr-1"></i>
                        Tipo de Apartado
                    </label>
                    <select class="form-control form-control-lg" id="apartado_type" name="apartado_type" 
                            onchange="toggleApartadoFields()" style="border-radius: 10px;">
                        <option value="not_applicable" <?= ($realEstate['apartado_type'] ?? 'not_applicable') == 'not_applicable' ? 'selected' : '' ?>>
                            No requiere apartado
                        </option>
                        <option value="percentage" <?= ($realEstate['apartado_type'] ?? '') == 'percentage' ? 'selected' : '' ?>>
                            Apartado por porcentaje
                        </option>
                        <option value="fixed_amount" <?= ($realEstate['apartado_type'] ?? '') == 'fixed_amount' ? 'selected' : '' ?>>
                            Apartado por monto fijo
                        </option>
                    </select>
                </div>
            </div>
            
            <div class="row">
                <div class="col-12 col-md-6">
                    <div class="form-group" id="percentage_field" style="display: none;">
                        <label for="apartado_percentage" class="form-label font-weight-bold">
                            <i class="fas fa-percentage text-info mr-1"></i>
                            Porcentaje del Apartado
                        </label>
                        <div class="input-group input-group-lg">
                            <input type="number" class="form-control" id="apartado_percentage" 
                                   name="apartado_percentage" step="0.01" min="0" max="100" 
                                   placeholder="Ej: 1.0" value="<?= esc($realEstate['apartado_percentage'] ?? '') ?>"
                                   style="border-radius: 10px 0 0 10px;">
                            <div class="input-group-append">
                                <span class="input-group-text" style="background: #f8f9fa; border-radius: 0 10px 10px 0; font-weight: bold;">%</span>
                            </div>
                        </div>
                        <small class="form-text text-muted">
                            <i class="fas fa-lightbulb mr-1"></i>
                            Porcentaje del precio total del inmueble
                        </small>
                    </div>
                    
                    <div class="form-group" id="fixed_amount_field" style="display: none;">
                        <label for="apartado_fixed_amount" class="form-label font-weight-bold">
                            <i class="fas fa-dollar-sign text-success mr-1"></i>
                            Monto Fijo del Apartado
                        </label>
                        <div class="input-group input-group-lg">
                            <div class="input-group-prepend">
                                <span class="input-group-text" style="background: #f8f9fa; border-radius: 10px 0 0 10px; font-weight: bold;">$</span>
                            </div>
                            <input type="number" class="form-control" id="apartado_fixed_amount" 
                                   name="apartado_fixed_amount" step="0.01" min="0" 
                                   placeholder="25,000.00" value="<?= esc($realEstate['apartado_fixed_amount'] ?? '') ?>"
                                   style="border-radius: 0 10px 10px 0;">
                        </div>
                        <small class="form-text text-muted">
                            <i class="fas fa-lightbulb mr-1"></i>
                            Cantidad fija sin importar el precio del inmueble
                        </small>
                    </div>
                </div>
                <div class="col-12 col-md-6">
                    <div class="form-group">
                        <label for="apartado_description" class="form-label font-weight-bold">
                            <i class="fas fa-file-alt text-primary mr-1"></i>
                            Descripción del Apartado
                        </label>
                        <textarea class="form-control" id="apartado_description" 
                                  name="apartado_description" rows="4" 
                                  style="border-radius: 10px;"
                                  placeholder="Ej: Se debe pagar al firmar contrato de promesa de compraventa..."><?= esc($realEstate['apartado_description'] ?? '') ?></textarea>
                        <small class="form-text text-muted">
                            <i class="fas fa-info-circle mr-1"></i>
                            Condiciones y términos del apartado
                        </small>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Información de Responsables -->
    <div class="card mb-4">
        <div class="card-header">
            <h5 class="mb-0"><i class="fas fa-users"></i> Información de Responsables</h5>
        </div>
        <div class="card-body">
            <div class="form-group">
                <label for="brought_by_user_id">¿Quién trajo este inmueble? <small class="text-muted">(Para comisiones)</small>:</label>
                <select class="form-control" id="brought_by_user_id" name="brought_by_user_id">
                    <option value="">Seleccionar asesor/usuario...</option>
                    <?php foreach ($users as $user): ?>
                        <option value="<?= $user['id'] ?>" <?= ($realEstate['brought_by_user_id'] == $user['id']) ? 'selected' : '' ?>>
                            <?= esc($user['full_name']) ?> - <?= esc($user['email']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <small class="form-text text-muted">
                    Esta persona recibirá comisión por haber conseguido/traído este inmueble al catálogo
                </small>
            </div>
        </div>
    </div>
    
    <!-- Configuración de Comisiones -->
    <div class="card mb-4">
        <div class="card-header">
            <h5 class="mb-0"><i class="fas fa-dollar-sign"></i> Configuración de Comisiones</h5>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-12 col-md-6">
                    <h6 class="text-primary"><i class="fas fa-handshake"></i> Comisión por Venta</h6>
                    
                    <div class="form-group">
                        <label for="sales_commission_type">Tipo de Comisión (Vendedor):</label>
                        <select class="form-control" id="sales_commission_type" name="sales_commission_type" onchange="toggleSalesCommissionFields()">
                            <option value="percentage" <?= ($realEstate['sales_commission_type'] == 'percentage') ? 'selected' : '' ?>>Porcentaje</option>
                            <option value="fixed_amount" <?= ($realEstate['sales_commission_type'] == 'fixed_amount') ? 'selected' : '' ?>>Monto Fijo</option>
                        </select>
                    </div>
                    
                    <div class="form-group" id="sales_percentage_field">
                        <label for="sales_commission_percentage">Porcentaje de Comisión (%):</label>
                        <div class="input-group">
                            <input type="number" class="form-control" id="sales_commission_percentage" 
                                   name="sales_commission_percentage" step="0.01" min="0" max="100" 
                                   placeholder="Ej: 3.5" value="<?= esc($realEstate['sales_commission_percentage'] ?? '') ?>">
                            <div class="input-group-append">
                                <span class="input-group-text">%</span>
                            </div>
                        </div>
                    </div>
                    
                    <div class="form-group" id="sales_fixed_field">
                        <label for="sales_commission_fixed_amount">Monto Fijo de Comisión ($):</label>
                        <div class="input-group">
                            <div class="input-group-prepend">
                                <span class="input-group-text">$</span>
                            </div>
                            <input type="number" class="form-control" id="sales_commission_fixed_amount" 
                                   name="sales_commission_fixed_amount" step="0.01" min="0" 
                                   placeholder="0.00" value="<?= esc($realEstate['sales_commission_fixed_amount'] ?? '') ?>">
                        </div>
                    </div>
                </div>
                
                <div class="col-12 col-md-6 mt-3 mt-md-0">
                    <h6 class="text-success"><i class="fas fa-home"></i> Comisión por Traer Inmueble</h6>
                    
                    <div class="form-group">
                        <label for="brought_commission_type">Tipo de Comisión (Quien trajo):</label>
                        <select class="form-control" id="brought_commission_type" name="brought_commission_type" onchange="toggleBroughtCommissionFields()">
                            <option value="percentage" <?= ($realEstate['brought_commission_type'] == 'percentage') ? 'selected' : '' ?>>Porcentaje</option>
                            <option value="fixed_amount" <?= ($realEstate['brought_commission_type'] == 'fixed_amount') ? 'selected' : '' ?>>Monto Fijo</option>
                        </select>
                    </div>
                    
                    <div class="form-group" id="brought_percentage_field">
                        <label for="brought_commission_percentage">Porcentaje de Comisión (%):</label>
                        <div class="input-group">
                            <input type="number" class="form-control" id="brought_commission_percentage" 
                                   name="brought_commission_percentage" step="0.01" min="0" max="100" 
                                   placeholder="Ej: 1.0" value="<?= esc($realEstate['brought_commission_percentage'] ?? '') ?>">
                            <div class="input-group-append">
                                <span class="input-group-text">%</span>
                            </div>
                        </div>
                    </div>
                    
                    <div class="form-group" id="brought_fixed_field">
                        <label for="brought_commission_fixed_amount">Monto Fijo de Comisión ($):</label>
                        <div class="input-group">
                            <div class="input-group-prepend">
                                <span class="input-group-text">$</span>
                            </div>
                            <input type="number" class="form-control" id="brought_commission_fixed_amount" 
                                   name="brought_commission_fixed_amount" step="0.01" min="0" 
                                   placeholder="0.00" value="<?= esc($realEstate['brought_commission_fixed_amount'] ?? '') ?>">
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="alert alert-info mt-3">
                <small><i class="fas fa-info-circle"></i> <strong>Nota:</strong> Las comisiones se calcularán automáticamente cuando se registre una venta.</small>
            </div>
        </div>
    </div>
    
    <!-- Información de Ubicación -->
    <div class="card my-4">
        <div class="card-header">
            <h5 class="mb-0"><i class="fas fa-map-marker-alt"></i> Ubicación del Inmueble</h5>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-12">
                    <div class="form-group">
                        <label for="address">Dirección Completa:</label>
                        <textarea class="form-control" id="address" name="address" rows="2" 
                                  placeholder="Ej: Calle Benito Juárez #123, Col. Centro, Ciudad de México, CDMX, CP 06000"><?= esc($realEstate['address'] ?? '') ?></textarea>
                        <small class="form-text text-muted">Incluye calle, número, colonia, ciudad, estado y código postal</small>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-12">
                    <div class="form-group">
                        <label for="google_maps_link">Liga de Google Maps:</label>
                        <textarea class="form-control" id="google_maps_link" name="google_maps_link" rows="2" 
                                  placeholder="Ej: https://maps.google.com/maps?q=19.4326,-99.1332 o https://goo.gl/maps/..."><?= esc($realEstate['google_maps_link'] ?? '') ?></textarea>
                        <small class="form-text text-muted">
                            <strong>Cómo obtener la liga:</strong> Busca la ubicación en Google Maps → Clic en "Compartir" → Copia el enlace
                        </small>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-12 col-md-6">
                    <div class="form-group">
                        <label for="zone_id">Zona:</label>
                        <div class="input-group">
                            <select class="form-control" name="zone_id" id="zone_id">
                                <option value="">-- Seleccionar Zona --</option>
                                <?php foreach ($zones as $zone): ?>
                                    <option value="<?= esc($zone['id']); ?>" <?= ($realEstate['zone_id'] == $zone['id']) ? 'selected' : ''; ?>>
                                        <?= esc($zone['name']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <button class="btn btn-outline-success" type="button" onclick="addNewZone(this)">+</button>
                        </div>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-6">
                    <button type="button" class="btn btn-success" onclick="generateMapsFromAddress()">
                        <i class="fas fa-search"></i> Buscar en Google Maps
                    </button>
                </div>
                <div class="col-6 text-end">
                    <button type="button" class="btn btn-warning" onclick="openMapsPreview()" id="preview-btn" style="display:<?= !empty($realEstate['google_maps_link']) ? 'inline-block' : 'none' ?>;">
                        <i class="fas fa-eye"></i> Vista Previa
                    </button>
                </div>
            </div>
        </div>
    </div>
    
    <div class="card my-4">
        <div class="card-header">
            <h5 class="mb-0">Amenidades</h5>
        </div>
        <div class="card-body">
            <div id="amenities-container">
                <?php if (!empty($amenities)): ?>
                    <?php foreach ($amenities as $amenity): ?>
                        <div class="amenity-item row g-2 mb-2">
                            <div class="col-12 col-md-5">
                                <input type="text" name="amenity_type[]" class="form-control" placeholder="Tipo (ej. Baños, Pisos)" value="<?= esc($amenity['amenity_type']) ?>">
                            </div>
                            <div class="col-12 col-md-5">
                                <input type="text" name="amenity_value[]" class="form-control" placeholder="Valor (ej. 2, 3)" value="<?= esc($amenity['value']) ?>">
                            </div>
                            <div class="col-12 col-md-2">
                                <button type="button" class="btn btn-danger remove-amenity">X</button>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
            <button type="button" id="add-amenity" class="btn btn-primary mt-3">Agregar Amenidad</button>
        </div>
    </div>
    <div class="card border-0 mb-4" style="box-shadow: 0 4px 15px rgba(0,0,0,0.1); border-radius: 15px;">
        <div class="card-header text-white" style="background: linear-gradient(45deg, #6f42c1, #007bff); border-radius: 15px 15px 0 0;">
            <h5 class="mb-0"><i class="fas fa-images mr-2"></i>Gestión de Fotografías</h5>
        </div>
        <div class="card-body">
            <p class="text-muted mb-3">Abre el gestor para borrar, subir y ordenar fotos. La primera posición siempre será la portada.</p>
            <button type="button" class="btn btn-outline-primary" data-toggle="modal" data-target="#photosManagerModal">
                <i class="fas fa-cog mr-1"></i> Administrar Fotos
            </button>
            <input type="hidden" name="existing_photo_order" id="existing_photo_order" value="">
            <input type="hidden" name="photos_to_delete" id="photos_to_delete" value="">
            <input type="hidden" name="cover_photo_index" id="cover_photo_index" value="">
        </div>
    </div>

    <div class="modal fade" id="photosManagerModal" tabindex="-1" role="dialog" aria-labelledby="photosManagerModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-xl" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="photosManagerModalLabel"><i class="fas fa-images mr-2"></i>Administrar Fotos del Inmueble</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-12 col-lg-7">
                            <h6 class="font-weight-bold">Fotos actuales (arriba = portada)</h6>
                            <div id="existing-photo-list" class="mt-3">
                                <?php if (!empty($photos)): ?>
                                    <?php foreach ($photos as $photo): ?>
                                        <div class="card mb-2 photo-item" data-photo-id="<?= (int) $photo['id'] ?>">
                                            <div class="card-body py-2">
                                                <div class="d-flex align-items-center">
                                                    <img src="<?= base_url($photo['file_path']) ?>" alt="Foto" style="width: 90px; height: 70px; object-fit: cover; border-radius: 8px;" class="mr-3">
                                                    <div class="flex-grow-1">
                                                        <div class="d-flex align-items-center mb-1">
                                                            <span class="badge badge-primary cover-badge mr-2" style="display:none;">Portada</span>
                                                            <small class="text-muted order-label"></small>
                                                        </div>
                                                        <small class="text-muted">ID foto: <?= (int) $photo['id'] ?></small>
                                                    </div>
                                                    <div class="btn-group btn-group-sm ml-2" role="group">
                                                        <button type="button" class="btn btn-outline-secondary move-up" title="Subir"><i class="fas fa-arrow-up"></i></button>
                                                        <button type="button" class="btn btn-outline-secondary move-down" title="Bajar"><i class="fas fa-arrow-down"></i></button>
                                                        <button type="button" class="btn btn-outline-danger toggle-delete" title="Marcar para borrar"><i class="fas fa-trash"></i></button>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <p class="text-muted">Este inmueble aún no tiene fotos.</p>
                                <?php endif; ?>
                            </div>
                        </div>
                        <div class="col-12 col-lg-5 mt-4 mt-lg-0">
                            <h6 class="font-weight-bold">Subir fotos nuevas</h6>
                            <input type="file" name="photos[]" id="photos" class="form-control-file" multiple="multiple" accept="image/*">
                            <div id="cover-photo-selector" class="mt-3"></div>
                            <div class="alert alert-light border mt-3 mb-0">
                                <small>
                                    1. Ordena las fotos actuales con flechas.<br>
                                    2. Marca para borrar las que no quieres.<br>
                                    3. Sube nuevas y elige portada de las nuevas (opcional).<br>
                                    4. Cierra esta ventana y presiona "Actualizar Inmueble".
                                </small>
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
    <button type="submit" class="btn btn-primary">Actualizar Inmueble</button>
    <?= form_close() ?>
</div>
<style>
    .photo-item-deleted {
        opacity: 0.45;
        filter: grayscale(100%);
    }
</style>
<script>
    // Función para mostrar/ocultar campos de comisión de venta
    function toggleSalesCommissionFields() {
        const commissionsType = document.getElementById('sales_commission_type').value;
        const percentageField = document.getElementById('sales_percentage_field');
        const fixedAmountField = document.getElementById('sales_fixed_field');
        
        if (commissionsType === 'percentage') {
            percentageField.style.display = 'block';
            fixedAmountField.style.display = 'none';
            document.getElementById('sales_commission_fixed_amount').value = '';
        } else {
            percentageField.style.display = 'none';
            fixedAmountField.style.display = 'block';
            document.getElementById('sales_commission_percentage').value = '';
        }
    }

    // Función para mostrar/ocultar campos de comisión por traer inmueble
    function toggleBroughtCommissionFields() {
        const commissionsType = document.getElementById('brought_commission_type').value;
        const percentageField = document.getElementById('brought_percentage_field');
        const fixedAmountField = document.getElementById('brought_fixed_field');
        
        if (commissionsType === 'percentage') {
            percentageField.style.display = 'block';
            fixedAmountField.style.display = 'none';
            document.getElementById('brought_commission_fixed_amount').value = '';
        } else {
            percentageField.style.display = 'none';
            fixedAmountField.style.display = 'block';
            document.getElementById('brought_commission_percentage').value = '';
        }
    }

    // Función simple para formatear precio como moneda
    function formatCurrency(input) {
        // Remover todo excepto números y puntos
        let value = input.value.replace(/[^\d.]/g, '');
        
        // Si está vacío, limpiar todo
        if (!value) {
            input.value = '';
            document.getElementById('price').value = '';
            return;
        }
        
        // Convertir a número
        let number = parseFloat(value);
        
        if (!isNaN(number)) {
            // Guardar valor numérico en campo oculto
            document.getElementById('price').value = number;
            
            // Mostrar con formato
            input.value = number.toLocaleString('en-US', {
                minimumFractionDigits: 2,
                maximumFractionDigits: 2
            });
        }
    }
    
    // Función para mostrar/ocultar campos según tipo de apartado
    function toggleApartadoFields() {
        const apartadoType = document.getElementById('apartado_type').value;
        const percentageField = document.getElementById('percentage_field');
        const fixedAmountField = document.getElementById('fixed_amount_field');
        
        // Ocultar todos los campos primero
        percentageField.style.display = 'none';
        fixedAmountField.style.display = 'none';
        
        // Mostrar el campo apropiado
        if (apartadoType === 'percentage') {
            percentageField.style.display = 'block';
        } else if (apartadoType === 'fixed_amount') {
            fixedAmountField.style.display = 'block';
        }
    }

    // Función para buscar dirección en Google Maps
    function generateMapsFromAddress() {
        const address = document.getElementById('address').value.trim();
        
        if (address) {
            // Crear URL de búsqueda en Google Maps
            const encodedAddress = encodeURIComponent(address);
            const mapsUrl = `https://maps.google.com/maps?q=${encodedAddress}`;
            
            document.getElementById('google_maps_link').value = mapsUrl;
            document.getElementById('preview-btn').style.display = 'inline-block';
            
            // Opcionalmente abrir en nueva pestaña para que el usuario confirme
            if (confirm('¿Quieres abrir la búsqueda en Google Maps para verificar la ubicación?')) {
                window.open(mapsUrl, '_blank');
            }
        } else {
            alert('Por favor ingresa una dirección válida');
        }
    }

    // Función para abrir vista previa en Google Maps
    function openMapsPreview() {
        const mapsLink = document.getElementById('google_maps_link').value;
        if (mapsLink) {
            window.open(mapsLink, '_blank');
        } else {
            alert('No hay URL de Google Maps para mostrar');
        }
    }

    document.addEventListener('DOMContentLoaded', function () {
        // Inicializar la vista de apartado según valor seleccionado
        toggleApartadoFields();
        
        // Inicializar la vista de comisiones según valores seleccionados
        toggleSalesCommissionFields();
        toggleBroughtCommissionFields();
        
        // Configurar campo de precio de forma simple
        const priceDisplay = document.getElementById('price_display');
        
        // Formatear al perder foco
        priceDisplay.addEventListener('blur', function() {
            formatCurrency(this);
        });
        
        // Mostrar/ocultar botón de vista previa si hay link
        const googleMapsLink = document.getElementById('google_maps_link');
        googleMapsLink.addEventListener('input', function() {
            const previewBtn = document.getElementById('preview-btn');
            if (this.value.trim()) {
                previewBtn.style.display = 'inline-block';
            } else {
                previewBtn.style.display = 'none';
            }
        });
        
        const addAmenityBtn = document.getElementById('add-amenity');
        const amenitiesContainer = document.getElementById('amenities-container');

        addAmenityBtn.addEventListener('click', function () {
            const newAmenityHtml = `
                <div class="amenity-item row g-2 mb-2">
                    <div class="col-12 col-md-5">
                        <input type="text" name="amenity_type[]" class="form-control" placeholder="Tipo (ej. Baños, Pisos)">
                    </div>
                    <div class="col-12 col-md-5">
                        <input type="text" name="amenity_value[]" class="form-control" placeholder="Valor (ej. 2, 3)">
                    </div>
                    <div class="col-12 col-md-2">
                        <button type="button" class="btn btn-danger remove-amenity">X</button>
                    </div>
                </div>
            `;
            amenitiesContainer.insertAdjacentHTML('beforeend', newAmenityHtml);
        });

        amenitiesContainer.addEventListener('click', function (e) {
            if (e.target.classList.contains('remove-amenity')) {
                e.target.closest('.amenity-item').remove();
            }
        });

        const photosInput = document.getElementById('photos');
        const coverPhotoIndexInput = document.getElementById('cover_photo_index');
        const coverPhotoSelector = document.getElementById('cover-photo-selector');
        const saveNewProjectBtn = document.getElementById('save_new_project_btn');
        const newProjectFeedback = document.getElementById('new-project-feedback');
        const projectSelect = document.getElementById('project_id');
        const existingPhotoList = document.getElementById('existing-photo-list');
        const existingPhotoOrderInput = document.getElementById('existing_photo_order');
        const photosToDeleteInput = document.getElementById('photos_to_delete');
        const deletedPhotoIds = new Set();

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

            let html = '<label class="font-weight-bold mb-2">Portada de las fotos nuevas:</label>';
            html += '<div class="small text-muted mb-2">No modifica las fotos existentes, solo el orden de las nuevas.</div>';

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

        function syncExistingPhotosState() {
            const items = existingPhotoList
                ? Array.from(existingPhotoList.querySelectorAll('.photo-item'))
                : [];

            const activeItems = items.filter(function(item) {
                return !item.classList.contains('photo-item-deleted');
            });

            activeItems.forEach(function(item, index) {
                const orderLabel = item.querySelector('.order-label');
                const coverBadge = item.querySelector('.cover-badge');
                if (orderLabel) {
                    orderLabel.textContent = 'Posicion ' + (index + 1);
                }
                if (coverBadge) {
                    coverBadge.style.display = index === 0 ? 'inline-block' : 'none';
                }
            });

            existingPhotoOrderInput.value = activeItems
                .map(function(item) { return item.getAttribute('data-photo-id'); })
                .join(',');

            photosToDeleteInput.value = Array.from(deletedPhotoIds).join(',');
        }

        if (existingPhotoList) {
            existingPhotoList.addEventListener('click', function(e) {
                const button = e.target.closest('button');
                if (!button) {
                    return;
                }

                const item = button.closest('.photo-item');
                if (!item) {
                    return;
                }

                const photoId = item.getAttribute('data-photo-id');

                if (button.classList.contains('move-up')) {
                    const prev = item.previousElementSibling;
                    if (prev && prev.classList.contains('photo-item')) {
                        existingPhotoList.insertBefore(item, prev);
                        syncExistingPhotosState();
                    }
                }

                if (button.classList.contains('move-down')) {
                    const next = item.nextElementSibling;
                    if (next && next.classList.contains('photo-item')) {
                        existingPhotoList.insertBefore(next, item);
                        syncExistingPhotosState();
                    }
                }

                if (button.classList.contains('toggle-delete')) {
                    const isDeleted = item.classList.toggle('photo-item-deleted');
                    if (isDeleted) {
                        deletedPhotoIds.add(photoId);
                        button.classList.remove('btn-outline-danger');
                        button.classList.add('btn-danger');
                    } else {
                        deletedPhotoIds.delete(photoId);
                        button.classList.remove('btn-danger');
                        button.classList.add('btn-outline-danger');
                    }
                    syncExistingPhotosState();
                }
            });
        }

        photosInput.addEventListener('change', renderCoverPhotoSelector);
        syncExistingPhotosState();

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
        inputLabel: 'Nombre del nuevo método (Tipo de Crédito)',
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
                    <input class="form-check-input" type="checkbox" name="payment_methods[]" value="${newMethodName}" id="pm_edit_${newMethodName.replace(/\s+/g, '-').toLowerCase()}" checked>
                    <label class="form-check-label" for="pm_edit_${newMethodName.replace(/\s+/g, '-').toLowerCase()}">${newMethodName}</label>
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
