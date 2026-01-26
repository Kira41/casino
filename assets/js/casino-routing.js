(function () {
  function updateProductLinks(container, casinoId) {
    const anchors = container.querySelectorAll('a[href*="product-details.php"]');

    anchors.forEach((link) => {
      const url = new URL(link.getAttribute("href"), window.location.href);
      url.searchParams.set("casino", casinoId);
      link.setAttribute("href", `${url.pathname}?${url.searchParams.toString()}`);
    });
  }

  function renderRating(container, rating) {
    if (!container || !window.CasinoData) return;

    container.innerHTML = window.CasinoData.renderStarsHTML(rating);
    container.setAttribute("aria-label", `Rating: ${rating} out of 5 stars`);
  }

  function hydrateCard(card, casino) {
    if (!card || !casino) return;

    const image = card.querySelector("[data-casino-card-image]");
    if (image) {
      image.setAttribute("src", casino.cardImage || casino.heroImage);
      image.setAttribute("alt", casino.name);
    }

    const name = card.querySelector("[data-casino-card-name]");
    if (name) {
      name.textContent = casino.name;
    }

    const price = card.querySelector("[data-casino-card-offer]");
    if (price && casino.minDepositLabel) {
      price.textContent = casino.minDepositLabel;
    }

    const ratingContainer = card.querySelector("[data-casino-rating]");
    renderRating(ratingContainer, casino.rating);

    updateProductLinks(card, casino.id);
  }

  document.addEventListener("DOMContentLoaded", () => {
    if (!window.CasinoData || !window.CasinoData.catalog) return;

    document.querySelectorAll("[data-casino-id]").forEach((card) => {
      const casinoId = card.getAttribute("data-casino-id");
      const casino = window.CasinoData.getCasino(casinoId);
      if (!casino) return;
      hydrateCard(card, casino);
    });
  });
})();
