window.civAudit = window.civAudit || {};
window.civAudit.loginHistory = window.civAudit.loginHistory || {};

window.civAudit.loginHistory.events = {
  bindEvents() {
    const modal = document.getElementById('sessionDetailsModal');
    if (modal) {
      modal.addEventListener('click', function(event) {
        const card = document.getElementById('modalCard');
        if (card && !card.contains(event.target)) {
          if (window.civAudit.loginHistory.modal) {
            window.civAudit.loginHistory.modal.closeSessionModal();
          }
        }
      });
    }

    document.addEventListener('keydown', function(event) {
      if (event.key === 'Escape') {
        if (window.civAudit.loginHistory.modal) {
          window.civAudit.loginHistory.modal.closeSessionModal();
        }
      }
    });
  }
};
