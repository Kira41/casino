<?php

declare(strict_types=1);

return [
    'database' => [
        'dsn' => getenv('DB_DSN') ?: 'sqlite:' . __DIR__ . DIRECTORY_SEPARATOR . 'storage' . DIRECTORY_SEPARATOR . 'casino.sqlite',
        'username' => getenv('DB_USERNAME') ?: null,
        'password' => getenv('DB_PASSWORD') ?: null,
        'options' => [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ],
    ],
];
