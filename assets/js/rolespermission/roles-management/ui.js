// ROLES MANAGEMENT UI

// PAGINATION STATE
let currentRolePage = 1;
const rolePageSize = 10;
let activeFilteredRoles = [];

window.currentRolePage = currentRolePage;

window.resetRolePage = function() {
  currentRolePage = 1;
  window.currentRolePage = 1;
};

window.changeRolePage = function(targetPage) {
  currentRolePage = targetPage;
  window.currentRolePage = targetPage;
  renderRoles(activeFilteredRoles);
};

// RENDER ROLES TABLE BASED ON DATABASE FIELDS
function renderRoles(dataToRender = systemRoles) {
  activeFilteredRoles = dataToRender;
  const rolesTbody = document.getElementById('rolesTableBody');
  if (!rolesTbody) return;
  rolesTbody.innerHTML = '';

  const totalItems = activeFilteredRoles.length;
  const totalPages = Math.ceil(totalItems / rolePageSize) || 1;

  if (currentRolePage > totalPages) currentRolePage = totalPages;
  if (currentRolePage < 1) currentRolePage = 1;
  window.currentRolePage = currentRolePage;
  
  if (totalItems === 0) {
    rolesTbody.innerHTML = `
      <tr>
        <td colspan="6" class="px-6 py-12 text-center text-slate-400 font-semibold">
          <i class="fa-solid fa-folder-open text-3xl mb-3 block opacity-60"></i>
          No roles match your search filter.
        </td>
      </tr>
    `;
    const paginationEl = document.getElementById('rolesPaginationText');
    if (paginationEl) {
      paginationEl.innerHTML = `Showing <span class="font-semibold text-slate-700 dark:text-slate-200">0</span> to <span class="font-semibold text-slate-700 dark:text-slate-200">0</span> of <span class="font-semibold text-slate-700 dark:text-slate-200">0</span> records`;
    }
    renderRolesPaginationControls(0, 1, 1);
    if (typeof updateMetrics === 'function') updateMetrics();
    return;
  }

  const startIndex = (currentRolePage - 1) * rolePageSize;
  const endIndex = Math.min(startIndex + rolePageSize, totalItems);
  const pageItems = activeFilteredRoles.slice(startIndex, endIndex);

  const isSuperAdmin = currentUserScope ? !!currentUserScope.is_superadmin : false;
  const grantedActions = currentUserScope ? (currentUserScope.granted_actions || []) : [];
  const canEdit = isSuperAdmin || grantedActions.includes('EDIT');

  pageItems.forEach(role => {
    // Status Badge classes
    const statusStyles = role.status === 'Active' 
      ? 'bg-emerald-50 text-emerald-700 border-emerald-200' 
      : 'bg-slate-100 text-slate-600 border-slate-200';
    
    const dotPulse = role.status === 'Active' 
      ? '<span class="h-1.5 w-1.5 rounded-full bg-emerald-500 animate-pulse"></span>' 
      : '<span class="h-1.5 w-1.5 rounded-full bg-slate-400"></span>';

    // Global Access Badge
    const globalAccessBadge = role.is_global_access
      ? `<span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-none text-[10px] font-black bg-emerald-50 text-emerald-700 border border-emerald-200"><i class="fa-solid fa-globe text-[9px]"></i> Global Access</span>`
      : `<span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-none text-[10px] font-bold bg-slate-100 text-slate-600 border border-slate-200"><i class="fa-solid fa-building text-[9px]"></i> Department Scoped</span>`;

    // System Role Protection Badge
    const systemRoleBadge = role.is_system_role
      ? `<span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-none text-[9px] font-black bg-amber-50 text-amber-700 border border-amber-200" title="Protected System Role"><i class="fa-solid fa-lock text-[8px]"></i> System Core</span>`
      : `<span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-none text-[9px] font-bold bg-slate-100 text-slate-600 border border-slate-200">Custom Role</span>`;

    const isChecked = role.status === 'Active' ? 'checked' : '';

    const tr = document.createElement('tr');
    tr.className = 'hover:bg-slate-50/50 dark:hover:bg-slate-800/30 transition';
    tr.innerHTML = `
      <!-- Role Designation & Prefix -->
      <td class="py-3 px-4 text-xs text-slate-600 dark:text-slate-300 border-b border-slate-100 dark:border-slate-800/60 align-middle">
        <div class="flex items-center gap-3">
          <div class="h-9 min-w-[2.25rem] px-2.5 rounded-none bg-gradient-to-br from-brand-light to-blue-50 border border-brand-border/80 flex items-center justify-center text-brand-dark shrink-0 font-mono font-bold text-xs tracking-wider shadow-2xs">
            ${role.role_prefix}
          </div>
          <div class="flex items-center gap-2">
            <span class="font-semibold text-slate-900 dark:text-slate-100 text-xs">${role.role_name}</span>
            ${systemRoleBadge}
          </div>
        </div>
      </td>
      
      <!-- Scope & Access Level -->
      <td class="py-3 px-4 text-xs text-slate-600 dark:text-slate-300 border-b border-slate-100 dark:border-slate-800/60 align-middle">
        <div class="space-y-1 max-w-xs">
          ${role.description ? `<p class="text-[11px] font-normal text-slate-500 dark:text-slate-400 leading-relaxed">${role.description}</p>` : `<span class="italic text-[11px] text-slate-400 dark:text-slate-500">No description</span>`}
          <div class="pt-0.5">
            ${globalAccessBadge}
          </div>
        </div>
      </td>

      <!-- Created At -->
      <td class="py-3 px-4 text-[11px] font-normal text-slate-500 dark:text-slate-400 border-b border-slate-100 dark:border-slate-800/60 align-middle font-mono">
        ${role.created_at}
      </td>

      <!-- Status Pill -->
      <td class="py-3 px-4 text-center border-b border-slate-100 dark:border-slate-800/60 align-middle">
        <span class="text-[10px] font-bold uppercase px-2.5 py-1 rounded-none border ${statusStyles} inline-flex items-center gap-1.5">
          ${dotPulse}
          <span>${role.status}</span>
        </span>
      </td>

      <!-- Actions -->
      <td class="py-3 px-4 text-right whitespace-nowrap border-b border-slate-100 dark:border-slate-800/60 align-middle">
        <div class="inline-flex items-center space-x-3">
          ${canEdit ? `
          <!-- iOS Status Switch Toggle -->
          <label class="relative inline-flex items-center cursor-pointer select-none ${role.is_system_role ? 'opacity-50 cursor-not-allowed' : ''}" title="${role.is_system_role ? 'System roles cannot be deactivated' : 'Activate/Deactivate toggle'}">
            <input type="checkbox" ${isChecked} ${role.is_system_role ? 'disabled' : ''} onchange="handleRoleStatusToggle(${role.role_id}, this)" class="sr-only peer">
            <div class="w-8 h-4.5 bg-slate-200 rounded-full peer peer-focus:ring-2 peer-focus:ring-brand-medium/20 peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-slate-300 after:border after:rounded-full after:h-3.5 after:w-3.5 after:transition-all peer-checked:bg-emerald-600"></div>
          </label>

          <!-- Edit button -->
          <button type="button" onclick="if(typeof openEditModal === 'function') openEditModal(${role.role_id})" class="w-7 h-7 flex items-center justify-center border rounded-none transition cursor-pointer shadow-2xs border-amber-300 bg-amber-50 text-amber-600 hover:bg-amber-100 hover:text-amber-700 dark:border-amber-800/80 dark:bg-amber-950/40 dark:text-amber-400 dark:hover:bg-amber-900/60 dark:hover:text-amber-300" title="Edit Role Parameters">
            <i class="fa-solid fa-pen text-xs"></i>
          </button>` : `<span class="text-[10px] text-slate-400 font-bold italic">Read-only</span>`}
        </div>
      </td>
    `;
    rolesTbody.appendChild(tr);
  });

  const paginationEl = document.getElementById('rolesPaginationText');
  if (paginationEl) {
    paginationEl.innerHTML = `Showing <span class="font-semibold text-slate-700 dark:text-slate-200">${startIndex + 1}</span> to <span class="font-semibold text-slate-700 dark:text-slate-200">${endIndex}</span> of <span class="font-semibold text-slate-700 dark:text-slate-200">${totalItems}</span> records`;
  }

  renderRolesPaginationControls(totalItems, totalPages, currentRolePage);
  if (typeof updateMetrics === 'function') updateMetrics();
}

// RENDER ROLES PAGINATION CONTROLS
function renderRolesPaginationControls(totalItems, totalPages, currentPage) {
  const container = document.getElementById('rolesPaginationControls');
  if (!container) return;

  const isPrevDisabled = currentPage <= 1 || totalItems === 0;
  const isNextDisabled = currentPage >= totalPages || totalItems === 0;

  let html = `
    <!-- Previous Button -->
    <button type="button" class="${isPrevDisabled ? 'px-3 py-1.5 text-xs font-medium text-slate-300 dark:text-slate-600 bg-slate-50/50 dark:bg-slate-800/30 cursor-not-allowed' : 'px-3 py-1.5 text-xs font-medium text-slate-500 hover:text-slate-700 hover:bg-slate-50 dark:text-slate-400 dark:hover:text-slate-200 dark:hover:bg-slate-800/60 transition cursor-pointer'}" 
            ${isPrevDisabled ? 'disabled' : ''} onclick="window.changeRolePage(${currentPage - 1})" aria-label="Previous Page">
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
        html += `<button type="button" class="px-3.5 py-1.5 text-xs font-medium text-slate-600 hover:text-slate-900 hover:bg-slate-50 dark:text-slate-400 dark:hover:text-slate-200 dark:hover:bg-slate-800/60 border-l border-slate-200 dark:border-slate-700 transition cursor-pointer" onclick="window.changeRolePage(${p})">${p}</button>`;
      }
    });
  }

  html += `
    <!-- Next Button -->
    <button type="button" class="${isNextDisabled ? 'px-3 py-1.5 text-xs font-medium text-slate-300 dark:text-slate-600 bg-slate-50/50 dark:bg-slate-800/30 cursor-not-allowed border-l border-slate-200 dark:border-slate-700' : 'px-3 py-1.5 text-xs font-medium text-slate-500 hover:text-slate-700 hover:bg-slate-50 dark:text-slate-400 dark:hover:text-slate-200 dark:hover:bg-slate-800/60 border-l border-slate-200 dark:border-slate-700 transition cursor-pointer'}" 
            ${isNextDisabled ? 'disabled' : ''} onclick="window.changeRolePage(${currentPage + 1})" aria-label="Next Page">
      <i class="fa-solid fa-chevron-right text-[10px]"></i>
    </button>
  `;

  container.innerHTML = html;
}

// UPDATE SUMMARY STATISTICS CARDS
function updateMetrics() {
  const total = systemRoles.length;
  const globalCount = systemRoles.filter(r => r.is_global_access).length;
  const activeCount = systemRoles.filter(r => r.status === 'Active').length;
  const systemCount = systemRoles.filter(r => r.is_system_role).length;

  const totalEl = document.getElementById('statTotalRoles');
  const globalEl = document.getElementById('statGlobalRoles');
  const activeEl = document.getElementById('statActiveRoles');
  const systemEl = document.getElementById('statSystemRoles');

  if (totalEl) totalEl.innerText = total;
  if (globalEl) globalEl.innerText = globalCount;
  if (activeEl) activeEl.innerText = activeCount;
  if (systemEl) systemEl.innerText = systemCount;
}
