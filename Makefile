# ─────────────────────────────────────────────────────────────────────────────
# Makefile — удобные команды для разработки
#
# Использование:
#   make up        — запустить контейнеры
#   make down      — остановить контейнеры
#   make migrate   — применить миграции
#   make seed      — заполнить БД тестовыми данными
#   make scss      — скомпилировать SCSS вручную
#   make scss-watch — компилировать SCSS в режиме watch (для разработки)
#   make shell     — войти в контейнер php-fpm
#   make logs      — показать логи всех контейнеров
#   make rebuild   — пересобрать образы и запустить
# ─────────────────────────────────────────────────────────────────────────────

.PHONY: up down migrate seed scss scss-watch shell logs rebuild help

# Контейнер php-fpm для выполнения PHP-команд
PHP = docker compose exec php-fpm

##@ Docker

up: ## Запустить все контейнеры в фоне
	docker compose up -d

down: ## Остановить все контейнеры
	docker compose down

rebuild: ## Пересобрать образы и запустить
	docker compose down
	docker compose build --no-cache
	docker compose up -d

logs: ## Показать логи (Ctrl+C для выхода)
	docker compose logs -f

##@ База данных

migrate: ## Применить все миграции
	$(PHP) php database/migrate.php

seed: ## Заполнить БД тестовыми данными (очищает таблицы!)
	$(PHP) php database/seed.php

##@ Стили

scss: ## Скомпилировать SCSS → CSS (продакшн, минификация)
	docker compose exec php-fpm sass \
		public/assets/scss/app.scss:public/assets/css/app.css \
		--style=compressed \
		--no-source-map

scss-watch: ## Компилировать SCSS в режиме watch (для разработки)
	docker compose exec php-fpm sass \
		public/assets/scss/app.scss:public/assets/css/app.css \
		--watch \
		--style=expanded

scss-local: ## Скомпилировать SCSS локально (нужен dart-sass)
	sass public/assets/scss/app.scss:public/assets/css/app.css \
		--style=compressed \
		--no-source-map

##@ Разработка

shell: ## Войти в контейнер php-fpm
	docker compose exec php-fpm bash

shell-root: ## Войти в контейнер php-fpm под root
	docker compose exec -u root php-fpm bash

##@ Справка

help: ## Показать эту справку
	@grep -E '^[a-zA-Z_-]+:.*?## .*$$' $(MAKEFILE_LIST) \
		| awk 'BEGIN {FS = ":.*?## "}; {printf "  \033[36m%-15s\033[0m %s\n", $$1, $$2}'

.DEFAULT_GOAL := help