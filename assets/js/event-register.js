document.addEventListener('DOMContentLoaded', () => {
  const form = document.querySelector('#em-event-register-form');
  if (!form || typeof EM_AJAX === 'undefined') return;

  const messageEl = form.querySelector('.em-message');

  const setMessage = (text, type = '') => {
    if (!messageEl) return;
    messageEl.textContent = text;
    messageEl.classList.remove('success', 'error');
    if (type) messageEl.classList.add(type);
  };

  form.addEventListener('submit', async (event) => {
    event.preventDefault();

    const nameInput = form.querySelector('input[name="name"]');
    const emailInput = form.querySelector('input[name="email"]');
    const eventId = parseInt(form.dataset.eventId, 10);

    const name = nameInput?.value.trim() || '';
    const email = emailInput?.value.trim() || '';

    // `required` w HTML zwykle blokuje submit po stronie przeglądarki.
    // Ten fallback zostawiam na wypadek usunięcia `required`.
    if (!name || !email || !Number.isInteger(eventId) || eventId <= 0) {
      setMessage('Please fill in all required fields.', 'error');
      return;
    }

    setMessage('');
    form.classList.add('is-loading');

    const body = new URLSearchParams({
      action: 'register_event',
      nonce: EM_AJAX.nonce,
      event_id: String(eventId),
      name,
      email
    });

    try {
      const response = await fetch(EM_AJAX.ajax_url, {
        method: 'POST',
        headers: {
          'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8'
        },
        body: body.toString()
      });

      let data;
      try {
        data = await response.json();
      } catch {
        setMessage('Unexpected server response.', 'error');
        return;
      }
      
      if (data.success) {
        setMessage(data.data?.message || 'Successfully registered!', 'success');

        if (typeof data.data?.current_count !== 'undefined') {
          const root = form.closest('.em-single-event');
          const counter = root?.querySelector('.em-current-count');
          const spotsLeft = root?.querySelector('.em-spots-left');
          
          if (counter) counter.textContent = String(data.data.current_count);
          if (spotsLeft) spotsLeft.textContent = String(data.data.limit - data.data.current_count);
        }

        form.reset();
      } else {
        setMessage(data.data?.message || 'Something went wrong.', 'error');
      }
    } catch (err) {
      console.error(err);
      setMessage('Server error. Please try again later.', 'error');
    } finally {
      form.classList.remove('is-loading');
    }
  });
});
