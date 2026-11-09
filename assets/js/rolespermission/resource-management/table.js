// RENDER RESOURCES DATATABLE & PAGINATION
var currentResourcePage = 1;
var resourcePageSize = 10;
var currentFilteredResources = [];

function changeResourcePage(delta) {
  const totalPages = Math.ceil(currentFilteredResources.length / resourcePageSize) || 1;
  const newPage = currentResourcePage + delta;
  if (newPage >= 1 && newPage <= totalPages) {
    currentResourcePage = newPage;
    renderResourcesTable(currentFilteredResources, false);
  }
}

function goToResourcePage(page) {
  const totalPages = Math.ceil(currentFilteredResources.length / resourcePageSize) || 1;
  if (page >= 1 && page <= totalPages) {
    currentResourcePage = page;
    renderResourcesTable(currentFilteredResources, false);
  }
}

window.changeResourcePage = changeResourcePage;
window.goToResourcePage = goToResourcePage;

function toggleSelectResource(checkbox, id) {
  if (!Array.isArray(window.selectedArchivedResourceIds)) window.selectedArchivedResourceIds = [];
  
  if (checkbox.checked) {
    if (!window.selectedArchivedResourceIds.includes(id)) {
      window.selectedArchivedResourceIds.push(id);
    }
  } else {
    window.selectedArchivedResourceIds = window.selectedArchivedResourceIds.filter(item => item !== id);
  }
  updateBulkDeleteToolbar();
}

function toggleSelectAllArchivedResources(selectAllCheckbox) {
  if (!Array.isArray(window.selectedArchivedResourceIds)) window.selectedArchivedResourceIds = [];
  const archivedRes = currentFilteredResources.filter(r => r.status === 'Archived');

  if (selectAllCheckbox.checked) {
    archivedRes.forEach(r => {
      if (!window.selectedArchivedResourceIds.includes(r.id)) {
        window.selectedArchivedResourceIds.push(r.id);
      }
    });
  } else {
    const pageIds = archivedRes.map(r => r.id);
    window.selectedArchivedResourceIds = window.selectedArchivedResourceIds.filter(id => !pageIds.includes(id));
  }

  const rowCheckboxes = document.querySelectorAll('.archive-resource-checkbox');
  rowCheckboxes.forEach(cb => {
    cb.checked = selectAllCheckbox.checked;
  });

  updateBulkDeleteToolbar();
}

function updateBulkDeleteToolbar() {
  const bulkBtn = document.getElementById('bulkDeleteBtn');
  const countEl = document.getElementById('selectedCount');
  const selectAllCb = document.getElementById('selectAllArchivedResources');

  const selectedCount = (window.selectedArchivedResourceIds || []).length;

  if (countEl) countEl.textContent = selectedCount;

  if (bulkBtn) {
    if (window.currentResourceTab === 'archived' && selectedCount > 0) {
      bulkBtn.classList.remove('hidden');
    } else {
      bulkBtn.classList.add('hidden');
    }
  }

  if (selectAllCb && window.currentResourceTab === 'archived') {
    const archivedRes = currentFilteredResources.filter(r => r.status === 'Archived');
    if (archivedRes.length > 0) {
      const allSelected = archivedRes.every(r => (window.selectedArchivedResourceIds || []).includes(r.id));
      selectAllCb.checked = allSelected;
    } else {
      selectAllCb.checked = false;
    }
  }
}

window.toggleSelectResource = toggleSelectResource;
window.toggleSelectAllArchivedResources = toggleSelectAllArchivedResources;
window.updateBulkDeleteToolbar = updateBulkDeleteToolbar;

function renderResourcesTable(dataToRender = systemResources, resetPage = true) {
  currentFilteredResources = dataToRender;
  if (resetPage) currentResourcePage = 1;

  const tableBody = document.getElementById('resourceTableBody');
  const emptyState = document.getElementById('emptyTableState');
  const paginationFooter = document.getElementById('resourcePaginationFooter');
  const checkboxTh = document.getElementById('checkboxTh');
  const selectAllCb = document.getElementById('selectAllArchivedResources');

  if (!tableBody) return;

  const isArchivedTab = window.currentResourceTab === 'archived';
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
    updateResourceMetrics();
    updateBulkDeleteToolbar();
    return;
  } else {
    if (emptyState) emptyState.classList.add('hidden');
    if (paginationFooter) paginationFooter.classList.remove('hidden');
  }

  const totalPages = Math.ceil(dataToRender.length / resourcePageSize) || 1;
  if (currentResourcePage > totalPages) currentResourcePage = totalPages;

  const startIdx = (currentResourcePage - 1) * resourcePageSize;
  const endIdx = Math.min(startIdx + resourcePageSize, dataToRender.length);
  const pageItems = dataToRender.slice(startIdx, endIdx);

  const moduleBadgeMap = {
    "User Management": "bg-purple-50 text-purple-700 border-purple-200",
    "Citizen Management": "bg-indigo-50 text-indigo-700 border-indigo-200",
    "Education & Scholarship": "bg-blue-50 text-blue-700 border-blue-200",
    "Health Services": "bg-rose-50 text-rose-700 border-rose-200",
    "BPLO Licensing & Permits": "bg-teal-50 text-teal-700 border-teal-200",
    "DRRM Dispatch & Emergency": "bg-amber-50 text-amber-700 border-amber-200",
    "Reports & Analytics": "bg-emerald-50 text-emerald-700 border-emerald-200",
    "System Settings": "bg-slate-100 text-slate-700 border-slate-200",
    "Legacy Cashiering": "bg-slate-100 text-slate-600 border-slate-200",
    "Archived Portal Gateway": "bg-amber-50 text-amber-700 border-amber-200"
  };

  const isSuperAdmin = currentUserScope ? !!currentUserScope.is_superadmin : false;
  const grantedActions = currentUserScope ? (currentUserScope.granted_actions || []) : [];
  const canEdit = isSuperAdmin || grantedActions.includes('EDIT');
  const canDelete = isSuperAdmin || grantedActions.includes('DELETE');

  pageItems.forEach(res => {
    const tr = document.createElement('tr');
    tr.className = 'hover:bg-slate-50/60 dark:hover:bg-slate-800/40 transition group';

    const badgeStyle = moduleBadgeMap[res.module] || 'bg-slate-100 text-slate-700 border-slate-200';

    let statusBadgeHtml = '';
    if (res.status === 'Active') {
      statusBadgeHtml = `
        <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-none text-[10px] font-black bg-emerald-50 text-emerald-700 border border-emerald-200">
          <span class="h-1.5 w-1.5 rounded-full bg-emerald-500 animate-pulse"></span>
          Active
        </span>
      `;
    } else if (res.status === 'Inactive') {
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

    const isChecked = res.status === 'Active';
    const isArchived = res.status === 'Archived';
    const isItemSelected = (window.selectedArchivedResourceIds || []).includes(res.id);

    const checkboxTdHtml = isArchivedTab ? `
      <td class="py-3 px-4 text-center border-b border-slate-100 dark:border-slate-800/60 align-middle">
        <input 
          type="checkbox" 
          ${isItemSelected ? 'checked' : ''} 
          onchange="toggleSelectResource(this, ${res.id})" 
          class="archive-resource-checkbox rounded border-slate-300 text-rose-600 focus:ring-rose-500 cursor-pointer"
        >
      </td>
    ` : '';

    tr.innerHTML = `
      ${checkboxTdHtml}
      <td class="py-3 px-4 border-b border-slate-100 dark:border-slate-800/60 align-middle">
        <div class="space-y-0.5">
          <span class="font-semibold text-slate-900 dark:text-slate-100 text-xs tracking-tight block">${res.name}</span>
          <code class="text-[10px] font-mono text-slate-500 dark:text-slate-400 bg-slate-50 dark:bg-slate-800 px-1.5 py-0.5 rounded-none border border-slate-200/60 dark:border-slate-700 block max-w-xs truncate" title="${res.route}">${res.route}</code>
        </div>
      </td>

      <td class="py-3 px-4 border-b border-slate-100 dark:border-slate-800/60 align-middle">
        <span class="inline-block px-2.5 py-1 rounded-none text-[10px] font-bold border ${badgeStyle}">
          ${res.module}
        </span>
      </td>

      <td class="py-3 px-4 max-w-xs border-b border-slate-100 dark:border-slate-800/60 align-middle">
        <p class="text-xs text-slate-600 dark:text-slate-300 font-normal leading-relaxed">${res.desc || '<span class="italic text-[11px] text-slate-400 dark:text-slate-500">No description</span>'}</p>
      </td>

      <td class="py-3 px-4 text-center border-b border-slate-100 dark:border-slate-800/60 align-middle">
        ${statusBadgeHtml}
      </td>

      <td class="py-3 px-4 text-[11px] font-normal text-slate-500 dark:text-slate-400 font-mono border-b border-slate-100 dark:border-slate-800/60 align-middle">
        ${res.created_at}
      </td>

      <td class="py-3 px-4 text-[11px] font-normal text-slate-500 dark:text-slate-400 font-mono border-b border-slate-100 dark:border-slate-800/60 align-middle">
        ${res.updated_at}
      </td>

      <td class="py-3 px-4 text-right border-b border-slate-100 dark:border-slate-800/60 align-middle">
        <div class="flex items-center justify-end gap-2.5">
          ${canEdit ? `
          ${!isArchived ? `
          <!-- iOS Toggle Switch -->
          <label class="relative inline-flex items-center cursor-pointer" title="Activate/Deactivate Resource">
            <input 
              type="checkbox" 
              ${isChecked ? 'checked' : ''} 
              onchange="toggleResourceStatus(${res.id})" 
              class="sr-only peer"
            >
            <div class="w-8 h-4.5 bg-slate-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-slate-300 after:border after:rounded-full after:h-3.5 after:w-3.5 after:transition-all peer-checked:bg-emerald-600"></div>
          </label>

          <!-- Edit Button -->
          <button 
            type="button" 
            onclick="openEditResourceModal(${res.id})" 
            class="w-7 h-7 flex items-center justify-center border rounded-none transition cursor-pointer shadow-2xs border-amber-300 bg-amber-50 text-amber-600 hover:bg-amber-100 hover:text-amber-700 dark:border-amber-800/80 dark:bg-amber-950/40 dark:text-amber-400 dark:hover:bg-amber-900/60 dark:hover:text-amber-300"
            title="Edit Resource"
          >
            <i class="fa-solid fa-pen text-xs"></i>
          </button>
          ` : ''}

          ${isArchived ? `
          <!-- Restore Button -->
          <button 
            type="button" 
            onclick="restoreResource(${res.id})" 
            class="w-7 h-7 flex items-center justify-center border rounded-none transition cursor-pointer shadow-2xs border-emerald-300 bg-emerald-50 text-emerald-600 hover:bg-emerald-100 hover:text-emerald-700 dark:border-emerald-800/80 dark:bg-emerald-950/40 dark:text-emerald-400 dark:hover:bg-emerald-900/60 dark:hover:text-emerald-300"
            title="Restore Resource to Active Tab"
          >
            <i class="fa-solid fa-rotate-left text-xs"></i>
          </button>

          ${canDelete ? `
          <!-- Permanent Delete Button -->
          <button 
            type="button" 
            onclick="openDeleteConfirmModal(${res.id})" 
            class="w-7 h-7 flex items-center justify-center border rounded-none transition cursor-pointer shadow-2xs border-rose-300 bg-rose-50 text-rose-600 hover:bg-rose-100 hover:text-rose-700 dark:border-rose-800/80 dark:bg-rose-950/40 dark:text-rose-400 dark:hover:bg-rose-900/60 dark:hover:text-rose-300"
            title="Permanently Delete Resource from Database"
          >
            <i class="fa-solid fa-trash text-xs"></i>
          </button>
          ` : ''}
          ` : `
          <!-- Archive Button -->
          <button 
            type="button" 
            onclick="openArchiveResourceModal(${res.id})" 
            class="w-7 h-7 flex items-center justify-center border rounded-none transition cursor-pointer shadow-2xs border-rose-300 bg-rose-50 text-rose-600 hover:bg-rose-100 hover:text-rose-700 dark:border-rose-800/80 dark:bg-rose-950/40 dark:text-rose-400 dark:hover:bg-rose-900/60 dark:hover:text-rose-300"
            title="Archive Resource"
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
  const startEl = document.getElementById('resourcePaginationStart');
  const endEl = document.getElementById('resourcePaginationEnd');
  const totalEl = document.getElementById('resourcePaginationTotal');
  const container = document.getElementById('resourcePaginationControls');

  if (startEl) startEl.textContent = dataToRender.length > 0 ? (startIdx + 1) : 0;
  if (endEl) endEl.textContent = endIdx;
  if (totalEl) totalEl.textContent = dataToRender.length;

  if (container) {
    const isPrevDisabled = currentResourcePage <= 1 || dataToRender.length === 0;
    const isNextDisabled = currentResourcePage >= totalPages || dataToRender.length === 0;

    let html = `
      <!-- Previous Button -->
      <button type="button" class="${isPrevDisabled ? 'px-3 py-1.5 text-xs font-medium text-slate-300 dark:text-slate-600 bg-slate-50/50 dark:bg-slate-800/30 cursor-not-allowed' : 'px-3 py-1.5 text-xs font-medium text-slate-500 hover:text-slate-700 hover:bg-slate-50 dark:text-slate-400 dark:hover:text-slate-200 dark:hover:bg-slate-800/60 transition cursor-pointer'}" 
              ${isPrevDisabled ? 'disabled' : ''} onclick="goToResourcePage(${currentResourcePage - 1})" aria-label="Previous Page">
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
      } else if (currentResourcePage <= 4) {
        pages = [1, 2, 3, 4, 5, '...', totalPages];
      } else if (currentResourcePage >= totalPages - 3) {
        pages = [1, '...', totalPages - 4, totalPages - 3, totalPages - 2, totalPages - 1, totalPages];
      } else {
        pages = [1, '...', currentResourcePage - 1, currentResourcePage, currentResourcePage + 1, '...', totalPages];
      }

      pages.forEach(p => {
        if (p === '...') {
          html += `<span class="px-3.5 py-1.5 text-xs font-medium text-slate-400 dark:text-slate-500 border-l border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 select-none">...</span>`;
        } else if (p === currentResourcePage) {
          html += `<button type="button" class="px-3.5 py-1.5 text-xs font-bold text-sky-600 dark:text-sky-400 bg-sky-50/80 dark:bg-sky-950/40 border border-sky-300 dark:border-sky-600/60 z-10 cursor-default" aria-current="page">${p}</button>`;
        } else {
          html += `<button type="button" class="px-3.5 py-1.5 text-xs font-medium text-slate-600 hover:text-slate-900 hover:bg-slate-50 dark:text-slate-400 dark:hover:text-slate-200 dark:hover:bg-slate-800/60 border-l border-slate-200 dark:border-slate-700 transition cursor-pointer" onclick="goToResourcePage(${p})">${p}</button>`;
        }
      });
    }

    html += `
      <!-- Next Button -->
      <button type="button" class="${isNextDisabled ? 'px-3 py-1.5 text-xs font-medium text-slate-300 dark:text-slate-600 bg-slate-50/50 dark:bg-slate-800/30 cursor-not-allowed border-l border-slate-200 dark:border-slate-700' : 'px-3 py-1.5 text-xs font-medium text-slate-500 hover:text-slate-700 hover:bg-slate-50 dark:text-slate-400 dark:hover:text-slate-200 dark:hover:bg-slate-800/60 border-l border-slate-200 dark:border-slate-700 transition cursor-pointer'}" 
              ${isNextDisabled ? 'disabled' : ''} onclick="goToResourcePage(${currentResourcePage + 1})" aria-label="Next Page">
        <i class="fa-solid fa-chevron-right text-[10px]"></i>
      </button>
    `;

    container.innerHTML = html;
  }

  updateResourceMetrics();
  updateBulkDeleteToolbar();
}

async function restoreResource(id) {
  if (typeof updateResourceStatusInDb === 'function') {
    await updateResourceStatusInDb(id, 'Active');
  }
}
window.restoreResource = restoreResource;

// UPDATE METRIC CARDS
function updateResourceMetrics() {
  const totalEl = document.getElementById('metricTotalResources');
  const activeEl = document.getElementById('metricActiveResources');
  const inactiveEl = document.getElementById('metricInactiveResources');

  if (totalEl) totalEl.textContent = systemResources.length;
  
  const activeCount = systemResources.filter(r => r.status === 'Active').length;
  if (activeEl) activeEl.textContent = activeCount;

  const inactiveCount = systemResources.filter(r => r.status !== 'Active').length;
  if (inactiveEl) inactiveEl.textContent = inactiveCount;
}

window.renderResourcesTable = renderResourcesTable;

