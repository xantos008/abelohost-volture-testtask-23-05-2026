<?php

declare(strict_types=1);

use App\Core\Router;
use App\Controllers\HomeController;
use App\Controllers\CategoryController;

/** @var Router $router */

$router->get('/', HomeController::class, 'index');
$router->get('/category/{id}', CategoryController::class, 'show');