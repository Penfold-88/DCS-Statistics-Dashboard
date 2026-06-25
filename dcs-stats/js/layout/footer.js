document.addEventListener('DOMContentLoaded', () => {
    const modal = document.getElementById('creditsModal');
    const openButton = document.getElementById('openCredits');
    const closeButton = document.getElementById('closeCredits');

    if (!modal || !openButton || !closeButton) return;

    const openCredits = () => {
      modal.classList.add('is-open');
      modal.setAttribute('aria-hidden', 'false');
      closeButton.focus();
    };

    const closeCredits = () => {
      modal.classList.remove('is-open');
      modal.setAttribute('aria-hidden', 'true');
      openButton.focus();
    };

    openButton.addEventListener('click', openCredits);
    closeButton.addEventListener('click', closeCredits);
    modal.addEventListener('click', event => {
      if (event.target === modal) {
        closeCredits();
      }
    });
    document.addEventListener('keydown', event => {
      if (event.key === 'Escape' && modal.classList.contains('is-open')) {
        closeCredits();
      }
    });
  });
