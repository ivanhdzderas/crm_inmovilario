<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */
$routes->get('/', 'Home::index');

// === CATÁLOGO PÚBLICO (URLs amigables) — deben estar registradas en producción ===
$routes->get('catalogo', 'Home::catalogo');
$routes->get('catalogo/(:segment)/(:segment)', 'Home::catalogoInmueble/$1/$2');
$routes->get('desarrollo/(:segment)/(:segment)', 'Home::fichaDesarrollo/$1/$2');
$routes->get('desarrollo/(:segment)', 'Home::desarrollo/$1');
$routes->get('ficha-tecnica/(:num)', 'Home::publicTechnicalSheet/$1');

// === CATÁLOGO FUNERARIO ===
$routes->get('funerario', 'FuneralCatalog::index');
$routes->get('funerario/lugar/(:segment)', 'FuneralCatalog::lugar/$1');
$routes->get('funerario/(:segment)/(:segment)/(:segment)', 'FuneralCatalog::producto/$1/$2/$3');

// Rutas de autenticación
$routes->get('register', 'AuthController::register');
$routes->post('register/store', 'AuthController::store');
$routes->get('login', 'AuthController::login');
$routes->post('login/auth', 'AuthController::authenticate');
$routes->get('forgot-password', 'AuthController::forgotPassword');
$routes->post('forgot-password', 'AuthController::processForgotPassword');
$routes->get('restablecer/(:segment)', 'AuthController::resetForm/$1');
$routes->post('restablecer/(:segment)', 'AuthController::resetPassword/$1');
$routes->get('dashboard', 'DashboardController::index', ['filter' => 'auth']);
$routes->get('logout', 'AuthController::logout');

// Rutas para el panel de administración
// Se agrega el namespace para simplificar las rutas internas
$routes->group('admin', ['namespace' => 'App\Controllers\Admin', 'filter' => 'auth:admin'], function ($routes) {
    // Rutas para inmuebles
    // Se quita el prefijo 'Admin\' porque ya está en el namespace del grupo
    $routes->get('real_estates', 'RealEstatesController::index');
    $routes->get('real_estates/new', 'RealEstatesController::new');
    $routes->post('real_estates/create', 'RealEstatesController::create');
    $routes->get('real_estates/edit/(:num)', 'RealEstatesController::edit/$1');
    $routes->post('real_estates/update/(:num)', 'RealEstatesController::update/$1');
    $routes->post('real_estates/delete/(:num)', 'RealEstatesController::delete/$1');
    
    // Ruta para la descarga del PDF (Nueva)
    $routes->get('real_estates/downloadTechnicalSheet/(:num)', 'RealEstatesController::downloadTechnicalSheet/$1');
    
    // Ruta para obtener prospectos que coinciden con un inmueble
    $routes->get('real_estates/getMatchingProspects/(:num)', 'RealEstatesController::getMatchingProspects/$1');

    // Rutas para la gestión de fotos de inmuebles
    $routes->post('real_estates/delete_photo/(:num)', 'RealEstatesController::delete_photo/$1');

    // Rutas para la gestión de documentos
    $routes->get('real_estates/documents/(:num)', 'DocumentsController::index/$1');
    $routes->post('real_estates/documents/upload', 'DocumentsController::upload');
    $routes->post('real_estates/documents/delete/(:num)', 'DocumentsController::delete/$1');

    // Rutas para usuarios
    $routes->get('users', '\\App\\Controllers\\Admin::users');
    $routes->get('users/edit/(:num)', '\\App\\Controllers\\Admin::edit/$1');
    $routes->post('users/update', '\\App\\Controllers\\Admin::update');
    $routes->post('users/delete/(:num)', '\\App\\Controllers\\Admin::delete/$1');

    // Rutas para la gestión de Proyectos Inmobiliarios
    $routes->group('proyectos', function($routes) { // El namespace ya es heredado del grupo 'admin'
        $routes->get('/', 'ProyectosInmobiliariosController::index');
        $routes->get('create', 'ProyectosInmobiliariosController::create');
        $routes->post('store', 'ProyectosInmobiliariosController::store');
        $routes->post('store-ajax', 'ProyectosInmobiliariosController::storeAjax');
        $routes->get('edit/(:num)', 'ProyectosInmobiliariosController::edit/$1');
        $routes->post('update/(:num)', 'ProyectosInmobiliariosController::update/$1');
        $routes->put('update/(:num)', 'ProyectosInmobiliariosController::update/$1');
        $routes->post('delete/(:num)', 'ProyectosInmobiliariosController::delete/$1');
        $routes->get('show/(:num)', 'ProyectosInmobiliariosController::show/$1');
    });

    // Rutas para la gestión de tipos de documentos (para inmuebles)
    $routes->group('document_types', function ($routes) {
        $routes->get('/', 'DocumentTypesController::index');
        $routes->get('create', 'DocumentTypesController::create');
        $routes->post('store', 'DocumentTypesController::store');
        $routes->get('edit/(:num)', 'DocumentTypesController::edit/$1');
        $routes->post('update/(:num)', 'DocumentTypesController::update/$1');
        $routes->post('delete/(:num)', 'DocumentTypesController::delete/$1');
    });

    // Las formas de pago se administran en client_document_catalog (no existe PaymentMethodsController)

    // Catálogo de motivos de cancelación
    $routes->get('cancellation-reasons', 'CancellationReasonsController::index');
    $routes->get('cancellation-reasons/create', 'CancellationReasonsController::create');
    $routes->post('cancellation-reasons/store', 'CancellationReasonsController::store');
    $routes->get('cancellation-reasons/edit/(:num)', 'CancellationReasonsController::edit/$1');
    $routes->post('cancellation-reasons/update/(:num)', 'CancellationReasonsController::update/$1');
    $routes->post('cancellation-reasons/delete/(:num)', 'CancellationReasonsController::delete/$1');

    // Catálogo de origen del cliente (redes sociales, referidos, etc.)
    $routes->get('acquisition-sources', 'AcquisitionSourcesController::index');

    // Reinicio operativo (URL oculta — requiere token en .env, solo admin)
    $routes->get('mantenimiento/reinicio/(:segment)', 'SystemResetController::index/$1');
    $routes->post('mantenimiento/reinicio/(:segment)/execute', 'SystemResetController::execute/$1');

    $routes->get('acquisition-sources/create', 'AcquisitionSourcesController::create');
    $routes->post('acquisition-sources/store', 'AcquisitionSourcesController::store');
    $routes->get('acquisition-sources/edit/(:num)', 'AcquisitionSourcesController::edit/$1');
    $routes->post('acquisition-sources/update/(:num)', 'AcquisitionSourcesController::update/$1');
    $routes->post('acquisition-sources/delete/(:num)', 'AcquisitionSourcesController::delete/$1');

    // Módulo funerario
    $routes->group('funeral-locations', static function ($routes) {
        $routes->get('/', 'FuneralLocationsController::index');
        $routes->get('create', 'FuneralLocationsController::create');
        $routes->post('store', 'FuneralLocationsController::store');
        $routes->get('edit/(:num)', 'FuneralLocationsController::edit/$1');
        $routes->post('update/(:num)', 'FuneralLocationsController::update/$1');
        $routes->post('delete/(:num)', 'FuneralLocationsController::delete/$1');
    });
    $routes->group('funeral-homes', static function ($routes) {
        $routes->get('/', 'FuneralHomesController::index');
        $routes->get('create', 'FuneralHomesController::create');
        $routes->post('store', 'FuneralHomesController::store');
        $routes->get('edit/(:num)', 'FuneralHomesController::edit/$1');
        $routes->post('update/(:num)', 'FuneralHomesController::update/$1');
        $routes->post('delete/(:num)', 'FuneralHomesController::delete/$1');
    });
    $routes->group('funeral-products', static function ($routes) {
        $routes->get('/', 'FuneralProductsController::index');
        $routes->get('create', 'FuneralProductsController::create');
        $routes->post('store', 'FuneralProductsController::store');
        $routes->get('edit/(:num)', 'FuneralProductsController::edit/$1');
        $routes->post('update/(:num)', 'FuneralProductsController::update/$1');
        $routes->post('delete/(:num)', 'FuneralProductsController::delete/$1');
        $routes->get('homes-by-location/(:num)', 'FuneralProductsController::homesByLocation/$1');
    });

    // Rutas para la gestión de zonas geográficas
    $routes->group('zones', function ($routes) { // El namespace ya es heredado
        // GET /admin/zones
        // Lista principal del catálogo de zonas
        $routes->get('/', 'ZonesController::index');
        
        // GET /admin/zones/create
        // Formulario para crear nueva zona
        $routes->get('create', 'ZonesController::create');
        
        // POST /admin/zones/store
        // Guardar nueva zona
        $routes->post('store', 'ZonesController::store');
        
        // GET /admin/zones/edit/[:num]
        // Formulario para editar zona existente
        $routes->get('edit/(:num)', 'ZonesController::edit/$1');
        
        // POST /admin/zones/update/[:num]
        // Actualizar zona existente
        $routes->post('update/(:num)', 'ZonesController::update/$1');
        
        // POST /admin/zones/delete/[:num]
        // Eliminar zona (AJAX)
        $routes->post('delete/(:num)', 'ZonesController::delete/$1');
        
        // GET /admin/zones/api/zones
        // API para obtener lista de zonas (usado en selects)
        $routes->get('api/zones', 'ZonesController::getZones');
        
        // GET /admin/zones/clients-count/[:num]
        // Obtener contador de clientes por zona
        $routes->get('clients-count/(:num)', 'ZonesController::getClientsCount/$1');
    });

    // Rutas para la gestión de catálogo de documentos por tipo de crédito
    $routes->group('client_document_catalog', function($routes) { // El namespace ya es heredado
        // GET /admin/client_document_catalog
        // Pantalla principal con tipos de crédito
        $routes->get('/', 'ClientDocumentCatalogController::index');
        
        // GET /admin/client_document_catalog/manage/[:any]
        // Gestionar documentos de un tipo de crédito específico
        $routes->get('manage/(:any)', 'ClientDocumentCatalogController::manageCreditType/$1');
        
        // POST /admin/client_document_catalog/add-credit-type
        // Agregar nuevo tipo de crédito
        $routes->post('add-credit-type', 'ClientDocumentCatalogController::addCreditType');
        
        // POST /admin/client_document_catalog/delete-credit-type
        // Eliminar tipo de crédito y sus documentos
        $routes->post('delete-credit-type', 'ClientDocumentCatalogController::deleteCreditType');
        
        // POST /admin/client_document_catalog/add-document
        // Agregar documento a un tipo de crédito
        $routes->post('add-document', 'ClientDocumentCatalogController::addDocument');
        
        // DELETE /admin/client_document_catalog/delete-document/[:num]
        // Eliminar un documento específico
        $routes->delete('delete-document/(:num)', 'ClientDocumentCatalogController::deleteDocument/$1');
        $routes->post('delete-document/(:num)', 'ClientDocumentCatalogController::deleteDocument/$1');
        
        // GET /admin/client_document_catalog/get-document/[:num]
        // Obtener datos de un documento para edición
        $routes->get('get-document/(:num)', 'ClientDocumentCatalogController::getDocument/$1');
        
        // POST /admin/client_document_catalog/update-document/[:num]
        // Actualizar un documento específico
        $routes->post('update-document/(:num)', 'ClientDocumentCatalogController::updateDocument/$1');
        
        // POST /admin/client_document_catalog/update-order
        // Actualizar orden de documentos
        $routes->post('update-order', 'ClientDocumentCatalogController::updateOrder');
    });

    // Rutas para la Configuración de Requisitos de Documentos
    $routes->match(['get', 'post'], 'config/document_requirement', 'ConfigController::documentRequirement');

    // Rutas para la Configuración General del Sistema
    $routes->get('settings', 'ConfigurationController::index');
    $routes->post('settings/update', 'ConfigurationController::update');
});

// ====================================================================
// RUTAS PARA GESTIÓN DE PROSPECTOS (Módulo CRM)
// Protegidas con el filtro 'auth' para usuarios logueados.
// ====================================================================

$routes->group('prospects', ['filter' => 'auth'], function ($routes) {
    // GET /prospects
    // Muestra la lista de prospectos (llama a Prospects::index)
    $routes->get('/', 'Prospects::index'); 

    // GET /prospects/create
    // Muestra el formulario para crear un nuevo prospecto (llama a Prospects::create)
    $routes->get('create', 'Prospects::create'); 

    // POST /prospects/store
    // Procesa y guarda un nuevo prospecto (llama a Prospects::store)
    $routes->post('store', 'Prospects::store'); 

    // GET /prospects/view/[:num]
    // Muestra el detalle del prospecto y el formulario de seguimiento (llama a Prospects::view con ID)
    $routes->get('view/(:num)', 'Prospects::view/$1');

    // GET /prospects/edit/[:num]
    // Muestra el formulario de edición (utilizado por el offcanvas)
    $routes->get('edit/(:num)', 'Prospects::edit/$1');

    // POST /prospects/update/[:num]
    // Procesa la actualización de un prospecto (soporta peticiones AJAX)
    $routes->post('update/(:num)', 'Prospects::update/$1');

    // POST /prospects/followup/[:num]
    // Procesa y guarda el registro de seguimiento/minuta y la agenda (llama a Prospects::saveFollowUp con ID)
    $routes->post('followup/(:num)', 'Prospects::saveFollowUp/$1');

    // POST /prospects/updateFollowUp/[:num]
    // Actualizar una minuta de seguimiento existente
    $routes->post('updateFollowUp/(:num)', 'Prospects::updateFollowUp/$1');

    // DELETE /prospects/deleteFollowUp/[:num]
    // Eliminar una minuta de seguimiento
    $routes->delete('deleteFollowUp/(:num)', 'Prospects::deleteFollowUp/$1');
    
    // POST /prospects/deactivate/[:num]
    // Descartar/Desactivar un prospecto con razón
    $routes->post('deactivate/(:num)', 'Prospects::deactivate/$1');

    // === RUTAS PARA GESTIÓN DE INMUEBLES DE INTERÉS ===
    // POST /prospects/addPropertyInterest/[:num]
    // Agregar interés en un inmueble específico
    $routes->post('addPropertyInterest/(:num)', 'Prospects::addPropertyInterest/$1');

    // POST /prospects/removePropertyInterest/[:num]/[:num]  
    // Quitar interés (client_id/interest_id)
    $routes->post('removePropertyInterest/(:num)/(:num)', 'Prospects::removePropertyInterest/$1/$2');

    // POST /prospects/registerApartado/[:num]/[:num]
    // Registrar apartado (client_id/interest_id)  
    $routes->post('registerApartado/(:num)/(:num)', 'Prospects::registerApartado/$1/$2');

    // POST /prospects/cancelApartado/[:num]/[:num]
    $routes->post('cancelApartado/(:num)/(:num)', 'Prospects::cancelApartado/$1/$2');

    // === INTERÉS FUNERARIO (usa catálogo existente) ===
    $routes->get('getFuneralProductPlans/(:num)', 'Prospects::getFuneralProductPlans/$1');
    $routes->post('addFuneralInterest/(:num)', 'Prospects::addFuneralInterest/$1');
    $routes->post('updateFuneralInterest/(:num)/(:num)', 'Prospects::updateFuneralInterest/$1/$2');
    $routes->post('removeFuneralInterest/(:num)/(:num)', 'Prospects::removeFuneralInterest/$1/$2');
    $routes->post('registerFuneralApartado/(:num)/(:num)', 'Prospects::registerFuneralApartado/$1/$2');
    $routes->post('cancelFuneralApartado/(:num)/(:num)', 'Prospects::cancelFuneralApartado/$1/$2');
    $routes->post('markFuneralSold/(:num)/(:num)', 'Prospects::markFuneralSold/$1/$2');
    
    // GET /prospects/getActiveClients
    // API para obtener clientes activos (para agenda)
    $routes->get('getActiveClients', 'Prospects::getActiveClients');
});

// === RUTAS DE HERRAMIENTAS ===
$routes->group('tools', ['filter' => 'auth'], function($routes) {
    $routes->get('isrCalculator', 'Tools::isrCalculator');
});

// === RUTAS DE REPORTES (ADMINISTRADOR) ===
$routes->group('admin/reports', ['filter' => 'auth', 'namespace' => 'App\Controllers\Admin'], function($routes) {
    $routes->get('prospects-follow-up', 'ReportsController::prospectsFollowUp');
    $routes->get('getProspectsFollowUpData', 'ReportsController::getProspectsFollowUpData');
    $routes->get('exportProspectsFollowUp', 'ReportsController::exportProspectsFollowUp');
    $routes->get('client-pipeline', 'ReportsController::clientPipeline');
    $routes->get('getClientPipelineData', 'ReportsController::getClientPipelineData');
    $routes->get('exportClientPipeline', 'ReportsController::exportClientPipeline');
    $routes->get('acquisition-sources', 'ReportsController::acquisitionSources');
    $routes->get('getAcquisitionSourcesData', 'ReportsController::getAcquisitionSourcesData');
});

// === RUTAS DE CHECKLIST DE DOCUMENTOS ===
$routes->group('client-document-checklist', ['filter' => 'auth', 'namespace' => 'App\\Controllers\\Admin'], function($routes) {
    // GET /client-document-checklist/view/[:num]
    // Ver checklist de documentos de una cuenta de cliente
    $routes->get('view/(:num)', 'ClientDocumentChecklistController::view/$1');
    
    // POST /client-document-checklist/update-status
    // Actualizar estado de un documento (AJAX)
    $routes->post('update-status', 'ClientDocumentChecklistController::updateStatus');
    
    // POST /client-document-checklist/upload-file
    // Subir archivo para un documento
    $routes->post('upload-file', 'ClientDocumentChecklistController::uploadFile');
    
    // GET /client-document-checklist/api-get-checklist/[:num]
    // API para obtener checklist (AJAX)
    $routes->get('api-get-checklist/(:num)', 'ClientDocumentChecklistController::apiGetChecklist/$1');
});

// === RUTAS DE AGENDA ===
$routes->group('agenda', ['filter' => 'auth'], function($routes) {
    // GET /agenda
    // Pantalla principal de la agenda
    $routes->get('/', 'Agenda::index');
    
    // GET /agenda/getActivities
    // API para obtener actividades (FullCalendar)
    $routes->get('getActivities', 'Agenda::getActivities');
    
    // POST /agenda/createActivity
    // Crear nueva actividad
    $routes->post('createActivity', 'Agenda::createActivity');
    
    // POST /agenda/moveActivity/[:num]
    // Mover/reprogramar una actividad
    $routes->post('moveActivity/(:num)', 'Agenda::moveActivity/$1');
    
    // POST /agenda/cancelActivity/[:num]
    // Cancelar una actividad
    $routes->post('cancelActivity/(:num)', 'Agenda::cancelActivity/$1');
    
    // POST /agenda/completeActivity/[:num]
    // Completar una actividad
    $routes->post('completeActivity/(:num)', 'Agenda::completeActivity/$1');
    
    // === RUTAS DE INTEGRACIÓN GOOGLE CALENDAR ===
    // GET /agenda/google/connect
    // Conectar con Google Calendar
    $routes->get('google/connect', 'Agenda::connectGoogle');
    
    // GET /agenda/google/callback
    // Callback de autorización OAuth de Google
    $routes->get('google/callback', 'Agenda::googleCallback');
    
    // POST /agenda/google/disconnect
    // Desconectar Google Calendar
    $routes->post('google/disconnect', 'Agenda::disconnectGoogle');
    
    // POST /agenda/google/sync
    // Sincronizar eventos con Google Calendar
    $routes->post('google/sync', 'Agenda::syncWithGoogle');
});

// === RUTAS LEGALES (PÚBLICAS) ===
$routes->get('politicas-de-privacidad', 'Legal::privacyPolicy');
$routes->get('terminos-y-condiciones', 'Legal::termsOfService');
$routes->get('terminos-condiciones-crm', 'Legal::crmTermsOfService');
$routes->get('politica-de-cookies', 'Legal::cookiePolicy');
$routes->get('condiciones-de-uso', 'Legal::conditionsOfUse');
$routes->get('nosotros', 'Home::aboutUs');