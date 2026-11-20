<?php
use CodeIgniter\Router\RouteCollection;

/** @var RouteCollection $routes */
$routes->setAutoRoute(false);
$routes->get('login', 'AuthController::login');
$routes->post('login', 'AuthController::authenticate');
$routes->get('setup', 'SetupController::index');
$routes->post('setup', 'SetupController::create');
$routes->group('', ['filter' => 'auth'], static function (RouteCollection $routes): void {
    $routes->get('/', 'DashboardController::index');
    $routes->post('logout', 'AuthController::logout');
    foreach (['products' => 'ProductController', 'customers' => 'CustomerController', 'staff' => 'StaffController'] as $path => $controller) {
        $routes->get($path, $controller . '::index');
        $routes->get($path . '/new', $controller . '::new');
        $routes->post($path, $controller . '::create');
        $routes->get($path . '/(:num)/edit', $controller . '::edit/$1');
        $routes->post($path . '/(:num)', $controller . '::update/$1');
        $routes->post($path . '/(:num)/delete', $controller . '::delete/$1');
    }
    $routes->get('sales', 'SaleController::index');
    $routes->get('sales/new', 'SaleController::new');
    $routes->post('sales', 'SaleController::create');
    $routes->get('media/(:segment)/(:segment)', 'MediaController::show/$1/$2');
});
