document.addEventListener('DOMContentLoaded', () => {

  // Obsługa wielu shortcode [em_event_search] na jednej stronie
  const blocks = document.querySelectorAll('[data-em-search]');
  if (!blocks.length || typeof EM_AJAX === 'undefined') return;

  blocks.forEach((block) => {
    const form = block.querySelector('.em-event-search-form');
    const resultsEl = block.querySelector('.em-event-search-results');
    if (!form || !resultsEl) return;

    const render = (html) => {
      resultsEl.innerHTML = html || '<p>No results.</p>';
    };

    form.addEventListener('submit', async (e) => {
      e.preventDefault();

      const fd = new FormData(form);

      const body = new URLSearchParams({
        action: 'em_search_events',
        nonce: EM_AJAX.nonce,
        city: String(fd.get('city') || ''),
        date_from: String(fd.get('date_from') || ''),
        date_to: String(fd.get('date_to') || '')
      });

      try {
        const res = await fetch(EM_AJAX.ajax_url, {
          method: 'POST',
          headers: { 'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8' },
          body: body.toString()
        });

        if (!res.ok) {
          render('<p class="em-message error">Request failed.</p>');
          return;
        }

        // Obsługa błędu parsowania
        let data;
        try {
          data = await res.json();
        } catch {
          render('<p class="em-message error">Unexpected server response.</p>');
          return;
        }

        if (!data.success) {
          render(`<p class="em-message error">${data.data?.message || 'Search error.'}</p>`);
          return;
        }

        render(data.data?.html || '<p>No results.</p>');
      } catch (err) {
        console.error(err);
        render('<p class="em-message error">Server error.</p>');
      }
    });
  });
});
