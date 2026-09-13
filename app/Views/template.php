<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=5.0, user-scalable=yes">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="mobile-web-app-capable" content="yes">
    <title><?= $this->renderSection('title') ?></title>
    
    <!-- 1. JQUERY (MOVIDO AQUÍ POR SOLICITUD) -->
    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    
    <!-- 2. POPPER.JS (MOVIDO AQUÍ POR SOLICITUD) -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.16.1/umd/popper.min.js"></script>
    
    <!-- 3. Bootstrap 4 CSS (EXISTENTE) -->
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    
    <!-- 4. Font Awesome CSS (NUEVO: Para los iconos fa) -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" xintegrity="sha512-SnH5WK+bZxgPHs44uWIX+LLMDJ8gHw3/RjE1A8qFz6/d3Z9R1z8V1T1KjP+6t0y5iO9x5d5tT8u8e8v5k5k5k5k5g==" crossorigin="anonymous" referrerpolicy="no-referrer" />

    <!-- 5. DataTables CSS con Responsive (Versión para Bootstrap 4) -->
    <link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/v/bs4/dt-1.13.6/r-2.5.0/datatables.min.css"/>

    <!-- 6. SweetAlert2 CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">

    <!-- Estilos de Sidebar (Tus estilos existentes) -->
    <style>
        /* Estilos básicos de la página */
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            background-color: #f0f2f5;
            color: #333;
        }

        /* SweetAlert2 personalización */
        .swal-wide {
            width: 600px !important;
        }
        
        .swal2-popup {
            border-radius: 10px !important;
        }
        
        .swal2-title {
            font-size: 1.5rem !important;
        }
        
        .swal2-html-container {
            font-size: 1rem !important;
        }

        /* Contenedor principal para el layout */
        .main-container {
            display: flex;
            min-height: 100vh;
        }

        /* Estilos del Sidebar Moderno */
        .sidebar {
            width: 280px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            padding: 0;
            box-shadow: 4px 0 20px rgba(0,0,0,0.1);
            display: flex;
            flex-direction: column;
            position: relative;
            overflow: hidden;
        }
        
        .sidebar::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100"><defs><pattern id="grain" width="100" height="100" patternUnits="userSpaceOnUse"><circle cx="50" cy="50" r="1" fill="%23ffffff" opacity="0.1"/></pattern></defs><rect width="100" height="100" fill="url(%23grain)"/></svg>') repeat;
            opacity: 0.1;
            z-index: 1;
        }
        
        .sidebar > * {
            position: relative;
            z-index: 2;
        }
        
        /* Header del Sidebar */
        .sidebar-header {
            padding: 25px 20px;
            border-bottom: 1px solid rgba(255,255,255,0.1);
        }
        
        .logo-container {
            text-align: center;
            color: white;
        }
        
        .logo-icon {
            width: 50px;
            height: 50px;
            background: rgba(255,255,255,0.2);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 15px;
            font-size: 24px;
            backdrop-filter: blur(10px);
        }
        
        .brand-name {
            font-size: 1.8rem;
            font-weight: 700;
            margin: 0;
            letter-spacing: -0.5px;
        }
        
        .brand-tagline {
            font-size: 0.75rem;
            opacity: 0.8;
            margin: 5px 0 0;
            text-transform: uppercase;
            letter-spacing: 1px;
        }
        
        /* Información del Usuario */
        .user-info {
            padding: 20px;
            background: rgba(255,255,255,0.1);
            display: flex;
            align-items: center;
            gap: 12px;
            backdrop-filter: blur(10px);
        }
        
        .user-avatar {
            width: 40px;
            height: 40px;
            background: rgba(255,255,255,0.2);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 20px;
        }
        
        .user-details {
            display: flex;
            flex-direction: column;
        }
        
        .user-name {
            color: white;
            font-weight: 600;
            font-size: 0.9rem;
        }
        
        .user-role {
            color: rgba(255,255,255,0.7);
            font-size: 0.75rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        
        /* Navegación */
        .sidebar-nav {
            flex: 1;
            padding: 20px 0;
            overflow-y: auto;
        }
        
        .nav-section {
            margin-bottom: 25px;
        }
        
        .nav-section-title {
            color: rgba(255,255,255,0.6);
            font-size: 0.7rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 1px;
            padding: 0 25px;
            margin-bottom: 10px;
            display: block;
        }
        
        .nav-links {
            list-style: none;
            padding: 0;
            margin: 0;
        }
        
        .nav-item {
            margin: 2px 15px;
        }
        
        .nav-link {
            display: flex;
            align-items: center;
            padding: 12px 15px;
            color: rgba(255,255,255,0.9);
            text-decoration: none;
            border-radius: 10px;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            position: relative;
            overflow: hidden;
        }
        
        .nav-link::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255,255,255,0.2), transparent);
            transition: left 0.5s;
        }
        
        .nav-indicator {
            position: absolute;
            right: 0;
            top: 50%;
            transform: translateY(-50%);
            width: 3px;
            height: 0;
            background: #ffd700;
            border-radius: 2px;
            transition: height 0.3s ease;
        }
        
        .nav-link:hover {
            background: rgba(255,255,255,0.15);
            transform: translateX(5px);
            color: white;
            text-decoration: none;
        }
        
        .nav-link:hover::before {
            left: 100%;
        }
        
        .nav-link:hover .nav-indicator {
            height: 60%;
        }
        
        .nav-link.active .nav-indicator {
            height: 80%;
            background: #ffd700;
            box-shadow: 0 0 10px rgba(255, 215, 0, 0.5);
        }
        
        .nav-icon {
            width: 20px;
            text-align: center;
            margin-right: 12px;
            font-size: 16px;
        }
        
        .nav-text {
            flex: 1;
            font-size: 0.9rem;
            font-weight: 500;
        }
        
        .nav-badge {
            background: rgba(255,255,255,0.2);
            color: white;
            padding: 2px 8px;
            border-radius: 12px;
            font-size: 0.7rem;
            font-weight: 600;
            text-transform: uppercase;
        }
        
        /* Footer del Sidebar */
        .sidebar-footer {
            padding: 20px;
            border-top: 1px solid rgba(255,255,255,0.1);
        }
        
        .logout-btn {
            display: flex;
            align-items: center;
            padding: 12px 15px;
            background: rgba(255,255,255,0.1);
            color: white;
            text-decoration: none;
            border-radius: 10px;
            transition: all 0.3s ease;
            backdrop-filter: blur(10px);
        }
        
        .logout-btn:hover {
            background: rgba(220, 53, 69, 0.8);
            transform: translateY(-2px);
            text-decoration: none;
            color: white;
        }
        
        .logout-btn i {
            margin-right: 10px;
            font-size: 16px;
        }
        
        /* Responsive */
        @media (max-width: 768px) {
            .sidebar {
                width: 280px;
                transform: translateX(-100%);
                position: fixed;
                top: 0;
                left: 0;
                height: 100vh;
                z-index: 1000;
                transition: transform 0.3s ease;
            }
            
            .sidebar.active {
                transform: translateX(0);
            }
            
            .main-container {
                flex-direction: column;
            }
            
            .content-area {
                padding: 20px;
            }
            
            /* Botón de menú móvil */
            .mobile-menu-btn {
                display: block;
                position: fixed;
                top: 20px;
                left: 20px;
                z-index: 1001;
                background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
                color: white;
                border: none;
                border-radius: 10px;
                padding: 12px;
                font-size: 18px;
                cursor: pointer;
                box-shadow: 0 4px 12px rgba(0,0,0,0.15);
            }
        }
        
        @media (min-width: 769px) {
            .mobile-menu-btn {
                display: none;
            }
            
            .sidebar {
                position: static !important;
                transform: translateX(0) !important;
                display: flex !important;
                width: 280px !important;
                z-index: auto !important;
            }
            
            .main-container {
                display: flex !important;
                flex-direction: row !important;
            }
            
            .content-area {
                flex: 1 !important;
                min-height: 100vh !important;
                padding: 30px !important;
            }
        }
        
        /* Estado activo del menú */
        .nav-link.active {
            background: rgba(255,255,255,0.2);
            color: white;
            font-weight: 600;
        }
        
        .nav-link.active .nav-icon {
            color: #ffd700;
        }
        
        /* Estilos del área de contenido principal */
        .content-area {
            flex-grow: 1;
            padding: 30px;
            background: #f8f9fa;
            min-height: 100vh;
        }
        
        .card {
            background-color: #fff;
            padding: 30px;
            border-radius: 12px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
            width: 100%;
            border: 1px solid rgba(0,0,0,0.05);
        }
        .text-center { text-align: center; }
        .table { width: 100%; border-collapse: collapse; }
        .table th, .table td { padding: 12px; text-align: left; border-bottom: 1px solid #ddd; }
        .btn:not([class*="outline"]):not(.btn-light):not(.btn-secondary):not(.btn-warning) { 
            padding: 8px 16px; 
            border-radius: 4px; 
            text-decoration: none; 
            color: #fff; 
        }
        .btn-light {
            color: #212529 !important;
            background-color: #f8f9fa !important;
            border-color: #f8f9fa !important;
        }
        .btn-light:hover {
            color: #212529 !important;
            background-color: #e2e6ea !important;
        }
        /* Asegurar que los botones outline mantengan sus colores correctos */
        .btn-outline-primary { color: #007bff !important; }
        .btn-outline-primary:hover { color: #fff !important; }
        .btn-outline-success { color: #28a745 !important; }
        .btn-outline-success:hover { color: #fff !important; }
        .btn-outline-info { color: #17a2b8 !important; }
        .btn-outline-info:hover { color: #fff !important; }
        .btn-outline-warning { color: #ffc107 !important; }
        .btn-outline-warning:hover { color: #212529 !important; }
        .btn-primary { background-color: #007bff; }
        .btn-danger { background-color: #dc3545; }
        .btn-warning { background-color: #ffc107; }

        /* Dropdown Navigation Styles */
        .nav-dropdown {
            position: relative;
        }

        .nav-dropdown-toggle {
            display: flex;
            align-items: center;
            justify-content: space-between;
            width: 100%;
        }

        .nav-arrow {
            font-size: 0.75rem;
            transition: transform 0.3s ease;
            margin-left: auto;
            margin-right: 10px;
        }

        .nav-dropdown.open .nav-arrow {
            transform: rotate(180deg);
        }

        .nav-dropdown-menu {
            max-height: 0;
            overflow: hidden;
            transition: max-height 0.3s ease, opacity 0.3s ease;
            opacity: 0;
            background: rgba(255,255,255,0.05);
            margin: 5px 0;
            border-radius: 8px;
            list-style: none;
            padding: 0;
            backdrop-filter: blur(10px);
        }

        .nav-dropdown.open .nav-dropdown-menu {
            max-height: 200px;
            opacity: 1;
        }

        .nav-dropdown-item {
            margin: 0;
        }

        .nav-dropdown-link {
            display: flex;
            align-items: center;
            padding: 8px 20px 8px 45px;
            color: rgba(255,255,255,0.8);
            text-decoration: none;
            font-size: 0.85rem;
            border-radius: 6px;
            margin: 2px 8px;
            transition: all 0.3s ease;
        }

        .nav-dropdown-link:hover {
            background: rgba(255,255,255,0.1);
            color: white;
            transform: translateX(3px);
            text-decoration: none;
        }

        .nav-dropdown-icon {
            font-size: 0.8rem;
            width: 16px;
            text-align: center;
            margin-right: 8px;
            opacity: 0.7;
        }

        .nav-dropdown-text {
            font-weight: 400;
        }

        /* Estado activo para dropdown links */
        .nav-dropdown-link.active {
            background: rgba(255,255,255,0.15);
            color: white;
            font-weight: 600;
        }

        .nav-dropdown-link.active .nav-dropdown-icon {
            color: #ffd700;
        }

        /* Mejoras para DataTables Responsive */
        table.dataTable.dtr-inline.collapsed > tbody > tr > td.dtr-control:before,
        table.dataTable.dtr-inline.collapsed > tbody > tr > th.dtr-control:before {
            content: '+' !important;
            background-color: transparent !important;
            border: 1px solid #6c757d !important;
            color: #6c757d !important;
            font-weight: bold !important;
            box-shadow: none !important;
        }
        
        table.dataTable.dtr-inline.collapsed > tbody > tr.parent > td.dtr-control:before,
        table.dataTable.dtr-inline.collapsed > tbody > tr.parent > th.dtr-control:before {
            content: '-' !important;
            background-color: transparent !important;
            border: 1px solid #6c757d !important;
            color: #6c757d !important;
            font-weight: bold !important;
            box-shadow: none !important;
        }
        
        /* Mejorar apariencia de filas expandidas en móvil */
        table.dataTable > tbody > tr.child ul.dtr-details {
            display: block;
            margin: 0;
            padding: 0;
        }
        
        table.dataTable > tbody > tr.child ul.dtr-details > li {
            border-bottom: 1px solid #e9ecef;
            padding: 0.5rem 0;
        }
        
        table.dataTable > tbody > tr.child span.dtr-title {
            font-weight: bold;
            color: #495057;
            display: inline-block;
            min-width: 120px;
        }
        
        /* Responsive: optimización para móviles */
        @media screen and (max-width: 768px) {
            /* Prevenir scroll horizontal */
            .table-responsive {
                overflow-x: visible !important;
                -webkit-overflow-scrolling: touch;
            }
            
            /* Ajustar tablas para móvil */
            table.dataTable {
                width: 100% !important;
                max-width: 100%;
            }
            
            /* Hacer botones más grandes y táctiles */
            table.dataTable tbody td .btn {
                font-size: 12px;
                padding: 4px 8px;
                margin: 2px 0;
                display: inline-block;
                white-space: nowrap;
            }
            
            /* Apilar botones en columna si son muchos */
            table.dataTable tbody td .btn + .btn {
                margin-left: 2px;
            }
            
            /* Asegurar que el wrapper de DataTables no cause overflow */
            div.dataTables_wrapper {
                width: 100%;
                overflow-x: hidden;
            }
            
            /* Controles de DataTable responsive para móvil */
            div.dataTables_wrapper .row {
                margin-left: 0;
                margin-right: 0;
            }
            
            div.dataTables_wrapper .row > div {
                padding-left: 8px;
                padding-right: 8px;
            }
            
            div.dataTables_wrapper div.dataTables_info,
            div.dataTables_wrapper div.dataTables_paginate {
                text-align: center;
                margin-top: 1rem;
            }
            
            /* Mejorar legibilidad del botón de expansión */
            table.dataTable.dtr-inline.collapsed > tbody > tr > td.dtr-control:before,
            table.dataTable.dtr-inline.collapsed > tbody > tr > th.dtr-control:before {
                width: 20px;
                height: 20px;
                line-height: 20px;
                font-size: 16px;
                text-align: center;
            }
            
            /* Hacer columnas de acciones más compactas */
            table.dataTable tbody td:last-child {
                min-width: 80px !important;
                width: 80px !important;
                white-space: nowrap;
            }
            
            /* Asegurar que botones de acción sean visibles */
            table.dataTable.dtr-inline.collapsed tbody td:last-child,
            table.dataTable.dtr-inline.collapsed tbody th:last-child {
                display: table-cell !important;
            }
        }
        
        /* Extra small devices (phones, menos de 576px) */
        @media screen and (max-width: 576px) {
            /* Reducir padding de cards */
            .card-body {
                padding: 0.75rem;
            }
            
            /* Hacer botones de acción más pequeños */
            table.dataTable tbody td .btn-sm {
                font-size: 11px;
                padding: 3px 6px;
            }
            
            /* Iconos en lugar de texto cuando sea posible */
            .btn .fa + span {
                display: none;
            }
            
            /* Reducir tamaño de fuente en tablas */
            table.dataTable {
                font-size: 13px;
            }
        }

        <?= $this->renderSection('styles') ?>
    </style>
</head>
<body>
    <!-- Botón de menú móvil -->
    <button class="mobile-menu-btn">
        <i class="fa fa-bars"></i>
    </button>
    
    <div class="main-container">
        <?= $this->include('sidebar') ?>
        <div class="content-area">
            <?= $this->renderSection('content') ?>
        </div>
    </div>

    <!-- SCRIPTS QUE AÚN NO SE PUEDEN MOVER AL HEAD -->
    
    <!-- 3. Bootstrap 4 JS (Debe ir después de Popper) -->
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
    
    <!-- 4. DataTables JS con Responsive (Debe ir después de jQuery) -->
    <script type="text/javascript" src="https://cdn.datatables.net/v/bs4/dt-1.13.6/r-2.5.0/datatables.min.js"></script>

    <!-- 5. SweetAlert2 JS -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <!-- JavaScript para el sidebar -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Manejo del menú móvil
            const sidebar = document.querySelector('.sidebar');
            const mobileMenuBtn = document.querySelector('.mobile-menu-btn');
            
            if (mobileMenuBtn) {
                mobileMenuBtn.addEventListener('click', function() {
                    sidebar.classList.toggle('active');
                });
            }
            
            // Cerrar menú al hacer clic fuera en móvil
            document.addEventListener('click', function(e) {
                if (window.innerWidth <= 768) {
                    if (!sidebar.contains(e.target) && !mobileMenuBtn.contains(e.target)) {
                        sidebar.classList.remove('active');
                    }
                }
            });
            
            // Marcar enlace activo
            const currentUrl = new URL(window.location.href);
            const currentPath = currentUrl.pathname.replace(/\/$/, '') || '/';
            const currentSearch = currentUrl.search;
            const normalizePath = (path) => path.replace(/\/$/, '') || '/';
            const navLinks = document.querySelectorAll('.nav-link');
            const dropdownLinks = document.querySelectorAll('.nav-dropdown-link');

            navLinks.forEach(link => {
                if (link.classList.contains('nav-dropdown-toggle')) {
                    return;
                }

                const linkUrl = new URL(link.href, window.location.origin);
                const linkPath = normalizePath(linkUrl.pathname);
                const isExactMatch = currentPath === linkPath && currentSearch === linkUrl.search;

                if (isExactMatch) {
                    link.classList.add('active');
                }
            });
            
            // Efecto de pulso en hover
            navLinks.forEach(link => {
                link.addEventListener('mouseenter', function() {
                    if (!this.classList.contains('nav-dropdown-toggle')) {
                        this.style.transform = 'translateX(8px) scale(1.02)';
                    }
                });
                
                link.addEventListener('mouseleave', function() {
                    if (!this.classList.contains('nav-dropdown-toggle')) {
                        this.style.transform = 'translateX(0) scale(1)';
                    }
                });
            });

            // Manejo de dropdowns
            const dropdownToggles = document.querySelectorAll('.nav-dropdown-toggle');
            
            dropdownToggles.forEach(toggle => {
                toggle.addEventListener('click', function(e) {
                    e.preventDefault();
                    
                    const dropdown = this.closest('.nav-dropdown');
                    const isOpen = dropdown.classList.contains('open');
                    
                    // Cerrar todos los dropdowns
                    document.querySelectorAll('.nav-dropdown').forEach(dd => {
                        dd.classList.remove('open');
                    });
                    
                    // Abrir el dropdown actual si estaba cerrado
                    if (!isOpen) {
                        dropdown.classList.add('open');
                    }
                });
            });

            dropdownLinks.forEach(link => {
                const linkUrl = new URL(link.href, window.location.origin);
                const linkPath = normalizePath(linkUrl.pathname);
                const isExactMatch = currentPath === linkPath && currentSearch === linkUrl.search;
                const isPathOnlyMatch = currentPath === linkPath && !linkUrl.search;

                if (isExactMatch || isPathOnlyMatch) {
                    link.classList.add('active');

                    // También abrir el dropdown padre
                    const dropdown = link.closest('.nav-dropdown');
                    if (dropdown) {
                        dropdown.classList.add('open');

                        const toggle = dropdown.querySelector('.nav-dropdown-toggle');
                        if (toggle) {
                            toggle.classList.add('active');
                        }
                    }
                }
            });
        });
    </script>

    <!-- Funciones globales de SweetAlert2 -->
    <script>
        // Función para confirmación de eliminación de usuarios
        function confirmDeleteUser(url, userName) {
            Swal.fire({
                title: '¿Eliminar Usuario?',
                text: `Se eliminará permanentemente el usuario "${userName}"`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#dc3545',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Sí, eliminar',
                cancelButtonText: 'Cancelar'
            }).then((result) => {
                if (result.isConfirmed) {
                    window.location.href = url;
                }
            });
        }

        // Función para confirmación de eliminación de inmuebles
        function confirmDeleteRealEstate(url, clave) {
            Swal.fire({
                title: '¿Eliminar Inmueble?',
                text: `Se eliminará permanentemente el inmueble "${clave}"`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#dc3545',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Sí, eliminar',
                cancelButtonText: 'Cancelar'
            }).then((result) => {
                if (result.isConfirmed) {
                    window.location.href = url;
                }
            });
        }

        // Función para confirmación de eliminación de fotos
        function confirmDeletePhoto(url, description) {
            Swal.fire({
                title: '¿Eliminar Foto?',
                text: `Se eliminará permanentemente la foto "${description}"`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#dc3545',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Sí, eliminar',
                cancelButtonText: 'Cancelar'
            }).then((result) => {
                if (result.isConfirmed) {
                    window.location.href = url;
                }
            });
        }

        // Función para confirmación de eliminación de documentos
        function confirmDeleteDocument(url, name) {
            Swal.fire({
                title: '¿Eliminar Documento?',
                text: `Se eliminará permanentemente el documento "${name}"`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#dc3545',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Sí, eliminar',
                cancelButtonText: 'Cancelar'
            }).then((result) => {
                if (result.isConfirmed) {
                    window.location.href = url;
                }
            });
        }

        // Función para confirmación de eliminación de proyectos
        function confirmDeleteProject(url, name) {
            Swal.fire({
                title: '¿Eliminar Proyecto?',
                text: `Se eliminará permanentemente el proyecto "${name}"`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#dc3545',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Sí, eliminar',
                cancelButtonText: 'Cancelar'
            }).then((result) => {
                if (result.isConfirmed) {
                    window.location.href = url;
                }
            });
        }

        // Función para confirmación de eliminación de tipos de documento
        function confirmDeleteDocumentType(url, name) {
            Swal.fire({
                title: '¿Eliminar Tipo de Documento?',
                text: `Se eliminará permanentemente el tipo de documento "${name}"`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#dc3545',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Sí, eliminar',
                cancelButtonText: 'Cancelar'
            }).then((result) => {
                if (result.isConfirmed) {
                    window.location.href = url;
                }
            });
        }
    </script>

    <!-- Footer con enlaces legales -->
    <footer class="bg-light py-3 mt-5 border-top">
        <div class="container-fluid">
            <div class="row align-items-center">
                <div class="col-md-6">
                    <p class="mb-0 text-muted">
                        <small>
                            © <?= date('Y') ?> Pacific Inmobiliaria. Todos los derechos reservados.<br>
                            <i class="fas fa-code me-1"></i>Creado por <strong>innovate</strong>
                        </small>
                    </p>
                </div>
                <div class="col-md-6 text-md-end">
                    <small class="text-muted">
                        <a href="<?= base_url('politicas-de-privacidad') ?>" target="_blank" class="text-decoration-none me-3">
                            <i class="fas fa-shield-alt me-1"></i>Políticas de Privacidad
                        </a>
                        <a href="<?= base_url('terminos-condiciones-crm') ?>" target="_blank" class="text-decoration-none me-3">
                            <i class="fas fa-file-contract me-1"></i>Términos y Condiciones
                        </a>
                        <a href="<?= base_url('condiciones-de-uso') ?>" target="_blank" class="text-decoration-none me-3">
                            <i class="fas fa-tasks me-1"></i>Condiciones de Uso
                        </a>
                        <a href="<?= base_url('politica-de-cookies') ?>" target="_blank" class="text-decoration-none">
                            <i class="fas fa-cookie-bite me-1"></i>Cookies
                        </a>
                    </small>
                </div>
            </div>
        </div>
    </footer>

    <!-- 6. Scripts de la vista específica (ej. la inicialización en index.php) -->
    <?= $this->renderSection('scripts') ?>

</body>
</html>