(function () {
  function renderRating(container, label, rating) {
    if (!container || !window.CasinoData) return;
    container.innerHTML = window.CasinoData.renderStarsHTML(rating);
    container.setAttribute("aria-label", `Rating: ${rating} out of 5 stars`);

    if (label) {
      label.textContent = `${rating} / 5`;
    }
  }

  function renderGames(tbody, games = []) {
    if (!tbody) return;
    tbody.innerHTML = "";

    games.forEach((game) => {
      const row = document.createElement("tr");
      const nameCell = document.createElement("td");
      nameCell.textContent = game.title;

      const liveDealerCell = document.createElement("td");
      liveDealerCell.innerHTML = game.liveDealer
        ? '<i class="fa fa-check text-success"></i>'
        : '<i class="fa fa-times text-danger"></i>';

      const vrCell = document.createElement("td");
      vrCell.innerHTML = game.virtualReality
        ? '<i class="fa fa-check text-success"></i>'
        : '<i class="fa fa-times text-danger"></i>';

      row.appendChild(nameCell);
      row.appendChild(liveDealerCell);
      row.appendChild(vrCell);
      tbody.appendChild(row);
    });
  }

  function renderGameCount(element, games = []) {
    if (!element) return;
    const total = games.length;
    element.textContent = total ? `Show All (${total})` : "Show All Games";
  }

  function renderList(listElement, items = [], type) {
    if (!listElement) return;
    listElement.innerHTML = "";
    const isCons = type === "cons";
    const iconClass = isCons ? "fa fa-times text-danger" : "fa fa-check text-success";
    items.forEach((item) => {
      const li = document.createElement("li");
      li.innerHTML = `<i class="${iconClass}"></i><span>${item}</span>`;
      listElement.appendChild(li);
    });
  }

  function populateProductDetails(casino) {
    const nameTargets = document.querySelectorAll("[data-casino-name], [data-casino-name-display], [data-casino-breadcrumb]");
    nameTargets.forEach((el) => {
      el.textContent = casino.name;
    });

    const image = document.querySelector("[data-casino-image]");
    if (image) {
      image.setAttribute("src", casino.heroImage || casino.cardImage);
      image.setAttribute("alt", casino.name);
    }

    const offer = document.querySelector("[data-casino-offer]");
    if (offer) {
      offer.innerHTML = `<i class="fa fa-gift me-2"></i>${casino.bonusHeadline}`;
    }

    const summary = document.querySelector("[data-casino-summary]");
    if (summary) {
      summary.innerHTML = `<i class="fa fa-magic me-2 text-warning"></i>${casino.summary}`;
    }

    const operator = document.querySelector("[data-casino-operator]");
    if (operator) operator.textContent = casino.operator;

    const genres = document.querySelector("[data-casino-genres]");
    if (genres) genres.textContent = casino.genres;

    const tags = document.querySelector("[data-casino-tags]");
    if (tags) tags.textContent = casino.tags;

    const license = document.querySelector("[data-casino-license]");
    if (license) license.textContent = casino.license;

    const descriptionPrimary = document.querySelector("[data-casino-description-primary]");
    if (descriptionPrimary) {
      descriptionPrimary.innerHTML = `<i class="fa fa-dice text-warning me-2"></i>${casino.descriptionPrimary}`;
    }

    const descriptionSecondary = document.querySelector("[data-casino-description-secondary]");
    if (descriptionSecondary) {
      descriptionSecondary.textContent = casino.descriptionSecondary;
    }

    const ratingContainer = document.querySelector("[data-casino-rating-display]");
    const ratingLabel = document.querySelector("[data-casino-rating-label]");
    renderRating(ratingContainer, ratingLabel, casino.rating);

    const gamesTable = document.querySelector("[data-casino-games]");
    renderGames(gamesTable, casino.games);
    const gamesCount = document.querySelector("[data-casino-games-count]");
    renderGameCount(gamesCount, casino.games);

    const prosList = document.querySelector("[data-casino-pros]");
    renderList(prosList, casino.pros, "pros");

    const consList = document.querySelector("[data-casino-cons]");
    renderList(consList, casino.cons, "cons");

    const ctaForm = document.querySelector("[data-casino-cta]");
    const visitButton = document.querySelector("[data-casino-visit]");
    if (ctaForm && visitButton) {
      ctaForm.addEventListener("submit", (event) => {
        event.preventDefault();
        const target = casino.ctaUrl || "#";
        window.open(target, "_blank", "noopener");
      });
      visitButton.setAttribute("aria-label", `Visit ${casino.name}`);
    }
  }

  document.addEventListener("DOMContentLoaded", () => {
    if (!window.CasinoData) return;

    const params = new URLSearchParams(window.location.search);
    const casinoId = params.get("casino");

    const selected =
      window.CasinoData.getCasino(casinoId) || window.CasinoData.getDefaultCasino();

    populateProductDetails(selected);
  });
})();
