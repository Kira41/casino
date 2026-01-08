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

    <div class="section">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-lg-5">
                    <div class="card shadow-sm">
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

if (isset($_POST['action']) && $_POST['action'] === 'save_casino') {
    $casinoId = isset($_POST['casino_id']) ? (int) $_POST['casino_id'] : 0;
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

    $categories = normalizeTagList((string) ($_POST['categories'] ?? ''));
    $genres = normalizeTagList((string) ($_POST['genres'] ?? ''));
    $perks = normalizeTagList((string) ($_POST['perks'] ?? ''));

    $errors = [];

    if ($name === '') {
        $errors[] = 'Casino name is required.';
    }

    if ($slug === '') {
        $slug = slugifyValue($name);
    } else {
        $slug = slugifyValue($slug);
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

    if ($errors === []) {
        $checkStatement = $database->prepare('SELECT id FROM casinos WHERE slug = :slug LIMIT 1');
        $checkStatement->execute([':slug' => $slug]);
        $existingId = $checkStatement->fetchColumn();

        if ($existingId !== false && (int) $existingId !== $casinoId) {
            $errors[] = 'Slug already exists. Please choose another.';
        }
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

if (isset($_POST['action']) && $_POST['action'] === 'delete_casino') {
    $casinoId = isset($_POST['casino_id']) ? (int) $_POST['casino_id'] : 0;
    if ($casinoId > 0) {
        $statement = $database->prepare('DELETE FROM casinos WHERE id = :id');
        $statement->execute([':id' => $casinoId]);
        header('Location: admin.php?status=' . urlencode('Casino deleted.'));
        exit;
    }
}

if ($actionMessage === '' && isset($_GET['status'])) {
    $actionMessage = (string) $_GET['status'];
}

$editCasinoId = isset($_GET['edit']) ? (int) $_GET['edit'] : 0;
$editCasino = $editCasinoId > 0 ? fetchCasinoById($database, $editCasinoId) : null;

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
];

$casinos = fetchCasinos($database);
$featuredSelections = [];
foreach ($featuredSections as $section => $config) {
    $featuredSelections[$section] = fetchFeaturedSectionSelections($database, $section);
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

<div class="section">
    <div class="container">
        <?php if ($actionMessage !== ''): ?>
            <div class="alert alert-success" role="alert"><?= htmlspecialchars($actionMessage, ENT_QUOTES, 'UTF-8') ?></div>
        <?php endif; ?>
        <?php if ($actionError !== ''): ?>
            <div class="alert alert-danger" role="alert"><?= htmlspecialchars($actionError, ENT_QUOTES, 'UTF-8') ?></div>
        <?php endif; ?>

        <div class="row">
            <div class="col-lg-5">
                <div class="card shadow-sm mb-4">
                    <div class="card-body">
                        <h5 class="card-title mb-3"><?= $formValues['id'] ? 'Edit Casino' : 'Add New Casino' ?></h5>
                        <form method="post" enctype="multipart/form-data">
                            <input type="hidden" name="action" value="save_casino">
                            <input type="hidden" name="casino_id" value="<?= (int) $formValues['id'] ?>">
                            <div class="mb-3">
                                <label class="form-label" for="name">Casino Name</label>
                                <input type="text" class="form-control" id="name" name="name" value="<?= htmlspecialchars((string) $formValues['name'], ENT_QUOTES, 'UTF-8') ?>" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label" for="slug">Slug</label>
                                <input type="text" class="form-control" id="slug" name="slug" value="<?= htmlspecialchars((string) $formValues['slug'], ENT_QUOTES, 'UTF-8') ?>">
                                <small class="text-muted">Used in URLs. Leave blank to auto-generate.</small>
                            </div>
                            <div class="mb-3">
                                <label class="form-label" for="operator">Operator</label>
                                <input type="text" class="form-control" id="operator" name="operator" value="<?= htmlspecialchars((string) $formValues['operator'], ENT_QUOTES, 'UTF-8') ?>">
                            </div>
                            <div class="mb-3">
                                <label class="form-label" for="license">License</label>
                                <input type="text" class="form-control" id="license" name="license" value="<?= htmlspecialchars((string) $formValues['license'], ENT_QUOTES, 'UTF-8') ?>">
                            </div>
                            <div class="mb-3">
                                <label class="form-label" for="headline_bonus">Headline Bonus</label>
                                <input type="text" class="form-control" id="headline_bonus" name="headline_bonus" value="<?= htmlspecialchars((string) $formValues['headline_bonus'], ENT_QUOTES, 'UTF-8') ?>">
                            </div>
                            <div class="mb-3">
                                <label class="form-label" for="min_deposit_usd">Minimum Deposit (USD)</label>
                                <input type="number" class="form-control" id="min_deposit_usd" name="min_deposit_usd" min="0" value="<?= htmlspecialchars((string) $formValues['min_deposit_usd'], ENT_QUOTES, 'UTF-8') ?>">
                            </div>
                            <div class="mb-3">
                                <label class="form-label" for="rating">Rating (0-5)</label>
                                <input type="number" class="form-control" id="rating" name="rating" min="0" max="5" value="<?= htmlspecialchars((string) $formValues['rating'], ENT_QUOTES, 'UTF-8') ?>">
                            </div>
                            <div class="mb-3">
                                <label class="form-label" for="cta_url">CTA URL</label>
                                <input type="url" class="form-control" id="cta_url" name="cta_url" value="<?= htmlspecialchars((string) $formValues['cta_url'], ENT_QUOTES, 'UTF-8') ?>">
                            </div>
                            <div class="mb-3">
                                <label class="form-label" for="short_description">Short Description</label>
                                <textarea class="form-control" id="short_description" name="short_description" rows="3"><?= htmlspecialchars((string) $formValues['short_description'], ENT_QUOTES, 'UTF-8') ?></textarea>
                            </div>
                            <div class="mb-3">
                                <label class="form-label" for="hero_image">Hero Image URL</label>
                                <input type="text" class="form-control" id="hero_image" name="hero_image" value="<?= htmlspecialchars((string) $formValues['hero_image'], ENT_QUOTES, 'UTF-8') ?>">
                                <input type="file" class="form-control mt-2" name="hero_image_upload" accept="image/*">
                            </div>
                            <div class="mb-3">
                                <label class="form-label" for="thumbnail_image">Thumbnail Image URL</label>
                                <input type="text" class="form-control" id="thumbnail_image" name="thumbnail_image" value="<?= htmlspecialchars((string) $formValues['thumbnail_image'], ENT_QUOTES, 'UTF-8') ?>">
                                <input type="file" class="form-control mt-2" name="thumbnail_image_upload" accept="image/*">
                            </div>
                            <div class="mb-3">
                                <label class="form-label" for="categories">Categories (comma separated)</label>
                                <input type="text" class="form-control" id="categories" name="categories" value="<?= htmlspecialchars((string) $formValues['categories'], ENT_QUOTES, 'UTF-8') ?>">
                            </div>
                            <div class="mb-3">
                                <label class="form-label" for="genres">Genres (comma separated)</label>
                                <input type="text" class="form-control" id="genres" name="genres" value="<?= htmlspecialchars((string) $formValues['genres'], ENT_QUOTES, 'UTF-8') ?>">
                            </div>
                            <div class="mb-3">
                                <label class="form-label" for="perks">Perks (comma separated)</label>
                                <input type="text" class="form-control" id="perks" name="perks" value="<?= htmlspecialchars((string) $formValues['perks'], ENT_QUOTES, 'UTF-8') ?>">
                            </div>
                            <button type="submit" class="btn btn-brand w-100">Save Casino</button>
                        </form>
                    </div>
                </div>
            </div>
            <div class="col-lg-7">
                <div class="card shadow-sm">
                    <div class="card-body">
                        <h5 class="card-title mb-3">Existing Casinos</h5>
                        <div class="table-responsive">
                            <table class="table align-middle">
                                <thead>
                                <tr>
                                    <th>Name</th>
                                    <th>Slug</th>
                                    <th>Rating</th>
                                    <th>Top 1</th>
                                    <th>Actions</th>
                                </tr>
                                </thead>
                                <tbody>
                                <?php foreach ($casinos as $casino): ?>
                                    <tr>
                                        <td><?= htmlspecialchars($casino['name'], ENT_QUOTES, 'UTF-8') ?></td>
                                        <td><?= htmlspecialchars($casino['slug'], ENT_QUOTES, 'UTF-8') ?></td>
                                        <td><?= (int) $casino['rating'] ?>/5</td>
                                        <td><?= (int) ($casino['is_top1'] ?? 0) === 1 ? 'Yes' : '—' ?></td>
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
        <div class="row mt-4">
            <div class="col-lg-12">
                <div class="card shadow-sm">
                    <div class="card-body">
                        <h5 class="card-title mb-3">Homepage Featured Sections</h5>
                        <p class="text-muted">Choose which casinos appear in the Top 1, Hot Picks, and Top Casinos sections on the homepage.</p>
                        <form method="post">
                            <input type="hidden" name="action" value="save_featured_sections">
                            <div class="row g-3">
                                <?php foreach ($featuredSections as $section => $config): ?>
                                    <div class="col-lg-6">
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
                                    </div>
                                <?php endforeach; ?>
                            </div>
                            <button type="submit" class="btn btn-brand">Save Featured Sections</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<?php
include __DIR__ . '/partials/footer.php';
