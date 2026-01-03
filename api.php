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
    $storageDir = __DIR__ . DIRECTORY_SEPARATOR . 'storage';

    if (!is_dir($storageDir) && !mkdir($storageDir, 0775, true) && !is_dir($storageDir)) {
        throw new RuntimeException('Unable to create storage directory.');
    }

    $databasePath = $storageDir . DIRECTORY_SEPARATOR . 'casino.sqlite';
    $database = new PDO('sqlite:' . $databasePath);
    $database->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $database->exec('PRAGMA foreign_keys = ON');

    initializeTables($database);

    return $database;
}

function initializeTables(PDO $database): void
{
    $database->exec(
        'CREATE TABLE IF NOT EXISTS subscriptions (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            email TEXT NOT NULL UNIQUE,
            created_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP,
            updated_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP
        )'
    );

    $database->exec(
        'CREATE TABLE IF NOT EXISTS signins (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            email TEXT NOT NULL UNIQUE,
            password_hash TEXT NOT NULL,
            last_login_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP,
            created_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP
        )'
    );

    $database->exec(
        'CREATE TABLE IF NOT EXISTS contact_messages (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            name TEXT NOT NULL,
            surname TEXT NOT NULL,
            email TEXT NOT NULL,
            subject TEXT,
            message TEXT,
            created_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP
        )'
    );
}

function handleSubscribe(PDO $database, array $payload): void
{
    $email = filter_var(trim((string) ($payload['email'] ?? '')), FILTER_VALIDATE_EMAIL);

    if ($email === false) {
        throw new InvalidArgumentException('Please provide a valid email address.');
    }

    $statement = $database->prepare(
        'INSERT INTO subscriptions (email) VALUES (:email)
        ON CONFLICT(email) DO UPDATE SET updated_at = CURRENT_TIMESTAMP'
    );

    $statement->execute([':email' => $email]);

    respond([
        'success' => true,
        'message' => 'Subscription saved to the SQL database.',
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
        ON CONFLICT(email) DO UPDATE SET password_hash = :password_hash, last_login_at = CURRENT_TIMESTAMP'
    );

    $statement->execute([
        ':email' => $email,
        ':password_hash' => $passwordHash,
    ]);

    respond([
        'success' => true,
        'message' => 'Sign-in recorded and synced with the SQL database.',
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
        'message' => 'Your message has been stored in the SQL database. Our PHP backend will follow up soon.',
    ]);
}

function respond(array $payload, int $statusCode = 200): void
{
    http_response_code($statusCode);
    echo json_encode($payload);
    exit;
}
