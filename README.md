# PHP Blog — учебный CRUD на PHP + SQLite + PDO

Простой блог: создание, просмотр, редактирование и удаление постов.
Без Composer и сторонних библиотек.

## Требования
- PHP 8.1+ с расширениями `pdo_sqlite` и `mbstring`

## Запуск

1. Проверь PHP:
```
   php -v
   php -m | grep -i sqlite     # Windows: php -m | findstr sqlite
```
2. Создай базу данных:
```
   php database/init.php
```
3. Запусти встроенный сервер:
```
   php -S localhost:8000 -t public
```
4. Открой в браузере: http://localhost:8000

## Структура
- `public/` — страницы (точки входа)
- `src/` — классы `Database` и `Post`
- `templates/` — общие куски HTML
- `database/` — скрипт создания БД и файл `blog.sqlite`

## Как идут данные
Browser → PHP (public/*.php) → Post → PDO → SQLite