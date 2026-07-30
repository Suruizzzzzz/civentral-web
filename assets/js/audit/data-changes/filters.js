window.civAudit = window.civAudit || {};
window.civAudit.dataChanges = window.civAudit.dataChanges || {};

window.civAudit.dataChanges.filters = {
  applyFilters() {
    const filterSearch = document.getElementById('filterSearch');
    const filterDate = document.getElementById('filterDate');
    const filterModule = document.getElementById('filterModule');

    const searchVal = filterSearch ? filterSearch.value.toLowerCase().trim() : '';
    const dateVal = filterDate ? filterDate.value : '';
    const moduleVal = filterModule ? filterModule.value : 'All';

    const api = window.civAudit.dataChanges.api;

    const filtered = api.auditLogsData.filter(log => {
      const action = log.action || '';

      // Exclude authentication/login/2FA events from Data Changes view
      const isAuthEvent = [
        'Initiate 2FA Login',
        '2FA Verification Success',
        'Resend 2FA OTP',
        'Failed Login',
        'Successful Login',
        'Logout',
        'View'
      ].includes(action);

      if (isAuthEvent) {
        return false;
      }

      let actorName = 'System';
      if (log.users) {
        actorName = `${log.users.first_name || ''} ${log.users.last_name || ''}`.trim() || log.users.email || 'User';
      }
      
      const recordId = log.target_id || log.session_id || '';
      const desc = log.description || '';
      const rawDate = log.created_at || '';
      const isoDate = rawDate.split(' ')[0] || '';
      const modName = (log.modules && log.modules.module_name) ? log.modules.module_name : (log.target_table || 'System');

      const matchSearch = !searchVal || 
                          actorName.toLowerCase().includes(searchVal) || 
                          recordId.toString().toLowerCase().includes(searchVal) || 
                          action.toLowerCase().includes(searchVal) || 
                          desc.toLowerCase().includes(searchVal);
      const matchDate = !dateVal || isoDate === dateVal;
      const matchModule = moduleVal === 'All' || modName === moduleVal;

      return matchSearch && matchDate && matchModule;
    });

    if (window.civAudit.dataChanges.ui) {
      window.civAudit.dataChanges.ui.renderMutationLogs(filtered, 1);
    }
  },

  resetFilters() {
    const filterSearch = document.getElementById('filterSearch');
    const filterDate = document.getElementById('filterDate');
    const filterModule = document.getElementById('filterModule');

    if (filterSearch) filterSearch.value = '';
    if (filterDate) filterDate.value = '';
    if (filterModule) filterModule.value = 'All';

    this.applyFilters();
    if (window.showToast) {
      window.showToast("Filters Reset", "Data mutation search parameters cleared.");
    }
  }
};

window.applyFilters = function() {
  if (window.civAudit && window.civAudit.dataChanges && window.civAudit.dataChanges.filters) {
    window.civAudit.dataChanges.filters.applyFilters();
  }
};

window.resetFilters = function() {
  if (window.civAudit && window.civAudit.dataChanges && window.civAudit.dataChanges.filters) {
    window.civAudit.dataChanges.filters.resetFilters();
  }
};
