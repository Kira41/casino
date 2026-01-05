
START TRANSACTION;

-- Core tables used by the PHP API
CREATE TABLE IF NOT EXISTS subscriptions (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    email VARCHAR(255) NOT NULL UNIQUE,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS signins (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    email VARCHAR(255) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    last_login_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS contact_messages (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    surname VARCHAR(255) NOT NULL,
    email VARCHAR(255) NOT NULL,
    subject VARCHAR(255),
    message TEXT,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS promotions (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  title VARCHAR(255) NOT NULL,
  subtitle VARCHAR(255) NULL,
  language VARCHAR(16) NULL,
  offer_percentage TINYINT UNSIGNED NULL,
  image_base64 LONGTEXT NOT NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Casino content tables
CREATE TABLE IF NOT EXISTS casinos (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    slug VARCHAR(255) NOT NULL UNIQUE,
    name VARCHAR(255) NOT NULL,
    operator VARCHAR(255),
    license VARCHAR(255),
    headline_bonus VARCHAR(255),
    min_deposit_usd INT,
    hero_image TEXT,
    thumbnail_image TEXT,
    rating TINYINT UNSIGNED NOT NULL DEFAULT 0 CHECK (rating BETWEEN 0 AND 5),
    short_description TEXT,
    cta_url TEXT,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS casino_tags (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    type VARCHAR(50) NOT NULL DEFAULT 'tag',
    UNIQUE(name, type)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS casino_tag_links (
    casino_id BIGINT UNSIGNED NOT NULL,
    tag_id BIGINT UNSIGNED NOT NULL,
    is_primary TINYINT(1) NOT NULL DEFAULT 0 CHECK (is_primary IN (0, 1)),
    PRIMARY KEY (casino_id, tag_id),
    FOREIGN KEY (casino_id) REFERENCES casinos(id) ON DELETE CASCADE,
    FOREIGN KEY (tag_id) REFERENCES casino_tags(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS casino_cards (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    casino_id BIGINT UNSIGNED NOT NULL,
    section VARCHAR(100) NOT NULL,
    title VARCHAR(255) NOT NULL,
    image_path TEXT,
    min_deposit_label VARCHAR(255),
    rating TINYINT UNSIGNED CHECK (rating BETWEEN 0 AND 5),
    price_label VARCHAR(255),
    position INT NOT NULL DEFAULT 1,
    FOREIGN KEY (casino_id) REFERENCES casinos(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE INDEX IF NOT EXISTS idx_casino_cards_section_position ON casino_cards(section, position);

CREATE TABLE IF NOT EXISTS content_cards (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    section VARCHAR(100) NOT NULL,
    title VARCHAR(255) NOT NULL,
    category VARCHAR(255),
    badge VARCHAR(255),
    description TEXT,
    image_path TEXT NOT NULL,
    position INT NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS category_cards (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    image_path TEXT NOT NULL,
    section VARCHAR(100) NOT NULL DEFAULT 'guide_category'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS casino_game_modes (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    casino_id BIGINT UNSIGNED NOT NULL,
    game_type VARCHAR(100) NOT NULL,
    live_dealer_supported TINYINT(1) NOT NULL DEFAULT 0 CHECK (live_dealer_supported IN (0, 1)),
    virtual_reality_supported TINYINT(1) NOT NULL DEFAULT 0 CHECK (virtual_reality_supported IN (0, 1)),
    FOREIGN KEY (casino_id) REFERENCES casinos(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS casino_review_sections (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    casino_id BIGINT UNSIGNED NOT NULL,
    title VARCHAR(255) NOT NULL,
    summary TEXT,
    FOREIGN KEY (casino_id) REFERENCES casinos(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS casino_review_points (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    review_section_id BIGINT UNSIGNED NOT NULL,
    icon VARCHAR(255),
    content TEXT NOT NULL,
    FOREIGN KEY (review_section_id) REFERENCES casino_review_sections(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS casino_pros_cons (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    casino_id BIGINT UNSIGNED NOT NULL,
    type VARCHAR(10) NOT NULL CHECK (type IN ('pro', 'con')),
    content TEXT NOT NULL,
    FOREIGN KEY (casino_id) REFERENCES casinos(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS casino_highlights (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    casino_id BIGINT UNSIGNED NOT NULL,
    label VARCHAR(255) NOT NULL,
    icon VARCHAR(255),
    FOREIGN KEY (casino_id) REFERENCES casinos(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Seed reference data from the existing HTML content
INSERT IGNORE INTO casinos (slug, name, operator, license, headline_bonus, min_deposit_usd, hero_image, thumbnail_image, rating, short_description, cta_url)
VALUES
    (
        'lucky-star-crypto-casino',
        'Lucky Star Crypto Casino',
        'Lucky Star Entertainment Group',
        'Curacao eGaming Authority',
        '$1,500 Welcome Bonus',
        NULL,
        'assets/images/single-game.jpg',
        'assets/images/trending-01.jpg',
        5,
        'Crypto-first live dealer casino with blockchain payouts, curated table games, and provably fair slots.',
        'https://luckystar.example.com'
    ),
    (
        'nova-royale-casino',
        'Nova Royale Casino',
        NULL,
        NULL,
        NULL,
        20,
        'assets/images/trending-01.jpg',
        'assets/images/trending-01.jpg',
        5,
        'Flagship pick with polished lobbies, player-first promos, and a $20 minimum deposit.',
        'https://novaroyale.example.com'
    ),
    (
        'starlight-spins-resort',
        'Starlight Spins Resort',
        NULL,
        NULL,
        NULL,
        44,
        'assets/images/trending-02.jpg',
        'assets/images/trending-02.jpg',
        4,
        'Resort-themed casino featuring quick sign-ins, curated bonuses, and mid-tier deposits.',
        'https://starlightspins.example.com'
    ),
    (
        'emerald-mirage-club',
        'Emerald Mirage Club',
        NULL,
        NULL,
        NULL,
        44,
        'assets/images/trending-03.jpg',
        'assets/images/trending-03.jpg',
        3,
        'Club-inspired pick blending classic tables with approachable wagering for new members.',
        'https://emeraldmirage.example.com'
    ),
    (
        'celestial-fortune-hall',
        'Celestial Fortune Hall',
        NULL,
        NULL,
        NULL,
        32,
        'assets/images/trending-04.jpg',
        'assets/images/trending-04.jpg',
        5,
        'High-rated hall with stellar promos, $32 minimum deposits, and VIP-ready support.',
        'https://celestialfortune.example.com'
    ),
    (
        'aurora-vault-casino',
        'Aurora Vault Casino',
        NULL,
        NULL,
        NULL,
        NULL,
        'assets/images/top-game-01.jpg',
        'assets/images/top-game-01.jpg',
        5,
        'Top casino draw with premium tables and a perfect score from our reviewers.',
        'https://auroravault.example.com'
    ),
    (
        'quantum-spin-lounge',
        'Quantum Spin Lounge',
        NULL,
        NULL,
        NULL,
        NULL,
        'assets/images/top-game-02.jpg',
        'assets/images/top-game-02.jpg',
        4,
        'Lounge experience with quick spins, dependable payouts, and fast onboarding.',
        'https://quantumspin.example.com'
    ),
    (
        'imperial-halo-casino',
        'Imperial Halo Casino',
        NULL,
        NULL,
        NULL,
        NULL,
        'assets/images/top-game-03.jpg',
        'assets/images/top-game-03.jpg',
        3,
        'Reliable operator that focuses on core table games and classic slots.',
        'https://imperialhalo.example.com'
    ),
    (
        'obsidian-crown-club',
        'Obsidian Crown Club',
        NULL,
        NULL,
        NULL,
        NULL,
        'assets/images/top-game-04.jpg',
        'assets/images/top-game-04.jpg',
        5,
        'VIP-focused destination featuring high-roller tables and concierge-style support.',
        'https://obsidiancrown.example.com'
    ),
    (
        'mirage-of-millions',
        'Mirage of Millions',
        NULL,
        NULL,
        NULL,
        NULL,
        'assets/images/top-game-05.jpg',
        'assets/images/top-game-05.jpg',
        2,
        'Approachable option for casual players with a lighter rating from reviewers.',
        'https://mirageofmillions.example.com'
    ),
    (
        'luminous-ledger-casino',
        'Luminous Ledger Casino',
        NULL,
        NULL,
        NULL,
        NULL,
        'assets/images/top-game-06.jpg',
        'assets/images/top-game-06.jpg',
        4,
        'Data-forward casino celebrated for transparency and strong payout history.',
        'https://luminousledger.example.com'
    ),
    (
        'neon-mirage-casino',
        'Neon Mirage Casino',
        NULL,
        NULL,
        NULL,
        NULL,
        'assets/images/categories-01.jpg',
        'assets/images/categories-01.jpg',
        0,
        'Bright, modern casino featured as a related destination in the review page.',
        'https://neonmirage.example.com'
    ),
    (
        'azure-spire-casino',
        'Azure Spire Casino',
        NULL,
        NULL,
        NULL,
        NULL,
        'assets/images/categories-05.jpg',
        'assets/images/categories-05.jpg',
        0,
        'Coastal-inspired casino listed alongside other related recommendations.',
        'https://azurespire.example.com'
    ),
    (
        'lucky-horizon-lounge',
        'Lucky Horizon Lounge',
        NULL,
        NULL,
        NULL,
        NULL,
        'assets/images/categories-03.jpg',
        'assets/images/categories-03.jpg',
        0,
        'Lounge experience for players exploring additional curated casinos.',
        'https://luckyhorizon.example.com'
    ),
    (
        'starlit-crown-casino',
        'Starlit Crown Casino',
        NULL,
        NULL,
        NULL,
        NULL,
        'assets/images/categories-04.jpg',
        'assets/images/categories-04.jpg',
        0,
        'Boutique casino highlighted as a related option for readers.',
        'https://starlitcrown.example.com'
    ),
    (
        'golden-drift-resort',
        'Golden Drift Resort',
        NULL,
        NULL,
        NULL,
        NULL,
        'assets/images/categories-05.jpg',
        'assets/images/categories-05.jpg',
        0,
        'Resort-style casino rounding out the related destinations list.',
        'https://goldendrift.example.com'
    );

INSERT IGNORE INTO casino_tags (name, type) VALUES
    ('Action', 'category'),
    ('Adventure', 'category'),
    ('Strategy', 'category'),
    ('Racing', 'category'),
    ('Crypto Casinos', 'category'),
    ('Fast Payouts', 'category'),
    ('Low Deposit', 'category'),
    ('High Roller', 'category'),
    ('Live Dealer', 'category'),
    ('Mobile Friendly', 'category'),
    ('Live Casino', 'genre'),
    ('Crypto', 'genre'),
    ('Mobile', 'genre'),
    ('Free Spins', 'perk'),
    ('Welcome Bonus', 'perk'),
    ('VIP Club', 'perk');

INSERT IGNORE INTO casino_tag_links (casino_id, tag_id, is_primary)
VALUES
    ((SELECT id FROM casinos WHERE slug = 'lucky-star-crypto-casino'), (SELECT id FROM casino_tags WHERE name = 'Live Casino'), 1),
    ((SELECT id FROM casinos WHERE slug = 'lucky-star-crypto-casino'), (SELECT id FROM casino_tags WHERE name = 'Crypto'), 1),
    ((SELECT id FROM casinos WHERE slug = 'lucky-star-crypto-casino'), (SELECT id FROM casino_tags WHERE name = 'Mobile'), 0),
    ((SELECT id FROM casinos WHERE slug = 'lucky-star-crypto-casino'), (SELECT id FROM casino_tags WHERE name = 'Free Spins'), 0),
    ((SELECT id FROM casinos WHERE slug = 'lucky-star-crypto-casino'), (SELECT id FROM casino_tags WHERE name = 'Welcome Bonus'), 0),
    ((SELECT id FROM casinos WHERE slug = 'lucky-star-crypto-casino'), (SELECT id FROM casino_tags WHERE name = 'VIP Club'), 0);

INSERT IGNORE INTO casino_tag_links (casino_id, tag_id, is_primary)
VALUES
    ((SELECT id FROM casinos WHERE slug = 'lucky-star-crypto-casino'), (SELECT id FROM casino_tags WHERE name = 'Crypto Casinos' AND type = 'category'), 1),
    ((SELECT id FROM casinos WHERE slug = 'lucky-star-crypto-casino'), (SELECT id FROM casino_tags WHERE name = 'Live Dealer' AND type = 'category'), 0),
    ((SELECT id FROM casinos WHERE slug = 'lucky-star-crypto-casino'), (SELECT id FROM casino_tags WHERE name = 'Fast Payouts' AND type = 'category'), 0),
    ((SELECT id FROM casinos WHERE slug = 'lucky-star-crypto-casino'), (SELECT id FROM casino_tags WHERE name = 'Mobile Friendly' AND type = 'category'), 0),
    ((SELECT id FROM casinos WHERE slug = 'nova-royale-casino'), (SELECT id FROM casino_tags WHERE name = 'Low Deposit' AND type = 'category'), 1),
    ((SELECT id FROM casinos WHERE slug = 'nova-royale-casino'), (SELECT id FROM casino_tags WHERE name = 'Fast Payouts' AND type = 'category'), 0),
    ((SELECT id FROM casinos WHERE slug = 'nova-royale-casino'), (SELECT id FROM casino_tags WHERE name = 'Mobile Friendly' AND type = 'category'), 0),
    ((SELECT id FROM casinos WHERE slug = 'starlight-spins-resort'), (SELECT id FROM casino_tags WHERE name = 'Live Dealer' AND type = 'category'), 1),
    ((SELECT id FROM casinos WHERE slug = 'starlight-spins-resort'), (SELECT id FROM casino_tags WHERE name = 'High Roller' AND type = 'category'), 0),
    ((SELECT id FROM casinos WHERE slug = 'emerald-mirage-club'), (SELECT id FROM casino_tags WHERE name = 'High Roller' AND type = 'category'), 1),
    ((SELECT id FROM casinos WHERE slug = 'emerald-mirage-club'), (SELECT id FROM casino_tags WHERE name = 'Fast Payouts' AND type = 'category'), 0),
    ((SELECT id FROM casinos WHERE slug = 'celestial-fortune-hall'), (SELECT id FROM casino_tags WHERE name = 'High Roller' AND type = 'category'), 1),
    ((SELECT id FROM casinos WHERE slug = 'celestial-fortune-hall'), (SELECT id FROM casino_tags WHERE name = 'Live Dealer' AND type = 'category'), 0),
    ((SELECT id FROM casinos WHERE slug = 'aurora-vault-casino'), (SELECT id FROM casino_tags WHERE name = 'Fast Payouts' AND type = 'category'), 1),
    ((SELECT id FROM casinos WHERE slug = 'aurora-vault-casino'), (SELECT id FROM casino_tags WHERE name = 'Mobile Friendly' AND type = 'category'), 0),
    ((SELECT id FROM casinos WHERE slug = 'quantum-spin-lounge'), (SELECT id FROM casino_tags WHERE name = 'Mobile Friendly' AND type = 'category'), 1),
    ((SELECT id FROM casinos WHERE slug = 'quantum-spin-lounge'), (SELECT id FROM casino_tags WHERE name = 'Fast Payouts' AND type = 'category'), 0),
    ((SELECT id FROM casinos WHERE slug = 'imperial-halo-casino'), (SELECT id FROM casino_tags WHERE name = 'High Roller' AND type = 'category'), 1),
    ((SELECT id FROM casinos WHERE slug = 'imperial-halo-casino'), (SELECT id FROM casino_tags WHERE name = 'Live Dealer' AND type = 'category'), 0),
    ((SELECT id FROM casinos WHERE slug = 'obsidian-crown-club'), (SELECT id FROM casino_tags WHERE name = 'Crypto Casinos' AND type = 'category'), 1),
    ((SELECT id FROM casinos WHERE slug = 'obsidian-crown-club'), (SELECT id FROM casino_tags WHERE name = 'High Roller' AND type = 'category'), 0),
    ((SELECT id FROM casinos WHERE slug = 'mirage-of-millions'), (SELECT id FROM casino_tags WHERE name = 'High Roller' AND type = 'category'), 1),
    ((SELECT id FROM casinos WHERE slug = 'mirage-of-millions'), (SELECT id FROM casino_tags WHERE name = 'Live Dealer' AND type = 'category'), 0),
    ((SELECT id FROM casinos WHERE slug = 'luminous-ledger-casino'), (SELECT id FROM casino_tags WHERE name = 'Fast Payouts' AND type = 'category'), 1),
    ((SELECT id FROM casinos WHERE slug = 'luminous-ledger-casino'), (SELECT id FROM casino_tags WHERE name = 'Low Deposit' AND type = 'category'), 0),
    ((SELECT id FROM casinos WHERE slug = 'neon-mirage-casino'), (SELECT id FROM casino_tags WHERE name = 'Crypto Casinos' AND type = 'category'), 1),
    ((SELECT id FROM casinos WHERE slug = 'neon-mirage-casino'), (SELECT id FROM casino_tags WHERE name = 'Live Dealer' AND type = 'category'), 0),
    ((SELECT id FROM casinos WHERE slug = 'azure-spire-casino'), (SELECT id FROM casino_tags WHERE name = 'Live Dealer' AND type = 'category'), 1),
    ((SELECT id FROM casinos WHERE slug = 'azure-spire-casino'), (SELECT id FROM casino_tags WHERE name = 'High Roller' AND type = 'category'), 0),
    ((SELECT id FROM casinos WHERE slug = 'lucky-horizon-lounge'), (SELECT id FROM casino_tags WHERE name = 'Low Deposit' AND type = 'category'), 1),
    ((SELECT id FROM casinos WHERE slug = 'lucky-horizon-lounge'), (SELECT id FROM casino_tags WHERE name = 'Mobile Friendly' AND type = 'category'), 0),
    ((SELECT id FROM casinos WHERE slug = 'starlit-crown-casino'), (SELECT id FROM casino_tags WHERE name = 'High Roller' AND type = 'category'), 1),
    ((SELECT id FROM casinos WHERE slug = 'starlit-crown-casino'), (SELECT id FROM casino_tags WHERE name = 'Fast Payouts' AND type = 'category'), 0),
    ((SELECT id FROM casinos WHERE slug = 'golden-drift-resort'), (SELECT id FROM casino_tags WHERE name = 'Live Dealer' AND type = 'category'), 1),
    ((SELECT id FROM casinos WHERE slug = 'golden-drift-resort'), (SELECT id FROM casino_tags WHERE name = 'Low Deposit' AND type = 'category'), 0);

INSERT IGNORE INTO casino_cards (casino_id, section, title, image_path, min_deposit_label, rating, price_label, position)
VALUES
    ((SELECT id FROM casinos WHERE slug = 'nova-royale-casino'), 'hot_picks', 'Nova Royale Casino', 'assets/images/trending-01.jpg', 'minimum deposit $20', 5, NULL, 1),
    ((SELECT id FROM casinos WHERE slug = 'starlight-spins-resort'), 'hot_picks', 'Starlight Spins Resort', 'assets/images/trending-02.jpg', 'minimum deposit $44', 4, NULL, 2),
    ((SELECT id FROM casinos WHERE slug = 'emerald-mirage-club'), 'hot_picks', 'Emerald Mirage Club', 'assets/images/trending-03.jpg', 'minimum deposit $44', 3, NULL, 3),
    ((SELECT id FROM casinos WHERE slug = 'celestial-fortune-hall'), 'hot_picks', 'Celestial Fortune Hall', 'assets/images/trending-04.jpg', 'minimum deposit $32', 5, NULL, 4),
    ((SELECT id FROM casinos WHERE slug = 'aurora-vault-casino'), 'most_played', 'Aurora Vault Casino', 'assets/images/top-game-01.jpg', NULL, 5, NULL, 1),
    ((SELECT id FROM casinos WHERE slug = 'quantum-spin-lounge'), 'most_played', 'Quantum Spin Lounge', 'assets/images/top-game-02.jpg', NULL, 4, NULL, 2),
    ((SELECT id FROM casinos WHERE slug = 'imperial-halo-casino'), 'most_played', 'Imperial Halo Casino', 'assets/images/top-game-03.jpg', NULL, 3, NULL, 3),
    ((SELECT id FROM casinos WHERE slug = 'obsidian-crown-club'), 'most_played', 'Obsidian Crown Club', 'assets/images/top-game-04.jpg', NULL, 5, NULL, 4),
    ((SELECT id FROM casinos WHERE slug = 'mirage-of-millions'), 'most_played', 'Mirage of Millions', 'assets/images/top-game-05.jpg', NULL, 2, NULL, 5),
    ((SELECT id FROM casinos WHERE slug = 'luminous-ledger-casino'), 'most_played', 'Luminous Ledger Casino', 'assets/images/top-game-06.jpg', NULL, 4, NULL, 6),
    ((SELECT id FROM casinos WHERE slug = 'neon-mirage-casino'), 'related', 'Neon Mirage Casino', 'assets/images/categories-01.jpg', NULL, NULL, NULL, 1),
    ((SELECT id FROM casinos WHERE slug = 'azure-spire-casino'), 'related', 'Azure Spire Casino', 'assets/images/categories-05.jpg', NULL, NULL, NULL, 2),
    ((SELECT id FROM casinos WHERE slug = 'lucky-horizon-lounge'), 'related', 'Lucky Horizon Lounge', 'assets/images/categories-03.jpg', NULL, NULL, NULL, 3),
    ((SELECT id FROM casinos WHERE slug = 'starlit-crown-casino'), 'related', 'Starlit Crown Casino', 'assets/images/categories-04.jpg', NULL, NULL, NULL, 4),
    ((SELECT id FROM casinos WHERE slug = 'golden-drift-resort'), 'related', 'Golden Drift Resort', 'assets/images/categories-05.jpg', NULL, NULL, NULL, 5);

INSERT IGNORE INTO content_cards (section, title, category, badge, description, image_path, position)
VALUES
    ('fast_payout_highlights', 'Instant blockchain approvals', 'Crypto cashouts', 'Under 2 hours', 'BTC, ETH, and USDT withdrawals are prioritized with automated checks and blockchain monitoring.', 'assets/images/trending-01.jpg', 1),
    ('fast_payout_highlights', 'Skip the bank queue', 'E-wallets', 'Same-day', 'Neteller, Skrill, and PayPal partners with 24/7 AML desks to release funds on the day you request.', 'assets/images/trending-02.jpg', 2),
    ('fast_payout_highlights', 'Local payouts', 'Bank wires', 'Next business day', 'Low-fee SEPA and ACH corridors keep funds domestic and reduce costly intermediary holds.', 'assets/images/trending-03.jpg', 3),
    ('fast_payout_highlights', 'Faster approvals', 'VIP queueing', 'Priority desk', 'Dedicated agents review large withdrawals with proactive KYC refreshes to keep lines moving.', 'assets/images/trending-04.jpg', 4),
    ('fast_payout_checklist', 'Processing metrics', 'Proof', NULL, 'Average approval times for each payment method and the hours when compliance teams are active.', 'assets/images/top-game-05.jpg', 1),
    ('fast_payout_checklist', 'Caps & escalations', 'Limits', NULL, 'Per-transaction limits, weekly ceilings, and when VIP managers can double or triple your cap.', 'assets/images/top-game-06.jpg', 2),
    ('fast_payout_checklist', 'KYC refresh rules', 'Verification', NULL, 'Document requests, source-of-funds standards, and cooldown periods after large cashouts.', 'assets/images/top-game-07.jpg', 3),
    ('fast_payout_checklist', 'Escalation paths', 'Support', NULL, 'Live chat and phone SLAs plus finance-team contacts when you need real-time updates.', 'assets/images/top-game-08.jpg', 4),
    ('bonus_guides', 'Maximize first deposits', 'Welcome bundles', 'Step-by-step', 'Stack deposit matches, free spins, and loyalty opt-ins without triggering tough wagering limits.', 'assets/images/trending-01.jpg', 1),
    ('bonus_guides', 'Instant-cash offers', 'No wagering', 'Low-risk', 'Spot no-wager deals, typical withdrawal rules, and the playthrough traps that still appear in the fine print.', 'assets/images/trending-02.jpg', 2),
    ('bonus_guides', 'Pick the right slots', 'Free spins', 'Reader favorite', 'Compare RTP, volatility, and eligible game lists so your spins ladder up to withdrawable balances.', 'assets/images/trending-03.jpg', 3),
    ('bonus_guides', 'Make losses sting less', 'Cashback', 'Sustained value', 'Find casinos with next-day cashback, tier multipliers, and transparent loss calculations.', 'assets/images/trending-04.jpg', 4),
    ('bonus_shortlist', 'Under $20 deposits', 'Budget', NULL, 'Combine small-deposit matches with low wagering to keep the bankroll flexible.', 'assets/images/top-game-01.jpg', 1),
    ('bonus_shortlist', 'Big match playbooks', 'High-roller', NULL, 'Prioritize flexible max bets, higher withdrawal caps, and accelerated VIP status triggers.', 'assets/images/top-game-02.jpg', 2),
    ('bonus_shortlist', 'Token-based promos', 'Crypto', NULL, 'Use faster payouts and coin-specific bonuses to avoid conversion fees and release delays.', 'assets/images/top-game-03.jpg', 3),
    ('bonus_shortlist', 'Bet-slip boosts', 'Sports', NULL, 'Balance bet insurance, odds boosts, and wagering contribution rules for multi-leg slips.', 'assets/images/top-game-04.jpg', 4),
    ('game_library', 'Fresh releases weekly', 'Slots & jackpots', '12K+ titles', 'We flag casinos adding new Megaways, cluster pays, and branded slots as soon as they drop.', 'assets/images/categories-01.jpg', 1),
    ('game_library', 'Studios with low latency', 'Tables & shows', 'Live dealers', 'Lightning roulette, blackjack parties, and game shows streamed with multi-camera coverage.', 'assets/images/categories-05.jpg', 2),
    ('game_library', 'One wallet play', 'Sports & eSports', 'Hybrid', 'Single-balance wagering across sportsbook, racebook, and in-house virtuals with instant settlement.', 'assets/images/categories-03.jpg', 3),
    ('game_library', 'Balanced catalogs', 'Premium studios', 'Provider mix', 'NetEnt visuals, Pragmatic volatility, and Evolution live tables curated for every bankroll.', 'assets/images/categories-04.jpg', 4),
    ('library_signals', 'Smart search', 'Filters', NULL, 'Category filters, volatility tags, and provider shortcuts make it easy to find the right title fast.', 'assets/images/top-game-09.jpg', 1),
    ('library_signals', 'RTP transparency', 'Fairness', NULL, 'Visible RTP, provably fair checks for crypto titles, and clear game contribution toward wagering.', 'assets/images/top-game-10.jpg', 2),
    ('library_signals', 'Mobile-ready', 'Performance', NULL, 'HTML5 catalogs with portrait mode, quick-load lobbies, and low-data settings for traveling players.', 'assets/images/top-game-11.jpg', 3),
    ('library_signals', 'Limited drops', 'Exclusives', NULL, 'Early-access slots, branded live tables, and loyalty-only jackpots we track weekly.', 'assets/images/top-game-12.jpg', 4),
    ('vip_playbooks', 'Accelerate early progress', 'Entry tiers', 'Level 1-3', 'Build comp multipliers with low-stakes play, mission streaks, and targeted reloads.', 'assets/images/trending-01.jpg', 1),
    ('vip_playbooks', 'Unlock priority support', 'Mid tiers', 'Level 4-6', 'Blend table and slot volume to hit thresholds while keeping KYC refreshes painless.', 'assets/images/trending-02.jpg', 2),
    ('vip_playbooks', 'Custom rewards', 'High roller', 'Level 7-9', 'Negotiate bespoke rakeback, higher limits, and concierge service for trips and events.', 'assets/images/trending-03.jpg', 3),
    ('vip_playbooks', 'Travel & hospitality', 'Invite-only', 'Elite desk', 'Dedicated hosts coordinate cashout speeds, table access, and personalized event invites.', 'assets/images/trending-04.jpg', 4),
    ('vip_signals', 'Point mechanics', 'Earning', NULL, 'Earn rates per game type, rollover windows, and how multipliers stack on promos.', 'assets/images/top-game-13.jpg', 1),
    ('vip_signals', 'What you can claim', 'Rewards', NULL, 'Cashback cadence, tournament entries, physical gifts, and real host response times.', 'assets/images/top-game-14.jpg', 2),
    ('vip_signals', 'Keep your status', 'Flexibility', NULL, 'Tier decay timelines, pause options during travel, and soft-landing offers from VIP hosts.', 'assets/images/top-game-15.jpg', 3),
    ('vip_signals', 'Events & hospitality', 'Experience', NULL, 'On-site suites, sports hospitality, and milestone gifts for the moments you want to celebrate.', 'assets/images/top-game-16.jpg', 4);

INSERT IGNORE INTO category_cards (title, image_path, section)
VALUES
    ('Slots & Jackpots', 'assets/images/categories-01.jpg', 'top_categories'),
    ('Live Dealer Tables', 'assets/images/categories-05.jpg', 'top_categories'),
    ('Sports Betting', 'assets/images/categories-03.jpg', 'top_categories'),
    ('VIP Programs', 'assets/images/categories-04.jpg', 'top_categories'),
    ('Crypto Casinos', 'assets/images/categories-05.jpg', 'top_categories');

INSERT IGNORE INTO casino_game_modes (casino_id, game_type, live_dealer_supported, virtual_reality_supported)
VALUES
    ((SELECT id FROM casinos WHERE slug = 'lucky-star-crypto-casino'), 'Roulette', 0, 0),
    ((SELECT id FROM casinos WHERE slug = 'lucky-star-crypto-casino'), 'Slots', 0, 0),
    ((SELECT id FROM casinos WHERE slug = 'lucky-star-crypto-casino'), 'Blackjack', 0, 0);

INSERT IGNORE INTO casino_review_sections (casino_id, title, summary)
VALUES
    ((SELECT id FROM casinos WHERE slug = 'lucky-star-crypto-casino'), 'Banking', 'Instant crypto deposits and on-chain withdrawals keep payouts transparent and quick.'),
    ((SELECT id FROM casinos WHERE slug = 'lucky-star-crypto-casino'), 'Support', 'Always-on support team ready to assist members across languages and time zones.'),
    ((SELECT id FROM casinos WHERE slug = 'lucky-star-crypto-casino'), 'Additional Info', 'Licensed operation that publishes regular payout audits for transparency.'),
    ((SELECT id FROM casinos WHERE slug = 'lucky-star-crypto-casino'), 'Available Game Types', 'Live-dealer tables, provably fair crash games, and gem-forward slot experiences.');

INSERT IGNORE INTO casino_review_points (review_section_id, icon, content)
VALUES
    ((SELECT id FROM casino_review_sections WHERE title = 'Banking' AND casino_id = (SELECT id FROM casinos WHERE slug = 'lucky-star-crypto-casino')), 'fa-university text-primary', 'Deposits clear instantly through popular crypto wallets to keep players moving.'),
    ((SELECT id FROM casino_review_sections WHERE title = 'Banking' AND casino_id = (SELECT id FROM casinos WHERE slug = 'lucky-star-crypto-casino')), 'fa-exchange-alt text-success', 'On-chain cashouts are verified quickly for transparent, near-instant payouts.'),
    ((SELECT id FROM casino_review_sections WHERE title = 'Support' AND casino_id = (SELECT id FROM casinos WHERE slug = 'lucky-star-crypto-casino')), 'fa-bolt text-warning', 'Lightning-fast responses that mirror the site''s payout speed.'),
    ((SELECT id FROM casino_review_sections WHERE title = 'Support' AND casino_id = (SELECT id FROM casinos WHERE slug = 'lucky-star-crypto-casino')), 'fa-headset text-info', '24/7 multilingual help desk backing the gaming studio experience.'),
    ((SELECT id FROM casino_review_sections WHERE title = 'Additional Info' AND casino_id = (SELECT id FROM casinos WHERE slug = 'lucky-star-crypto-casino')), 'fa-info-circle text-warning', 'Publishes monthly payout audits confirming the integrity of RNG-powered games.'),
    ((SELECT id FROM casino_review_sections WHERE title = 'Available Game Types' AND casino_id = (SELECT id FROM casinos WHERE slug = 'lucky-star-crypto-casino')), 'fa-chess-knight', 'Live blackjack, roulette, and baccarat streams straight from the studio.'),
    ((SELECT id FROM casino_review_sections WHERE title = 'Available Game Types' AND casino_id = (SELECT id FROM casinos WHERE slug = 'lucky-star-crypto-casino')), 'fa-random', 'Provably fair crash and plinko experiences for crypto-first players.'),
    ((SELECT id FROM casino_review_sections WHERE title = 'Available Game Types' AND casino_id = (SELECT id FROM casinos WHERE slug = 'lucky-star-crypto-casino')), 'fa-gem', 'Feature-rich video slots with bonus-buy mechanics and seasonal events.');

INSERT IGNORE INTO casino_pros_cons (casino_id, type, content)
VALUES
    ((SELECT id FROM casinos WHERE slug = 'lucky-star-crypto-casino'), 'pro', 'Crypto-friendly cashier with fast withdrawals'),
    ((SELECT id FROM casinos WHERE slug = 'lucky-star-crypto-casino'), 'pro', 'Generous VIP loyalty ladder and rakeback'),
    ((SELECT id FROM casinos WHERE slug = 'lucky-star-crypto-casino'), 'pro', 'Mobile-optimized live-dealer studios'),
    ((SELECT id FROM casinos WHERE slug = 'lucky-star-crypto-casino'), 'con', 'No dedicated mobile app'),
    ((SELECT id FROM casinos WHERE slug = 'lucky-star-crypto-casino'), 'con', 'Limited virtual reality experiences');

INSERT IGNORE INTO casino_highlights (casino_id, label, icon)
VALUES
    ((SELECT id FROM casinos WHERE slug = 'lucky-star-crypto-casino'), 'Casino Name: Lucky Star Entertainment Group', 'fa-building'),
    ((SELECT id FROM casinos WHERE slug = 'lucky-star-crypto-casino'), 'License: Curacao eGaming Authority', 'fa-shield-alt'),
    ((SELECT id FROM casinos WHERE slug = 'lucky-star-crypto-casino'), 'Welcome Bonus: $1,500 headline offer with blockchain payouts', 'fa-gift'),
    ((SELECT id FROM casinos WHERE slug = 'lucky-star-crypto-casino'), 'Genres: Live Casino, Crypto, Mobile friendly', 'fa-layer-group');

COMMIT;
