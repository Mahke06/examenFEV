<?php

use app\middlewares\SecurityHeadersMiddleware;
use flight\Engine;
use flight\net\Router;

/** 
 * @var Router $router 
 * @var Engine $app
 */

// Chargement des controllers
require_once __DIR__ . '/../controllers/DashboardController.php';
require_once __DIR__ . '/../controllers/BesoinController.php';
require_once __DIR__ . '/../controllers/DonController.php';
require_once __DIR__ . '/../controllers/VilleController.php';

// This wraps all routes in the group with the SecurityHeadersMiddleware
$router->group('', function(Router $router) use ($app) {

	// --- Dashboard ---
	$router->get('/', function() {
		$controller = new DashboardController();
		$controller->index();
	});

	// --- Besoins ---
	$router->get('/besoins', function() {
		$controller = new BesoinController();
		$controller->index();
	});

	$router->get('/besoins/create', function() {
		$controller = new BesoinController();
		$controller->create();
	});

	$router->post('/besoins/store', function() {
		$controller = new BesoinController();
		$controller->store();
	});

	// --- Dons ---
	$router->get('/dons', function() {
		$controller = new DonController();
		$controller->index();
	});

	$router->get('/dons/create', function() {
		$controller = new DonController();
		$controller->create();
	});

	$router->post('/dons/store', function() {
		$controller = new DonController();
		$controller->store();
	});

	// --- Villes ---
	$router->get('/villes', function() {
		$controller = new VilleController();
		$controller->index();
	});

}, [ SecurityHeadersMiddleware::class ]);