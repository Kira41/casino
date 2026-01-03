PRAGMA foreign_keys = ON;

BEGIN;

-- Core tables used by the PHP API
CREATE TABLE IF NOT EXISTS subscriptions (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    email TEXT NOT NULL UNIQUE,
    created_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS signins (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    email TEXT NOT NULL UNIQUE,
    password_hash TEXT NOT NULL,
    last_login_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP,
    created_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS contact_messages (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    name TEXT NOT NULL,
    surname TEXT NOT NULL,
    email TEXT NOT NULL,
    subject TEXT,
    message TEXT,
    created_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP
);

-- Casino content tables
CREATE TABLE IF NOT EXISTS casinos (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    slug TEXT NOT NULL UNIQUE,
    name TEXT NOT NULL,
    operator TEXT,
    license TEXT,
    headline_bonus TEXT,
    min_deposit_usd INTEGER,
    hero_image TEXT,
    thumbnail_image TEXT,
    rating INTEGER NOT NULL DEFAULT 0 CHECK (rating BETWEEN 0 AND 5),
    short_description TEXT,
    created_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS casino_tags (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    name TEXT NOT NULL,
    type TEXT NOT NULL DEFAULT 'tag',
    UNIQUE(name, type)
);

CREATE TABLE IF NOT EXISTS casino_tag_links (
    casino_id INTEGER NOT NULL,
    tag_id INTEGER NOT NULL,
    is_primary INTEGER NOT NULL DEFAULT 0 CHECK (is_primary IN (0, 1)),
    PRIMARY KEY (casino_id, tag_id),
    FOREIGN KEY (casino_id) REFERENCES casinos(id) ON DELETE CASCADE,
    FOREIGN KEY (tag_id) REFERENCES casino_tags(id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS casino_cards (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    casino_id INTEGER NOT NULL,
    section TEXT NOT NULL,
    title TEXT NOT NULL,
    image_path TEXT,
    min_deposit_label TEXT,
    rating INTEGER CHECK (rating BETWEEN 0 AND 5),
    price_label TEXT,
    position INTEGER NOT NULL DEFAULT 1,
    FOREIGN KEY (casino_id) REFERENCES casinos(id) ON DELETE CASCADE
);

CREATE INDEX IF NOT EXISTS idx_casino_cards_section_position ON casino_cards(section, position);

CREATE TABLE IF NOT EXISTS category_cards (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    title TEXT NOT NULL,
    image_path TEXT NOT NULL,
    section TEXT NOT NULL DEFAULT 'guide_category'
);

CREATE TABLE IF NOT EXISTS casino_game_modes (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    casino_id INTEGER NOT NULL,
    game_type TEXT NOT NULL,
    live_dealer_supported INTEGER NOT NULL DEFAULT 0 CHECK (live_dealer_supported IN (0, 1)),
    virtual_reality_supported INTEGER NOT NULL DEFAULT 0 CHECK (virtual_reality_supported IN (0, 1)),
    FOREIGN KEY (casino_id) REFERENCES casinos(id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS casino_review_sections (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    casino_id INTEGER NOT NULL,
    title TEXT NOT NULL,
    summary TEXT,
    FOREIGN KEY (casino_id) REFERENCES casinos(id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS casino_review_points (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    review_section_id INTEGER NOT NULL,
    icon TEXT,
    content TEXT NOT NULL,
    FOREIGN KEY (review_section_id) REFERENCES casino_review_sections(id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS casino_pros_cons (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    casino_id INTEGER NOT NULL,
    type TEXT NOT NULL CHECK (type IN ('pro', 'con')),
    content TEXT NOT NULL,
    FOREIGN KEY (casino_id) REFERENCES casinos(id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS casino_highlights (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    casino_id INTEGER NOT NULL,
    label TEXT NOT NULL,
    icon TEXT,
    FOREIGN KEY (casino_id) REFERENCES casinos(id) ON DELETE CASCADE
);

-- Seed reference data from the existing HTML content
INSERT OR IGNORE INTO casinos (slug, name, operator, license, headline_bonus, min_deposit_usd, hero_image, thumbnail_image, rating, short_description)
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
        'Crypto-first live dealer casino with blockchain payouts, curated table games, and provably fair slots.'
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
        'Flagship pick with polished lobbies, player-first promos, and a $20 minimum deposit.'
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
        'Resort-themed casino featuring quick sign-ins, curated bonuses, and mid-tier deposits.'
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
        'Club-inspired pick blending classic tables with approachable wagering for new members.'
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
        'High-rated hall with stellar promos, $32 minimum deposits, and VIP-ready support.'
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
        'Top casino draw with premium tables and a perfect score from our reviewers.'
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
        'Lounge experience with quick spins, dependable payouts, and fast onboarding.'
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
        'Reliable operator that focuses on core table games and classic slots.'
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
        'VIP-focused destination featuring high-roller tables and concierge-style support.'
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
        'Approachable option for casual players with a lighter rating from reviewers.'
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
        'Data-forward casino celebrated for transparency and strong payout history.'
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
        'Bright, modern casino featured as a related destination in the review page.'
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
        'Coastal-inspired casino listed alongside other related recommendations.'
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
        'Lounge experience for players exploring additional curated casinos.'
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
        'Boutique casino highlighted as a related option for readers.'
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
        'Resort-style casino rounding out the related destinations list.'
    );

INSERT OR IGNORE INTO casino_tags (name, type) VALUES
    ('Action', 'category'),
    ('Adventure', 'category'),
    ('Strategy', 'category'),
    ('Racing', 'category'),
    ('Live Casino', 'genre'),
    ('Crypto', 'genre'),
    ('Mobile', 'genre'),
    ('Free Spins', 'perk'),
    ('Welcome Bonus', 'perk'),
    ('VIP Club', 'perk');

INSERT OR IGNORE INTO casino_tag_links (casino_id, tag_id, is_primary)
VALUES
    ((SELECT id FROM casinos WHERE slug = 'lucky-star-crypto-casino'), (SELECT id FROM casino_tags WHERE name = 'Live Casino'), 1),
    ((SELECT id FROM casinos WHERE slug = 'lucky-star-crypto-casino'), (SELECT id FROM casino_tags WHERE name = 'Crypto'), 1),
    ((SELECT id FROM casinos WHERE slug = 'lucky-star-crypto-casino'), (SELECT id FROM casino_tags WHERE name = 'Mobile'), 0),
    ((SELECT id FROM casinos WHERE slug = 'lucky-star-crypto-casino'), (SELECT id FROM casino_tags WHERE name = 'Free Spins'), 0),
    ((SELECT id FROM casinos WHERE slug = 'lucky-star-crypto-casino'), (SELECT id FROM casino_tags WHERE name = 'Welcome Bonus'), 0),
    ((SELECT id FROM casinos WHERE slug = 'lucky-star-crypto-casino'), (SELECT id FROM casino_tags WHERE name = 'VIP Club'), 0);

INSERT OR IGNORE INTO casino_cards (casino_id, section, title, image_path, min_deposit_label, rating, price_label, position)
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

INSERT OR IGNORE INTO category_cards (title, image_path, section)
VALUES
    ('Slots & Jackpots', 'assets/images/categories-01.jpg', 'top_categories'),
    ('Live Dealer Tables', 'assets/images/categories-05.jpg', 'top_categories'),
    ('Sports Betting', 'assets/images/categories-03.jpg', 'top_categories'),
    ('VIP Programs', 'assets/images/categories-04.jpg', 'top_categories'),
    ('Crypto Casinos', 'assets/images/categories-05.jpg', 'top_categories');

INSERT OR IGNORE INTO casino_game_modes (casino_id, game_type, live_dealer_supported, virtual_reality_supported)
VALUES
    ((SELECT id FROM casinos WHERE slug = 'lucky-star-crypto-casino'), 'Roulette', 0, 0),
    ((SELECT id FROM casinos WHERE slug = 'lucky-star-crypto-casino'), 'Slots', 0, 0),
    ((SELECT id FROM casinos WHERE slug = 'lucky-star-crypto-casino'), 'Blackjack', 0, 0);

INSERT OR IGNORE INTO casino_review_sections (casino_id, title, summary)
VALUES
    ((SELECT id FROM casinos WHERE slug = 'lucky-star-crypto-casino'), 'Banking', 'Instant crypto deposits and on-chain withdrawals keep payouts transparent and quick.'),
    ((SELECT id FROM casinos WHERE slug = 'lucky-star-crypto-casino'), 'Support', 'Always-on support team ready to assist members across languages and time zones.'),
    ((SELECT id FROM casinos WHERE slug = 'lucky-star-crypto-casino'), 'Additional Info', 'Licensed operation that publishes regular payout audits for transparency.'),
    ((SELECT id FROM casinos WHERE slug = 'lucky-star-crypto-casino'), 'Available Game Types', 'Live-dealer tables, provably fair crash games, and gem-forward slot experiences.');

INSERT OR IGNORE INTO casino_review_points (review_section_id, icon, content)
VALUES
    ((SELECT id FROM casino_review_sections WHERE title = 'Banking' AND casino_id = (SELECT id FROM casinos WHERE slug = 'lucky-star-crypto-casino')), 'fa-university text-primary', 'Deposits clear instantly through popular crypto wallets to keep players moving.'),
    ((SELECT id FROM casino_review_sections WHERE title = 'Banking' AND casino_id = (SELECT id FROM casinos WHERE slug = 'lucky-star-crypto-casino')), 'fa-exchange-alt text-success', 'On-chain cashouts are verified quickly for transparent, near-instant payouts.'),
    ((SELECT id FROM casino_review_sections WHERE title = 'Support' AND casino_id = (SELECT id FROM casinos WHERE slug = 'lucky-star-crypto-casino')), 'fa-bolt text-warning', 'Lightning-fast responses that mirror the site''s payout speed.'),
    ((SELECT id FROM casino_review_sections WHERE title = 'Support' AND casino_id = (SELECT id FROM casinos WHERE slug = 'lucky-star-crypto-casino')), 'fa-headset text-info', '24/7 multilingual help desk backing the gaming studio experience.'),
    ((SELECT id FROM casino_review_sections WHERE title = 'Additional Info' AND casino_id = (SELECT id FROM casinos WHERE slug = 'lucky-star-crypto-casino')), 'fa-info-circle text-warning', 'Publishes monthly payout audits confirming the integrity of RNG-powered games.'),
    ((SELECT id FROM casino_review_sections WHERE title = 'Available Game Types' AND casino_id = (SELECT id FROM casinos WHERE slug = 'lucky-star-crypto-casino')), 'fa-chess-knight', 'Live blackjack, roulette, and baccarat streams straight from the studio.'),
    ((SELECT id FROM casino_review_sections WHERE title = 'Available Game Types' AND casino_id = (SELECT id FROM casinos WHERE slug = 'lucky-star-crypto-casino')), 'fa-random', 'Provably fair crash and plinko experiences for crypto-first players.'),
    ((SELECT id FROM casino_review_sections WHERE title = 'Available Game Types' AND casino_id = (SELECT id FROM casinos WHERE slug = 'lucky-star-crypto-casino')), 'fa-gem', 'Feature-rich video slots with bonus-buy mechanics and seasonal events.');

INSERT OR IGNORE INTO casino_pros_cons (casino_id, type, content)
VALUES
    ((SELECT id FROM casinos WHERE slug = 'lucky-star-crypto-casino'), 'pro', 'Crypto-friendly cashier with fast withdrawals'),
    ((SELECT id FROM casinos WHERE slug = 'lucky-star-crypto-casino'), 'pro', 'Generous VIP loyalty ladder and rakeback'),
    ((SELECT id FROM casinos WHERE slug = 'lucky-star-crypto-casino'), 'pro', 'Mobile-optimized live-dealer studios'),
    ((SELECT id FROM casinos WHERE slug = 'lucky-star-crypto-casino'), 'con', 'No dedicated mobile app'),
    ((SELECT id FROM casinos WHERE slug = 'lucky-star-crypto-casino'), 'con', 'Limited virtual reality experiences');

INSERT OR IGNORE INTO casino_highlights (casino_id, label, icon)
VALUES
    ((SELECT id FROM casinos WHERE slug = 'lucky-star-crypto-casino'), 'Casino Name: Lucky Star Entertainment Group', 'fa-building'),
    ((SELECT id FROM casinos WHERE slug = 'lucky-star-crypto-casino'), 'License: Curacao eGaming Authority', 'fa-shield-alt'),
    ((SELECT id FROM casinos WHERE slug = 'lucky-star-crypto-casino'), 'Welcome Bonus: $1,500 headline offer with blockchain payouts', 'fa-gift'),
    ((SELECT id FROM casinos WHERE slug = 'lucky-star-crypto-casino'), 'Genres: Live Casino, Crypto, Mobile friendly', 'fa-layer-group');

COMMIT;
