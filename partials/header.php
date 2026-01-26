<?php
if (!isset($activePage)) {
    $activePage = '';
}

$navItems = [
    ['label' => 'Home', 'href' => 'index.php', 'key' => 'home'],
    ['label' => 'All Casinos', 'href' => 'all-casinos.php', 'key' => 'all'],
    ['label' => 'Contact Us', 'href' => 'contact.php', 'key' => 'contact'],
    ['label' => 'VIP & Loyalty Insights', 'href' => 'vip-loyalty-insights.php', 'key' => 'vip'],
];
?>
  <!-- ***** Header Area Start ***** -->
  <header class="header-area header-sticky">
    <div class="container">
        <div class="row">
            <div class="col-12">
                <nav class="main-nav">
                    <!-- ***** Logo Start ***** -->
                    <a href="index.php" class="logo">
                        <img src="assets/images/logo.png" alt="" style="width: 158px;">
                    </a>
                    <!-- ***** Logo End ***** -->
                    <!-- ***** Menu Start ***** -->
                    <ul class="nav">
                      <?php foreach ($navItems as $item): ?>
                        <li><a href="<?= htmlspecialchars($item['href'], ENT_QUOTES, 'UTF-8') ?>" class="<?= $activePage === $item['key'] ? 'active' : '' ?>"><?= htmlspecialchars($item['label'], ENT_QUOTES, 'UTF-8') ?></a></li>
                      <?php endforeach; ?>
                      <li><a href="#subscribe-now" data-open-subscribe>Subscribe Now</a></li>
                      <li><a href="#" data-bs-toggle="modal" data-bs-target="#signInModal">Sign In</a></li>
                  </ul>
                    <a class='menu-trigger'>
                        <span>Menu</span>
                    </a>
                    <!-- ***** Menu End ***** -->
                </nav>
            </div>
        </div>
    </div>
  </header>
  <!-- ***** Header Area End ***** -->
