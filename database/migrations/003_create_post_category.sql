-- Таблица связи M:M статей и категорий
--
-- Индексы:
--   PRIMARY KEY (post_id, category_id)  — составной PK, исключает дубли,
--                                         ускоряет «все категории поста»
--   INDEX (category_id, post_id)        — ускоряет «все посты категории»
--                                         (обратный порядок колонок важен)
--
-- ON DELETE CASCADE:
--   Удалили пост/категорию → связи чистятся автоматически.
--   Referential Integrity гарантирована на уровне БД.

CREATE TABLE IF NOT EXISTS post_category (
    post_id     INT UNSIGNED NOT NULL,
    category_id INT UNSIGNED NOT NULL,

    PRIMARY KEY (post_id, category_id),
    INDEX idx_post_category_category (category_id, post_id),

    CONSTRAINT fk_pc_post
    FOREIGN KEY (post_id)
    REFERENCES posts (id)
    ON DELETE CASCADE
    ON UPDATE CASCADE,

    CONSTRAINT fk_pc_category
    FOREIGN KEY (category_id)
    REFERENCES categories (id)
    ON DELETE CASCADE
    ON UPDATE CASCADE
) ENGINE=InnoDB
    DEFAULT CHARSET=utf8mb4
    COLLATE=utf8mb4_unicode_ci;