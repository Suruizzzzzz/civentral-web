// DEPARTMENT TABLE UI & PAGINATION

let currentDeptPage = 1;
const deptPageSize = 10;
let activeFilteredDepts = [];

window.currentDeptPage = currentDeptPage;

window.resetDeptPage = function() {
  currentDeptPage = 1;
  window.currentDeptPage = 1;
};

window.changeDeptPage = function(targetPage) {
  currentDeptPage = targetPage;
  window.currentDeptPage = targetPage;
  renderDepts(activeFilteredDepts);
};

// Render Table Rows
function renderDepts(deptsList = departmentsData) {
  if (!deptsTbody) return;
  activeFilteredDepts = deptsList;
  deptsTbody.innerHTML = '';

  const totalItems = activeFilteredDepts.length;
  const totalPages = Math.ceil(totalItems / deptPageSize) || 1;

  if (currentDeptPage > totalPages) currentDeptPage = totalPages;
  if (currentDeptPage < 1) currentDeptPage = 1;
  window.currentDeptPage = currentDeptPage;

  if (totalItems === 0) {
    deptsTbody.innerHTML = `
      <tr>
        <td colspan="5" class="px-6 py-12 text-center text-slate-400 font-semibold">
          <i class="fa-solid fa-building-circle-exclamation text-3xl mb-3 block opacity-60"></i>
          No departments matched your query.
        </td>
      </tr>
    `;
    const paginationEl = document.getElementById('deptsPaginationText');
    if (paginationEl) {
      paginationEl.innerHTML = `Showing <span class="font-semibold text-slate-700 dark:text-slate-200">0</span> to <span class="font-semibold text-slate-700 dark:text-slate-200">0</span> of <span class="font-semibold text-slate-700 dark:text-slate-200">0</span> records`;
    }
    renderDeptsPaginationControls(0, 1, 1);
    return;
  }

  const startIndex = (currentDeptPage - 1) * deptPageSize;
  const endIndex = Math.min(startIndex + deptPageSize, totalItems);
  const pageItems = activeFilteredDepts.slice(startIndex, endIndex);

  const isSuperAdmin = currentUserScope ? !!currentUserScope.is_superadmin : false;
  const grantedActions = currentUserScope ? (currentUserScope.granted_actions || []) : [];
  const canEdit = isSuperAdmin || grantedActions.includes('EDIT');

  pageItems.forEach(dept => {
    const isActive = dept.status === 'Active';
    const statusClass = isActive ? 'bg-emerald-50 text-emerald-700 border-emerald-150' : 'bg-slate-50 text-slate-500 border-slate-200';
    const dotPulse = isActive
      ? '<span class="h-1.5 w-1.5 rounded-full bg-emerald-500 animate-pulse"></span>'
      : '<span class="h-1.5 w-1.5 rounded-full bg-slate-400"></span>';

    // Resolve admin name from joined users object
    const adminObj = dept.users || null;
    const adminName = adminObj ? `${adminObj.first_name} ${adminObj.last_name}` : null;
    const hasAdmin = !!adminName;

    const adminDisplay = hasAdmin
      ? `<span class="font-extrabold text-slate-700">${adminName}</span>`
      : `<span class="bg-amber-50 text-amber-700 px-2 py-0.5 rounded-none text-[10px] font-black border border-amber-100/60 inline-flex items-center gap-1"><i class="fa-solid fa-user-slash text-[9px]"></i> Unassigned</span>`;

    const isChecked = isActive ? 'checked' : '';

    const warningBanner = !hasAdmin ? `
      <tr class="bg-amber-50/40 border-b border-slate-200/50">
        <td colspan="5" class="px-6 py-2">
          <div class="flex items-center gap-2 text-[10px] text-amber-800 font-bold">
            <i class="fa-solid fa-triangle-exclamation text-amber-600"></i>
            <span>Attention required: This department currently has no assigned Department Head. System routing may default to global admin.</span>
          </div>
        </td>
      </tr>
    ` : '';

    const row = `
      <tr class="hover:bg-slate-50/60 dark:hover:bg-slate-800/40 transition group">
        <!-- Department Name & Code -->
        <td class="py-3 px-4 border-b border-slate-100 dark:border-slate-800/60 align-middle">
          <div class="flex items-center gap-3">
            <div class="h-9 w-9 rounded-none bg-slate-100 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 flex items-center justify-center text-slate-600 dark:text-slate-300 shrink-0 font-bold text-xs">
              <i class="fa-solid fa-building"></i>
            </div>
            <div>
              <span class="font-semibold text-slate-900 dark:text-slate-100 tracking-tight text-xs block">${dept.department_name}</span>
              <span class="text-[11px] font-normal text-slate-500 dark:text-slate-400 font-mono uppercase tracking-wider">${dept.department_code}</span>
            </div>
          </div>
        </td>

        <!-- Department Head -->
        <td class="py-3 px-4 text-xs text-slate-600 dark:text-slate-300 border-b border-slate-100 dark:border-slate-800/60 align-middle">
          ${adminDisplay}
        </td>

        <!-- Integration Tag -->
        <td class="py-3 px-4 border-b border-slate-100 dark:border-slate-800/60 align-middle">
          <span class="bg-slate-50 dark:bg-slate-800 border border-slate-200/50 dark:border-slate-700 rounded-none px-2 py-0.5 text-[9px] text-slate-450 dark:text-slate-400 font-bold font-mono tracking-tight">${dept.department_code.toLowerCase()}_core</span>
        </td>

        <!-- Status -->
        <td class="py-3 px-4 border-b border-slate-100 dark:border-slate-800/60 align-middle">
          <span class="text-[10px] font-bold uppercase px-2 py-0.5 rounded-none border ${statusClass} inline-flex items-center gap-1.5">
            ${dotPulse}
            <span>${dept.status}</span>
          </span>
        </td>

        <!-- Actions -->
        <td class="py-3 px-4 text-right whitespace-nowrap border-b border-slate-100 dark:border-slate-800/60 align-middle">
          <div class="inline-flex items-center space-x-3">
            ${canEdit ? `
            <!-- Toggle Status Switch -->
            <label class="relative inline-flex items-center cursor-pointer select-none" title="Activate/Deactivate department">
              <input type="checkbox" ${isChecked} onchange="handleDeptToggle(${dept.department_id}, this)" class="sr-only peer">
              <div class="w-8 h-4.5 bg-slate-200 rounded-full peer peer-focus:ring-2 peer-focus:ring-brand-medium/20 peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-slate-300 after:border after:rounded-full after:h-3.5 after:w-3.5 after:transition-all peer-checked:bg-emerald-500"></div>
            </label>` : ''}

            <!-- View details button -->
            <button onclick="openViewModal(${dept.department_id})" class="w-7 h-7 flex items-center justify-center border rounded-none transition cursor-pointer shadow-2xs border-sky-300 bg-sky-50 text-sky-600 hover:bg-sky-100 hover:text-sky-700 dark:border-sky-800/80 dark:bg-sky-950/40 dark:text-sky-400 dark:hover:bg-sky-900/60 dark:hover:text-sky-300" title="View Summary Details">
              <i class="fa-solid fa-eye text-xs"></i>
            </button>

            ${canEdit ? `
            <!-- Edit button -->
            <button onclick="openEditModal(${dept.department_id})" class="w-7 h-7 flex items-center justify-center border rounded-none transition cursor-pointer shadow-2xs border-amber-300 bg-amber-50 text-amber-600 hover:bg-amber-100 hover:text-amber-700 dark:border-amber-800/80 dark:bg-amber-950/40 dark:text-amber-400 dark:hover:bg-amber-900/60 dark:hover:text-amber-300" title="Edit Parameters">
              <i class="fa-solid fa-pen text-xs"></i>
            </button>` : ''}
          </div>
        </td>
      </tr>
      ${warningBanner}
    `;
    deptsTbody.innerHTML += row;
  });

  const paginationEl = document.getElementById('deptsPaginationText');
  if (paginationEl) {
    paginationEl.innerHTML = `Showing <span class="font-semibold text-slate-700 dark:text-slate-200">${startIndex + 1}</span> to <span class="font-semibold text-slate-700 dark:text-slate-200">${endIndex}</span> of <span class="font-semibold text-slate-700 dark:text-slate-200">${totalItems}</span> records`;
  }

  renderDeptsPaginationControls(totalItems, totalPages, currentDeptPage);
}

// Render Department Pagination Controls
function renderDeptsPaginationControls(totalItems, totalPages, currentPage) {
  const container = document.getElementById('deptsPaginationControls');
  if (!container) return;

  const isPrevDisabled = currentPage <= 1 || totalItems === 0;
  const isNextDisabled = currentPage >= totalPages || totalItems === 0;

  let html = `
    <!-- Previous Button -->
    <button type="button" class="${isPrevDisabled ? 'px-3 py-1.5 text-xs font-medium text-slate-300 dark:text-slate-600 bg-slate-50/50 dark:bg-slate-800/30 cursor-not-allowed' : 'px-3 py-1.5 text-xs font-medium text-slate-500 hover:text-slate-700 hover:bg-slate-50 dark:text-slate-400 dark:hover:text-slate-200 dark:hover:bg-slate-800/60 transition cursor-pointer'}" 
            ${isPrevDisabled ? 'disabled' : ''} onclick="window.changeDeptPage(${currentPage - 1})" aria-label="Previous Page">
      <i class="fa-solid fa-chevron-left text-[10px]"></i>
    </button>
  `;

  if (totalItems === 0) {
    html += `
      <button type="button" class="px-3.5 py-1.5 text-xs font-bold text-sky-600 dark:text-sky-400 bg-sky-50/80 dark:bg-sky-950/40 border border-sky-300 dark:border-sky-600/60 z-10 cursor-default" disabled>1</button>
    `;
  } else {
    let pages = [];
    if (totalPages <= 7) {
      for (let i = 1; i <= totalPages; i++) pages.push(i);
    } else if (currentPage <= 4) {
      pages = [1, 2, 3, 4, 5, '...', totalPages];
    } else if (currentPage >= totalPages - 3) {
      pages = [1, '...', totalPages - 4, totalPages - 3, totalPages - 2, totalPages - 1, totalPages];
    } else {
      pages = [1, '...', currentPage - 1, currentPage, currentPage + 1, '...', totalPages];
    }

    pages.forEach(p => {
      if (p === '...') {
        html += `<span class="px-3.5 py-1.5 text-xs font-medium text-slate-400 dark:text-slate-500 border-l border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 select-none">...</span>`;
      } else if (p === currentPage) {
        html += `<button type="button" class="px-3.5 py-1.5 text-xs font-bold text-sky-600 dark:text-sky-400 bg-sky-50/80 dark:bg-sky-950/40 border border-sky-300 dark:border-sky-600/60 z-10 cursor-default" aria-current="page">${p}</button>`;
      } else {
        html += `<button type="button" class="px-3.5 py-1.5 text-xs font-medium text-slate-600 hover:text-slate-900 hover:bg-slate-50 dark:text-slate-400 dark:hover:text-slate-200 dark:hover:bg-slate-800/60 border-l border-slate-200 dark:border-slate-700 transition cursor-pointer" onclick="window.changeDeptPage(${p})">${p}</button>`;
      }
    });
  }

  html += `
    <!-- Next Button -->
    <button type="button" class="${isNextDisabled ? 'px-3 py-1.5 text-xs font-medium text-slate-300 dark:text-slate-600 bg-slate-50/50 dark:bg-slate-800/30 cursor-not-allowed border-l border-slate-200 dark:border-slate-700' : 'px-3 py-1.5 text-xs font-medium text-slate-500 hover:text-slate-700 hover:bg-slate-50 dark:text-slate-400 dark:hover:text-slate-200 dark:hover:bg-slate-800/60 border-l border-slate-200 dark:border-slate-700 transition cursor-pointer'}" 
            ${isNextDisabled ? 'disabled' : ''} onclick="window.changeDeptPage(${currentPage + 1})" aria-label="Next Page">
      <i class="fa-solid fa-chevron-right text-[10px]"></i>
    </button>
  `;

  container.innerHTML = html;
}

// Update Statistics Cards
function updateSummary() {
  const total = departmentsData.length;
  const assignedAdmins = departmentsData.filter(d => d.department_head_user_id).length;

  document.getElementById('statTotalDepts').innerText = `${total} Defined`;
  document.getElementById('statAssignedAdmins').innerText = `${assignedAdmins} Assigned`;
}
