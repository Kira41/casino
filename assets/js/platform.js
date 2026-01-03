(function () {
  const API_ENDPOINT = 'api.php';
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

      setStatusText(status, 'Saving your subscription with our SQL backend...');

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

      setStatusText(status, 'Signing in with the PHP + SQL service...');

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

      setStatusText(status, 'Sending to the SQL inbox...', 'info');

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
})(); 
