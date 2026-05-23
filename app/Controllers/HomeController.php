<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Request;
use Smarty\Smarty;

final class HomeController
{
    private Smarty $smarty;

    public function __construct()
    {
        $this->smarty = $GLOBALS['smarty'];
    }

    public function index(Request $request, array $params): void
    {
        // Временная заглушка — данные добавим в следующем этапе
        $this->smarty->assign('pageTitle', 'Главная');
        $this->smarty->assign('categories', []);
        $this->smarty->display('pages/home.tpl');
    }
}