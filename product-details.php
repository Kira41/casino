<?php

declare(strict_types=1);

require_once __DIR__ . '/includes/bootstrap.php';

$database = getDatabase();
$activePage = 'product';
$casinoDirectory = fetchCasinoDirectory($database);
$categorySlug = isset($_GET['category']) ? (string) $_GET['category'] : '';
$slug = isset($_GET['casino']) ? (string) $_GET['casino'] : '';
$categoryCasinos = $categorySlug !== '' ? fetchCasinosByCategory($database, $categorySlug) : [];
$casino = null;

if ($slug !== '') {
    if (ctype_digit($slug)) {
        $casino = fetchCasinoById($database, (int) $slug);
    } else {
        $casino = fetchCasinoBySlug($database, $slug);
    }
} elseif (!empty($categoryCasinos)) {
    $firstCategoryCasino = $categoryCasinos[0]['slug'] ?? '';
    $casino = $firstCategoryCasino !== '' ? fetchCasinoBySlug($database, (string) $firstCategoryCasino) : null;
} else {
    $casino = fetchFirstCasino($database);
}

if (!$casino) {
    http_response_code(404);
    echo 'Casino not found';
    exit;
}

$pageTitle = 'Lugx Gaming - Product Detail';
$categoryLabel = '';
if ($categorySlug !== '') {
    foreach ($casino['categories'] ?? [] as $categoryName) {
        if (slugifyTag((string) $categoryName) === slugifyTag($categorySlug)) {
            $categoryLabel = $categoryName;
            break;
        }
    }
    $categoryLabel = $categoryLabel !== '' ? $categoryLabel : ucwords(str_replace('-', ' ', $categorySlug));
}
$genres = implode(', ', $casino['genres'] ?? []);
$perks = implode(', ', $casino['perks'] ?? []);
$minDeposit = formatMinDeposit(is_numeric($casino['min_deposit_usd'] ?? null) ? (int) $casino['min_deposit_usd'] : null);
$rating = (int) ($casino['rating'] ?? 0);
$games = $casino['games'] ?? [];
$prosCons = $casino['pros_cons'] ?? ['pros' => [], 'cons' => []];
$highlights = $casino['highlights'] ?? [];
$reviewSections = $casino['review_sections'] ?? [];
$paymentMethods = $casino['payment_methods'] ?? [];
$providers = $casino['providers'] ?? [];
if (empty($providers)) {
    $providers = fetchProviders($database);
}
$hasReviews = !empty($reviewSections);
$reviewSectionAliases = [
    'banking-methods' => 'banking-methods',
    'banking' => 'banking-methods',
    'general-info' => 'general-information',
    'general-information' => 'general-information',
    'support' => 'support',
    'devices' => 'devices',
    'software-providers' => 'software-providers',
    'software-provider' => 'software-providers',
    'additional-info' => 'additional-info',
    'additional-information' => 'additional-info',
];
$reviewSectionOrder = [
    'general-information' => ['label' => 'General Information', 'icon' => 'fa-info-circle text-warning'],
    'banking-methods' => ['label' => 'Banking Methods', 'icon' => 'fa-credit-card text-warning'],
    'support' => ['label' => 'Support', 'icon' => 'fa-headset text-warning'],
    'devices' => ['label' => 'Devices', 'icon' => 'fa-desktop text-warning'],
    'software-providers' => ['label' => 'Software Providers', 'icon' => 'fa-cubes text-warning'],
    'additional-info' => ['label' => 'Additional Info', 'icon' => 'fa-plus-circle text-warning'],
];
$reviewSectionsByKey = [];
foreach ($reviewSections as $section) {
    $key = strtolower(trim((string) $section['title']));
    $key = preg_replace('/[^a-z0-9]+/', '-', $key) ?? '';
    $key = trim($key, '-');
    $canonicalKey = $reviewSectionAliases[$key] ?? $key;
    if ($canonicalKey === '') {
        continue;
    }
    if (!isset($reviewSectionsByKey[$canonicalKey])) {
        $reviewSectionsByKey[$canonicalKey] = $section;
    }
}
$orderedReviewSections = [];
foreach ($reviewSectionOrder as $key => $meta) {
    $section = $reviewSectionsByKey[$key] ?? ['title' => $meta['label'], 'summary' => '', 'points' => []];
    $section['title'] = $meta['label'];
    $section['icon'] = $meta['icon'];
    $section['key'] = $key;
    $orderedReviewSections[] = $section;
}
$defaultGameRows = [
    [
        'game_type' => 'Roulette',
        'live_dealer_supported' => false,
        'virtual_reality_supported' => false,
    ],
    [
        'game_type' => 'Slots',
        'live_dealer_supported' => false,
        'virtual_reality_supported' => false,
    ],
    [
        'game_type' => 'Blackjack',
        'live_dealer_supported' => false,
        'virtual_reality_supported' => false,
    ],
    [
        'game_type' => 'Video Poker',
        'live_dealer_supported' => false,
        'virtual_reality_supported' => false,
    ],
    [
        'game_type' => 'Scratch Cards',
        'live_dealer_supported' => false,
        'virtual_reality_supported' => false,
    ],
    [
        'game_type' => 'Keno',
        'live_dealer_supported' => false,
        'virtual_reality_supported' => false,
    ],
    [
        'game_type' => 'Craps',
        'live_dealer_supported' => false,
        'virtual_reality_supported' => false,
    ],
    [
        'game_type' => 'Bingo',
        'live_dealer_supported' => false,
        'virtual_reality_supported' => false,
    ],
    [
        'game_type' => 'Baccarat',
        'live_dealer_supported' => false,
        'virtual_reality_supported' => false,
    ],
];
$gamesByType = [];
foreach ($games as $gameRow) {
    $gameTypeKey = strtolower(trim((string) ($gameRow['game_type'] ?? '')));
    if ($gameTypeKey === '') {
        continue;
    }
    $gamesByType[$gameTypeKey] = $gameRow;
}
$gameRows = [];
foreach ($defaultGameRows as $defaultGameRow) {
    $gameTypeKey = strtolower(trim((string) ($defaultGameRow['game_type'] ?? '')));
    $gameRows[] = isset($gamesByType[$gameTypeKey])
        ? array_merge($defaultGameRow, $gamesByType[$gameTypeKey])
        : $defaultGameRow;
    unset($gamesByType[$gameTypeKey]);
}
$gameRows = array_merge($gameRows, array_values($gamesByType));
$uniqueGameRows = [];
$seenGameTypes = [];
foreach ($gameRows as $gameRow) {
    $gameTypeKey = strtolower(trim((string) ($gameRow['game_type'] ?? '')));
    if ($gameTypeKey === '') {
        continue;
    }
    if (isset($seenGameTypes[$gameTypeKey])) {
        continue;
    }
    $seenGameTypes[$gameTypeKey] = true;
    $uniqueGameRows[] = $gameRow;
}
$gameRows = !empty($uniqueGameRows) ? $uniqueGameRows : $gameRows;
$iconifyBase = 'https://api.iconify.design/';
$iconAccent = '#b33aa4';
$reviewIconColor = '#595c5f';
$gameTypeIcons = [
    'roulette' => 'game-icons:abstract-066',
    'slots' => 'mdi:slot-machine',
    'blackjack' => 'mdi:cards',
    'video-poker' => 'mdi:cards-playing-outline',
    'scratch-cards' => 'mdi:ticket-percent-outline',
    'keno' => 'mdi:chart-bubble',
    'craps' => 'mdi:dice-multiple',
    'bingo' => 'mdi:grid',
    'baccarat' => 'mdi:cards-playing',
    'poker' => 'mdi:cards-playing-outline',
    'live-shows' => 'mdi:account-voice',
    'crash-games' => 'mdi:rocket-launch',
    'live-roulette' => 'game-icons:abstract-066',
    'live-blackjack' => 'mdi:cards',
    'table-games' => 'mdi:table-furniture',
    'game-shows' => 'mdi:television-classic',
    'high-roller-tables' => 'mdi:diamond-stone',
    'live-dealer' => 'mdi:account-group',
];
$tableHeaderIcons = [
    'Game Type' => 'mdi:cards-playing-outline',
    'Live Dealer' => 'mdi:account-group',
    'Virtual Reality' => 'mdi:virtual-reality',
    'Reviews' => 'mdi:star-circle',
];
$deviceSupportGroups = buildDeviceSupportGroups($casino['devices'] ?? []);
$relatedCasinos = $categorySlug !== ''
    ? array_values(array_filter($categoryCasinos, static fn($card) => (string) ($card['slug'] ?? '') !== (string) $casino['slug']))
    : fetchCasinoCards($database, 'related');
if ($categorySlug !== '') {
    $relatedCasinos = array_map(static function (array $card): array {
        if (empty($card['image_path'])) {
            $card['image_path'] = $card['thumbnail_image'] ?? '';
        }
        return $card;
    }, $relatedCasinos);
}
$relatedCasinos = array_slice($relatedCasinos, 0, 20);
$headlineBonus = $casino['headline_bonus'] ?? '';
$additionalScripts = ['assets/js/casino-detail.js'];

include __DIR__ . '/partials/html-head.php';
include __DIR__ . '/partials/header.php';
?>

  <div class="page-heading header-text">
    <div class="container">
      <div class="row">
        <div class="col-lg-12">
          <h3><?= htmlspecialchars($casino['name'], ENT_QUOTES, 'UTF-8') ?></h3>
          <span class="breadcrumb"><a href="#">Home</a>  >  <a href="#">Casinos</a>  >  <span><?= htmlspecialchars($casino['name'], ENT_QUOTES, 'UTF-8') ?></span></span>
        </div>
      </div>
    </div>
  </div>

  <div class="single-product section">
    <div class="container">
      <div class="row">
        <div class="col-lg-6">
          <div class="left-image">
            <img src="<?= htmlspecialchars($casino['hero_image'] ?: $casino['thumbnail_image'], ENT_QUOTES, 'UTF-8') ?>" alt="<?= htmlspecialchars($casino['name'], ENT_QUOTES, 'UTF-8') ?>">
          </div>
        </div>
        <div class="col-lg-6 align-self-center product-details-copy">
          <h4><?= htmlspecialchars($casino['name'], ENT_QUOTES, 'UTF-8') ?></h4>
          <span class="price"><i class="fa fa-gift me-2"></i><?= htmlspecialchars($headlineBonus ?: 'Featured Welcome Bonus', ENT_QUOTES, 'UTF-8') ?></span>
          <div class="d-flex align-items-center gap-2 mb-2">
            <span class="category" aria-label="Rating"><?= renderRatingStars($rating) ?></span>
            <span class="small text-muted"><?= $rating ?> / 5</span>
          </div>
          <p><i class="fa fa-magic me-2 text-warning"></i><?= htmlspecialchars($casino['short_description'] ?? '', ENT_QUOTES, 'UTF-8') ?></p>
          <form id="qty" action="#" onsubmit="window.open('<?= htmlspecialchars($casino['cta_url'] ?: '#', ENT_QUOTES, 'UTF-8') ?>','_blank','noopener'); return false;">
            <button type="submit"><i class="fa fa-arrow-up-right-from-square me-2"></i><span class="ms-1">Visit Casino</span></button>
          </form>
          <ul class="product-meta-list">
            <li class="product-meta-item">
              <i class="fa fa-building me-2"></i>
              <span class="product-meta-label">Operator:</span>
              <span class="product-meta-value"><?= htmlspecialchars($casino['operator'] ?? $casino['name'], ENT_QUOTES, 'UTF-8') ?></span>
            </li>
            <li class="product-meta-item">
              <i class="fa fa-layer-group me-2"></i>
              <span class="product-meta-label">Genre:</span>
              <span class="product-meta-value"><?= htmlspecialchars($genres !== '' ? $genres : 'N/A', ENT_QUOTES, 'UTF-8') ?></span>
            </li>
            <li class="product-meta-item">
              <i class="fa fa-tags me-2"></i>
              <span class="product-meta-label">Multi-tags:</span>
              <span class="product-meta-value"><?= htmlspecialchars($perks !== '' ? $perks : 'N/A', ENT_QUOTES, 'UTF-8') ?></span>
            </li>
            <li class="product-meta-item">
              <i class="fa fa-shield-alt me-2"></i>
              <span class="product-meta-label">License:</span>
              <span class="product-meta-value"><?= htmlspecialchars($casino['license'] ?? 'TBD', ENT_QUOTES, 'UTF-8') ?></span>
            </li>
            <?php if ($minDeposit): ?>
              <li class="product-meta-item">
                <i class="fa fa-credit-card me-2"></i>
                <span class="product-meta-label">Minimum Deposit:</span>
                <span class="product-meta-value"><?= htmlspecialchars($minDeposit, ENT_QUOTES, 'UTF-8') ?></span>
              </li>
            <?php endif; ?>
          </ul>
        </div>
        <div class="col-lg-12">
          <div class="sep"></div>
        </div>
      </div>
    </div>
  </div>

  <div class="more-info">
    <div class="container">
      <div class="row">
        <div class="col-lg-12">
          <div class="tabs-content">
            <div class="row">
              <div class="nav-wrapper ">
                <ul class="nav nav-tabs" role="tablist">
                  <li class="nav-item" role="presentation">
                    <button class="nav-link active" id="description-tab" data-bs-toggle="tab" data-bs-target="#description" type="button" role="tab" aria-controls="description" aria-selected="true">Description</button>
                  </li>
                  <li class="nav-item" role="presentation">
                    <button class="nav-link" id="reviews-tab" data-bs-toggle="tab" data-bs-target="#reviews" type="button" role="tab" aria-controls="reviews" aria-selected="false">Reviews</button>
                  </li>
                </ul>
              </div>              
              <div class="tab-content" id="myTabContent">
                <div class="tab-pane fade show active" id="description" role="tabpanel" aria-labelledby="description-tab">
                  <p><i class="fa fa-dice text-warning me-2"></i><?= htmlspecialchars($casino['short_description'] ?? '', ENT_QUOTES, 'UTF-8') ?></p>
                  <div class="table-responsive mt-4">
                    <table class="table">
                      <thead>
                        <tr>
                          <?php foreach ($tableHeaderIcons as $label => $iconKey): ?>
                            <th>
                              <span class="d-inline-flex align-items-center gap-2">
                                <img class="table-header-icon" src="<?= htmlspecialchars($iconifyBase . $iconKey . '.svg?color=' . urlencode($iconAccent), ENT_QUOTES, 'UTF-8') ?>" alt="">
                                <span><?= htmlspecialchars($label, ENT_QUOTES, 'UTF-8') ?></span>
                              </span>
                            </th>
                          <?php endforeach; ?>
                        </tr>
                      </thead>
                      <tbody>
                        <?php foreach ($gameRows as $game): ?>
                          <?php
                          $gameTypeLabel = (string) $game['game_type'];
                          $gameTypeSlug = strtolower(trim($gameTypeLabel));
                          $gameTypeSlug = preg_replace('/[^a-z0-9]+/', '-', $gameTypeSlug) ?? '';
                          $gameTypeSlug = trim($gameTypeSlug, '-');
                          $gameTypeIconKey = $gameTypeIcons[$gameTypeSlug] ?? 'mdi:casino';
                          ?>
                          <tr>
                            <td>
                              <div class="d-flex align-items-center gap-2">
                                <img class="game-type-icon" src="<?= htmlspecialchars($iconifyBase . $gameTypeIconKey . '.svg?color=' . urlencode($iconAccent), ENT_QUOTES, 'UTF-8') ?>" alt="">
                                <span><?= htmlspecialchars($gameTypeLabel, ENT_QUOTES, 'UTF-8') ?></span>
                              </div>
                            </td>
                            <td><?= $game['live_dealer_supported'] ? '<i class="fa fa-check text-success"></i>' : '<i class="fa fa-times text-danger"></i>' ?></td>
                            <td><?= $game['virtual_reality_supported'] ? '<i class="fa fa-check text-success"></i>' : '<i class="fa fa-times text-danger"></i>' ?></td>
                            <td><?= $hasReviews ? '<i class="fa fa-check text-success"></i>' : '<i class="fa fa-times text-danger"></i>' ?></td>
                          </tr>
                        <?php endforeach; ?>
                      </tbody>
                    </table>
                  </div>
                </div>
                <div class="tab-pane fade" id="reviews" role="tabpanel" aria-labelledby="reviews-tab">
                  <div class="review-panel">
                    <div class="accordion review-accordion" id="reviewsAccordion">
                      <?php foreach ($orderedReviewSections as $index => $section): ?>
                        <?php $collapseId = 'section-' . $index; ?>
                        <?php $hasPaymentList = $section['key'] === 'banking-methods' && !empty($paymentMethods); ?>
                        <?php $hasProviderList = $section['key'] === 'software-providers' && !empty($providers); ?>
                        <?php $hasSectionContent = $hasPaymentList || $hasProviderList || !empty($section['summary']) || !empty($section['points']); ?>
                        <div class="accordion-item">
                          <h2 class="accordion-header" id="heading-<?= $collapseId ?>">
                            <button class="accordion-button <?= $index === 0 ? '' : 'collapsed' ?>" type="button" data-bs-toggle="collapse" data-bs-target="#collapse-<?= $collapseId ?>" aria-expanded="<?= $index === 0 ? 'true' : 'false' ?>" aria-controls="collapse-<?= $collapseId ?>">
                              <i class="fa <?= htmlspecialchars($section['icon'] ?? 'fa-info-circle text-warning', ENT_QUOTES, 'UTF-8') ?> me-2"></i>
                              <span><?= htmlspecialchars($section['title'], ENT_QUOTES, 'UTF-8') ?></span>
                            </button>
                          </h2>
                          <div id="collapse-<?= $collapseId ?>" class="accordion-collapse collapse <?= $index === 0 ? 'show' : '' ?>" aria-labelledby="heading-<?= $collapseId ?>" data-bs-parent="#reviewsAccordion">
                            <div class="accordion-body">
                              <?php if ($section['key'] === 'banking-methods'): ?>
                                <?php if (!empty($paymentMethods)): ?>
                                  <div class="payment-methods-list">
                                    <?php foreach ($paymentMethods as $method): ?>
                                      <?php
                                      $methodName = (string) ($method['method_name'] ?? ($method['name'] ?? ''));
                                      $iconKey = (string) ($method['icon_key'] ?? '');
                                      $isImage = $iconKey !== '' && (str_contains($iconKey, '/') || str_starts_with($iconKey, 'http'));
                                      $iconSrc = $isImage ? $iconKey : ($iconKey !== '' ? $iconifyBase . $iconKey . '.svg?color=' . urlencode($reviewIconColor) : '');
                                      ?>
                                      <div class="payment-method">
                                        <?php if ($iconSrc !== ''): ?>
                                          <img src="<?= htmlspecialchars($iconSrc, ENT_QUOTES, 'UTF-8') ?>" alt="<?= htmlspecialchars($methodName, ENT_QUOTES, 'UTF-8') ?>">
                                        <?php endif; ?>
                                        <span><?= htmlspecialchars($methodName, ENT_QUOTES, 'UTF-8') ?></span>
                                      </div>
                                    <?php endforeach; ?>
                                  </div>
                                <?php endif; ?>
                                <div class="row g-3 mt-3">
                                  <div class="col-md-6">
                                    <div class="d-flex align-items-start gap-3">
                                      <i class="fa fa-clock text-warning mt-1" aria-hidden="true"></i>
                                      <div>
                                        <p class="mb-1 fw-semibold">Processing times</p>
                                        <p class="mb-0 text-muted">Deposits are instant, while withdrawals can take 12–48 hours depending on the provider.</p>
                                      </div>
                                    </div>
                                  </div>
                                  <div class="col-md-6">
                                    <div class="d-flex align-items-start gap-3">
                                      <i class="fa fa-lock text-warning mt-1" aria-hidden="true"></i>
                                      <div>
                                        <p class="mb-1 fw-semibold">Security checks</p>
                                        <p class="mb-0 text-muted">Extra verification keeps payouts secure and helps prevent fraud.</p>
                                      </div>
                                    </div>
                                  </div>
                                  <div class="col-md-6">
                                    <div class="d-flex align-items-start gap-3">
                                      <i class="fa fa-money-bill-wave text-warning mt-1" aria-hidden="true"></i>
                                      <div>
                                        <p class="mb-1 fw-semibold">Fees &amp; limits</p>
                                        <p class="mb-0 text-muted">Check daily limits and possible fees for cards, e-wallets, and bank transfers.</p>
                                      </div>
                                    </div>
                                  </div>
                                  <div class="col-md-6">
                                    <div class="d-flex align-items-start gap-3">
                                      <i class="fa fa-mobile-alt text-warning mt-1" aria-hidden="true"></i>
                                      <div>
                                        <p class="mb-1 fw-semibold">Mobile-friendly banking</p>
                                        <p class="mb-0 text-muted">Popular wallets and instant banking apps are supported for on-the-go play.</p>
                                      </div>
                                    </div>
                                  </div>
                                </div>
                              <?php endif; ?>
                              <?php if ($hasProviderList): ?>
                                <div class="providers-list mb-3">
                                  <?php foreach ($providers as $provider): ?>
                                    <div class="provider-card">
                                      <img src="<?= htmlspecialchars($provider['image_path'], ENT_QUOTES, 'UTF-8') ?>" alt="<?= htmlspecialchars($provider['name'], ENT_QUOTES, 'UTF-8') ?>">
                                      <span><?= htmlspecialchars($provider['name'], ENT_QUOTES, 'UTF-8') ?></span>
                                    </div>
                                  <?php endforeach; ?>
                                </div>
                              <?php endif; ?>
                              <?php if ($section['key'] === 'devices'): ?>
                                <div class="row g-3 mb-3">
                                  <?php foreach ($deviceSupportGroups as $group): ?>
                                    <div class="col-md-6">
                                      <div class="bg-light border rounded-3 p-3 h-100">
                                        <div class="d-flex align-items-center gap-2 text-uppercase small fw-semibold text-muted mb-2">
                                          <i class="fa <?= htmlspecialchars($group['icon'], ENT_QUOTES, 'UTF-8') ?>" aria-hidden="true"></i>
                                          <span><?= htmlspecialchars($group['label'], ENT_QUOTES, 'UTF-8') ?></span>
                                        </div>
                                        <div class="d-flex flex-wrap gap-2">
                                          <?php foreach ($group['items'] as $item): ?>
                                            <span class="badge bg-white text-dark border d-inline-flex align-items-center gap-2">
                                              <i class="<?= htmlspecialchars($item['icon'], ENT_QUOTES, 'UTF-8') ?>" aria-hidden="true"></i>
                                              <span><?= htmlspecialchars($item['label'], ENT_QUOTES, 'UTF-8') ?></span>
                                            </span>
                                          <?php endforeach; ?>
                                        </div>
                                      </div>
                                    </div>
                                  <?php endforeach; ?>
                                </div>
                              <?php endif; ?>
                              <?php
                              $bankingHighlights = [];
                              if ($section['key'] === 'banking-methods') {
                                  if (!empty($section['summary'])) {
                                      $bankingHighlights[] = [
                                          'icon' => 'fa-credit-card text-warning',
                                          'content' => $section['summary'],
                                      ];
                                  }
                                  $bankingHighlights[] = [
                                      'icon' => 'fa-shield-alt text-warning',
                                      'content' => 'Some casinos require KYC verification before any withdrawal.',
                                  ];
                              }
                              $pointsToRender = $section['points'];
                              if (!empty($bankingHighlights)) {
                                  $pointsToRender = array_merge($bankingHighlights, $pointsToRender);
                              }
                              ?>
                              <?php if (!empty($section['summary']) && $section['key'] !== 'banking-methods'): ?>
                                <?php
                                $summaryClasses = 'mb-3';
                                ?>
                                <p class="<?= $summaryClasses ?>"><?= htmlspecialchars($section['summary'], ENT_QUOTES, 'UTF-8') ?></p>
                              <?php endif; ?>
                              <?php if (!empty($pointsToRender) && $section['key'] !== 'devices' && $section['key'] !== 'software-providers'): ?>
                                <div class="row g-3 align-items-start">
                                  <?php foreach ($pointsToRender as $point): ?>
                                    <?php
                                    $pointContent = (string) ($point['content'] ?? '');
                                    $pointIcon = trim((string) ($point['icon'] ?? ''));
                                    if ($pointIcon === '' && $section['key'] === 'support' && stripos($pointContent, 'Live chat:') === 0) {
                                        $liveChatValue = strtolower(trim(substr($pointContent, strlen('Live chat:'))));
                                        if (str_starts_with($liveChatValue, 'yes')) {
                                            $pointIcon = 'fa-check text-success';
                                        } elseif (str_starts_with($liveChatValue, 'no')) {
                                            $pointIcon = 'fa-times text-danger';
                                        }
                                    }
                                    ?>
                                    <div class="col-md-6">
                                      <div class="d-flex align-items-start">
                                        <?php if ($pointIcon !== ''): ?>
                                          <i class="fa <?= htmlspecialchars($pointIcon, ENT_QUOTES, 'UTF-8') ?> me-3 mt-1"></i>
                                        <?php endif; ?>
                                        <p class="mb-0"><?= htmlspecialchars($pointContent, ENT_QUOTES, 'UTF-8') ?></p>
                                      </div>
                                    </div>
                                  <?php endforeach; ?>
                                </div>
                              <?php endif; ?>
                              <?php if (!$hasSectionContent): ?>
                                <p class="mb-0 text-muted">Details coming soon.</p>
                              <?php endif; ?>
                            </div>
                          </div>
                        </div>
                      <?php endforeach; ?>
                      <div class="accordion-item">
                        <h2 class="accordion-header" id="headingProsCons">
                          <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseProsCons" aria-expanded="false" aria-controls="collapseProsCons">
                            <i class="fa fa-balance-scale text-muted me-2"></i>
                            <span class="ms-1">Pros &amp; Cons</span>
                          </button>
                        </h2>
                        <div id="collapseProsCons" class="accordion-collapse collapse" aria-labelledby="headingProsCons" data-bs-parent="#reviewsAccordion">
                          <div class="accordion-body">
                            <div class="row g-4">
                              <div class="col-md-6">
                                <h6 class="pros-cons-heading pros"><i class="fa fa-thumbs-up me-2 text-success"></i><span>Pros</span></h6>
                                <ul class="list-with-icons pros mb-0">
                                  <?php foreach ($prosCons['pros'] as $pro): ?>
                                    <li><i class="fa fa-check me-2 text-success"></i><span><?= htmlspecialchars($pro, ENT_QUOTES, 'UTF-8') ?></span></li>
                                  <?php endforeach; ?>
                                </ul>
                              </div>
                              <div class="col-md-6">
                                <h6 class="pros-cons-heading cons"><i class="fa fa-thumbs-down me-2 text-danger"></i><span>Cons</span></h6>
                                <ul class="list-with-icons cons mb-0">
                                  <?php foreach ($prosCons['cons'] as $con): ?>
                                    <li><i class="fa fa-times me-2 text-danger"></i><span><?= htmlspecialchars($con, ENT_QUOTES, 'UTF-8') ?></span></li>
                                  <?php endforeach; ?>
                                </ul>
                              </div>
                            </div>
                          </div>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>

  <div class="section categories related-games">
    <div class="container">
      <div class="row">
        <div class="col-lg-6">
          <div class="section-heading">
            <h6><?= htmlspecialchars($categoryLabel ?: 'Related', ENT_QUOTES, 'UTF-8') ?></h6>
            <h2><?= $categorySlug !== '' ? 'Casinos in this Category' : 'Related Casinos' ?></h2>
          </div>
        </div>
        <div class="col-lg-6">
          <div class="main-button">
            <a href="all-casinos.php">View All</a>
          </div>
        </div>
        <?php foreach ($relatedCasinos as $card): ?>
          <div class="col-lg col-sm-6 col-xs-12">
            <div class="item" data-casino-id="<?= htmlspecialchars($card['slug'], ENT_QUOTES, 'UTF-8') ?>">
              <h4><?= htmlspecialchars($card['name'], ENT_QUOTES, 'UTF-8') ?></h4>
              <div class="thumb">
                <a href="product-details.php?casino=<?= urlencode($card['slug']) ?><?= $categorySlug !== '' ? '&category=' . urlencode($categorySlug) : '' ?>"><img src="<?= htmlspecialchars($card['image_path'] ?? ($card['thumbnail_image'] ?? ''), ENT_QUOTES, 'UTF-8') ?>" alt="<?= htmlspecialchars($card['name'], ENT_QUOTES, 'UTF-8') ?>" data-casino-card-image></a>
              </div>
            </div>
          </div>
        <?php endforeach; ?>
      </div>
    </div>
  </div>

  <div class="platform-subscribe" id="subscribe-now">
    <div class="container">
      <div class="row align-items-center gy-4">
        <div class="col-lg-7">
          <h4>Subscribe now for platform changelogs</h4>
          <p>We pair sign-in activity with our recommendations engine to keep picks current—opt in to get the latest review drops.</p>
          <div class="d-flex flex-wrap gap-2">
            <span class="badge-soft"><i class="fa fa-database me-2" aria-hidden="true"></i>Trusted data updates</span>
            <span class="badge-soft"><i class="fa fa-star me-2" aria-hidden="true"></i>Personalized picks</span>
          </div>
        </div>
        <div class="col-lg-5">
          <form class="subscribe-form" data-subscribe-form>
            <div class="input-group">
              <input type="email" class="form-control" placeholder="Enter your email" aria-label="Email address" required>
              <button class="btn btn-accent" type="submit">Subscribe Now</button>
            </div>
          </form>
          <p class="small text-muted mt-2 mb-0" data-subscribe-status aria-live="polite"></p>
        </div>
      </div>
    </div>
  </div>

<?php include __DIR__ . '/partials/footer.php'; ?>
