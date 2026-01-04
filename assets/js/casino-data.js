(function () {
  const catalog = {
    "nova-royale": {
      id: "nova-royale",
      name: "Nova Royale Casino",
      cardImage: "assets/images/trending-01.jpg",
      heroImage: "assets/images/trending-01.jpg",
      minDepositLabel: "Minimum deposit $20",
      bonusHeadline: "<em>$2,000</em> $1,500 welcome bundle",
      summary:
        "Nova Royale Casino blends curated live tables with crypto-friendly banking, ensuring smooth onboarding and steady reload perks for loyal players.",
      operator: "Nova Royale Group",
      genres: "Live Casino, Crypto, Slots",
      tags: "VIP cashback, Weekly reloads, Tournaments",
      license: "Malta Gaming Authority",
      descriptionPrimary:
        "Nova Royale delivers a polished live-dealer hub with priority crypto lanes. Roulette, blackjack, and baccarat tables are paired with instant verification for rapid seating and payouts.",
      descriptionSecondary:
        "Slot fans get Megaways, feature buys, and seasonal jackpots, while sportsbook-style missions keep casual play rewarding. Two-factor security and third-party testing back every launch.",
      games: [
        { title: "Roulette Royale Live", liveDealer: true, virtualReality: false },
        { title: "Solar Spins Megaways", liveDealer: false, virtualReality: false },
        { title: "Blockchain Blackjack", liveDealer: true, virtualReality: false },
      ],
      pros: [
        "Instant crypto cashier with transparent limits",
        "Deep VIP ladder that includes weekly cashback",
        "Mobile-optimized lobby for live tables",
      ],
      cons: [
        "No fiat card processor support",
        "VR tables limited to select roulette rooms",
      ],
      ctaUrl: "https://play.novaroyale.example.com",
      rating: 5,
    },
    "starlight-spins": {
      id: "starlight-spins",
      name: "Starlight Spins Resort",
      cardImage: "assets/images/trending-02.jpg",
      heroImage: "assets/images/trending-02.jpg",
      minDepositLabel: "Minimum deposit $44",
      bonusHeadline: "<em>$1,500</em> $1,050 + 100 spins",
      summary:
        "Starlight Spins Resort focuses on feature-packed slots, pairing boosted reloads with on-demand cashout approvals for verified players.",
      operator: "Starlight Interactive",
      genres: "Slots, Crash, Cash Tournaments",
      tags: "Reload boosts, Jackpots, Cash races",
      license: "Curacao eGaming Authority",
      descriptionPrimary:
        "Seasonal slot sprints and jackpot drops headline the lobby alongside provably fair crash titles. Progressive jackpots refresh daily to keep high-volatility runs exciting.",
      descriptionSecondary:
        "Live verification unlocks same-hour withdrawals, and race leaderboards refresh in real time for both slots and instant-win games.",
      games: [
        { title: "Aurora Scatter Slots", liveDealer: false, virtualReality: false },
        { title: "Meteor Crash", liveDealer: false, virtualReality: false },
        { title: "High Roller Roulette", liveDealer: true, virtualReality: false },
      ],
      pros: [
        "Races refresh hourly with transparent prize pools",
        "Jackpot catalog highlights volatility and RTP",
        "Supports multi-wallet crypto deposits",
      ],
      cons: [
        "No weekend live chat outside VIP tier",
        "Table game catalog is smaller than slots lineup",
      ],
      ctaUrl: "https://play.starlightspins.example.com",
      rating: 4,
    },
    "emerald-mirage": {
      id: "emerald-mirage",
      name: "Emerald Mirage Club",
      cardImage: "assets/images/trending-03.jpg",
      heroImage: "assets/images/trending-03.jpg",
      minDepositLabel: "Minimum deposit $25",
      bonusHeadline: "<em>$1,000</em> $750 reload-ready offer",
      summary:
        "Emerald Mirage Club mixes classic table streams with weekly reloads and on-demand rakeback for steady grinders.",
      operator: "Emerald Interactive",
      genres: "Live Dealer, Roulette, Blackjack",
      tags: "Rakeback, Table streaks, Cashback",
      license: "Isle of Man Gambling Supervision",
      descriptionPrimary:
        "Studio-grade blackjack and roulette streams anchor the lobby, with multiple seat speeds and table limits for every bankroll.",
      descriptionSecondary:
        "Weekly reloads and rakeback tiers scale with wagering volume, while instant balance reminders keep bankroll tracking straightforward.",
      games: [
        { title: "Emerald Blackjack Live", liveDealer: true, virtualReality: false },
        { title: "Crystal Roulette", liveDealer: true, virtualReality: false },
        { title: "Mirage Slots", liveDealer: false, virtualReality: false },
      ],
      pros: [
        "Rakeback paid daily with no cap",
        "Multiple studio partners for roulette streams",
        "Clear wagering tracker in cashier",
      ],
      cons: [
        "Sports betting absent from the lobby",
        "VR support limited to demo room",
      ],
      ctaUrl: "https://play.emeraldmirage.example.com",
      rating: 4,
    },
    "celestial-fortune": {
      id: "celestial-fortune",
      name: "Celestial Fortune Hall",
      cardImage: "assets/images/trending-04.jpg",
      heroImage: "assets/images/trending-04.jpg",
      minDepositLabel: "Minimum deposit $32",
      bonusHeadline: "<em>$2,200</em> $1,650 matched bonus",
      summary:
        "Celestial Fortune Hall leans into high-limit live games, cashback guarantees, and concierge-style VIP hosts.",
      operator: "Celestial Play Ltd.",
      genres: "Live Casino, High Roller, Crypto",
      tags: "Cashback, VIP host, High limits",
      license: "Gibraltar Regulatory Authority",
      descriptionPrimary:
        "High-stakes baccarat, blackjack, and roulette streams deliver studio-quality visuals and priority seating for verified members.",
      descriptionSecondary:
        "Concierge hosts arrange boosted limits, exclusive tournaments, and personalized cashback for consistent players.",
      games: [
        { title: "Celestial Baccarat Live", liveDealer: true, virtualReality: false },
        { title: "Galactic Roulette", liveDealer: true, virtualReality: false },
        { title: "Constellation Slots", liveDealer: false, virtualReality: false },
      ],
      pros: [
        "Priority withdrawals for high-limit accounts",
        "Dedicated VIP hosts with custom perks",
        "Diverse live-dealer partners",
      ],
      cons: [
        "High minimum deposit for some promos",
        "No pooled progressive jackpots",
      ],
      ctaUrl: "https://play.celestialfortune.example.com",
      rating: 5,
    },
    "aurora-vault": {
      id: "aurora-vault",
      name: "Aurora Vault Casino",
      cardImage: "assets/images/top-game-01.jpg",
      heroImage: "assets/images/top-game-01.jpg",
      minDepositLabel: "Minimum deposit $18",
      bonusHeadline: "<em>$1,800</em> $1,250 welcome vault",
      summary:
        "Aurora Vault Casino combines fast onboarding with a balanced mix of live tables, crash titles, and jackpot slots.",
      operator: "Aurora Gaming Group",
      genres: "Live Dealer, Crash, Jackpots",
      tags: "Frictionless KYC, Fast cashouts, Jackpots",
      license: "Alderney Gambling Control Commission",
      descriptionPrimary:
        "Vaulted withdrawals and light-touch verification mean most cashouts clear in under an hour for approved payment methods.",
      descriptionSecondary:
        "Community jackpots and weekly crash ladders keep regulars engaged, with transparency on return-to-player ranges.",
      games: [
        { title: "Vault Roulette Live", liveDealer: true, virtualReality: false },
        { title: "Polar Crash", liveDealer: false, virtualReality: false },
        { title: "Northern Lights Slots", liveDealer: false, virtualReality: false },
      ],
      pros: [
        "Speedy verification for popular wallets",
        "Jackpot timers posted in the lobby",
        "Crash ladders with capped losses",
      ],
      cons: [
        "Limited video poker catalog",
        "VIP perks require manual opt-in",
      ],
      ctaUrl: "https://play.auroravault.example.com",
      rating: 5,
    },
    "quantum-spin": {
      id: "quantum-spin",
      name: "Quantum Spin Lounge",
      cardImage: "assets/images/top-game-02.jpg",
      heroImage: "assets/images/top-game-02.jpg",
      minDepositLabel: "Minimum deposit $22",
      bonusHeadline: "<em>$1,600</em> $1,100 + 80 spins",
      summary:
        "Quantum Spin Lounge specializes in feature-rich slots, multi-buy bonuses, and transparent wagering trackers.",
      operator: "Quantum Leisure",
      genres: "Slots, Feature Buy, Live Games",
      tags: "Free spins, Reload calendar, RTP insights",
      license: "Curacao eGaming Authority",
      descriptionPrimary:
        "Feature-buy slots, crash games, and jackpot drops headline the lineup, with volatility tags that help players pick sessions faster.",
      descriptionSecondary:
        "A rotating reload calendar keeps deposits fresh, and the cashier surfaces wagering progress and expiry timers in real time.",
      games: [
        { title: "Quantum Reels", liveDealer: false, virtualReality: false },
        { title: "Photon Blackjack", liveDealer: true, virtualReality: false },
        { title: "Nebula Crash", liveDealer: false, virtualReality: false },
      ],
      pros: [
        "Wagering tracker shows real-time progress",
        "Clear volatility tags on slots",
        "Crypto and fiat methods both supported",
      ],
      cons: [
        "Weekend withdrawals limited to crypto",
        "Live chat queues during peak hours",
      ],
      ctaUrl: "https://play.quantumspin.example.com",
      rating: 4,
    },
    "imperial-halo": {
      id: "imperial-halo",
      name: "Imperial Halo Casino",
      cardImage: "assets/images/top-game-03.jpg",
      heroImage: "assets/images/top-game-03.jpg",
      minDepositLabel: "Minimum deposit $28",
      bonusHeadline: "<em>$1,400</em> $950 welcome offer",
      summary:
        "Imperial Halo Casino caters to table game enthusiasts with multi-seat blackjack, roulette variants, and steady cashback.",
      operator: "Imperial Gaming Co.",
      genres: "Table Games, Live Dealer, Cashback",
      tags: "Multi-seat blackjack, Cashback, Live roulette",
      license: "Kahnawake Gaming Commission",
      descriptionPrimary:
        "Players can toggle between multiple blackjack seats or try fast-spin roulette with detailed history trackers and favorite bet saves.",
      descriptionSecondary:
        "Cashback cycles post weekly with no manual claim required, and leaderboards highlight low-edge table variants.",
      games: [
        { title: "Imperial Blackjack", liveDealer: true, virtualReality: false },
        { title: "Halo Roulette", liveDealer: true, virtualReality: false },
        { title: "Dynasty Slots", liveDealer: false, virtualReality: false },
      ],
      pros: [
        "Cashback auto-posted every Monday",
        "Seat-saver on busy blackjack tables",
        "Transparent table history stats",
      ],
      cons: [
        "No esports betting vertical",
        "Limited crash game partners",
      ],
      ctaUrl: "https://play.imperialhalo.example.com",
      rating: 3,
    },
    "obsidian-crown": {
      id: "obsidian-crown",
      name: "Obsidian Crown Club",
      cardImage: "assets/images/top-game-04.jpg",
      heroImage: "assets/images/top-game-04.jpg",
      minDepositLabel: "Minimum deposit $35",
      bonusHeadline: "<em>$2,500</em> $1,900 high-roller set",
      summary:
        "Obsidian Crown Club is built for high-rollers, offering concierge-level hosts, bespoke cashback, and elevated table limits.",
      operator: "Obsidian Entertainment",
      genres: "High Roller, Live Dealer, Crypto",
      tags: "Concierge hosts, High limits, Cashback",
      license: "Isle of Man Gambling Supervision",
      descriptionPrimary:
        "Tiered live rooms with premium dealers keep premium players engaged, while private table reservations protect seat availability.",
      descriptionSecondary:
        "Custom cashback deals, VIP racing events, and white-glove payment handling are standard for top-tier members.",
      games: [
        { title: "Crown Blackjack Elite", liveDealer: true, virtualReality: false },
        { title: "Onyx Roulette", liveDealer: true, virtualReality: false },
        { title: "Shadow Slots", liveDealer: false, virtualReality: false },
      ],
      pros: [
        "Concierge hosts with custom deals",
        "Private table reservations",
        "Priority cashouts for VIPs",
      ],
      cons: [
        "High deposits required to unlock perks",
        "No fiat bonuses beyond VIP tier",
      ],
      ctaUrl: "https://play.obsidiancrown.example.com",
      rating: 5,
    },
    "mirage-of-millions": {
      id: "mirage-of-millions",
      name: "Mirage of Millions",
      cardImage: "assets/images/top-game-05.jpg",
      heroImage: "assets/images/top-game-05.jpg",
      minDepositLabel: "Minimum deposit $15",
      bonusHeadline: "<em>$900</em> $650 + 50 spins",
      summary:
        "Mirage of Millions favors casual slot fans, delivering quick-claim spins, mini jackpots, and easy-to-read wagering terms.",
      operator: "Mirage Entertainment",
      genres: "Slots, Jackpots, Instant Wins",
      tags: "Free spins, Mini jackpots, Low wagering",
      license: "Curacao eGaming Authority",
      descriptionPrimary:
        "Micro and mini jackpots trigger frequently with transparent odds, and free-spin bundles arrive with concise requirements.",
      descriptionSecondary:
        "Cashier reminders surface expiry timers and wagering progress, keeping casual players on track for redemption.",
      games: [
        { title: "Mirage Millions Slots", liveDealer: false, virtualReality: false },
        { title: "Jackpot Avenue", liveDealer: false, virtualReality: false },
        { title: "Quick Spin Roulette", liveDealer: true, virtualReality: false },
      ],
      pros: [
        "Low wagering on most free spin offers",
        "Mini jackpots with frequent drops",
        "Clear cashier reminders for expiry",
      ],
      cons: [
        "Limited live-dealer catalog",
        "No sportsbook companion app",
      ],
      ctaUrl: "https://play.mirageofmillions.example.com",
      rating: 2,
    },
    "luminous-ledger": {
      id: "luminous-ledger",
      name: "Luminous Ledger Casino",
      cardImage: "assets/images/top-game-06.jpg",
      heroImage: "assets/images/top-game-06.jpg",
      minDepositLabel: "Minimum deposit $26",
      bonusHeadline: "<em>$1,200</em> $850 cashback starter",
      summary:
        "Luminous Ledger Casino offers ledger-style transaction tracking, pairing cashback with multi-wallet crypto support.",
      operator: "Luminous Labs",
      genres: "Crypto, Cashback, Live Dealer",
      tags: "Ledger tracking, Cashback, Multi-wallet",
      license: "Gibraltar Regulatory Authority",
      descriptionPrimary:
        "Every transaction is ledgered in real time with exportable statements, helping players reconcile bonuses and payouts easily.",
      descriptionSecondary:
        "Live tables, crash rooms, and jackpot slots are grouped by speed and volatility, making session planning straightforward.",
      games: [
        { title: "Ledger Blackjack", liveDealer: true, virtualReality: false },
        { title: "Balance Crash", liveDealer: false, virtualReality: false },
        { title: "Luminary Slots", liveDealer: false, virtualReality: false },
      ],
      pros: [
        "Detailed transaction exports",
        "Cashback credited daily",
        "Supports multiple crypto chains",
      ],
      cons: [
        "No fiat loyalty perks",
        "Limited weekend support hours",
      ],
      ctaUrl: "https://play.luminousledger.example.com",
      rating: 4,
    },
    "neon-mirage": {
      id: "neon-mirage",
      name: "Neon Mirage Casino",
      cardImage: "assets/images/categories-01.jpg",
      heroImage: "assets/images/categories-01.jpg",
      minDepositLabel: "Minimum deposit $21",
      bonusHeadline: "<em>$1,050</em> $750 welcome path",
      summary:
        "Neon Mirage Casino mixes vibrant slots with quick live tables, spotlighting free spins for new signups.",
      operator: "Neon Studios",
      genres: "Slots, Live Dealer, Crypto",
      tags: "Free spins, Fast signup, Cashback",
      license: "Malta Gaming Authority",
      descriptionPrimary:
        "Bright, fast-loading slots and casual live rooms keep the lobby nimble, while curated playlists help new players pick games.",
      descriptionSecondary:
        "Free-spin bundles rotate weekly, and cashback posts automatically when wagering thresholds are met.",
      games: [
        { title: "Neon Nights Slots", liveDealer: false, virtualReality: false },
        { title: "Mirage Roulette", liveDealer: true, virtualReality: false },
        { title: "Pulse Crash", liveDealer: false, virtualReality: false },
      ],
      pros: [
        "Quick signup with minimal steps",
        "Weekly free-spin refreshes",
        "Balanced mix of slots and live tables",
      ],
      cons: [
        "Limited VIP tiers",
        "No dedicated mobile app",
      ],
      ctaUrl: "https://play.neonmirage.example.com",
      rating: 4,
    },
    "azure-spire": {
      id: "azure-spire",
      name: "Azure Spire Casino",
      cardImage: "assets/images/categories-05.jpg",
      heroImage: "assets/images/categories-05.jpg",
      minDepositLabel: "Minimum deposit $23",
      bonusHeadline: "<em>$1,400</em> $950 + 70 spins",
      summary:
        "Azure Spire Casino emphasizes mobile-first design with crisp live streams and reward boosts for consistent weekly play.",
      operator: "Azure Gaming Network",
      genres: "Mobile, Live Dealer, Slots",
      tags: "Mobile-first, Weekly boosts, VIP Club",
      license: "Curacao eGaming Authority",
      descriptionPrimary:
        "Adaptive streaming keeps live tables smooth on any device, and mobile gestures simplify bet sizing on the go.",
      descriptionSecondary:
        "Weekly play streaks unlock extra spins and cashback, while the cashier posts wager progress in real time.",
      games: [
        { title: "Azure Blackjack Live", liveDealer: true, virtualReality: false },
        { title: "Skyline Roulette", liveDealer: true, virtualReality: false },
        { title: "Cloudburst Slots", liveDealer: false, virtualReality: false },
      ],
      pros: [
        "Responsive mobile UI for all tables",
        "Weekly streak rewards",
        "Clear wagering progress bars",
      ],
      cons: [
        "Desktop app not available",
        "No esports or sportsbook options",
      ],
      ctaUrl: "https://play.azurespire.example.com",
      rating: 4,
    },
    "lucky-horizon": {
      id: "lucky-horizon",
      name: "Lucky Horizon Lounge",
      cardImage: "assets/images/categories-03.jpg",
      heroImage: "assets/images/categories-03.jpg",
      minDepositLabel: "Minimum deposit $19",
      bonusHeadline: "<em>$1,300</em> $900 launch bonus",
      summary:
        "Lucky Horizon Lounge offers casual-friendly limits, weekend reloads, and transparent payment speeds.",
      operator: "Lucky Horizon Entertainment",
      genres: "Slots, Live Dealer, Casual Tables",
      tags: "Weekend reloads, Low limits, Fast payouts",
      license: "Gibraltar Regulatory Authority",
      descriptionPrimary:
        "Low-limit blackjack and roulette streams make it easy for casual players to join, with clear seat timers and table histories.",
      descriptionSecondary:
        "Weekend reloads come with capped wagering, and payouts show expected timelines before submission.",
      games: [
        { title: "Horizon Blackjack", liveDealer: true, virtualReality: false },
        { title: "Sunset Roulette", liveDealer: true, virtualReality: false },
        { title: "Skyline Slots", liveDealer: false, virtualReality: false },
      ],
      pros: [
        "Low minimums on live tables",
        "Weekend reloads with capped wagering",
        "Transparent payout timelines",
      ],
      cons: [
        "No VIP lounge beyond base cashback",
        "Limited jackpot slot range",
      ],
      ctaUrl: "https://play.luckyhorizon.example.com",
      rating: 4,
    },
    "starlit-crown": {
      id: "starlit-crown",
      name: "Starlit Crown Casino",
      cardImage: "assets/images/categories-04.jpg",
      heroImage: "assets/images/categories-04.jpg",
      minDepositLabel: "Minimum deposit $27",
      bonusHeadline: "<em>$1,750</em> $1,200 VIP intro",
      summary:
        "Starlit Crown Casino layers loyalty points on top of cashback, with curated live rooms and seasonal VIP quests.",
      operator: "Starlit Crown Group",
      genres: "VIP, Live Dealer, Slots",
      tags: "VIP quests, Cashback, Loyalty points",
      license: "Malta Gaming Authority",
      descriptionPrimary:
        "VIP quests rotate monthly with bespoke rewards, while loyalty points accrue across slots, live rooms, and crash games.",
      descriptionSecondary:
        "Live hosts keep premium rooms engaging, and cashback levels scale with points earned each week.",
      games: [
        { title: "Crown Roulette", liveDealer: true, virtualReality: false },
        { title: "Royal Blackjack", liveDealer: true, virtualReality: false },
        { title: "Starlit Slots", liveDealer: false, virtualReality: false },
      ],
      pros: [
        "VIP quests with rotating rewards",
        "Cashback stacked with loyalty points",
        "Premium live hosts in VIP rooms",
      ],
      cons: [
        "High play requirements for top tiers",
        "No fiat welcome bonus",
      ],
      ctaUrl: "https://play.starlitcrown.example.com",
      rating: 5,
    },
    "golden-drift": {
      id: "golden-drift",
      name: "Golden Drift Resort",
      cardImage: "assets/images/categories-05.jpg",
      heroImage: "assets/images/categories-05.jpg",
      minDepositLabel: "Minimum deposit $24",
      bonusHeadline: "<em>$1,500</em> $1,050 + 60 spins",
      summary:
        "Golden Drift Resort highlights fast withdrawals, pragmatic bonuses, and a balance of jackpot slots and live rooms.",
      operator: "Golden Drift Entertainment",
      genres: "Slots, Live Dealer, Crypto",
      tags: "Fast withdrawals, Spins, Cashback",
      license: "Curacao eGaming Authority",
      descriptionPrimary:
        "Bonus terms are summarized at checkout, with wagering caps listed before confirming the deposit.",
      descriptionSecondary:
        "Jackpot slots sit beside roulette and blackjack rooms, while cashback lands automatically for active players.",
      games: [
        { title: "Golden Roulette", liveDealer: true, virtualReality: false },
        { title: "Drift Blackjack", liveDealer: true, virtualReality: false },
        { title: "Treasure Falls", liveDealer: false, virtualReality: false },
      ],
      pros: [
        "Clear bonus summaries pre-deposit",
        "Reliable withdrawal timelines",
        "Balanced mix of slots and live games",
      ],
      cons: [
        "No crash games available",
        "Phone support not offered",
      ],
      ctaUrl: "https://play.goldendrift.example.com",
      rating: 4,
    },
  };

  function renderStarsHTML(rating) {
    const total = 5;
    const fullStars = Math.max(0, Math.min(total, Math.round(rating || 0)));
    let starsHTML = "";

    for (let i = 0; i < total; i += 1) {
      const starClass = i < fullStars ? "fa-star" : "fa-star-o";
      starsHTML += `<i class="fa ${starClass}" aria-hidden="true"></i>`;
    }

    return starsHTML;
  }

  function getDefaultCasino() {
    const firstKey = Object.keys(catalog)[0];
    return catalog[firstKey];
  }

  window.CasinoData = {
    catalog,
    getCasino(id) {
      return catalog[id];
    },
    getDefaultCasino,
    renderStarsHTML,
  };
})();
