# API платформы «Моделизм»

REST API на Laravel + Sanctum. Все ответы и тексты ошибок — на русском языке.

- **Базовый URL (dev):** `https://dev-cloude.modelizmclub.ru/api/v1`
- **Базовый URL (локально):** `http://127.0.0.1:8000/api/v1`
- **Версия API:** `v1`
- **Формат:** `application/json`

## Интерактивная документация (Swagger / OpenAPI)

- **Swagger UI:** `/docs/api` (Stoplight Elements, есть «Try It»)
- **OpenAPI JSON (живой):** `/docs/api.json`
- **OpenAPI JSON (экспорт в репозитории):** [`docs/openapi.json`](./openapi.json)

UI открыт локально всегда. На дев-сервере доступ включается переменной `SCRAMBLE_ENABLED=true` в `.env`.

Пере-генерация спецификации:

```bash
php artisan scramble:export --path=docs/openapi.json
```

## Аутентификация

Используется Bearer-токен Sanctum. После регистрации/логина передавайте токен в каждом
защищённом запросе:

```
Authorization: Bearer <token>
```

| Метод | Путь | Назначение |
|------|------|-----------|
| POST | `/auth/register` | Регистрация, возвращает токен |
| POST | `/auth/login` | Вход, возвращает токен |
| POST | `/auth/logout` | Выход (отзыв токена) |
| GET | `/auth/me` | Текущий пользователь |
| POST | `/auth/email/verify` | Подтверждение e-mail |
| POST | `/auth/email/resend` | Повторная отправка письма |
| POST | `/auth/password/forgot` | Запрос сброса пароля |
| POST | `/auth/password/reset` | Сброс пароля |
| GET | `/auth/social/{provider}/redirect` | OAuth-редирект (VK ID, Yandex ID) |
| GET | `/auth/social/{provider}/callback` | OAuth-callback |

### QA-песочница (тестовая БД для Swagger)

На dev-сервере (`dev-cloude.modelizmclub.ru`) включён режим **`DB_USE_QA=true`**: API и Swagger Try It
работают с **отдельной PostgreSQL-базой** `modelizm_cloude_qa`. CRUD-операции (создание, редактирование,
удаление) не затрагивают основную БД.

Пересоздать QA-базу с тестовыми данными:

```bash
php artisan qa:reset --force
# или на сервере:
bash deploy/server-qa-db.sh
```

После сида в БД есть: справочники, demo/admin-пользователи, ~15 factory-пользователей (пароль `password`),
5 сообществ, посты, комментарии, реакции.

### Тестовые учётные данные (QA / Swagger Try It)

| Переменная / роль | E-mail | Пароль |
|-------------------|--------|--------|
| `SANCTUM_EMAIL` / demo | `demo@modelizmclub.ru` | `DemoPass123` |
| Админ | `admin@modelizmclub.ru` | `password` |
| Factory-пользователи | *(см. API)* | `password` |

```bash
# Вход (полный путь API)
curl -X POST https://dev-cloude.modelizmclub.ru/api/v1/auth/login \
  -H "Accept: application/json" -H "Content-Type: application/json" \
  -d '{"email":"demo@modelizmclub.ru","password":"DemoPass123"}'

# Текущий пользователь
curl https://dev-cloude.modelizmclub.ru/api/v1/auth/me \
  -H "Authorization: Bearer <token>"
```

> **Важно:** web-маршруты `/login` и `/register` (без `/api/v1`) — это подсказки для браузера/сканеров.
> Реальная авторизация только через **`POST /api/v1/auth/login`** и **`POST /api/v1/auth/register`**
> (поле `consent: true` обязательно при регистрации).

### Пример: регистрация и запрос

```bash
# Регистрация (consent обязателен)
curl -X POST http://127.0.0.1:8000/api/v1/auth/register \
  -H "Accept: application/json" -H "Content-Type: application/json" \
  -d '{"name":"Иван","email":"ivan@example.com","password":"password123","password_confirmation":"password123","consent":true}'

# Использование токена
curl http://127.0.0.1:8000/api/v1/auth/me \
  -H "Accept: application/json" -H "Authorization: Bearer <token>"
```

## Лимиты запросов (rate limiting)

Все эндпоинты `/api/v1/*` ограничены глобально, а загрузки — отдельным строгим лимитом.
Значения настраиваются в `.env` (`RATE_LIMIT_API`, `RATE_LIMIT_GUEST`, `RATE_LIMIT_UPLOADS`).

| Лимит | По умолчанию | На что распространяется |
|------|--------------|-------------------------|
| API (авторизован) | 120 запр./мин на пользователя | весь API |
| API (гость) | 40 запр./мин на IP | публичные эндпоинты |
| Загрузки | 30 запр./мин | создание/обновление постов, аватар |
| Авторизация | 3–10 запр./мин | register/login/verify/reset |

При превышении возвращается `429 Too Many Requests` с заголовками `X-RateLimit-*` и `Retry-After`.
Каждый ответ содержит `X-Request-Id` для трассировки в логах.

## Загрузка файлов и обработка изображений

Политика загрузок централизована в `config/uploads.php`. Доступные расширения,
MIME-типы и максимальный вес задаются по профилям и переопределяются через `.env`.

| Профиль | Расширения | Макс. вес | Конверсии (WebP) |
|--------|-----------|----------|------------------|
| Аватар | jpg, jpeg, png, webp | 5 МБ | `thumb` 128², `medium` 512² (crop) |
| Фото поста | jpg, jpeg, png, webp, gif | 15 МБ (до 10 шт.) | `thumb` 400² (crop), `medium` 1080w, `large` 1920w |
| Фото объявления | jpg, jpeg, png, webp | 15 МБ | `thumb` 400² (crop), `medium` 1080w |
| Видео поста | mp4, webm, mov | 200 МБ | — |
| Вложение в чат | jpg, png, webp, gif, pdf | 20 МБ | `thumb` 400² |

- Все изображения автоматически конвертируются в **WebP** (качество `IMAGE_QUALITY`, по умолчанию 82) для ускоренной загрузки.
- Квадратные превью обрезаются по соотношению сторон (crop), крупные — масштабируются с сохранением пропорций.
- Конверсии выполняются в фоне через очередь (Horizon); исходники и производные хранятся на диске `MEDIA_DISK`.
- Опасные расширения (`php`, `exe`, `svg`, `html`, …) отклоняются на уровне media-library.
- Медиа отдаётся по адресу из `MEDIA_URL` (выделенный поддомен, напр. `https://img.modelizmclub.ru`).

## Профиль и пользователи

| Метод | Путь | Назначение |
|------|------|-----------|
| GET | `/users/{user}` | Публичный профиль |
| PATCH | `/profile` | Обновить свой профиль |
| POST | `/profile/avatar` | Загрузить аватар (multipart) |
| PUT | `/profile/interests` | Синхронизировать интересы |
| PUT | `/profile/privacy` | Настройки приватности |
| POST/DELETE | `/users/{user}/block` | Заблокировать / разблокировать пользователя |

## Лента (Feed)

| Метод | Путь | Назначение |
|------|------|-----------|
| GET | `/posts` | Список постов (фильтры/сортировки через query-builder) |
| POST | `/posts` | Создать пост |
| GET | `/posts/{post}` | Пост |
| PUT/PATCH | `/posts/{post}` | Обновить пост |
| DELETE | `/posts/{post}` | Удалить пост |
| POST/DELETE | `/posts/{post}/pin` | Закрепить / открепить |
| POST | `/posts/{post}/reactions` | Реакция на пост (toggle) |
| POST | `/posts/{post}/bookmark` | Закладка (toggle) |
| GET | `/bookmarks` | Мои закладки |
| GET | `/posts/{post}/comments` | Корневые комментарии |
| POST | `/posts/{post}/comments` | Добавить комментарий |
| GET | `/comments/{comment}/replies` | Ответы на комментарий |
| POST | `/comments/{comment}/reactions` | Реакция на комментарий |
| DELETE | `/comments/{comment}` | Удалить комментарий |

### Фильтрация, сортировка, пагинация

Список постов поддерживает `spatie/laravel-query-builder`:

```
GET /api/v1/posts?filter[community_id]=1&sort=-created_at&page=2&per_page=20
```

## Каталог (Catalog)

| Метод | Путь | Назначение |
|------|------|-----------|
| GET | `/categories` | Дерево категорий (nested set) |
| POST | `/categories` | Создать категорию (admin) |
| GET | `/categories/{category}` | Категория |
| PUT/PATCH | `/categories/{category}` | Обновить |
| DELETE | `/categories/{category}` | Удалить |
| POST | `/categories/reorder` | Переупорядочить дерево |
| GET | `/cities` | Поиск городов (`?filter[search]=`) |
| GET | `/delivery-methods` | Способы доставки |

## Сообщества (Communities)

| Метод | Путь | Назначение |
|------|------|-----------|
| GET | `/communities` | Список сообществ |
| GET | `/communities/{community}` | Сообщество |
| PUT/PATCH | `/communities/{community}` | Обновить (владелец/модератор) |
| DELETE | `/communities/{community}` | Удалить |
| POST | `/communities/{community}/join` | Вступить |
| DELETE | `/communities/{community}/leave` | Выйти |
| GET | `/communities/{community}/members` | Участники |
| PATCH | `/communities/{community}/members/{user}` | Изменить роль участника |
| DELETE | `/communities/{community}/members/{user}` | Удалить участника |
| GET/POST | `/communities/{community}/sections` | Разделы |
| PUT/PATCH/DELETE | `/communities/{community}/sections/{section}` | Управление разделом |
| GET/POST | `/community-applications` | Заявки на создание сообщества |
| POST | `/community-applications/{application}/approve` | Одобрить (модератор) |
| POST | `/community-applications/{application}/reject` | Отклонить (модератор) |

## Объявления (Ads)

| Метод | Путь | Назначение |
|------|------|-----------|
| GET | `/ads` | Список объявлений |
| POST | `/ads` | Создать объявление |
| GET | `/ads/{ad}` | Объявление |
| POST | `/ads/ai-drafts` | Создать AI-черновик |
| GET | `/ads/ai-drafts/{draft}` | AI-черновик |

## Сообщения (Messaging, real-time)

Real-time через Laravel Reverb. Приватные каналы: `users.{id}`, `conversations.{conversation}`.

| Метод | Путь | Назначение |
|------|------|-----------|
| GET/POST | `/conversations` | Список / создать диалог |
| GET | `/conversations/{conversation}` | Диалог |
| GET/POST | `/conversations/{conversation}/messages` | Сообщения |
| POST | `/conversations/{conversation}/read` | Отметить прочитанным |

События трансляции: `MessageSent`, `MessageRead`.

## Биллинг (Billing)

| Метод | Путь | Назначение |
|------|------|-----------|
| GET | `/plans` | Тарифные планы |
| GET/POST | `/subscriptions` | Подписки пользователя |

## Промо (Promotions)

| Метод | Путь | Назначение |
|------|------|-----------|
| GET | `/banners` | Баннеры |
| POST | `/banners/{banner}/click` | Учёт клика |
| POST | `/promo-codes/validate` | Проверка промокода |

## Поддержка (Support)

| Метод | Путь | Назначение |
|------|------|-----------|
| GET | `/kb/articles` | Статьи базы знаний |
| GET | `/kb/articles/{kbArticle}` | Статья |
| GET | `/legal` | Список юридических документов |
| GET | `/legal/{type}` | Документ по типу |
| GET/POST | `/support/tickets` | Тикеты поддержки |
| GET | `/support/tickets/{ticket}` | Тикет |
| POST | `/support/tickets/{ticket}/reply` | Ответ в тикет |

## Уведомления (Notifications)

| Метод | Путь | Назначение |
|------|------|-----------|
| GET | `/notifications` | Список уведомлений |
| GET | `/notifications/unread-count` | Кол-во непрочитанных |
| POST | `/notifications/{id}/read` | Отметить прочитанным |
| POST | `/notifications/read-all` | Прочитать все |
| POST/DELETE | `/push-subscriptions` | Web-push подписки |

## Модерация (Moderation)

| Метод | Путь | Назначение |
|------|------|-----------|
| GET | `/moderation/queue` | Очередь модерации |
| GET | `/moderation/queue/{item}` | Элемент очереди |
| POST | `/moderation/queue/{item}/approve` | Одобрить |
| POST | `/moderation/queue/{item}/reject` | Отклонить |
| POST | `/moderation/queue/{item}/needs-revision` | На доработку |
| GET/POST | `/reports` | Жалобы |
| POST | `/reports/{report}/resolve` | Решить жалобу |
| POST | `/reports/{report}/dismiss` | Отклонить жалобу |
| GET/POST | `/content-rules` | Правила контента (стоп-слова и т.п.) |
| PUT/PATCH/DELETE | `/content-rules/{contentRule}` | Управление правилом |

## Админ-панель (Admin)

| Метод | Путь | Назначение |
|------|------|-----------|
| GET | `/admin/dashboard` | Сводка |
| GET | `/admin/users` | Список пользователей |
| GET | `/admin/users/{user}` | Пользователь |
| POST | `/admin/users/{user}/ban` | Бан |
| POST | `/admin/users/{user}/unban` | Снять бан |
| PUT | `/admin/users/{user}/roles` | Синхронизировать роли |
| GET | `/admin/audit-log` | Журнал аудита |

## Служебное

| Метод | Путь | Назначение |
|------|------|-----------|
| GET | `/ping` | Проверка доступности API |
| GET | `/docs/api` | Swagger UI |
| GET | `/docs/api.json` | OpenAPI JSON |
| — | `/horizon` | Панель очередей (Horizon) |

## Коды ответов

| Код | Значение |
|-----|----------|
| 200 | OK |
| 201 | Создано |
| 204 | Нет содержимого |
| 401 | Не авторизован |
| 403 | Доступ запрещён |
| 404 | Не найдено |
| 422 | Ошибка валидации (`errors` с описаниями полей) |
| 429 | Слишком много запросов |
