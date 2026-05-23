<?php

declare(strict_types=1);

use App\Core\Router;
use App\Controllers\HomeController;
use App\Controllers\CategoryController;
use App\Controllers\PostController;

/** @var Router $router */

$router->get('/', HomeController::class, 'index');
$router->get('/category/{id}', CategoryController::class, 'show');
$router->get('/post/{id}', PostController::class, 'show');