// FILTER RESOURCES REAL TIME & TAB MANAGEMENT
window.currentResourceTab = 'active';

window.switchResourceTab = function(tabName) {
  window.currentResourceTab = tabName;
  const activeBtn = document.getElementById('tabActiveResourcesBtn');
  const archivedBtn = document.getElementById('tabArchivedResourcesBtn');
  const statusFilterSelect = document.getElementById('statusFilterSelect');

  if (tabName === 'active') {
    window.selectedArchivedResourceIds = [];
  }

  if (activeBtn && archivedBtn) {
    if (tabName === 'active') {
      activeBtn.className = "resource-tab-btn px-4 py-2.5 text-xs font-bold border-b-2 border-brand-dark text-brand-dark flex items-center gap-2 transition cursor-pointer";
      archivedBtn.className = "resource-tab-btn px-4 py-2.5 text-xs font-bold border-b-2 border-transparent text-slate-500 hover:text-slate-700 flex items-center gap-2 transition cursor-pointer";
      if (statusFilterSelect) statusFilterSelect.disabled = false;
    } else {
      activeBtn.className = "resource-tab-btn px-4 py-2.5 text-xs font-bold border-b-2 border-transparent text-slate-500 hover:text-slate-700 flex items-center gap-2 transition cursor-pointer";
      archivedBtn.className = "resource-tab-btn px-4 py-2.5 text-xs font-bold border-b-2 border-amber-600 text-amber-700 flex items-center gap-2 transition cursor-pointer";
      if (statusFilterSelect) statusFilterSelect.disabled = true;
    }
  }

  if (typeof window.filterResources === 'function') {
    window.filterResources();
  }
};

window.filterResources = function() {
  const searchInput = document.getElementById('resourceSearchInput');
  const parentFilter = document.getElementById('parentModuleFilter');
  const statusFilter = document.getElementById('statusFilterSelect');
  const activeBadge = document.getElementById('tabActiveResourcesBadge');
  const archivedBadge = document.getElementById('tabArchivedResourcesBadge');

  const query = searchInput ? searchInput.value.toLowerCase().trim() : '';
  const selectedModule = parentFilter ? parentFilter.value : 'ALL';
  const selectedStatus = statusFilter ? statusFilter.value : 'ALL';

  const sysRes = Array.isArray(window.systemResources) ? window.systemResources : (typeof systemResources !== 'undefined' ? systemResources : []);

  const activeCount = sysRes.filter(r => r.status !== 'Archived').length;
  const archivedCount = sysRes.filter(r => r.status === 'Archived').length;

  if (activeBadge) activeBadge.textContent = activeCount;
  if (archivedBadge) archivedBadge.textContent = archivedCount;

  const filtered = sysRes.filter(res => {
    const matchesQuery = res.name.toLowerCase().includes(query) || 
                         res.route.toLowerCase().includes(query) ||
                         res.module.toLowerCase().includes(query) ||
                         (res.desc && res.desc.toLowerCase().includes(query));

    const matchesModule = selectedModule === 'ALL' || 
                          String(res.module_id) === String(selectedModule) || 
                          res.module === selectedModule;

    const matchesStatus = selectedStatus === 'ALL' || res.status === selectedStatus;

    if (window.currentResourceTab === 'archived') {
      return matchesQuery && matchesModule && res.status === 'Archived';
    } else {
      return matchesQuery && matchesModule && matchesStatus && res.status !== 'Archived';
    }
  });

  if (typeof renderResourcesTable === 'function') renderResourcesTable(filtered);
};

var switchResourceTab = window.switchResourceTab;
var filterResources = window.filterResources;
