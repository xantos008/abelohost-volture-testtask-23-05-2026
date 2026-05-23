<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Request;
use App\Models\CategoryModel;
use App\Models\PostModel;
use Smarty\Smarty;

final class HomeController
{
    private Smarty        $smarty;
    private CategoryModel $categoryModel;
    private PostModel     $postModel;

    public function __construct()
    {
        $this->smarty        = $GLOBALS['smarty'];
        $this->categoryModel = new CategoryModel();
        $this->postModel     = new PostModel();
    }

    public function index(Request $request, array $params): void
    {
        // Получаем категории, в которых есть опубликованные статьи
        $categories = $this->categoryModel->getActiveCategories();

        // Для каждой категории подгружаем последние 3 статьи.
        // N+1 здесь осознан: на главной максимум ~10-15 категорий,
        // итого ~10-15 лёгких запросов. Это проще и понятнее,
        // чем один сложный запрос с оконными функциями ROW_NUMBER().
        // При необходимости легко заменить на один запрос.
        foreach ($categories as &$category) {
            $category['posts'] = $this->postModel->getLatestByCategory(
                (int) $category['id']
            );
        }
        unset($category); // разрываем ссылку после foreach

        $this->smarty->assign('pageTitle', 'Главная');
        $this->smarty->assign('categories', $categories);
        $this->smarty->display('pages/home.tpl');
    }
}