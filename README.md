# Product Search API

REST API для поиска товаров на Laravel с использованием Elasticsearch.

## Стек

* PHP 8.3
* Laravel 13
* MySQL 8.4
* Elasticsearch 8.14
* Kibana 8.14
* Docker / Docker Compose

## Реализовано

* ~100 000 товаров в Elasticsearch.
* Полнотекстовый поиск по названию и описанию.
* Фильтры: категория, бренд, цена, рейтинг, активность.
* Сортировка, пагинация и highlighting.
* Массовая индексация через Bulk API.
* Синхронизация MySQL → Elasticsearch через Observer и Queue.
* Импорт товаров из Elasticsearch в MySQL.
* Artisan-команда для полной синхронизации.
* Laravel Scheduler + Linux Cron для автоматической синхронизации.
* Elasticsearch и Kibana работают в Docker.

## Запуск

```bash
docker compose up -d --build
```

## Генерация 100000 товаров и сохраняет в файле products.jsonl

```bash
docker compose exec app php artisan products:generate --count=100000 
```

## Отправка товаров из файла в ElasticSearch

```bash
docker compose exec app php artisan products:index
```

## Импорт данных из Elasticsearch в MySQL

```bash
docker compose exec app php artisan app:import-products-from-elasticsearch
```

## Основные сервисы

* API: `http://localhost:8000`
* Elasticsearch: `http://localhost:9200`
* Kibana: `http://localhost:5601`
  

## Ручная синхронизация

```bash
docker compose exec app php artisan app:sync-products
```

Автоматическая синхронизация выполняется через Laravel Scheduler и Linux Cron.
