<?php

declare(strict_types=1);

use App\Core\Env;
use App\Core\Request;
use App\Core\Response;
use App\Core\Router;
use Smarty\Smarty;

// ── 1. Автозагрузка ──────────────────────────────────────────────────────────
require_once dirname(__DIR__) . '/vendor/autoload.php';

// ── 2. Переменные окружения ───────────────────────────────────────────────────
Env::load(dirname(__DIR__) . '/.env');

// ── 3. Конфигурация ───────────────────────────────────────────────────────────
$config   = require dirname(__DIR__) . '/config/app.php';
$dbConfig = require dirname(__DIR__) . '/config/database.php';

// ── 4. Сессия ─────────────────────────────────────────────────────────────────
ini_set('session.cookie_httponly', '1');
ini_set('session.cookie_samesite', 'Lax');
ini_set('session.use_strict_mode', '1');

session_name($config['session']['name']);
session_start();

// ── 5. HTTP-заголовки безопасности ────────────────────────────────────────────
Response::setSecurityHeaders($config['security']);

// ── 6. Режим отображения ошибок ───────────────────────────────────────────────
if ($config['debug']) {
    ini_set('display_errors', '1');
    error_reporting(E_ALL);
} else {
    ini_set('display_errors', '0');
    error_reporting(0);
}

// ── 7. Smarty ─────────────────────────────────────────────────────────────────
$smarty = new Smarty();
$smarty->setTemplateDir(dirname(__DIR__) . '/app/Views/');
$smarty->setCompileDir(dirname(__DIR__) . '/storage/compiled/');
$smarty->setCacheDir(dirname(__DIR__) . '/storage/cache/');
$smarty->setEscapeHtml(true);   // Автоэкранирование XSS — включено глобально

// Делаем Smarty доступным для роутера (обработчик 404)
// В реальном проекте это был бы DI, но по требованиям обходимся без него
$GLOBALS['smarty'] = $smarty;

// ── 8. Request / Router ───────────────────────────────────────────────────────
$request = new Request();
$router  = new Router();

require dirname(__DIR__) . '/routes/web.php';

// ── 9. Диспетчеризация ────────────────────────────────────────────────────────
$router->dispatch($request);