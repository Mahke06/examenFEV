<?php

use app\controllers\ApiExampleController;
use app\controllers\BesoinController;
use app\controllers\DashboardController;
use app\controllers\DonController;
use app\middlewares\SecurityHeadersMiddleware;
use flight\Engine;
use flight\net\Router;

/** * @var Router $router 
 * @var Engine $app
 */

// Ce groupe enveloppe toutes les routes avec le middleware de sécurité
$router->group('', function(Router $router) use ($app) {

    // --- ROUTE PRINCIPALE (Dashboard) ---
    $router->get('/', [ DashboardController::class, 'index' ]);

    // --- ROUTES BESOINS ---
    $router->get('/besoins', [ BesoinController::class, 'index' ]);
    $router->post('/besoins', [ BesoinController::class, 'store' ]);

    // --- ROUTES DONS ---
    $router->get('/dons', [ DonController::class, 'index' ]);
    $router->post('/dons', [ DonController::class, 'store' ]);

    // --- AUTRES ROUTES (Exemples existants) ---
    $router->get('/route-iray', function() use ($app) {
        echo '<h1>Route Iray ve!</h1>';
    });

    $router->get('/hello-world/@name', function($name) {
        echo '<h1>Hello world! Oh hey '.$name.'!</h1>';
    });

    // Groupe API (inchangé)
    $router->group('/api', function() use ($router) {
        $router->get('/users', [ ApiExampleController::class, 'getUsers' ]);
        $router->get('/users/@id:[0-9]', [ ApiExampleController::class, 'getUser' ]);
        $router->post('/users/@id:[0-9]', [ ApiExampleController::class, 'updateUser' ]);
    });
    
}, [ SecurityHeadersMiddleware::class ]);