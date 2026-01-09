<?php

declare(strict_types=1);

require_once __DIR__ . '/database.php';

function getDatabase(): PDO
{
    static $database = null;

    if ($database instanceof PDO) {
        return $database;
    }

    $database = openDatabase();
    return $database;
}

function fetchCasinoCards(PDO $database, string $section): array
{
    $statement = $database->prepare(
        'SELECT cc.section, cc.title, cc.image_path, cc.min_deposit_label, cc.rating, cc.price_label, cc.position, c.slug, c.name, c.min_deposit_usd
        FROM casino_cards cc
        LEFT JOIN casinos c ON c.id = cc.casino_id
        WHERE cc.section = :section
        ORDER BY cc.position ASC, cc.id ASC'
    );
    $statement->execute([':section' => $section]);

    $cards = $statement->fetchAll(PDO::FETCH_ASSOC) ?: [];
    $uniqueCards = [];
    $seen = [];

    foreach ($cards as $card) {
        $slug = strtolower(trim((string) ($card['slug'] ?? '')));
        $title = strtolower(trim((string) ($card['title'] ?? '')));
        $key = $slug !== '' ? $slug : $title;

        if ($key !== '' && isset($seen[$key])) {
            continue;
        }

        if ($key !== '') {
            $seen[$key] = true;
        }

        $uniqueCards[] = $card;
    }

    return $uniqueCards;
}

function fetchCategoryCards(PDO $database, string $section): array
{
    $statement = $database->prepare(
        'SELECT title, image_path FROM category_cards WHERE section = :section ORDER BY id ASC'
    );
    $statement->execute([':section' => $section]);

    $categories = $statement->fetchAll(PDO::FETCH_ASSOC) ?: [];
    $uniqueCategories = [];
    $seenTitles = [];

    foreach ($categories as $category) {
        $titleKey = strtolower(trim((string) ($category['title'] ?? '')));

        if (isset($seenTitles[$titleKey])) {
            continue;
        }

        $seenTitles[$titleKey] = true;
        $uniqueCategories[] = $category;
    }

    return $uniqueCategories;
}

function fetchCasinos(PDO $database): array
{
    $statement = $database->query(
        'SELECT id, slug, name, thumbnail_image, min_deposit_usd, rating, is_top1 FROM casinos ORDER BY name ASC'
    );

    return $statement->fetchAll(PDO::FETCH_ASSOC) ?: [];
}

function fetchCasinosWithCategories(PDO $database): array
{
    $casinos = fetchCasinos($database);

    foreach ($casinos as &$casino) {
        $statement = $database->prepare(
            'SELECT t.name FROM casino_tag_links l INNER JOIN casino_tags t ON t.id = l.tag_id WHERE l.casino_id = :casino_id AND t.type = :type ORDER BY l.is_primary DESC, t.name ASC'
        );
        $statement->execute([':casino_id' => $casino['id'], ':type' => 'category']);
        $categories = $statement->fetchAll(PDO::FETCH_COLUMN) ?: [];
        $casino['categories'] = $categories;
    }

    return $casinos;
}

function fetchCasinosByCategory(PDO $database, string $categorySlug): array
{
    $categorySlug = trim($categorySlug);

    if ($categorySlug === '') {
        return [];
    }

    $categorySlug = slugifyTag($categorySlug);
    $casinos = fetchCasinosWithCategories($database);

    return array_values(array_filter($casinos, static function (array $casino) use ($categorySlug) {
        foreach ($casino['categories'] ?? [] as $categoryName) {
            if (slugifyTag((string) $categoryName) === $categorySlug) {
                return true;
            }
        }

        return false;
    }));
}

function casinoHasCategory(array $casino, string $categorySlug): bool
{
    $categorySlug = trim($categorySlug);

    if ($categorySlug === '') {
        return false;
    }

    $categorySlug = slugifyTag($categorySlug);
    foreach ($casino['categories'] ?? [] as $categoryName) {
        if (slugifyTag((string) $categoryName) === $categorySlug) {
            return true;
        }
    }

    return false;
}

function fetchCasinoDirectory(PDO $database): array
{
    $statement = $database->query(
        'SELECT id, name, slug, COALESCE(thumbnail_image, hero_image, "") AS thumbnail FROM casinos ORDER BY name ASC'
    );

    return $statement->fetchAll(PDO::FETCH_ASSOC) ?: [];
}

function fetchCasinoTags(PDO $database, int $casinoId, ?string $type = null): array
{
    $sql = 'SELECT t.name, t.type, l.is_primary FROM casino_tag_links l INNER JOIN casino_tags t ON t.id = l.tag_id WHERE l.casino_id = :casino_id';
    $params = [':casino_id' => $casinoId];

    if ($type !== null) {
        $sql .= ' AND t.type = :type';
        $params[':type'] = $type;
    }

    $sql .= ' ORDER BY l.is_primary DESC, t.name ASC';

    $statement = $database->prepare($sql);
    $statement->execute($params);

    return $statement->fetchAll(PDO::FETCH_ASSOC) ?: [];
}

function fetchCasinoProsCons(PDO $database, int $casinoId): array
{
    $statement = $database->prepare(
        'SELECT type, content FROM casino_pros_cons WHERE casino_id = :casino_id ORDER BY id ASC'
    );
    $statement->execute([':casino_id' => $casinoId]);

    $pros = [];
    $cons = [];
    $seenPros = [];
    $seenCons = [];

    foreach ($statement->fetchAll(PDO::FETCH_ASSOC) ?: [] as $row) {
        $content = trim((string) ($row['content'] ?? ''));
        $contentKey = strtolower($content);

        if ($row['type'] === 'pro') {
            if ($contentKey === '' || isset($seenPros[$contentKey])) {
                continue;
            }
            $seenPros[$contentKey] = true;
            $pros[] = $content;
        } elseif ($row['type'] === 'con') {
            if ($contentKey === '' || isset($seenCons[$contentKey])) {
                continue;
            }
            $seenCons[$contentKey] = true;
            $cons[] = $content;
        }
    }

    return ['pros' => $pros, 'cons' => $cons];
}

function fetchCasinoHighlights(PDO $database, int $casinoId): array
{
    $statement = $database->prepare(
        'SELECT label, icon FROM casino_highlights WHERE casino_id = :casino_id ORDER BY id ASC'
    );
    $statement->execute([':casino_id' => $casinoId]);

    return $statement->fetchAll(PDO::FETCH_ASSOC) ?: [];
}

function fetchCasinoGameModes(PDO $database, int $casinoId): array
{
    $statement = $database->prepare(
        'SELECT game_type, live_dealer_supported, virtual_reality_supported FROM casino_game_modes WHERE casino_id = :casino_id ORDER BY id ASC'
    );
    $statement->execute([':casino_id' => $casinoId]);

    return $statement->fetchAll(PDO::FETCH_ASSOC) ?: [];
}

function fetchCasinoPaymentMethods(PDO $database, int $casinoId): array
{
    $statement = $database->prepare(
        'SELECT method_name, icon_key FROM casino_payment_methods WHERE casino_id = :casino_id ORDER BY id ASC'
    );
    $statement->execute([':casino_id' => $casinoId]);

    return $statement->fetchAll(PDO::FETCH_ASSOC) ?: [];
}

function fetchPaymentMethodsCatalog(PDO $database): array
{
    if (!tableExists($database, 'payment_methods')) {
        return [];
    }

    $statement = $database->query('SELECT id, name, image_path FROM payment_methods ORDER BY id ASC');

    return $statement->fetchAll(PDO::FETCH_ASSOC) ?: [];
}

function fetchProviders(PDO $database): array
{
    if (!tableExists($database, 'providers')) {
        return [];
    }

    $statement = $database->query('SELECT id, name, image_path FROM providers ORDER BY id ASC');

    return $statement->fetchAll(PDO::FETCH_ASSOC) ?: [];
}

function fetchCasinoProviders(PDO $database, int $casinoId): array
{
    if (!tableExists($database, 'casino_provider_links') || !tableExists($database, 'providers')) {
        return [];
    }

    $statement = $database->prepare(
        'SELECT p.id, p.name, p.image_path
        FROM casino_provider_links l
        INNER JOIN providers p ON p.id = l.provider_id
        WHERE l.casino_id = :casino_id
        ORDER BY p.name ASC'
    );
    $statement->execute([':casino_id' => $casinoId]);

    return $statement->fetchAll(PDO::FETCH_ASSOC) ?: [];
}

function getDeviceSupportCatalog(): array
{
    return [
        'mobile' => [
            'label' => 'Mobile',
            'icon' => 'fa-mobile-alt',
            'items' => [
                'android' => ['label' => 'Android', 'icon' => 'fab fa-android'],
                'ios' => ['label' => 'iOS', 'icon' => 'fab fa-apple'],
            ],
        ],
        'desktop' => [
            'label' => 'Desktop',
            'icon' => 'fa-desktop',
            'items' => [
                'chrome' => ['label' => 'Chrome', 'icon' => 'fab fa-chrome'],
                'safari' => ['label' => 'Safari', 'icon' => 'fab fa-safari'],
                'firefox' => ['label' => 'Firefox', 'icon' => 'fab fa-firefox-browser'],
                'edge' => ['label' => 'Edge', 'icon' => 'fab fa-edge'],
            ],
        ],
    ];
}

function buildDeviceSupportGroups(array $selectedDevices): array
{
    $catalog = getDeviceSupportCatalog();
    $hasSelections = false;
    foreach ($selectedDevices as $devices) {
        if (!empty($devices)) {
            $hasSelections = true;
            break;
        }
    }

    $groups = [];
    foreach ($catalog as $groupKey => $group) {
        $groupItems = [];
        $selectedKeys = $selectedDevices[$groupKey] ?? [];
        foreach ($group['items'] as $deviceKey => $device) {
            if (!$hasSelections || in_array($deviceKey, $selectedKeys, true)) {
                $groupItems[] = [
                    'label' => $device['label'],
                    'icon' => $device['icon'],
                ];
            }
        }

        if ($groupItems !== []) {
            $groups[] = [
                'label' => $group['label'],
                'icon' => $group['icon'],
                'items' => $groupItems,
            ];
        }
    }

    return $groups;
}

function fetchCasinoDevices(PDO $database, int $casinoId): array
{
    if (!tableExists($database, 'casino_devices')) {
        return [];
    }

    $statement = $database->prepare(
        'SELECT device_group, device_key FROM casino_devices WHERE casino_id = :casino_id ORDER BY id ASC'
    );
    $statement->execute([':casino_id' => $casinoId]);

    $devices = [];
    foreach ($statement->fetchAll(PDO::FETCH_ASSOC) ?: [] as $row) {
        $group = (string) ($row['device_group'] ?? '');
        $key = (string) ($row['device_key'] ?? '');
        if ($group === '' || $key === '') {
            continue;
        }
        $devices[$group][] = $key;
    }

    foreach ($devices as $group => $keys) {
        $devices[$group] = array_values(array_unique($keys));
    }

    return $devices;
}

function buildGeneralInformationPoints(array $casino): array
{
    $operator = trim((string) ($casino['operator'] ?? ''));
    if ($operator === '') {
        $operator = (string) ($casino['name'] ?? '');
    }

    $license = trim((string) ($casino['license'] ?? ''));
    if ($license === '') {
        $license = 'TBD';
    }

    $minDepositValue = $casino['min_deposit_usd'] ?? null;
    $minDeposit = $minDepositValue === null
        ? 'Minimum Deposit: TBD'
        : 'Minimum Deposit: $' . number_format((int) $minDepositValue);

    $ratingValue = is_numeric($casino['rating'] ?? null) ? (int) $casino['rating'] : 0;
    $ratingValue = max(0, min(5, $ratingValue));
    $rating = 'Rating: ' . $ratingValue . ' / 5';

    return [
        ['icon' => 'fa-building text-info', 'content' => 'Operator: ' . $operator],
        ['icon' => 'fa-shield-alt text-warning', 'content' => 'License: ' . $license],
        ['icon' => 'fa-credit-card text-success', 'content' => $minDeposit],
        ['icon' => 'fa-star text-warning', 'content' => $rating],
    ];
}

function fetchCasinoReviewSections(PDO $database, int $casinoId, ?array $casino = null): array
{
    $statement = $database->prepare(
        'SELECT id, title, summary FROM casino_review_sections WHERE casino_id = :casino_id ORDER BY id ASC'
    );
    $statement->execute([':casino_id' => $casinoId]);

    $sections = [];
    foreach ($statement->fetchAll(PDO::FETCH_ASSOC) ?: [] as $section) {
        $points = [];
        if (strtolower((string) $section['title']) === 'general information' && $casino !== null) {
            $points = buildGeneralInformationPoints($casino);
        } else {
            $pointsStmt = $database->prepare(
                'SELECT icon, content FROM casino_review_points WHERE review_section_id = :section_id ORDER BY id ASC'
            );
            $pointsStmt->execute([':section_id' => $section['id']]);
            $points = $pointsStmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
        }
        $sections[] = [
            'title' => $section['title'],
            'summary' => $section['summary'],
            'points' => $points,
        ];
    }

    return $sections;
}

function fetchCasinoBySlug(PDO $database, string $slug): ?array
{
    $statement = $database->prepare('SELECT * FROM casinos WHERE slug = :slug LIMIT 1');
    $statement->execute([':slug' => $slug]);

    $casino = $statement->fetch(PDO::FETCH_ASSOC);

    return hydrateCasinoDetails($database, $casino ?: null);
}

function fetchCasinoById(PDO $database, int $casinoId): ?array
{
    $statement = $database->prepare('SELECT * FROM casinos WHERE id = :id LIMIT 1');
    $statement->execute([':id' => $casinoId]);

    $casino = $statement->fetch(PDO::FETCH_ASSOC);

    return hydrateCasinoDetails($database, $casino ?: null);
}

function hydrateCasinoDetails(PDO $database, ?array $casino): ?array
{
    if (!$casino) {
        return null;
    }

    $casinoId = (int) $casino['id'];
    $tags = fetchCasinoTags($database, $casinoId);
    $genres = array_filter($tags, static fn($tag) => $tag['type'] === 'genre');
    $perks = array_filter($tags, static fn($tag) => $tag['type'] === 'perk');
    $categories = array_filter($tags, static fn($tag) => $tag['type'] === 'category');

    $casino['genres'] = array_column($genres, 'name');
    $casino['perks'] = array_column($perks, 'name');
    $casino['categories'] = array_column($categories, 'name');

    $casino['pros_cons'] = fetchCasinoProsCons($database, $casinoId);
    $casino['highlights'] = fetchCasinoHighlights($database, $casinoId);
    $casino['games'] = fetchCasinoGameModes($database, $casinoId);
    $casino['payment_methods'] = fetchCasinoPaymentMethods($database, $casinoId);
    $casino['providers'] = fetchCasinoProviders($database, $casinoId);
    $casino['devices'] = fetchCasinoDevices($database, $casinoId);
    $casino['review_sections'] = fetchCasinoReviewSections($database, $casinoId, $casino);

    return $casino;
}

function fetchFirstCasino(PDO $database): ?array
{
    $statement = $database->query('SELECT slug FROM casinos ORDER BY id ASC LIMIT 1');
    $slug = $statement->fetchColumn();

    return $slug ? fetchCasinoBySlug($database, (string) $slug) : null;
}

function fetchTopCasino(PDO $database): ?array
{
    $statement = $database->query('SELECT slug FROM casinos WHERE is_top1 = 1 ORDER BY id ASC LIMIT 1');
    $slug = $statement->fetchColumn();

    return $slug ? fetchCasinoBySlug($database, (string) $slug) : null;
}

function formatMinDeposit(?int $amount): string
{
    if ($amount === null) {
        return '';
    }

    return 'Minimum deposit $' . number_format($amount);
}

function renderRatingStars(?int $rating): string
{
    $rating = is_numeric($rating) ? (int) $rating : 0;
    $rating = max(0, min(5, $rating));

    $html = '';
    for ($i = 0; $i < 5; $i += 1) {
        $isFilled = $i < $rating;
        $html .= sprintf(
            '<i class="fa %s" aria-hidden="true"></i>',
            $isFilled ? 'fa-star' : 'fa-star-o'
        );
    }

    return $html;
}

function fetchContentCards(PDO $database, string $section): array
{
    $statement = $database->prepare(
        'SELECT title, category, badge, description, image_path, position FROM content_cards WHERE section = :section ORDER BY position ASC, id ASC'
    );
    $statement->execute([':section' => $section]);

    return $statement->fetchAll(PDO::FETCH_ASSOC) ?: [];
}

function slugifyTag(string $value): string
{
    $value = strtolower(trim($value));
    $value = preg_replace('/[^a-z0-9]+/', '-', $value) ?? '';
    return trim($value, '-') ?: 'tag';
}

function buildCasinoDataPayload(PDO $database): array
{
    $statement = $database->query('SELECT slug FROM casinos ORDER BY id ASC');
    $catalog = [];

    foreach ($statement->fetchAll(PDO::FETCH_COLUMN) as $slug) {
        $casino = fetchCasinoBySlug($database, (string) $slug);
        if (!$casino) {
            continue;
        }

        $casinoId = (int) $casino['id'];
        $genres = $casino['genres'] ?? [];
        $perks = $casino['perks'] ?? [];
        $categories = $casino['categories'] ?? [];
        $prosCons = $casino['pros_cons'] ?? ['pros' => [], 'cons' => []];

        $games = array_map(
            static fn($game) => [
                'title' => $game['game_type'],
                'liveDealer' => (bool) $game['live_dealer_supported'],
                'virtualReality' => (bool) $game['virtual_reality_supported'],
            ],
            $casino['games'] ?? []
        );

        $catalog[$casino['slug']] = [
            'id' => $casino['slug'],
            'name' => $casino['name'],
            'cardImage' => $casino['thumbnail_image'] ?: $casino['hero_image'],
            'heroImage' => $casino['hero_image'] ?: $casino['thumbnail_image'],
            'minDepositLabel' => formatMinDeposit(is_numeric($casino['min_deposit_usd']) ? (int) $casino['min_deposit_usd'] : null),
            'bonusHeadline' => $casino['headline_bonus'] ?: '',
            'summary' => $casino['short_description'] ?: '',
            'operator' => $casino['operator'] ?: '',
            'genres' => implode(', ', $genres),
            'tags' => implode(', ', $perks),
            'license' => $casino['license'] ?: '',
            'descriptionPrimary' => $casino['short_description'] ?: '',
            'descriptionSecondary' => $casino['short_description'] ?: '',
            'games' => $games,
            'pros' => $prosCons['pros'] ?? [],
            'cons' => $prosCons['cons'] ?? [],
            'ctaUrl' => $casino['cta_url'] ?? '',
            'rating' => (int) $casino['rating'],
        ];
    }

    return $catalog;
}
