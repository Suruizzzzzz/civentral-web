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
    tr.className = 'hover:bg-slate-50/60 transition group';

    const badgeStyle = moduleBadgeMap[res.module] || 'bg-slate-100 text-slate-700 border-slate-200';

    let statusBadgeHtml = '';
    if (res.status === 'Active') {
      statusBadgeHtml = `
        <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-[10px] font-black bg-emerald-50 text-emerald-700 border border-emerald-200">
          <span class="h-1.5 w-1.5 rounded-full bg-emerald-500 animate-pulse"></span>
          Active
        </span>
      `;
    } else if (res.status === 'Inactive') {
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

    const isChecked = res.status === 'Active';
    const isArchived = res.status === 'Archived';
    const isItemSelected = (window.selectedArchivedResourceIds || []).includes(res.id);

    const checkboxTdHtml = isArchivedTab ? `
      <td class="px-4 py-4 text-center">
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
      <td class="px-6 py-4">
        <div class="space-y-0.5">
          <span class="font-extrabold text-slate-900 tracking-tight block text-xs">${res.name}</span>
          <code class="text-[10px] font-mono text-slate-500 bg-slate-50 px-1.5 py-0.5 rounded border border-slate-200/60 block max-w-xs truncate" title="${res.route}">${res.route}</code>
        </div>
      </td>

      <td class="px-6 py-4">
        <span class="inline-block px-2.5 py-1 rounded-lg text-[10px] font-bold border ${badgeStyle}">
          ${res.module}
        </span>
      </td>

      <td class="px-6 py-4 max-w-xs">
        <p class="text-xs text-slate-600 font-medium leading-relaxed">${res.desc || '<span class="text-slate-400 italic">No description</span>'}</p>
      </td>

      <td class="px-6 py-4 text-center">
        ${statusBadgeHtml}
      </td>

      <td class="px-6 py-4 text-slate-500 font-mono text-[10px] font-bold">
        ${res.created_at}
      </td>

      <td class="px-6 py-4 text-slate-500 font-mono text-[10px] font-bold">
        ${res.updated_at}
      </td>

      <td class="px-6 py-4 text-right">
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
            class="h-8 w-8 rounded-lg border border-slate-200 hover:bg-slate-100 text-slate-600 hover:text-brand-dark flex items-center justify-center transition cursor-pointer"
            title="Edit Resource"
          >
            <i class="fa-solid fa-pen-to-square text-xs"></i>
          </button>
          ` : ''}

          ${isArchived ? `
          <!-- Restore Button -->
          <button 
            type="button" 
            onclick="restoreResource(${res.id})" 
            class="px-2.5 py-1.5 rounded-lg border border-emerald-200 bg-emerald-50 hover:bg-emerald-100 text-emerald-700 font-bold text-[11px] flex items-center gap-1.5 transition cursor-pointer shadow-2xs"
            title="Restore Resource to Active Tab"
          >
            <i class="fa-solid fa-rotate-left text-xs"></i>
            <span>Restore</span>
          </button>

          ${canDelete ? `
          <!-- Permanent Delete Button -->
          <button 
            type="button" 
            onclick="openDeleteConfirmModal(${res.id})" 
            class="h-8 w-8 rounded-lg border border-rose-200 bg-rose-50 hover:bg-rose-100 text-rose-600 flex items-center justify-center transition cursor-pointer"
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
            class="h-8 w-8 rounded-lg border border-slate-200 hover:bg-amber-50 text-slate-400 hover:text-amber-600 flex items-center justify-center transition cursor-pointer"
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
  const prevBtn = document.getElementById('resourcePrevPageBtn');
  const nextBtn = document.getElementById('resourceNextPageBtn');
  const pageNumsEl = document.getElementById('resourcePageNumbers');

  if (startEl) startEl.textContent = dataToRender.length > 0 ? (startIdx + 1) : 0;
  if (endEl) endEl.textContent = endIdx;
  if (totalEl) totalEl.textContent = dataToRender.length;

  if (prevBtn) prevBtn.disabled = (currentResourcePage <= 1);
  if (nextBtn) nextBtn.disabled = (currentResourcePage >= totalPages);

  if (pageNumsEl) {
    let numsHtml = '';
    for (let p = 1; p <= totalPages; p++) {
      if (p === currentResourcePage) {
        numsHtml += `<button type="button" onclick="goToResourcePage(${p})" class="h-7 w-7 rounded-lg bg-[#86B6F6] text-slate-900 font-extrabold text-xs flex items-center justify-center cursor-pointer shadow-2xs">${p}</button>`;
      } else {
        numsHtml += `<button type="button" onclick="goToResourcePage(${p})" class="h-7 w-7 rounded-lg border border-slate-200 bg-white hover:bg-slate-50 text-slate-600 font-bold text-xs flex items-center justify-center cursor-pointer">${p}</button>`;
      }
    }
    pageNumsEl.innerHTML = numsHtml;
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

