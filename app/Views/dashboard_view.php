<?= $this->extend('template') ?>

<?= $this->section('title') ?>
Dashboard
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="container-fluid py-4">
    <div class="row">
        <div class="col-12">
            <div class="card mb-4">
                <div class="card-header bg-primary text-white">
                    <?php $name = session()->get('full_name') ?: 'Usuario'; ?>
                    <h4 class="mb-0">¡Bienvenido, <?= esc($name) ?>!</h4>
                </div>
                <div class="card-body">
                    <p class="mb-0">Has iniciado sesión correctamente en la plataforma. Aquí podrás gestionar tus prospectos, agenda y más.</p>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Estadísticas Mensuales -->
        <div class="col-lg-12">
            <div class="card mb-4">
                <div class="card-header bg-warning text-white">
                    <h5 class="mb-0">
                        <i class="fa fa-chart-line me-2"></i>
                        Estadísticas del Mes - <?= date('F Y') ?>
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row text-center">
                        <div class="col-md-3">
                            <div class="card border-primary">
                                <div class="card-body">
                                    <h4 class="text-primary"><?= $monthlyStats['casas_traidas'] ?? 0 ?></h4>
                                    <p class="mb-0"><i class="fa fa-home me-1"></i> Casas Captadas</p>
                                    <small class="text-muted">Por ti este mes</small>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card border-success">
                                <div class="card-body">
                                    <h4 class="text-success"><?= $monthlyStats['inmuebles_vendidos'] ?? 0 ?></h4>
                                    <p class="mb-0"><i class="fa fa-handshake me-1"></i> Inmuebles Vendidos</p>
                                    <small class="text-muted">Total del mes</small>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card border-info">
                                <div class="card-body">
                                    <h4 class="text-info">$<?= number_format($monthlyStats['comisiones_venta'] ?? 0, 2) ?></h4>
                                    <p class="mb-0"><i class="fa fa-dollar-sign me-1"></i> Comisiones Venta</p>
                                    <small class="text-muted">Tus ventas</small>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card border-warning">
                                <div class="card-body">
                                    <h4 class="text-warning">$<?= number_format($monthlyStats['comisiones_captacion'] ?? 0, 2) ?></h4>
                                    <p class="mb-0"><i class="fa fa-search me-1"></i> Comisiones Captación</p>
                                    <small class="text-muted">Por traer casas</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <?php if (session()->get('role_id') == 1): // Solo para administradores ?>
    <div class="row">
        <!-- Estadísticas Generales del Mes -->
        <div class="col-lg-6">
            <div class="card">
                <div class="card-header bg-secondary text-white">
                    <h5 class="mb-0">
                        <i class="fa fa-building me-2"></i>
                        Estadísticas Generales
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row text-center">
                        <div class="col-6">
                            <h3 class="text-primary"><?= $generalStats['casas_traidas'] ?? 0 ?></h3>
                            <p>Total Casas Captadas</p>
                        </div>
                        <div class="col-6">
                            <h3 class="text-success"><?= $generalStats['inmuebles_vendidos'] ?? 0 ?></h3>
                            <p>Total Ventas</p>
                        </div>
                    </div>
                    <hr>
                    <div class="row text-center">
                        <div class="col-6">
                            <h4 class="text-info">$<?= number_format(($generalStats['comisiones_venta'] ?? 0), 2) ?></h4>
                            <p>Comisiones Ventas</p>
                        </div>
                        <div class="col-6">
                            <h4 class="text-warning">$<?= number_format(($generalStats['comisiones_captacion'] ?? 0), 2) ?></h4>
                            <p>Comisiones Captación</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Top Asesores del Mes -->
        <div class="col-lg-6">
            <div class="card">
                <div class="card-header bg-success text-white">
                    <h5 class="mb-0">
                        <i class="fa fa-trophy me-2"></i>
                        Top Asesores del Mes
                    </h5>
                </div>
                <div class="card-body p-0">
                    <?php if (!empty($topAgents)): ?>
                        <div class="table-responsive">
                            <table class="table table-sm mb-0">
                                <tbody>
                                    <?php foreach ($topAgents as $index => $agent): ?>
                                        <tr>
                                            <td>
                                                <span class="badge badge-<?= $index == 0 ? 'warning' : ($index == 1 ? 'secondary' : 'info') ?>">
                                                    #<?= $index + 1 ?>
                                                </span>
                                            </td>
                                            <td><?= esc($agent['full_name']) ?></td>
                                            <td class="text-right">
                                                <strong><?= $agent['casas_traidas'] ?> casas</strong>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <div class="text-center p-4">
                            <p class="text-muted">No hay datos de captación este mes</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <div class="row">
        <!-- Widget de Próximas Citas -->
        <div class="col-lg-6">
            <div class="card h-100">
                <div class="card-header bg-info text-white">
                    <h5 class="mb-0">
                        <i class="fa fa-calendar-alt me-2"></i>
                        Próximas Citas
                    </h5>
                </div>
                <div class="card-body p-0">
                    <?php if (!empty($upcomingActivities)): ?>
                        <?php foreach ($upcomingActivities as $activity): ?>
                            <div class="border-bottom p-3">
                                <div class="d-flex justify-content-between align-items-start">
                                    <div>
                                        <h6 class="mb-1"><?= esc($activity['title']) ?></h6>
                                        <?php if ($activity['client_name']): ?>
                                            <p class="text-muted mb-1">
                                                <i class="fa fa-user me-1"></i>
                                                <?= esc($activity['client_name']) ?>
                                            </p>
                                        <?php endif; ?>
                                        <small class="text-muted">
                                            <i class="fa fa-clock me-1"></i>
                                            <?= date('d/m/Y H:i', strtotime($activity['start_time'])) ?>
                                        </small>
                                    </div>
                                    <span class="badge bg-primary">
                                        <?= esc($activity['type']) ?>
                                    </span>
                                </div>
                                <?php if ($activity['description']): ?>
                                    <p class="small text-muted mb-0 mt-2"><?= esc($activity['description']) ?></p>
                                <?php endif; ?>
                            </div>
                        <?php endforeach; ?>
                        <div class="card-footer text-center">
                            <a href="<?= base_url('agenda') ?>" class="btn btn-outline-info btn-sm">
                                <i class="fa fa-calendar me-1"></i>
                                Ver Agenda Completa
                            </a>
                        </div>
                    <?php else: ?>
                        <div class="text-center p-4">
                            <i class="fa fa-calendar fa-3x text-muted mb-3"></i>
                            <p class="text-muted">No tienes citas programadas en los próximos días</p>
                            <a href="<?= base_url('agenda') ?>" class="btn btn-outline-info btn-sm">
                                <i class="fa fa-calendar me-1"></i>
                                Ir a Mi Agenda
                            </a>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Widget de Acciones Rápidas -->
        <div class="col-lg-6">
            <div class="card h-100">
                <div class="card-header bg-success text-white">
                    <h5 class="mb-0">
                        <i class="fa fa-bolt me-2"></i>
                        Acciones Rápidas
                    </h5>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-3">
                        <a href="<?= base_url('prospects/create') ?>" class="btn btn-outline-success">
                            <i class="fa fa-user-plus me-2"></i>
                            Nuevo Prospecto
                        </a>
                        <a href="<?= base_url('prospects') ?>" class="btn btn-outline-primary">
                            <i class="fa fa-users me-2"></i>
                            Ver Prospectos
                        </a>
                        <a href="<?= base_url('agenda') ?>" class="btn btn-outline-info">
                            <i class="fa fa-calendar-alt me-2"></i>
                            Mi Agenda
                        </a>
                        <?php if (session()->get('role_id') == 1): ?>
                            <hr>
                            <h6 class="text-muted mb-2">
                                <i class="fa fa-shield-alt me-1"></i>
                                Panel de Administración
                            </h6>
                            <a href="<?= base_url('admin/real_estates') ?>" class="btn btn-outline-warning mb-2">
                                <i class="fa fa-home me-2"></i>
                                Gestión de Inmuebles
                            </a>
                            <a href="<?= base_url('admin/zones') ?>" class="btn btn-outline-secondary">
                                <i class="fa fa-map-marker-alt me-2"></i>
                                Catálogo de Zonas
                                <span class="badge badge-warning ml-2">NUEVO</span>
                            </a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<?= $this->endSection() ?>