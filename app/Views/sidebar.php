<div class="sidebar">
    <!-- Header del Sidebar -->
    <div class="sidebar-header">
        <div class="logo-container">
            <div class="logo-icon">
                <i class="fa fa-cube"></i>
            </div>
            <p class="brand-tagline">Real Estate CRM</p>
        </div>
    </div>

    <!-- Información del Usuario -->
    <div class="user-info">
        <div class="user-avatar">
            <i class="fa fa-user-circle"></i>
        </div>
        <div class="user-details">
            <span class="user-name"><?= session()->get('full_name') ?: 'Usuario' ?></span>
            <span class="user-role"><?= session()->get('role_id') == 1 ? 'Administrador' : 'Asesor' ?></span>
        </div>
    </div>

    <!-- Navegación Principal -->
    <nav class="sidebar-nav">
        <div class="nav-section">
            <span class="nav-section-title">PRINCIPAL</span>
            <ul class="nav-links">
                <li class="nav-item">
                    <a href="<?= base_url('dashboard') ?>" class="nav-link">
                        <i class="fa fa-tachometer-alt nav-icon"></i>
                        <span class="nav-text">Dashboard</span>
                        <div class="nav-indicator"></div>
                    </a>
                </li>
                <li class="nav-item nav-dropdown">
                    <a href="#" class="nav-link nav-dropdown-toggle">
                        <i class="fa fa-users nav-icon"></i>
                        <span class="nav-text">Prospectos</span>
                        <i class="fa fa-chevron-down nav-arrow"></i>
                        <div class="nav-indicator"></div>
                    </a>
                    <ul class="nav-dropdown-menu">
                        <li class="nav-dropdown-item">
                            <a href="<?= base_url('prospects?type=comprador') ?>" class="nav-dropdown-link">
                                <i class="fa fa-shopping-cart nav-dropdown-icon"></i>
                                <span class="nav-dropdown-text">Compradores</span>
                            </a>
                        </li>
                        <li class="nav-dropdown-item">
                            <a href="<?= base_url('prospects?interest=funerario') ?>" class="nav-dropdown-link">
                                <i class="fa fa-monument nav-dropdown-icon"></i>
                                <span class="nav-dropdown-text">Comprador funerario</span>
                            </a>
                        </li>
                        <li class="nav-dropdown-item">
                            <a href="<?= base_url('prospects?type=vendedor') ?>" class="nav-dropdown-link">
                                <i class="fa fa-home nav-dropdown-icon"></i>
                                <span class="nav-dropdown-text">Propietarios</span>
                            </a>
                        </li>
                        <li class="nav-dropdown-item">
                            <a href="<?= base_url('prospects') ?>" class="nav-dropdown-link">
                                <i class="fa fa-list nav-dropdown-icon"></i>
                                <span class="nav-dropdown-text">Todos</span>
                            </a>
                        </li>
                    </ul>
                </li>
                <li class="nav-item">
                    <a href="<?= base_url('agenda') ?>" class="nav-link">
                        <i class="fa fa-calendar-alt nav-icon"></i>
                        <span class="nav-text">Mi Agenda</span>
                        <div class="nav-indicator"></div>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="<?= base_url('matching') ?>" class="nav-link">
                        <i class="fa fa-magic nav-icon"></i>
                        <span class="nav-text">¿Qué le ofrezco?</span>
                        <div class="nav-indicator"></div>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="<?= base_url('tramites') ?>" class="nav-link">
                        <i class="fa fa-route nav-icon"></i>
                        <span class="nav-text">Trámites</span>
                        <div class="nav-indicator"></div>
                    </a>
                </li>
            </ul>
        </div>

        <?php if (session()->get('role_id') == 1): ?>
        <div class="nav-section">
            <span class="nav-section-title">ADMINISTRACIÓN</span>
            <ul class="nav-links">
                <li class="nav-item">
                    <a href="<?= base_url('admin/users') ?>" class="nav-link">
                        <i class="fa fa-user-cog nav-icon"></i>
                        <span class="nav-text">Usuarios</span>
                        <div class="nav-indicator"></div>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="<?= base_url('admin/real_estates') ?>" class="nav-link">
                        <i class="fa fa-home nav-icon"></i>
                        <span class="nav-text">Inmuebles</span>
                        <div class="nav-indicator"></div>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="<?= base_url('admin/proyectos'); ?>" class="nav-link">
                        <i class="fa fa-building nav-icon"></i>
                        <span class="nav-text">Proyectos</span>
                        <div class="nav-indicator"></div>
                    </a>
                </li>
                <li class="nav-item nav-dropdown">
                    <a href="#" class="nav-link nav-dropdown-toggle">
                        <i class="fa fa-cross nav-icon"></i>
                        <span class="nav-text">Funerario</span>
                        <i class="fa fa-chevron-down nav-arrow"></i>
                        <div class="nav-indicator"></div>
                    </a>
                    <ul class="nav-dropdown-menu">
                        <li class="nav-dropdown-item">
                            <a href="<?= base_url('admin/funeral-locations') ?>" class="nav-dropdown-link">
                                <i class="fa fa-map nav-dropdown-icon"></i>
                                <span class="nav-dropdown-text">Lugares de descanso</span>
                            </a>
                        </li>
                        <li class="nav-dropdown-item">
                            <a href="<?= base_url('admin/funeral-homes') ?>" class="nav-dropdown-link">
                                <i class="fa fa-store nav-dropdown-icon"></i>
                                <span class="nav-dropdown-text">Funerarias</span>
                            </a>
                        </li>
                        <li class="nav-dropdown-item">
                            <a href="<?= base_url('admin/funeral-products') ?>" class="nav-dropdown-link">
                                <i class="fa fa-box nav-dropdown-icon"></i>
                                <span class="nav-dropdown-text">Paquetes funerarios</span>
                            </a>
                        </li>
                        <li class="nav-dropdown-item">
                            <a href="<?= base_url('funerario') ?>" class="nav-dropdown-link" target="_blank">
                                <i class="fa fa-external-link-alt nav-dropdown-icon"></i>
                                <span class="nav-dropdown-text">Ver catálogo público</span>
                            </a>
                        </li>
                    </ul>
                </li>
                <li class="nav-item">
                    <a href="<?= base_url('admin/zones'); ?>" class="nav-link">
                        <i class="fa fa-map-marker-alt nav-icon"></i>
                        <span class="nav-text">Catálogo de Zonas</span>
                        <div class="nav-indicator"></div>
                    </a>
                </li>
                <!-- Submenú de Documentos -->
                <li class="nav-item nav-dropdown">
                    <a href="#" class="nav-link nav-dropdown-toggle">
                        <i class="fa fa-file-alt nav-icon"></i>
                        <span class="nav-text">Documentos</span>
                        <i class="fa fa-chevron-down nav-arrow"></i>
                        <div class="nav-indicator"></div>
                    </a>
                    <ul class="nav-dropdown-menu">
                        <li class="nav-dropdown-item">
                            <a href="<?= base_url('admin/document_types') ?>" class="nav-dropdown-link">
                                <i class="fa fa-home nav-dropdown-icon"></i>
                                <span class="nav-dropdown-text">Docs. Inmuebles</span>
                            </a>
                        </li>
                        <li class="nav-dropdown-item">
                            <a href="<?= base_url('admin/client_document_catalog') ?>" class="nav-dropdown-link">
                                <i class="fa fa-clipboard-check nav-dropdown-icon"></i>
                                <span class="nav-dropdown-text">Catálogo por Crédito</span>
                            </a>
                        </li>
                    </ul>
                </li>
                <!-- Submenú de Configuración -->
                <li class="nav-item nav-dropdown">
                    <a href="#" class="nav-link nav-dropdown-toggle">
                        <i class="fa fa-cogs nav-icon"></i>
                        <span class="nav-text">Configuración</span>
                        <i class="fa fa-chevron-down nav-arrow"></i>
                        <div class="nav-indicator"></div>
                    </a>
                    <ul class="nav-dropdown-menu">
                        <li class="nav-dropdown-item">
                            <a href="<?= base_url('admin/settings') ?>" class="nav-dropdown-link">
                                <i class="fa fa-sliders-h nav-dropdown-icon"></i>
                                <span class="nav-dropdown-text">General</span>
                            </a>
                        </li>
                        <li class="nav-dropdown-item">
                            <a href="<?= base_url('admin/config/document_requirement') ?>" class="nav-dropdown-link">
                                <i class="fa fa-tasks nav-dropdown-icon"></i>
                                <span class="nav-dropdown-text">Requisitos Documentos</span>
                            </a>
                        </li>
                        <li class="nav-dropdown-item">
                            <a href="<?= base_url('admin/cancellation-reasons') ?>" class="nav-dropdown-link">
                                <i class="fa fa-ban nav-dropdown-icon"></i>
                                <span class="nav-dropdown-text">Motivos Cancelación</span>
                            </a>
                        </li>
                        <li class="nav-dropdown-item">
                            <a href="<?= base_url('admin/acquisition-sources') ?>" class="nav-dropdown-link">
                                <i class="fa fa-bullhorn nav-dropdown-icon"></i>
                                <span class="nav-dropdown-text">Origen del Cliente</span>
                            </a>
                        </li>
                    </ul>
                </li>
            </ul>
        </div>
        
        <div class="nav-section">
            <span class="nav-section-title">REPORTES</span>
            <ul class="nav-links">
                <li class="nav-item">
                    <a href="<?= base_url('admin/reports/prospects-follow-up') ?>" class="nav-link">
                        <i class="fa fa-chart-line nav-icon"></i>
                        <span class="nav-text">Seguimiento de Prospectos</span>
                        <div class="nav-indicator"></div>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="<?= base_url('admin/reports/client-pipeline') ?>" class="nav-link">
                        <i class="fa fa-users nav-icon"></i>
                        <span class="nav-text">Pipeline de Clientes</span>
                        <div class="nav-indicator"></div>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="<?= base_url('admin/reports/acquisition-sources') ?>" class="nav-link">
                        <i class="fa fa-bullhorn nav-icon"></i>
                        <span class="nav-text">Origen de Clientes</span>
                        <div class="nav-indicator"></div>
                    </a>
                </li>
            </ul>
        </div>

        <div class="nav-section">
            <span class="nav-section-title">HERRAMIENTAS</span>
            <ul class="nav-links">
                <li class="nav-item">
                    <a href="<?= base_url('tools/isrCalculator') ?>" class="nav-link">
                        <i class="fa fa-calculator nav-icon"></i>
                        <span class="nav-text">Calculadora ISR</span>
                        <div class="nav-indicator"></div>
                    </a>
                </li>
            </ul>
        </div>
        <?php endif; ?>
    </nav>

    <!-- Footer del Sidebar -->
    <div class="sidebar-footer">
        <a href="<?= base_url('logout') ?>" class="logout-btn">
            <i class="fa fa-sign-out-alt"></i>
            <span>Cerrar Sesión</span>
        </a>
    </div>
</div>