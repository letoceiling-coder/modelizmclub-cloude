<?php

use Dedoc\Scramble\Http\Middleware\RestrictedDocsAccess;

return [
    /*
     * Which routes to document. String or array form; use Scramble::routes() for custom selection.
     *
     * 'api_path' => [
     *     'include' => 'api',
     *     'exclude' => ['api/internal'],
     * ],
     *
     * Without *, patterns match path segments (api matches api and api/users, not apiary).
     * With *, Str::is is used (e.g. api/v*).
     *
     * One static include → default server is /{include} and paths are stripped (/users).
     * Multiple includes or wildcards → server defaults to / and paths stay full (/api/users).
     * Override with `servers`, or use Scramble::registerApi() for separate bases.
     */
    'api_path' => 'api',

    /*
     * Открыть документацию на не-локальных окружениях (дев-сервер).
     * Читается через config(), чтобы корректно работать с `config:cache`.
     */
    'docs_enabled' => env('SCRAMBLE_ENABLED', false),

    /*
     * Your API domain. By default, app domain is used. This is also a part of the default API routes
     * matcher, so when implementing your own, make sure you use this config if needed.
     */
    'api_domain' => null,

    /*
     * The path where your OpenAPI specification will be exported.
     */
    'export_path' => 'api.json',

    /*
     * Cache configuration for the generated OpenAPI document.
     *
     * Use `scramble:cache` to warm the cache and `scramble:clear` to invalidate it.
     */
    'cache' => [
        'key' => 'scramble.openapi',
        'store' => 'file',
    ],

    'info' => [
        /*
         * API version.
         */
        'version' => env('API_VERSION', 'v1'),

        /*
         * Description rendered on the home page of the API documentation (`/docs/api`).
         */
        'description' => <<<'MD'
        REST API платформы «Моделизм» (Laravel + Sanctum).

        ## Быстрый старт (Swagger Try It)

        1. Выберите сервер **Dev** (`https://dev-cloude.modelizmclub.ru/api/v1`).
        2. Выполните **`POST /auth/login`** с телом из примера ниже.
        3. Скопируйте `token` из ответа.
        4. Нажмите **Authorize** (замок) → введите `Bearer <token>` → **Authorize**.
        5. Проверьте **`GET /auth/me`** и **`GET /ping`**.

        ## QA-песочница (тестовая БД)

        На **dev-cloude** включён режим `DB_USE_QA=true`: все запросы Swagger/API работают
        с **отдельной PostgreSQL-базой** (`modelizm_cloude_qa`), не затрагивая основную БД.
        Создание, редактирование и удаление в Try It безопасны для тестов.

        Пересоздать QA-базу с нуля (справочники + demo-контент):

        ```bash
        php artisan qa:reset --force
        ```

        **Что внутри после `qa:reset`:**
        - Справочники: города, категории, тарифы, правила контента
        - Пользователи: demo QA, admin, 5 QA-пользователей (`qa_user_1@modelizmclub.ru` … `qa_user_5@modelizmclub.ru`, пароль `password`)
        - 2 сообщества с разделами и участниками
        - 3 поста с комментариями и реакциями

        ## Тестовые учётные данные (dev)

        | Роль | E-mail | Пароль | Примечание |
        |------|--------|--------|------------|
        | QA / demo | `demo@modelizmclub.ru` | `DemoPass123` | основной аккаунт для Swagger |
        | Админ | `admin@modelizmclub.ru` | `password` | роль admin |
        | Factory-пользователи | `qa_user_1@modelizmclub.ru` … `qa_user_5@modelizmclub.ru` | `password` | 5 шт. после qa:reset |

        ### Пример входа

        ```json
        POST /auth/login
        {
          "email": "demo@modelizmclub.ru",
          "password": "DemoPass123",
          "device_name": "swagger"
        }
        ```

        ### Пример регистрации

        Поле **`consent: true`** обязательно (152-ФЗ).

        ```json
        POST /auth/register
        {
          "name": "Demo User",
          "email": "newuser@example.com",
          "password": "DemoPass123",
          "password_confirmation": "DemoPass123",
          "consent": true
        }
        ```

        > Web-маршруты `/login` и `/register` (без `/api/v1`) — только подсказки JSON.
        > Реальная авторизация: **`POST /api/v1/auth/login`** и **`POST /api/v1/auth/register`**.

        ## Авторизация

        - Базовый префикс: `/api/v1`.
        - Заголовок: `Authorization: Bearer <token>`.
        - Локализация ответов и ошибок — русская.

        ## Лимиты запросов

        | Лимит | По умолчанию |
        |-------|--------------|
        | API (авторизован) | 120 запр./мин |
        | API (гость) | 40 запр./мин |
        | Загрузки | 30 запр./мин |
        | Auth (login/register) | 3–10 запр./мин |

        При `429` смотрите заголовки `X-RateLimit-*`, `Retry-After`, `X-Request-Id`.

        ## Загрузка файлов

        | Профиль | Расширения | Макс. вес |
        |---------|-----------|----------|
        | Аватар | jpg, jpeg, png, webp | 5 МБ |
        | Фото поста | jpg, jpeg, png, webp, gif | 15 МБ (до 10 шт.) |
        | Фото объявления | jpg, jpeg, png, webp | 15 МБ |
        | Видео поста | mp4, webm, mov | 200 МБ |

        Изображения конвертируются в **WebP**. Медиа отдаётся с `MEDIA_URL` (напр. `https://img.modelizmclub.ru`).

        ## Полезные эндпоинты

        - `GET /ping` — healthcheck
        - `GET /auth/me` — текущий пользователь (требует Bearer)
        MD,
    ],

    'ui' => [
        'title' => 'Моделизм API',
    ],

    'renderer' => 'elements',

    'renderers' => [
        /*
         * Stoplight Elements config options: https://docs.stoplight.io/docs/elements/b074dc47b2826-elements-configuration-options
         */
        'elements' => [
            'view' => 'scramble::docs',
            'theme' => 'light',
            'hideTryIt' => false,
            'hideSchemas' => false,
            'logo' => '',
            'tryItCredentialsPolicy' => 'include',
            'layout' => 'responsive',
            'router' => 'hash',
        ],
        /*
         * Scalar API reference config options: https://scalar.com/products/api-references/configuration
         */
        'scalar' => [
            'view' => 'scramble::scalar',
            'cdn' => 'https://cdn.jsdelivr.net/npm/@scalar/api-reference',
            'theme' => 'laravel',
            'proxyUrl' => 'https://proxy.scalar.com',
            'darkMode' => false,
            'showDeveloperTools' => 'never',
            'agent' => ['disabled' => true],
            'credentials' => 'include',
        ],
    ],

    /*
     * The list of servers of the API. By default, when `null`, server URL will be created from
     * `scramble.api_path` and `scramble.api_domain` config variables. When providing an array, you
     * will need to specify the local server URL manually (if needed).
     *
     * Example of non-default config (final URLs are generated using Laravel `url` helper):
     *
     * ```php
     * 'servers' => [
     *     'Live' => 'api',
     *     'Prod' => 'https://scramble.dedoc.co/api',
     * ],
     * ```
     */
    'servers' => [
        'Local' => 'api/v1',
        'Dev' => 'https://dev-cloude.modelizmclub.ru/api/v1',
    ],

    /**
     * Determines how Scramble stores the descriptions of enum cases.
     * Available options:
     * - 'description' – Case descriptions are stored as the enum schema's description using table formatting.
     * - 'extension' – Case descriptions are stored in the `x-enumDescriptions` enum schema extension.
     *
     *    @see https://redocly.com/docs-legacy/api-reference-docs/specification-extensions/x-enum-descriptions
     * - false - Case descriptions are ignored.
     */
    'enum_cases_description_strategy' => 'description',

    /**
     * Determines how Scramble stores the names of enum cases.
     * Available options:
     * - 'names' – Case names are stored in the `x-enumNames` enum schema extension.
     * - 'varnames' - Case names are stored in the `x-enum-varnames` enum schema extension.
     * - false - Case names are not stored.
     */
    'enum_cases_names_strategy' => false,

    /**
     * When Scramble encounters deep objects in query parameters, it flattens the parameters so the generated
     * OpenAPI document correctly describes the API. Flattening deep query parameters is relevant until
     * OpenAPI 3.2 is released and query string structure can be described properly.
     *
     * For example, this nested validation rule describes the object with `bar` property:
     * `['foo.bar' => ['required', 'int']]`.
     *
     * When `flatten_deep_query_parameters` is `true`, Scramble will document the parameter like so:
     * `{"name":"foo[bar]", "schema":{"type":"int"}, "required":true}`.
     *
     * When `flatten_deep_query_parameters` is `false`, Scramble will document the parameter like so:
     *  `{"name":"foo", "schema": {"type":"object", "properties":{"bar":{"type": "int"}}, "required": ["bar"]}, "required":true}`.
     */
    'flatten_deep_query_parameters' => true,

    'middleware' => [
        'web',
        RestrictedDocsAccess::class,
    ],

    'extensions' => [],

    /*
     * Automatically document API security (OpenAPI `security` / `securitySchemes`) based on route
     * middleware.
     *
     * Disabled by default. Uncomment the line below to enable `MiddlewareAuthSecurityStrategy`.
     * When at least one documented route uses middleware matching the configured patterns (by default
     * `auth` and `auth:*`), bearer auth is applied globally. Routes without matching middleware are
     * marked as public (`security: []`).
     *
     * Set to `null` explicitly to disable. If you already configure security manually via
     * `afterOpenApiGenerated` / `extendOpenApi`, keep this disabled to avoid duplicate schemes.
     *
     * Customize with a class-string or [class, options]:
     *
     * 'security_strategy' => [
     *     \Dedoc\Scramble\SecurityDocumentation\MiddlewareAuthSecurityStrategy::class,
     *     [
     *         'middleware' => ['auth', 'auth:*'],
     *         'scheme' => \Dedoc\Scramble\Support\Generator\SecurityScheme::http('bearer'),
     *     ],
     * ],
     */
    'security_strategy' => \Dedoc\Scramble\SecurityDocumentation\MiddlewareAuthSecurityStrategy::class,
];
