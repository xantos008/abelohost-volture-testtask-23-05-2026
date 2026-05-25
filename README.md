# abelohost-volture-testtask-23-05-2026
This repository contains a completed technical assessment for the Fullstack Developer (PHP/JS) vacancy at AbeloHost. The task was provided by @vulturerec.


## Содержание

- [Быстрый старт](#быстрый-старт)
- [Установка и запуск](#установка-и-запуск)
- [Команды](#команды)
- [Структура проекта](#структура-проекта)
- [Архитектура](#архитектура)
- [Безопасность](#безопасность)

---

## Быстрый старт

```bash
# 1. Клонировать репозиторий
git clone https://github.com/xantos008/abelohost-volture-testtask-23-05-2026.git php-blog
cd php-blog

# 2. Создать .env
cp .env.example .env

# 3. Запустить контейнеры (сборка займёт 2-3 минуты в первый раз)
docker compose up -d

# 4. Применить миграции
make migrate

# 5. Заполнить БД тестовыми данными
make seed

# 6. Собрать .css из .scss (optional)
make scss
```

Открыть в браузере: **http://localhost**

---

## Установка и запуск

### Требования

- Docker Desktop 4.x+ или Docker Engine 24+
- docker compose (v2, встроен в Docker Desktop)
- GNU Make (опционально, для удобных команд)

### Шаги

#### 1. Переменные окружения

```bash
cp .env.example .env
```

При необходимости отредактировать `.env`:

```ini
APP_NAME="PHP Blog"
APP_ENV=local
APP_DEBUG=true

DB_HOST=mysql
DB_PORT=3306
DB_DATABASE=blog
DB_USERNAME=blog_user
DB_PASSWORD=secret
```

> `DB_HOST=mysql` — имя сервиса в docker-compose, не менять для Docker-окружения.

#### 2. Запуск контейнеров

```bash
docker compose up -d
```

Будет запущено три контейнера:

| Контейнер  | Описание            | Порт              |
|------------|---------------------|-------------------|
| `nginx`    | Веб-сервер          | `80 → 80`         |
| `php-fpm`  | PHP 8.4 + Node 24   | внутренний `9000` |
| `mysql`    | MySQL 8.4           | `3306 → 3306`     |

Проверить статус:

```bash
docker compose ps
```

#### 3. Миграции

```bash
make migrate
# или напрямую:
docker compose exec php-fpm php database/migrate.php
```

Ожидаемый вывод:

```
  [applied] 001_create_categories.sql
  [applied] 002_create_posts.sql
  [applied] 003_create_post_category.sql

Migrations complete.
```

Повторный запуск безопасен — уже применённые миграции пропускаются.

#### 4. Сидинг

```bash
make seed
# или напрямую:
docker compose exec php-fpm php database/seed.php
```

Создаёт:
- **10 категорий** с реалистичными названиями и описаниями
- **100 статей** с HTML-контентом, изображениями и случайными датами
- Каждой статье назначается **от 1 до 3 категорий**
- ~10% статей остаются черновиками (`published_at = NULL`)

> ⚠️ Сидинг очищает таблицы перед заполнением.

---

## Команды

```bash
make up           # Запустить контейнеры
make down         # Остановить контейнеры
make rebuild      # Пересобрать образы и запустить

make migrate      # Применить миграции
make seed         # Заполнить БД (очищает данные!)

make scss         # Скомпилировать SCSS → CSS (продакшн)
make scss-watch   # Компилировать SCSS в режиме watch (разработка)
make scss-local   # Скомпилировать локально (нужен dart-sass)

make shell        # Войти в контейнер php-fpm
make logs         # Логи всех контейнеров

make help         # Список всех команд
```

### SCSS вне Docker (локально)

```bash
# Установить dart-sass
npm install -g sass

# Однократная компиляция
make scss-local

# Режим watch для разработки
sass public/assets/scss/app.scss:public/assets/css/app.css --watch
```

---

## Структура проекта

```
php-blog/
├── app/
│   ├── Controllers/          # HTTP-контроллеры
│   │   ├── HomeController.php
│   │   ├── CategoryController.php
│   │   └── PostController.php
│   │
│   ├── Core/                 # Ядро фреймворка
│   │   ├── Database.php      # PDO-обёртка (singleton)
│   │   ├── Env.php           # Парсер .env
│   │   ├── Request.php       # HTTP-запрос
│   │   ├── Response.php      # HTTP-ответ и редиректы
│   │   ├── Router.php        # Роутер с {param}-маршрутами
│   │   └── Security/
│   │       └── CsrfGuard.php # CSRF-токены
│   │
│   ├── Models/               # Модели (прямой SQL через PDO)
│   │   ├── BaseModel.php
│   │   ├── CategoryModel.php
│   │   └── PostModel.php
│   │
│   └── Views/                # Smarty-шаблоны
│       ├── layouts/
│       │   └── main.tpl
│       ├── partials/
│       │   ├── header.tpl
│       │   ├── footer.tpl
│       │   ├── post-card.tpl
│       │   ├── pagination.tpl
│       │   └── related-posts.tpl
│       └── pages/
│           ├── home.tpl
│           ├── category.tpl
│           ├── post.tpl
│           └── error.tpl
│
├── bootstrap/
│   └── app.php               # Инициализация приложения
│
├── config/
│   ├── app.php               # Конфигурация приложения
│   └── database.php          # Конфигурация БД
│
├── database/
│   ├── migrate.php           # Migration runner
│   ├── seed.php              # Seed runner
│   ├── migrations/           # SQL-файлы миграций
│   │   ├── 001_create_categories.sql
│   │   ├── 002_create_posts.sql
│   │   └── 003_create_post_category.sql
│   └── seeders/
│       ├── BaseSeeder.php
│       ├── CategorySeeder.php
│       └── PostSeeder.php
│
├── docker/
│   ├── nginx/
│   │   └── default.conf
│   ├── php/
│   │   └── Dockerfile
│   └── mysql/
│       └── init/
│
├── public/                   # Единственная публичная директория
│   ├── index.php             # Front controller
│   ├── .htaccess
│   └── assets/
│       ├── scss/             # SCSS-исходники
│       └── css/
│           └── app.css       # Скомпилированный CSS
│
├── routes/
│   └── web.php               # Маршруты
│
├── storage/
│   ├── cache/                # Smarty-кэш
│   └── compiled/             # Скомпилированные шаблоны Smarty
│
├── .env.example
├── composer.json
├── docker-compose.yml
├── Makefile
└── README.md
```

---

## Архитектура

### Принципы

Проект намеренно **без фреймворка** и без оверинжиниринга:

- Нет DI-контейнера — зависимости создаются напрямую в конструкторе
- Нет репозиториев — модели пишут SQL явно, это читаемо и прозрачно
- Нет QueryBuilder — сложные запросы проще читать как SQL, чем как цепочки методов
- Нет EventDispatcher, CQRS, DDD — для блога это избыточно

### Жизненный цикл запроса

```
Browser → nginx → public/index.php
                      ↓
              bootstrap/app.php
           (env, session, headers, DB, Smarty)
                      ↓
               routes/web.php
                      ↓
                   Router
              (regex matching)
                      ↓
               Controller
           (валидация, модели)
                      ↓
                   Model
             (SQL через PDO)
                      ↓
               Controller
           (assign к Smarty)
                      ↓
              Smarty Template
                      ↓
                  Browser
```

### База данных

```
categories          posts
──────────          ─────
id (PK)             id (PK)
name (UNIQUE)       title
description         description
created_at          content
updated_at          image
                    views_count
                    published_at ← NULL = черновик
                    created_at
                    updated_at

post_category (M:M)
───────────────────
post_id (FK → posts)
category_id (FK → categories)
PK: (post_id, category_id)
```

### Маршруты

| Метод | URL               | Контроллер          | Описание           |
|-------|-------------------|---------------------|--------------------|
| GET   | `/`               | HomeController      | Главная страница   |
| GET   | `/category/{id}`  | CategoryController  | Страница категории |
| GET   | `/post/{id}`      | PostController      | Страница статьи    |

---

## Безопасность

### SQL Injection

Все запросы через **PDO Prepared Statements** с явным биндингом типов:

```php
// Только так — никакой конкатенации строк
$this->db->fetchOne(
    'SELECT * FROM posts WHERE id = :id',
    ['id' => $postId]
);
```

Сортировка `ORDER BY` защищена **whitelist**:

```php
private const SORT_WHITELIST = [
    'date'  => 'p.published_at DESC',
    'views' => 'p.views_count DESC',
];
// Пользовательская строка не попадает в SQL — только ключ whitelist
$orderBy = self::SORT_WHITELIST[$sort] ?? self::SORT_WHITELIST['date'];
```

### XSS

Smarty настроен с **глобальным автоэкранированием**:

```php
$smarty->setEscapeHtml(true); // bootstrap/app.php
```

Все переменные в шаблонах экранируются автоматически. Исключение — `{$post.content nofilter}` для доверенного HTML-контента от редактора.

### CSRF

Класс `CsrfGuard` готов для защиты POST-форм:

```php
// Генерация токена (в шаблоне формы)
$token = CsrfGuard::token();

// Валидация (в контроллере)
if (!CsrfGuard::validate($request->post('_csrf'))) {
    // отклонить запрос
}
```

Токен хранится в сессии, сравнение через `hash_equals()` — защита от timing-атак.

### HTTP Security Headers

Устанавливаются в каждом ответе через `Response::setSecurityHeaders()`:

```
X-Frame-Options: SAMEORIGIN
X-Content-Type-Options: nosniff
Referrer-Policy: strict-origin-when-cross-origin
Content-Security-Policy: default-src 'self'; style-src 'self' 'unsafe-inline'; ...
```

### Сессии

```php
ini_set('session.cookie_httponly', '1');  // недоступна JS
ini_set('session.cookie_samesite', 'Lax'); // защита от CSRF
ini_set('session.use_strict_mode', '1');   // отклонять чужие session id
```

---

## Разработка

### Добавить новый маршрут

1. Добавить маршрут в `routes/web.php`
2. Создать контроллер в `app/Controllers/`
3. Создать шаблон в `app/Views/pages/`

### Добавить миграцию

Создать файл `database/migrations/004_*.sql` и запустить:

```bash
make migrate
```

### Изменить стили

Редактировать файлы в `public/assets/scss/`, затем:

```bash
make scss        # однократно
make scss-watch  # в режиме watch
```