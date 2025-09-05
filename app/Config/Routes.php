<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */

$routes->get('login', 'AuthController::login');
$routes->post('login', 'AuthController::attemptLogin');
$routes->get('logout', 'AuthController::logout');

$routes->get('/', 'Cotizador::index');
$routes->post('cotizador/calcular', 'Cotizador::calcular');
$routes->post('cotizador/guardar', 'Cotizador::guardar');
$routes->get('cotizador/fechas-ocupadas', 'Cotizador::fechasOcupadas');

$routes->group('admin', ['filter' => 'auth'], static function ($routes) {
    // La ruta /admin cargará el Dashboard
    $routes->get('/', 'Admin\Dashboard::index');
    $routes->get('cotizaciones', 'Admin\Cotizaciones::index');
    $routes->get('cotizaciones/ver/(:num)', 'Admin\Cotizaciones::ver/$1');
    $routes->post('cotizaciones/actualizar-estado', 'Admin\Cotizaciones::actualizarEstado');
    $routes->post('cotizaciones/eliminar', 'Admin\Cotizaciones::eliminar');
    $routes->get('cotizaciones/editar/(:num)', 'Admin\Cotizaciones::editar/$1');
    $routes->post('cotizaciones/actualizar', 'Admin\Cotizaciones::actualizar');
    $routes->get('servicios', 'Admin\Servicios::index');
    $routes->get('servicios/crear', 'Admin\Servicios::crear');
    $routes->post('servicios/guardar', 'Admin\Servicios::guardar');
    $routes->post('servicios/eliminar', 'Admin\Servicios::eliminar');
    $routes->get('servicios/editar/(:num)', 'Admin\Servicios::editar/$1');
    $routes->post('servicios/actualizar', 'Admin\Servicios::actualizar');
    $routes->get('calendario', 'Admin\CalendarioController::index');
    $routes->get('calendario/eventos', 'Admin\CalendarioController::eventos');
});

$routes->group('api/v1', static function ($routes) {
    // ===================================================================
    // --- ZONA PÚBLICA / INVITADO ---
    // ===================================================================
    $routes->post('auth/login', 'Api\AuthController::login');
    $routes->post('auth/register', 'Api\AuthController::register');
    $routes->post('auth/refresh', 'Api\AuthController::refresh');

    $routes->resource('servicios', [
        'controller' => 'Api\ServiciosController',
        'only' => ['index', 'show']
    ]);

    // Rutas de cotizaciones para INVITADOS
    $routes->post('cotizaciones', 'Api\CotizacionesController::create');
    $routes->get('cotizaciones/(:num)', 'Api\CotizacionesController::show/$1');
    $routes->put('cotizaciones/(:num)', 'Api\CotizacionesController::update/$1');
    
    $routes->get('calendario/fechas-ocupadas', 'Api\CotizacionesController::fechasOcupadas');

    // ===================================================================
    // --- ZONA PROTEGIDA / ADMINISTRADOR ---
    // ===================================================================
    // Todas las rutas aquí dentro estarán prefijadas con 'admin/' y requerirán JWT
    $routes->group('admin', ['filter' => 'api-auth'], static function ($routes) {
        
        $routes->get('dashboard', 'Api\DashboardController::index');

        // Rutas de servicios para ADMINS (ej: POST /api/v1/admin/servicios)
        $routes->resource('servicios', [
            'controller' => 'Api\ServiciosController',
            'except' => ['index', 'show']
        ]);

        // Rutas de cotizaciones para ADMINS (ej: GET /api/v1/admin/cotizaciones/16)
        $routes->resource('cotizaciones', [
            'controller' => 'Api\CotizacionesController',
            // No excluimos nada, el admin tiene acceso a todo.
        ]);

        // Rutas de notificaciones para ADMINS
        $routes->resource('notifications', ['controller' => 'Api\NotificationsController']);

        // Rutas de calendario para ADMINS
        $routes->get('calendario/eventos', 'Api\CalendarioController::index');
    });
});
