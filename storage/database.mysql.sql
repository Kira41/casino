
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
    is_top1 TINYINT(1) NOT NULL DEFAULT 0 CHECK (is_top1 IN (0, 1)),
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

CREATE UNIQUE INDEX IF NOT EXISTS uniq_content_cards_section_position_title
    ON content_cards(section, position, title);

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

CREATE TABLE IF NOT EXISTS casino_devices (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    casino_id BIGINT UNSIGNED NOT NULL,
    device_group VARCHAR(50) NOT NULL,
    device_key VARCHAR(50) NOT NULL,
    FOREIGN KEY (casino_id) REFERENCES casinos(id) ON DELETE CASCADE
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

CREATE TABLE IF NOT EXISTS casino_payment_methods (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    casino_id BIGINT UNSIGNED NOT NULL,
    method_name VARCHAR(100) NOT NULL,
    icon_key VARCHAR(100) NOT NULL,
    FOREIGN KEY (casino_id) REFERENCES casinos(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS providers (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(120) NOT NULL,
    image_path VARCHAR(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS payment_methods (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(120) NOT NULL,
    image_path VARCHAR(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS casino_provider_links (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    casino_id BIGINT UNSIGNED NOT NULL,
    provider_id BIGINT UNSIGNED NOT NULL,
    UNIQUE KEY casino_provider_unique (casino_id, provider_id),
    FOREIGN KEY (casino_id) REFERENCES casinos(id) ON DELETE CASCADE,
    FOREIGN KEY (provider_id) REFERENCES providers(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Seed reference data from the existing HTML content
INSERT IGNORE INTO casinos (id, slug, name, operator, license, headline_bonus, min_deposit_usd, hero_image, thumbnail_image, rating, short_description, cta_url)
VALUES
    (
        1,
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
        2,
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
        3,
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
        4,
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
        5,
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
        6,
        'aurora-vault-casino',
        'Aurora Vault Casino',
        NULL,
        NULL,
        NULL,
        NULL,
        'assets/images/bonus-page/top-game-01.png',
        'assets/images/bonus-page/top-game-01.png',
        5,
        'Top casino draw with premium tables and a perfect score from our reviewers.',
        'https://auroravault.example.com'
    ),
    (
        7,
        'quantum-spin-lounge',
        'Quantum Spin Lounge',
        NULL,
        NULL,
        NULL,
        NULL,
        'assets/images/bonus-page/top-game-02.png',
        'assets/images/bonus-page/top-game-02.png',
        4,
        'Lounge experience with quick spins, dependable payouts, and fast onboarding.',
        'https://quantumspin.example.com'
    ),
    (
        8,
        'imperial-halo-casino',
        'Imperial Halo Casino',
        NULL,
        NULL,
        NULL,
        NULL,
        'assets/images/bonus-page/top-game-03.png',
        'assets/images/bonus-page/top-game-03.png',
        3,
        'Reliable operator that focuses on core table games and classic slots.',
        'https://imperialhalo.example.com'
    ),
    (
        9,
        'obsidian-crown-club',
        'Obsidian Crown Club',
        NULL,
        NULL,
        NULL,
        NULL,
        'assets/images/bonus-page/top-game-04.png',
        'assets/images/bonus-page/top-game-04.png',
        5,
        'VIP-focused destination featuring high-roller tables and concierge-style support.',
        'https://obsidiancrown.example.com'
    ),
    (
        10,
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
        11,
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
        12,
        'neon-mirage-casino',
        'Neon Mirage Casino',
        NULL,
        NULL,
        NULL,
        NULL,
        'assets/images/slots-jackpots.png',
        'assets/images/slots-jackpots.png',
        0,
        'Bright, modern casino featured as a related destination in the review page.',
        'https://neonmirage.example.com'
    ),
    (
        13,
        'azure-spire-casino',
        'Azure Spire Casino',
        NULL,
        NULL,
        NULL,
        NULL,
        'assets/images/live-dealer-tables.png',
        'assets/images/live-dealer-tables.png',
        0,
        'Coastal-inspired casino listed alongside other related recommendations.',
        'https://azurespire.example.com'
    ),
    (
        14,
        'lucky-horizon-lounge',
        'Lucky Horizon Lounge',
        NULL,
        NULL,
        NULL,
        NULL,
        'assets/images/sports-betting.png',
        'assets/images/sports-betting.png',
        0,
        'Lounge experience for players exploring additional curated casinos.',
        'https://luckyhorizon.example.com'
    ),
    (
        15,
        'starlit-crown-casino',
        'Starlit Crown Casino',
        NULL,
        NULL,
        NULL,
        NULL,
        'assets/images/vip-programs.png',
        'assets/images/vip-programs.png',
        0,
        'Boutique casino highlighted as a related option for readers.',
        'https://starlitcrown.example.com'
    ),
    (
        16,
        'golden-drift-resort',
        'Golden Drift Resort',
        NULL,
        NULL,
        NULL,
        NULL,
        'assets/images/crypto-casinos.png',
        'assets/images/crypto-casinos.png',
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
    ((SELECT id FROM casinos WHERE slug = 'lucky-star-crypto-casino'), (SELECT id FROM casino_tags WHERE name = 'VIP Club'), 0),
    ((SELECT id FROM casinos WHERE slug = 'nova-royale-casino'), (SELECT id FROM casino_tags WHERE name = 'Live Casino'), 1),
    ((SELECT id FROM casinos WHERE slug = 'nova-royale-casino'), (SELECT id FROM casino_tags WHERE name = 'Mobile'), 0),
    ((SELECT id FROM casinos WHERE slug = 'nova-royale-casino'), (SELECT id FROM casino_tags WHERE name = 'Welcome Bonus'), 0),
    ((SELECT id FROM casinos WHERE slug = 'nova-royale-casino'), (SELECT id FROM casino_tags WHERE name = 'Free Spins'), 0),
    ((SELECT id FROM casinos WHERE slug = 'starlight-spins-resort'), (SELECT id FROM casino_tags WHERE name = 'Live Casino'), 1),
    ((SELECT id FROM casinos WHERE slug = 'starlight-spins-resort'), (SELECT id FROM casino_tags WHERE name = 'Mobile'), 0),
    ((SELECT id FROM casinos WHERE slug = 'starlight-spins-resort'), (SELECT id FROM casino_tags WHERE name = 'VIP Club'), 0),
    ((SELECT id FROM casinos WHERE slug = 'starlight-spins-resort'), (SELECT id FROM casino_tags WHERE name = 'Free Spins'), 0),
    ((SELECT id FROM casinos WHERE slug = 'emerald-mirage-club'), (SELECT id FROM casino_tags WHERE name = 'Live Casino'), 1),
    ((SELECT id FROM casinos WHERE slug = 'emerald-mirage-club'), (SELECT id FROM casino_tags WHERE name = 'Crypto'), 0),
    ((SELECT id FROM casinos WHERE slug = 'emerald-mirage-club'), (SELECT id FROM casino_tags WHERE name = 'VIP Club'), 0),
    ((SELECT id FROM casinos WHERE slug = 'celestial-fortune-hall'), (SELECT id FROM casino_tags WHERE name = 'Live Casino'), 1),
    ((SELECT id FROM casinos WHERE slug = 'celestial-fortune-hall'), (SELECT id FROM casino_tags WHERE name = 'Welcome Bonus'), 0),
    ((SELECT id FROM casinos WHERE slug = 'celestial-fortune-hall'), (SELECT id FROM casino_tags WHERE name = 'VIP Club'), 0),
    ((SELECT id FROM casinos WHERE slug = 'aurora-vault-casino'), (SELECT id FROM casino_tags WHERE name = 'Crypto'), 1),
    ((SELECT id FROM casinos WHERE slug = 'aurora-vault-casino'), (SELECT id FROM casino_tags WHERE name = 'Mobile'), 0),
    ((SELECT id FROM casinos WHERE slug = 'aurora-vault-casino'), (SELECT id FROM casino_tags WHERE name = 'Welcome Bonus'), 0),
    ((SELECT id FROM casinos WHERE slug = 'aurora-vault-casino'), (SELECT id FROM casino_tags WHERE name = 'VIP Club'), 0),
    ((SELECT id FROM casinos WHERE slug = 'quantum-spin-lounge'), (SELECT id FROM casino_tags WHERE name = 'Live Casino'), 1),
    ((SELECT id FROM casinos WHERE slug = 'quantum-spin-lounge'), (SELECT id FROM casino_tags WHERE name = 'Mobile'), 0),
    ((SELECT id FROM casinos WHERE slug = 'quantum-spin-lounge'), (SELECT id FROM casino_tags WHERE name = 'Free Spins'), 0),
    ((SELECT id FROM casinos WHERE slug = 'imperial-halo-casino'), (SELECT id FROM casino_tags WHERE name = 'Live Casino'), 1),
    ((SELECT id FROM casinos WHERE slug = 'imperial-halo-casino'), (SELECT id FROM casino_tags WHERE name = 'VIP Club'), 0),
    ((SELECT id FROM casinos WHERE slug = 'obsidian-crown-club'), (SELECT id FROM casino_tags WHERE name = 'Crypto'), 1),
    ((SELECT id FROM casinos WHERE slug = 'obsidian-crown-club'), (SELECT id FROM casino_tags WHERE name = 'Live Casino'), 0),
    ((SELECT id FROM casinos WHERE slug = 'obsidian-crown-club'), (SELECT id FROM casino_tags WHERE name = 'VIP Club'), 0),
    ((SELECT id FROM casinos WHERE slug = 'mirage-of-millions'), (SELECT id FROM casino_tags WHERE name = 'Mobile'), 1),
    ((SELECT id FROM casinos WHERE slug = 'mirage-of-millions'), (SELECT id FROM casino_tags WHERE name = 'Welcome Bonus'), 0),
    ((SELECT id FROM casinos WHERE slug = 'luminous-ledger-casino'), (SELECT id FROM casino_tags WHERE name = 'Crypto'), 1),
    ((SELECT id FROM casinos WHERE slug = 'luminous-ledger-casino'), (SELECT id FROM casino_tags WHERE name = 'Mobile'), 0),
    ((SELECT id FROM casinos WHERE slug = 'luminous-ledger-casino'), (SELECT id FROM casino_tags WHERE name = 'Free Spins'), 0),
    ((SELECT id FROM casinos WHERE slug = 'neon-mirage-casino'), (SELECT id FROM casino_tags WHERE name = 'Live Casino'), 1),
    ((SELECT id FROM casinos WHERE slug = 'neon-mirage-casino'), (SELECT id FROM casino_tags WHERE name = 'Free Spins'), 0),
    ((SELECT id FROM casinos WHERE slug = 'azure-spire-casino'), (SELECT id FROM casino_tags WHERE name = 'Live Casino'), 1),
    ((SELECT id FROM casinos WHERE slug = 'azure-spire-casino'), (SELECT id FROM casino_tags WHERE name = 'Mobile'), 0),
    ((SELECT id FROM casinos WHERE slug = 'azure-spire-casino'), (SELECT id FROM casino_tags WHERE name = 'Welcome Bonus'), 0),
    ((SELECT id FROM casinos WHERE slug = 'lucky-horizon-lounge'), (SELECT id FROM casino_tags WHERE name = 'Live Casino'), 1),
    ((SELECT id FROM casinos WHERE slug = 'lucky-horizon-lounge'), (SELECT id FROM casino_tags WHERE name = 'Free Spins'), 0),
    ((SELECT id FROM casinos WHERE slug = 'starlit-crown-casino'), (SELECT id FROM casino_tags WHERE name = 'Crypto'), 1),
    ((SELECT id FROM casinos WHERE slug = 'starlit-crown-casino'), (SELECT id FROM casino_tags WHERE name = 'Live Casino'), 0),
    ((SELECT id FROM casinos WHERE slug = 'starlit-crown-casino'), (SELECT id FROM casino_tags WHERE name = 'VIP Club'), 0),
    ((SELECT id FROM casinos WHERE slug = 'golden-drift-resort'), (SELECT id FROM casino_tags WHERE name = 'Live Casino'), 1),
    ((SELECT id FROM casinos WHERE slug = 'golden-drift-resort'), (SELECT id FROM casino_tags WHERE name = 'Mobile'), 0),
    ((SELECT id FROM casinos WHERE slug = 'golden-drift-resort'), (SELECT id FROM casino_tags WHERE name = 'Welcome Bonus'), 0);

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
    ((SELECT id FROM casinos WHERE slug = 'aurora-vault-casino'), 'most_played', 'Aurora Vault Casino', 'assets/images/bonus-page/top-game-01.png', NULL, 5, NULL, 1),
    ((SELECT id FROM casinos WHERE slug = 'quantum-spin-lounge'), 'most_played', 'Quantum Spin Lounge', 'assets/images/bonus-page/top-game-02.png', NULL, 4, NULL, 2),
    ((SELECT id FROM casinos WHERE slug = 'imperial-halo-casino'), 'most_played', 'Imperial Halo Casino', 'assets/images/bonus-page/top-game-03.png', NULL, 3, NULL, 3),
    ((SELECT id FROM casinos WHERE slug = 'obsidian-crown-club'), 'most_played', 'Obsidian Crown Club', 'assets/images/bonus-page/top-game-04.png', NULL, 5, NULL, 4),
    ((SELECT id FROM casinos WHERE slug = 'mirage-of-millions'), 'most_played', 'Mirage of Millions', 'assets/images/top-game-05.jpg', NULL, 2, NULL, 5),
    ((SELECT id FROM casinos WHERE slug = 'luminous-ledger-casino'), 'most_played', 'Luminous Ledger Casino', 'assets/images/top-game-06.jpg', NULL, 4, NULL, 6),
    ((SELECT id FROM casinos WHERE slug = 'neon-mirage-casino'), 'related', 'Neon Mirage Casino', 'assets/images/slots-jackpots.png', NULL, NULL, NULL, 1),
    ((SELECT id FROM casinos WHERE slug = 'azure-spire-casino'), 'related', 'Azure Spire Casino', 'assets/images/live-dealer-tables.png', NULL, NULL, NULL, 2),
    ((SELECT id FROM casinos WHERE slug = 'lucky-horizon-lounge'), 'related', 'Lucky Horizon Lounge', 'assets/images/sports-betting.png', NULL, NULL, NULL, 3),
    ((SELECT id FROM casinos WHERE slug = 'starlit-crown-casino'), 'related', 'Starlit Crown Casino', 'assets/images/vip-programs.png', NULL, NULL, NULL, 4),
    ((SELECT id FROM casinos WHERE slug = 'golden-drift-resort'), 'related', 'Golden Drift Resort', 'assets/images/crypto-casinos.png', NULL, NULL, NULL, 5);

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
    ('bonus_guides', 'Maximize first deposits', 'Welcome bundles', 'Step-by-step', 'Stack deposit matches, free spins, and loyalty opt-ins without triggering tough wagering limits.', 'assets/images/bonus-page/trending-01.png', 1),
    ('bonus_guides', 'Instant-cash offers', 'No wagering', 'Low-risk', 'Spot no-wager deals, typical withdrawal rules, and the playthrough traps that still appear in the fine print.', 'assets/images/bonus-page/trending-02.png', 2),
    ('bonus_guides', 'Pick the right slots', 'Free spins', 'Reader favorite', 'Compare RTP, volatility, and eligible game lists so your spins ladder up to withdrawable balances.', 'assets/images/bonus-page/trending-03.png', 3),
    ('bonus_guides', 'Make losses sting less', 'Cashback', 'Sustained value', 'Find casinos with next-day cashback, tier multipliers, and transparent loss calculations.', 'assets/images/bonus-page/trending-04.png', 4),
    ('bonus_shortlist', 'Under $20 deposits', 'Budget', NULL, 'Combine small-deposit matches with low wagering to keep the bankroll flexible.', 'assets/images/bonus-page/top-game-01.png', 1),
    ('bonus_shortlist', 'Big match playbooks', 'High-roller', NULL, 'Prioritize flexible max bets, higher withdrawal caps, and accelerated VIP status triggers.', 'assets/images/bonus-page/top-game-02.png', 2),
    ('bonus_shortlist', 'Token-based promos', 'Crypto', NULL, 'Use faster payouts and coin-specific bonuses to avoid conversion fees and release delays.', 'assets/images/bonus-page/top-game-03.png', 3),
    ('bonus_shortlist', 'Bet-slip boosts', 'Sports', NULL, 'Balance bet insurance, odds boosts, and wagering contribution rules for multi-leg slips.', 'assets/images/bonus-page/top-game-04.png', 4),
    ('game_library', 'Fresh releases weekly', 'Slots & jackpots', '12K+ titles', 'We flag casinos adding new Megaways, cluster pays, and branded slots as soon as they drop.', 'assets/images/slots-jackpots.png', 1),
    ('game_library', 'Studios with low latency', 'Tables & shows', 'Live dealers', 'Lightning roulette, blackjack parties, and game shows streamed with multi-camera coverage.', 'assets/images/live-dealer-tables.png', 2),
    ('game_library', 'One wallet play', 'Sports & eSports', 'Hybrid', 'Single-balance wagering across sportsbook, racebook, and in-house virtuals with instant settlement.', 'assets/images/sports-betting.png', 3),
    ('game_library', 'Balanced catalogs', 'Premium studios', 'Provider mix', 'NetEnt visuals, Pragmatic volatility, and Evolution live tables curated for every bankroll.', 'assets/images/vip-programs.png', 4),
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
    ('Slots & Jackpots', 'assets/images/slots-jackpots.png', 'top_categories'),
    ('Live Dealer Tables', 'assets/images/live-dealer-tables.png', 'top_categories'),
    ('Sports Betting', 'assets/images/sports-betting.png', 'top_categories'),
    ('VIP Programs', 'assets/images/vip-programs.png', 'top_categories'),
    ('Crypto Casinos', 'assets/images/crypto-casinos.png', 'top_categories');

INSERT IGNORE INTO casino_game_modes (casino_id, game_type, live_dealer_supported, virtual_reality_supported)
VALUES
    ((SELECT id FROM casinos WHERE slug = 'lucky-star-crypto-casino'), 'Roulette', 0, 0),
    ((SELECT id FROM casinos WHERE slug = 'lucky-star-crypto-casino'), 'Slots', 0, 0),
    ((SELECT id FROM casinos WHERE slug = 'lucky-star-crypto-casino'), 'Blackjack', 0, 0),
    ((SELECT id FROM casinos WHERE slug = 'lucky-star-crypto-casino'), 'Baccarat', 1, 0),
    ((SELECT id FROM casinos WHERE slug = 'lucky-star-crypto-casino'), 'Crash Games', 0, 0),
    ((SELECT id FROM casinos WHERE slug = 'lucky-star-crypto-casino'), 'Live Shows', 1, 0),
    ((SELECT id FROM casinos WHERE slug = 'lucky-star-crypto-casino'), 'Video Poker', 0, 0),
    ((SELECT id FROM casinos WHERE slug = 'nova-royale-casino'), 'Slots', 0, 0),
    ((SELECT id FROM casinos WHERE slug = 'nova-royale-casino'), 'Blackjack', 1, 0),
    ((SELECT id FROM casinos WHERE slug = 'nova-royale-casino'), 'Live Shows', 1, 0),
    ((SELECT id FROM casinos WHERE slug = 'starlight-spins-resort'), 'Roulette', 1, 0),
    ((SELECT id FROM casinos WHERE slug = 'starlight-spins-resort'), 'Baccarat', 1, 0),
    ((SELECT id FROM casinos WHERE slug = 'starlight-spins-resort'), 'Slots', 0, 0),
    ((SELECT id FROM casinos WHERE slug = 'emerald-mirage-club'), 'Blackjack', 1, 0),
    ((SELECT id FROM casinos WHERE slug = 'emerald-mirage-club'), 'Poker', 0, 0),
    ((SELECT id FROM casinos WHERE slug = 'emerald-mirage-club'), 'Slots', 0, 0),
    ((SELECT id FROM casinos WHERE slug = 'celestial-fortune-hall'), 'Roulette', 1, 0),
    ((SELECT id FROM casinos WHERE slug = 'celestial-fortune-hall'), 'Slots', 0, 0),
    ((SELECT id FROM casinos WHERE slug = 'celestial-fortune-hall'), 'Crash Games', 0, 0),
    ((SELECT id FROM casinos WHERE slug = 'aurora-vault-casino'), 'Slots', 0, 0),
    ((SELECT id FROM casinos WHERE slug = 'aurora-vault-casino'), 'Blackjack', 1, 0),
    ((SELECT id FROM casinos WHERE slug = 'aurora-vault-casino'), 'Live Roulette', 1, 0),
    ((SELECT id FROM casinos WHERE slug = 'quantum-spin-lounge'), 'Slots', 0, 0),
    ((SELECT id FROM casinos WHERE slug = 'quantum-spin-lounge'), 'Game Shows', 1, 1),
    ((SELECT id FROM casinos WHERE slug = 'quantum-spin-lounge'), 'Table Games', 1, 0),
    ((SELECT id FROM casinos WHERE slug = 'imperial-halo-casino'), 'Blackjack', 1, 0),
    ((SELECT id FROM casinos WHERE slug = 'imperial-halo-casino'), 'Roulette', 1, 0),
    ((SELECT id FROM casinos WHERE slug = 'imperial-halo-casino'), 'Slots', 0, 0),
    ((SELECT id FROM casinos WHERE slug = 'obsidian-crown-club'), 'High Roller Tables', 1, 0),
    ((SELECT id FROM casinos WHERE slug = 'obsidian-crown-club'), 'Slots', 0, 0),
    ((SELECT id FROM casinos WHERE slug = 'obsidian-crown-club'), 'Poker', 1, 0),
    ((SELECT id FROM casinos WHERE slug = 'mirage-of-millions'), 'Slots', 0, 0),
    ((SELECT id FROM casinos WHERE slug = 'mirage-of-millions'), 'Roulette', 0, 0),
    ((SELECT id FROM casinos WHERE slug = 'mirage-of-millions'), 'Live Blackjack', 1, 0),
    ((SELECT id FROM casinos WHERE slug = 'luminous-ledger-casino'), 'Slots', 0, 0),
    ((SELECT id FROM casinos WHERE slug = 'luminous-ledger-casino'), 'Crash Games', 0, 0),
    ((SELECT id FROM casinos WHERE slug = 'luminous-ledger-casino'), 'Live Dealer', 1, 0),
    ((SELECT id FROM casinos WHERE slug = 'neon-mirage-casino'), 'Slots', 0, 0),
    ((SELECT id FROM casinos WHERE slug = 'neon-mirage-casino'), 'Live Shows', 1, 1),
    ((SELECT id FROM casinos WHERE slug = 'neon-mirage-casino'), 'Table Games', 1, 0),
    ((SELECT id FROM casinos WHERE slug = 'azure-spire-casino'), 'Roulette', 1, 0),
    ((SELECT id FROM casinos WHERE slug = 'azure-spire-casino'), 'Blackjack', 1, 0),
    ((SELECT id FROM casinos WHERE slug = 'azure-spire-casino'), 'Slots', 0, 0),
    ((SELECT id FROM casinos WHERE slug = 'lucky-horizon-lounge'), 'Slots', 0, 0),
    ((SELECT id FROM casinos WHERE slug = 'lucky-horizon-lounge'), 'Live Blackjack', 1, 0),
    ((SELECT id FROM casinos WHERE slug = 'lucky-horizon-lounge'), 'Game Shows', 1, 0),
    ((SELECT id FROM casinos WHERE slug = 'starlit-crown-casino'), 'Slots', 0, 0),
    ((SELECT id FROM casinos WHERE slug = 'starlit-crown-casino'), 'Roulette', 1, 0),
    ((SELECT id FROM casinos WHERE slug = 'starlit-crown-casino'), 'Blackjack', 1, 0),
    ((SELECT id FROM casinos WHERE slug = 'golden-drift-resort'), 'Slots', 0, 0),
    ((SELECT id FROM casinos WHERE slug = 'golden-drift-resort'), 'Poker', 0, 0),
    ((SELECT id FROM casinos WHERE slug = 'golden-drift-resort'), 'Live Roulette', 1, 0);

INSERT IGNORE INTO casino_review_sections (casino_id, title, summary)
VALUES
    ((SELECT id FROM casinos WHERE slug = 'lucky-star-crypto-casino'), 'Banking', 'Instant crypto deposits and on-chain withdrawals keep payouts transparent and quick.'),
    ((SELECT id FROM casinos WHERE slug = 'lucky-star-crypto-casino'), 'Support', 'Always-on support team ready to assist members across languages and time zones.'),
    ((SELECT id FROM casinos WHERE slug = 'lucky-star-crypto-casino'), 'Additional Info', 'Licensed operation that publishes regular payout audits for transparency.'),
    ((SELECT id FROM casinos WHERE slug = 'lucky-star-crypto-casino'), 'Available Game Types', 'Live-dealer tables, provably fair crash games, and gem-forward slot experiences.'),
    ((SELECT id FROM casinos WHERE slug = 'nova-royale-casino'), 'Banking', 'Credit, e-wallet, and prepaid options clear quickly with proactive fraud checks.'),
    ((SELECT id FROM casinos WHERE slug = 'nova-royale-casino'), 'Support', 'Concierge-style help desk with scripted recovery paths for stalled withdrawals.'),
    ((SELECT id FROM casinos WHERE slug = 'nova-royale-casino'), 'Game Portfolio', 'Polished lobby with balanced slots, live tables, and seasonal missions.'),
    ((SELECT id FROM casinos WHERE slug = 'starlight-spins-resort'), 'Banking', 'Fast approvals for mid-sized deposits with clear limits per corridor.'),
    ((SELECT id FROM casinos WHERE slug = 'starlight-spins-resort'), 'Support', 'Resort-style agents trained to accelerate identity verification.'),
    ((SELECT id FROM casinos WHERE slug = 'starlight-spins-resort'), 'Game Portfolio', 'Live dealer focus with curated baccarat and roulette streams.'),
    ((SELECT id FROM casinos WHERE slug = 'emerald-mirage-club'), 'Banking', 'Priority desk for high-roller transfers and same-day wires.'),
    ((SELECT id FROM casinos WHERE slug = 'emerald-mirage-club'), 'Support', 'VIP hosts reachable via chat and phone with escalation power.'),
    ((SELECT id FROM casinos WHERE slug = 'emerald-mirage-club'), 'Game Portfolio', 'Poker-forward catalog plus lounge-ready table minimums.'),
    ((SELECT id FROM casinos WHERE slug = 'celestial-fortune-hall'), 'Banking', 'Hybrid cashiers supporting cards, wallets, and crypto with low fees.'),
    ((SELECT id FROM casinos WHERE slug = 'celestial-fortune-hall'), 'Support', 'Hall attendants staffed around the clock with multilingual coverage.'),
    ((SELECT id FROM casinos WHERE slug = 'celestial-fortune-hall'), 'Game Portfolio', 'Crash games, roulette, and slots with refined design touches.'),
    ((SELECT id FROM casinos WHERE slug = 'aurora-vault-casino'), 'Banking', 'Instant crypto routes and fast card payouts.'),
    ((SELECT id FROM casinos WHERE slug = 'aurora-vault-casino'), 'Support', 'Specialists oversee compliance checks to keep withdrawals on track.'),
    ((SELECT id FROM casinos WHERE slug = 'aurora-vault-casino'), 'Game Portfolio', 'High-rated mix of blackjack, roulette, and blockbuster slots.'),
    ((SELECT id FROM casinos WHERE slug = 'quantum-spin-lounge'), 'Banking', 'Automated approvals with dynamic limits for frequent withdrawals.'),
    ((SELECT id FROM casinos WHERE slug = 'quantum-spin-lounge'), 'Support', 'Live lounge ambassadors respond quickly during busy hours.'),
    ((SELECT id FROM casinos WHERE slug = 'quantum-spin-lounge'), 'Game Portfolio', 'VR-ready shows, tables, and quick-spin slots for casual play.'),
    ((SELECT id FROM casinos WHERE slug = 'imperial-halo-casino'), 'Banking', 'Traditional banking pairs with trusted e-wallet corridors.'),
    ((SELECT id FROM casinos WHERE slug = 'imperial-halo-casino'), 'Support', 'Structured help center with transparent SLAs and callbacks.'),
    ((SELECT id FROM casinos WHERE slug = 'imperial-halo-casino'), 'Game Portfolio', 'Core blackjack and roulette roster with featured slots.'),
    ((SELECT id FROM casinos WHERE slug = 'obsidian-crown-club'), 'Banking', 'Crypto-first cashier keeps high-limit players moving.'),
    ((SELECT id FROM casinos WHERE slug = 'obsidian-crown-club'), 'Support', 'Concierge hosts coordinate cashouts and table access.'),
    ((SELECT id FROM casinos WHERE slug = 'obsidian-crown-club'), 'Game Portfolio', 'High-limit tables, exclusive slots, and poker variants.'),
    ((SELECT id FROM casinos WHERE slug = 'mirage-of-millions'), 'Banking', 'Straightforward cashier that publishes average approval times.'),
    ((SELECT id FROM casinos WHERE slug = 'mirage-of-millions'), 'Support', 'Responsive chat team keeps casual players informed.'),
    ((SELECT id FROM casinos WHERE slug = 'mirage-of-millions'), 'Game Portfolio', 'Entry-level mix of slots, roulette, and live blackjack.'),
    ((SELECT id FROM casinos WHERE slug = 'luminous-ledger-casino'), 'Banking', 'Ledger-grade transparency with on-chain status updates.'),
    ((SELECT id FROM casinos WHERE slug = 'luminous-ledger-casino'), 'Support', 'Analyst-led help desk that shares payout metrics openly.'),
    ((SELECT id FROM casinos WHERE slug = 'luminous-ledger-casino'), 'Game Portfolio', 'Provably fair crash titles, live dealers, and trusted slots.'),
    ((SELECT id FROM casinos WHERE slug = 'neon-mirage-casino'), 'Banking', 'Bright UI wraps card, wallet, and crypto options with timers.'),
    ((SELECT id FROM casinos WHERE slug = 'neon-mirage-casino'), 'Support', 'Live hosts ready during peak streaming events.'),
    ((SELECT id FROM casinos WHERE slug = 'neon-mirage-casino'), 'Game Portfolio', 'Neon-branded shows, slots, and table games for variety.'),
    ((SELECT id FROM casinos WHERE slug = 'azure-spire-casino'), 'Banking', 'Coastal-themed cashier with reliable live-dealer friendly limits.'),
    ((SELECT id FROM casinos WHERE slug = 'azure-spire-casino'), 'Support', 'Agents specialize in live game troubleshooting and payouts.'),
    ((SELECT id FROM casinos WHERE slug = 'azure-spire-casino'), 'Game Portfolio', 'Balanced set of blackjack, roulette, and slot favorites.'),
    ((SELECT id FROM casinos WHERE slug = 'lucky-horizon-lounge'), 'Banking', 'Small-deposit lanes tuned for quick starts and recurring reloads.'),
    ((SELECT id FROM casinos WHERE slug = 'lucky-horizon-lounge'), 'Support', 'Lounge team handles document refreshes with minimal friction.'),
    ((SELECT id FROM casinos WHERE slug = 'lucky-horizon-lounge'), 'Game Portfolio', 'Live blackjack, shows, and approachable slots for new members.'),
    ((SELECT id FROM casinos WHERE slug = 'starlit-crown-casino'), 'Banking', 'Crypto and card hybrids with predictable processing windows.'),
    ((SELECT id FROM casinos WHERE slug = 'starlit-crown-casino'), 'Support', 'Crown club hosts keep players updated on bonus milestones.'),
    ((SELECT id FROM casinos WHERE slug = 'starlit-crown-casino'), 'Game Portfolio', 'Star-studded roulette, blackjack, and slot lineup.'),
    ((SELECT id FROM casinos WHERE slug = 'golden-drift-resort'), 'Banking', 'Resort cashier supports weekend requests with steady approval times.'),
    ((SELECT id FROM casinos WHERE slug = 'golden-drift-resort'), 'Support', 'On-call agents for live tables and hospitality perks.'),
    ((SELECT id FROM casinos WHERE slug = 'golden-drift-resort'), 'Game Portfolio', 'Live roulette, poker corners, and beachy slot picks.');

INSERT INTO casino_review_sections (casino_id, title, summary)
SELECT
    c.id,
    'General Information',
    CONCAT('Snapshot of ', c.name, ' including operator, licensing, and key requirements.')
FROM casinos c
WHERE NOT EXISTS (
    SELECT 1 FROM casino_review_sections s WHERE s.casino_id = c.id AND s.title = 'General Information'
);

INSERT INTO casino_review_sections (casino_id, title, summary)
SELECT
    c.id,
    'Devices',
    'Supported device options for playing on mobile and desktop.'
FROM casinos c
WHERE NOT EXISTS (
    SELECT 1 FROM casino_review_sections s WHERE s.casino_id = c.id AND s.title = 'Devices'
);

INSERT INTO casino_review_sections (casino_id, title, summary)
SELECT
    c.id,
    'Software Providers',
    'Curated studio mix delivering slots, tables, and live dealer experiences.'
FROM casinos c
WHERE NOT EXISTS (
    SELECT 1 FROM casino_review_sections s WHERE s.casino_id = c.id AND s.title = 'Software Providers'
);

INSERT INTO casino_review_sections (casino_id, title, summary)
SELECT
    c.id,
    'Additional Info',
    'Extra notes on promotions, security, and responsible play features.'
FROM casinos c
WHERE NOT EXISTS (
    SELECT 1 FROM casino_review_sections s WHERE s.casino_id = c.id AND s.title = 'Additional Info'
);

INSERT IGNORE INTO casino_review_points (review_section_id, icon, content)
VALUES
    ((SELECT id FROM casino_review_sections WHERE title = 'Banking' AND casino_id = (SELECT id FROM casinos WHERE slug = 'lucky-star-crypto-casino')), 'fa-university text-primary', 'Deposits clear instantly through popular crypto wallets to keep players moving.'),
    ((SELECT id FROM casino_review_sections WHERE title = 'Banking' AND casino_id = (SELECT id FROM casinos WHERE slug = 'lucky-star-crypto-casino')), 'fa-exchange-alt text-success', 'On-chain cashouts are verified quickly for transparent, near-instant payouts.'),
    ((SELECT id FROM casino_review_sections WHERE title = 'Support' AND casino_id = (SELECT id FROM casinos WHERE slug = 'lucky-star-crypto-casino')), 'fa-bolt text-warning', 'Lightning-fast responses that mirror the site''s payout speed.'),
    ((SELECT id FROM casino_review_sections WHERE title = 'Support' AND casino_id = (SELECT id FROM casinos WHERE slug = 'lucky-star-crypto-casino')), 'fa-headset text-info', '24/7 multilingual help desk backing the gaming studio experience.'),
    ((SELECT id FROM casino_review_sections WHERE title = 'Additional Info' AND casino_id = (SELECT id FROM casinos WHERE slug = 'lucky-star-crypto-casino')), 'fa-info-circle text-warning', 'Publishes monthly payout audits confirming the integrity of RNG-powered games.'),
    ((SELECT id FROM casino_review_sections WHERE title = 'Available Game Types' AND casino_id = (SELECT id FROM casinos WHERE slug = 'lucky-star-crypto-casino')), 'fa-chess-knight', 'Live blackjack, roulette, and baccarat streams straight from the studio.'),
    ((SELECT id FROM casino_review_sections WHERE title = 'Available Game Types' AND casino_id = (SELECT id FROM casinos WHERE slug = 'lucky-star-crypto-casino')), 'fa-random', 'Provably fair crash and plinko experiences for crypto-first players.'),
    ((SELECT id FROM casino_review_sections WHERE title = 'Available Game Types' AND casino_id = (SELECT id FROM casinos WHERE slug = 'lucky-star-crypto-casino')), 'fa-gem', 'Feature-rich video slots with bonus-buy mechanics and seasonal events.'),
    ((SELECT id FROM casino_review_sections WHERE title = 'Banking' AND casino_id = (SELECT id FROM casinos WHERE slug = 'nova-royale-casino')), 'fa-credit-card', 'Card and wallet deposits confirm in minutes for smooth session starts.'),
    ((SELECT id FROM casino_review_sections WHERE title = 'Banking' AND casino_id = (SELECT id FROM casinos WHERE slug = 'nova-royale-casino')), 'fa-shield', 'Fraud checks run quietly in the background to keep payouts moving.'),
    ((SELECT id FROM casino_review_sections WHERE title = 'Support' AND casino_id = (SELECT id FROM casinos WHERE slug = 'nova-royale-casino')), 'fa-life-ring', 'Concierge agents provide step-by-step fixes for stalled withdrawals.'),
    ((SELECT id FROM casino_review_sections WHERE title = 'Support' AND casino_id = (SELECT id FROM casinos WHERE slug = 'nova-royale-casino')), 'fa-commenting', 'Live chat transcripts include timelines for the next update.'),
    ((SELECT id FROM casino_review_sections WHERE title = 'Game Portfolio' AND casino_id = (SELECT id FROM casinos WHERE slug = 'nova-royale-casino')), 'fa-star', 'Seasonal missions highlight new slots and live tables.'),
    ((SELECT id FROM casino_review_sections WHERE title = 'Game Portfolio' AND casino_id = (SELECT id FROM casinos WHERE slug = 'nova-royale-casino')), 'fa-television', 'HD streams for blackjack and roulette keep the lobby polished.'),
    ((SELECT id FROM casino_review_sections WHERE title = 'Banking' AND casino_id = (SELECT id FROM casinos WHERE slug = 'starlight-spins-resort')), 'fa-bolt', 'Deposits under $100 verify quickly for resort guests.'),
    ((SELECT id FROM casino_review_sections WHERE title = 'Banking' AND casino_id = (SELECT id FROM casinos WHERE slug = 'starlight-spins-resort')), 'fa-umbrella-beach', 'Regional corridors are clearly marked with limits and fees.'),
    ((SELECT id FROM casino_review_sections WHERE title = 'Support' AND casino_id = (SELECT id FROM casinos WHERE slug = 'starlight-spins-resort')), 'fa-headset', 'Hosts coordinate document requests before big withdrawals.'),
    ((SELECT id FROM casino_review_sections WHERE title = 'Support' AND casino_id = (SELECT id FROM casinos WHERE slug = 'starlight-spins-resort')), 'fa-clock-o', 'Average response times stay under five minutes during peak hours.'),
    ((SELECT id FROM casino_review_sections WHERE title = 'Game Portfolio' AND casino_id = (SELECT id FROM casinos WHERE slug = 'starlight-spins-resort')), 'fa-glass', 'Baccarat and roulette streams feature multiple camera angles.'),
    ((SELECT id FROM casino_review_sections WHERE title = 'Game Portfolio' AND casino_id = (SELECT id FROM casinos WHERE slug = 'starlight-spins-resort')), 'fa-diamond', 'Slots rotate weekly with resort-branded events.'),
    ((SELECT id FROM casino_review_sections WHERE title = 'Banking' AND casino_id = (SELECT id FROM casinos WHERE slug = 'emerald-mirage-club')), 'fa-money', 'Same-day wires and crypto support high-roller bankrolls.'),
    ((SELECT id FROM casino_review_sections WHERE title = 'Banking' AND casino_id = (SELECT id FROM casinos WHERE slug = 'emerald-mirage-club')), 'fa-lock', 'Enhanced due diligence is handled proactively to avoid pauses.'),
    ((SELECT id FROM casino_review_sections WHERE title = 'Support' AND casino_id = (SELECT id FROM casinos WHERE slug = 'emerald-mirage-club')), 'fa-user-circle', 'Dedicated hosts respond directly to VIP chat threads.'),
    ((SELECT id FROM casino_review_sections WHERE title = 'Support' AND casino_id = (SELECT id FROM casinos WHERE slug = 'emerald-mirage-club')), 'fa-phone', 'Callback commitments are honored within 15 minutes.'),
    ((SELECT id FROM casino_review_sections WHERE title = 'Game Portfolio' AND casino_id = (SELECT id FROM casinos WHERE slug = 'emerald-mirage-club')), 'fa-suitcase', 'Poker rooms feature cash games and turbos with steady traffic.'),
    ((SELECT id FROM casino_review_sections WHERE title = 'Game Portfolio' AND casino_id = (SELECT id FROM casinos WHERE slug = 'emerald-mirage-club')), 'fa-star-half-o', 'Table minimums stay approachable while offering high-limit seats.'),
    ((SELECT id FROM casino_review_sections WHERE title = 'Banking' AND casino_id = (SELECT id FROM casinos WHERE slug = 'celestial-fortune-hall')), 'fa-rocket', 'Crypto, cards, and wallets share the same fast-track queue.'),
    ((SELECT id FROM casino_review_sections WHERE title = 'Banking' AND casino_id = (SELECT id FROM casinos WHERE slug = 'celestial-fortune-hall')), 'fa-percent', 'Low-fee corridors are highlighted before you confirm a transfer.'),
    ((SELECT id FROM casino_review_sections WHERE title = 'Support' AND casino_id = (SELECT id FROM casinos WHERE slug = 'celestial-fortune-hall')), 'fa-comments', 'Agents operate 24/7 with multilingual coverage.'),
    ((SELECT id FROM casino_review_sections WHERE title = 'Support' AND casino_id = (SELECT id FROM casinos WHERE slug = 'celestial-fortune-hall')), 'fa-lightbulb-o', 'Self-help center links to live chat if troubleshooting stalls.'),
    ((SELECT id FROM casino_review_sections WHERE title = 'Game Portfolio' AND casino_id = (SELECT id FROM casinos WHERE slug = 'celestial-fortune-hall')), 'fa-bolt', 'Crash games sit alongside classic roulette and slots.'),
    ((SELECT id FROM casino_review_sections WHERE title = 'Game Portfolio' AND casino_id = (SELECT id FROM casinos WHERE slug = 'celestial-fortune-hall')), 'fa-certificate', 'Seasonal jackpots and streak missions keep sessions engaging.'),
    ((SELECT id FROM casino_review_sections WHERE title = 'Banking' AND casino_id = (SELECT id FROM casinos WHERE slug = 'aurora-vault-casino')), 'fa-key', 'Secure cashier steps keep payouts moving.'),
    ((SELECT id FROM casino_review_sections WHERE title = 'Banking' AND casino_id = (SELECT id FROM casinos WHERE slug = 'aurora-vault-casino')), 'fa-line-chart', 'Crypto withdrawals are tracked with status updates.'),
    ((SELECT id FROM casino_review_sections WHERE title = 'Support' AND casino_id = (SELECT id FROM casinos WHERE slug = 'aurora-vault-casino')), 'fa-check', 'Specialists pre-approve documents to avoid weekend delays.'),
    ((SELECT id FROM casino_review_sections WHERE title = 'Support' AND casino_id = (SELECT id FROM casinos WHERE slug = 'aurora-vault-casino')), 'fa-envelope-open', 'Email follow-ups summarize each support step for clarity.'),
    ((SELECT id FROM casino_review_sections WHERE title = 'Game Portfolio' AND casino_id = (SELECT id FROM casinos WHERE slug = 'aurora-vault-casino')), 'fa-trophy', 'High-rated blackjack and roulette anchors the catalog.'),
    ((SELECT id FROM casino_review_sections WHERE title = 'Game Portfolio' AND casino_id = (SELECT id FROM casinos WHERE slug = 'aurora-vault-casino')), 'fa-film', 'Slots feature cinematic intros and frequent bonus rounds.'),
    ((SELECT id FROM casino_review_sections WHERE title = 'Banking' AND casino_id = (SELECT id FROM casinos WHERE slug = 'quantum-spin-lounge')), 'fa-random', 'Automated approvals adjust to your withdrawal history.'),
    ((SELECT id FROM casino_review_sections WHERE title = 'Banking' AND casino_id = (SELECT id FROM casinos WHERE slug = 'quantum-spin-lounge')), 'fa-podcast', 'Crypto and e-wallets post the fastest results.'),
    ((SELECT id FROM casino_review_sections WHERE title = 'Support' AND casino_id = (SELECT id FROM casinos WHERE slug = 'quantum-spin-lounge')), 'fa-comments-o', 'Lounge ambassadors keep responses personable and quick.'),
    ((SELECT id FROM casino_review_sections WHERE title = 'Support' AND casino_id = (SELECT id FROM casinos WHERE slug = 'quantum-spin-lounge')), 'fa-clock-o', 'Average queue times stay low even during weekend events.'),
    ((SELECT id FROM casino_review_sections WHERE title = 'Game Portfolio' AND casino_id = (SELECT id FROM casinos WHERE slug = 'quantum-spin-lounge')), 'fa-eye', 'VR-friendly shows pair with instant-play slots.'),
    ((SELECT id FROM casino_review_sections WHERE title = 'Game Portfolio' AND casino_id = (SELECT id FROM casinos WHERE slug = 'quantum-spin-lounge')), 'fa-magic', 'Table games include side bets and modern variants.'),
    ((SELECT id FROM casino_review_sections WHERE title = 'Banking' AND casino_id = (SELECT id FROM casinos WHERE slug = 'imperial-halo-casino')), 'fa-bank', 'Classic bank rails remain the core of this cashier.'),
    ((SELECT id FROM casino_review_sections WHERE title = 'Banking' AND casino_id = (SELECT id FROM casinos WHERE slug = 'imperial-halo-casino')), 'fa-paper-plane', 'E-wallet payouts aim to land within the same day.'),
    ((SELECT id FROM casino_review_sections WHERE title = 'Support' AND casino_id = (SELECT id FROM casinos WHERE slug = 'imperial-halo-casino')), 'fa-info', 'FAQ entries are concise and link to real agents when needed.'),
    ((SELECT id FROM casino_review_sections WHERE title = 'Support' AND casino_id = (SELECT id FROM casinos WHERE slug = 'imperial-halo-casino')), 'fa-mobile', 'Mobile chat support stays online during peak gaming windows.'),
    ((SELECT id FROM casino_review_sections WHERE title = 'Game Portfolio' AND casino_id = (SELECT id FROM casinos WHERE slug = 'imperial-halo-casino')), 'fa-cubes', 'Tables and slots cover the essentials without clutter.'),
    ((SELECT id FROM casino_review_sections WHERE title = 'Game Portfolio' AND casino_id = (SELECT id FROM casinos WHERE slug = 'imperial-halo-casino')), 'fa-puzzle-piece', 'Jackpot drops and classics rotate through featured rows.'),
    ((SELECT id FROM casino_review_sections WHERE title = 'Banking' AND casino_id = (SELECT id FROM casinos WHERE slug = 'obsidian-crown-club')), 'fa-diamond', 'High-limit withdrawals are prioritized with concierge oversight.'),
    ((SELECT id FROM casino_review_sections WHERE title = 'Banking' AND casino_id = (SELECT id FROM casinos WHERE slug = 'obsidian-crown-club')), 'fa-link', 'Crypto corridors keep fees lean for large movements.'),
    ((SELECT id FROM casino_review_sections WHERE title = 'Support' AND casino_id = (SELECT id FROM casinos WHERE slug = 'obsidian-crown-club')), 'fa-user-secret', 'Discreet hosts handle sensitive account changes.'),
    ((SELECT id FROM casino_review_sections WHERE title = 'Support' AND casino_id = (SELECT id FROM casinos WHERE slug = 'obsidian-crown-club')), 'fa-flag', 'Escalation paths are clearly stated for rapid resolutions.'),
    ((SELECT id FROM casino_review_sections WHERE title = 'Game Portfolio' AND casino_id = (SELECT id FROM casinos WHERE slug = 'obsidian-crown-club')), 'fa-crown', 'Exclusive tables cater to high rollers with tailored limits.'),
    ((SELECT id FROM casino_review_sections WHERE title = 'Game Portfolio' AND casino_id = (SELECT id FROM casinos WHERE slug = 'obsidian-crown-club')), 'fa-bolt', 'Slots and poker variants refresh with VIP-only events.'),
    ((SELECT id FROM casino_review_sections WHERE title = 'Banking' AND casino_id = (SELECT id FROM casinos WHERE slug = 'mirage-of-millions')), 'fa-exchange', 'Straightforward cashier shows expected approval times.'),
    ((SELECT id FROM casino_review_sections WHERE title = 'Banking' AND casino_id = (SELECT id FROM casinos WHERE slug = 'mirage-of-millions')), 'fa-thumbs-o-up', 'Low deposit options keep things approachable.'),
    ((SELECT id FROM casino_review_sections WHERE title = 'Support' AND casino_id = (SELECT id FROM casinos WHERE slug = 'mirage-of-millions')), 'fa-comments', 'Chat agents are honest about queue times.'),
    ((SELECT id FROM casino_review_sections WHERE title = 'Support' AND casino_id = (SELECT id FROM casinos WHERE slug = 'mirage-of-millions')), 'fa-compass', 'Guided walkthroughs help with first withdrawals.'),
    ((SELECT id FROM casino_review_sections WHERE title = 'Game Portfolio' AND casino_id = (SELECT id FROM casinos WHERE slug = 'mirage-of-millions')), 'fa-gift', 'Slots and roulette form the backbone of the library.'),
    ((SELECT id FROM casino_review_sections WHERE title = 'Game Portfolio' AND casino_id = (SELECT id FROM casinos WHERE slug = 'mirage-of-millions')), 'fa-television', 'Live blackjack tables are available during prime time.'),
    ((SELECT id FROM casino_review_sections WHERE title = 'Banking' AND casino_id = (SELECT id FROM casinos WHERE slug = 'luminous-ledger-casino')), 'fa-sitemap', 'On-chain proofs accompany each crypto cashout.'),
    ((SELECT id FROM casino_review_sections WHERE title = 'Banking' AND casino_id = (SELECT id FROM casinos WHERE slug = 'luminous-ledger-casino')), 'fa-check-square', 'Clear rules for verification are published ahead of time.'),
    ((SELECT id FROM casino_review_sections WHERE title = 'Support' AND casino_id = (SELECT id FROM casinos WHERE slug = 'luminous-ledger-casino')), 'fa-line-chart', 'Analysts provide payout stats inside the help center.'),
    ((SELECT id FROM casino_review_sections WHERE title = 'Support' AND casino_id = (SELECT id FROM casinos WHERE slug = 'luminous-ledger-casino')), 'fa-bell-o', 'Status alerts let you track each ticket step.'),
    ((SELECT id FROM casino_review_sections WHERE title = 'Game Portfolio' AND casino_id = (SELECT id FROM casinos WHERE slug = 'luminous-ledger-casino')), 'fa-sun-o', 'Provably fair crash games headline the crypto catalog.'),
    ((SELECT id FROM casino_review_sections WHERE title = 'Game Portfolio' AND casino_id = (SELECT id FROM casinos WHERE slug = 'luminous-ledger-casino')), 'fa-heartbeat', 'Live dealers and slots balance speed with transparency.'),
    ((SELECT id FROM casino_review_sections WHERE title = 'Banking' AND casino_id = (SELECT id FROM casinos WHERE slug = 'neon-mirage-casino')), 'fa-battery-full', 'Crypto, wallets, and cards post estimated timings before you pay.'),
    ((SELECT id FROM casino_review_sections WHERE title = 'Banking' AND casino_id = (SELECT id FROM casinos WHERE slug = 'neon-mirage-casino')), 'fa-lightbulb-o', 'Notifications outline next steps if reviews are needed.'),
    ((SELECT id FROM casino_review_sections WHERE title = 'Support' AND casino_id = (SELECT id FROM casinos WHERE slug = 'neon-mirage-casino')), 'fa-comments-o', 'Hosts stay active during live show peaks for quick fixes.'),
    ((SELECT id FROM casino_review_sections WHERE title = 'Support' AND casino_id = (SELECT id FROM casinos WHERE slug = 'neon-mirage-casino')), 'fa-microphone', 'Voice chat options open during select events.'),
    ((SELECT id FROM casino_review_sections WHERE title = 'Game Portfolio' AND casino_id = (SELECT id FROM casinos WHERE slug = 'neon-mirage-casino')), 'fa-bolt', 'Live shows and table games run alongside neon-styled slots.'),
    ((SELECT id FROM casino_review_sections WHERE title = 'Game Portfolio' AND casino_id = (SELECT id FROM casinos WHERE slug = 'neon-mirage-casino')), 'fa-mobile', 'Mobile layouts keep the interface light and quick.'),
    ((SELECT id FROM casino_review_sections WHERE title = 'Banking' AND casino_id = (SELECT id FROM casinos WHERE slug = 'azure-spire-casino')), 'fa-anchor', 'Coastal cashier highlights the fastest corridors for live players.'),
    ((SELECT id FROM casino_review_sections WHERE title = 'Banking' AND casino_id = (SELECT id FROM casinos WHERE slug = 'azure-spire-casino')), 'fa-shield', 'Security prompts appear early to avoid mid-session pauses.'),
    ((SELECT id FROM casino_review_sections WHERE title = 'Support' AND casino_id = (SELECT id FROM casinos WHERE slug = 'azure-spire-casino')), 'fa-life-ring', 'Support agents know live dealer etiquette and troubleshooting.'),
    ((SELECT id FROM casino_review_sections WHERE title = 'Support' AND casino_id = (SELECT id FROM casinos WHERE slug = 'azure-spire-casino')), 'fa-clock-o', 'Transparent SLAs with updates every few minutes.'),
    ((SELECT id FROM casino_review_sections WHERE title = 'Game Portfolio' AND casino_id = (SELECT id FROM casinos WHERE slug = 'azure-spire-casino')), 'fa-ship', 'Roulette and blackjack streams pair with shoreline-inspired slots.'),
    ((SELECT id FROM casino_review_sections WHERE title = 'Game Portfolio' AND casino_id = (SELECT id FROM casinos WHERE slug = 'azure-spire-casino')), 'fa-compass', 'Navigation shortcuts make it easy to swap between live tables.'),
    ((SELECT id FROM casino_review_sections WHERE title = 'Banking' AND casino_id = (SELECT id FROM casinos WHERE slug = 'lucky-horizon-lounge')), 'fa-ticket', 'Low deposit thresholds make starting sessions simple.'),
    ((SELECT id FROM casino_review_sections WHERE title = 'Banking' AND casino_id = (SELECT id FROM casinos WHERE slug = 'lucky-horizon-lounge')), 'fa-road', 'Reload paths are optimized for frequent small top-ups.'),
    ((SELECT id FROM casino_review_sections WHERE title = 'Support' AND casino_id = (SELECT id FROM casinos WHERE slug = 'lucky-horizon-lounge')), 'fa-smile-o', 'Friendly lounge team replies with clear next steps.'),
    ((SELECT id FROM casino_review_sections WHERE title = 'Support' AND casino_id = (SELECT id FROM casinos WHERE slug = 'lucky-horizon-lounge')), 'fa-leaf', 'Verification checklists are short and easy to follow.'),
    ((SELECT id FROM casino_review_sections WHERE title = 'Game Portfolio' AND casino_id = (SELECT id FROM casinos WHERE slug = 'lucky-horizon-lounge')), 'fa-sun-o', 'Live blackjack and shows headline the schedule.'),
    ((SELECT id FROM casino_review_sections WHERE title = 'Game Portfolio' AND casino_id = (SELECT id FROM casinos WHERE slug = 'lucky-horizon-lounge')), 'fa-paper-plane', 'Slots stay approachable with clear volatility labels.'),
    ((SELECT id FROM casino_review_sections WHERE title = 'Banking' AND casino_id = (SELECT id FROM casinos WHERE slug = 'starlit-crown-casino')), 'fa-bell', 'Banking lanes display countdowns for reviews.'),
    ((SELECT id FROM casino_review_sections WHERE title = 'Banking' AND casino_id = (SELECT id FROM casinos WHERE slug = 'starlit-crown-casino')), 'fa-map-pin', 'Regional options are called out with fees and limits.'),
    ((SELECT id FROM casino_review_sections WHERE title = 'Support' AND casino_id = (SELECT id FROM casinos WHERE slug = 'starlit-crown-casino')), 'fa-moon-o', 'Hosts cover late-night hours to keep crown guests moving.'),
    ((SELECT id FROM casino_review_sections WHERE title = 'Support' AND casino_id = (SELECT id FROM casinos WHERE slug = 'starlit-crown-casino')), 'fa-bolt', 'Escalations happen fast when withdrawal timers slip.'),
    ((SELECT id FROM casino_review_sections WHERE title = 'Game Portfolio' AND casino_id = (SELECT id FROM casinos WHERE slug = 'starlit-crown-casino')), 'fa-star', 'Roulette, blackjack, and slots lean into the crown theme.'),
    ((SELECT id FROM casino_review_sections WHERE title = 'Game Portfolio' AND casino_id = (SELECT id FROM casinos WHERE slug = 'starlit-crown-casino')), 'fa-cloud', 'Content rotates frequently to keep repeat visits fresh.'),
    ((SELECT id FROM casino_review_sections WHERE title = 'Banking' AND casino_id = (SELECT id FROM casinos WHERE slug = 'golden-drift-resort')), 'fa-ship', 'Resort cashier handles weekend requests without slowing down.'),
    ((SELECT id FROM casino_review_sections WHERE title = 'Banking' AND casino_id = (SELECT id FROM casinos WHERE slug = 'golden-drift-resort')), 'fa-calendar-check-o', 'Payout windows are posted daily for transparency.'),
    ((SELECT id FROM casino_review_sections WHERE title = 'Support' AND casino_id = (SELECT id FROM casinos WHERE slug = 'golden-drift-resort')), 'fa-sun-o', 'Hospitality-trained agents manage live chat and phone queues.'),
    ((SELECT id FROM casino_review_sections WHERE title = 'Support' AND casino_id = (SELECT id FROM casinos WHERE slug = 'golden-drift-resort')), 'fa-plane', 'Hosts can coordinate events or table access on request.'),
    ((SELECT id FROM casino_review_sections WHERE title = 'Game Portfolio' AND casino_id = (SELECT id FROM casinos WHERE slug = 'golden-drift-resort')), 'fa-anchor', 'Live roulette anchors the floor with beach-inspired branding.'),
    ((SELECT id FROM casino_review_sections WHERE title = 'Game Portfolio' AND casino_id = (SELECT id FROM casinos WHERE slug = 'golden-drift-resort')), 'fa-flag-checkered', 'Poker corners and slots round out the resort experience.');

INSERT INTO casino_review_points (review_section_id, icon, content)
SELECT
    s.id,
    'fa-building text-info',
    CONCAT('Operator: ', COALESCE(c.operator, c.name))
FROM casino_review_sections s
JOIN casinos c ON c.id = s.casino_id
WHERE s.title = 'General Information'
  AND NOT EXISTS (
      SELECT 1 FROM casino_review_points p
      WHERE p.review_section_id = s.id
        AND p.content = CONCAT('Operator: ', COALESCE(c.operator, c.name))
  );

INSERT INTO casino_review_points (review_section_id, icon, content)
SELECT
    s.id,
    'fa-shield-alt text-warning',
    CONCAT('License: ', COALESCE(c.license, 'TBD'))
FROM casino_review_sections s
JOIN casinos c ON c.id = s.casino_id
WHERE s.title = 'General Information'
  AND NOT EXISTS (
      SELECT 1 FROM casino_review_points p
      WHERE p.review_section_id = s.id
        AND p.content = CONCAT('License: ', COALESCE(c.license, 'TBD'))
  );

INSERT INTO casino_review_points (review_section_id, icon, content)
SELECT
    s.id,
    'fa-credit-card text-success',
    CASE
        WHEN c.min_deposit_usd IS NULL THEN 'Minimum Deposit: TBD'
        ELSE CONCAT('Minimum Deposit: $', c.min_deposit_usd)
    END
FROM casino_review_sections s
JOIN casinos c ON c.id = s.casino_id
WHERE s.title = 'General Information'
  AND NOT EXISTS (
      SELECT 1 FROM casino_review_points p
      WHERE p.review_section_id = s.id
        AND p.content = CASE
            WHEN c.min_deposit_usd IS NULL THEN 'Minimum Deposit: TBD'
            ELSE CONCAT('Minimum Deposit: $', c.min_deposit_usd)
        END
  );

INSERT INTO casino_review_points (review_section_id, icon, content)
SELECT
    s.id,
    'fa-star text-warning',
    CONCAT('Rating: ', COALESCE(c.rating, 0), ' / 5')
FROM casino_review_sections s
JOIN casinos c ON c.id = s.casino_id
WHERE s.title = 'General Information'
  AND NOT EXISTS (
      SELECT 1 FROM casino_review_points p
      WHERE p.review_section_id = s.id
        AND p.content = CONCAT('Rating: ', COALESCE(c.rating, 0), ' / 5')
  );

INSERT INTO casino_review_points (review_section_id, icon, content)
SELECT
    s.id,
    'fa-mobile text-success',
    'Mobile: iOS and Android browsers supported.'
FROM casino_review_sections s
WHERE s.title = 'Devices'
  AND NOT EXISTS (
      SELECT 1 FROM casino_review_points p
      WHERE p.review_section_id = s.id
        AND p.content = 'Mobile: iOS and Android browsers supported.'
  );

INSERT INTO casino_review_points (review_section_id, icon, content)
SELECT
    s.id,
    'fa-desktop text-info',
    'Desktop: Chrome, Safari, and Edge supported.'
FROM casino_review_sections s
WHERE s.title = 'Devices'
  AND NOT EXISTS (
      SELECT 1 FROM casino_review_points p
      WHERE p.review_section_id = s.id
        AND p.content = 'Desktop: Chrome, Safari, and Edge supported.'
  );

INSERT INTO casino_review_points (review_section_id, icon, content)
SELECT
    s.id,
    'fa-cubes text-primary',
    'Evolution and Pragmatic Play lead the live dealer lineup.'
FROM casino_review_sections s
WHERE s.title = 'Software Providers'
  AND NOT EXISTS (
      SELECT 1 FROM casino_review_points p
      WHERE p.review_section_id = s.id
        AND p.content = 'Evolution and Pragmatic Play lead the live dealer lineup.'
  );

INSERT INTO casino_review_points (review_section_id, icon, content)
SELECT
    s.id,
    'fa-gamepad text-success',
    'Play''n GO and NetEnt fuel the slot catalogue.'
FROM casino_review_sections s
WHERE s.title = 'Software Providers'
  AND NOT EXISTS (
      SELECT 1 FROM casino_review_points p
      WHERE p.review_section_id = s.id
        AND p.content = 'Play''n GO and NetEnt fuel the slot catalogue.'
  );

INSERT INTO casino_review_points (review_section_id, icon, content)
SELECT
    s.id,
    'fa-dice text-warning',
    'New studios rotate in regularly to keep the library fresh.'
FROM casino_review_sections s
WHERE s.title = 'Software Providers'
  AND NOT EXISTS (
      SELECT 1 FROM casino_review_points p
      WHERE p.review_section_id = s.id
        AND p.content = 'New studios rotate in regularly to keep the library fresh.'
  );

INSERT INTO casino_review_points (review_section_id, icon, content)
SELECT
    s.id,
    'fa-lock text-success',
    'SSL-secured payments and data protection.'
FROM casino_review_sections s
WHERE s.title = 'Additional Info'
  AND NOT EXISTS (
      SELECT 1 FROM casino_review_points p
      WHERE p.review_section_id = s.id
        AND p.content = 'SSL-secured payments and data protection.'
  );

INSERT INTO casino_review_points (review_section_id, icon, content)
SELECT
    s.id,
    'fa-heart text-danger',
    'Responsible gaming tools and customizable limits.'
FROM casino_review_sections s
WHERE s.title = 'Additional Info'
  AND NOT EXISTS (
      SELECT 1 FROM casino_review_points p
      WHERE p.review_section_id = s.id
        AND p.content = 'Responsible gaming tools and customizable limits.'
  );

INSERT INTO casino_review_points (review_section_id, icon, content)
SELECT
    s.id,
    'fa-gift text-warning',
    'Promotions refresh regularly with seasonal campaigns.'
FROM casino_review_sections s
WHERE s.title = 'Additional Info'
  AND NOT EXISTS (
      SELECT 1 FROM casino_review_points p
      WHERE p.review_section_id = s.id
        AND p.content = 'Promotions refresh regularly with seasonal campaigns.'
  );

INSERT IGNORE INTO casino_pros_cons (casino_id, type, content)
VALUES
    ((SELECT id FROM casinos WHERE slug = 'lucky-star-crypto-casino'), 'pro', 'Crypto-friendly cashier with fast withdrawals'),
    ((SELECT id FROM casinos WHERE slug = 'lucky-star-crypto-casino'), 'pro', 'Generous VIP loyalty ladder and rakeback'),
    ((SELECT id FROM casinos WHERE slug = 'lucky-star-crypto-casino'), 'pro', 'Mobile-optimized live-dealer studios'),
    ((SELECT id FROM casinos WHERE slug = 'lucky-star-crypto-casino'), 'con', 'No dedicated mobile app'),
    ((SELECT id FROM casinos WHERE slug = 'lucky-star-crypto-casino'), 'con', 'Limited virtual reality experiences'),
    ((SELECT id FROM casinos WHERE slug = 'nova-royale-casino'), 'pro', 'Quick approvals for small deposits'),
    ((SELECT id FROM casinos WHERE slug = 'nova-royale-casino'), 'pro', 'Polished lobby with seasonal missions'),
    ((SELECT id FROM casinos WHERE slug = 'nova-royale-casino'), 'con', 'Limited crypto corridors'),
    ((SELECT id FROM casinos WHERE slug = 'nova-royale-casino'), 'con', 'Phone support only during business hours'),
    ((SELECT id FROM casinos WHERE slug = 'starlight-spins-resort'), 'pro', 'Resort-themed live dealer streams'),
    ((SELECT id FROM casinos WHERE slug = 'starlight-spins-resort'), 'pro', 'Transparent limits on regional payments'),
    ((SELECT id FROM casinos WHERE slug = 'starlight-spins-resort'), 'con', 'Higher minimum deposits for some methods'),
    ((SELECT id FROM casinos WHERE slug = 'starlight-spins-resort'), 'con', 'Limited VR support'),
    ((SELECT id FROM casinos WHERE slug = 'emerald-mirage-club'), 'pro', 'Priority cashiers for high-roller transfers'),
    ((SELECT id FROM casinos WHERE slug = 'emerald-mirage-club'), 'pro', 'Dedicated VIP hosts'),
    ((SELECT id FROM casinos WHERE slug = 'emerald-mirage-club'), 'con', 'Few low-limit table options'),
    ((SELECT id FROM casinos WHERE slug = 'emerald-mirage-club'), 'con', 'No sportsbook integration'),
    ((SELECT id FROM casinos WHERE slug = 'celestial-fortune-hall'), 'pro', 'Wide payment mix with low fees'),
    ((SELECT id FROM casinos WHERE slug = 'celestial-fortune-hall'), 'pro', '24/7 multilingual support'),
    ((SELECT id FROM casinos WHERE slug = 'celestial-fortune-hall'), 'con', 'Crash game limits can be tight'),
    ((SELECT id FROM casinos WHERE slug = 'celestial-fortune-hall'), 'con', 'Few localized promos outside peak seasons'),
    ((SELECT id FROM casinos WHERE slug = 'aurora-vault-casino'), 'pro', 'Instant crypto payouts with status tracking'),
    ((SELECT id FROM casinos WHERE slug = 'aurora-vault-casino'), 'pro', 'High-rated blackjack and roulette suite'),
    ((SELECT id FROM casinos WHERE slug = 'aurora-vault-casino'), 'con', 'Limited niche game providers'),
    ((SELECT id FROM casinos WHERE slug = 'aurora-vault-casino'), 'con', 'Weekend phone lines can be busy'),
    ((SELECT id FROM casinos WHERE slug = 'quantum-spin-lounge'), 'pro', 'Automated approvals adapt to play history'),
    ((SELECT id FROM casinos WHERE slug = 'quantum-spin-lounge'), 'pro', 'VR-friendly game shows'),
    ((SELECT id FROM casinos WHERE slug = 'quantum-spin-lounge'), 'con', 'Table limits may fluctuate during events'),
    ((SELECT id FROM casinos WHERE slug = 'quantum-spin-lounge'), 'con', 'Fewer jackpot slots than competitors'),
    ((SELECT id FROM casinos WHERE slug = 'imperial-halo-casino'), 'pro', 'Trusted banking rails with e-wallet speed'),
    ((SELECT id FROM casinos WHERE slug = 'imperial-halo-casino'), 'pro', 'Clear FAQ and escalation steps'),
    ((SELECT id FROM casinos WHERE slug = 'imperial-halo-casino'), 'con', 'Bonus rotations are less frequent'),
    ((SELECT id FROM casinos WHERE slug = 'imperial-halo-casino'), 'con', 'No VR table options'),
    ((SELECT id FROM casinos WHERE slug = 'obsidian-crown-club'), 'pro', 'High-limit cashier with concierge oversight'),
    ((SELECT id FROM casinos WHERE slug = 'obsidian-crown-club'), 'pro', 'Exclusive tables and events'),
    ((SELECT id FROM casinos WHERE slug = 'obsidian-crown-club'), 'con', 'Not many beginner-friendly stakes'),
    ((SELECT id FROM casinos WHERE slug = 'obsidian-crown-club'), 'con', 'Email replies can feel formal'),
    ((SELECT id FROM casinos WHERE slug = 'mirage-of-millions'), 'pro', 'Approachable deposit options'),
    ((SELECT id FROM casinos WHERE slug = 'mirage-of-millions'), 'pro', 'Clear timelines for approvals'),
    ((SELECT id FROM casinos WHERE slug = 'mirage-of-millions'), 'con', 'Lower rating from reviewers'),
    ((SELECT id FROM casinos WHERE slug = 'mirage-of-millions'), 'con', 'Limited VIP perks'),
    ((SELECT id FROM casinos WHERE slug = 'luminous-ledger-casino'), 'pro', 'On-chain visibility for crypto payouts'),
    ((SELECT id FROM casinos WHERE slug = 'luminous-ledger-casino'), 'pro', 'Provably fair crash titles'),
    ((SELECT id FROM casinos WHERE slug = 'luminous-ledger-casino'), 'con', 'Traditional banking options are limited'),
    ((SELECT id FROM casinos WHERE slug = 'luminous-ledger-casino'), 'con', 'Bonus terms can be data-heavy'),
    ((SELECT id FROM casinos WHERE slug = 'neon-mirage-casino'), 'pro', 'Fast-loading neon-styled lobby'),
    ((SELECT id FROM casinos WHERE slug = 'neon-mirage-casino'), 'pro', 'Live hosts during show events'),
    ((SELECT id FROM casinos WHERE slug = 'neon-mirage-casino'), 'con', 'Some payouts require extra verification during events'),
    ((SELECT id FROM casinos WHERE slug = 'neon-mirage-casino'), 'con', 'Limited high-limit tables'),
    ((SELECT id FROM casinos WHERE slug = 'azure-spire-casino'), 'pro', 'Reliable live-dealer payments'),
    ((SELECT id FROM casinos WHERE slug = 'azure-spire-casino'), 'pro', 'Support team knows live play inside out'),
    ((SELECT id FROM casinos WHERE slug = 'azure-spire-casino'), 'con', 'Few crypto options compared to peers'),
    ((SELECT id FROM casinos WHERE slug = 'azure-spire-casino'), 'con', 'Slots catalog rotates slowly'),
    ((SELECT id FROM casinos WHERE slug = 'lucky-horizon-lounge'), 'pro', 'Low deposit thresholds for quick starts'),
    ((SELECT id FROM casinos WHERE slug = 'lucky-horizon-lounge'), 'pro', 'Friendly lounge-style support'),
    ((SELECT id FROM casinos WHERE slug = 'lucky-horizon-lounge'), 'con', 'Limited VIP escalations'),
    ((SELECT id FROM casinos WHERE slug = 'lucky-horizon-lounge'), 'con', 'Fewer high-roller bonuses'),
    ((SELECT id FROM casinos WHERE slug = 'starlit-crown-casino'), 'pro', 'Predictable processing windows'),
    ((SELECT id FROM casinos WHERE slug = 'starlit-crown-casino'), 'pro', 'Responsive hosts for bonus tracking'),
    ((SELECT id FROM casinos WHERE slug = 'starlit-crown-casino'), 'con', 'Crypto corridors are still expanding'),
    ((SELECT id FROM casinos WHERE slug = 'starlit-crown-casino'), 'con', 'VR content is limited'),
    ((SELECT id FROM casinos WHERE slug = 'golden-drift-resort'), 'pro', 'Steady weekend payouts'),
    ((SELECT id FROM casinos WHERE slug = 'golden-drift-resort'), 'pro', 'Hospitality-style support coverage'),
    ((SELECT id FROM casinos WHERE slug = 'golden-drift-resort'), 'con', 'Fewer niche table variants'),
    ((SELECT id FROM casinos WHERE slug = 'golden-drift-resort'), 'con', 'Seasonal promos can sell out fast');

INSERT IGNORE INTO casino_payment_methods (casino_id, method_name, icon_key)
VALUES
    ((SELECT id FROM casinos WHERE slug = 'lucky-star-crypto-casino'), 'Bitcoin', 'logos:bitcoin'),
    ((SELECT id FROM casinos WHERE slug = 'lucky-star-crypto-casino'), 'Ethereum', 'logos:ethereum'),
    ((SELECT id FROM casinos WHERE slug = 'lucky-star-crypto-casino'), 'Tether', 'logos:tether'),
    ((SELECT id FROM casinos WHERE slug = 'lucky-star-crypto-casino'), 'Visa', 'logos:visa'),
    ((SELECT id FROM casinos WHERE slug = 'lucky-star-crypto-casino'), 'Mastercard', 'logos:mastercard'),
    ((SELECT id FROM casinos WHERE slug = 'nova-royale-casino'), 'Visa', 'logos:visa'),
    ((SELECT id FROM casinos WHERE slug = 'nova-royale-casino'), 'Mastercard', 'logos:mastercard'),
    ((SELECT id FROM casinos WHERE slug = 'nova-royale-casino'), 'PayPal', 'logos:paypal'),
    ((SELECT id FROM casinos WHERE slug = 'nova-royale-casino'), 'Skrill', 'logos:skrill'),
    ((SELECT id FROM casinos WHERE slug = 'nova-royale-casino'), 'Neteller', 'logos:neteller'),
    ((SELECT id FROM casinos WHERE slug = 'starlight-spins-resort'), 'Visa', 'logos:visa'),
    ((SELECT id FROM casinos WHERE slug = 'starlight-spins-resort'), 'Mastercard', 'logos:mastercard'),
    ((SELECT id FROM casinos WHERE slug = 'starlight-spins-resort'), 'Apple Pay', 'logos:apple-pay'),
    ((SELECT id FROM casinos WHERE slug = 'starlight-spins-resort'), 'Google Pay', 'logos:google-pay'),
    ((SELECT id FROM casinos WHERE slug = 'starlight-spins-resort'), 'PayPal', 'logos:paypal'),
    ((SELECT id FROM casinos WHERE slug = 'emerald-mirage-club'), 'Visa', 'logos:visa'),
    ((SELECT id FROM casinos WHERE slug = 'emerald-mirage-club'), 'Mastercard', 'logos:mastercard'),
    ((SELECT id FROM casinos WHERE slug = 'emerald-mirage-club'), 'Skrill', 'logos:skrill'),
    ((SELECT id FROM casinos WHERE slug = 'emerald-mirage-club'), 'Neteller', 'logos:neteller'),
    ((SELECT id FROM casinos WHERE slug = 'emerald-mirage-club'), 'PayPal', 'logos:paypal'),
    ((SELECT id FROM casinos WHERE slug = 'celestial-fortune-hall'), 'Visa', 'logos:visa'),
    ((SELECT id FROM casinos WHERE slug = 'celestial-fortune-hall'), 'Mastercard', 'logos:mastercard'),
    ((SELECT id FROM casinos WHERE slug = 'celestial-fortune-hall'), 'Bitcoin', 'logos:bitcoin'),
    ((SELECT id FROM casinos WHERE slug = 'celestial-fortune-hall'), 'Ethereum', 'logos:ethereum'),
    ((SELECT id FROM casinos WHERE slug = 'celestial-fortune-hall'), 'PayPal', 'logos:paypal'),
    ((SELECT id FROM casinos WHERE slug = 'aurora-vault-casino'), 'Visa', 'logos:visa'),
    ((SELECT id FROM casinos WHERE slug = 'aurora-vault-casino'), 'Mastercard', 'logos:mastercard'),
    ((SELECT id FROM casinos WHERE slug = 'aurora-vault-casino'), 'Bitcoin', 'logos:bitcoin'),
    ((SELECT id FROM casinos WHERE slug = 'aurora-vault-casino'), 'Ethereum', 'logos:ethereum'),
    ((SELECT id FROM casinos WHERE slug = 'aurora-vault-casino'), 'Apple Pay', 'logos:apple-pay'),
    ((SELECT id FROM casinos WHERE slug = 'quantum-spin-lounge'), 'Visa', 'logos:visa'),
    ((SELECT id FROM casinos WHERE slug = 'quantum-spin-lounge'), 'Mastercard', 'logos:mastercard'),
    ((SELECT id FROM casinos WHERE slug = 'quantum-spin-lounge'), 'Skrill', 'logos:skrill'),
    ((SELECT id FROM casinos WHERE slug = 'quantum-spin-lounge'), 'Neteller', 'logos:neteller'),
    ((SELECT id FROM casinos WHERE slug = 'quantum-spin-lounge'), 'Bitcoin', 'logos:bitcoin'),
    ((SELECT id FROM casinos WHERE slug = 'imperial-halo-casino'), 'Visa', 'logos:visa'),
    ((SELECT id FROM casinos WHERE slug = 'imperial-halo-casino'), 'Mastercard', 'logos:mastercard'),
    ((SELECT id FROM casinos WHERE slug = 'imperial-halo-casino'), 'PayPal', 'logos:paypal'),
    ((SELECT id FROM casinos WHERE slug = 'imperial-halo-casino'), 'Apple Pay', 'logos:apple-pay'),
    ((SELECT id FROM casinos WHERE slug = 'imperial-halo-casino'), 'Google Pay', 'logos:google-pay'),
    ((SELECT id FROM casinos WHERE slug = 'obsidian-crown-club'), 'Visa', 'logos:visa'),
    ((SELECT id FROM casinos WHERE slug = 'obsidian-crown-club'), 'Mastercard', 'logos:mastercard'),
    ((SELECT id FROM casinos WHERE slug = 'obsidian-crown-club'), 'Bitcoin', 'logos:bitcoin'),
    ((SELECT id FROM casinos WHERE slug = 'obsidian-crown-club'), 'Ethereum', 'logos:ethereum'),
    ((SELECT id FROM casinos WHERE slug = 'obsidian-crown-club'), 'Tether', 'logos:tether'),
    ((SELECT id FROM casinos WHERE slug = 'mirage-of-millions'), 'Visa', 'logos:visa'),
    ((SELECT id FROM casinos WHERE slug = 'mirage-of-millions'), 'Mastercard', 'logos:mastercard'),
    ((SELECT id FROM casinos WHERE slug = 'mirage-of-millions'), 'PayPal', 'logos:paypal'),
    ((SELECT id FROM casinos WHERE slug = 'mirage-of-millions'), 'Skrill', 'logos:skrill'),
    ((SELECT id FROM casinos WHERE slug = 'mirage-of-millions'), 'Neteller', 'logos:neteller'),
    ((SELECT id FROM casinos WHERE slug = 'luminous-ledger-casino'), 'Bitcoin', 'logos:bitcoin'),
    ((SELECT id FROM casinos WHERE slug = 'luminous-ledger-casino'), 'Ethereum', 'logos:ethereum'),
    ((SELECT id FROM casinos WHERE slug = 'luminous-ledger-casino'), 'Tether', 'logos:tether'),
    ((SELECT id FROM casinos WHERE slug = 'luminous-ledger-casino'), 'Visa', 'logos:visa'),
    ((SELECT id FROM casinos WHERE slug = 'luminous-ledger-casino'), 'Mastercard', 'logos:mastercard'),
    ((SELECT id FROM casinos WHERE slug = 'neon-mirage-casino'), 'Visa', 'logos:visa'),
    ((SELECT id FROM casinos WHERE slug = 'neon-mirage-casino'), 'Mastercard', 'logos:mastercard'),
    ((SELECT id FROM casinos WHERE slug = 'neon-mirage-casino'), 'Apple Pay', 'logos:apple-pay'),
    ((SELECT id FROM casinos WHERE slug = 'neon-mirage-casino'), 'Google Pay', 'logos:google-pay'),
    ((SELECT id FROM casinos WHERE slug = 'neon-mirage-casino'), 'PayPal', 'logos:paypal'),
    ((SELECT id FROM casinos WHERE slug = 'azure-spire-casino'), 'Visa', 'logos:visa'),
    ((SELECT id FROM casinos WHERE slug = 'azure-spire-casino'), 'Mastercard', 'logos:mastercard'),
    ((SELECT id FROM casinos WHERE slug = 'azure-spire-casino'), 'PayPal', 'logos:paypal'),
    ((SELECT id FROM casinos WHERE slug = 'azure-spire-casino'), 'Skrill', 'logos:skrill'),
    ((SELECT id FROM casinos WHERE slug = 'azure-spire-casino'), 'Neteller', 'logos:neteller'),
    ((SELECT id FROM casinos WHERE slug = 'lucky-horizon-lounge'), 'Visa', 'logos:visa'),
    ((SELECT id FROM casinos WHERE slug = 'lucky-horizon-lounge'), 'Mastercard', 'logos:mastercard'),
    ((SELECT id FROM casinos WHERE slug = 'lucky-horizon-lounge'), 'PayPal', 'logos:paypal'),
    ((SELECT id FROM casinos WHERE slug = 'lucky-horizon-lounge'), 'Apple Pay', 'logos:apple-pay'),
    ((SELECT id FROM casinos WHERE slug = 'lucky-horizon-lounge'), 'Google Pay', 'logos:google-pay'),
    ((SELECT id FROM casinos WHERE slug = 'starlit-crown-casino'), 'Visa', 'logos:visa'),
    ((SELECT id FROM casinos WHERE slug = 'starlit-crown-casino'), 'Mastercard', 'logos:mastercard'),
    ((SELECT id FROM casinos WHERE slug = 'starlit-crown-casino'), 'Bitcoin', 'logos:bitcoin'),
    ((SELECT id FROM casinos WHERE slug = 'starlit-crown-casino'), 'Ethereum', 'logos:ethereum'),
    ((SELECT id FROM casinos WHERE slug = 'starlit-crown-casino'), 'PayPal', 'logos:paypal'),
    ((SELECT id FROM casinos WHERE slug = 'golden-drift-resort'), 'Visa', 'logos:visa'),
    ((SELECT id FROM casinos WHERE slug = 'golden-drift-resort'), 'Mastercard', 'logos:mastercard'),
    ((SELECT id FROM casinos WHERE slug = 'golden-drift-resort'), 'PayPal', 'logos:paypal'),
    ((SELECT id FROM casinos WHERE slug = 'golden-drift-resort'), 'Skrill', 'logos:skrill'),
    ((SELECT id FROM casinos WHERE slug = 'golden-drift-resort'), 'Neteller', 'logos:neteller');

INSERT IGNORE INTO casino_highlights (casino_id, label, icon)
VALUES
    ((SELECT id FROM casinos WHERE slug = 'lucky-star-crypto-casino'), 'Casino Name: Lucky Star Entertainment Group', 'fa-building'),
    ((SELECT id FROM casinos WHERE slug = 'lucky-star-crypto-casino'), 'License: Curacao eGaming Authority', 'fa-shield-alt'),
    ((SELECT id FROM casinos WHERE slug = 'lucky-star-crypto-casino'), 'Welcome Bonus: $1,500 headline offer with blockchain payouts', 'fa-gift'),
    ((SELECT id FROM casinos WHERE slug = 'lucky-star-crypto-casino'), 'Genres: Live Casino, Crypto, Mobile friendly', 'fa-layer-group'),
    ((SELECT id FROM casinos WHERE slug = 'nova-royale-casino'), 'Casino Name: Nova Royale Casino', 'fa-building'),
    ((SELECT id FROM casinos WHERE slug = 'nova-royale-casino'), 'License: Pending independent audit', 'fa-shield-alt'),
    ((SELECT id FROM casinos WHERE slug = 'nova-royale-casino'), 'Welcome Bonus: $20 minimum deposit perks', 'fa-gift'),
    ((SELECT id FROM casinos WHERE slug = 'nova-royale-casino'), 'Genres: Live Casino, Mobile friendly', 'fa-layer-group'),
    ((SELECT id FROM casinos WHERE slug = 'starlight-spins-resort'), 'Casino Name: Starlight Spins Resort', 'fa-building'),
    ((SELECT id FROM casinos WHERE slug = 'starlight-spins-resort'), 'License: Resort compliance underway', 'fa-shield-alt'),
    ((SELECT id FROM casinos WHERE slug = 'starlight-spins-resort'), 'Welcome Bonus: Resort table boosters', 'fa-gift'),
    ((SELECT id FROM casinos WHERE slug = 'starlight-spins-resort'), 'Genres: Live Casino, VIP Club', 'fa-layer-group'),
    ((SELECT id FROM casinos WHERE slug = 'emerald-mirage-club'), 'Casino Name: Emerald Mirage Club', 'fa-building'),
    ((SELECT id FROM casinos WHERE slug = 'emerald-mirage-club'), 'License: International gaming review', 'fa-shield-alt'),
    ((SELECT id FROM casinos WHERE slug = 'emerald-mirage-club'), 'Welcome Bonus: High-roller reloads', 'fa-gift'),
    ((SELECT id FROM casinos WHERE slug = 'emerald-mirage-club'), 'Genres: Live Casino, Crypto friendly', 'fa-layer-group'),
    ((SELECT id FROM casinos WHERE slug = 'celestial-fortune-hall'), 'Casino Name: Celestial Fortune Hall', 'fa-building'),
    ((SELECT id FROM casinos WHERE slug = 'celestial-fortune-hall'), 'License: Multi-jurisdiction filing', 'fa-shield-alt'),
    ((SELECT id FROM casinos WHERE slug = 'celestial-fortune-hall'), 'Welcome Bonus: Crash game boosters', 'fa-gift'),
    ((SELECT id FROM casinos WHERE slug = 'celestial-fortune-hall'), 'Genres: Live Casino, Crash Games', 'fa-layer-group'),
    ((SELECT id FROM casinos WHERE slug = 'aurora-vault-casino'), 'Casino Name: Aurora Vault Casino', 'fa-building'),
    ((SELECT id FROM casinos WHERE slug = 'aurora-vault-casino'), 'License: Curacao review in progress', 'fa-shield-alt'),
    ((SELECT id FROM casinos WHERE slug = 'aurora-vault-casino'), 'Welcome Bonus: Premium table match offers', 'fa-gift'),
    ((SELECT id FROM casinos WHERE slug = 'aurora-vault-casino'), 'Genres: Crypto, Mobile', 'fa-layer-group'),
    ((SELECT id FROM casinos WHERE slug = 'quantum-spin-lounge'), 'Casino Name: Quantum Spin Lounge', 'fa-building'),
    ((SELECT id FROM casinos WHERE slug = 'quantum-spin-lounge'), 'License: eGaming certification submitted', 'fa-shield-alt'),
    ((SELECT id FROM casinos WHERE slug = 'quantum-spin-lounge'), 'Welcome Bonus: Lounge welcome spins', 'fa-gift'),
    ((SELECT id FROM casinos WHERE slug = 'quantum-spin-lounge'), 'Genres: Live Casino, VR-ready', 'fa-layer-group'),
    ((SELECT id FROM casinos WHERE slug = 'imperial-halo-casino'), 'Casino Name: Imperial Halo Casino', 'fa-building'),
    ((SELECT id FROM casinos WHERE slug = 'imperial-halo-casino'), 'License: EU compliance audit', 'fa-shield-alt'),
    ((SELECT id FROM casinos WHERE slug = 'imperial-halo-casino'), 'Welcome Bonus: Classic table match', 'fa-gift'),
    ((SELECT id FROM casinos WHERE slug = 'imperial-halo-casino'), 'Genres: Live Casino, VIP Club', 'fa-layer-group'),
    ((SELECT id FROM casinos WHERE slug = 'obsidian-crown-club'), 'Casino Name: Obsidian Crown Club', 'fa-building'),
    ((SELECT id FROM casinos WHERE slug = 'obsidian-crown-club'), 'License: High-roller jurisdiction review', 'fa-shield-alt'),
    ((SELECT id FROM casinos WHERE slug = 'obsidian-crown-club'), 'Welcome Bonus: VIP concierge perks', 'fa-gift'),
    ((SELECT id FROM casinos WHERE slug = 'obsidian-crown-club'), 'Genres: Crypto Casinos, High Roller', 'fa-layer-group'),
    ((SELECT id FROM casinos WHERE slug = 'mirage-of-millions'), 'Casino Name: Mirage of Millions', 'fa-building'),
    ((SELECT id FROM casinos WHERE slug = 'mirage-of-millions'), 'License: Compliance check pending', 'fa-shield-alt'),
    ((SELECT id FROM casinos WHERE slug = 'mirage-of-millions'), 'Welcome Bonus: Casual player cashback', 'fa-gift'),
    ((SELECT id FROM casinos WHERE slug = 'mirage-of-millions'), 'Genres: Live Casino, Mobile friendly', 'fa-layer-group'),
    ((SELECT id FROM casinos WHERE slug = 'luminous-ledger-casino'), 'Casino Name: Luminous Ledger Casino', 'fa-building'),
    ((SELECT id FROM casinos WHERE slug = 'luminous-ledger-casino'), 'License: Crypto fairness audits', 'fa-shield-alt'),
    ((SELECT id FROM casinos WHERE slug = 'luminous-ledger-casino'), 'Welcome Bonus: Token-based headline offer', 'fa-gift'),
    ((SELECT id FROM casinos WHERE slug = 'luminous-ledger-casino'), 'Genres: Crypto, Live Dealer', 'fa-layer-group'),
    ((SELECT id FROM casinos WHERE slug = 'neon-mirage-casino'), 'Casino Name: Neon Mirage Casino', 'fa-building'),
    ((SELECT id FROM casinos WHERE slug = 'neon-mirage-casino'), 'License: Nightlife gaming review', 'fa-shield-alt'),
    ((SELECT id FROM casinos WHERE slug = 'neon-mirage-casino'), 'Welcome Bonus: Neon-styled free spins', 'fa-gift'),
    ((SELECT id FROM casinos WHERE slug = 'neon-mirage-casino'), 'Genres: Live Casino, Mobile', 'fa-layer-group'),
    ((SELECT id FROM casinos WHERE slug = 'azure-spire-casino'), 'Casino Name: Azure Spire Casino', 'fa-building'),
    ((SELECT id FROM casinos WHERE slug = 'azure-spire-casino'), 'License: Coastal jurisdiction filing', 'fa-shield-alt'),
    ((SELECT id FROM casinos WHERE slug = 'azure-spire-casino'), 'Welcome Bonus: Live dealer reloads', 'fa-gift'),
    ((SELECT id FROM casinos WHERE slug = 'azure-spire-casino'), 'Genres: Live Casino, High Roller', 'fa-layer-group'),
    ((SELECT id FROM casinos WHERE slug = 'lucky-horizon-lounge'), 'Casino Name: Lucky Horizon Lounge', 'fa-building'),
    ((SELECT id FROM casinos WHERE slug = 'lucky-horizon-lounge'), 'License: Low-deposit market review', 'fa-shield-alt'),
    ((SELECT id FROM casinos WHERE slug = 'lucky-horizon-lounge'), 'Welcome Bonus: Low deposit double-up', 'fa-gift'),
    ((SELECT id FROM casinos WHERE slug = 'lucky-horizon-lounge'), 'Genres: Live Casino, Mobile', 'fa-layer-group'),
    ((SELECT id FROM casinos WHERE slug = 'starlit-crown-casino'), 'Casino Name: Starlit Crown Casino', 'fa-building'),
    ((SELECT id FROM casinos WHERE slug = 'starlit-crown-casino'), 'License: Crown gaming review', 'fa-shield-alt'),
    ((SELECT id FROM casinos WHERE slug = 'starlit-crown-casino'), 'Welcome Bonus: Crown free spin bundles', 'fa-gift'),
    ((SELECT id FROM casinos WHERE slug = 'starlit-crown-casino'), 'Genres: Crypto Casinos, Live Dealer', 'fa-layer-group'),
    ((SELECT id FROM casinos WHERE slug = 'golden-drift-resort'), 'Casino Name: Golden Drift Resort', 'fa-building'),
    ((SELECT id FROM casinos WHERE slug = 'golden-drift-resort'), 'License: Resort jurisdiction filing', 'fa-shield-alt'),
    ((SELECT id FROM casinos WHERE slug = 'golden-drift-resort'), 'Welcome Bonus: Beachside reloads', 'fa-gift'),
    ((SELECT id FROM casinos WHERE slug = 'golden-drift-resort'), 'Genres: Live Casino, Low Deposit', 'fa-layer-group');

INSERT IGNORE INTO providers (id, name, image_path)
VALUES
    (1, 'NetEnt', 'assets/images/providers/netent.svg'),
    (2, 'Microgaming', 'assets/images/providers/microgaming.svg'),
    (3, 'Evolution', 'assets/images/providers/evolution.svg'),
    (4, 'Play''n GO', 'assets/images/providers/playngo.svg'),
    (5, 'Pragmatic Play', 'assets/images/providers/pragmatic.svg');

INSERT IGNORE INTO payment_methods (id, name, image_path)
VALUES
    (1, 'Wire Transfer', 'assets/images/payment-methods/wire-transfer.svg'),
    (2, 'Ethereum', 'assets/images/payment-methods/ethereum.svg'),
    (3, 'Litecoin', 'assets/images/payment-methods/litecoin.svg'),
    (4, 'Dogecoin', 'assets/images/payment-methods/dogecoin.svg'),
    (5, 'Tether Wallet', 'assets/images/payment-methods/tether.svg'),
    (6, 'MiFinity', 'assets/images/payment-methods/mifinity.svg'),
    (7, 'Revolut', 'assets/images/payment-methods/revolut.svg'),
    (8, 'Apple Pay', 'assets/images/payment-methods/apple-pay.svg'),
    (9, 'Google Pay', 'assets/images/payment-methods/google-pay.svg'),
    (10, 'Binance Pay', 'assets/images/payment-methods/binance-pay.svg'),
    (11, 'Ripple', 'assets/images/payment-methods/ripple.svg');

COMMIT;
