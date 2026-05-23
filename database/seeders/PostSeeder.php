<?php

declare(strict_types=1);

namespace Database\Seeders;

use Faker\Factory;
use Faker\Generator;

final class PostSeeder extends BaseSeeder
{
    private const POSTS_COUNT         = 100;
    private const MAX_CATEGORIES_PER_POST = 3;
    private const MIN_CATEGORIES_PER_POST = 1;

    // Реалистичные изображения — используем picsum.photos с фиксированными
    // размерами. Каждый id даёт уникальное фото.
    private const IMAGE_BASE_URL = 'https://picsum.photos/id';
    private const IMAGE_IDS      = [
        10, 20, 30, 40, 50, 60, 70, 80, 90, 100,
        110, 120, 130, 140, 150, 160, 170, 180, 190, 200,
    ];

    public function run(): void
    {
        $faker = Factory::create('ru_RU');

        // Получаем все id категорий из БД — не хардкодим,
        // чтобы не зависеть от порядка вставки CategorySeeder.
        $categoryIds = $this->pdo
            ->query('SELECT id FROM categories')
            ->fetchAll(\PDO::FETCH_COLUMN);

        if (empty($categoryIds)) {
            $this->log('No categories found — run CategorySeeder first.');
            return;
        }

        $categoryIds = array_map('intval', $categoryIds);

        $postStmt = $this->pdo->prepare(
            'INSERT INTO posts
                (image, title, description, content, views_count, published_at, created_at, updated_at)
             VALUES
                (:image, :title, :description, :content, :views_count, :published_at, :created_at, :updated_at)'
        );

        $postCategoryStmt = $this->pdo->prepare(
            'INSERT INTO post_category (post_id, category_id) VALUES (:post_id, :category_id)'
        );

        for ($i = 1; $i <= self::POSTS_COUNT; $i++) {
            // ── Даты ─────────────────────────────────────────────────────────
            // Разбрасываем статьи по последним 2 годам — реалистичная история.
            $createdAt   = $faker->dateTimeBetween('-2 years', 'now');
            // published_at — чуть позже created_at (от 0 до 3 дней)
            $publishedAt = (clone $createdAt)->modify(
                '+' . random_int(0, 72) . ' hours'
            );
            // Примерно 10% статей — черновики (published_at = NULL)
            $isPublished = random_int(1, 10) > 1;

            // ── Изображение ───────────────────────────────────────────────────
            // Каждый пост с вероятностью 80% имеет изображение
            $imageId = self::IMAGE_IDS[array_rand(self::IMAGE_IDS)];
            $image   = random_int(1, 10) <= 8
                ? self::IMAGE_BASE_URL . "/{$imageId}/800/450"
                : null;

            // ── Контент ───────────────────────────────────────────────────────
            // Генерируем HTML-контент — реалистичнее plain text.
            $content = $this->generateContent($faker);

            $postStmt->execute([
                'image'        => $image,
                'title'        => $faker->sentence(nbWords: random_int(5, 10)),
                'description'  => $faker->sentences(
                    nb: random_int(1, 3),
                    asText: true
                ),
                'content'      => $content,
                'views_count'  => random_int(0, 10000),
                'published_at' => $isPublished
                    ? $publishedAt->format('Y-m-d H:i:s')
                    : null,
                'created_at'   => $createdAt->format('Y-m-d H:i:s'),
                'updated_at'   => $createdAt->format('Y-m-d H:i:s'),
            ]);

            $postId = (int) $this->pdo->lastInsertId();

            // ── Категории поста ───────────────────────────────────────────────
            // Выбираем случайное подмножество категорий (1–3 штуки).
            // array_rand возвращает ключи, shuffle + array_slice — чище.
            $shuffled   = $categoryIds;
            shuffle($shuffled);
            $count      = random_int(
                self::MIN_CATEGORIES_PER_POST,
                min(self::MAX_CATEGORIES_PER_POST, count($shuffled))
            );
            $assigned   = array_slice($shuffled, 0, $count);

            foreach ($assigned as $categoryId) {
                $postCategoryStmt->execute([
                    'post_id'     => $postId,
                    'category_id' => $categoryId,
                ]);
            }

            $this->log(
                sprintf(
                    '  Post %3d/%d created (id=%d, categories=%s)',
                    $i,
                    self::POSTS_COUNT,
                    $postId,
                    implode(',', $assigned)
                )
            );
        }

        $this->log('PostSeeder done — ' . self::POSTS_COUNT . ' posts.');
    }

    /**
     * Генерирует HTML-контент статьи из нескольких параграфов,
     * заголовков и списков — имитирует реальную статью.
     */
    private function generateContent(Generator $faker): string
    {
        $blocks = [];

        // Вступительный параграф
        $blocks[] = '<p>' . $faker->paragraph(nbSentences: random_int(4, 7)) . '</p>';

        // 3–5 случайных блоков
        $blockCount = random_int(3, 5);

        for ($i = 0; $i < $blockCount; $i++) {
            $type = random_int(1, 3);

            if ($type === 1) {
                // Заголовок + параграф
                $blocks[] = '<h2>' . $faker->sentence(nbWords: random_int(3, 6)) . '</h2>';
                $blocks[] = '<p>' . $faker->paragraph(nbSentences: random_int(3, 6)) . '</p>';
            } elseif ($type === 2) {
                // Просто параграф
                $blocks[] = '<p>' . $faker->paragraph(nbSentences: random_int(3, 8)) . '</p>';
            } else {
                // Маркированный список
                $items      = $faker->sentences(nb: random_int(3, 6));
                $listItems  = array_map(
                    static fn(string $s): string => "<li>{$s}</li>",
                    $items
                );
                $blocks[]   = '<ul>' . implode('', $listItems) . '</ul>';
            }
        }

        // Заключительный параграф
        $blocks[] = '<p>' . $faker->paragraph(nbSentences: random_int(3, 5)) . '</p>';

        return implode("\n", $blocks);
    }
}