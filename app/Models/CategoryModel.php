<?php

declare(strict_types=1);

namespace App\Models;

/**
 * Модель категорий.
 *
 * Отвечает только за данные категорий.
 * Посты внутри категории — задача PostModel.
 */
final class CategoryModel extends BaseModel
{
    /**
     * Возвращает все категории, в которых есть хотя бы одна
     * опубликованная статья.
     *
     * Используется на главной странице.
     *
     * @return list<array<string, mixed>>
     */
    public function getActiveCategories(): array
    {
        // EXISTS эффективнее JOIN + GROUP BY здесь:
        // не тащим строки постов, просто проверяем наличие.
        $sql = <<<SQL
            SELECT
                c.id,
                c.name,
                c.description
            FROM categories c
            WHERE EXISTS (
                SELECT 1
                FROM post_category pc
                INNER JOIN posts p ON p.id = pc.post_id
                WHERE pc.category_id = c.id
                  AND p.published_at IS NOT NULL
                  AND p.published_at <= NOW()
            )
            ORDER BY c.name ASC
        SQL;

        return $this->db->fetchAll($sql);
    }

    /**
     * Возвращает одну категорию по id или null.
     *
     * @return array<string, mixed>|null
     */
    public function findById(int $id): ?array
    {
        $sql = <<<SQL
            SELECT id, name, description
            FROM categories
            WHERE id = :id
        SQL;

        return $this->db->fetchOne($sql, ['id' => $id]);
    }
}