<?php

declare(strict_types=1);

require_once __DIR__ . '/includes/bootstrap.php';

$database = getDatabase();
$activePage = 'all';
$casinoDirectory = fetchCasinoDirectory($database);
$casinos = fetchCasinosWithCategories($database);
$pageTitle = 'Lugx Gaming - All Casinos Page';

include __DIR__ . '/partials/html-head.php';
include __DIR__ . '/partials/header.php';
?>

  <div class="page-heading header-text">
    <div class="container">
      <div class="row">
        <div class="col-lg-12">
          <h3>All Casinos</h3>
          <span class="breadcrumb"><a href="#">Home</a> > All Casinos</span>
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
      <div class="row trending-box">
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
          <div class="col-lg-3 col-md-6 align-self-center mb-30 <?= htmlspecialchars($classString, ENT_QUOTES, 'UTF-8') ?>" data-casino-id="<?= htmlspecialchars($casino['slug'], ENT_QUOTES, 'UTF-8') ?>">
            <div class="item">
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
          <ul class="pagination">
          <li><a href="#"> &lt; </a></li>
            <li><a href="#">1</a></li>
            <li><a class="is_active" href="#">2</a></li>
            <li><a href="#">3</a></li>
            <li><a href="#"> &gt; </a></li>
          </ul>
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
