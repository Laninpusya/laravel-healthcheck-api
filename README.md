# Laravel Health Check API

Тестовое задание: Laravel API с `GET /api/v1/health`, MySQL, Redis и Docker Compose.

## Что реализовано

- `GET /api/v1/health`
- ответ `200` или `500` в зависимости от доступности MySQL и Redis
- JSON в формате:

```json
{
  "db": true,
  "cache": false
}
```

- обязательный заголовок `X-Owner: {uuid}`
- ограничение `60` запросов в минуту
- логирование каждого запроса в таблицу `health_check_requests`

## Подход

- Проверка инфраструктуры вынесена в сервис `InfrastructureHealthService`.
- Валидация заголовка и логирование разнесены по отдельным middleware.
- Throttle использует отдельный store `file`, чтобы endpoint продолжал отвечать JSON даже при падении Redis.
- Миграции запускаются автоматически при старте контейнера `app`.

## Важное допущение

В ТЗ заголовок записан как `X Owner`, но HTTP-заголовки со space в имени невалидны. Поэтому в проекте используется стандартный вариант `X-Owner`.

## Запуск

```bash
git clone <repo>
cd laravel-healthcheck-api
docker compose up -d
```

После старта endpoint будет доступен по адресу:

```text
http://localhost:8080/api/v1/health
```

MySQL для внешнего подключения доступен на:

```text
127.0.0.1:13306
```

## Пример запроса

```bash
curl --request GET \
  --url http://localhost:8080/api/v1/health \
  --header "X-Owner: 123e4567-e89b-12d3-a456-426614174000"
```

## Подключение через TablePlus

- Host: `127.0.0.1`
- Port: `13306`
- User: `laravel`
- Password: `laravel`
- Database: `healthcheck`
- SSL: `off`

Если на другом устройстве порт `13306` окажется занят, можно переопределить его перед запуском:

```bash
MYSQL_FORWARD_PORT=23306 docker compose up -d
```

## Структура решения

- `app/Services/InfrastructureHealthService.php` — проверка MySQL и Redis
- `app/Http/Middleware/EnsureOwnerHeader.php` — проверка `X-Owner`
- `app/Http/Middleware/LogHealthCheckRequest.php` — логирование запросов
- `database/migrations/*create_health_check_requests_table.php` — таблица логов
- `docker-compose.yml` — стек из `app`, `nginx`, `mysql`, `redis`

## Локальная проверка без Docker

```bash
php artisan test
```
