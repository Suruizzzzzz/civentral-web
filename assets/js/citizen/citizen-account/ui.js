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
    if (pagText) pagText.innerText = "Showing 0 to 0 of 0 accounts";
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
            <select onchange="if(typeof triggerStateChange === 'function') triggerStateChange('${cit.id}', this)" class="border border-slate-200 rounded-none px-2 py-1 text-[11px] bg-slate-50 hover:bg-white text-slate-650 font-bold focus:outline-none transition cursor-pointer select-none">
              <option value="">Choose Action</option>
              <option value="Activate">Activate</option>
              <option value="Deactivate">Deactivate</option>
              <option value="Lock">Lock Account</option>
              <option value="Unlock">Unlock Account</option>
              <option value="Suspend">Suspend Profile</option>
              <option value="Restore">Restore Defaults</option>
            </select>

            <!-- Security Audit Timeline icon -->
            <button onclick="if(typeof openTimelineModal === 'function') openTimelineModal('${cit.id}')" class="text-slate-400 hover:text-[#0f172a] hover:bg-slate-50 p-1.5 rounded-none border border-transparent hover:border-slate-150 transition cursor-pointer" title="View Security Audits">
              <i class="fa-solid fa-clock-rotate-left text-xs"></i>
            </button>

          </div>
        </td>
      </tr>
    `;
    ctrlTbody.innerHTML += row;
  });

  const pagText = document.getElementById('ctrlPaginationText');
  if (pagText) pagText.innerText = `Showing ${startIndex + 1} to ${endIndex} of ${totalItems} accounts`;

  renderControlPaginationControls(totalItems, totalPages, currentCtrlPage);
}

// RENDER CONTROL PAGINATION CONTROLS
function renderControlPaginationControls(totalItems, totalPages, currentPage) {
  const prevBtn = document.getElementById('ctrlPrevBtn');
  const nextBtn = document.getElementById('ctrlNextBtn');
  const pageNumbersContainer = document.getElementById('ctrlPageNumbers');

  if (prevBtn) {
    const isPrevDisabled = currentPage <= 1 || totalItems === 0;
    prevBtn.disabled = isPrevDisabled;
    if (isPrevDisabled) {
      prevBtn.className = "px-3 py-1.5 rounded-none border border-slate-200 bg-white text-slate-300 cursor-not-allowed transition";
    } else {
      prevBtn.className = "px-3 py-1.5 rounded-none border border-slate-200 bg-white hover:bg-slate-50 text-slate-600 cursor-pointer transition";
    }
    prevBtn.onclick = () => {
      if (!isPrevDisabled) window.changeControlPage(currentPage - 1);
    };
  }

  if (nextBtn) {
    const isNextDisabled = currentPage >= totalPages || totalItems === 0;
    nextBtn.disabled = isNextDisabled;
    if (isNextDisabled) {
      nextBtn.className = "px-3 py-1.5 rounded-none border border-slate-200 bg-white text-slate-300 cursor-not-allowed transition";
    } else {
      nextBtn.className = "px-3 py-1.5 rounded-none border border-slate-200 bg-white hover:bg-slate-50 text-slate-600 cursor-pointer transition";
    }
    nextBtn.onclick = () => {
      if (!isNextDisabled) window.changeControlPage(currentPage + 1);
    };
  }

  if (pageNumbersContainer) {
    pageNumbersContainer.innerHTML = '';
    if (totalItems === 0) {
      pageNumbersContainer.innerHTML = `<button class="px-3 py-1.5 rounded-none bg-brand-light border border-brand-border text-brand-dark font-extrabold shadow-2xs" disabled>1</button>`;
      return;
    }

    let startPage = 1;
    let endPage = totalPages;

    if (totalPages > 7) {
      if (currentPage <= 4) {
        startPage = 1;
        endPage = 5;
      } else if (currentPage >= totalPages - 3) {
        startPage = totalPages - 4;
        endPage = totalPages;
      } else {
        startPage = currentPage - 2;
        endPage = currentPage + 2;
      }
    }

    if (startPage > 1) {
      const firstBtn = document.createElement('button');
      firstBtn.className = "px-3 py-1.5 rounded-none border border-slate-200 bg-white hover:bg-slate-50 text-slate-600 font-bold cursor-pointer transition";
      firstBtn.textContent = '1';
      firstBtn.onclick = () => window.changeControlPage(1);
      pageNumbersContainer.appendChild(firstBtn);

      if (startPage > 2) {
        const dots = document.createElement('span');
        dots.className = "px-1.5 text-slate-400 font-bold select-none";
        dots.textContent = '...';
        pageNumbersContainer.appendChild(dots);
      }
    }

    for (let p = startPage; p <= endPage; p++) {
      const pageBtn = document.createElement('button');
      if (p === currentPage) {
        pageBtn.className = "px-3 py-1.5 rounded-none bg-brand-light border border-brand-border text-brand-dark font-extrabold shadow-2xs cursor-pointer";
      } else {
        pageBtn.className = "px-3 py-1.5 rounded-none border border-slate-200 bg-white hover:bg-slate-50 text-slate-600 font-bold cursor-pointer transition";
      }
      pageBtn.textContent = p;
      pageBtn.onclick = () => window.changeControlPage(p);
      pageNumbersContainer.appendChild(pageBtn);
    }

    if (endPage < totalPages) {
      if (endPage < totalPages - 1) {
        const dots = document.createElement('span');
        dots.className = "px-1.5 text-slate-400 font-bold select-none";
        dots.textContent = '...';
        pageNumbersContainer.appendChild(dots);
      }

      const lastBtn = document.createElement('button');
      lastBtn.className = "px-3 py-1.5 rounded-none border border-slate-200 bg-white hover:bg-slate-50 text-slate-600 font-bold cursor-pointer transition";
      lastBtn.textContent = totalPages;
      lastBtn.onclick = () => window.changeControlPage(totalPages);
      pageNumbersContainer.appendChild(lastBtn);
    }
  }
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
