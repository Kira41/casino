<?php

declare(strict_types=1);

require_once __DIR__ . '/includes/bootstrap.php';

$database = getDatabase();
$activePage = 'vip';
$casinoDirectory = fetchCasinoDirectory($database);
$vipPlaybooks = fetchContentCards($database, 'vip_playbooks');
$vipSignals = fetchContentCards($database, 'vip_signals');
$pageTitle = 'Lugx Gaming - VIP & Loyalty Insights';

function dedupeContentCards(array $cards): array
{
    $seen = [];
    $unique = [];

    foreach ($cards as $card) {
        $keyParts = [
            strtolower(trim((string) ($card['title'] ?? ''))),
            strtolower(trim((string) ($card['category'] ?? ''))),
            strtolower(trim((string) ($card['badge'] ?? ''))),
            strtolower(trim((string) ($card['description'] ?? ''))),
            strtolower(trim((string) ($card['image_path'] ?? ''))),
        ];
        $key = implode('|', $keyParts);

        if (isset($seen[$key])) {
            continue;
        }

        $seen[$key] = true;
        $unique[] = $card;
    }

    return $unique;
}

$vipPlaybooks = dedupeContentCards($vipPlaybooks);
$vipSignals = dedupeContentCards($vipSignals);

if (empty($vipPlaybooks)) {
    $vipPlaybooks = [
        [
            'image_path' => 'assets/images/vip-loyalty-insights/accelerate-early-progress.png',
            'badge' => 'Tier 1',
            'category' => 'Onboarding',
            'title' => 'First 30 Days Blueprint',
            'description' => 'Hit early wagering goals, unlock support access, and track weekly lift metrics.',
        ],
        [
            'image_path' => 'assets/images/vip-loyalty-insights/unlock-priority-support.png',
            'badge' => 'Tier 2',
            'category' => 'Momentum',
            'title' => 'Seasonal Bonus Radar',
            'description' => 'Plan around promo calendars, leaderboard surges, and reload rhythm windows.',
        ],
        [
            'image_path' => 'assets/images/vip-loyalty-insights/custom-rewards.png',
            'badge' => 'Tier 3',
            'category' => 'Retention',
            'title' => 'Concierge Access Map',
            'description' => 'Trigger VIP outreach with session cadence, game mix, and deposit timing.',
        ],
        [
            'image_path' => 'assets/images/vip-loyalty-insights/travel-hospitality.png',
            'badge' => 'Tier 4',
            'category' => 'Elite',
            'title' => 'High-Roller Safeguards',
            'description' => 'Balance volatility targets with cash-out plans and reward multipliers.',
        ],
    ];
}

if (empty($vipSignals)) {
    $vipSignals = [
        [
            'image_path' => 'assets/images/vip-loyalty-insights/point-mechanics.png',
            'category' => 'Perks',
            'title' => 'Cashback Velocity',
            'description' => 'Measure how quickly you can redeem cashback after peak play sessions.',
        ],
        [
            'image_path' => 'assets/images/vip-loyalty-insights/what-you-can-claim.png',
            'category' => 'Support',
            'title' => 'Concierge Response Time',
            'description' => 'Look for under-30 minute replies when hosts manage withdrawals.',
        ],
        [
            'image_path' => 'assets/images/vip-loyalty-insights/keep-your-status.png',
            'category' => 'Events',
            'title' => 'Invite-Only Calendar',
            'description' => 'Track live tournaments, travel offers, and seasonal VIP stacks.',
        ],
        [
            'image_path' => 'assets/images/vip-loyalty-insights/events-hospitality.png',
            'category' => 'Limits',
            'title' => 'Withdrawal Priority',
            'description' => 'Confirm fast-track lanes and personalized payout thresholds.',
        ],
    ];
}

include __DIR__ . '/partials/html-head.php';
include __DIR__ . '/partials/header.php';
?>

  <div class="page-heading header-text">
    <div class="container">
      <div class="row">
        <div class="col-lg-12">
          <h3>VIP & Loyalty Insights</h3>
          <span class="breadcrumb"><a href="#">Home</a> > VIP & Loyalty Insights</span>
        </div>
      </div>
    </div>
  </div>

  <div class="section trending">
    <div class="container">
      <div class="row align-items-center mb-4">
        <div class="col-lg-8">
          <div class="section-heading">
            <h6>Tier Playbooks</h6>
            <h2>Map your path to premium perks</h2>
          </div>
        </div>
        <div class="col-lg-4 text-lg-end">
          <div class="main-button">
            <a href="#subscribe-now" data-open-subscribe>Unlock weekly VIP notes</a>
          </div>
        </div>
      </div>
      <div class="row trending-box">
        <?php foreach ($vipPlaybooks as $card): ?>
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
            <h6>Loyalty Signals</h6>
            <h2>Evaluate perks before you commit</h2>
          </div>
        </div>
        <div class="col-lg-6">
          <div class="main-button">
            <a href="all-casinos.php">Compare VIP programs</a>
          </div>
        </div>
        <?php foreach ($vipSignals as $card): ?>
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
