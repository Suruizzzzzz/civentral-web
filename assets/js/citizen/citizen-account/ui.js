// CITIZEN ACCOUNT UI

// PAGINATION STATE
let currentCtrlPage = 1;
const ctrlPageSize = 10;
let activeFilteredCtrl = [];

window.currentCtrlPage = currentCtrlPage;

window.resetControlPage = function() {
  currentCtrlPage = 1;
  window.currentCtrlPage = 1;
};

window.changeControlPage = function(targetPage) {
  currentCtrlPage = targetPage;
  window.currentCtrlPage = targetPage;
  renderControlTable(activeFilteredCtrl);
};

// RENDER CONTROL TABLE
function renderControlTable(list = ctrlCitizens) {
  activeFilteredCtrl = list;
  const ctrlTbody = document.getElementById('controlTableBody');
  if (!ctrlTbody) return;
  ctrlTbody.innerHTML = '';

  const totalItems = activeFilteredCtrl.length;
  const totalPages = Math.ceil(totalItems / ctrlPageSize) || 1;

  if (currentCtrlPage > totalPages) currentCtrlPage = totalPages;
  if (currentCtrlPage < 1) currentCtrlPage = 1;
  window.currentCtrlPage = currentCtrlPage;

  if (totalItems === 0) {
    ctrlTbody.innerHTML = `
      <tr>
        <td colspan="5" class="px-6 py-12 text-center text-slate-400 font-semibold">
          <i class="fa-solid fa-shield-halved text-3xl mb-3 block opacity-60"></i>
          No records matched the filter criteria.
        </td>
      </tr>
    `;
    const pagText = document.getElementById('ctrlPaginationText');
    if (pagText) {
      pagText.innerHTML = `Showing <span class="font-semibold text-slate-700 dark:text-slate-200">0</span> to <span class="font-semibold text-slate-700 dark:text-slate-200">0</span> of <span class="font-semibold text-slate-700 dark:text-slate-200">0</span> records`;
    }
    renderControlPaginationControls(0, 1, 1);
    return;
  }

  const startIndex = (currentCtrlPage - 1) * ctrlPageSize;
  const endIndex = Math.min(startIndex + ctrlPageSize, totalItems);
  const pageItems = activeFilteredCtrl.slice(startIndex, endIndex);

  pageItems.forEach(cit => {
    // Badges
    let badgeClass = '';
    let iconIndicator = '';

    if (cit.status === 'Active') {
      badgeClass = 'bg-emerald-50 text-emerald-700 border-emerald-150';
      iconIndicator = '<span class="h-1.5 w-1.5 rounded-none bg-emerald-500 animate-pulse shrink-0"></span>';
    } else if (cit.status === 'Inactive') {
      badgeClass = 'bg-slate-50 text-slate-500 border-slate-200';
      iconIndicator = '<span class="h-1.5 w-1.5 rounded-none bg-slate-400 shrink-0"></span>';
    } else if (cit.status === 'Locked') {
      badgeClass = 'bg-rose-50 text-rose-700 border-rose-150';
      iconIndicator = '<i class="fa-solid fa-lock text-[9px] text-rose-500 shrink-0"></i>';
    } else if (cit.status === 'Suspended') {
      badgeClass = 'bg-amber-50 text-amber-700 border-amber-150';
      iconIndicator = '<i class="fa-solid fa-triangle-exclamation text-[9px] text-amber-600 shrink-0"></i>';
    }

    // Violation logs styling
    const violationClass = cit.flagged ? 'text-rose-600 font-bold' : 'text-slate-500 font-medium';

    const row = `
      <tr class="hover:bg-slate-50/60 dark:hover:bg-slate-800/40 transition group">
        <!-- Citizen ID -->
        <td class="py-3 px-4 font-mono text-[11px] font-normal text-slate-500 dark:text-slate-400 tracking-tight select-all border-b border-slate-100 dark:border-slate-800/60 align-middle">
          ${cit.id}
        </td>

        <!-- Citizen User -->
        <td class="py-3 px-4 border-b border-slate-100 dark:border-slate-800/60 align-middle">
          <div class="flex flex-col">
            <span class="font-semibold text-slate-900 dark:text-slate-100 text-xs tracking-tight leading-snug">${cit.name}</span>
            <span class="text-[11px] font-normal text-slate-500 dark:text-slate-400">${cit.email}</span>
          </div>
        </td>

        <!-- Security Status -->
        <td class="py-3 px-4 border-b border-slate-100 dark:border-slate-800/60 align-middle">
          <span class="text-[10px] font-bold uppercase px-2 py-0.5 rounded-none border ${badgeClass} inline-flex items-center gap-1.5">
            ${iconIndicator}
            <span>${cit.status}</span>
          </span>
        </td>

        <!-- Violations logs -->
        <td class="py-3 px-4 border-b border-slate-100 dark:border-slate-800/60 align-middle">
          <span class="text-xs ${violationClass}">${cit.violations}</span>
        </td>

        <!-- State Controller Actions -->
        <td class="py-3 px-4 text-right whitespace-nowrap border-b border-slate-100 dark:border-slate-800/60 align-middle">
          <div class="inline-flex items-center space-x-2.5">
            <!-- Quick Action Selector Dropdown -->
            <select onchange="if(typeof triggerStateChange === 'function') triggerStateChange('${cit.id}', this)" class="border border-slate-200 dark:border-slate-700 rounded-none px-2 py-1 text-[11px] bg-slate-50 dark:bg-slate-800 hover:bg-white dark:hover:bg-slate-700/60 text-slate-650 dark:text-slate-300 font-bold focus:outline-none transition cursor-pointer select-none">
              <option value="">Choose Action</option>
              <option value="Activate">Activate</option>
              <option value="Deactivate">Deactivate</option>
              <option value="Lock">Lock Account</option>
              <option value="Unlock">Unlock Account</option>
              <option value="Suspend">Suspend Profile</option>
              <option value="Restore">Restore Defaults</option>
            </select>
          </div>
        </td>
      </tr>
    `;
    ctrlTbody.innerHTML += row;
  });

  const pagText = document.getElementById('ctrlPaginationText');
  if (pagText) {
    pagText.innerHTML = `Showing <span class="font-semibold text-slate-700 dark:text-slate-200">${startIndex + 1}</span> to <span class="font-semibold text-slate-700 dark:text-slate-200">${endIndex}</span> of <span class="font-semibold text-slate-700 dark:text-slate-200">${totalItems}</span> records`;
  }

  renderControlPaginationControls(totalItems, totalPages, currentCtrlPage);
}

// RENDER CONTROL PAGINATION CONTROLS
function renderControlPaginationControls(totalItems, totalPages, currentPage) {
  const container = document.getElementById('ctrlPaginationControls');
  if (!container) return;

  const isPrevDisabled = currentPage <= 1 || totalItems === 0;
  const isNextDisabled = currentPage >= totalPages || totalItems === 0;

  let html = `
    <!-- Previous Button -->
    <button type="button" class="${isPrevDisabled ? 'px-3 py-1.5 text-xs font-medium text-slate-300 dark:text-slate-600 bg-slate-50/50 dark:bg-slate-800/30 cursor-not-allowed' : 'px-3 py-1.5 text-xs font-medium text-slate-500 hover:text-slate-700 hover:bg-slate-50 dark:text-slate-400 dark:hover:text-slate-200 dark:hover:bg-slate-800/60 transition cursor-pointer'}" 
            ${isPrevDisabled ? 'disabled' : ''} onclick="window.changeControlPage(${currentPage - 1})" aria-label="Previous Page">
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
        html += `<button type="button" class="px-3.5 py-1.5 text-xs font-medium text-slate-600 hover:text-slate-900 hover:bg-slate-50 dark:text-slate-400 dark:hover:text-slate-200 dark:hover:bg-slate-800/60 border-l border-slate-200 dark:border-slate-700 transition cursor-pointer" onclick="window.changeControlPage(${p})">${p}</button>`;
      }
    });
  }

  html += `
    <!-- Next Button -->
    <button type="button" class="${isNextDisabled ? 'px-3 py-1.5 text-xs font-medium text-slate-300 dark:text-slate-600 bg-slate-50/50 dark:bg-slate-800/30 cursor-not-allowed border-l border-slate-200 dark:border-slate-700' : 'px-3 py-1.5 text-xs font-medium text-slate-500 hover:text-slate-700 hover:bg-slate-50 dark:text-slate-400 dark:hover:text-slate-200 dark:hover:bg-slate-800/60 border-l border-slate-200 dark:border-slate-700 transition cursor-pointer'}" 
            ${isNextDisabled ? 'disabled' : ''} onclick="window.changeControlPage(${currentPage + 1})" aria-label="Next Page">
      <i class="fa-solid fa-chevron-right text-[10px]"></i>
    </button>
  `;

  container.innerHTML = html;
}

// UPDATE STATUS RIBBON STATISTICS
function updateControlStats() {
  const activeCount = ctrlCitizens.filter(c => c.status === 'Active').length;
  const inactiveCount = ctrlCitizens.filter(c => c.status === 'Inactive').length;
  const lockedCount = ctrlCitizens.filter(c => c.status === 'Locked').length;
  const suspendedCount = ctrlCitizens.filter(c => c.status === 'Suspended').length;

  const stActive = document.getElementById('statActiveCount');
  const stInactive = document.getElementById('statInactiveCount');
  const stLocked = document.getElementById('statLockedCount');
  const stSuspended = document.getElementById('statSuspendedCount');

  if (stActive) stActive.innerText = `${activeCount} Accounts`;
  if (stInactive) stInactive.innerText = `${inactiveCount} Accounts`;
  if (stLocked) stLocked.innerText = `${lockedCount} Accounts`;
  if (stSuspended) stSuspended.innerText = `${suspendedCount} Accounts`;
}
