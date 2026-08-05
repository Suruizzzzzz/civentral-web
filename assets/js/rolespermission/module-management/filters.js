// FILTER MODULES REAL TIME & TAB MANAGEMENT
window.currentModuleTab = 'active';

window.switchModuleTab = function(tabName) {
  window.currentModuleTab = tabName;
  const activeBtn = document.getElementById('tabActiveModulesBtn');
  const archivedBtn = document.getElementById('tabArchivedModulesBtn');
  const statusFilterSelect = document.getElementById('statusFilterSelect');

  if (tabName === 'active') {
    window.selectedArchivedIds = [];
  }

  if (activeBtn && archivedBtn) {
    if (tabName === 'active') {
      activeBtn.className = "module-tab-btn px-4 py-2.5 text-xs font-bold border-b-2 border-brand-dark text-brand-dark flex items-center gap-2 transition cursor-pointer";
      archivedBtn.className = "module-tab-btn px-4 py-2.5 text-xs font-bold border-b-2 border-transparent text-slate-500 hover:text-slate-700 flex items-center gap-2 transition cursor-pointer";
      if (statusFilterSelect) statusFilterSelect.disabled = false;
    } else {
      activeBtn.className = "module-tab-btn px-4 py-2.5 text-xs font-bold border-b-2 border-transparent text-slate-500 hover:text-slate-700 flex items-center gap-2 transition cursor-pointer";
      archivedBtn.className = "module-tab-btn px-4 py-2.5 text-xs font-bold border-b-2 border-amber-600 text-amber-700 flex items-center gap-2 transition cursor-pointer";
      if (statusFilterSelect) statusFilterSelect.disabled = true;
    }
  }

  if (typeof window.filterModules === 'function') {
    window.filterModules();
  }
};

window.filterModules = function() {
  const searchInput = document.getElementById('moduleSearchInput');
  const statusSelect = document.getElementById('statusFilterSelect');
  const activeBadge = document.getElementById('tabActiveModulesBadge');
  const archivedBadge = document.getElementById('tabArchivedModulesBadge');

  const query = searchInput ? searchInput.value.toLowerCase().trim() : '';
  const selectedStatus = statusSelect ? statusSelect.value : 'ALL';
  const sysMods = Array.isArray(window.systemModules) ? window.systemModules : (typeof systemModules !== 'undefined' ? systemModules : []);

  const activeCount = sysMods.filter(m => m.status !== 'Archived').length;
  const archivedCount = sysMods.filter(m => m.status === 'Archived').length;

  if (activeBadge) activeBadge.textContent = activeCount;
  if (archivedBadge) archivedBadge.textContent = archivedCount;

  const filtered = sysMods.filter(mod => {
    const matchesQuery = mod.name.toLowerCase().includes(query) || 
                         (mod.desc && mod.desc.toLowerCase().includes(query));

    const matchesStatus = selectedStatus === 'ALL' || mod.status === selectedStatus;

    if (window.currentModuleTab === 'archived') {
      return matchesQuery && mod.status === 'Archived';
    } else {
      return matchesQuery && matchesStatus && mod.status !== 'Archived';
    }
  });

  if (typeof renderModulesTable === 'function') renderModulesTable(filtered);
};

// Aliases
var switchModuleTab = window.switchModuleTab;
var filterModules = window.filterModules;
