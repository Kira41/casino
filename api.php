<?php

declare(strict_types=1);

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    respond(['success' => false, 'message' => 'Only POST requests are allowed.'], 405);
}

$rawInput = file_get_contents('php://input') ?: '';
$payload = json_decode($rawInput, true);

if (!is_array($payload)) {
    $payload = $_POST ?? [];
}

$action = isset($payload['action']) ? (string) $payload['action'] : '';

if ($action === '') {
    respond(['success' => false, 'message' => 'An action is required.'], 400);
}

try {
    $database = openDatabase();
} catch (Throwable $error) {
    respond([
        'success' => false,
        'message' => 'Database unavailable: ' . $error->getMessage(),
    ], 500);
}

try {
    switch ($action) {
        case 'subscribe':
            handleSubscribe($database, $payload);
            break;
        case 'signin':
            handleSignin($database, $payload);
            break;
        case 'contact':
            handleContact($database, $payload);
            break;
        default:
            respond(['success' => false, 'message' => 'Unknown action requested.'], 400);
    }
} catch (InvalidArgumentException $validationError) {
    respond(['success' => false, 'message' => $validationError->getMessage()], 422);
} catch (Throwable $error) {
    respond([
        'success' => false,
        'message' => 'Unexpected server error: ' . $error->getMessage(),
    ], 500);
}

function openDatabase(): PDO
{
    $configPath = __DIR__ . DIRECTORY_SEPARATOR . 'config.php';
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

    $schemaPath = __DIR__ . DIRECTORY_SEPARATOR . 'storage' . DIRECTORY_SEPARATOR . $schemaFilename;
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

function handleSubscribe(PDO $database, array $payload): void
{
    $email = filter_var(trim((string) ($payload['email'] ?? '')), FILTER_VALIDATE_EMAIL);

    if ($email === false) {
        throw new InvalidArgumentException('Please provide a valid email address.');
    }

    $database->beginTransaction();

    try {
        $statement = $database->prepare(
            'INSERT INTO subscriptions (email) VALUES (:email)
            ON DUPLICATE KEY UPDATE updated_at = VALUES(updated_at)'
        );

        $statement->execute([':email' => $email]);

        if ($statement->rowCount() < 1) {
            throw new RuntimeException('Subscription could not be saved. Please try again.');
        }

        $subscriptionLookup = $database->prepare(
            'SELECT id FROM subscriptions WHERE email = :email LIMIT 1'
        );
        $subscriptionLookup->execute([':email' => $email]);
        $subscriptionId = (int) $subscriptionLookup->fetchColumn();

        $database->commit();
    } catch (Throwable $error) {
        if ($database->inTransaction()) {
            $database->rollBack();
        }

        throw $error;
    }

    respond([
        'success' => true,
        'message' => 'Subscription saved successfully.',
        'subscription_id' => $subscriptionId,
    ]);
}

function handleSignin(PDO $database, array $payload): void
{
    $email = filter_var(trim((string) ($payload['email'] ?? '')), FILTER_VALIDATE_EMAIL);
    $password = trim((string) ($payload['password'] ?? ''));

    if ($email === false) {
        throw new InvalidArgumentException('Please enter a valid email to sign in.');
    }

    if (strlen($password) < 6) {
        throw new InvalidArgumentException('Password must be at least 6 characters long.');
    }

    $passwordHash = password_hash($password, PASSWORD_BCRYPT);

    $statement = $database->prepare(
        'INSERT INTO signins (email, password_hash, last_login_at) VALUES (:email, :password_hash, CURRENT_TIMESTAMP)
        ON DUPLICATE KEY UPDATE password_hash = VALUES(password_hash), last_login_at = CURRENT_TIMESTAMP'
    );

    $statement->execute([
        ':email' => $email,
        ':password_hash' => $passwordHash,
    ]);

    respond([
        'success' => true,
        'message' => 'Sign-in recorded and synced.',
    ]);
}

function handleContact(PDO $database, array $payload): void
{
    $name = trim((string) ($payload['name'] ?? ''));
    $surname = trim((string) ($payload['surname'] ?? ''));
    $email = filter_var(trim((string) ($payload['email'] ?? '')), FILTER_VALIDATE_EMAIL);
    $subject = trim((string) ($payload['subject'] ?? ''));
    $message = trim((string) ($payload['message'] ?? ''));

    if ($name === '' || $surname === '') {
        throw new InvalidArgumentException('Please include your first and last name.');
    }

    if ($email === false) {
        throw new InvalidArgumentException('Please provide a valid email address.');
    }

    if ($message === '') {
        throw new InvalidArgumentException('A message is required.');
    }

    $statement = $database->prepare(
        'INSERT INTO contact_messages (name, surname, email, subject, message)
        VALUES (:name, :surname, :email, :subject, :message)'
    );

    $statement->execute([
        ':name' => $name,
        ':surname' => $surname,
        ':email' => $email,
        ':subject' => $subject,
        ':message' => $message,
    ]);

    respond([
        'success' => true,
        'message' => 'Your message has been stored. Our team will follow up soon.',
    ]);
}

function respond(array $payload, int $statusCode = 200): void
{
    http_response_code($statusCode);
    echo json_encode($payload);
    exit;
}
