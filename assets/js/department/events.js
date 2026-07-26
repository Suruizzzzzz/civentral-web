// Setup Events
document.querySelectorAll('[id$="Modal"]').forEach(modal => {
  modal.addEventListener('mousedown', function(e) {
    if (e.target === this) closeModal(this.id);
  });
});

searchInput.addEventListener('input', filterDepts);
