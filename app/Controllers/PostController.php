<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Request;
use App\Models\PostModel;
use Smarty\Smarty;

final class PostController
{
    private Smarty    $smarty;
    private PostModel $postModel;

    public function __construct()
    {
        $this->smarty    = $GLOBALS['smarty'];
        $this->postModel = new PostModel();
    }

    /**
     * @param array<string, string> $params  Параметры из роутера: ['id' => '12']
     */
    public function show(Request $request, array $params): void
    {
        $postId = (int) ($params['id'] ?? 0);

        if ($postId <= 0) {
            $this->renderNotFound();
        }

        $post = $this->postModel->findById($postId);

        if ($post === null) {
            $this->renderNotFound();
        }

        // ── Счётчик просмотров ────────────────────────────────────────────────
        // Инкремент делаем сразу после того, как убедились, что статья
        // существует. Транзакция здесь не нужна: UPDATE атомарен сам по себе.
        // Если счётчик не обновится (например, MySQL упадёт) — это не критично
        // для бизнес-логики, поэтому не оборачиваем в try/catch специально.
        $this->postModel->incrementViews($postId);

        // После инкремента обновляем значение локально, чтобы
        // не делать повторный SELECT и показать актуальное число.
        $post['views_count'] = (int) $post['views_count'] + 1;

        // ── Категории статьи ──────────────────────────────────────────────────
        $categories = $this->postModel->getCategoriesForPost($postId);

        // Собираем id категорий для запроса похожих статей.
        // array_column — эффективнее foreach для простой выборки одного поля.
        $categoryIds = array_column($categories, 'id');
        $categoryIds = array_map('intval', $categoryIds);

        // ── Похожие статьи ────────────────────────────────────────────────────
        $relatedPosts = $this->postModel->getRelated($postId, $categoryIds);

        $this->smarty->assign('pageTitle',    $post['title']);
        $this->smarty->assign('post',         $post);
        $this->smarty->assign('categories',   $categories);
        $this->smarty->assign('relatedPosts', $relatedPosts);
        $this->smarty->display('pages/post.tpl');
    }

    private function renderNotFound(): never
    {
        http_response_code(404);
        $this->smarty->display('pages/error.tpl');
        exit;
    }
}