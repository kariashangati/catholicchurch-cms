document.addEventListener('DOMContentLoaded', () => {
  document.querySelectorAll('[data-open-modal]').forEach(btn => {
    btn.addEventListener('click', () => {
      const modal = document.getElementById(btn.dataset.openModal);
      if (modal) modal.hidden = false;
    });
  });

  document.querySelectorAll('[data-close-modal]').forEach(btn => {
    btn.addEventListener('click', () => {
      const modal = document.getElementById(btn.dataset.closeModal);
      if (modal) modal.hidden = true;
    });
  });

  document.querySelectorAll('[data-copy-target]').forEach(btn => {
    btn.addEventListener('click', async () => {
      const target = document.querySelector(btn.dataset.copyTarget);
      if (!target) return;
      const text = target.textContent.trim();
      try {
        await navigator.clipboard.writeText(text);
        btn.dataset.originalText = btn.textContent;
        btn.textContent = 'Copied';
        setTimeout(() => btn.textContent = btn.dataset.originalText || 'Copy', 1500);
      } catch (e) {
        console.warn('Copy failed', e);
      }
    });
  });

  const customMessage = document.getElementById('custom_message');
  const preview = document.getElementById('smsPreviewText');
  if (customMessage && preview) {
    customMessage.addEventListener('input', () => {
      preview.textContent = customMessage.value.trim() || preview.dataset.fallback || '';
    });
  }
});
