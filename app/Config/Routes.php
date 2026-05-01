<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */
//$routes->get('/', 'Home::index');
$routes->get('/', 'FormController::index');
$routes->add('form/procesar', 'FormController::procesar');
// Rutas para el módulo de datos
$routes->get('datos', 'Datos::index');
$routes->post('datos/toggleEmailStatus', 'Datos::toggleEmailStatus');
$routes->get('datos/exportCsv', 'Datos::exportCsv');