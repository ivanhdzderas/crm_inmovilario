<?= $this->extend('template') ?>

<?= $this->section('title') ?><?= esc($proyecto['name']) ?> - Detalles del Proyecto<?= $this->endSection() ?>

<?= $this->section('styles') ?>
<style>
    .project-header {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        border-radius: 12px;
        padding: 25px;
        margin-bottom: 25px;
        box-shadow: 0 8px 25px rgba(0,0,0,0.1);
    }

    .inmueble-card {
        border: none;
        border-radius: 12px;
        box-shadow: 0 4px 15px rgba(0,0,0,0.08);
        transition: all 0.3s ease;
        overflow: hidden;
    }

    .inmueble-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 8px 25px rgba(0,0,0,0.15);
    }

    .inmueble-image {
        width: 100%;
        height: 200px;
        object-fit: cover;
        border-radius: 8px 8px 0 0;
    }

    .no-image {
        width: 100%;
        height: 200px;
        background: linear-gradient(135deg, #f8f9fa, #e9ecef);
        display: flex;
        align-items: center;
        justify-content: center;
        flex-direction: column;
        color: #6c757d;
        font-size: 1rem;
        border-radius: 8px 8px 0 0;
    }

    .price-tag {
        background: linear-gradient(45deg, #28a745, #20c997);
        color: white;
        font-weight: bold;
        font-size: 1.1rem;
    }

    .status-badge {
        position: absolute;
        top: 10px;
        right: 10px;
        z-index: 2;
        font-size: 0.75rem;
        text-transform: uppercase;
        font-weight: 600;
        letter-spacing: 0.5px;
    }

    .property-info {
        padding: 20px;
    }

    .property-title {
        font-weight: 600;
        color: #2c3e50;
        margin-bottom: 8px;
        font-size: 1.1rem;
    }

    .property-key {
        color: #6c757d;
        font-size: 0.85rem;
        margin-bottom: 12px;
    }

    .property-description {
        color: #495057;
        font-size: 0.9rem;
        line-height: 1.5;
        margin-bottom: 15px;
        display: -webkit-box;
        -webkit-line-clamp: 3;
        line-clamp: 3;
        -webkit-box-orient: vertical;
        overflow: hidden;
    }

    .property-details {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding-top: 15px;
        border-top: 1px solid rgba(0,0,0,0.1);
    }

    .photo-indicator {
        background: rgba(0,0,0,0.7);
        color: white;
        padding: 4px 8px;
        border-radius: 12px;
        font-size: 0.75rem;
        position: absolute;
        bottom: 10px;
        left: 10px;
    }

    .stats-card {
        background: white;
        border-radius: 12px;
        padding: 20px;
        box-shadow: 0 4px 15px rgba(0,0,0,0.08);
        text-align: center;
    }

    .stats-number {
        font-size: 2rem;
        font-weight: 700;
        color: #667eea;
        margin-bottom: 5px;
    }

    .stats-label {
        color: #6c757d;
        font-size: 0.9rem;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    .breadcrumb {
        background: none;
        padding: 0;
        margin-bottom: 20px;
    }

    .breadcrumb-item a {
        color: #667eea;
        text-decoration: none;
    }

    .breadcrumb-item.active {
        color: #6c757d;
    }
</style>
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="container-fluid py-4">
    <!-- Breadcrumb -->
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item">
                <a href="<?= base_url('admin/proyectos') ?>">
                    <i class="fa fa-building me-1"></i>
                    Proyectos
                </a>
            </li>
            <li class="breadcrumb-item active"><?= esc($proyecto['name']) ?></li>
        </ol>
    </nav>

    <!-- Header del Proyecto -->
    <div class="project-header">
        <div class="row align-items-center">
            <div class="col-md-8">
                <h1 class="mb-2">
                    <i class="fa fa-building me-3"></i>
                    <?= esc($proyecto['name']) ?>
                </h1>
                <p class="mb-2 opacity-90">
                    <i class="fa fa-map-marker-alt me-2"></i>
                    <?= esc($proyecto['location']) ?>
                </p>
                <?php if (!empty($proyecto['description'])): ?>
                    <p class="mb-0 opacity-75"><?= esc($proyecto['description']) ?></p>
                <?php endif; ?>
            </div>
            <div class="col-md-4 text-md-end">
                <a href="<?= base_url('admin/proyectos/edit/' . $proyecto['id']) ?>" class="btn btn-outline-light btn-lg">
                    <i class="fa fa-edit me-2"></i>
                    Editar Proyecto
                </a>
            </div>
        </div>
    </div>

    <!-- Estadísticas -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="stats-card">
                <div class="stats-number"><?= count($inmuebles) ?></div>
                <div class="stats-label">Total Inmuebles</div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stats-card">
                <div class="stats-number"><?= count(array_filter($inmuebles, fn($i) => $i['status'] === 'Disponible')) ?></div>
                <div class="stats-label">Disponibles</div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stats-card">
                <div class="stats-number"><?= count(array_filter($inmuebles, fn($i) => $i['status'] === 'Vendido')) ?></div>
                <div class="stats-label">Vendidos</div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stats-card">
                <div class="stats-number"><?= count(array_filter($inmuebles, fn($i) => $i['status'] === 'Apartado')) ?></div>
                <div class="stats-label">Apartados</div>
            </div>
        </div>
    </div>

    <!-- Lista de Inmuebles -->
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h3 class="mb-0">
            <i class="fa fa-home me-2"></i>
            Inmuebles del Proyecto
        </h3>
        <?php if (!empty($inmuebles)): ?>
            <small class="text-muted"><?= count($inmuebles) ?> inmueble(s) encontrado(s)</small>
        <?php endif; ?>
    </div>

    <?php if (!empty($inmuebles)): ?>
        <div class="row">
            <?php foreach ($inmuebles as $inmueble): ?>
                <div class="col-lg-4 col-md-6 mb-4">
                    <div class="card inmueble-card">
                        <div class="position-relative">
                            <?php if ($inmueble['primera_foto']): ?>
                                <img src="<?= base_url('uploads/' . $inmueble['primera_foto']['photo_path']) ?>" 
                                     alt="<?= esc($inmueble['name']) ?>" 
                                     class="inmueble-image">
                            <?php else: ?>
                                <div class="no-image">
                                    <i class="fa fa-image fa-3x mb-2"></i>
                                    <span>Sin imágenes</span>
                                </div>
                            <?php endif; ?>
                            
                            <!-- Badge de estado -->
                            <?php
                                $statusClass = match($inmueble['status']) {
                                    'Disponible' => 'bg-success',
                                    'Vendido' => 'bg-danger',
                                    'Apartado' => 'bg-warning text-dark',
                                    'Cancelado' => 'bg-secondary',
                                    default => 'bg-info'
                                };
                            ?>
                            <span class="badge <?= $statusClass ?> status-badge">
                                <?= esc($inmueble['status']) ?>
                            </span>

                            <!-- Indicador de fotos -->
                            <?php if ($inmueble['total_fotos'] > 0): ?>
                                <span class="photo-indicator">
                                    <i class="fa fa-camera me-1"></i>
                                    <?= $inmueble['total_fotos'] ?>
                                </span>
                            <?php endif; ?>
                        </div>

                        <div class="property-info">
                            <div class="property-title"><?= esc($inmueble['name']) ?></div>
                            <div class="property-key">
                                <i class="fa fa-key me-1"></i>
                                Clave: <?= esc($inmueble['clave']) ?>
                            </div>

                            <?php if (!empty($inmueble['description_general'])): ?>
                                <div class="property-description">
                                    <?= esc($inmueble['description_general']) ?>
                                </div>
                            <?php endif; ?>

                            <div class="property-details">
                                <span class="badge price-tag">
                                    $<?= number_format($inmueble['price'], 2) ?>
                                </span>
                                <a href="<?= base_url('admin/real_estates/edit/' . $inmueble['id']) ?>" 
                                   class="btn btn-outline-primary btn-sm">
                                    <i class="fa fa-eye me-1"></i>
                                    Ver Detalles
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php else: ?>
        <div class="text-center py-5">
            <div class="mb-3">
                <i class="fa fa-home fa-4x text-muted"></i>
            </div>
            <h4 class="text-muted">No hay inmuebles en este proyecto</h4>
            <p class="text-muted">Aún no se han agregado inmuebles a este proyecto.</p>
            <a href="<?= base_url('admin/real_estates/new') ?>" class="btn btn-primary">
                <i class="fa fa-plus me-2"></i>
                Agregar Primer Inmueble
            </a>
        </div>
    <?php endif; ?>
</div>
<?= $this->endSection() ?>