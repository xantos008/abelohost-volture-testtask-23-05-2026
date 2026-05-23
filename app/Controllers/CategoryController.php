<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Request;
use App\Core\Response;
use App\Models\CategoryModel;
use App\Models\PostModel;
use Smarty\Smarty;

final class CategoryController
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

    /**
     * @param array<string, string> $params  Параметры из роутера: ['id' => '5']
     */
    public function show(Request $request, array $params): void
    {
        $categoryId = (int) ($params['id'] ?? 0);

        if ($categoryId <= 0) {
            $this->renderNotFound();
        }

        $category = $this->categoryModel->findById($categoryId);

        if ($category === null) {
            $this->renderNotFound();
        }

        // ── Сортировка ────────────────────────────────────────────────────────
        // Принимаем только значения из whitelist модели.
        // Всё остальное молча заменяем на дефолт.
        // Пользовательская строка НИКОГДА не попадает в SQL напрямую.
        $sortParam   = $request->getString('sort', $this->postModel->getDefaultSort());
        $whitelist   = $this->postModel->getSortWhitelist();
        $sortParam   = array_key_exists($sortParam, $whitelist)
            ? $sortParam
            : $this->postModel->getDefaultSort();

        // ── Пагинация ─────────────────────────────────────────────────────────
        $currentPage = max(1, $request->getInt('page', 1));
        $totalPages  = $this->postModel->getTotalPages($categoryId);

        // Если запрошена страница за пределами диапазона — редиректим на первую
        if ($currentPage > $totalPages && $totalPages > 0) {
            Response::redirect("/category/{$categoryId}?sort={$sortParam}&page=1");
        }

        $posts = $this->postModel->getByCategoryPaginated(
            $categoryId,
            $currentPage,
            $sortParam,
        );

        $this->smarty->assign('pageTitle', $category['name']);
        $this->smarty->assign('category',    $category);
        $this->smarty->assign('posts',       $posts);
        $this->smarty->assign('sort',        $sortParam);
        $this->smarty->assign('currentPage', $currentPage);
        $this->smarty->assign('totalPages',  $totalPages);
        $this->smarty->display('pages/category.tpl');
    }

    /**
     * Выводит 404 и останавливает выполнение.
     * never — PHP 8.1 return type для функций, которые не возвращаются.
     */
    private function renderNotFound(): never
    {
        http_response_code(404);
        $this->smarty->display('pages/error.tpl');
        exit;
    }
}