<?php

namespace Config;

// Create a new instance of our RouteCollection class.
$routes = Services::routes();

// Load the system's routing file first, so that the app and ENVIRONMENT
// can override as needed.
if (file_exists(SYSTEMPATH . 'Config/Routes.php'))
{
  require SYSTEMPATH . 'Config/Routes.php';
}

/**
 * --------------------------------------------------------------------
 * Router Setup
 * --------------------------------------------------------------------
 */
 
$routes->setDefaultNamespace('EMPORIKO\Controllers');
$routes->setDefaultController('Home');
$routes->setDefaultMethod('index');
$routes->setTranslateURIDashes(false);
$routes->set404Override();
$routes->setAutoRoute(TRUE);

/*
 * --------------------------------------------------------------------
 * Route Definitions
 * --------------------------------------------------------------------
 */

// We get a performance increase by specifying the default
// route since we don't have to scan directories.



$routes->add('/url(:any)','Connections::url/$1');

$routes->add('/portal/(:any).html', 'Pages::page/$1');

$routes->add('/pages/(:any).html', 'Pages::page/$1');

$routes->add('/products/download', 'Products::download');

//$routes->add('/pricing/(:any)/(:any)', 'Products::download_pricefile/$1');

$routes->add('/', 'Home::index');

$routes->add('/login','Users::loginform');

$routes->add('/logout', 'Home::logout');

$routes->add('/myaccount', 'Customers::profile');

$routes->set404Override('EMPORIKO\Controllers\Home::pageNotFound');

/*
 * --------------------------------------------------------------------
 * Additional Routing
 * --------------------------------------------------------------------
 *
 * There will often be times that you need additional routing and you
 * need it to be able to override any defaults in this file. Environment
 * based routes is one such time. require() additional route files here
 * to make that happen.
 *
 * You will have access to the $routes object within that file without
 * needing to reload it.
 */
if (file_exists(APPPATH . 'Config/' . ENVIRONMENT . '/Routes.php'))
{
  require APPPATH . 'Config/' . ENVIRONMENT . '/Routes.php';
}
