<?php

declare(strict_types=1);

namespace Database\Seeders;

use Faker\Factory;

final class CategorySeeder extends BaseSeeder
{
    // Фиксированный список категорий — реалистичнее, чем случайные слова.
    // Faker здесь используется только для описаний.
    private const CATEGORIES = [
        'Технологии',
        'Наука',
        'Здоровье и медицина',
        'Бизнес и финансы',
        'Путешествия',
        'Культура и искусство',
        'Спорт',
        'Образование',
        'Экология',
        'Политика и общество',
    ];

    public function run(): void
    {
        $faker = Factory::create('ru_RU');

        $stmt = $this->pdo->prepare(
            'INSERT INTO categories (name, description, created_at, updated_at)
             VALUES (:name, :description, :created_at, :updated_at)'
        );

        foreach (self::CATEGORIES as $name) {
            // Faker генерирует 2–4 предложения для описания категории
            $description = $faker->paragraphs(
                nb: random_int(1, 2),
                asText: true
            );

            $createdAt = $faker->dateTimeBetween('-2 years', '-1 year')
                ->format('Y-m-d H:i:s');

            $stmt->execute([
                'name'        => $name,
                'description' => $description,
                'created_at'  => $createdAt,
                'updated_at'  => $createdAt,
            ]);

            $this->log("  Category created: {$name}");
        }

        $this->log('CategorySeeder done — ' . count(self::CATEGORIES) . ' categories.');
    }
}