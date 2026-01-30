<?php

declare(strict_types=1);

require_once __DIR__ . '/includes/bootstrap.php';

$database = getDatabase();
$activePage = 'payouts';
$casinoDirectory = fetchCasinoDirectory($database);
$payoutHighlights = fetchContentCards($database, 'fast_payout_highlights');
$payoutChecklist = fetchContentCards($database, 'fast_payout_checklist');
$pageTitle = 'Lugx Gaming - Fast Payout Casinos';
$imageSwap = [
    'assets/images/trending-01.jpg' => 'assets/images/fast-payout/crypto-cashouts.png',
    'assets/images/trending-02.jpg' => 'assets/images/fast-payout/e-wallets.png',
    'assets/images/trending-03.jpg' => 'assets/images/fast-payout/bank-wires.png',
    'assets/images/trending-04.jpg' => 'assets/images/fast-payout/VIP-queueing.png',
    'assets/images/top-game-05.jpg' => 'assets/images/fast-payout/processing-metrics.png',
    'assets/images/top-game-06.jpg' => 'assets/images/fast-payout/caps-escalations.png',
    'assets/images/top-game-07.jpg' => 'assets/images/fast-payout/KYC-refresh-rules.png',
    'assets/images/top-game-08.jpg' => 'assets/images/fast-payout/escalation-paths.png',
];

function payoutImage(string $path, array $imageSwap): string
{
    return $imageSwap[$path] ?? $path;
}

include __DIR__ . '/partials/html-head.php';
include __DIR__ . '/partials/header.php';
?>

  <div class="page-heading header-text">
    <div class="container">
      <div class="row">
        <div class="col-lg-12">
          <h3>Fast Payout Casinos</h3>
          <span class="breadcrumb"><a href="#">Home</a> > Fast Payout Casinos</span>
        </div>
      </div>
    </div>
  </div>

  <div class="section trending">
    <div class="container">
      <div class="row align-items-center mb-4">
        <div class="col-lg-8">
          <div class="section-heading">
            <h6>Speed Tracker</h6>
            <h2>Casinos that release winnings quickly</h2>
          </div>
        </div>
        <div class="col-lg-4 text-lg-end">
          <div class="main-button">
            <a href="#subscribe-now" data-open-subscribe>Get payout alerts</a>
          </div>
        </div>
      </div>
      <div class="row trending-box">
        <?php foreach ($payoutHighlights as $card): ?>
          <div class="col-lg-3 col-md-6 align-self-center mb-30 trending-items">
            <div class="item">
              <div class="thumb">
                <img src="<?= htmlspecialchars(payoutImage($card['image_path'], $imageSwap), ENT_QUOTES, 'UTF-8') ?>" alt="<?= htmlspecialchars($card['title'], ENT_QUOTES, 'UTF-8') ?>">
                <?php if (!empty($card['badge'])): ?>
                  <span class="price"><?= htmlspecialchars($card['badge'], ENT_QUOTES, 'UTF-8') ?></span>
                <?php endif; ?>
              </div>
              <div class="down-content">
                <span class="category"><?= htmlspecialchars($card['category'], ENT_QUOTES, 'UTF-8') ?></span>
                <h4><?= htmlspecialchars($card['title'], ENT_QUOTES, 'UTF-8') ?></h4>
                <p class="mb-0"><?= htmlspecialchars($card['description'], ENT_QUOTES, 'UTF-8') ?></p>
              </div>
            </div>
          </div>
        <?php endforeach; ?>
      </div>
    </div>
  </div>

  <div class="section">
    <div class="container">
      <div class="row align-items-center mb-4">
        <div class="col-lg-8">
          <div class="section-heading">
            <h6>Fast Payout Visuals</h6>
            <h2>Inside the workflows that keep cashouts quick</h2>
          </div>
        </div>
        <div class="col-lg-4 text-lg-end">
          <p class="mb-0 text-muted">Verified process snapshots and banking touchpoints.</p>
        </div>
      </div>
      <div class="row g-4">
        <div class="col-lg-3 col-md-4 col-sm-6">
          <div class="item">
            <div class="thumb">
              <img src="assets/images/fast-payout/processing-metrics.png" alt="Payout processing metrics dashboard">
            </div>
          </div>
        </div>
        <div class="col-lg-3 col-md-4 col-sm-6">
          <div class="item">
            <div class="thumb">
              <img src="assets/images/fast-payout/crypto-cashouts.png" alt="Crypto cashouts tracking visuals">
            </div>
          </div>
        </div>
        <div class="col-lg-3 col-md-4 col-sm-6">
          <div class="item">
            <div class="thumb">
              <img src="assets/images/fast-payout/e-wallets.png" alt="E-wallet speed performance tiles">
            </div>
          </div>
        </div>
        <div class="col-lg-3 col-md-4 col-sm-6">
          <div class="item">
            <div class="thumb">
              <img src="assets/images/fast-payout/bank-wires.png" alt="Bank wire payout schedule chart">
            </div>
          </div>
        </div>
        <div class="col-lg-3 col-md-4 col-sm-6">
          <div class="item">
            <div class="thumb">
              <img src="assets/images/fast-payout/KYC-refresh-rules.png" alt="KYC refresh rules checklist">
            </div>
          </div>
        </div>
        <div class="col-lg-3 col-md-4 col-sm-6">
          <div class="item">
            <div class="thumb">
              <img src="assets/images/fast-payout/VIP-queueing.png" alt="VIP queueing priority overview">
            </div>
          </div>
        </div>
        <div class="col-lg-3 col-md-4 col-sm-6">
          <div class="item">
            <div class="thumb">
              <img src="assets/images/fast-payout/caps-escalations.png" alt="Cashout caps and escalation guide">
            </div>
          </div>
        </div>
        <div class="col-lg-3 col-md-4 col-sm-6">
          <div class="item">
            <div class="thumb">
              <img src="assets/images/fast-payout/escalation-paths.png" alt="Escalation paths for payout issues">
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>

  <div class="section most-played">
    <div class="container">
      <div class="row">
        <div class="col-lg-6">
          <div class="section-heading">
            <h6>Banking Checklist</h6>
            <h2>What we verify before recommending a site</h2>
          </div>
        </div>
        <div class="col-lg-6">
          <div class="main-button">
            <a href="all-casinos.php">See all verified casinos</a>
          </div>
        </div>
        <?php foreach ($payoutChecklist as $card): ?>
          <div class="col-lg-3 col-md-6 col-sm-6">
            <div class="item">
              <div class="thumb">
                <img src="<?= htmlspecialchars(payoutImage($card['image_path'], $imageSwap), ENT_QUOTES, 'UTF-8') ?>" alt="<?= htmlspecialchars($card['title'], ENT_QUOTES, 'UTF-8') ?>">
              </div>
              <div class="down-content">
                  <span class="category"><?= htmlspecialchars($card['category'], ENT_QUOTES, 'UTF-8') ?></span>
                  <h4><?= htmlspecialchars($card['title'], ENT_QUOTES, 'UTF-8') ?></h4>
                  <p class="mb-0"><?= htmlspecialchars($card['description'], ENT_QUOTES, 'UTF-8') ?></p>
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
