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
    
    // Aquí puedes añadir más rutas de admin en el futuro, por ejemplo:
    // $routes->get('cotizaciones', 'Admin\Cotizaciones::index');
    // $routes->get('cotizaciones/ver/(:num)', 'Admin\Cotizaciones::show/$1');
});

$routes->group('api/v1', static function ($routes) {
    // Rutas de autenticación
    $routes->post('auth/login', 'Api\AuthController::login');
    $routes->post('auth/register', 'Api\AuthController::register');

    $routes->get('test', static function () {
        return service('response')->setJSON(['status' => 'success', 'message' => 'POST de prueba recibido!']);
    });

    $routes->get('servicios', 'Api\ServiciosController::index');
    // GET para obtener las fechas no disponibles del calendario
    $routes->get('calendario/fechas-ocupadas', 'Api\CotizacionesController::fechasOcupadas');

    // POST para crear/guardar una nueva cotización
    $routes->post('cotizaciones', 'Api\CotizacionesController::guardar');

    // Aquí irán tus futuras rutas protegidas por token
    // Ejemplo:
    // $routes->get('cotizaciones', 'Api\CotizacionesController::index', ['filter' => 'auth-api']);
});
