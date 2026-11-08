// ACCOUNT STATUS UI

// PAGINATION STATE
let currentAccountPage = 1;
const accountPageSize = 10;
let activeFilteredAccounts = [];

window.currentAccountPage = currentAccountPage;

window.resetAccountStatusPage = function() {
  currentAccountPage = 1;
  window.currentAccountPage = 1;
};

window.changeAccountStatusPage = function(targetPage) {
  currentAccountPage = targetPage;
  window.currentAccountPage = targetPage;
  renderTable(activeFilteredAccounts);
};

// RENDER TABLE
function renderTable(usersList = accountUsers) {
  activeFilteredAccounts = usersList;
  const tbody = document.getElementById('statusTableBody');
  if (!tbody) return;
  tbody.innerHTML = '';

  const totalItems = activeFilteredAccounts.length;
  const totalPages = Math.ceil(totalItems / accountPageSize) || 1;

  if (currentAccountPage > totalPages) currentAccountPage = totalPages;
  if (currentAccountPage < 1) currentAccountPage = 1;
  window.currentAccountPage = currentAccountPage;
  
  if (totalItems === 0) {
    tbody.innerHTML = `
      <tr>
        <td colspan="4" class="px-6 py-12 text-center text-slate-400 font-semibold">
          <i class="fa-solid fa-user-slash text-3xl mb-3 block opacity-60"></i>
          No profiles matched your filter criteria.
        </td>
      </tr>
    `;
    const pagText = document.getElementById('statusPaginationText');
    if (pagText) pagText.innerText = "Showing 0 to 0 of 0 accounts";
    renderAccountPaginationControls(0, 1, 1);
    return;
  }

  const startIndex = (currentAccountPage - 1) * accountPageSize;
  const endIndex = Math.min(startIndex + accountPageSize, totalItems);
  const pageUsers = activeFilteredAccounts.slice(startIndex, endIndex);

  pageUsers.forEach(user => {
    let statusStyles = '';
    let dotPulse = '';
    
    if (user.status === 'Active') {
      statusStyles = 'bg-emerald-50 text-emerald-700 border-emerald-150';
      dotPulse = '<span class="h-1.5 w-1.5 rounded-full bg-emerald-500 animate-pulse shrink-0"></span>';
    } else if (user.status === 'Deactivated' || user.status === 'Inactive') {
      statusStyles = 'bg-slate-50 text-slate-600 border-slate-200';
      dotPulse = '<span class="h-1.5 w-1.5 rounded-full bg-slate-400 shrink-0"></span>';
    } else if (user.status === 'Locked') {
      statusStyles = 'bg-rose-50 text-rose-700 border-rose-150';
      dotPulse = '<i class="fa-solid fa-lock text-[9px] shrink-0 text-rose-500"></i>';
    } else if (user.status === 'Archived') {
      statusStyles = 'bg-amber-50 text-amber-700 border-amber-150';
      dotPulse = '<i class="fa-solid fa-box-archive text-[9px] shrink-0 text-amber-500"></i>';
    }

    let attemptsText = `${user.failedAttempts}/5`;
    if (user.failedAttempts >= 5 || user.status === 'Locked') {
      attemptsText = '<span class="text-rose-600 font-bold flex items-center gap-1"><i class="fa-solid fa-lock text-[9px]"></i> Locked</span>';
    }

    // Status switch details
    const isChecked = user.status === 'Active' ? 'checked' : '';
    const isToggleDisabled = (user.status === 'Locked' || user.status === 'Archived') ? 'disabled opacity-50 cursor-not-allowed' : '';

    const lockBtnIcon = user.status === 'Locked' ? 'fa-lock text-rose-600 hover:bg-rose-50 border-rose-100' : 'fa-lock-open text-slate-400 hover:text-slate-700 hover:bg-slate-50 border-transparent';
    const lockBtnTitle = user.status === 'Locked' ? 'Unlock Account' : 'Suspend Account (Lock)';

    const archiveBtnIcon = user.status === 'Archived' ? 'fa-arrow-rotate-left text-amber-600 hover:bg-amber-50 hover:border-amber-150' : 'fa-box-archive text-slate-400 hover:text-rose-600 hover:bg-rose-50 border-transparent';
    const archiveBtnTitle = user.status === 'Archived' ? 'Restore Profile' : 'Archive Profile';

    const colors = ['bg-blue-500', 'bg-indigo-500', 'bg-purple-500', 'bg-rose-500', 'bg-emerald-500', 'bg-slate-500', 'bg-amber-600'];
    let colorIndex = (user.name.charCodeAt(0) + user.name.charCodeAt(user.name.length - 1)) % colors.length;
    let avatarBg = colors[colorIndex];

    let avatarHTML = `
      <div class="h-9 w-9 rounded-none ${avatarBg} text-white font-extrabold flex items-center justify-center text-xs shadow-xs border border-white/20 shrink-0">
        ${user.initials}
      </div>
    `;
    if (user.profile_picture && user.profile_picture !== 'default-avatar.png') {
      let pic = user.profile_picture;
      if (!pic.startsWith('http') && !pic.startsWith('data:')) {
        const bPath = window.civentralBasePath || '../../';
        pic = bPath + pic.replace(/^\/+/, '');
      }
      avatarHTML = `<img src="${pic}" alt="${user.name}" class="h-9 w-9 rounded-none object-cover border border-slate-200 shrink-0">`;
    }

    const row = `
      <tr class="hover:bg-slate-50/60 dark:hover:bg-slate-800/40 transition group">
        <!-- User Details -->
        <td class="py-3 px-4 border-b border-slate-100 dark:border-slate-800/60 align-middle">
          <div class="flex items-center space-x-3">
            ${avatarHTML}
            <div class="flex flex-col">
              <span class="font-semibold text-slate-900 dark:text-slate-100 text-xs tracking-tight leading-snug">${user.name}</span>
              <span class="text-[11px] font-normal text-slate-500 dark:text-slate-400 font-mono">${user.id} &bull; ${user.position}</span>
            </div>
          </div>
        </td>

        <!-- Current Status Badge -->
        <td class="py-3 px-4 border-b border-slate-100 dark:border-slate-800/60 align-middle">
          <span class="text-[10px] font-bold uppercase px-2.5 py-1 rounded-none border ${statusStyles} inline-flex items-center gap-1.5">
            ${dotPulse}
            <span>${(user.status === 'Deactivated' || user.status === 'Inactive') ? 'Inactive' : user.status}</span>
          </span>
        </td>

        <!-- Security Health Indicators -->
        <td class="py-3 px-4 border-b border-slate-100 dark:border-slate-800/60 align-middle space-y-1">
          <div class="flex items-center gap-1.5 text-slate-650">
            <span class="text-[9px] font-bold text-slate-400 uppercase">Last Login:</span>
            <span class="font-normal text-[11px] text-slate-600 dark:text-slate-300">${user.lastLogin}</span>
          </div>
          <div class="flex items-center gap-1.5 text-slate-650">
            <span class="text-[9px] font-bold text-slate-400 uppercase">Failed attempts:</span>
            <span class="font-normal text-[11px] text-slate-600 dark:text-slate-300">${attemptsText}</span>
          </div>
        </td>

        <!-- Action Controls -->
        <td class="py-3 px-4 text-right whitespace-nowrap border-b border-slate-100 dark:border-slate-800/60 align-middle">
          <div class="inline-flex items-center space-x-3">
            
            <!-- iOS-style Status Toggle Switch -->
            <label class="relative inline-flex items-center ${isToggleDisabled ? 'cursor-not-allowed' : 'cursor-pointer'} select-none" title="Activate/Deactivate toggle">
              <input type="checkbox" ${isChecked} ${isToggleDisabled} onchange="if(typeof handleStatusToggle === 'function') handleStatusToggle('${user.id}', this)" class="sr-only peer">
              <div class="w-8 h-4.5 bg-slate-200 rounded-full peer peer-focus:ring-2 peer-focus:ring-brand-medium/20 peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-slate-300 after:border after:rounded-full after:h-3.5 after:w-3.5 after:transition-all peer-checked:bg-emerald-500"></div>
            </label>

            <!-- Lock/Unlock toggle button -->
            <button onclick="if(typeof triggerLockToggle === 'function') triggerLockToggle('${user.id}')" class="border p-1.5 rounded-none transition cursor-pointer flex items-center justify-center ${lockBtnIcon}" title="${lockBtnTitle}">
              <i class="fa-solid ${user.status === 'Locked' ? 'fa-lock' : 'fa-lock-open'} text-xs"></i>
            </button>

            <!-- Archive/Restore toggle button -->
            <button onclick="if(typeof triggerArchiveToggle === 'function') triggerArchiveToggle('${user.id}')" class="border p-1.5 rounded-none transition cursor-pointer flex items-center justify-center ${archiveBtnIcon}" title="${archiveBtnTitle}">
              <i class="fa-solid ${user.status === 'Archived' ? 'fa-arrow-rotate-left' : 'fa-box-archive'} text-xs"></i>
            </button>

          </div>
        </td>
      </tr>
    `;
    tbody.innerHTML += row;
  });

  const pagText = document.getElementById('statusPaginationText');
  if (pagText) pagText.innerText = `Showing ${startIndex + 1} to ${endIndex} of ${totalItems} accounts`;
  renderAccountPaginationControls(totalItems, totalPages, currentAccountPage);
}

// RENDER ACCOUNT STATUS PAGINATION CONTROLS
function renderAccountPaginationControls(totalItems, totalPages, currentPage) {
  const prevBtn = document.getElementById('statusPrevBtn');
  const nextBtn = document.getElementById('statusNextBtn');
  const pageNumbersContainer = document.getElementById('statusPageNumbers');

  if (prevBtn) {
    const isPrevDisabled = currentPage <= 1 || totalItems === 0;
    prevBtn.disabled = isPrevDisabled;
    if (isPrevDisabled) {
      prevBtn.className = "px-3 py-1.5 rounded-none border border-slate-200 bg-white text-slate-300 cursor-not-allowed transition";
    } else {
      prevBtn.className = "px-3 py-1.5 rounded-none border border-slate-200 bg-white hover:bg-slate-50 text-slate-600 cursor-pointer transition";
    }
    prevBtn.onclick = () => {
      if (!isPrevDisabled) window.changeAccountStatusPage(currentPage - 1);
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
      if (!isNextDisabled) window.changeAccountStatusPage(currentPage + 1);
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
      firstBtn.onclick = () => window.changeAccountStatusPage(1);
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
      pageBtn.onclick = () => window.changeAccountStatusPage(p);
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
      lastBtn.onclick = () => window.changeAccountStatusPage(totalPages);
      pageNumbersContainer.appendChild(lastBtn);
    }
  }
}

// UPDATE FILTER COUNTS
function updateCounts() {
  const all = accountUsers.length;
  const active = accountUsers.filter(u => u.status === 'Active').length;
  const deactivated = accountUsers.filter(u => u.status === 'Deactivated' || u.status === 'Inactive').length;
  const locked = accountUsers.filter(u => u.status === 'Locked').length;
  const archived = accountUsers.filter(u => u.status === 'Archived').length;

  const countAll = document.getElementById('tabCountAll');
  const countActive = document.getElementById('tabCountActive');
  const countDeactivated = document.getElementById('tabCountDeactivated');
  const countLocked = document.getElementById('tabCountLocked');
  const countArchived = document.getElementById('tabCountArchived');

  if (countAll) countAll.innerText = all;
  if (countActive) countActive.innerText = active;
  if (countDeactivated) countDeactivated.innerText = deactivated;
  if (countLocked) countLocked.innerText = locked;
  if (countArchived) countArchived.innerText = archived;
}

// EXPORT TO CSV
function exportStatusList() {
  const searchInput = document.getElementById('statusSearchInput');
  const query = searchInput ? searchInput.value.toLowerCase().trim() : '';
  const activeTab = window.activeStatusTab;

  let csvContent = "data:text/csv;charset=utf-8,";
  csvContent += "Employee ID,Full Name,Email Address,Operational Status,Last Authentication,Failed Counters\r\n";

  const filtered = accountUsers.filter(user => {
    const matchesSearch = user.name.toLowerCase().includes(query) ||
                          user.email.toLowerCase().includes(query) ||
                          user.id.toLowerCase().includes(query);
    let matchesTab = true;
    if (activeTab !== 'all') {
      if (activeTab === 'Deactivated') {
        matchesTab = user.status === 'Deactivated' || user.status === 'Inactive';
      } else {
        matchesTab = user.status === activeTab;
      }
    }
    return matchesSearch && matchesTab;
  });

  filtered.forEach(user => {
    const row = `"${user.id}","${user.name}","${user.email}","${user.status}","${user.lastLogin}","${user.failedAttempts}"`;
    csvContent += row + "\r\n";
  });

  const encodedUri = encodeURI(csvContent);
  const link = document.createElement("a");
  link.setAttribute("href", encodedUri);
  link.setAttribute("download", `LGU_Account_Status_Roster_${new Date().toISOString().split('T')[0]}.csv`);
  document.body.appendChild(link);
  
  link.click();
  document.body.removeChild(link);

  if (typeof showToast === 'function') showToast(`Successfully compiled and exported ${filtered.length} credentials records.`);
}
