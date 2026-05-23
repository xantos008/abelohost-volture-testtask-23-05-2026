<?php

declare(strict_types=1);

namespace App\Models;

/**
 * Модель статей.
 *
 * Содержит все запросы, связанные с постами:
 * - главная страница
 * - страница категории (со страницами и сортировкой)
 * - страница статьи
 * - похожие статьи
 * - счётчик просмотров
 */
final class PostModel extends BaseModel
{
    private const POSTS_PER_PAGE        = 10;
    private const LATEST_PER_CATEGORY   = 3;
    private const RELATED_POSTS_COUNT   = 3;

    // ── Главная страница ──────────────────────────────────────────────────────

    /**
     * Возвращает последние N опубликованных статей для одной категории.
     *
     * Вызывается в цикле по категориям на главной.
     * N задаётся константой LATEST_PER_CATEGORY.
     *
     * @return list<array<string, mixed>>
     */
    public function getLatestByCategory(int $categoryId): array
    {
        $sql = <<<SQL
            SELECT
                p.id,
                p.title,
                p.description,
                p.image,
                p.views_count,
                p.published_at
            FROM posts p
            INNER JOIN post_category pc ON pc.post_id = p.id
            WHERE pc.category_id = :category_id
              AND p.published_at IS NOT NULL
              AND p.published_at <= NOW()
            ORDER BY p.published_at DESC
            LIMIT :limit
        SQL;

        return $this->db->fetchAll($sql, [
            'category_id' => $categoryId,
            'limit'       => self::LATEST_PER_CATEGORY,
        ]);
    }

    // ── Страница категории ────────────────────────────────────────────────────

    /**
     * Разрешённые поля сортировки.
     *
     * Whitelist защищает от SQL-инъекции через ORDER BY.
     * Никакой пользовательский ввод не попадает в SQL напрямую.
     */
    private const SORT_WHITELIST = [
        'date'  => 'p.published_at DESC',
        'views' => 'p.views_count DESC',
    ];

    private const DEFAULT_SORT = 'date';

    /**
     * Возвращает статьи категории с пагинацией и сортировкой.
     *
     * @return list<array<string, mixed>>
     */
    public function getByCategoryPaginated(
        int    $categoryId,
        int    $page,
        string $sort
    ): array {
        $orderBy = self::SORT_WHITELIST[$sort] ?? self::SORT_WHITELIST[self::DEFAULT_SORT];
        $offset  = ($page - 1) * self::POSTS_PER_PAGE;

        // ORDER BY подставляется из whitelist — это не конкатенация
        // пользовательского ввода, а выбор из фиксированного набора.
        $sql = <<<SQL
            SELECT
                p.id,
                p.title,
                p.description,
                p.image,
                p.views_count,
                p.published_at
            FROM posts p
            INNER JOIN post_category pc ON pc.post_id = p.id
            WHERE pc.category_id = :category_id
              AND p.published_at IS NOT NULL
              AND p.published_at <= NOW()
            ORDER BY {$orderBy}
            LIMIT :limit OFFSET :offset
        SQL;

        return $this->db->fetchAll($sql, [
            'category_id' => $categoryId,
            'limit'       => self::POSTS_PER_PAGE,
            'offset'      => $offset,
        ]);
    }

    /**
     * Количество опубликованных статей в категории.
     * Нужно для расчёта числа страниц пагинации.
     */
    public function countByCategory(int $categoryId): int
    {
        $sql = <<<SQL
            SELECT COUNT(*) as cnt
            FROM posts p
            INNER JOIN post_category pc ON pc.post_id = p.id
            WHERE pc.category_id = :category_id
              AND p.published_at IS NOT NULL
              AND p.published_at <= NOW()
        SQL;

        $row = $this->db->fetchOne($sql, ['category_id' => $categoryId]);
        return (int) ($row['cnt'] ?? 0);
    }

    /**
     * Возвращает количество страниц пагинации.
     */
    public function getTotalPages(int $categoryId): int
    {
        $total = $this->countByCategory($categoryId);
        return (int) ceil($total / self::POSTS_PER_PAGE);
    }

    // ── Страница статьи ───────────────────────────────────────────────────────

    /**
     * Возвращает полную статью по id или null.
     *
     * @return array<string, mixed>|null
     */
    public function findById(int $id): ?array
    {
        $sql = <<<SQL
            SELECT
                p.id,
                p.title,
                p.description,
                p.content,
                p.image,
                p.views_count,
                p.published_at
            FROM posts p
            WHERE p.id = :id
              AND p.published_at IS NOT NULL
              AND p.published_at <= NOW()
        SQL;

        return $this->db->fetchOne($sql, ['id' => $id]);
    }

    /**
     * Возвращает все категории, к которым относится статья.
     *
     * @return list<array<string, mixed>>
     */
    public function getCategoriesForPost(int $postId): array
    {
        $sql = <<<SQL
            SELECT
                c.id,
                c.name
            FROM categories c
            INNER JOIN post_category pc ON pc.category_id = c.id
            WHERE pc.post_id = :post_id
            ORDER BY c.name ASC
        SQL;

        return $this->db->fetchAll($sql, ['post_id' => $postId]);
    }

    /**
     * Атомарно увеличивает счётчик просмотров.
     *
     * Используем UPDATE с инкрементом на стороне БД —
     * это атомарно и не требует транзакции:
     * нет race condition между SELECT и UPDATE.
     */
    public function incrementViews(int $postId): void
    {
        $sql = <<<SQL
            UPDATE posts
            SET views_count = views_count + 1
            WHERE id = :id
        SQL;

        $this->db->execute($sql, ['id' => $postId]);
    }

    // ── Похожие статьи ────────────────────────────────────────────────────────

    /**
     * Возвращает похожие статьи:
     *   1. Из тех же категорий, что и текущая статья.
     *   2. Исключает текущую статью.
     *   3. Сортирует по дате публикации DESC.
     *   4. Без дублей (DISTINCT).
     *
     * @param list<int> $categoryIds  id категорий текущей статьи
     * @return list<array<string, mixed>>
     */
    public function getRelated(int $postId, array $categoryIds): array
    {
        if (empty($categoryIds)) {
            return [];
        }

        // IN (:ids) нельзя передать одним параметром в PDO.
        // Строим placeholders вручную: ?,?,?
        // Сами значения — int'ы, SQL-инъекция невозможна.
        $placeholders = implode(',', array_fill(0, count($categoryIds), '?'));

        $sql = <<<SQL
            SELECT DISTINCT
                p.id,
                p.title,
                p.description,
                p.image,
                p.views_count,
                p.published_at
            FROM posts p
            INNER JOIN post_category pc ON pc.post_id = p.id
            WHERE pc.category_id IN ({$placeholders})
              AND p.id != ?
              AND p.published_at IS NOT NULL
              AND p.published_at <= NOW()
            ORDER BY p.published_at DESC
            LIMIT ?
        SQL;

        // Позиционные биндинги: сначала id категорий, затем текущий post_id, затем limit
        $bindings = [...$categoryIds, $postId, self::RELATED_POSTS_COUNT];

        return $this->db->fetchAll($sql, $bindings);
    }

    // ── Геттеры констант (для шаблонов/контроллеров) ─────────────────────────

    public function getPostsPerPage(): int
    {
        return self::POSTS_PER_PAGE;
    }

    public function getSortWhitelist(): array
    {
        return self::SORT_WHITELIST;
    }

    public function getDefaultSort(): string
    {
        return self::DEFAULT_SORT;
    }
}