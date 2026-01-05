<?php

declare(strict_types=1);

require_once __DIR__ . '/includes/bootstrap.php';

$database = getDatabase();
$activePage = 'all';
$casinoDirectory = fetchCasinoDirectory($database);
$categorySlug = isset($_GET['category']) ? trim((string) $_GET['category']) : '';
$casinos = $categorySlug === ''
    ? fetchCasinosWithCategories($database)
    : fetchCasinosByCategory($database, $categorySlug);
$categoryLabel = $categorySlug !== '' ? ucwords(str_replace('-', ' ', $categorySlug)) : '';
if ($categorySlug !== '' && !empty($casinos)) {
    foreach ($casinos[0]['categories'] ?? [] as $categoryName) {
        if (slugifyTag((string) $categoryName) === slugifyTag($categorySlug)) {
            $categoryLabel = $categoryName;
            break;
        }
    }
}
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
      <ul class="trending-filter">
        <li>
          <a class="is_active" href="#!" data-filter="*">Show All</a>
        </li>
        <li>
          <a href="#!" data-filter=".adv">Adventure</a>
        </li>
        <li>
          <a href="#!" data-filter=".str">Strategy</a>
        </li>
        <li>
          <a href="#!" data-filter=".rac">Racing</a>
        </li>
      </ul>
      <div class="trending-box row row-cols-1 row-cols-md-2 row-cols-xl-4 g-4" data-pagination-scope="all-casinos" data-items-per-page="4" data-layout-mode="fitRows">
        <?php if (empty($casinos)): ?>
          <div class="col-12">
            <div class="alert alert-warning mb-0" role="alert">
              No casinos found for this category. <a href="all-casinos.php" class="alert-link">View all casinos</a>.
            </div>
          </div>
        <?php endif; ?>
        <?php foreach ($casinos as $casino): ?>
          <?php
            $categoryClasses = ['trending-items'];
            $filterMap = [
                'adventure' => 'adv',
                'action' => 'adv',
                'strategy' => 'str',
                'racing' => 'rac',
            ];
            foreach ($casino['categories'] as $categoryName) {
                $slug = slugifyTag($categoryName);
                $categoryClasses[] = $filterMap[$slug] ?? substr($slug, 0, 3);
            }
            $classString = implode(' ', array_unique($categoryClasses));
            $minDepositLabel = formatMinDeposit(is_numeric($casino['min_deposit_usd']) ? (int) $casino['min_deposit_usd'] : null);
          ?>
          <div class="col <?= htmlspecialchars($classString, ENT_QUOTES, 'UTF-8') ?>" data-casino-id="<?= htmlspecialchars($casino['slug'], ENT_QUOTES, 'UTF-8') ?>" data-pagination-item>
            <div class="item h-100 d-flex flex-column">
              <div class="thumb">
                <a href="product-details.php?casino=<?= urlencode($casino['slug']) ?>"><img src="<?= htmlspecialchars($casino['thumbnail_image'], ENT_QUOTES, 'UTF-8') ?>" alt="<?= htmlspecialchars($casino['name'], ENT_QUOTES, 'UTF-8') ?>" data-casino-card-image></a>
                <?php if ($minDepositLabel): ?>
                  <span class="price" data-casino-card-offer><?= htmlspecialchars($minDepositLabel, ENT_QUOTES, 'UTF-8') ?></span>
                <?php endif; ?>
              </div>
              <div class="down-content">
                <span class="category" data-casino-rating aria-label="Rating"><?= renderRatingStars($casino['rating']) ?></span>
                <h4 data-casino-card-name><?= htmlspecialchars($casino['name'], ENT_QUOTES, 'UTF-8') ?></h4>
                <a href="product-details.php?casino=<?= urlencode($casino['slug']) ?>"><i class="fa fa-shopping-bag"></i></a>
              </div>
            </div>
          </div>
        <?php endforeach; ?>
      </div>
      <div class="row">
        <div class="col-lg-12">
          <ul class="pagination" data-pagination-controls-for="all-casinos" aria-label="Casino list pagination"></ul>
        </div>
      </div>
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
