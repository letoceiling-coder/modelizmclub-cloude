<?php

declare(strict_types=1);

$base = dirname(__DIR__);
$openapiPath = $base.'/docs/openapi.json';
$openapi = json_decode((string) file_get_contents($openapiPath), true, 512, JSON_THROW_ON_ERROR);

$routeOutput = shell_exec('php '.escapeshellarg($base.'/artisan').' route:list --path=api 2>&1') ?: '';

$docOps = [];
foreach ($openapi['paths'] ?? [] as $path => $ops) {
    foreach (array_keys($ops) as $method) {
        if (in_array($method, ['get', 'post', 'put', 'patch', 'delete'], true)) {
            $docOps[strtoupper($method).' '.$path] = true;
        }
    }
}

$appOps = [];
foreach (explode("\n", $routeOutput) as $line) {
    $line = trim($line);
    if (! preg_match('/^((?:GET|HEAD|POST|PUT|PATCH|DELETE)(?:\|(?:GET|HEAD|POST|PUT|PATCH|DELETE))*)\s+api\/v1\/(\S+)/', $line, $m)) {
        continue;
    }
    $path = '/api/v1/'.preg_replace('/\s+.*/', '', $m[2]);
    foreach (explode('|', $m[1]) as $method) {
        if ($method === 'HEAD') {
            continue;
        }
        $appOps[strtoupper($method).' '.$path] = true;
    }
}

$normalize = static function (string $key): string {
    // Scramble: server /api + paths /v1/...  ↔  Laravel routes api/v1/...
    return (string) preg_replace(
        '#^(GET|POST|PUT|PATCH|DELETE) /api/v1/#',
        '$1 /v1/',
        $key,
    );
};

$docOpsNorm = [];
foreach (array_keys($docOps) as $key) {
    $docOpsNorm[$normalize($key)] = $key;
}
$appOpsNorm = [];
foreach (array_keys($appOps) as $key) {
    $appOpsNorm[$normalize($key)] = $key;
}

$missing = array_diff(array_keys($appOpsNorm), array_keys($docOpsNorm));
$extra = array_diff(array_keys($docOpsNorm), array_keys($appOpsNorm));

sort($missing);
sort($extra);

echo "=== OpenAPI сверка ===\n";
echo 'Файл: '.$openapiPath."\n";
echo 'Title: '.($openapi['info']['title'] ?? '?').' v'.($openapi['info']['version'] ?? '?')."\n";
echo 'Paths в OpenAPI: '.count($openapi['paths'] ?? [])."\n";
echo 'Операций в OpenAPI: '.count($docOps)."\n";
echo 'Операций в приложении (api/v1): '.count($appOps)."\n";
echo 'Покрытие: '.round((count($appOps) - count($missing)) / max(count($appOps), 1) * 100, 1)."%\n\n";

echo 'Отсутствуют в OpenAPI ('.count($missing)."):\n";
foreach ($missing as $m) {
    echo "  - {$m}\n";
}

if ($extra !== []) {
    echo "\nЛишние в OpenAPI (нет в route:list) (".count($extra)."):\n";
    foreach ($extra as $e) {
        echo "  + {$e}\n";
    }
}

// Проверка ключевых эндпоинтов медиа/загрузок
$checks = [
    'POST /api/v1/posts' => 'Создание поста (photos/video)',
    'POST /api/v1/profile/avatar' => 'Загрузка аватара',
    'GET /api/v1/ping' => 'Health ping',
    'GET /api/v1/docs/api.json' => null,
];
echo "\n=== Ключевые эндпоинты ===\n";
foreach ($checks as $op => $label) {
    if ($label === null) {
        continue;
    }
    $norm = $normalize($op);
    $ok = isset($docOpsNorm[$norm]);
    echo ($ok ? '[OK]' : '[MISS]')." {$label} ({$norm})\n";
}

// multipart на posts
$post = $openapi['paths']['/v1/posts']['post'] ?? null;
if ($post) {
    $hasMultipart = false;
    $rb = $post['requestBody']['content'] ?? [];
    $hasMultipart = isset($rb['multipart/form-data']);
    echo ($hasMultipart ? '[OK]' : '[WARN]')." POST /posts: multipart/form-data для photos\n";
}

$avatar = $openapi['paths']['/v1/profile/avatar']['post'] ?? null;
if ($avatar) {
    $hasMultipart = isset(($avatar['requestBody']['content'] ?? [])['multipart/form-data']);
    echo ($hasMultipart ? '[OK]' : '[WARN]')." POST /profile/avatar: multipart/form-data\n";
}

// Security scheme
$sec = array_keys($openapi['components']['securitySchemes'] ?? []);
echo "\nSecurity schemes: ".implode(', ', $sec ?: ['нет'])."\n";

exit(count($missing) > 0 ? 1 : 0);
