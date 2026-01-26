<?php

declare(strict_types=1);

require_once __DIR__ . '/includes/database.php';

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

// Database helpers are now shared via includes/database.php

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
