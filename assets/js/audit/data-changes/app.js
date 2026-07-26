document.addEventListener('DOMContentLoaded', () => {
  if (window.civAudit && window.civAudit.dataChanges) {
    if (window.civAudit.dataChanges.events) {
      window.civAudit.dataChanges.events.registerListeners();
    }
    if (window.civAudit.dataChanges.api) {
      window.civAudit.dataChanges.api.fetchMutationLogs();
    }
  }
});
