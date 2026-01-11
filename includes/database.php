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

    $schemaInitialized = isSchemaInitialized($database);

    if (!$schemaInitialized) {
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

    ensureCasinoCategorySeeds($database);
    ensureTopCasinoColumn($database);
    ensureCasinoProviderLinks($database);
    ensureCasinoDevices($database);
    ensureContentCardUniqueness($database);
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

    if ($driver === 'mysql') {
        $tableLike = $database->quote($table);
        if ($tableLike === false) {
            return false;
        }

        $statement = $database->query("SHOW TABLES LIKE {$tableLike}");
        return (bool) $statement->fetchColumn();
    }

    $statement = $database->prepare('SELECT 1 FROM information_schema.tables WHERE table_name = :table LIMIT 1');
    $statement->execute([':table' => $table]);
    return (bool) $statement->fetchColumn();
}

function columnExists(PDO $database, string $table, string $column): bool
{
    $driver = $database->getAttribute(PDO::ATTR_DRIVER_NAME);

    if ($driver === 'sqlite') {
        $statement = $database->prepare('PRAGMA table_info(' . $table . ')');
        $statement->execute();
        $columns = $statement->fetchAll(PDO::FETCH_ASSOC) ?: [];
        foreach ($columns as $columnInfo) {
            if (($columnInfo['name'] ?? null) === $column) {
                return true;
            }
        }
        return false;
    }

    if ($driver === 'mysql') {
        $columnLike = $database->quote($column);
        if ($columnLike === false) {
            return false;
        }

        $statement = $database->query("SHOW COLUMNS FROM `{$table}` LIKE {$columnLike}");
        return (bool) $statement->fetchColumn();
    }

    $statement = $database->prepare(
        'SELECT 1 FROM information_schema.columns WHERE table_name = :table AND column_name = :column LIMIT 1'
    );
    $statement->execute([':table' => $table, ':column' => $column]);
    return (bool) $statement->fetchColumn();
}

function indexExists(PDO $database, string $table, string $index): bool
{
    $driver = $database->getAttribute(PDO::ATTR_DRIVER_NAME);

    if ($driver === 'sqlite') {
        $statement = $database->prepare('PRAGMA index_list(' . $table . ')');
        $statement->execute();
        $indexes = $statement->fetchAll(PDO::FETCH_ASSOC) ?: [];
        foreach ($indexes as $indexInfo) {
            if (($indexInfo['name'] ?? null) === $index) {
                return true;
            }
        }
        return false;
    }

    if ($driver === 'mysql') {
        $indexLike = $database->quote($index);
        if ($indexLike === false) {
            return false;
        }

        $statement = $database->query("SHOW INDEX FROM `{$table}` WHERE Key_name = {$indexLike}");
        return (bool) $statement->fetchColumn();
    }

    $statement = $database->prepare(
        'SELECT 1 FROM information_schema.statistics WHERE table_name = :table AND index_name = :index LIMIT 1'
    );
    $statement->execute([':table' => $table, ':index' => $index]);
    return (bool) $statement->fetchColumn();
}

function ensureTopCasinoColumn(PDO $database): void
{
    if (!tableExists($database, 'casinos')) {
        return;
    }

    if (columnExists($database, 'casinos', 'is_top1')) {
        return;
    }

    $driver = $database->getAttribute(PDO::ATTR_DRIVER_NAME);
    $columnType = $driver === 'sqlite' ? 'INTEGER' : 'TINYINT(1)';

    $database->exec(sprintf(
        'ALTER TABLE casinos ADD COLUMN is_top1 %s NOT NULL DEFAULT 0',
        $columnType
    ));
}

function ensureCasinoProviderLinks(PDO $database): void
{
    if (tableExists($database, 'casino_provider_links')) {
        return;
    }

    $database->exec(
        'CREATE TABLE IF NOT EXISTS casino_provider_links (
            id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            casino_id BIGINT UNSIGNED NOT NULL,
            provider_id BIGINT UNSIGNED NOT NULL,
            UNIQUE KEY casino_provider_unique (casino_id, provider_id),
            FOREIGN KEY (casino_id) REFERENCES casinos(id) ON DELETE CASCADE,
            FOREIGN KEY (provider_id) REFERENCES providers(id) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci'
    );
}

function ensureCasinoDevices(PDO $database): void
{
    if (tableExists($database, 'casino_devices')) {
        return;
    }

    $database->exec(
        'CREATE TABLE IF NOT EXISTS casino_devices (
            id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            casino_id BIGINT UNSIGNED NOT NULL,
            device_group VARCHAR(50) NOT NULL,
            device_key VARCHAR(50) NOT NULL,
            FOREIGN KEY (casino_id) REFERENCES casinos(id) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci'
    );
}

function ensureContentCardUniqueness(PDO $database): void
{
    if (!tableExists($database, 'content_cards')) {
        return;
    }

    $indexName = 'uniq_content_cards_section_position_title';

    if ($database->getAttribute(PDO::ATTR_DRIVER_NAME) === 'mysql') {
        deleteDuplicateRows($database, 'content_cards', ['section', 'position', 'title']);
    }

    if (indexExists($database, 'content_cards', $indexName)) {
        return;
    }

    $database->exec(sprintf(
        'CREATE UNIQUE INDEX %s ON content_cards(section, position, title)',
        $indexName
    ));
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

function ensureCasinoCategorySeeds(PDO $database): void
{
    static $categoriesSeeded = false;

    if ($categoriesSeeded) {
        return;
    }

    $categoriesSeeded = true;

    if (!tableExists($database, 'casino_tags') || !tableExists($database, 'casino_tag_links') || !tableExists($database, 'casinos')) {
        return;
    }

    $categoryNames = [
        'Crypto Casinos',
        'Fast Payouts',
        'Low Deposit',
        'High Roller',
        'Live Dealer',
        'Mobile Friendly',
    ];

    $driver = $database->getAttribute(PDO::ATTR_DRIVER_NAME);
    $insertIgnore = $driver === 'mysql' ? 'INSERT IGNORE INTO' : 'INSERT OR IGNORE INTO';

    $insertTagStatement = $database->prepare("{$insertIgnore} casino_tags (name, type) VALUES (:name, :type)");
    foreach ($categoryNames as $categoryName) {
        $insertTagStatement->execute([':name' => $categoryName, ':type' => 'category']);
    }

    $casinoCategoryMap = [
        'lucky-star-crypto-casino' => ['Crypto Casinos', 'Live Dealer', 'Fast Payouts', 'Mobile Friendly'],
        'nova-royale-casino' => ['Low Deposit', 'Fast Payouts', 'Mobile Friendly'],
        'starlight-spins-resort' => ['Live Dealer', 'High Roller'],
        'emerald-mirage-club' => ['High Roller', 'Fast Payouts'],
        'celestial-fortune-hall' => ['High Roller', 'Live Dealer'],
        'aurora-vault-casino' => ['Fast Payouts', 'Mobile Friendly'],
        'quantum-spin-lounge' => ['Mobile Friendly', 'Fast Payouts'],
        'imperial-halo-casino' => ['High Roller', 'Live Dealer'],
        'obsidian-crown-club' => ['Crypto Casinos', 'High Roller'],
        'mirage-of-millions' => ['High Roller', 'Live Dealer'],
        'luminous-ledger-casino' => ['Fast Payouts', 'Low Deposit'],
        'neon-mirage-casino' => ['Crypto Casinos', 'Live Dealer'],
        'azure-spire-casino' => ['Live Dealer', 'High Roller'],
        'lucky-horizon-lounge' => ['Low Deposit', 'Mobile Friendly'],
        'starlit-crown-casino' => ['High Roller', 'Fast Payouts'],
        'golden-drift-resort' => ['Live Dealer', 'Low Deposit'],
    ];

    $fetchCasinoId = $database->prepare('SELECT id FROM casinos WHERE slug = :slug LIMIT 1');
    $fetchTagId = $database->prepare('SELECT id FROM casino_tags WHERE name = :name AND type = :type LIMIT 1');
    $insertTagLink = $database->prepare("{$insertIgnore} casino_tag_links (casino_id, tag_id, is_primary) VALUES (:casino_id, :tag_id, :is_primary)");

    foreach ($casinoCategoryMap as $casinoSlug => $categories) {
        $fetchCasinoId->execute([':slug' => $casinoSlug]);
        $casinoId = $fetchCasinoId->fetchColumn();

        if ($casinoId === false) {
            continue;
        }

        foreach (array_values($categories) as $index => $categoryName) {
            $fetchTagId->execute([':name' => $categoryName, ':type' => 'category']);
            $tagId = $fetchTagId->fetchColumn();

            if ($tagId === false) {
                continue;
            }

            $insertTagLink->execute([
                ':casino_id' => $casinoId,
                ':tag_id' => $tagId,
                ':is_primary' => $index === 0 ? 1 : 0,
            ]);
        }
    }
}
