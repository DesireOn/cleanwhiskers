document.addEventListener('DOMContentLoaded', () => {
  const btn = document.getElementById('join-waitlist-btn');
  if (!btn) return;

  const feedback = document.getElementById('waitlist-feedback');
  const setFeedback = (msg, ok = true) => {
    if (!feedback) return;
    feedback.textContent = msg;
    feedback.style.color = ok ? '#1a7f37' : '#b3261e';
  };

  // Use query params to scope state per lead/recipient/email
  const params = new URLSearchParams(window.location.search);
  const lid = params.get('lid') || '';
  const rid = params.get('rid') || '';
  const email = params.get('email') || '';
  const storageKey = `waitlist:${lid}:${rid}:${email}`;

  const markJoined = (message = 'Added to the waitlist. We will notify you.') => {
    try { sessionStorage.setItem(storageKey, 'joined'); } catch (_) {}
    btn.disabled = true;
    btn.setAttribute('aria-disabled', 'true');
    btn.classList.add('is-disabled');
    btn.textContent = 'On the waitlist';
    setFeedback(message);
  };

  // If already joined earlier in this session, keep UI disabled
  try {
    if (sessionStorage.getItem(storageKey) === 'joined') {
      markJoined('Already on the waitlist.');
    }
  } catch (_) {}

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
        if (data.status === 'exists') {
          markJoined('Already on the waitlist.');
        } else {
          markJoined('Added to the waitlist. We will notify you.');
        }
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
