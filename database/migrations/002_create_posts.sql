-- Таблица статей
-- Индексы:
--   PRIMARY KEY (id)           — поиск по id
--   INDEX (published_at DESC)  — главная и страница категории сортируют по дате
--   INDEX (views_count DESC)   — сортировка по просмотрам на странице категории
--   FULLTEXT (title, content)  — задел на поиск (не обязателен сейчас, но дёшев)
--
-- published_at NULL означает «черновик» — статья не опубликована.

CREATE TABLE IF NOT EXISTS posts (
    id           INT UNSIGNED     NOT NULL AUTO_INCREMENT,
    image        VARCHAR(500)         NULL,
    title        VARCHAR(255)     NOT NULL,
    description  VARCHAR(500)     NOT NULL,
    content      LONGTEXT         NOT NULL,
    views_count  INT UNSIGNED     NOT NULL DEFAULT 0,
    published_at DATETIME             NULL,
    created_at   DATETIME         NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at   DATETIME         NOT NULL DEFAULT CURRENT_TIMESTAMP
    ON UPDATE CURRENT_TIMESTAMP,

    PRIMARY KEY (id),
    INDEX idx_posts_published_at (published_at DESC),
    INDEX idx_posts_views_count  (views_count  DESC),
    FULLTEXT INDEX ft_posts_search (title, content)
) ENGINE=InnoDB
    DEFAULT CHARSET=utf8mb4
    COLLATE=utf8mb4_unicode_ci;