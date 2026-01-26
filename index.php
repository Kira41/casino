<?php

declare(strict_types=1);

require_once __DIR__ . '/includes/bootstrap.php';

$database = getDatabase();
$activePage = 'home';
$casinoDirectory = fetchCasinoDirectory($database);
$hotPicks = fetchCasinoCards($database, 'hot_picks');
$mostPlayed = fetchCasinoCards($database, 'most_played');
$topFeaturedCards = fetchCasinoCards($database, 'top_1');
$categories = fetchCategoryCards($database, 'top_categories');
$topFeaturedCard = $topFeaturedCards[0] ?? null;

if ($topFeaturedCard !== null) {
    $topCasinoImage = $topFeaturedCard['image_path'] ?? '';
    $topCasinoName = $topFeaturedCard['name'] ?? 'Top Casino';
    $topCasinoSlug = $topFeaturedCard['slug'] ?? '';
    $topCasinoMinDeposit = $topFeaturedCard['min_deposit_label'] ?: formatMinDeposit(
        isset($topFeaturedCard['min_deposit_usd']) && is_numeric($topFeaturedCard['min_deposit_usd'])
            ? (int) $topFeaturedCard['min_deposit_usd']
            : null
    );
} else {
    $topCasino = fetchTopCasino($database) ?? fetchFirstCasino($database);
    $topCasinoImage = $topCasino['hero_image'] ?? '';
    if ($topCasinoImage === '') {
        $topCasinoImage = $topCasino['thumbnail_image'] ?? '';
    }
    $topCasinoName = $topCasino['name'] ?? 'Top Casino';
    $topCasinoSlug = $topCasino['slug'] ?? '';
    $topCasinoMinDeposit = formatMinDeposit(
        isset($topCasino['min_deposit_usd']) && is_numeric($topCasino['min_deposit_usd'])
            ? (int) $topCasino['min_deposit_usd']
            : null
    );
}

if ($topCasinoImage === '') {
    $topCasinoImage = 'assets/images/banner-image.jpg';
}

$topCasinoLink = $topCasinoSlug !== '' ? 'product-details.php?casino=' . urlencode($topCasinoSlug) : 'product-details.php?casino=1';
$additionalScripts = ['assets/js/casino-detail.js'];
$pageTitle = 'Lugx Gaming All Casinos HTML5 Template';

include __DIR__ . '/partials/html-head.php';
include __DIR__ . '/partials/header.php';
?>

  <div class="main-banner">
    <div class="container">
      <div class="row">
        <div class="col-lg-6 align-self-center">
          <div class="caption header-text">
            <h6>Welcome to timelord casino review</h6>
            <h2>BEST CASINO SITES EVER!</h2>
            <p>Timelord Casino Review is your portal to honest rankings, trusted operator breakdowns, and the latest promotions worth your chips. Explore our expert picks, compare the perks, and find the perfect place to play with complete confidence.</p>
            <div class="search-input">
              <form id="search" action="#" data-casino-search-form>
                <input type="text" placeholder="Type Something" id='searchText' name="searchKeyword" data-casino-search />
                <button type="submit">Search Now</button>
              </form>
              <div class="search-results" data-search-results aria-live="polite"></div>
            </div>
          </div>
        </div>
        <div class="col-lg-4 offset-lg-2">
          <a class="right-image d-inline-block" href="<?= htmlspecialchars($topCasinoLink, ENT_QUOTES, 'UTF-8') ?>">
            <img src="<?= htmlspecialchars($topCasinoImage, ENT_QUOTES, 'UTF-8') ?>" alt="<?= htmlspecialchars($topCasinoName, ENT_QUOTES, 'UTF-8') ?>">
            <?php if ($topCasinoMinDeposit !== ''): ?>
              <span class="price"><?= htmlspecialchars($topCasinoMinDeposit, ENT_QUOTES, 'UTF-8') ?></span>
            <?php endif; ?>
            <span class="offer">TOP 1</span>
          </a>
        </div>
      </div>
    </div>
  </div>

  <div class="features">
    <div class="container">
      <div class="row">
        <div class="col-lg-3 col-md-6">
          <div class="item">
            <a class="d-block text-decoration-none text-reset" href="top-bonus-guides.php">
              <div class="image">
                <i class="fa fa-gift" aria-hidden="true" style="font-size: 44px;"></i>
              </div>
              <h4>Top Bonus Guides</h4>
            </a>
          </div>
        </div>
        <div class="col-lg-3 col-md-6">
          <div class="item">
            <a class="d-block text-decoration-none text-reset" href="fast-payout-casinos.php">
              <div class="image">
                <i class="fa fa-bolt" aria-hidden="true" style="font-size: 44px;"></i>
              </div>
              <h4>Fast Payout Casinos</h4>
            </a>
          </div>
        </div>
        <div class="col-lg-3 col-md-6">
          <div class="item">
            <a class="d-block text-decoration-none text-reset" href="game-library-highlights.php">
              <div class="image">
                <i class="fa fa-gamepad" aria-hidden="true" style="font-size: 44px;"></i>
              </div>
              <h4>Game Library Highlights</h4>
            </a>
          </div>
        </div>
        <div class="col-lg-3 col-md-6">
          <div class="item">
            <a class="d-block text-decoration-none text-reset" href="vip-loyalty-insights.php">
              <div class="image">
                <i class="fa fa-gem" aria-hidden="true" style="font-size: 44px;"></i>
              </div>
              <h4>VIP & Loyalty Insights</h4>
            </a>
          </div>
        </div>
      </div>
    </div>
  </div>

  <div class="platform-showcase" id="platform-access">
    <div class="container">
      <div class="row align-items-center mb-4">
        <div class="col-lg-12">
          <div class="section-heading">
            <h6>Platform Foundations</h6>
            <h2>Sign in, subscribe, and stay in sync</h2>
          </div>
        </div>
      </div>
      <div class="row g-4">
        <div class="col-lg-4 col-md-6">
          <div class="platform-card h-100">
            <div class="icon">
              <i class="fa fa-sign-in" aria-hidden="true"></i>
            </div>
            <h4>Sign into the platform</h4>
            <span class="tagline">Secure access for members who want personalized casino lineups.</span>
            <p>Use the new sign-in modal to pick up right where you left off and sync preferences across devices.</p>
            <span class="badge-soft">Live access</span>
          </div>
        </div>
        <div class="col-lg-4 col-md-6">
          <div class="platform-card h-100">
            <div class="icon">
              <i class="fa fa-database" aria-hidden="true"></i>
            </div>
            <h4>Data-backed insights</h4>
            <span class="tagline">Real-time updates from a dependable platform.</span>
            <p>Casino rankings, bonus terms, and payout speeds are refreshed through our content services for reliable insights.</p>
            <ul class="platform-meta">
              <li><i class="fa fa-check-circle" aria-hidden="true"></i><span>Daily freshness checks</span></li>
              <li><i class="fa fa-check-circle" aria-hidden="true"></i><span>Replicated reads for speed</span></li>
              <li><i class="fa fa-check-circle" aria-hidden="true"></i><span>Export-ready reporting views</span></li>
            </ul>
          </div>
        </div>
        <div class="col-lg-4 col-md-6">
          <div class="platform-card h-100">
            <div class="icon">
              <i class="fa fa-envelope-open" aria-hidden="true"></i>
            </div>
            <h4>Subscribe now</h4>
            <span class="tagline">Weekly drops tailored to your play style.</span>
            <p>Sign up to get curated bonus alerts, payout comparisons, and new casino launches as soon as they land.</p>
            <div class="d-flex flex-wrap gap-2">
              <a class="btn btn-accent" href="#subscribe-now" data-open-subscribe>Subscribe Now</a>
              <a class="btn btn-brand" href="#" data-bs-toggle="modal" data-bs-target="#signInModal">Sign In</a>
            </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>

  <div class="section trending">
    <div class="container">
      <div class="row">
        <div class="col-lg-6">
          <div class="section-heading">
            <h6 data-top-picks-subtitle>Hot Picks</h6>
            <h2 data-top-picks-heading>Top Picks</h2>
          </div>
        </div>
        <div class="col-lg-6">
          <div class="main-button">
            <a href="all-casinos.php">View All</a>
          </div>
        </div>
        <?php foreach ($hotPicks as $card): ?>
          <div class="col-lg-3 col-md-6">
            <div class="item" data-casino-id="<?= htmlspecialchars($card['slug'], ENT_QUOTES, 'UTF-8') ?>">
              <div class="thumb">
                <a href="product-details.php?casino=<?= urlencode($card['slug']) ?>"><img src="<?= htmlspecialchars($card['image_path'], ENT_QUOTES, 'UTF-8') ?>" alt="<?= htmlspecialchars($card['name'], ENT_QUOTES, 'UTF-8') ?>" data-casino-card-image></a>
                <?php $minDeposit = $card['min_deposit_label'] ?: formatMinDeposit(is_numeric($card['min_deposit_usd']) ? (int) $card['min_deposit_usd'] : null); ?>
                <?php if ($minDeposit): ?>
                  <span class="price" data-casino-card-offer><?= htmlspecialchars($minDeposit, ENT_QUOTES, 'UTF-8') ?></span>
                <?php endif; ?>
              </div>
              <div class="down-content">
                <span class="category" data-casino-rating aria-label="Rating"><?= renderRatingStars($card['rating']) ?></span>
                <h4 data-casino-card-name><?= htmlspecialchars($card['name'], ENT_QUOTES, 'UTF-8') ?></h4>
                <a href="product-details.php?casino=<?= urlencode($card['slug']) ?>"><i class="fa fa-external-link-square"></i></a>
              </div>
            </div>
          </div>
        <?php endforeach; ?>
      </div>
    </div>
  </div>

  <div class="section most-played">
    <div class="container">
      <div class="row">
        <div class="col-lg-6">
          <div class="section-heading">
            <h6>Top Casinos</h6>
            <h2>Most Played Destinations</h2>
          </div>
        </div>
        <div class="col-lg-6">
          <div class="main-button">
            <a href="all-casinos.php">View All</a>
          </div>
        </div>
        <?php foreach ($mostPlayed as $card): ?>
          <div class="col-lg-2 col-md-6 col-sm-6">
            <div class="item" data-casino-id="<?= htmlspecialchars($card['slug'], ENT_QUOTES, 'UTF-8') ?>">
              <div class="thumb">
                <a href="product-details.php?casino=<?= urlencode($card['slug']) ?>"><img src="<?= htmlspecialchars($card['image_path'], ENT_QUOTES, 'UTF-8') ?>" alt="<?= htmlspecialchars($card['name'], ENT_QUOTES, 'UTF-8') ?>" data-casino-card-image></a>
              </div>
              <div class="down-content">
                  <span class="category" data-casino-rating aria-label="Rating"></span>
                  <h4 data-casino-card-name><?= htmlspecialchars($card['name'], ENT_QUOTES, 'UTF-8') ?></h4>
                  <a href="product-details.php?casino=<?= urlencode($card['slug']) ?>">Read</a>
              </div>
            </div>
          </div>
        <?php endforeach; ?>
      </div>
    </div>
  </div>

  <div class="section categories">
    <div class="container">
      <div class="row">
        <div class="col-lg-12 text-center">
          <div class="section-heading">
            <h6>Casino Guides</h6>
            <h2>Top Categories</h2>
          </div>
        </div>
        <?php foreach ($categories as $category): ?>
          <div class="col-lg col-sm-6 col-xs-12">
            <div class="item">
              <h4><?= htmlspecialchars($category['title'], ENT_QUOTES, 'UTF-8') ?></h4>
              <div class="thumb">
                <?php
                $categorySlug = slugifyTag((string) ($category['title'] ?? ''));
                $categoryImagePath = (string) ($category['image_path'] ?? '');
                $categoryImageOverrides = [
                    'slots-jackpots' => 'assets/images/slots-jackpots.png',
                    'live-dealer-tables' => 'assets/images/live-dealer-tables.png',
                    'sports-betting' => 'assets/images/sports-betting.png',
                    'vip-programs' => 'assets/images/vip-programs.png',
                    'crypto-casinos' => 'assets/images/crypto-casinos.png',
                ];
                $legacyCategoryImages = [
                    'categories-01.jpg' => 'assets/images/slots-jackpots.png',
                    'categories-03.jpg' => 'assets/images/sports-betting.png',
                    'categories-04.jpg' => 'assets/images/vip-programs.png',
                    'categories-05.jpg' => 'assets/images/live-dealer-tables.png',
                ];
                $categoryImageBasename = basename($categoryImagePath);
                if ($categoryImagePath === '' && isset($categoryImageOverrides[$categorySlug])) {
                    $categoryImagePath = $categoryImageOverrides[$categorySlug];
                } elseif (isset($legacyCategoryImages[$categoryImageBasename])) {
                    $categoryImagePath = $categoryImageOverrides[$categorySlug] ?? $legacyCategoryImages[$categoryImageBasename];
                }
                if ($categoryImagePath !== '' && !str_contains($categoryImagePath, '/')) {
                    $categoryImagePath = 'assets/images/' . $categoryImagePath;
                }
                ?>
                <a href="all-casinos.php?category=<?= urlencode($categorySlug) ?>"><img src="<?= htmlspecialchars($categoryImagePath, ENT_QUOTES, 'UTF-8') ?>" alt="<?= htmlspecialchars($category['title'], ENT_QUOTES, 'UTF-8') ?>"></a>
              </div>
            </div>
          </div>
        <?php endforeach; ?>
      </div>
    </div>
  </div>
  
  <div class="section cta" id="subscribe-now">
    <div class="container">
      <div class="row">
        <div class="col-lg-5">
          <div class="shop">
            <div class="row">
              <div class="col-lg-12">
                <div class="section-heading">
                  <h6>Expert Rankings</h6>
                  <h2>Compare Bonuses & Unlock the <em>Best</em> Casino Deals!</h2>
                </div>
                <p>We sift through wagering terms, payment limits, and loyalty perks so you can land at the table with the right expectations.</p>
                <div class="main-button">
                  <a href="all-casinos.php">See Top Offers</a>
                </div>
              </div>
            </div>
          </div>
        </div>
        <div class="col-lg-5 offset-lg-2 align-self-end">
          <div class="subscribe">
            <div class="row">
              <div class="col-lg-12">
                <div class="section-heading">
                  <h6>INSIDER UPDATES</h6>
                  <h2>Get Weekly Bonus Alerts When You <em>Subscribe</em> Today!</h2>
                </div>
                <p class="mb-3">Every alert is generated from our research desk so you always get fresh limits, wagering rules, and payout speeds.</p>
                <div class="search-input">
                  <form id="subscribe" data-subscribe-form action="#">
                    <input type="email" class="form-control" id="exampleInputEmail1" aria-describedby="emailHelp" placeholder="Your email..." required>
                    <button type="submit">Subscribe Now</button>
                  </form>
                  <p class="small text-muted mt-2 mb-0" data-subscribe-status aria-live="polite"></p>
                </div>
                <div class="mt-3 d-flex flex-wrap gap-2">
                  <span class="badge-soft"><i class="fa fa-shield" aria-hidden="true"></i>Data secured and private</span>
                  <span class="badge-soft"><i class="fa fa-bolt" aria-hidden="true"></i>Instant alerts</span>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>

<?php include __DIR__ . '/partials/footer.php'; ?>
