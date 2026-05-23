<?php

declare(strict_types=1);

use App\Core\Router;
use App\Controllers\HomeController;

/** @var Router $router */

$router->get('/',HomeController::class, 'index');