(function() {
  const now = new Date();
  const year = now.getFullYear();
  const monthName = now.toLocaleString('default', { month: 'long' });

  document.querySelectorAll('[data-current-year]').forEach((element) => {
    element.textContent = year;
  });

  const topPicksHeading = document.querySelector('[data-top-picks-heading]');
  if (topPicksHeading) {
    topPicksHeading.textContent = `Top Picks for ${monthName} ${year}`;
  }

  const subscribeModalEl = document.getElementById('subscribeSuccessModal');
  const subscribeModal =
    subscribeModalEl && typeof bootstrap !== 'undefined'
      ? new bootstrap.Modal(subscribeModalEl)
      : null;

  document.querySelectorAll('[data-subscribe-form]').forEach((form) => {
    form.addEventListener('submit', (event) => {
      event.preventDefault();
      if (subscribeModal) {
        subscribeModal.show();
      }
      form.reset();
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
    signInForm.addEventListener('submit', (event) => {
      event.preventDefault();
      const status = document.querySelector('[data-signin-status]');
      if (status) {
        status.textContent =
          'Signed in. Platform access now uses our MySQL-backed insights for personalized casino picks.';
        status.classList.remove('text-danger');
        status.classList.add('text-success', 'fw-semibold');
      }
      signInForm.reset();

      const modalEl = document.getElementById('signInModal');
      if (modalEl && typeof bootstrap !== 'undefined') {
        const modalInstance = bootstrap.Modal.getInstance(modalEl);
        if (modalInstance) {
          setTimeout(() => modalInstance.hide(), 900);
        }
      }
    });
  }
})();
