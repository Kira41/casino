(function () {
  const runtimeCatalog = typeof window !== 'undefined' && window.__CASINO_DATA__
    ? window.__CASINO_DATA__
    : null;

  const catalog = runtimeCatalog && typeof runtimeCatalog === 'object'
    ? runtimeCatalog
    : {};

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
    const keys = Object.keys(catalog);
    if (keys.length === 0) return null;
    return catalog[keys[0]];
  }

  window.CasinoData = {
    catalog,
    getCasino(id) {
      if (!id) return null;
      return catalog[id] || null;
    },
    getDefaultCasino() {
      return getDefaultCasino() || {};
    },
    renderStarsHTML,
  };
})();
