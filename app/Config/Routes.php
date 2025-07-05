<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */
$routes->get('/', 'Home::index');

$routes->get('/clientes', 'Clientes::index');

$routes->post('api/catering/estimate', 'CateringBudget::estimate');

$routes->get('admin/cotizaciones', 'Cotizaciones::index');

$routes->get('admin/cotizaciones/(:num)', 'Cotizaciones::detalle/$1');

$routes->post('admin/cotizaciones/cambiar-estado', 'Cotizaciones::cambiarEstado');

$routes->get('admin/dashboard', 'Dashboard::index');

$routes->get('admin/dashboard/exportar', 'Dashboard::exportarExcel');

$routes->get('admin/dashboard/pdf', 'Dashboard::exportarPDF');

$routes->get('cotizacion', 'Cotizacion::index');

$routes->get('cotizador', 'Cotizador::index');

$routes->post('cotizador/calcular', 'Cotizador::calcular');


