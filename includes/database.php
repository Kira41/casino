<?php

declare(strict_types=1);

function openDatabase(): PDO
{
    $configPath = __DIR__ . '/../config.php';
    $config = is_file($configPath) ? require $configPath : [];
    $databaseConfig = isset($config['database']) && is_array($config['database']) ? $config['database'] : [];

    $dsn = isset($databaseConfig['dsn']) && is_string($databaseConfig['dsn']) ? trim($databaseConfig['dsn']) : '';

    if ($dsn === '') {
        throw new RuntimeException('A valid MySQL DSN is required.');
    }

    $username = isset($databaseConfig['username']) && is_string($databaseConfig['username']) ? $databaseConfig['username'] : null;
    $password = isset($databaseConfig['password']) && is_string($databaseConfig['password']) ? $databaseConfig['password'] : null;
    $options = isset($databaseConfig['options']) && is_array($databaseConfig['options']) ? $databaseConfig['options'] : [];
    $options += [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION];

    if (str_starts_with($dsn, 'mysql:')) {
        if (!defined('PDO::MYSQL_ATTR_INIT_COMMAND')) {
            throw new RuntimeException('MySQL PDO extension is required.');
        }

        $options += [
            PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci",
            PDO::MYSQL_ATTR_MULTI_STATEMENTS => true,
        ];
    }

    $database = new PDO($dsn, $username, $password, $options);

    if ($database->getAttribute(PDO::ATTR_DRIVER_NAME) === 'mysql') {
        $database->exec('SET time_zone = "+00:00"');
    }

    initializeTables($database);

    return $database;
}

function initializeTables(PDO $database): void
{
    $schemaFilename = $database->getAttribute(PDO::ATTR_DRIVER_NAME) === 'mysql'
        ? 'database.mysql.sql'
        : 'database.sql';

    $schemaPath = __DIR__ . '/../storage/' . $schemaFilename;
    $schemaSql = is_file($schemaPath) ? file_get_contents($schemaPath) : false;

    if ($schemaSql !== false) {
        $database->exec($schemaSql);
        return;
    }

    $database->exec(
        'CREATE TABLE IF NOT EXISTS subscriptions (
            id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            email VARCHAR(255) NOT NULL UNIQUE,
            created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci'
    );

    $database->exec(
        'CREATE TABLE IF NOT EXISTS signins (
            id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            email VARCHAR(255) NOT NULL UNIQUE,
            password_hash VARCHAR(255) NOT NULL,
            last_login_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci'
    );

    $database->exec(
        'CREATE TABLE IF NOT EXISTS contact_messages (
            id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            name VARCHAR(255) NOT NULL,
            surname VARCHAR(255) NOT NULL,
            email VARCHAR(255) NOT NULL,
            subject VARCHAR(255),
            message TEXT,
            created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci'
    );
}
