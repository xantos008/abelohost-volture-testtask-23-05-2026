-- Таблица категорий
-- Индексы:
--   PRIMARY KEY (id)          — автоматически, поиск по id O(log n)
--   UNIQUE (name)             — запрет дублирующихся названий
--   INDEX (created_at)        — если понадобится сортировка по дате создания

CREATE TABLE IF NOT EXISTS categories (
    id          INT UNSIGNED    NOT NULL AUTO_INCREMENT,
    name        VARCHAR(150)    NOT NULL,
    description TEXT                NULL,
    created_at  DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at  DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP
    ON UPDATE CURRENT_TIMESTAMP,

    PRIMARY KEY (id),
    UNIQUE  KEY uq_categories_name (name),
    INDEX       idx_categories_created_at (created_at)
) ENGINE=InnoDB
    DEFAULT CHARSET=utf8mb4
    COLLATE=utf8mb4_unicode_ci;