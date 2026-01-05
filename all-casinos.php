<?php

declare(strict_types=1);

require_once __DIR__ . '/includes/bootstrap.php';

$database = getDatabase();
$activePage = 'all';
$casinoDirectory = fetchCasinoDirectory($database);
$categorySlug = isset($_GET['category']) ? trim((string) $_GET['category']) : '';
$normalizedCategorySlug = slugifyTag($categorySlug);
$allCasinos = fetchCasinosWithCategories($database);
$categoryLabel = '';
$categoryStats = [];

foreach ($allCasinos as $casino) {
    foreach ($casino['categories'] ?? [] as $categoryName) {
        $slug = slugifyTag((string) $categoryName);
        if ($slug === '') {
            continue;
        }

        if (!isset($categoryStats[$slug])) {
            $categoryStats[$slug] = [
                'name' => $categoryName,
                'count' => 0,
            ];
        }

        $categoryStats[$slug]['count'] += 1;
    }
}

ksort($categoryStats);

$hasCategoryFilter = $normalizedCategorySlug !== '' && isset($categoryStats[$normalizedCategorySlug]);

if (!$hasCategoryFilter) {
    $normalizedCategorySlug = '';
}

if ($hasCategoryFilter) {
    $categoryLabel = $categoryStats[$normalizedCategorySlug]['name'];
}

$filteredCasinos = $hasCategoryFilter
    ? array_values(array_filter(
        $allCasinos,
        static fn(array $casino): bool => casinoHasCategory($casino, $normalizedCategorySlug)
    ))
    : $allCasinos;

$casinosPerPage = 9;
$totalCasinos = count($filteredCasinos);
$totalTrackedCasinos = count($allCasinos);
$totalPages = (int) max(1, ceil($totalCasinos / $casinosPerPage));
$currentPage = isset($_GET['page']) ? max(1, (int) $_GET['page']) : 1;

if ($currentPage > $totalPages) {
    $currentPage = $totalPages;
}

$offset = ($currentPage - 1) * $casinosPerPage;
$casinos = array_slice($filteredCasinos, $offset, $casinosPerPage);

$paginationQueryParams = [];
if ($normalizedCategorySlug !== '') {
    $paginationQueryParams['category'] = $normalizedCategorySlug;
}
$paginationBase = 'all-casinos.php';
$buildPageUrl = static function (int $page) use ($paginationBase, $paginationQueryParams): string {
    return $paginationBase . '?' . http_build_query(array_merge($paginationQueryParams, ['page' => $page]));
};
$buildCategoryUrl = static function (?string $category) use ($paginationBase): string {
    if ($category === null || $category === '') {
        return $paginationBase;
    }

    return $paginationBase . '?' . http_build_query(['category' => $category]);
};
$pageTitle = 'Lugx Gaming - All Casinos Page';

include __DIR__ . '/partials/html-head.php';
include __DIR__ . '/partials/header.php';
?>

  <div class="page-heading header-text">
    <div class="container">
      <div class="row">
        <div class="col-lg-12">
          <h3><?= $categoryLabel !== '' ? htmlspecialchars($categoryLabel, ENT_QUOTES, 'UTF-8') : 'All Casinos' ?></h3>
          <span class="breadcrumb">
            <a href="#">Home</a>
            > <a href="all-casinos.php">All Casinos</a>
            <?php if ($categoryLabel !== ''): ?>
              > <span><?= htmlspecialchars($categoryLabel, ENT_QUOTES, 'UTF-8') ?></span>
            <?php endif; ?>
          </span>
        </div>
      </div>
    </div>
  </div>

  <div class="section trending">
    <div class="container">
      <div class="row align-items-center justify-content-between g-3 casino-grid-header">
        <div class="col-lg-8">
          <div class="section-heading mb-0">
            <h6>Casino Library</h6>
            <h2 class="mb-2">Explore every casino we track</h2>
            <p class="lead text-muted mb-0">Filter by category and browse our verified listings.</p>
          </div>
        </div>
        <div class="col-lg-4">
          <div class="casino-grid-meta d-flex flex-wrap gap-2 justify-content-lg-end">
            <span class="badge-soft">
              <i class="fa fa-database" aria-hidden="true"></i>
              <?= $totalCasinos ?> <?= $categoryLabel !== '' ? htmlspecialchars($categoryLabel, ENT_QUOTES, 'UTF-8') . ' ' : '' ?>casinos
            </span>
            <?php if ($totalTrackedCasinos !== $totalCasinos): ?>
              <span class="badge-soft"><i class="fa fa-archive" aria-hidden="true"></i>Total: <?= $totalTrackedCasinos ?></span>
            <?php endif; ?>
            <?php if (!empty($categoryStats)): ?>
              <span class="badge-soft"><i class="fa fa-th-large" aria-hidden="true"></i><?= count($categoryStats) ?> categories</span>
            <?php endif; ?>
          </div>
        </div>
      </div>
      <div class="casino-filter-bar">
        <div class="filter-label text-muted d-flex flex-column flex-sm-row align-items-sm-center gap-1 mb-0">
          <span>Filter by category</span>
          <span class="filter-summary text-secondary small">
            Showing <?= $totalCasinos ?> of <?= $totalTrackedCasinos ?> casinos<?= $categoryLabel !== '' ? ' in ' . htmlspecialchars($categoryLabel, ENT_QUOTES, 'UTF-8') : '' ?>
          </span>
        </div>
        <ul class="trending-filter flex-wrap" data-filter-mode="server" aria-label="Filter casinos by category">
          <li>
            <a
              class="<?= $normalizedCategorySlug === '' ? 'is_active' : '' ?>"
              data-filter="*"
              data-filter-url="<?= htmlspecialchars($buildCategoryUrl(null), ENT_QUOTES, 'UTF-8') ?>"
              href="<?= htmlspecialchars($buildCategoryUrl(null), ENT_QUOTES, 'UTF-8') ?>"
              aria-current="<?= $normalizedCategorySlug === '' ? 'page' : 'false' ?>"
            >
              <span class="filter-name">Show All</span>
              <span class="filter-count"><?= $totalTrackedCasinos ?></span>
            </a>
          </li>
          <?php foreach ($categoryStats as $categorySlugKey => $categoryMeta): ?>
            <li>
              <a
                class="<?= $normalizedCategorySlug !== '' && $categorySlugKey === $normalizedCategorySlug ? 'is_active' : '' ?>"
                data-filter=".category-<?= htmlspecialchars($categorySlugKey, ENT_QUOTES, 'UTF-8') ?>"
                data-filter-url="<?= htmlspecialchars($buildCategoryUrl($categorySlugKey), ENT_QUOTES, 'UTF-8') ?>"
                href="<?= htmlspecialchars($buildCategoryUrl($categorySlugKey), ENT_QUOTES, 'UTF-8') ?>"
                aria-current="<?= $normalizedCategorySlug !== '' && $categorySlugKey === $normalizedCategorySlug ? 'page' : 'false' ?>"
              >
                <span class="filter-name"><?= htmlspecialchars($categoryMeta['name'], ENT_QUOTES, 'UTF-8') ?></span>
                <span class="filter-count"><?= $categoryMeta['count'] ?></span>
              </a>
            </li>
          <?php endforeach; ?>
        </ul>
      </div>
      <div class="trending-box row row-cols-1 row-cols-md-2 row-cols-xl-3 g-4" data-layout-mode="fitRows">
        <?php if ($totalCasinos === 0): ?>
          <div class="col-12">
            <div class="alert alert-warning mb-0" role="alert">
              No casinos found for this category. <a href="all-casinos.php" class="alert-link">View all casinos</a>.
            </div>
          </div>
        <?php endif; ?>
        <?php foreach ($casinos as $casino): ?>
          <?php
            $categoryClasses = ['trending-items'];
            foreach ($casino['categories'] ?? [] as $categoryName) {
                $slug = slugifyTag((string) $categoryName);
                if ($slug === '') {
                    continue;
                }

                $categoryClasses[] = 'category-' . $slug;
            }
            $classString = implode(' ', array_unique($categoryClasses));
            $minDepositLabel = formatMinDeposit(is_numeric($casino['min_deposit_usd']) ? (int) $casino['min_deposit_usd'] : null);
          ?>
          <div class="col <?= htmlspecialchars($classString, ENT_QUOTES, 'UTF-8') ?>" data-casino-id="<?= htmlspecialchars($casino['slug'], ENT_QUOTES, 'UTF-8') ?>">
            <div class="casino-card h-100 d-flex flex-column">
              <div class="casino-card__thumb">
                <a class="casino-card__image" href="product-details.php?casino=<?= urlencode($casino['slug']) ?>">
                  <img src="<?= htmlspecialchars($casino['thumbnail_image'], ENT_QUOTES, 'UTF-8') ?>" alt="<?= htmlspecialchars($casino['name'], ENT_QUOTES, 'UTF-8') ?>" data-casino-card-image>
                </a>
                <?php if ($minDepositLabel): ?>
                  <span class="casino-card__badge" data-casino-card-offer><?= htmlspecialchars($minDepositLabel, ENT_QUOTES, 'UTF-8') ?></span>
                <?php endif; ?>
              </div>
              <div class="casino-card__body d-flex flex-column flex-grow-1">
                <div class="d-flex align-items-start justify-content-between gap-2">
                  <div>
                    <h4 class="casino-card__title mb-1" data-casino-card-name><?= htmlspecialchars($casino['name'], ENT_QUOTES, 'UTF-8') ?></h4>
                    <div class="casino-card__meta">
                      <span class="casino-card__rating" data-casino-rating aria-label="Rating"><?= renderRatingStars($casino['rating']) ?></span>
                      <?php if ($minDepositLabel): ?>
                        <span class="casino-card__pill">Starts at <?= htmlspecialchars($minDepositLabel, ENT_QUOTES, 'UTF-8') ?></span>
                      <?php endif; ?>
                    </div>
                  </div>
                  <a class="btn btn-accent btn-sm" href="product-details.php?casino=<?= urlencode($casino['slug']) ?>">Details</a>
                </div>
                <?php if (!empty($casino['categories'])): ?>
                  <div class="casino-card__tags" aria-label="Casino categories">
                    <?php foreach ($casino['categories'] as $categoryName): ?>
                      <span class="casino-card__tag"><?= htmlspecialchars($categoryName, ENT_QUOTES, 'UTF-8') ?></span>
                    <?php endforeach; ?>
                  </div>
                <?php endif; ?>
              </div>
            </div>
          </div>
        <?php endforeach; ?>
      </div>
      <?php if ($totalPages > 1): ?>
        <nav aria-label="Casino pagination">
          <ul class="pagination pagination-bubbles justify-content-center mt-4">
            <li class="page-item prev <?= $currentPage <= 1 ? 'disabled' : '' ?>">
              <a class="page-link" href="<?= htmlspecialchars($buildPageUrl(max(1, $currentPage - 1)), ENT_QUOTES, 'UTF-8') ?>" aria-label="Previous page">
                Previous
              </a>
            </li>
            <?php for ($page = 1; $page <= $totalPages; $page += 1): ?>
              <li class="page-item pagination-number <?= $page === $currentPage ? 'active' : '' ?>">
                <a class="page-link" href="<?= htmlspecialchars($buildPageUrl($page), ENT_QUOTES, 'UTF-8') ?>">
                  <?= $page ?>
                </a>
              </li>
            <?php endfor; ?>
            <li class="page-item next <?= $currentPage >= $totalPages ? 'disabled' : '' ?>">
              <a class="page-link" href="<?= htmlspecialchars($buildPageUrl(min($totalPages, $currentPage + 1)), ENT_QUOTES, 'UTF-8') ?>" aria-label="Next page">
                Next
              </a>
            </li>
          </ul>
        </nav>
      <?php endif; ?>
    </div>
  </div>

  <div class="platform-subscribe" id="subscribe-now">
    <div class="container">
      <div class="row align-items-center gy-4">
        <div class="col-lg-7">
          <h4>Subscribe now for live platform intel</h4>
          <p>Stay signed in and receive casino updates, bonus changes, and new-site alerts before they trend.</p>
          <div class="d-flex flex-wrap gap-2">
            <span class="badge-soft"><i class="fa fa-database" aria-hidden="true"></i>Reliable data service</span>
            <span class="badge-soft"><i class="fa fa-bell" aria-hidden="true"></i>Instant notifications</span>
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
