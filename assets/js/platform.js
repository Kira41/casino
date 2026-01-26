(function () {
  const API_ENDPOINT = 'api.php';
  const now = new Date();
  const year = now.getFullYear();
  const monthName = now.toLocaleString('default', { month: 'long' });

  document.querySelectorAll('[data-current-year]').forEach((element) => {
    element.textContent = year;
  });

  const topPicksHeading = document.querySelector('[data-top-picks-heading]');
  const topPicksSubtitle = document.querySelector('[data-top-picks-subtitle]');
  let defaultTopPicksHeading = topPicksHeading?.textContent || '';
  const defaultTopPicksSubtitle = topPicksSubtitle?.textContent || '';

  if (topPicksHeading) {
    defaultTopPicksHeading = `Top Picks for ${monthName} ${year}`;
    topPicksHeading.textContent = defaultTopPicksHeading;
  }

  const subscribeModalEl = document.getElementById('subscribeSuccessModal');
  const subscribeModal =
    subscribeModalEl && typeof bootstrap !== 'undefined'
      ? new bootstrap.Modal(subscribeModalEl)
      : null;

  function setStatusText(target, message, type = 'info') {
    if (!target) return;
    target.textContent = message;
    target.classList.remove('text-danger', 'text-success', 'fw-semibold');
    if (type === 'success') {
      target.classList.add('text-success', 'fw-semibold');
    } else if (type === 'error') {
      target.classList.add('text-danger', 'fw-semibold');
    }
  }

  async function postToApi(action, payload) {
    const response = await fetch(API_ENDPOINT, {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ action, ...payload }),
    });

    let data = null;
    try {
      data = await response.json();
    } catch (error) {
      throw new Error('The server returned an unreadable response.');
    }

    if (!response.ok || !data || data.success !== true) {
      const message =
        (data && data.message) ||
        'The request could not be completed. Please try again.';
      throw new Error(message);
    }

    return data;
  }

  document.querySelectorAll('[data-subscribe-form]').forEach((form) => {
    form.addEventListener('submit', async (event) => {
      event.preventDefault();

      const emailInput = form.querySelector('input[type="email"]');
      const status = form.parentElement?.querySelector('[data-subscribe-status]');
      const email = emailInput ? emailInput.value.trim() : '';

      setStatusText(status, 'Saving your subscription...');

      try {
        const result = await postToApi('subscribe', { email });
        setStatusText(status, result.message || 'Subscription saved.', 'success');
        form.reset();
        if (subscribeModal) {
          subscribeModal.show();
        }
      } catch (error) {
        setStatusText(status, error.message, 'error');
      }
    });
  });

  document.querySelectorAll('[data-open-subscribe]').forEach((trigger) => {
    trigger.addEventListener('click', (event) => {
      event.preventDefault();
      const subscribeSection = document.getElementById('subscribe-now');
      if (subscribeSection) {
        subscribeSection.scrollIntoView({ behavior: 'smooth', block: 'start' });
        return;
      }
      if (subscribeModal) {
        subscribeModal.show();
      }
    });
  });

  const signInForm = document.getElementById('signInForm');
  if (signInForm) {
    signInForm.addEventListener('submit', async (event) => {
      event.preventDefault();
      const status = document.querySelector('[data-signin-status]');
      const email = signInForm.querySelector('#signInEmail')?.value.trim() || '';
      const password =
        signInForm.querySelector('#signInPassword')?.value.trim() || '';

      setStatusText(status, 'Signing in...');

      try {
        const result = await postToApi('signin', { email, password });
        setStatusText(status, result.message, 'success');
        signInForm.reset();

        const modalEl = document.getElementById('signInModal');
        if (modalEl && typeof bootstrap !== 'undefined') {
          const modalInstance = bootstrap.Modal.getInstance(modalEl);
          if (modalInstance) {
            setTimeout(() => modalInstance.hide(), 900);
          }
        }
      } catch (error) {
        setStatusText(status, error.message, 'error');
      }
    });
  }

  const contactForm = document.querySelector('[data-contact-form]');
  if (contactForm) {
    contactForm.addEventListener('submit', async (event) => {
      event.preventDefault();
      const status = contactForm.querySelector('[data-contact-status]');
      const name = contactForm.querySelector('#name')?.value.trim() || '';
      const surname = contactForm.querySelector('#surname')?.value.trim() || '';
      const email = contactForm.querySelector('#email')?.value.trim() || '';
      const subject = contactForm.querySelector('#subject')?.value.trim() || '';
      const message = contactForm.querySelector('#message')?.value.trim() || '';

      setStatusText(status, 'Sending your message...', 'info');

      try {
        const result = await postToApi('contact', {
          name,
          surname,
          email,
          subject,
          message,
        });
        setStatusText(status, result.message, 'success');
        contactForm.reset();
      } catch (error) {
        setStatusText(status, error.message, 'error');
      }
    });
  }

  const searchForm = document.querySelector('[data-casino-search-form]');
  const searchInput = document.querySelector('[data-casino-search]');
  const searchResultsContainer = document.querySelector('[data-search-results]');
  const casinoCards = document.querySelectorAll('.trending .item');
  const casinoDirectory = (typeof window !== 'undefined' && window.__CASINO_DIRECTORY__) || [];
  const casinoProfiles = {
    'lucky-star-crypto-casino': {
      name: 'Lucky Star Crypto Casino',
      heroImage: 'assets/images/single-game.jpg',
      offerHighlight: '$2,000',
      offer: '$1,500 Welcome Bonus',
      url: 'https://luckystar.example.com',
      summary:
        'Lucky Star Crypto Casino blends a sleek live-dealer experience with lightning-fast blockchain payments. Explore a curated lobby of roulette, blackjack, and immersive slots backed by provably fair technology and generous promotions tailored for crypto enthusiasts.',
      operator: 'Lucky Star Entertainment Group',
      genres: ['Live Casino', 'Crypto', 'Mobile'],
      tags: ['Free Spins', 'Welcome Bonus', 'VIP Club'],
      license: 'Curacao eGaming Authority',
      descriptions: {
        primary:
          'Lucky Star Crypto Casino delivers a polished, mobile-first lobby that supports instant deposits with Bitcoin, Ethereum, and other major tokens. Players can jump into streaming live-dealer tables hosted from a modern studio while unlocking tiered rewards that scale with wagering activity.',
        secondary:
          'The casino’s promotion calendar features rotating free-spin bundles, reload boosts, and weekly rakeback. Security remains a priority thanks to two-factor authentication, cold-storage reserves, and independent game testing that keeps blackjack, roulette, and crash titles provably fair.',
      },
      gameTypes: [
        { name: 'Roulette', liveDealer: true, virtualReality: false },
        { name: 'Slots', liveDealer: false, virtualReality: false },
        { name: 'Blackjack', liveDealer: true, virtualReality: false },
      ],
      pros: [
        { icon: 'bolt', text: 'Crypto-friendly cashier with fast withdrawals' },
        { icon: 'crown', text: 'Generous VIP loyalty ladder and rakeback' },
        { icon: 'mobile-alt', text: 'Mobile-optimized live-dealer studios' },
      ],
      cons: [
        { icon: 'ban', text: 'No dedicated mobile app' },
        { icon: 'vr-cardboard', text: 'Limited virtual reality experiences' },
      ],
    },
    'nova-royale-casino': {
      name: 'Nova Royale Casino',
      heroImage: 'assets/images/trending-01.jpg',
      offerHighlight: '$3,000',
      offer: '$2,500 Match Bonus + 150 Spins',
      url: 'https://novaroyale.example.com',
      summary:
        'Nova Royale Casino highlights cinematic slots, crisp live tables, and generous welcome boosts tuned for high-rollers.',
      operator: 'Nova Royale Gaming Ltd.',
      genres: ['Slots', 'Live Dealer', 'VIP'],
      tags: ['High Roller', 'Cashback', 'Free Spins'],
      license: 'MGA License',
      descriptions: {
        primary:
          'Nova Royale Casino puts polished, cinematic slots front and center with live dealers streaming from luxury studios.',
        secondary:
          'VIP members unlock cashback milestones and concierge banking, while new players enjoy boosted match bonuses and curated tournaments.',
      },
      gameTypes: [
        { name: 'Roulette', liveDealer: true, virtualReality: false },
        { name: 'Slots', liveDealer: false, virtualReality: false },
        { name: 'Blackjack', liveDealer: true, virtualReality: false },
      ],
      pros: [
        { icon: 'gift', text: 'Large matched deposit for new sign-ups' },
        { icon: 'headset', text: '24/7 concierge live chat support' },
        { icon: 'shield-alt', text: 'MGA-licensed with routine audits' },
      ],
      cons: [
        { icon: 'clock', text: 'Weekly withdrawal limits for new players' },
        { icon: 'gamepad', text: 'Limited crash game selection' },
      ],
    },
    'starlight-spins-resort': {
      name: 'Starlight Spins Resort',
      heroImage: 'assets/images/trending-02.jpg',
      offerHighlight: '$1,000',
      offer: '$750 Welcome Bundle + Cashback',
      url: 'https://starlightspins.example.com',
      summary:
        'Starlight Spins Resort favors fast payouts and seasonal leaderboard races for slot and roulette fans.',
      operator: 'Starlight Resorts Ltd.',
      genres: ['Slots', 'Table Games', 'Crypto'],
      tags: ['Cashback', 'Fast Payout', 'VIP Ladder'],
      license: 'Isle of Man License',
      descriptions: {
        primary:
          'Starlight Spins Resort curates quick-withdrawal tables and seasonal slot races with transparent prize pools.',
        secondary:
          'Crypto and fiat players both benefit from instant deposits, weekly rakeback, and rotating cashback coupons.',
      },
      gameTypes: [
        { name: 'Roulette', liveDealer: true, virtualReality: false },
        { name: 'Slots', liveDealer: false, virtualReality: false },
        { name: 'Baccarat', liveDealer: true, virtualReality: false },
      ],
      pros: [
        { icon: 'bolt', text: 'Lightning-fast payout queue' },
        { icon: 'trophy', text: 'Seasonal leaderboard races' },
        { icon: 'bitcoin', text: 'Crypto and fiat cashiers supported' },
      ],
      cons: [
        { icon: 'ban', text: 'No dedicated sportsbook' },
        { icon: 'mobile-alt', text: 'Limited native mobile app features' },
      ],
    },
    'emerald-mirage-club': {
      name: 'Emerald Mirage Club',
      heroImage: 'assets/images/trending-03.jpg',
      offerHighlight: '$2,200',
      offer: '$1,800 Bonus + 100 Spins',
      url: 'https://emeraldmirage.example.com',
      summary:
        'Emerald Mirage Club blends lush aesthetics with table-heavy lobbies and tiered loyalty perks.',
      operator: 'Emerald Entertainment Group',
      genres: ['Live Casino', 'Table Games', 'VIP'],
      tags: ['VIP Club', 'Free Spins', 'Cashback'],
      license: 'Gibraltar License',
      descriptions: {
        primary:
          'Emerald Mirage Club leans into table game depth with premium roulette and blackjack studios.',
        secondary:
          'Tiered loyalty perks deliver cashback, bespoke promos, and dedicated hosts for top-tier players.',
      },
      gameTypes: [
        { name: 'Roulette', liveDealer: true, virtualReality: false },
        { name: 'Blackjack', liveDealer: true, virtualReality: false },
        { name: 'Poker', liveDealer: true, virtualReality: false },
      ],
      pros: [
        { icon: 'crown', text: 'VIP hosts for top loyalty tiers' },
        { icon: 'gift', text: 'Stacked welcome + reload bonuses' },
        { icon: 'users', text: 'Busy live-dealer lobbies' },
      ],
      cons: [
        { icon: 'clock', text: 'Weekend withdrawals slower than weekday' },
        { icon: 'globe', text: 'Some regional restrictions apply' },
      ],
    },
    'celestial-fortune-hall': {
      name: 'Celestial Fortune Hall',
      heroImage: 'assets/images/trending-04.jpg',
      offerHighlight: '$1,200',
      offer: '$900 Welcome Bonus + 80 Spins',
      url: 'https://celestialfortune.example.com',
      summary:
        'Celestial Fortune Hall focuses on mobile-first design with a balanced mix of live tables and jackpots.',
      operator: 'Celestial Gaming Group',
      genres: ['Mobile', 'Live Dealer', 'Jackpots'],
      tags: ['Jackpots', 'Mobile First', 'Cashback'],
      license: 'Curacao eGaming Authority',
      descriptions: {
        primary:
          'Celestial Fortune Hall offers crisp mobile gameplay with responsive live studios and progressive jackpots.',
        secondary:
          'Regular cashback, mobile-exclusive promos, and jackpot spotlights keep returning players engaged.',
      },
      gameTypes: [
        { name: 'Roulette', liveDealer: true, virtualReality: false },
        { name: 'Slots', liveDealer: false, virtualReality: false },
        { name: 'Blackjack', liveDealer: true, virtualReality: false },
      ],
      pros: [
        { icon: 'mobile-alt', text: 'Mobile-first live lobby design' },
        { icon: 'gem', text: 'Multiple progressive jackpot picks' },
        { icon: 'bolt', text: 'Frequent cashback boosts' },
      ],
      cons: [
        { icon: 'ban', text: 'VIP program invite-only' },
        { icon: 'ticket-alt', text: 'Limited tournament schedule' },
      ],
    },
  };

  function slugifyCasinoName(name) {
    return name
      .toLowerCase()
      .replace(/[^a-z0-9]+/g, '-')
      .replace(/(^-|-$)+/g, '');
  }

  function updateTopPicksHeading(isSearching) {
    if (isSearching) {
      if (topPicksSubtitle) topPicksSubtitle.textContent = 'search result';
      if (topPicksHeading) topPicksHeading.textContent = 'search result';
      return;
    }

    if (topPicksSubtitle) {
      topPicksSubtitle.textContent =
        defaultTopPicksSubtitle || 'Hot Picks';
    }

    if (topPicksHeading) {
      topPicksHeading.textContent =
        defaultTopPicksHeading || `Top Picks for ${monthName} ${year}`;
    }
  }

  function filterTopPickCards(term) {
    const query = term.trim().toLowerCase();

    casinoCards.forEach((card) => {
      const textContent = card.textContent.toLowerCase();
      const column = card.closest('[class*="col-"]');
      if (!column) return;
      column.style.display =
        query === '' || textContent.includes(query) ? '' : 'none';
    });
  }

  function buildResultCard(casino) {
    const slug = casino.slug || slugifyCasinoName(casino.name || '');
    const casinoId = casino.id ? String(casino.id) : '';
    const card = document.createElement('a');
    card.className = 'search-result-card';
    const casinoParam = casinoId || slug;
    card.href = `product-details.php?casino=${encodeURIComponent(casinoParam)}`;
    if (casinoId) {
      card.setAttribute('data-casino-id', casinoId);
    }
    card.setAttribute('data-casino-slug', slug);

    const thumb = document.createElement('div');
    thumb.className = 'search-result-thumb';

    const image = document.createElement('img');
    image.src = casino.thumbnail;
    image.alt = `${casino.name} logo`;
    thumb.appendChild(image);

    const name = document.createElement('span');
    name.className = 'search-result-name';
    name.textContent = casino.name;

    card.append(thumb, name);
    return card;
  }

  function renderSearchResults(term) {
    if (!searchResultsContainer) return;

    const query = term.trim().toLowerCase();
    searchResultsContainer.innerHTML = '';

    if (query === '') {
      const empty = document.createElement('p');
      empty.className = 'search-results-empty';
      empty.textContent = 'Start typing to find a casino.';
      searchResultsContainer.appendChild(empty);
      return;
    }

    const matches = casinoDirectory.filter((casino) =>
      casino.name.toLowerCase().includes(query)
    );

    if (matches.length === 0) {
      const empty = document.createElement('p');
      empty.className = 'search-results-empty';
      empty.textContent = `No casinos found in our database matching "${term}".`;
      searchResultsContainer.appendChild(empty);
      return;
    }

    const grid = document.createElement('div');
    grid.className = 'search-results-grid';
    matches.forEach((casino) => grid.appendChild(buildResultCard(casino)));
    searchResultsContainer.appendChild(grid);
  }

  function handleSearch(term) {
    const trimmedTerm = term.trim();
    updateTopPicksHeading(trimmedTerm !== '');
    filterTopPickCards(trimmedTerm);
    renderSearchResults(trimmedTerm);
  }

  function navigateToCasinoDetail(slug, baseHref = 'product-details.php') {
    const selectedSlug = slug || 'lucky-star-crypto-casino';
    const url = new URL(baseHref, window.location.href);
    url.searchParams.set('casino', selectedSlug);
    sessionStorage.setItem('selectedCasino', selectedSlug);
    window.location.href = url.toString();
  }

  if (searchForm && searchInput) {
    searchForm.addEventListener('submit', (event) => {
      event.preventDefault();
      handleSearch(searchInput.value || '');
    });

    searchInput.addEventListener('input', () => {
      handleSearch(searchInput.value || '');
    });

    handleSearch(searchInput.value || '');
  }

  if (searchResultsContainer) {
    searchResultsContainer.addEventListener('click', (event) => {
      const card = event.target.closest('[data-casino-slug]');
      if (!card) return;
      const casinoId = card.getAttribute('data-casino-id');
      const casinoSlug = card.getAttribute('data-casino-slug');
      navigateToCasinoDetail(casinoId || casinoSlug);
    });
  }

  function attachCasinoLinkHandlers() {
    const productLinks = document.querySelectorAll(
      'a[href$="product-details.php"]'
    );

    productLinks.forEach((link) => {
      link.addEventListener('click', (event) => {
        const container = link.closest('.item');
        const heading = container?.querySelector('h4');
        const name = heading?.textContent?.trim();
        const slug = name ? slugifyCasinoName(name) : 'lucky-star-crypto-casino';

        event.preventDefault();
        navigateToCasinoDetail(slug, link.href);
      });
    });
  }

  function getSelectedCasinoSlug() {
    const params = new URLSearchParams(window.location.search);
    const urlSlug = params.get('casino') || params.get('slug');
    return (
      urlSlug ||
      sessionStorage.getItem('selectedCasino') ||
      'lucky-star-crypto-casino'
    );
  }

  function setTextContent(selector, value) {
    const element = document.querySelector(selector);
    if (!element || typeof value !== 'string') return;
    element.textContent = value;
  }

  function setInnerHtml(selector, value) {
    const element = document.querySelector(selector);
    if (!element || typeof value !== 'string') return;
    element.innerHTML = value;
  }

  function buildIcon(state) {
    const icon = document.createElement('i');
    icon.className = `fa fa-${state ? 'check text-success' : 'times text-danger'}`;
    return icon;
  }

  function populateGameTable(games) {
    const tableBody = document.querySelector('[data-casino-games]');
    if (!tableBody || !Array.isArray(games)) return;
    tableBody.innerHTML = '';

    games.forEach((game) => {
      const row = document.createElement('tr');
      const nameCell = document.createElement('td');
      nameCell.textContent = game.name;

      const liveCell = document.createElement('td');
      liveCell.appendChild(buildIcon(Boolean(game.liveDealer)));

      const vrCell = document.createElement('td');
      vrCell.appendChild(buildIcon(Boolean(game.virtualReality)));

      row.append(nameCell, liveCell, vrCell);
      tableBody.appendChild(row);
    });
  }

  function populateFeatureList(selector, items) {
    const list = document.querySelector(selector);
    if (!list || !Array.isArray(items)) return;
    list.innerHTML = '';
    items.forEach((item) => {
      const li = document.createElement('li');
      const icon = document.createElement('i');
      icon.className = `fa fa-${item.icon || 'check'}`;
      const span = document.createElement('span');
      span.textContent = item.text;
      li.append(icon, span);
      list.appendChild(li);
    });
  }

  function populateCasinoDetails() {
    const detailRoot = document.querySelector('[data-casino-name]');
    if (!detailRoot) return;

    const slug = getSelectedCasinoSlug();
    const profile =
      casinoProfiles[slug] || casinoProfiles['lucky-star-crypto-casino'];

    if (!profile) return;

    sessionStorage.setItem('selectedCasino', slug);

    setTextContent('[data-casino-name]', profile.name);
    setTextContent('[data-casino-breadcrumb]', profile.name);
    setTextContent('[data-casino-name-display]', profile.name);

    const offerText = `${profile.offerHighlight ? `<em>${profile.offerHighlight}</em> ` : ''}${profile.offer}`;
    setInnerHtml(
      '[data-casino-offer]',
      `<i class="fa fa-gift me-2"></i>${offerText}`
    );

    setInnerHtml(
      '[data-casino-summary]',
      `<i class="fa fa-magic me-2 text-warning"></i>${profile.summary}`
    );
    setTextContent('[data-casino-operator]', profile.operator);
    setTextContent('[data-casino-genres]', profile.genres?.join(', '));
    setTextContent('[data-casino-tags]', profile.tags?.join(', '));
    setTextContent('[data-casino-license]', profile.license);
    setInnerHtml(
      '[data-casino-description-primary]',
      `<i class="fa fa-dice text-warning me-2"></i>${profile.descriptions?.primary || ''}`
    );
    setTextContent(
      '[data-casino-description-secondary]',
      profile.descriptions?.secondary || ''
    );

    const image = document.querySelector('[data-casino-image]');
    if (image) {
      image.src = profile.heroImage;
      image.alt = `${profile.name} hero image`;
    }

    populateGameTable(profile.gameTypes);
    populateFeatureList('[data-casino-pros]', profile.pros);
    populateFeatureList('[data-casino-cons]', profile.cons);

    const visitButton = document.querySelector('[data-casino-visit]');
    const visitForm = document.querySelector('[data-casino-cta]');
    if (visitButton && visitForm) {
      visitForm.addEventListener('submit', (event) => event.preventDefault());
      visitButton.addEventListener('click', (event) => {
        event.preventDefault();
        if (profile.url) {
          window.location.href = profile.url;
        }
      });
      visitButton.innerHTML = `<i class="fa fa-arrow-up-right-from-square"></i> Visit ${profile.name}`;
    }

    if (profile.name) {
      document.title = `${profile.name} - Product Detail`;
    }
  }

  attachCasinoLinkHandlers();
  populateCasinoDetails();
})(); 
