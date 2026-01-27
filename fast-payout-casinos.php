<?php

declare(strict_types=1);

require_once __DIR__ . '/includes/bootstrap.php';

$database = getDatabase();
$activePage = 'payouts';
$casinoDirectory = fetchCasinoDirectory($database);
$payoutHighlights = fetchContentCards($database, 'fast_payout_highlights');
$payoutChecklist = fetchContentCards($database, 'fast_payout_checklist');
$payoutSpeedInsights = [
    [
        'eyebrow' => 'Instant blockchain approvals',
        'title' => 'Crypto cashouts',
        'description' => 'BTC, ETH, and USDT withdrawals are prioritized with automated checks and blockchain monitoring.',
        'image' => 'assets/images/fast-payout/crypto-cashouts.png',
    ],
    [
        'eyebrow' => 'Skip the bank queue',
        'title' => 'E-wallets',
        'description' => 'Neteller, Skrill, and PayPal partners with 24/7 AML desks to release funds on the day you request.',
        'image' => 'assets/images/fast-payout/e-wallets.png',
    ],
    [
        'eyebrow' => 'Local payouts',
        'title' => 'Bank wires',
        'description' => 'Low-fee SEPA and ACH corridors keep funds domestic and reduce costly intermediary holds.',
        'image' => 'assets/images/fast-payout/bank-wires.png',
    ],
    [
        'eyebrow' => 'Faster approvals',
        'title' => 'VIP queueing',
        'description' => 'Dedicated agents review large withdrawals with proactive KYC refreshes to keep lines moving.',
        'image' => 'assets/images/fast-payout/VIP-queueing.png',
    ],
    [
        'eyebrow' => 'Processing metrics',
        'title' => 'Proof',
        'description' => 'Average approval times for each payment method and the hours when compliance teams are active.',
        'image' => 'assets/images/fast-payout/processing-metrics.png',
    ],
    [
        'eyebrow' => 'Caps & escalations',
        'title' => 'Limits',
        'description' => 'Per-transaction limits, weekly ceilings, and when VIP managers can double or triple your cap.',
        'image' => 'assets/images/fast-payout/caps-escalations.png',
    ],
    [
        'eyebrow' => 'KYC refresh rules',
        'title' => 'Verification',
        'description' => 'Document requests, source-of-funds standards, and cooldown periods after large cashouts.',
        'image' => 'assets/images/fast-payout/KYC-refresh-rules.png',
    ],
    [
        'eyebrow' => 'Escalation paths',
        'title' => 'Support',
        'description' => 'Live chat and phone SLAs plus finance-team contacts when you need real-time updates.',
        'image' => 'assets/images/fast-payout/escalation-paths.png',
    ],
];
$pageTitle = 'Lugx Gaming - Fast Payout Casinos';

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
                <a href="product-details.php"><img src="<?= htmlspecialchars($card['image_path'], ENT_QUOTES, 'UTF-8') ?>" alt="<?= htmlspecialchars($card['title'], ENT_QUOTES, 'UTF-8') ?>"></a>
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
                <a href="product-details.php"><img src="<?= htmlspecialchars($card['image_path'], ENT_QUOTES, 'UTF-8') ?>" alt="<?= htmlspecialchars($card['title'], ENT_QUOTES, 'UTF-8') ?>"></a>
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
      <div class="row mb-4 align-items-end">
        <div class="col-lg-8">
          <div class="section-heading">
            <h6>Fast Payout Intel</h6>
            <h2>Know exactly where the speed advantage comes from</h2>
          </div>
        </div>
      </div>
      <div class="row">
        <?php foreach ($payoutSpeedInsights as $insight): ?>
          <div class="col-lg-3 col-md-6 mb-4">
            <div class="item h-100">
              <div class="thumb">
                <img src="<?= htmlspecialchars($insight['image'], ENT_QUOTES, 'UTF-8') ?>" alt="<?= htmlspecialchars($insight['title'], ENT_QUOTES, 'UTF-8') ?>">
              </div>
              <div class="down-content">
                <span class="category"><?= htmlspecialchars($insight['eyebrow'], ENT_QUOTES, 'UTF-8') ?></span>
                <h4><?= htmlspecialchars($insight['title'], ENT_QUOTES, 'UTF-8') ?></h4>
                <p class="mb-0"><?= htmlspecialchars($insight['description'], ENT_QUOTES, 'UTF-8') ?></p>
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
