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
    ensureMetadataTable($database);

    if (isSchemaInitialized($database)) {
        return;
    }

    $schemaFilename = $database->getAttribute(PDO::ATTR_DRIVER_NAME) === 'mysql'
        ? 'database.mysql.sql'
        : 'database.sql';

    $schemaPath = __DIR__ . '/../storage/' . $schemaFilename;
    $schemaSql = is_file($schemaPath) ? file_get_contents($schemaPath) : false;

    if ($schemaSql !== false) {
        $database->exec($schemaSql);
    } else {
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

    removeDuplicateSeedData($database);
    markSchemaInitialized($database);
}

function ensureMetadataTable(PDO $database): void
{
    $database->exec(
        'CREATE TABLE IF NOT EXISTS schema_metadata (
            id INTEGER PRIMARY KEY,
            seed_version INTEGER NOT NULL DEFAULT 1,
            initialized_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
        )'
    );
}

function isSchemaInitialized(PDO $database): bool
{
    if (!tableExists($database, 'schema_metadata')) {
        return false;
    }

    $statement = $database->query('SELECT COUNT(*) FROM schema_metadata WHERE id = 1');
    return (int) $statement->fetchColumn() > 0;
}

function markSchemaInitialized(PDO $database): void
{
    $database->exec('INSERT INTO schema_metadata (id, seed_version) VALUES (1, 1)');
}

function tableExists(PDO $database, string $table): bool
{
    $driver = $database->getAttribute(PDO::ATTR_DRIVER_NAME);

    if ($driver === 'sqlite') {
        $statement = $database->prepare('SELECT name FROM sqlite_master WHERE type = :type AND name = :table LIMIT 1');
        $statement->execute([':type' => 'table', ':table' => $table]);
        return (bool) $statement->fetchColumn();
    }

    $statement = $database->prepare('SHOW TABLES LIKE :table');
    $statement->execute([':table' => $table]);
    return (bool) $statement->fetchColumn();
}

function removeDuplicateSeedData(PDO $database): void
{
    if ($database->getAttribute(PDO::ATTR_DRIVER_NAME) !== 'mysql') {
        return;
    }

    $targets = [
        'casino_cards' => ['casino_id', 'section', 'position'],
        'content_cards' => ['section', 'position', 'title'],
        'category_cards' => ['section', 'title'],
        'casino_game_modes' => ['casino_id', 'game_type'],
        'casino_review_sections' => ['casino_id', 'title'],
        'casino_review_points' => ['review_section_id', 'icon', 'content'],
        'casino_pros_cons' => ['casino_id', 'type', 'content'],
        'casino_highlights' => ['casino_id', 'label', 'icon'],
    ];

    foreach ($targets as $table => $uniqueColumns) {
        if (!tableExists($database, $table)) {
            continue;
        }

        deleteDuplicateRows($database, $table, $uniqueColumns);
    }
}

function deleteDuplicateRows(PDO $database, string $table, array $uniqueColumns): void
{
    if ($uniqueColumns === []) {
        return;
    }

    $conditions = array_map(
        static fn(string $column): string => sprintf('t1.%1$s <=> t2.%1$s', $column),
        $uniqueColumns
    );

    $sql = sprintf(
        'DELETE t1 FROM `%1$s` t1 INNER JOIN `%1$s` t2 ON t1.id > t2.id AND %2$s',
        $table,
        implode(' AND ', $conditions)
    );

    $database->exec($sql);
}
