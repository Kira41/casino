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
  const casinoDirectory = [
    {
      name: 'Lucky Star Crypto Casino',
      slug: 'lucky-star-crypto-casino',
      thumbnail: 'assets/images/trending-01.jpg',
    },
    {
      name: 'Nova Royale Casino',
      slug: 'nova-royale-casino',
      thumbnail: 'assets/images/trending-01.jpg',
    },
    {
      name: 'Starlight Spins Resort',
      slug: 'starlight-spins-resort',
      thumbnail: 'assets/images/trending-02.jpg',
    },
    {
      name: 'Emerald Mirage Club',
      slug: 'emerald-mirage-club',
      thumbnail: 'assets/images/trending-03.jpg',
    },
    {
      name: 'Celestial Fortune Hall',
      slug: 'celestial-fortune-hall',
      thumbnail: 'assets/images/trending-04.jpg',
    },
    {
      name: 'Aurora Vault Casino',
      slug: 'aurora-vault-casino',
      thumbnail: 'assets/images/top-game-01.jpg',
    },
    {
      name: 'Quantum Spin Lounge',
      slug: 'quantum-spin-lounge',
      thumbnail: 'assets/images/top-game-02.jpg',
    },
    {
      name: 'Imperial Halo Casino',
      slug: 'imperial-halo-casino',
      thumbnail: 'assets/images/top-game-03.jpg',
    },
    {
      name: 'Obsidian Crown Club',
      slug: 'obsidian-crown-club',
      thumbnail: 'assets/images/top-game-04.jpg',
    },
    {
      name: 'Mirage of Millions',
      slug: 'mirage-of-millions',
      thumbnail: 'assets/images/top-game-05.jpg',
    },
    {
      name: 'Luminous Ledger Casino',
      slug: 'luminous-ledger-casino',
      thumbnail: 'assets/images/top-game-06.jpg',
    },
    {
      name: 'Neon Mirage Casino',
      slug: 'neon-mirage-casino',
      thumbnail: 'assets/images/categories-01.jpg',
    },
    {
      name: 'Azure Spire Casino',
      slug: 'azure-spire-casino',
      thumbnail: 'assets/images/categories-05.jpg',
    },
    {
      name: 'Lucky Horizon Lounge',
      slug: 'lucky-horizon-lounge',
      thumbnail: 'assets/images/categories-03.jpg',
    },
    {
      name: 'Starlit Crown Casino',
      slug: 'starlit-crown-casino',
      thumbnail: 'assets/images/categories-04.jpg',
    },
    {
      name: 'Golden Drift Resort',
      slug: 'golden-drift-resort',
      thumbnail: 'assets/images/categories-05.jpg',
    },
  ];

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
    const card = document.createElement('div');
    card.className = 'search-result-card';

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
    const matches =
      query === ''
        ? casinoDirectory
        : casinoDirectory.filter((casino) =>
            casino.name.toLowerCase().includes(query)
          );

    searchResultsContainer.innerHTML = '';

    if (matches.length === 0) {
      const empty = document.createElement('p');
      empty.className = 'search-results-empty';
      empty.textContent =
        query === ''
          ? 'Start typing to find a casino.'
          : `No casinos found in our database matching "${term}".`;
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
})(); 
