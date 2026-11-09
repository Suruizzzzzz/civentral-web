// RENDER MODULES TABLE, METRICS AND PAGINATION
var currentModulePage = 1;
var modulePageSize = 10;
var currentFilteredModules = [];

function changeModulePage(delta) {
  const totalPages = Math.ceil(currentFilteredModules.length / modulePageSize) || 1;
  const newPage = currentModulePage + delta;
  if (newPage >= 1 && newPage <= totalPages) {
    currentModulePage = newPage;
    renderModulesTable(currentFilteredModules, false);
  }
}

function goToModulePage(page) {
  const totalPages = Math.ceil(currentFilteredModules.length / modulePageSize) || 1;
  if (page >= 1 && page <= totalPages) {
    currentModulePage = page;
    renderModulesTable(currentFilteredModules, false);
  }
}

window.changeModulePage = changeModulePage;
window.goToModulePage = goToModulePage;

function toggleSelectModule(checkbox, id) {
  if (!Array.isArray(window.selectedArchivedIds)) window.selectedArchivedIds = [];
  
  if (checkbox.checked) {
    if (!window.selectedArchivedIds.includes(id)) {
      window.selectedArchivedIds.push(id);
    }
  } else {
    window.selectedArchivedIds = window.selectedArchivedIds.filter(item => item !== id);
  }
  updateBulkDeleteToolbar();
}

function toggleSelectAllArchived(selectAllCheckbox) {
  if (!Array.isArray(window.selectedArchivedIds)) window.selectedArchivedIds = [];
  const archivedMods = currentFilteredModules.filter(m => m.status === 'Archived');

  if (selectAllCheckbox.checked) {
    archivedMods.forEach(m => {
      if (!window.selectedArchivedIds.includes(m.id)) {
        window.selectedArchivedIds.push(m.id);
      }
    });
  } else {
    const pageIds = archivedMods.map(m => m.id);
    window.selectedArchivedIds = window.selectedArchivedIds.filter(id => !pageIds.includes(id));
  }

  const rowCheckboxes = document.querySelectorAll('.archive-module-checkbox');
  rowCheckboxes.forEach(cb => {
    cb.checked = selectAllCheckbox.checked;
  });

  updateBulkDeleteToolbar();
}

function updateBulkDeleteToolbar() {
  const bulkBtn = document.getElementById('bulkDeleteBtn');
  const countEl = document.getElementById('selectedCount');
  const selectAllCb = document.getElementById('selectAllArchived');

  const selectedCount = (window.selectedArchivedIds || []).length;

  if (countEl) countEl.textContent = selectedCount;

  if (bulkBtn) {
    if (window.currentModuleTab === 'archived' && selectedCount > 0) {
      bulkBtn.classList.remove('hidden');
    } else {
      bulkBtn.classList.add('hidden');
    }
  }

  if (selectAllCb && window.currentModuleTab === 'archived') {
    const archivedMods = currentFilteredModules.filter(m => m.status === 'Archived');
    if (archivedMods.length > 0) {
      const allSelected = archivedMods.every(m => (window.selectedArchivedIds || []).includes(m.id));
      selectAllCb.checked = allSelected;
    } else {
      selectAllCb.checked = false;
    }
  }
}

window.toggleSelectModule = toggleSelectModule;
window.toggleSelectAllArchived = toggleSelectAllArchived;
window.updateBulkDeleteToolbar = updateBulkDeleteToolbar;

function renderModulesTable(dataToRender = systemModules, resetPage = true) {
  currentFilteredModules = dataToRender;
  if (resetPage) currentModulePage = 1;

  const tableBody = document.getElementById('moduleTableBody');
  const emptyState = document.getElementById('emptyTableState');
  const paginationFooter = document.getElementById('modulePaginationFooter');
  const checkboxTh = document.getElementById('checkboxTh');
  const selectAllCb = document.getElementById('selectAllArchived');

  if (!tableBody) return;

  const isArchivedTab = window.currentModuleTab === 'archived';
  if (checkboxTh) {
    if (isArchivedTab) {
      checkboxTh.classList.remove('hidden');
    } else {
      checkboxTh.classList.add('hidden');
      if (selectAllCb) selectAllCb.checked = false;
    }
  }

  tableBody.innerHTML = '';

  if (dataToRender.length === 0) {
    if (emptyState) emptyState.classList.remove('hidden');
    if (paginationFooter) paginationFooter.classList.add('hidden');
    updateMetrics();
    updateBulkDeleteToolbar();
    return;
  } else {
    if (emptyState) emptyState.classList.add('hidden');
    if (paginationFooter) paginationFooter.classList.remove('hidden');
  }

  const totalPages = Math.ceil(dataToRender.length / modulePageSize) || 1;
  if (currentModulePage > totalPages) currentModulePage = totalPages;

  const startIdx = (currentModulePage - 1) * modulePageSize;
  const endIdx = Math.min(startIdx + modulePageSize, dataToRender.length);
  const pageItems = dataToRender.slice(startIdx, endIdx);

  const isSuperAdmin = currentUserScope ? !!currentUserScope.is_superadmin : false;
  const grantedActions = currentUserScope ? (currentUserScope.granted_actions || []) : [];
  const canEdit = isSuperAdmin || grantedActions.includes('EDIT');
  const canDelete = isSuperAdmin || grantedActions.includes('DELETE');

  pageItems.forEach(mod => {
    const tr = document.createElement('tr');
    tr.className = 'hover:bg-slate-50/60 dark:hover:bg-slate-800/40 transition group';

    let statusBadgeHtml = '';
    if (mod.status === 'Active') {
      statusBadgeHtml = `
        <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-none text-[10px] font-black bg-emerald-50 text-emerald-700 border border-emerald-200">
          <span class="h-1.5 w-1.5 rounded-full bg-emerald-500 animate-pulse"></span>
          Active
        </span>
      `;
    } else if (mod.status === 'Inactive') {
      statusBadgeHtml = `
        <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-none text-[10px] font-black bg-slate-100 text-slate-600 border border-slate-200">
          <span class="h-1.5 w-1.5 rounded-full bg-slate-400"></span>
          Inactive
        </span>
      `;
    } else {
      statusBadgeHtml = `
        <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-none text-[10px] font-black bg-amber-50 text-amber-700 border border-amber-200">
          <span class="h-1.5 w-1.5 rounded-full bg-amber-500"></span>
          Archived
        </span>
      `;
    }

    const isChecked = mod.status === 'Active';
    const isArchived = mod.status === 'Archived';
    const isItemSelected = (window.selectedArchivedIds || []).includes(mod.id);

    const checkboxTdHtml = isArchivedTab ? `
      <td class="py-3 px-4 text-center border-b border-slate-100 dark:border-slate-800/60 align-middle">
        <input 
          type="checkbox" 
          ${isItemSelected ? 'checked' : ''} 
          onchange="toggleSelectModule(this, ${mod.id})" 
          class="archive-module-checkbox rounded border-slate-300 text-rose-600 focus:ring-rose-500 cursor-pointer"
        >
      </td>
    ` : '';

    tr.innerHTML = `
      ${checkboxTdHtml}
      <td class="py-3 px-4 border-b border-slate-100 dark:border-slate-800/60 align-middle">
        <span class="font-semibold text-slate-900 dark:text-slate-100 text-xs tracking-tight block">${mod.name}</span>
      </td>

      <td class="py-3 px-4 max-w-xs border-b border-slate-100 dark:border-slate-800/60 align-middle">
        <p class="text-xs text-slate-600 dark:text-slate-300 font-normal leading-relaxed">${mod.desc || '<span class="italic text-[11px] text-slate-400 dark:text-slate-500">No description</span>'}</p>
      </td>

      <td class="py-3 px-4 text-center border-b border-slate-100 dark:border-slate-800/60 align-middle">
        ${statusBadgeHtml}
      </td>

      <td class="py-3 px-4 text-[11px] font-normal text-slate-500 dark:text-slate-400 font-mono border-b border-slate-100 dark:border-slate-800/60 align-middle">
        ${mod.created_at}
      </td>

      <td class="py-3 px-4 text-[11px] font-normal text-slate-500 dark:text-slate-400 font-mono border-b border-slate-100 dark:border-slate-800/60 align-middle">
        ${mod.updated_at}
      </td>

      <td class="py-3 px-4 text-right border-b border-slate-100 dark:border-slate-800/60 align-middle">
        <div class="flex items-center justify-end gap-2.5">
          ${canEdit ? `
          ${!isArchived ? `
          <!-- iOS Toggle Switch -->
          <label class="relative inline-flex items-center cursor-pointer" title="Activate/Deactivate Module">
            <input 
              type="checkbox" 
              ${isChecked ? 'checked' : ''} 
              onchange="toggleModuleStatus(${mod.id})" 
              class="sr-only peer"
            >
            <div class="w-8 h-4.5 bg-slate-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-slate-300 after:border after:rounded-full after:h-3.5 after:w-3.5 after:transition-all peer-checked:bg-emerald-600"></div>
          </label>

          <!-- Edit Button -->
          <button 
            type="button" 
            onclick="openEditModal(${mod.id})" 
            class="w-7 h-7 flex items-center justify-center border rounded-none transition cursor-pointer shadow-2xs border-amber-300 bg-amber-50 text-amber-600 hover:bg-amber-100 hover:text-amber-700 dark:border-amber-800/80 dark:bg-amber-950/40 dark:text-amber-400 dark:hover:bg-amber-900/60 dark:hover:text-amber-300"
            title="Edit Module Parameters"
          >
            <i class="fa-solid fa-pen text-xs"></i>
          </button>
          ` : ''}

          ${isArchived ? `
          <!-- Restore Button -->
          <button 
            type="button" 
            onclick="restoreModule(${mod.id})" 
            class="w-7 h-7 flex items-center justify-center border rounded-none transition cursor-pointer shadow-2xs border-emerald-300 bg-emerald-50 text-emerald-600 hover:bg-emerald-100 hover:text-emerald-700 dark:border-emerald-800/80 dark:bg-emerald-950/40 dark:text-emerald-400 dark:hover:bg-emerald-900/60 dark:hover:text-emerald-300"
            title="Restore Module to Active Tab"
          >
            <i class="fa-solid fa-rotate-left text-xs"></i>
          </button>

          ${canDelete ? `
          <!-- Permanent Delete Button -->
          <button 
            type="button" 
            onclick="openDeleteConfirmModal(${mod.id})" 
            class="w-7 h-7 flex items-center justify-center border rounded-none transition cursor-pointer shadow-2xs border-rose-300 bg-rose-50 text-rose-600 hover:bg-rose-100 hover:text-rose-700 dark:border-rose-800/80 dark:bg-rose-950/40 dark:text-rose-400 dark:hover:bg-rose-900/60 dark:hover:text-rose-300"
            title="Permanently Delete Module from Database"
          >
            <i class="fa-solid fa-trash text-xs"></i>
          </button>
          ` : ''}
          ` : `
          <!-- Archive Button -->
          <button 
            type="button" 
            onclick="openArchiveModal(${mod.id})" 
            class="w-7 h-7 flex items-center justify-center border rounded-none transition cursor-pointer shadow-2xs border-rose-300 bg-rose-50 text-rose-600 hover:bg-rose-100 hover:text-rose-700 dark:border-rose-800/80 dark:bg-rose-950/40 dark:text-rose-400 dark:hover:bg-rose-900/60 dark:hover:text-rose-300"
            title="Archive Module"
          >
            <i class="fa-solid fa-box-archive text-xs"></i>
          </button>
          `}` : `<span class="text-[10px] text-slate-400 font-bold italic">Read-only</span>`}
        </div>
      </td>
    `;

    tableBody.appendChild(tr);
  });

  // Update Pagination UI
  const startEl = document.getElementById('modulePaginationStart');
  const endEl = document.getElementById('modulePaginationEnd');
  const totalEl = document.getElementById('modulePaginationTotal');
  const container = document.getElementById('modulePaginationControls');

  if (startEl) startEl.textContent = dataToRender.length > 0 ? (startIdx + 1) : 0;
  if (endEl) endEl.textContent = endIdx;
  if (totalEl) totalEl.textContent = dataToRender.length;

  if (container) {
    const isPrevDisabled = currentModulePage <= 1 || dataToRender.length === 0;
    const isNextDisabled = currentModulePage >= totalPages || dataToRender.length === 0;

    let html = `
      <!-- Previous Button -->
      <button type="button" class="${isPrevDisabled ? 'px-3 py-1.5 text-xs font-medium text-slate-300 dark:text-slate-600 bg-slate-50/50 dark:bg-slate-800/30 cursor-not-allowed' : 'px-3 py-1.5 text-xs font-medium text-slate-500 hover:text-slate-700 hover:bg-slate-50 dark:text-slate-400 dark:hover:text-slate-200 dark:hover:bg-slate-800/60 transition cursor-pointer'}" 
              ${isPrevDisabled ? 'disabled' : ''} onclick="goToModulePage(${currentModulePage - 1})" aria-label="Previous Page">
        <i class="fa-solid fa-chevron-left text-[10px]"></i>
      </button>
    `;

    if (dataToRender.length === 0) {
      html += `
        <button type="button" class="px-3.5 py-1.5 text-xs font-bold text-sky-600 dark:text-sky-400 bg-sky-50/80 dark:bg-sky-950/40 border border-sky-300 dark:border-sky-600/60 z-10 cursor-default" disabled>1</button>
      `;
    } else {
      let pages = [];
      if (totalPages <= 7) {
        for (let i = 1; i <= totalPages; i++) pages.push(i);
      } else if (currentModulePage <= 4) {
        pages = [1, 2, 3, 4, 5, '...', totalPages];
      } else if (currentModulePage >= totalPages - 3) {
        pages = [1, '...', totalPages - 4, totalPages - 3, totalPages - 2, totalPages - 1, totalPages];
      } else {
        pages = [1, '...', currentModulePage - 1, currentModulePage, currentModulePage + 1, '...', totalPages];
      }

      pages.forEach(p => {
        if (p === '...') {
          html += `<span class="px-3.5 py-1.5 text-xs font-medium text-slate-400 dark:text-slate-500 border-l border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 select-none">...</span>`;
        } else if (p === currentModulePage) {
          html += `<button type="button" class="px-3.5 py-1.5 text-xs font-bold text-sky-600 dark:text-sky-400 bg-sky-50/80 dark:bg-sky-950/40 border border-sky-300 dark:border-sky-600/60 z-10 cursor-default" aria-current="page">${p}</button>`;
        } else {
          html += `<button type="button" class="px-3.5 py-1.5 text-xs font-medium text-slate-600 hover:text-slate-900 hover:bg-slate-50 dark:text-slate-400 dark:hover:text-slate-200 dark:hover:bg-slate-800/60 border-l border-slate-200 dark:border-slate-700 transition cursor-pointer" onclick="goToModulePage(${p})">${p}</button>`;
        }
      });
    }

    html += `
      <!-- Next Button -->
      <button type="button" class="${isNextDisabled ? 'px-3 py-1.5 text-xs font-medium text-slate-300 dark:text-slate-600 bg-slate-50/50 dark:bg-slate-800/30 cursor-not-allowed border-l border-slate-200 dark:border-slate-700' : 'px-3 py-1.5 text-xs font-medium text-slate-500 hover:text-slate-700 hover:bg-slate-50 dark:text-slate-400 dark:hover:text-slate-200 dark:hover:bg-slate-800/60 border-l border-slate-200 dark:border-slate-700 transition cursor-pointer'}" 
              ${isNextDisabled ? 'disabled' : ''} onclick="goToModulePage(${currentModulePage + 1})" aria-label="Next Page">
        <i class="fa-solid fa-chevron-right text-[10px]"></i>
      </button>
    `;

    container.innerHTML = html;
  }

  updateMetrics();
  updateBulkDeleteToolbar();
}

async function restoreModule(id) {
  if (typeof updateModuleStatusInDb === 'function') {
    await updateModuleStatusInDb(id, 'Active');
  }
}
window.restoreModule = restoreModule;

// UPDATE TOP METRIC CARDS
function updateMetrics() {
  const totalEl = document.getElementById('metricTotalModules');
  const activeEl = document.getElementById('metricActiveModules');
  const inactiveEl = document.getElementById('metricInactiveModules');

  if (totalEl) totalEl.textContent = systemModules.length;
  
  const activeCount = systemModules.filter(m => m.status === 'Active').length;
  if (activeEl) activeEl.textContent = activeCount;

  const inactiveCount = systemModules.filter(m => m.status !== 'Active').length;
  if (inactiveEl) inactiveEl.textContent = inactiveCount;
}

window.renderModulesTable = renderModulesTable;

