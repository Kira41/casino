<?php

declare(strict_types=1);

session_start();

require_once __DIR__ . '/includes/bootstrap.php';

$database = getDatabase();
$pageTitle = 'Casino Admin';

const ADMIN_USER = 'shiva';
const ADMIN_PASS = 'Shiva@41';

$featuredSections = [
    'top_1' => [
        'label' => 'Top 1 Casino',
        'slots' => 1,
    ],
    'hot_picks' => [
        'label' => 'Hot Picks',
        'slots' => 4,
    ],
    'most_played' => [
        'label' => 'Top Casinos',
        'slots' => 6,
    ],
];

$gameTypeOptions = [
    ['label' => 'Roulette'],
    ['label' => 'Slots'],
    ['label' => 'Blackjack'],
    ['label' => 'Video Poker'],
    ['label' => 'Scratch Cards'],
    ['label' => 'Keno'],
    ['label' => 'Craps'],
    ['label' => 'Bingo'],
    ['label' => 'Baccarat'],
];
$deviceSupportCatalog = getDeviceSupportCatalog();

$loginError = '';
$actionMessage = '';
$actionError = '';

if (isset($_POST['action']) && $_POST['action'] === 'logout') {
    $_SESSION = [];
    session_destroy();
    header('Location: admin.php');
    exit;
}

if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    if (isset($_POST['action']) && $_POST['action'] === 'login') {
        $username = isset($_POST['username']) ? trim((string) $_POST['username']) : '';
        $password = isset($_POST['password']) ? (string) $_POST['password'] : '';

        if ($username === ADMIN_USER && $password === ADMIN_PASS) {
            $_SESSION['admin_logged_in'] = true;
            session_regenerate_id(true);
            header('Location: admin.php');
            exit;
        }

        $loginError = 'Invalid credentials. Please try again.';
    }

    include __DIR__ . '/partials/html-head.php';
    ?>
    <div class="page-heading header-text">
        <div class="container">
            <div class="row">
                <div class="col-lg-12">
                    <h3>Admin Login</h3>
                    <span class="breadcrumb"><a href="index.php">Home</a>  >  <span>Admin Login</span></span>
                </div>
            </div>
        </div>
    </div>

    <div class="section admin-login">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-lg-5">
                    <div class="card shadow-sm admin-card">
                        <div class="card-body">
                            <h5 class="card-title mb-3">Sign in to manage casinos</h5>
                            <?php if ($loginError !== ''): ?>
                                <div class="alert alert-danger" role="alert"><?= htmlspecialchars($loginError, ENT_QUOTES, 'UTF-8') ?></div>
                            <?php endif; ?>
                            <form method="post">
                                <input type="hidden" name="action" value="login">
                                <div class="mb-3">
                                    <label for="username" class="form-label">Username</label>
                                    <input type="text" class="form-control" id="username" name="username" required>
                                </div>
                                <div class="mb-3">
                                    <label for="password" class="form-label">Password</label>
                                    <input type="password" class="form-control" id="password" name="password" required>
                                </div>
                                <button type="submit" class="btn btn-brand w-100">Login</button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php
    include __DIR__ . '/partials/footer.php';
    exit;
}

function slugifyValue(string $value): string
{
    return slugifyTag($value);
}

function normalizeTagList(string $raw): array
{
    $tags = array_map('trim', explode(',', $raw));
    $tags = array_filter($tags, static fn(string $tag): bool => $tag !== '');
    return array_values(array_unique($tags));
}

function normalizeGameTypeKey(string $value): string
{
    $slug = strtolower(trim($value));
    $slug = preg_replace('/[^a-z0-9]+/', '-', $slug) ?? '';
    return trim($slug, '-');
}

function insertIgnoreKeyword(PDO $database): string
{
    $driver = $database->getAttribute(PDO::ATTR_DRIVER_NAME);
    return $driver === 'sqlite' ? 'INSERT OR IGNORE INTO' : 'INSERT IGNORE INTO';
}

function updateCasinoTags(PDO $database, int $casinoId, string $type, array $tags): void
{
    $driver = $database->getAttribute(PDO::ATTR_DRIVER_NAME);
    if ($driver === 'sqlite') {
        $deleteStatement = $database->prepare(
            'DELETE FROM casino_tag_links WHERE casino_id = :casino_id AND tag_id IN (SELECT id FROM casino_tags WHERE type = :type)'
        );
    } else {
        $deleteStatement = $database->prepare(
            'DELETE l FROM casino_tag_links l INNER JOIN casino_tags t ON t.id = l.tag_id WHERE l.casino_id = :casino_id AND t.type = :type'
        );
    }
    $deleteStatement->execute([':casino_id' => $casinoId, ':type' => $type]);

    $insertTagSql = sprintf('%s casino_tags (name, type) VALUES (:name, :type)', insertIgnoreKeyword($database));
    $insertTagStatement = $database->prepare($insertTagSql);
    $fetchTagStatement = $database->prepare('SELECT id FROM casino_tags WHERE name = :name AND type = :type LIMIT 1');
    $insertLinkStatement = $database->prepare(
        'INSERT INTO casino_tag_links (casino_id, tag_id, is_primary) VALUES (:casino_id, :tag_id, :is_primary)'
    );

    foreach ($tags as $index => $tag) {
        $insertTagStatement->execute([':name' => $tag, ':type' => $type]);
        $fetchTagStatement->execute([':name' => $tag, ':type' => $type]);
        $tagId = $fetchTagStatement->fetchColumn();

        if ($tagId === false) {
            continue;
        }

        $insertLinkStatement->execute([
            ':casino_id' => $casinoId,
            ':tag_id' => (int) $tagId,
            ':is_primary' => $index === 0 ? 1 : 0,
        ]);
    }
}

function updateCasinoPaymentMethods(PDO $database, int $casinoId, array $paymentMethodIds): void
{
    if (!tableExists($database, 'casino_payment_methods') || !tableExists($database, 'payment_methods')) {
        return;
    }

    $deleteStatement = $database->prepare('DELETE FROM casino_payment_methods WHERE casino_id = :casino_id');
    $deleteStatement->execute([':casino_id' => $casinoId]);

    if ($paymentMethodIds === []) {
        return;
    }

    $placeholders = implode(',', array_fill(0, count($paymentMethodIds), '?'));
    $fetchStatement = $database->prepare(
        "SELECT name, image_path FROM payment_methods WHERE id IN ({$placeholders}) ORDER BY id ASC"
    );
    $fetchStatement->execute($paymentMethodIds);

    $insertStatement = $database->prepare(
        'INSERT INTO casino_payment_methods (casino_id, method_name, icon_key) VALUES (:casino_id, :method_name, :icon_key)'
    );

    foreach ($fetchStatement->fetchAll(PDO::FETCH_ASSOC) ?: [] as $method) {
        $insertStatement->execute([
            ':casino_id' => $casinoId,
            ':method_name' => $method['name'],
            ':icon_key' => $method['image_path'],
        ]);
    }
}

function updateCasinoProviders(PDO $database, int $casinoId, array $providerIds): void
{
    if (!tableExists($database, 'casino_provider_links')) {
        return;
    }

    $deleteStatement = $database->prepare('DELETE FROM casino_provider_links WHERE casino_id = :casino_id');
    $deleteStatement->execute([':casino_id' => $casinoId]);

    if ($providerIds === []) {
        return;
    }

    $insertStatement = $database->prepare(
        'INSERT INTO casino_provider_links (casino_id, provider_id) VALUES (:casino_id, :provider_id)'
    );

    foreach ($providerIds as $providerId) {
        $insertStatement->execute([
            ':casino_id' => $casinoId,
            ':provider_id' => $providerId,
        ]);
    }
}

function updateCasinoGameModes(PDO $database, int $casinoId, array $gameModes): void
{
    if (!tableExists($database, 'casino_game_modes')) {
        return;
    }

    $deleteStatement = $database->prepare('DELETE FROM casino_game_modes WHERE casino_id = :casino_id');
    $deleteStatement->execute([':casino_id' => $casinoId]);

    if ($gameModes === []) {
        return;
    }

    $insertStatement = $database->prepare(
        'INSERT INTO casino_game_modes (casino_id, game_type, live_dealer_supported, virtual_reality_supported)
        VALUES (:casino_id, :game_type, :live_dealer_supported, :virtual_reality_supported)'
    );

    foreach ($gameModes as $mode) {
        $gameType = trim((string) ($mode['game_type'] ?? ''));
        if ($gameType === '') {
            continue;
        }
        $insertStatement->execute([
            ':casino_id' => $casinoId,
            ':game_type' => $gameType,
            ':live_dealer_supported' => !empty($mode['live_dealer_supported']) ? 1 : 0,
            ':virtual_reality_supported' => !empty($mode['virtual_reality_supported']) ? 1 : 0,
        ]);
    }
}

function updateCasinoProsCons(PDO $database, int $casinoId, array $pros, array $cons): void
{
    if (!tableExists($database, 'casino_pros_cons')) {
        return;
    }

    $deleteStatement = $database->prepare('DELETE FROM casino_pros_cons WHERE casino_id = :casino_id');
    $deleteStatement->execute([':casino_id' => $casinoId]);

    $insertStatement = $database->prepare(
        'INSERT INTO casino_pros_cons (casino_id, type, content) VALUES (:casino_id, :type, :content)'
    );

    foreach (['pro' => $pros, 'con' => $cons] as $type => $items) {
        foreach ($items as $item) {
            $content = trim((string) $item);
            if ($content === '') {
                continue;
            }
            $insertStatement->execute([
                ':casino_id' => $casinoId,
                ':type' => $type,
                ':content' => $content,
            ]);
        }
    }
}

function updateCasinoDevices(PDO $database, int $casinoId, array $devices): void
{
    if (!tableExists($database, 'casino_devices')) {
        return;
    }

    $deleteStatement = $database->prepare('DELETE FROM casino_devices WHERE casino_id = :casino_id');
    $deleteStatement->execute([':casino_id' => $casinoId]);

    if ($devices === []) {
        return;
    }

    $insertStatement = $database->prepare(
        'INSERT INTO casino_devices (casino_id, device_group, device_key) VALUES (:casino_id, :device_group, :device_key)'
    );

    foreach ($devices as $groupKey => $deviceKeys) {
        foreach ($deviceKeys as $deviceKey) {
            $insertStatement->execute([
                ':casino_id' => $casinoId,
                ':device_group' => $groupKey,
                ':device_key' => $deviceKey,
            ]);
        }
    }
}

function updateCasinoReviewSectionPoints(PDO $database, int $casinoId, string $title, array $points, ?string $summary = null): void
{
    if (!tableExists($database, 'casino_review_sections') || !tableExists($database, 'casino_review_points')) {
        return;
    }

    $selectStatement = $database->prepare(
        'SELECT id FROM casino_review_sections WHERE casino_id = :casino_id AND title = :title LIMIT 1'
    );
    $selectStatement->execute([':casino_id' => $casinoId, ':title' => $title]);
    $sectionId = $selectStatement->fetchColumn();

    if ($sectionId === false) {
        $insertStatement = $database->prepare(
            'INSERT INTO casino_review_sections (casino_id, title, summary) VALUES (:casino_id, :title, :summary)'
        );
        $insertStatement->execute([
            ':casino_id' => $casinoId,
            ':title' => $title,
            ':summary' => $summary ?? '',
        ]);
        $sectionId = (int) $database->lastInsertId();
    } else {
        $sectionId = (int) $sectionId;
        if ($summary !== null) {
            $updateStatement = $database->prepare(
                'UPDATE casino_review_sections SET summary = :summary WHERE id = :id'
            );
            $updateStatement->execute([
                ':summary' => $summary,
                ':id' => $sectionId,
            ]);
        }
    }

    $deleteStatement = $database->prepare('DELETE FROM casino_review_points WHERE review_section_id = :section_id');
    $deleteStatement->execute([':section_id' => $sectionId]);

    if ($points === []) {
        return;
    }

    $insertPointStatement = $database->prepare(
        'INSERT INTO casino_review_points (review_section_id, icon, content) VALUES (:review_section_id, :icon, :content)'
    );

    foreach ($points as $point) {
        $content = trim((string) ($point['content'] ?? ''));
        if ($content === '') {
            continue;
        }
        $icon = trim((string) ($point['icon'] ?? ''));
        $insertPointStatement->execute([
            ':review_section_id' => $sectionId,
            ':icon' => $icon,
            ':content' => $content,
        ]);
    }
}

function seedCasinoReviewSections(PDO $database, int $casinoId, string $casinoName): void
{
    if (!tableExists($database, 'casino_review_sections')) {
        return;
    }

    $defaults = [
        'Banking Methods' => 'Payment options overview for deposits and withdrawals.',
        'General Information' => sprintf('Snapshot of %s including operator, licensing, and key requirements.', $casinoName),
        'Support' => 'Support channels, response times, and help center highlights.',
        'Devices' => 'Supported device options for playing on mobile and desktop.',
        'Software Providers' => 'Curated studio mix delivering slots, tables, and live dealer experiences.',
        'Additional Info' => 'Extra notes on promotions, security, and responsible play features.',
    ];

    $checkStatement = $database->prepare(
        'SELECT id FROM casino_review_sections WHERE casino_id = :casino_id AND title = :title LIMIT 1'
    );
    $insertStatement = $database->prepare(
        'INSERT INTO casino_review_sections (casino_id, title, summary) VALUES (:casino_id, :title, :summary)'
    );

    foreach ($defaults as $title => $summary) {
        $checkStatement->execute([':casino_id' => $casinoId, ':title' => $title]);
        if ($checkStatement->fetchColumn() !== false) {
            continue;
        }

        $insertStatement->execute([
            ':casino_id' => $casinoId,
            ':title' => $title,
            ':summary' => $summary,
        ]);
    }
}

function handleImageUpload(string $fieldName, string $slug, array &$errors): ?string
{
    if (!isset($_FILES[$fieldName]) || !is_array($_FILES[$fieldName])) {
        return null;
    }

    $file = $_FILES[$fieldName];

    if ($file['error'] === UPLOAD_ERR_NO_FILE) {
        return null;
    }

    if ($file['error'] !== UPLOAD_ERR_OK) {
        $errors[] = 'Image upload failed. Please try again.';
        return null;
    }

    $imageInfo = getimagesize($file['tmp_name']);
    if ($imageInfo === false) {
        $errors[] = 'Uploaded file is not a valid image.';
        return null;
    }

    $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
    $extension = strtolower($extension);
    $allowed = ['jpg', 'jpeg', 'png', 'webp'];
    if (!in_array($extension, $allowed, true)) {
        $errors[] = 'Only JPG, PNG, or WebP images are allowed.';
        return null;
    }

    $targetDir = __DIR__ . '/assets/images/casinos';
    if (!is_dir($targetDir)) {
        mkdir($targetDir, 0775, true);
    }

    $safeSlug = slugifyValue($slug);
    $filename = sprintf('%s-%s.%s', $safeSlug, bin2hex(random_bytes(4)), $extension);
    $targetPath = $targetDir . '/' . $filename;

    if (!move_uploaded_file($file['tmp_name'], $targetPath)) {
        $errors[] = 'Unable to save uploaded image.';
        return null;
    }

    return 'assets/images/casinos/' . $filename;
}

function handleCatalogImageUpload(string $fieldName, string $slug, string $folder, array &$errors): ?string
{
    if (!isset($_FILES[$fieldName]) || !is_array($_FILES[$fieldName])) {
        return null;
    }

    $file = $_FILES[$fieldName];

    if ($file['error'] === UPLOAD_ERR_NO_FILE) {
        return null;
    }

    if ($file['error'] !== UPLOAD_ERR_OK) {
        $errors[] = 'Image upload failed. Please try again.';
        return null;
    }

    $imageInfo = getimagesize($file['tmp_name']);
    if ($imageInfo === false) {
        $errors[] = 'Uploaded file is not a valid image.';
        return null;
    }

    $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
    $extension = strtolower($extension);
    $allowed = ['jpg', 'jpeg', 'png', 'webp', 'svg'];
    if (!in_array($extension, $allowed, true)) {
        $errors[] = 'Only JPG, PNG, SVG, or WebP images are allowed.';
        return null;
    }

    $targetDir = __DIR__ . '/assets/images/' . $folder;
    if (!is_dir($targetDir)) {
        mkdir($targetDir, 0775, true);
    }

    $safeSlug = slugifyValue($slug);
    $filename = sprintf('%s-%s.%s', $safeSlug, bin2hex(random_bytes(4)), $extension);
    $targetPath = $targetDir . '/' . $filename;

    if (!move_uploaded_file($file['tmp_name'], $targetPath)) {
        $errors[] = 'Unable to save uploaded image.';
        return null;
    }

    return 'assets/images/' . $folder . '/' . $filename;
}

function fetchFeaturedSectionSelections(PDO $database, string $section): array
{
    $statement = $database->prepare(
        'SELECT casino_id, position FROM casino_cards WHERE section = :section ORDER BY position ASC, id ASC'
    );
    $statement->execute([':section' => $section]);

    $selections = [];
    foreach ($statement->fetchAll(PDO::FETCH_ASSOC) ?: [] as $row) {
        $position = (int) ($row['position'] ?? 0);
        $casinoId = (int) ($row['casino_id'] ?? 0);
        if ($position > 0 && $casinoId > 0) {
            $selections[$position] = $casinoId;
        }
    }

    return $selections;
}

function fetchCasinoCardSources(PDO $database, array $casinoIds): array
{
    if ($casinoIds === []) {
        return [];
    }

    $placeholders = implode(',', array_fill(0, count($casinoIds), '?'));
    $statement = $database->prepare(
        "SELECT id, name, thumbnail_image, hero_image, rating, min_deposit_usd FROM casinos WHERE id IN ({$placeholders})"
    );
    $statement->execute(array_values($casinoIds));

    $sources = [];
    foreach ($statement->fetchAll(PDO::FETCH_ASSOC) ?: [] as $row) {
        $sources[(int) $row['id']] = $row;
    }

    return $sources;
}

function normalizeReviewSectionKey(string $title): string
{
    $key = strtolower(trim($title));
    $key = preg_replace('/[^a-z0-9]+/', '-', $key) ?? '';
    $key = trim($key, '-');
    $aliases = [
        'additional-info' => 'additional-info',
        'additional-information' => 'additional-info',
        'support' => 'support',
    ];

    return $aliases[$key] ?? $key;
}

function mapReviewSectionsByKey(array $sections): array
{
    $mapped = [];
    foreach ($sections as $section) {
        $title = (string) ($section['title'] ?? '');
        if ($title === '') {
            continue;
        }
        $key = normalizeReviewSectionKey($title);
        if ($key === '' || isset($mapped[$key])) {
            continue;
        }
        $mapped[$key] = $section;
    }

    return $mapped;
}

if (isset($_POST['action']) && $_POST['action'] === 'save_casino') {
    $casinoId = isset($_POST['casino_id']) ? (int) $_POST['casino_id'] : 0;
    $existingCasino = $casinoId > 0 ? fetchCasinoById($database, $casinoId) : null;
    $name = isset($_POST['name']) ? trim((string) $_POST['name']) : '';
    $slug = isset($_POST['slug']) ? trim((string) $_POST['slug']) : '';
    $operator = isset($_POST['operator']) ? trim((string) $_POST['operator']) : '';
    $license = isset($_POST['license']) ? trim((string) $_POST['license']) : '';
    $headlineBonus = isset($_POST['headline_bonus']) ? trim((string) $_POST['headline_bonus']) : '';
    $minDepositRaw = isset($_POST['min_deposit_usd']) ? trim((string) $_POST['min_deposit_usd']) : '';
    $minDeposit = $minDepositRaw === '' ? null : (int) $minDepositRaw;
    $rating = isset($_POST['rating']) ? (int) $_POST['rating'] : 0;
    $shortDescription = isset($_POST['short_description']) ? trim((string) $_POST['short_description']) : '';
    $ctaUrl = isset($_POST['cta_url']) ? trim((string) $_POST['cta_url']) : '';
    $heroImageInput = isset($_POST['hero_image']) ? trim((string) $_POST['hero_image']) : '';
    $thumbnailImageInput = isset($_POST['thumbnail_image']) ? trim((string) $_POST['thumbnail_image']) : '';
    $availableLanguages = isset($_POST['available_languages']) ? trim((string) $_POST['available_languages']) : '';
    $restrictedCountries = isset($_POST['restricted_countries']) ? trim((string) $_POST['restricted_countries']) : '';
    $affiliateProgram = isset($_POST['affiliate_program']) ? trim((string) $_POST['affiliate_program']) : '';
    $supportLiveChat = strtolower(trim((string) ($_POST['support_live_chat'] ?? '')));
    $supportEmails = isset($_POST['support_emails']) ? trim((string) $_POST['support_emails']) : '';
    $supportPhone = isset($_POST['support_phone']) ? trim((string) $_POST['support_phone']) : '';
    $validSupportLiveChatValues = ['yes', 'no'];
    if (!in_array($supportLiveChat, $validSupportLiveChatValues, true)) {
        $supportLiveChat = '';
    }

    $deviceCatalog = getDeviceSupportCatalog();
    $deviceInput = (array) ($_POST['devices'] ?? []);
    $deviceSelections = [];
    foreach ($deviceCatalog as $groupKey => $group) {
        $selectedKeys = array_map('strval', (array) ($deviceInput[$groupKey] ?? []));
        $allowedKeys = array_keys($group['items']);
        $filtered = array_values(array_filter(
            array_unique($selectedKeys),
            static fn(string $key): bool => in_array($key, $allowedKeys, true)
        ));
        if ($filtered !== []) {
            $deviceSelections[$groupKey] = $filtered;
        }
    }

    $pros = array_map('trim', (array) ($_POST['pros'] ?? []));
    $pros = array_values(array_filter($pros, static fn(string $value): bool => $value !== ''));
    $cons = array_map('trim', (array) ($_POST['cons'] ?? []));
    $cons = array_values(array_filter($cons, static fn(string $value): bool => $value !== ''));

    $categories = normalizeTagList((string) ($_POST['categories'] ?? ''));
    $genres = normalizeTagList((string) ($_POST['genres'] ?? ''));
    $perks = normalizeTagList((string) ($_POST['perks'] ?? ''));
    $paymentMethodIds = array_map('intval', (array) ($_POST['payment_methods'] ?? []));
    $paymentMethodIds = array_values(array_filter($paymentMethodIds, static fn(int $id): bool => $id > 0));
    $providerIds = array_map('intval', (array) ($_POST['providers'] ?? []));
    $providerIds = array_values(array_filter($providerIds, static fn(int $id): bool => $id > 0));
    $relatedSelections = [];
    for ($slot = 1; $slot <= 5; $slot += 1) {
        $fieldName = 'related_slot_' . $slot;
        $casinoId = isset($_POST[$fieldName]) ? (int) $_POST[$fieldName] : 0;
        if ($casinoId > 0) {
            $relatedSelections[$slot] = $casinoId;
        }
    }
    $gameModesInput = (array) ($_POST['game_modes'] ?? []);
    $gameModes = [];
    if ($gameModesInput !== []) {
        foreach ($gameModesInput as $modeInput) {
            if (!is_array($modeInput)) {
                continue;
            }
            $gameTypeLabel = trim((string) ($modeInput['label'] ?? ''));
            if ($gameTypeLabel === '') {
                continue;
            }
            $gameModes[] = [
                'game_type' => $gameTypeLabel,
                'live_dealer_supported' => !empty($modeInput['live_dealer']),
                'virtual_reality_supported' => !empty($modeInput['virtual_reality']),
            ];
        }
    } else {
        foreach ($gameTypeOptions as $option) {
            $gameTypeLabel = (string) ($option['label'] ?? '');
            $gameModes[] = [
                'game_type' => $gameTypeLabel,
                'live_dealer_supported' => false,
                'virtual_reality_supported' => false,
            ];
        }
    }

    $errors = [];
    $isNewCasino = $casinoId <= 0;

    if ($name === '') {
        $errors[] = 'Casino name is required.';
    }

    if ($slug === '') {
        $slug = slugifyValue($name);
    } else {
        $slug = slugifyValue($slug);
    }

    if ($slug !== '') {
        $checkStatement = $database->prepare('SELECT id FROM casinos WHERE slug = :slug LIMIT 1');
        $checkStatement->execute([':slug' => $slug]);
        $existingId = $checkStatement->fetchColumn();

        if ($existingId !== false && (int) $existingId !== $casinoId) {
            $casinoId = (int) $existingId;
            $existingCasino = fetchCasinoById($database, $casinoId);
            $isNewCasino = false;
        }
    }

    if ($rating < 0 || $rating > 5) {
        $errors[] = 'Rating must be between 0 and 5.';
    }

    $heroUpload = handleImageUpload('hero_image_upload', $slug, $errors);
    $thumbnailUpload = handleImageUpload('thumbnail_image_upload', $slug, $errors);

    $heroImage = $heroUpload ?? $heroImageInput;
    $thumbnailImage = $thumbnailUpload ?? $thumbnailImageInput;

    if ($heroImage === '' && $thumbnailImage === '') {
        $errors[] = 'Provide at least one casino image.';
    }

    if ($isNewCasino && $paymentMethodIds === []) {
        $errors[] = 'Select at least one payment method.';
    }

    if ($isNewCasino && $providerIds === []) {
        $errors[] = 'Select at least one software provider.';
    }

    if ($isNewCasino && $deviceSelections === []) {
        $errors[] = 'Select at least one device option.';
    }

    if ($isNewCasino && $pros === []) {
        $errors[] = 'Add at least one pro.';
    }

    if ($isNewCasino && $cons === []) {
        $errors[] = 'Add at least one con.';
    }

    if ($isNewCasino && $availableLanguages === '') {
        $errors[] = 'Provide available languages.';
    }

    if ($isNewCasino && $restrictedCountries === '') {
        $errors[] = 'Provide restricted countries.';
    }

    if ($isNewCasino && $affiliateProgram === '') {
        $errors[] = 'Provide affiliate program details.';
    }

    if ($isNewCasino && $supportLiveChat === '') {
        $errors[] = 'Select live chat availability.';
    }

    if ($isNewCasino && $supportEmails === '') {
        $errors[] = 'Provide support emails.';
    }

    if ($isNewCasino && $supportPhone === '') {
        $errors[] = 'Provide a support phone number.';
    }

    if ($relatedSelections !== [] && count($relatedSelections) !== count(array_unique($relatedSelections))) {
        $errors[] = 'Related casino selections must be unique.';
    }

    if ($errors === []) {
        if ($casinoId > 0) {
            $statement = $database->prepare(
                'UPDATE casinos SET slug = :slug, name = :name, operator = :operator, license = :license, headline_bonus = :headline_bonus, min_deposit_usd = :min_deposit_usd, hero_image = :hero_image, thumbnail_image = :thumbnail_image, rating = :rating, short_description = :short_description, cta_url = :cta_url WHERE id = :id'
            );
            $statement->execute([
                ':slug' => $slug,
                ':name' => $name,
                ':operator' => $operator,
                ':license' => $license,
                ':headline_bonus' => $headlineBonus,
                ':min_deposit_usd' => $minDeposit,
                ':hero_image' => $heroImage,
                ':thumbnail_image' => $thumbnailImage,
                ':rating' => $rating,
                ':short_description' => $shortDescription,
                ':cta_url' => $ctaUrl,
                ':id' => $casinoId,
            ]);
        } else {
            $statement = $database->prepare(
                'INSERT INTO casinos (slug, name, operator, license, headline_bonus, min_deposit_usd, hero_image, thumbnail_image, rating, short_description, cta_url) VALUES (:slug, :name, :operator, :license, :headline_bonus, :min_deposit_usd, :hero_image, :thumbnail_image, :rating, :short_description, :cta_url)'
            );
            $statement->execute([
                ':slug' => $slug,
                ':name' => $name,
                ':operator' => $operator,
                ':license' => $license,
                ':headline_bonus' => $headlineBonus,
                ':min_deposit_usd' => $minDeposit,
                ':hero_image' => $heroImage,
                ':thumbnail_image' => $thumbnailImage,
                ':rating' => $rating,
                ':short_description' => $shortDescription,
                ':cta_url' => $ctaUrl,
            ]);
            $casinoId = (int) $database->lastInsertId();
        }

        updateCasinoTags($database, $casinoId, 'category', $categories);
        updateCasinoTags($database, $casinoId, 'genre', $genres);
        updateCasinoTags($database, $casinoId, 'perk', $perks);
        if ($isNewCasino) {
            seedCasinoReviewSections($database, $casinoId, $name);
        }

        $existingPaymentMethods = $existingCasino['payment_methods'] ?? [];
        if ($paymentMethodIds !== [] || $isNewCasino || $existingPaymentMethods === []) {
            updateCasinoPaymentMethods($database, $casinoId, $paymentMethodIds);
        }

        $existingProviders = $existingCasino['providers'] ?? [];
        if ($providerIds !== [] || $isNewCasino || $existingProviders === []) {
            updateCasinoProviders($database, $casinoId, $providerIds);
        }

        updateCasinoGameModes($database, $casinoId, $gameModes);
        updateCasinoProsCons($database, $casinoId, $pros, $cons);
        updateCasinoDevices($database, $casinoId, $deviceSelections);
        $additionalInfoPoints = [];
        if ($availableLanguages !== '') {
            $additionalInfoPoints[] = [
                'icon' => 'fa-language text-warning',
                'content' => 'Available Languages: ' . $availableLanguages,
            ];
        }
        if ($restrictedCountries !== '') {
            $additionalInfoPoints[] = [
                'icon' => 'fa-ban text-danger',
                'content' => 'Restricted Countries: ' . $restrictedCountries,
            ];
        }
        if ($affiliateProgram !== '') {
            $additionalInfoPoints[] = [
                'icon' => 'fa-handshake text-success',
                'content' => 'Affiliate program: ' . $affiliateProgram,
            ];
        }
        $supportPoints = [];
        if ($supportLiveChat !== '') {
            $supportPoints[] = [
                'icon' => $supportLiveChat === 'yes' ? 'fa-check text-success' : 'fa-times text-danger',
                'content' => 'Live chat: ' . ($supportLiveChat === 'yes' ? 'Yes' : 'No'),
            ];
        }
        if ($supportEmails !== '') {
            $supportPoints[] = [
                'icon' => 'fa-envelope text-warning',
                'content' => 'Emails: ' . $supportEmails,
            ];
        }
        if ($supportPhone !== '') {
            $supportPoints[] = [
                'icon' => 'fa-phone text-warning',
                'content' => 'Phone number: ' . $supportPhone,
            ];
        }
        updateCasinoReviewSectionPoints($database, $casinoId, 'Additional Info', $additionalInfoPoints);
        updateCasinoReviewSectionPoints($database, $casinoId, 'Support', $supportPoints);

        if ($relatedSelections !== [] || $isNewCasino) {
            $deleteStatement = $database->prepare('DELETE FROM casino_cards WHERE section = :section');
            $deleteStatement->execute([':section' => 'related']);

            if ($relatedSelections !== []) {
                $sources = fetchCasinoCardSources($database, array_values($relatedSelections));
                $insertStatement = $database->prepare(
                    'INSERT INTO casino_cards (casino_id, section, title, image_path, min_deposit_label, rating, price_label, position)
                    VALUES (:casino_id, :section, :title, :image_path, :min_deposit_label, :rating, :price_label, :position)'
                );

                foreach ($relatedSelections as $position => $casinoId) {
                    if (!isset($sources[$casinoId])) {
                        continue;
                    }

                    $source = $sources[$casinoId];
                    $imagePath = (string) ($source['thumbnail_image'] ?? '');
                    if ($imagePath === '') {
                        $imagePath = (string) ($source['hero_image'] ?? '');
                    }

                    $rating = is_numeric($source['rating'] ?? null) ? (int) $source['rating'] : null;
                    $minDepositLabel = formatMinDeposit(
                        isset($source['min_deposit_usd']) && is_numeric($source['min_deposit_usd'])
                            ? (int) $source['min_deposit_usd']
                            : null
                    );

                    $insertStatement->execute([
                        ':casino_id' => $casinoId,
                        ':section' => 'related',
                        ':title' => $source['name'] ?? '',
                        ':image_path' => $imagePath,
                        ':min_deposit_label' => $minDepositLabel,
                        ':rating' => $rating,
                        ':price_label' => null,
                        ':position' => $position,
                    ]);
                }
            }
        }

        $actionMessage = $casinoId > 0 ? 'Casino saved successfully.' : 'Casino created successfully.';
        header('Location: admin.php?status=' . urlencode($actionMessage));
        exit;
    }

    $actionError = implode(' ', $errors);
}

if (isset($_POST['action']) && $_POST['action'] === 'save_featured_sections') {
    $errors = [];
    $selectionsBySection = [];

    foreach ($featuredSections as $section => $config) {
        $sectionSelections = [];
        for ($slot = 1; $slot <= (int) $config['slots']; $slot += 1) {
            $fieldName = $section . '_slot_' . $slot;
            $casinoId = isset($_POST[$fieldName]) ? (int) $_POST[$fieldName] : 0;
            if ($casinoId > 0) {
                $sectionSelections[$slot] = $casinoId;
            }
        }

        if (count($sectionSelections) !== count(array_unique($sectionSelections))) {
            $errors[] = sprintf('%s selections must be unique.', $config['label']);
        }

        $selectionsBySection[$section] = $sectionSelections;
    }

    if ($errors === []) {
        $database->beginTransaction();
        try {
            foreach ($featuredSections as $section => $config) {
                $deleteStatement = $database->prepare('DELETE FROM casino_cards WHERE section = :section');
                $deleteStatement->execute([':section' => $section]);

                $sectionSelections = $selectionsBySection[$section] ?? [];
                if ($sectionSelections === []) {
                    continue;
                }

                $sources = fetchCasinoCardSources($database, array_values($sectionSelections));
                $insertStatement = $database->prepare(
                    'INSERT INTO casino_cards (casino_id, section, title, image_path, min_deposit_label, rating, price_label, position)
                    VALUES (:casino_id, :section, :title, :image_path, :min_deposit_label, :rating, :price_label, :position)'
                );

                foreach ($sectionSelections as $position => $casinoId) {
                    if (!isset($sources[$casinoId])) {
                        throw new RuntimeException('A selected casino could not be found.');
                    }

                    $source = $sources[$casinoId];
                    $imagePath = (string) ($source['thumbnail_image'] ?? '');
                    if ($imagePath === '') {
                        $imagePath = (string) ($source['hero_image'] ?? '');
                    }

                    $rating = is_numeric($source['rating'] ?? null) ? (int) $source['rating'] : null;
                    $minDepositLabel = formatMinDeposit(
                        isset($source['min_deposit_usd']) && is_numeric($source['min_deposit_usd'])
                            ? (int) $source['min_deposit_usd']
                            : null
                    );

                    $insertStatement->execute([
                        ':casino_id' => $casinoId,
                        ':section' => $section,
                        ':title' => $source['name'] ?? '',
                        ':image_path' => $imagePath,
                        ':min_deposit_label' => $minDepositLabel,
                        ':rating' => $rating,
                        ':price_label' => null,
                        ':position' => $position,
                    ]);
                }
            }

            $database->commit();
            header('Location: admin.php?status=' . urlencode('Featured sections updated.'));
            exit;
        } catch (Throwable $error) {
            if ($database->inTransaction()) {
                $database->rollBack();
            }
            $errors[] = 'Unable to update featured sections. Please try again.';
        }
    }

    $actionError = implode(' ', $errors);
}

if (isset($_POST['action']) && $_POST['action'] === 'save_provider') {
    $name = isset($_POST['provider_name']) ? trim((string) $_POST['provider_name']) : '';
    $imagePathInput = isset($_POST['provider_image']) ? trim((string) $_POST['provider_image']) : '';
    $errors = [];

    if ($name === '') {
        $errors[] = 'Provider name is required.';
    }

    if (!tableExists($database, 'providers')) {
        $errors[] = 'Providers catalog is not available.';
    }

    $upload = handleCatalogImageUpload('provider_image_upload', $name ?: 'provider', 'providers', $errors);
    $imagePath = $upload ?? $imagePathInput;

    if ($imagePath === '') {
        $errors[] = 'Provider image URL or upload is required.';
    }

    if ($errors === []) {
        $statement = $database->prepare('INSERT INTO providers (name, image_path) VALUES (:name, :image_path)');
        $statement->execute([
            ':name' => $name,
            ':image_path' => $imagePath,
        ]);
        header('Location: admin.php?status=' . urlencode('Provider added.'));
        exit;
    }

    $actionError = implode(' ', $errors);
}

if (isset($_POST['action']) && $_POST['action'] === 'save_payment_method') {
    $name = isset($_POST['payment_name']) ? trim((string) $_POST['payment_name']) : '';
    $imagePathInput = isset($_POST['payment_image']) ? trim((string) $_POST['payment_image']) : '';
    $errors = [];

    if ($name === '') {
        $errors[] = 'Payment method name is required.';
    }

    if (!tableExists($database, 'payment_methods')) {
        $errors[] = 'Payment methods catalog is not available.';
    }

    $upload = handleCatalogImageUpload('payment_image_upload', $name ?: 'payment', 'payment-methods', $errors);
    $imagePath = $upload ?? $imagePathInput;

    if ($imagePath === '') {
        $errors[] = 'Payment method image URL or upload is required.';
    }

    if ($errors === []) {
        $statement = $database->prepare('INSERT INTO payment_methods (name, image_path) VALUES (:name, :image_path)');
        $statement->execute([
            ':name' => $name,
            ':image_path' => $imagePath,
        ]);
        header('Location: admin.php?status=' . urlencode('Payment method added.'));
        exit;
    }

    $actionError = implode(' ', $errors);
}

if (isset($_POST['action']) && $_POST['action'] === 'delete_casino') {
    $casinoId = isset($_POST['casino_id']) ? (int) $_POST['casino_id'] : 0;
    if ($casinoId > 0) {
        $statement = $database->prepare('DELETE FROM casinos WHERE id = :id');
        $statement->execute([':id' => $casinoId]);
        header('Location: admin.php?status=' . urlencode('Casino deleted.'));
        exit;
    }
}

if (isset($_POST['action']) && $_POST['action'] === 'delete_provider') {
    $providerId = isset($_POST['provider_id']) ? (int) $_POST['provider_id'] : 0;
    if ($providerId > 0) {
        $statement = $database->prepare('DELETE FROM providers WHERE id = :id');
        $statement->execute([':id' => $providerId]);
        header('Location: admin.php?status=' . urlencode('Provider deleted.'));
        exit;
    }
}

if (isset($_POST['action']) && $_POST['action'] === 'delete_payment_method') {
    $paymentId = isset($_POST['payment_id']) ? (int) $_POST['payment_id'] : 0;
    if ($paymentId > 0) {
        $statement = $database->prepare('DELETE FROM payment_methods WHERE id = :id');
        $statement->execute([':id' => $paymentId]);
        header('Location: admin.php?status=' . urlencode('Payment method deleted.'));
        exit;
    }
}

if ($actionMessage === '' && isset($_GET['status'])) {
    $actionMessage = (string) $_GET['status'];
}

$editCasinoId = isset($_GET['edit']) ? (int) $_GET['edit'] : 0;
$editCasino = $editCasinoId > 0 ? fetchCasinoById($database, $editCasinoId) : null;
$gameTypeOptionsByKey = [];
foreach ($gameTypeOptions as $option) {
    $label = (string) ($option['label'] ?? '');
    $key = normalizeGameTypeKey($label);
    if ($key === '') {
        continue;
    }
    $gameTypeOptionsByKey[$key] = [
        'label' => $label,
        'key' => $key,
    ];
}
if ($editCasino) {
    foreach ($editCasino['games'] ?? [] as $game) {
        $label = trim((string) ($game['game_type'] ?? ''));
        $key = normalizeGameTypeKey($label);
        if ($label === '' || $key === '') {
            continue;
        }
        if (!isset($gameTypeOptionsByKey[$key])) {
            $gameTypeOptionsByKey[$key] = [
                'label' => $label,
                'key' => $key,
            ];
        }
    }
}
$gameTypeOptionsForForm = array_values($gameTypeOptionsByKey);

$formValues = [
    'id' => $editCasino['id'] ?? 0,
    'name' => $editCasino['name'] ?? '',
    'slug' => $editCasino['slug'] ?? '',
    'operator' => $editCasino['operator'] ?? '',
    'license' => $editCasino['license'] ?? '',
    'headline_bonus' => $editCasino['headline_bonus'] ?? '',
    'min_deposit_usd' => $editCasino['min_deposit_usd'] ?? '',
    'hero_image' => $editCasino['hero_image'] ?? '',
    'thumbnail_image' => $editCasino['thumbnail_image'] ?? '',
    'rating' => $editCasino['rating'] ?? 0,
    'short_description' => $editCasino['short_description'] ?? '',
    'cta_url' => $editCasino['cta_url'] ?? '',
    'categories' => isset($editCasino['categories']) ? implode(', ', $editCasino['categories']) : '',
    'genres' => isset($editCasino['genres']) ? implode(', ', $editCasino['genres']) : '',
    'perks' => isset($editCasino['perks']) ? implode(', ', $editCasino['perks']) : '',
    'payment_methods' => [],
    'providers' => [],
    'game_modes' => [],
    'pros' => $editCasino['pros_cons']['pros'] ?? [],
    'cons' => $editCasino['pros_cons']['cons'] ?? [],
    'devices' => $editCasino['devices'] ?? [],
    'available_languages' => '',
    'restricted_countries' => '',
    'affiliate_program' => '',
    'support_live_chat' => '',
    'support_emails' => '',
    'support_phone' => '',
];

$casinos = fetchCasinos($database);
$relatedSelections = fetchFeaturedSectionSelections($database, 'related');
$featuredSelections = [];
foreach ($featuredSections as $section => $config) {
    $featuredSelections[$section] = fetchFeaturedSectionSelections($database, $section);
}
$providers = fetchProviders($database);
$paymentMethodsCatalog = fetchPaymentMethodsCatalog($database);

if ($editCasino) {
    $reviewSectionsByKey = mapReviewSectionsByKey($editCasino['review_sections'] ?? []);
    $additionalInfoSection = $reviewSectionsByKey['additional-info'] ?? null;
    if (is_array($additionalInfoSection)) {
        foreach ($additionalInfoSection['points'] ?? [] as $point) {
            $content = trim((string) ($point['content'] ?? ''));
            if ($content === '') {
                continue;
            }
            if (stripos($content, 'Available Languages:') === 0) {
                $formValues['available_languages'] = trim(substr($content, strlen('Available Languages:')));
            } elseif (stripos($content, 'Restricted Countries:') === 0) {
                $formValues['restricted_countries'] = trim(substr($content, strlen('Restricted Countries:')));
            } elseif (stripos($content, 'Affiliate program:') === 0) {
                $formValues['affiliate_program'] = trim(substr($content, strlen('Affiliate program:')));
            }
        }
    }
    $supportSection = $reviewSectionsByKey['support'] ?? null;
    if (is_array($supportSection)) {
        foreach ($supportSection['points'] ?? [] as $point) {
            $content = trim((string) ($point['content'] ?? ''));
            if ($content === '') {
                continue;
            }
            if (stripos($content, 'Live chat:') === 0) {
                $value = strtolower(trim(substr($content, strlen('Live chat:'))));
                if (str_starts_with($value, 'yes')) {
                    $formValues['support_live_chat'] = 'yes';
                } elseif (str_starts_with($value, 'no')) {
                    $formValues['support_live_chat'] = 'no';
                }
            } elseif (stripos($content, 'Emails:') === 0) {
                $formValues['support_emails'] = trim(substr($content, strlen('Emails:')));
            } elseif (stripos($content, 'Phone number:') === 0) {
                $formValues['support_phone'] = trim(substr($content, strlen('Phone number:')));
            }
        }
    }

    $selectedPaymentMethodNames = array_map(
        static fn(array $method): string => (string) ($method['method_name'] ?? ''),
        $editCasino['payment_methods'] ?? []
    );
    $selectedPaymentMethodNames = array_filter($selectedPaymentMethodNames, static fn(string $name): bool => $name !== '');
    foreach ($paymentMethodsCatalog as $method) {
        if (in_array($method['name'], $selectedPaymentMethodNames, true)) {
            $formValues['payment_methods'][] = (int) $method['id'];
        }
    }

    foreach ($editCasino['providers'] ?? [] as $provider) {
        if (isset($provider['id'])) {
            $formValues['providers'][] = (int) $provider['id'];
        }
    }
}

foreach ($gameTypeOptionsForForm as $option) {
    $key = (string) ($option['key'] ?? '');
    if ($key === '') {
        continue;
    }
    $formValues['game_modes'][$key] = [
        'live_dealer_supported' => false,
        'virtual_reality_supported' => false,
    ];
}

if ($editCasino) {
    foreach ($editCasino['games'] ?? [] as $game) {
        $key = normalizeGameTypeKey((string) ($game['game_type'] ?? ''));
        if ($key === '' || !isset($formValues['game_modes'][$key])) {
            continue;
        }
        $formValues['game_modes'][$key] = [
            'live_dealer_supported' => !empty($game['live_dealer_supported']),
            'virtual_reality_supported' => !empty($game['virtual_reality_supported']),
        ];
    }
}

if ($formValues['pros'] === []) {
    $formValues['pros'] = [''];
}

if ($formValues['cons'] === []) {
    $formValues['cons'] = [''];
}

include __DIR__ . '/partials/html-head.php';
?>
<div class="page-heading header-text">
    <div class="container">
        <div class="row">
            <div class="col-lg-12 d-flex justify-content-between align-items-center">
                <div>
                    <h3>Casino Admin</h3>
                    <span class="breadcrumb"><a href="index.php">Home</a>  >  <span>Casino Admin</span></span>
                </div>
                <form method="post">
                    <input type="hidden" name="action" value="logout">
                    <button type="submit" class="btn btn-sm btn-outline-light">Logout</button>
                </form>
            </div>
        </div>
    </div>
</div>

<div class="section admin-shell">
    <div class="container">
        <?php if ($actionMessage !== ''): ?>
            <div class="alert alert-success" role="alert"><?= htmlspecialchars($actionMessage, ENT_QUOTES, 'UTF-8') ?></div>
        <?php endif; ?>
        <?php if ($actionError !== ''): ?>
            <div class="alert alert-danger" role="alert"><?= htmlspecialchars($actionError, ENT_QUOTES, 'UTF-8') ?></div>
        <?php endif; ?>

        <nav class="navbar admin-navbar mb-4">
            <div class="container-fluid">
                <div class="admin-menu-title">
                    <span class="text-uppercase small fw-semibold text-muted">Admin Menu</span>
                    <h5 class="mb-0">Quick Navigation</h5>
                </div>
                <nav class="admin-menu-links">
                    <ul class="nav nav-pills flex-wrap gap-2">
                        <li class="nav-item">
                            <a class="nav-link active" href="#add-casino">Add New Casino</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="#existing-casinos">Existing Casinos</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="#featured-sections">Homepage Featured</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="#providers-payment-methods">Providers &amp; Payment Methods</a>
                        </li>
                    </ul>
                </nav>
            </div>
        </nav>

        <div class="row">
            <div class="col-12">
                <div class="card shadow-sm mb-4 admin-card" id="add-casino" data-admin-section="add-casino">
                    <div class="card-body">
                        <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-3">
                            <div>
                                <h5 class="card-title mb-1"><?= $formValues['id'] ? 'Edit Casino' : 'Add New Casino' ?></h5>
                                <p class="text-muted mb-0">Complete the details in five guided steps.</p>
                            </div>
                            <span class="admin-step-indicator badge bg-light text-dark" data-step-indicator>Step 1 of 5</span>
                        </div>
                        <form method="post" enctype="multipart/form-data" data-admin-stepper>
                            <input type="hidden" name="action" value="save_casino">
                            <input type="hidden" name="casino_id" value="<?= (int) $formValues['id'] ?>">

                            <ul class="nav nav-pills admin-stepper-nav mb-4" role="tablist">
                                <li class="nav-item" role="presentation">
                                    <button class="nav-link active" type="button" data-step-target="1">General Information</button>
                                </li>
                                <li class="nav-item" role="presentation">
                                    <button class="nav-link" type="button" data-step-target="2">Description + Game Types</button>
                                </li>
                                <li class="nav-item" role="presentation">
                                    <button class="nav-link" type="button" data-step-target="3">Additional Information &amp; Support</button>
                                </li>
                                <li class="nav-item" role="presentation">
                                    <button class="nav-link" type="button" data-step-target="4">Reviews &amp; Extras</button>
                                </li>
                                <li class="nav-item" role="presentation">
                                    <button class="nav-link" type="button" data-step-target="5">Related Casinos</button>
                                </li>
                            </ul>

                            <div class="admin-step" data-step="1">
                                <div class="row g-3">
                                    <div class="col-lg-6">
                                        <label class="form-label" for="name">Casino Name</label>
                                        <input type="text" class="form-control" id="name" name="name" value="<?= htmlspecialchars((string) $formValues['name'], ENT_QUOTES, 'UTF-8') ?>" required>
                                    </div>
                                    <div class="col-lg-6">
                                        <label class="form-label" for="slug">Slug</label>
                                        <input type="text" class="form-control" id="slug" name="slug" value="<?= htmlspecialchars((string) $formValues['slug'], ENT_QUOTES, 'UTF-8') ?>">
                                        <small class="text-muted">Used in URLs. Leave blank to auto-generate.</small>
                                    </div>
                                    <div class="col-lg-6">
                                        <label class="form-label" for="operator">Operator</label>
                                        <input type="text" class="form-control" id="operator" name="operator" value="<?= htmlspecialchars((string) $formValues['operator'], ENT_QUOTES, 'UTF-8') ?>">
                                    </div>
                                    <div class="col-lg-6">
                                        <label class="form-label" for="license">License</label>
                                        <input type="text" class="form-control" id="license" name="license" value="<?= htmlspecialchars((string) $formValues['license'], ENT_QUOTES, 'UTF-8') ?>">
                                    </div>
                                    <div class="col-lg-6">
                                        <label class="form-label" for="headline_bonus">Headline Bonus</label>
                                        <input type="text" class="form-control" id="headline_bonus" name="headline_bonus" value="<?= htmlspecialchars((string) $formValues['headline_bonus'], ENT_QUOTES, 'UTF-8') ?>">
                                    </div>
                                    <div class="col-lg-6">
                                        <label class="form-label" for="min_deposit_usd">Minimum Deposit (USD)</label>
                                        <input type="number" class="form-control" id="min_deposit_usd" name="min_deposit_usd" min="0" value="<?= htmlspecialchars((string) $formValues['min_deposit_usd'], ENT_QUOTES, 'UTF-8') ?>">
                                    </div>
                                    <div class="col-lg-6">
                                        <label class="form-label" for="rating">Rating (0-5)</label>
                                        <input type="number" class="form-control" id="rating" name="rating" min="0" max="5" value="<?= htmlspecialchars((string) $formValues['rating'], ENT_QUOTES, 'UTF-8') ?>">
                                    </div>
                                    <div class="col-lg-6">
                                        <label class="form-label" for="cta_url">CTA URL</label>
                                        <input type="url" class="form-control" id="cta_url" name="cta_url" value="<?= htmlspecialchars((string) $formValues['cta_url'], ENT_QUOTES, 'UTF-8') ?>">
                                    </div>
                                </div>
                                <div class="admin-step-actions">
                                    <span class="text-muted small">Next: Description + Game Types</span>
                                    <button class="btn btn-brand" type="button" data-step-next>Next</button>
                                </div>
                            </div>

                            <div class="admin-step d-none" data-step="2">
                                <div class="row g-3">
                                    <div class="col-12">
                                        <label class="form-label" for="short_description">Short Description</label>
                                        <textarea class="form-control" id="short_description" name="short_description" rows="3"><?= htmlspecialchars((string) $formValues['short_description'], ENT_QUOTES, 'UTF-8') ?></textarea>
                                    </div>
                                    <div class="col-lg-6">
                                        <label class="form-label" for="hero_image">Hero Image URL</label>
                                        <input type="text" class="form-control" id="hero_image" name="hero_image" value="<?= htmlspecialchars((string) $formValues['hero_image'], ENT_QUOTES, 'UTF-8') ?>">
                                        <input type="file" class="form-control mt-2" name="hero_image_upload" accept="image/*">
                                    </div>
                                    <div class="col-lg-6">
                                        <label class="form-label" for="thumbnail_image">Thumbnail Image URL</label>
                                        <input type="text" class="form-control" id="thumbnail_image" name="thumbnail_image" value="<?= htmlspecialchars((string) $formValues['thumbnail_image'], ENT_QUOTES, 'UTF-8') ?>">
                                        <input type="file" class="form-control mt-2" name="thumbnail_image_upload" accept="image/*">
                                    </div>
                                    <div class="col-lg-4">
                                        <label class="form-label" for="categories">Categories (comma separated)</label>
                                        <input type="text" class="form-control" id="categories" name="categories" value="<?= htmlspecialchars((string) $formValues['categories'], ENT_QUOTES, 'UTF-8') ?>">
                                    </div>
                                    <div class="col-lg-4">
                                        <label class="form-label" for="genres">Genres (comma separated)</label>
                                        <input type="text" class="form-control" id="genres" name="genres" value="<?= htmlspecialchars((string) $formValues['genres'], ENT_QUOTES, 'UTF-8') ?>">
                                    </div>
                                    <div class="col-lg-4">
                                        <label class="form-label" for="perks">Perks (comma separated)</label>
                                        <input type="text" class="form-control" id="perks" name="perks" value="<?= htmlspecialchars((string) $formValues['perks'], ENT_QUOTES, 'UTF-8') ?>">
                                    </div>
                                    <div class="col-12">
                                        <label class="form-label">Game Types</label>
                                        <div class="table-responsive">
                                            <table class="table table-sm align-middle">
                                                <thead>
                                                <tr>
                                                    <th>Game Type</th>
                                                    <th>Live Dealer</th>
                                                    <th>Virtual Reality</th>
                                                </tr>
                                                </thead>
                                                <tbody>
                                                <?php foreach ($gameTypeOptionsForForm as $option): ?>
                                                    <?php
                                                    $gameKey = (string) ($option['key'] ?? '');
                                                    $modeValues = $formValues['game_modes'][$gameKey] ?? [
                                                        'live_dealer_supported' => false,
                                                        'virtual_reality_supported' => false,
                                                    ];
                                                    ?>
                                                    <tr>
                                                        <td><?= htmlspecialchars((string) ($option['label'] ?? ''), ENT_QUOTES, 'UTF-8') ?></td>
                                                        <td>
                                                            <div class="form-check m-0">
                                                                <input type="hidden" name="game_modes[<?= htmlspecialchars($gameKey, ENT_QUOTES, 'UTF-8') ?>][label]" value="<?= htmlspecialchars((string) ($option['label'] ?? ''), ENT_QUOTES, 'UTF-8') ?>">
                                                                <input type="hidden" name="game_modes[<?= htmlspecialchars($gameKey, ENT_QUOTES, 'UTF-8') ?>][live_dealer]" value="0">
                                                                <input class="form-check-input" type="checkbox"
                                                                       id="game-<?= htmlspecialchars($gameKey, ENT_QUOTES, 'UTF-8') ?>-live"
                                                                       name="game_modes[<?= htmlspecialchars($gameKey, ENT_QUOTES, 'UTF-8') ?>][live_dealer]"
                                                                       value="1"
                                                                       <?= !empty($modeValues['live_dealer_supported']) ? 'checked' : '' ?>>
                                                            </div>
                                                        </td>
                                                        <td>
                                                            <div class="form-check m-0">
                                                                <input type="hidden" name="game_modes[<?= htmlspecialchars($gameKey, ENT_QUOTES, 'UTF-8') ?>][virtual_reality]" value="0">
                                                                <input class="form-check-input" type="checkbox"
                                                                       id="game-<?= htmlspecialchars($gameKey, ENT_QUOTES, 'UTF-8') ?>-vr"
                                                                       name="game_modes[<?= htmlspecialchars($gameKey, ENT_QUOTES, 'UTF-8') ?>][virtual_reality]"
                                                                       value="1"
                                                                       <?= !empty($modeValues['virtual_reality_supported']) ? 'checked' : '' ?>>
                                                            </div>
                                                        </td>
                                                    </tr>
                                                <?php endforeach; ?>
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                                <div class="admin-step-actions">
                                    <button class="btn btn-outline-light" type="button" data-step-prev>Back</button>
                                    <button class="btn btn-brand" type="button" data-step-next>Next</button>
                                </div>
                            </div>

                            <div class="admin-step d-none" data-step="3">
                                <div class="row g-3">
                                    <div class="col-lg-4">
                                        <label class="form-label" for="available_languages">Available Languages</label>
                                        <input type="text" class="form-control" id="available_languages" name="available_languages" value="<?= htmlspecialchars((string) $formValues['available_languages'], ENT_QUOTES, 'UTF-8') ?>" required>
                                        <small class="text-muted">Comma-separated or full list.</small>
                                    </div>
                                    <div class="col-lg-4">
                                        <label class="form-label" for="restricted_countries">Restricted Countries</label>
                                        <input type="text" class="form-control" id="restricted_countries" name="restricted_countries" value="<?= htmlspecialchars((string) $formValues['restricted_countries'], ENT_QUOTES, 'UTF-8') ?>" required>
                                    </div>
                                    <div class="col-lg-4">
                                        <label class="form-label" for="affiliate_program">Affiliate Program</label>
                                        <input type="text" class="form-control" id="affiliate_program" name="affiliate_program" value="<?= htmlspecialchars((string) $formValues['affiliate_program'], ENT_QUOTES, 'UTF-8') ?>" required>
                                    </div>
                                </div>
                                <div class="row g-3 mt-1">
                                    <div class="col-lg-4">
                                        <label class="form-label" for="support_live_chat">Live Chat</label>
                                        <select class="form-select" id="support_live_chat" name="support_live_chat" required>
                                            <option value="" disabled <?= $formValues['support_live_chat'] === '' ? 'selected' : '' ?>>Select</option>
                                            <option value="yes" <?= $formValues['support_live_chat'] === 'yes' ? 'selected' : '' ?>>Yes</option>
                                            <option value="no" <?= $formValues['support_live_chat'] === 'no' ? 'selected' : '' ?>>No</option>
                                        </select>
                                    </div>
                                    <div class="col-lg-4">
                                        <label class="form-label" for="support_emails">Emails</label>
                                        <input type="text" class="form-control" id="support_emails" name="support_emails" value="<?= htmlspecialchars((string) $formValues['support_emails'], ENT_QUOTES, 'UTF-8') ?>" required>
                                    </div>
                                    <div class="col-lg-4">
                                        <label class="form-label" for="support_phone">Phone Number</label>
                                        <input type="text" class="form-control" id="support_phone" name="support_phone" value="<?= htmlspecialchars((string) $formValues['support_phone'], ENT_QUOTES, 'UTF-8') ?>" required>
                                    </div>
                                </div>
                                <div class="admin-step-actions">
                                    <button class="btn btn-outline-light" type="button" data-step-prev>Back</button>
                                    <button class="btn btn-brand" type="button" data-step-next>Next</button>
                                </div>
                            </div>

                            <div class="admin-step d-none" data-step="4">
                                <div class="mb-4">
                                    <label class="form-label">Payment Methods</label>
                                    <?php if (!empty($paymentMethodsCatalog)): ?>
                                        <div class="d-flex flex-wrap gap-3">
                                            <?php foreach ($paymentMethodsCatalog as $method): ?>
                                                <?php $methodId = (int) ($method['id'] ?? 0); ?>
                                                <div class="form-check">
                                                    <input class="form-check-input" type="checkbox" name="payment_methods[]"
                                                           id="payment-method-<?= $methodId ?>"
                                                           value="<?= $methodId ?>"
                                                           <?= in_array($methodId, $formValues['payment_methods'], true) ? 'checked' : '' ?>>
                                                    <label class="form-check-label" for="payment-method-<?= $methodId ?>">
                                                        <?= htmlspecialchars($method['name'], ENT_QUOTES, 'UTF-8') ?>
                                                    </label>
                                                </div>
                                            <?php endforeach; ?>
                                        </div>
                                    <?php else: ?>
                                        <p class="text-muted mb-0">No payment methods available.</p>
                                    <?php endif; ?>
                                </div>
                                <div class="mb-4">
                                    <label class="form-label">Software Providers</label>
                                    <?php if (!empty($providers)): ?>
                                        <div class="d-flex flex-wrap gap-3">
                                            <?php foreach ($providers as $provider): ?>
                                                <?php $providerId = (int) ($provider['id'] ?? 0); ?>
                                                <div class="form-check">
                                                    <input class="form-check-input" type="checkbox" name="providers[]"
                                                           id="provider-<?= $providerId ?>"
                                                           value="<?= $providerId ?>"
                                                           <?= in_array($providerId, $formValues['providers'], true) ? 'checked' : '' ?>>
                                                    <label class="form-check-label" for="provider-<?= $providerId ?>">
                                                        <?= htmlspecialchars($provider['name'], ENT_QUOTES, 'UTF-8') ?>
                                                    </label>
                                                </div>
                                            <?php endforeach; ?>
                                        </div>
                                    <?php else: ?>
                                        <p class="text-muted mb-0">No providers available.</p>
                                    <?php endif; ?>
                                </div>
                                <div class="mb-4">
                                    <label class="form-label">Devices</label>
                                    <div class="row g-3">
                                        <?php foreach ($deviceSupportCatalog as $groupKey => $group): ?>
                                            <div class="col-md-6">
                                                <div class="bg-light border rounded-3 p-3 h-100">
                                                    <div class="d-flex align-items-center gap-2 text-uppercase small fw-semibold text-muted mb-2">
                                                        <i class="fa <?= htmlspecialchars($group['icon'], ENT_QUOTES, 'UTF-8') ?>" aria-hidden="true"></i>
                                                        <span><?= htmlspecialchars($group['label'], ENT_QUOTES, 'UTF-8') ?></span>
                                                    </div>
                                                    <div class="d-flex flex-wrap gap-3">
                                                        <?php foreach ($group['items'] as $deviceKey => $device): ?>
                                                            <?php
                                                            $deviceId = sprintf('device-%s-%s', $groupKey, $deviceKey);
                                                            $isChecked = in_array(
                                                                $deviceKey,
                                                                $formValues['devices'][$groupKey] ?? [],
                                                                true
                                                            );
                                                            ?>
                                                            <div class="form-check">
                                                                <input class="form-check-input" type="checkbox"
                                                                       id="<?= htmlspecialchars($deviceId, ENT_QUOTES, 'UTF-8') ?>"
                                                                       name="devices[<?= htmlspecialchars($groupKey, ENT_QUOTES, 'UTF-8') ?>][]"
                                                                       value="<?= htmlspecialchars($deviceKey, ENT_QUOTES, 'UTF-8') ?>"
                                                                    <?= $isChecked ? 'checked' : '' ?>>
                                                                <label class="form-check-label" for="<?= htmlspecialchars($deviceId, ENT_QUOTES, 'UTF-8') ?>">
                                                                    <i class="<?= htmlspecialchars($device['icon'], ENT_QUOTES, 'UTF-8') ?> me-1" aria-hidden="true"></i>
                                                                    <?= htmlspecialchars($device['label'], ENT_QUOTES, 'UTF-8') ?>
                                                                </label>
                                                            </div>
                                                        <?php endforeach; ?>
                                                    </div>
                                                </div>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Pros &amp; Cons</label>
                                    <div class="row g-3">
                                        <div class="col-md-6">
                                            <h6 class="text-success mb-2"><i class="fa fa-thumbs-up me-2"></i>Pros</h6>
                                            <div data-pros-list>
                                                <?php foreach ($formValues['pros'] as $pro): ?>
                                                    <div class="input-group mb-2" data-pros-row>
                                                        <input type="text" class="form-control" name="pros[]"
                                                               value="<?= htmlspecialchars((string) $pro, ENT_QUOTES, 'UTF-8') ?>"
                                                               placeholder="Add a pro">
                                                        <button class="btn btn-outline-danger" type="button" data-remove-row>
                                                            <i class="fa fa-times" aria-hidden="true"></i>
                                                        </button>
                                                    </div>
                                                <?php endforeach; ?>
                                            </div>
                                            <button class="btn btn-sm btn-outline-success" type="button" data-add-pro>
                                                <i class="fa fa-plus me-1" aria-hidden="true"></i>Add Pro
                                            </button>
                                        </div>
                                        <div class="col-md-6">
                                            <h6 class="text-danger mb-2"><i class="fa fa-thumbs-down me-2"></i>Cons</h6>
                                            <div data-cons-list>
                                                <?php foreach ($formValues['cons'] as $con): ?>
                                                    <div class="input-group mb-2" data-cons-row>
                                                        <input type="text" class="form-control" name="cons[]"
                                                               value="<?= htmlspecialchars((string) $con, ENT_QUOTES, 'UTF-8') ?>"
                                                               placeholder="Add a con">
                                                        <button class="btn btn-outline-danger" type="button" data-remove-row>
                                                            <i class="fa fa-times" aria-hidden="true"></i>
                                                        </button>
                                                    </div>
                                                <?php endforeach; ?>
                                            </div>
                                            <button class="btn btn-sm btn-outline-danger" type="button" data-add-con>
                                                <i class="fa fa-plus me-1" aria-hidden="true"></i>Add Con
                                            </button>
                                        </div>
                                    </div>
                                </div>
                                <div class="admin-step-actions">
                                    <button class="btn btn-outline-light" type="button" data-step-prev>Back</button>
                                    <button class="btn btn-brand" type="button" data-step-next>Next</button>
                                </div>
                            </div>

                            <div class="admin-step d-none" data-step="5">
                                <div class="row g-3">
                                    <?php
                                    $relatedCasinoOptions = array_values(array_filter(
                                        $casinos,
                                        static fn(array $casino): bool => (int) $casino['id'] !== (int) $formValues['id']
                                    ));
                                    ?>
                                    <?php for ($slot = 1; $slot <= 5; $slot += 1): ?>
                                        <?php
                                        $fieldName = 'related_slot_' . $slot;
                                        $selectedId = $relatedSelections[$slot] ?? 0;
                                        ?>
                                        <div class="col-lg-6">
                                            <label class="form-label" for="<?= htmlspecialchars($fieldName, ENT_QUOTES, 'UTF-8') ?>">Related Casino <?= (int) $slot ?></label>
                                            <select class="form-select"
                                                    id="<?= htmlspecialchars($fieldName, ENT_QUOTES, 'UTF-8') ?>"
                                                    name="<?= htmlspecialchars($fieldName, ENT_QUOTES, 'UTF-8') ?>"
                                                <?= $formValues['id'] ? '' : 'required' ?>>
                                                <option value="0">Select a casino</option>
                                                <?php foreach ($relatedCasinoOptions as $casino): ?>
                                                    <option value="<?= (int) $casino['id'] ?>" <?= (int) $selectedId === (int) $casino['id'] ? 'selected' : '' ?>>
                                                        <?= htmlspecialchars($casino['name'], ENT_QUOTES, 'UTF-8') ?>
                                                    </option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>
                                    <?php endfor; ?>
                                </div>
                                <div class="admin-step-actions">
                                    <button class="btn btn-outline-light" type="button" data-step-prev>Back</button>
                                    <button type="submit" class="btn btn-brand">Save Casino</button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-12">
                <div class="card shadow-sm admin-card d-none" id="existing-casinos" data-admin-section="existing-casinos">
                    <div class="card-body">
                        <h5 class="card-title mb-3">Existing Casinos</h5>
                        <div class="table-responsive">
                            <table class="table align-middle">
                                <thead>
                                <tr>
                                    <th>Name</th>
                                    <th>Slug</th>
                                    <th>Rating</th>
                                    <th>Actions</th>
                                </tr>
                                </thead>
                                <tbody>
                                <?php foreach ($casinos as $casino): ?>
                                    <tr>
                                        <td><?= htmlspecialchars($casino['name'], ENT_QUOTES, 'UTF-8') ?></td>
                                        <td><?= htmlspecialchars($casino['slug'], ENT_QUOTES, 'UTF-8') ?></td>
                                        <td><?= (int) $casino['rating'] ?>/5</td>
                                        <td class="d-flex gap-2">
                                            <a class="btn btn-sm btn-outline-primary" href="admin.php?edit=<?= (int) $casino['id'] ?>">Edit</a>
                                            <form method="post" onsubmit="return confirm('Delete this casino?');">
                                                <input type="hidden" name="action" value="delete_casino">
                                                <input type="hidden" name="casino_id" value="<?= (int) $casino['id'] ?>">
                                                <button type="submit" class="btn btn-sm btn-outline-danger">Delete</button>
                                            </form>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="row mt-4 d-none" id="featured-sections" data-admin-section="featured-sections">
            <div class="col-lg-12">
                <div class="card shadow-sm admin-card">
                    <div class="card-body">
                        <h5 class="card-title mb-3">Homepage Featured Sections</h5>
                        <p class="text-muted">Choose which casinos appear in the Top 1, Hot Picks, and Top Casinos sections on the homepage.</p>
                        <form method="post">
                            <input type="hidden" name="action" value="save_featured_sections">
                            <div class="row g-3">
                                <?php
                                $featuredColumnGroups = [
                                    [
                                        'column_class' => 'col-12 col-lg-6',
                                        'sections' => ['top_1', 'hot_picks'],
                                    ],
                                    [
                                        'column_class' => 'col-12 col-lg-6',
                                        'sections' => ['most_played'],
                                    ],
                                ];
                                ?>
                                <?php foreach ($featuredColumnGroups as $group): ?>
                                    <div class="<?= htmlspecialchars($group['column_class'], ENT_QUOTES, 'UTF-8') ?>">
                                        <div class="admin-featured-card">
                                            <?php foreach ($group['sections'] as $sectionIndex => $section): ?>
                                                <?php
                                                $config = $featuredSections[$section];
                                                if ($sectionIndex > 0) {
                                                    echo '<hr class="my-4">';
                                                }
                                                ?>
                                                <h6 class="mb-2"><?= htmlspecialchars($config['label'], ENT_QUOTES, 'UTF-8') ?></h6>
                                                <?php for ($slot = 1; $slot <= (int) $config['slots']; $slot += 1): ?>
                                                    <?php
                                                    $fieldName = $section . '_slot_' . $slot;
                                                    $selectedId = $featuredSelections[$section][$slot] ?? 0;
                                                    ?>
                                                    <div class="mb-3">
                                                        <label class="form-label" for="<?= htmlspecialchars($fieldName, ENT_QUOTES, 'UTF-8') ?>">Slot <?= (int) $slot ?></label>
                                                        <select class="form-select" id="<?= htmlspecialchars($fieldName, ENT_QUOTES, 'UTF-8') ?>" name="<?= htmlspecialchars($fieldName, ENT_QUOTES, 'UTF-8') ?>">
                                                            <option value="0">-- None --</option>
                                                            <?php foreach ($casinos as $casino): ?>
                                                                <option value="<?= (int) $casino['id'] ?>" <?= (int) $selectedId === (int) $casino['id'] ? 'selected' : '' ?>>
                                                                    <?= htmlspecialchars($casino['name'], ENT_QUOTES, 'UTF-8') ?>
                                                                </option>
                                                            <?php endforeach; ?>
                                                        </select>
                                                    </div>
                                                <?php endfor; ?>
                                            <?php endforeach; ?>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                            <button type="submit" class="btn btn-brand mt-3">Save Featured Sections</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
        <div class="row mt-4 d-none" id="providers-payment-methods" data-admin-section="providers-payment-methods">
            <div class="col-lg-6" id="providers">
                <div class="card shadow-sm h-100 admin-card">
                    <div class="card-body">
                        <h5 class="card-title mb-3">Software Providers</h5>
                        <form method="post" enctype="multipart/form-data" class="mb-4">
                            <input type="hidden" name="action" value="save_provider">
                            <div class="mb-3">
                                <label class="form-label" for="provider_name">Provider Name</label>
                                <input type="text" class="form-control" id="provider_name" name="provider_name" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label" for="provider_image">Provider Image URL</label>
                                <input type="text" class="form-control" id="provider_image" name="provider_image">
                                <input type="file" class="form-control mt-2" name="provider_image_upload" accept="image/*">
                            </div>
                            <button type="submit" class="btn btn-brand w-100">Add Provider</button>
                        </form>
                        <?php if (!empty($providers)): ?>
                            <div class="table-responsive">
                                <table class="table align-middle">
                                    <thead>
                                    <tr>
                                        <th>Name</th>
                                        <th>Image</th>
                                        <th>Actions</th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    <?php foreach ($providers as $provider): ?>
                                        <tr>
                                            <td><?= htmlspecialchars($provider['name'], ENT_QUOTES, 'UTF-8') ?></td>
                                            <td>
                                                <img src="<?= htmlspecialchars($provider['image_path'], ENT_QUOTES, 'UTF-8') ?>" alt="<?= htmlspecialchars($provider['name'], ENT_QUOTES, 'UTF-8') ?>" style="max-height: 40px;">
                                            </td>
                                            <td>
                                                <form method="post" onsubmit="return confirm('Delete this provider?');">
                                                    <input type="hidden" name="action" value="delete_provider">
                                                    <input type="hidden" name="provider_id" value="<?= (int) ($provider['id'] ?? 0) ?>">
                                                    <button type="submit" class="btn btn-sm btn-outline-danger">Delete</button>
                                                </form>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php else: ?>
                            <p class="text-muted">No providers added yet.</p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            <div class="col-lg-6" id="payment-methods">
                <div class="card shadow-sm h-100 admin-card">
                    <div class="card-body">
                        <h5 class="card-title mb-3">Payment Methods</h5>
                        <form method="post" enctype="multipart/form-data" class="mb-4">
                            <input type="hidden" name="action" value="save_payment_method">
                            <div class="mb-3">
                                <label class="form-label" for="payment_name">Payment Method Name</label>
                                <input type="text" class="form-control" id="payment_name" name="payment_name" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label" for="payment_image">Payment Method Image URL</label>
                                <input type="text" class="form-control" id="payment_image" name="payment_image">
                                <input type="file" class="form-control mt-2" name="payment_image_upload" accept="image/*">
                            </div>
                            <button type="submit" class="btn btn-brand w-100">Add Payment Method</button>
                        </form>
                        <?php if (!empty($paymentMethodsCatalog)): ?>
                            <div class="table-responsive">
                                <table class="table align-middle">
                                    <thead>
                                    <tr>
                                        <th>Name</th>
                                        <th>Image</th>
                                        <th>Actions</th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    <?php foreach ($paymentMethodsCatalog as $method): ?>
                                        <tr>
                                            <td><?= htmlspecialchars($method['name'], ENT_QUOTES, 'UTF-8') ?></td>
                                            <td>
                                                <img src="<?= htmlspecialchars($method['image_path'], ENT_QUOTES, 'UTF-8') ?>" alt="<?= htmlspecialchars($method['name'], ENT_QUOTES, 'UTF-8') ?>" style="max-height: 40px;">
                                            </td>
                                            <td>
                                                <form method="post" onsubmit="return confirm('Delete this payment method?');">
                                                    <input type="hidden" name="action" value="delete_payment_method">
                                                    <input type="hidden" name="payment_id" value="<?= (int) ($method['id'] ?? 0) ?>">
                                                    <button type="submit" class="btn btn-sm btn-outline-danger">Delete</button>
                                                </form>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php else: ?>
                            <p class="text-muted">No payment methods added yet.</p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<?php
?>
<template id="pros-row-template">
    <div class="input-group mb-2" data-pros-row>
        <input type="text" class="form-control" name="pros[]" placeholder="Add a pro">
        <button class="btn btn-outline-danger" type="button" data-remove-row>
            <i class="fa fa-times" aria-hidden="true"></i>
        </button>
    </div>
</template>
<template id="cons-row-template">
    <div class="input-group mb-2" data-cons-row>
        <input type="text" class="form-control" name="cons[]" placeholder="Add a con">
        <button class="btn btn-outline-danger" type="button" data-remove-row>
            <i class="fa fa-times" aria-hidden="true"></i>
        </button>
    </div>
</template>
<script>
  (function () {
    const prosList = document.querySelector("[data-pros-list]");
    const consList = document.querySelector("[data-cons-list]");
    const addProButton = document.querySelector("[data-add-pro]");
    const addConButton = document.querySelector("[data-add-con]");
    const prosTemplate = document.getElementById("pros-row-template");
    const consTemplate = document.getElementById("cons-row-template");

    const addRow = (list, template) => {
      if (!list || !template) {
        return;
      }
      const node = template.content.firstElementChild.cloneNode(true);
      list.appendChild(node);
    };

    const bindRemove = (list) => {
      if (!list) {
        return;
      }
      list.addEventListener("click", (event) => {
        const button = event.target.closest("[data-remove-row]");
        if (!button) {
          return;
        }
        const row = button.closest(".input-group");
        if (row) {
          row.remove();
        }
      });
    };

    if (addProButton) {
      addProButton.addEventListener("click", () => addRow(prosList, prosTemplate));
    }
    if (addConButton) {
      addConButton.addEventListener("click", () => addRow(consList, consTemplate));
    }
    bindRemove(prosList);
    bindRemove(consList);

    const navLinks = Array.from(document.querySelectorAll(".admin-menu-links .nav-link"));
    const sections = Array.from(document.querySelectorAll("[data-admin-section]"));

    const showSection = (sectionId) => {
      sections.forEach((section) => {
        const isActive = section.dataset.adminSection === sectionId;
        section.classList.toggle("d-none", !isActive);
      });
      navLinks.forEach((link) => {
        const linkTarget = (link.getAttribute("href") || "").replace("#", "");
        link.classList.toggle("active", linkTarget === sectionId);
      });
    };

    if (navLinks.length > 0 && sections.length > 0) {
      navLinks.forEach((link) => {
        link.addEventListener("click", (event) => {
          event.preventDefault();
          const targetId = (link.getAttribute("href") || "").replace("#", "");
          if (!targetId) {
            return;
          }
          showSection(targetId);
          if (window.history && window.history.replaceState) {
            window.history.replaceState(null, "", `#${targetId}`);
          }
        });
      });

      const initialHash = window.location.hash.replace("#", "");
      const defaultSection = "add-casino";
      const hasInitial = sections.some((section) => section.dataset.adminSection === initialHash);
      showSection(hasInitial ? initialHash : defaultSection);
    }

    const stepper = document.querySelector("[data-admin-stepper]");
    if (!stepper) {
      return;
    }

    const steps = Array.from(stepper.querySelectorAll("[data-step]"));
    const stepButtons = Array.from(stepper.querySelectorAll("[data-step-target]"));
    const nextButtons = Array.from(stepper.querySelectorAll("[data-step-next]"));
    const prevButtons = Array.from(stepper.querySelectorAll("[data-step-prev]"));
    const stepIndicator = document.querySelector("[data-step-indicator]");
    let activeStep = 1;

    const setActiveStep = (step) => {
      activeStep = step;
      steps.forEach((panel) => {
        const panelStep = Number(panel.dataset.step || 0);
        panel.classList.toggle("d-none", panelStep !== step);
      });
      stepButtons.forEach((button) => {
        const buttonStep = Number(button.dataset.stepTarget || 0);
        button.classList.toggle("active", buttonStep === step);
      });
      if (stepIndicator) {
        stepIndicator.textContent = `Step ${step} of ${steps.length}`;
      }
      const focusTarget = stepper.querySelector(`[data-step="${step}"]`);
      if (focusTarget) {
        focusTarget.scrollIntoView({ behavior: "smooth", block: "start" });
      }
    };

    stepButtons.forEach((button) => {
      button.addEventListener("click", () => {
        const target = Number(button.dataset.stepTarget || 0);
        if (target) {
          setActiveStep(target);
        }
      });
    });

    nextButtons.forEach((button) => {
      button.addEventListener("click", () => {
        if (activeStep < steps.length) {
          setActiveStep(activeStep + 1);
        }
      });
    });

    prevButtons.forEach((button) => {
      button.addEventListener("click", () => {
        if (activeStep > 1) {
          setActiveStep(activeStep - 1);
        }
      });
    });

    setActiveStep(activeStep);
  })();
</script>
<?php
include __DIR__ . '/partials/footer.php';
