document.addEventListener('DOMContentLoaded', () => {
  const btn = document.getElementById('join-waitlist-btn');
  if (!btn) return;

  const feedback = document.getElementById('waitlist-feedback');
  const setFeedback = (msg, ok = true) => {
    if (!feedback) return;
    feedback.textContent = msg;
    feedback.style.color = ok ? '#1a7f37' : '#b3261e';
  };

  btn.addEventListener('click', async () => {
    btn.disabled = true;
    setFeedback('Joining waitlist…');
    try {
      const res = await fetch(`/leads/waitlist/join${window.location.search}`, {
        method: 'POST',
        headers: { 'X-Requested-With': 'XMLHttpRequest' },
      });
      const data = await res.json().catch(() => ({}));
      if (res.ok) {
        setFeedback(data.status === 'exists' ? 'Already on the waitlist.' : 'Added to the waitlist. We will notify you.');
      } else {
        setFeedback('Could not join the waitlist. Please try again later.', false);
        btn.disabled = false; // allow retry on failure
      }
    } catch (e) {
      setFeedback('Network error. Please try again.', false);
      btn.disabled = false; // allow retry on failure
    }
  });
});

