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
    tr.className = 'hover:bg-slate-50/60 transition group';

    let statusBadgeHtml = '';
    if (mod.status === 'Active') {
      statusBadgeHtml = `
        <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-[10px] font-black bg-emerald-50 text-emerald-700 border border-emerald-200">
          <span class="h-1.5 w-1.5 rounded-full bg-emerald-500 animate-pulse"></span>
          Active
        </span>
      `;
    } else if (mod.status === 'Inactive') {
      statusBadgeHtml = `
        <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-[10px] font-black bg-slate-100 text-slate-600 border border-slate-200">
          <span class="h-1.5 w-1.5 rounded-full bg-slate-400"></span>
          Inactive
        </span>
      `;
    } else {
      statusBadgeHtml = `
        <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-[10px] font-black bg-amber-50 text-amber-700 border border-amber-200">
          <span class="h-1.5 w-1.5 rounded-full bg-amber-500"></span>
          Archived
        </span>
      `;
    }

    const isChecked = mod.status === 'Active';
    const isArchived = mod.status === 'Archived';
    const isItemSelected = (window.selectedArchivedIds || []).includes(mod.id);

    const checkboxTdHtml = isArchivedTab ? `
      <td class="px-4 py-4 text-center">
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
      <td class="px-6 py-4">
        <span class="font-extrabold text-slate-900 tracking-tight block text-xs">${mod.name}</span>
      </td>

      <td class="px-6 py-4 max-w-xs">
        <p class="text-xs text-slate-600 font-medium leading-relaxed">${mod.desc || '<span class="text-slate-400 italic">No description</span>'}</p>
      </td>

      <td class="px-6 py-4 text-center">
        ${statusBadgeHtml}
      </td>

      <td class="px-6 py-4 text-slate-500 font-mono text-[10px] font-bold">
        ${mod.created_at}
      </td>

      <td class="px-6 py-4 text-slate-500 font-mono text-[10px] font-bold">
        ${mod.updated_at}
      </td>

      <td class="px-6 py-4 text-right">
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
            class="h-8 w-8 rounded-lg border border-slate-200 hover:bg-slate-100 text-slate-600 hover:text-brand-dark flex items-center justify-center transition cursor-pointer"
            title="Edit Module Parameters"
          >
            <i class="fa-solid fa-pen-to-square text-xs"></i>
          </button>
          ` : ''}

          ${isArchived ? `
          <!-- Restore Button -->
          <button 
            type="button" 
            onclick="restoreModule(${mod.id})" 
            class="px-2.5 py-1.5 rounded-lg border border-emerald-200 bg-emerald-50 hover:bg-emerald-100 text-emerald-700 font-bold text-[11px] flex items-center gap-1.5 transition cursor-pointer shadow-2xs"
            title="Restore Module to Active Tab"
          >
            <i class="fa-solid fa-rotate-left text-xs"></i>
            <span>Restore</span>
          </button>

          ${canDelete ? `
          <!-- Permanent Delete Button -->
          <button 
            type="button" 
            onclick="openDeleteConfirmModal(${mod.id})" 
            class="h-8 w-8 rounded-lg border border-rose-200 bg-rose-50 hover:bg-rose-100 text-rose-600 flex items-center justify-center transition cursor-pointer"
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
            class="h-8 w-8 rounded-lg border border-slate-200 hover:bg-amber-50 text-slate-400 hover:text-amber-600 flex items-center justify-center transition cursor-pointer"
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
  const prevBtn = document.getElementById('modulePrevPageBtn');
  const nextBtn = document.getElementById('moduleNextPageBtn');
  const pageNumsEl = document.getElementById('modulePageNumbers');

  if (startEl) startEl.textContent = dataToRender.length > 0 ? (startIdx + 1) : 0;
  if (endEl) endEl.textContent = endIdx;
  if (totalEl) totalEl.textContent = dataToRender.length;

  if (prevBtn) prevBtn.disabled = (currentModulePage <= 1);
  if (nextBtn) nextBtn.disabled = (currentModulePage >= totalPages);

  if (pageNumsEl) {
    let numsHtml = '';
    for (let p = 1; p <= totalPages; p++) {
      if (p === currentModulePage) {
        numsHtml += `<button type="button" onclick="goToModulePage(${p})" class="h-7 w-7 rounded-lg bg-[#86B6F6] text-slate-900 font-extrabold text-xs flex items-center justify-center cursor-pointer shadow-2xs">${p}</button>`;
      } else {
        numsHtml += `<button type="button" onclick="goToModulePage(${p})" class="h-7 w-7 rounded-lg border border-slate-200 bg-white hover:bg-slate-50 text-slate-600 font-bold text-xs flex items-center justify-center cursor-pointer">${p}</button>`;
      }
    }
    pageNumsEl.innerHTML = numsHtml;
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

