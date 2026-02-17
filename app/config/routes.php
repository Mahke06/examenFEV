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
require_once __DIR__ . '/../controllers/AchatController.php';
require_once __DIR__ . '/../controllers/SimulationController.php';
require_once __DIR__ . '/../controllers/RecapitulationController.php';

$router->group('', function(Router $router) use ($app) {

	$router->get('/', function() {
		$controller = new DashboardController();
		$controller->index();
	});

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

	$router->post('/besoins/delete', function() {
		$controller = new BesoinController();
		$controller->delete();
	});

	$router->post('/besoins/reinitialiser', function() {
		$controller = new BesoinController();
		$controller->reinitialiser();
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

	$router->get('/dons/create-plus-petit', function() {
		$controller = new DonController();
		$controller->createPlusPetit();
	});

	$router->post('/dons/store', function() {
		$controller = new DonController();
		$controller->store();
	});

	$router->post('/dons/store-plus-petit', function() {
		$controller = new DonController();
		$controller->storePlusPetit();
	});

	$router->get('/dons/create-proportionnel', function() {
		$controller = new DonController();
		$controller->createProportionnel();
	});

	$router->post('/dons/store-proportionnel', function() {
		$controller = new DonController();
		$controller->storeProportionnel();
	});

	$router->post('/dons/delete', function() {
		$controller = new DonController();
		$controller->delete();
	});

	// --- Villes ---
	$router->get('/villes', function() {
		$controller = new VilleController();
		$controller->index();
	});

	// --- Achats ---
	$router->get('/achats', function() {
		$controller = new AchatController();
		$controller->index();
	});

	$router->get('/achats/create', function() {
		$controller = new AchatController();
		$controller->create();
	});

	$router->post('/achats/store', function() {
		$controller = new AchatController();
		$controller->store();
	});

	$router->post('/achats/delete', function() {
		$controller = new AchatController();
		$controller->delete();
	});

	$router->get('/simulation', function() {
		$controller = new SimulationController();
		$controller->index();
	});

	$router->get('/simulation/simuler', function() {
		$controller = new SimulationController();
		$controller->simuler();
	});

	$router->post('/simulation/valider', function() {
		$controller = new SimulationController();
		$controller->valider();
	});

	$router->get('/recapitulation', function() {
		$controller = new RecapitulationController();
		$controller->index();
	});

	$router->get('/recapitulation/api', function() {
		$controller = new RecapitulationController();
		$controller->api();
	});

}, [ SecurityHeadersMiddleware::class ]);