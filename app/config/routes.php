<?php

Flight::route('GET /', function() {
    require_once __DIR__ . '/../controllers/DashboardController.php';
    $controller = new DashboardController();
    $controller->index();
});

Flight::route('GET /besoins', function() {
    require_once __DIR__ . '/../controllers/BesoinController.php';
    $controller = new BesoinController();
    $controller->index();
});

Flight::route('GET /besoins/create', function() {
    require_once __DIR__ . '/../controllers/BesoinController.php';
    $controller = new BesoinController();
    $controller->create();
});

Flight::route('POST /besoins/store', function() {
    require_once __DIR__ . '/../controllers/BesoinController.php';
    $controller = new BesoinController();
    $controller->store();
});

Flight::route('GET /dons', function() {
    require_once __DIR__ . '/../controllers/DonController.php';
    $controller = new DonController();
    $controller->index();
});

Flight::route('GET /dons/create', function() {
    require_once __DIR__ . '/../controllers/DonController.php';
    $controller = new DonController();
    $controller->create();
});

Flight::route('POST /dons/store', function() {
    require_once __DIR__ . '/../controllers/DonController.php';
    $controller = new DonController();
    $controller->store();
});

Flight::route('GET /villes', function() {
    require_once __DIR__ . '/../controllers/VilleController.php';
    $controller = new VilleController();
    $controller->index();
});

?>