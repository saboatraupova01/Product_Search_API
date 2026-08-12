# Product Search API

REST API для поиска и синхронизации товаров с использованием Laravel и Elasticsearch.

## Стек

* PHP 8.3
* Laravel 13
* MySQL 8.4
* Elasticsearch 8.14
* Kibana 8.14
* Docker / Docker Compose

## Реализовано

* Индексирование ~100 000 товаров в Elasticsearch.
* Полнотекстовый поиск по названию и описанию.
* Фильтрация по категории, бренду, цене, рейтингу и активности.
* Сортировка, пагинация и highlighting.
* Массовое индексирование через Bulk API.
* Синхронизация MySQL → Elasticsearch через Observer и Queue.
* Импорт товаров из Elasticsearch в MySQL.
* Artisan-команда для полной синхронизации товаров.
* Laravel Scheduler + Linux Cron для автоматического запуска синхронизации.
* Docker-окружение с Elasticsearch, Kibana, MySQL, Nginx и отдельным Scheduler-контейнером.

## Запуск

```bash
git clone https://github.com/saboatraupova01/Product_Search_API.git
cd Product_Search_API

cp .env.example .env

docker compose up -d --build

docker compose exec app composer install
docker compose exec app php artisan key:generate
docker compose exec app php artisan migrate
```

API доступен на:

```text
http://localhost:8000
```

Elasticsearch:

```text
http://localhost:9200
```

Kibana:

```text
http://localhost:5601
```

## Основная команда синхронизации

```bash
docker compose exec app php artisan app:sync-products
```

Автоматический запуск выполняется через Laravel Scheduler и Linux Cron.
