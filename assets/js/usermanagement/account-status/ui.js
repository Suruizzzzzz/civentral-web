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
    if (pagText) {
      pagText.innerHTML = `Showing <span class="font-semibold text-slate-700 dark:text-slate-200">0</span> to <span class="font-semibold text-slate-700 dark:text-slate-200">0</span> of <span class="font-semibold text-slate-700 dark:text-slate-200">0</span> records`;
    }
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

    const lockBtnTitle = user.status === 'Locked' ? 'Unlock Account' : 'Suspend Account (Lock)';
    const lockBtnTheme = user.status === 'Locked'
      ? 'border-emerald-300 bg-emerald-50 text-emerald-600 hover:bg-emerald-100 hover:text-emerald-700 dark:border-emerald-800/80 dark:bg-emerald-950/40 dark:text-emerald-400 dark:hover:bg-emerald-900/60 dark:hover:text-emerald-300'
      : 'border-rose-300 bg-rose-50 text-rose-600 hover:bg-rose-100 hover:text-rose-700 dark:border-rose-800/80 dark:bg-rose-950/40 dark:text-rose-400 dark:hover:bg-rose-900/60 dark:hover:text-rose-300';

    const archiveBtnTitle = user.status === 'Archived' ? 'Restore Profile' : 'Archive Profile';
    const archiveBtnTheme = user.status === 'Archived'
      ? 'border-emerald-300 bg-emerald-50 text-emerald-600 hover:bg-emerald-100 hover:text-emerald-700 dark:border-emerald-800/80 dark:bg-emerald-950/40 dark:text-emerald-400 dark:hover:bg-emerald-900/60 dark:hover:text-emerald-300'
      : 'border-rose-300 bg-rose-50 text-rose-600 hover:bg-rose-100 hover:text-rose-700 dark:border-rose-800/80 dark:bg-rose-950/40 dark:text-rose-400 dark:hover:bg-rose-900/60 dark:hover:text-rose-300';

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
            <button onclick="if(typeof triggerLockToggle === 'function') triggerLockToggle('${user.id}')" class="w-7 h-7 flex items-center justify-center border rounded-none transition cursor-pointer shadow-2xs ${lockBtnTheme}" title="${lockBtnTitle}">
              <i class="fa-solid ${user.status === 'Locked' ? 'fa-lock' : 'fa-lock-open'} text-xs"></i>
            </button>

            <!-- Archive/Restore toggle button -->
            <button onclick="if(typeof triggerArchiveToggle === 'function') triggerArchiveToggle('${user.id}')" class="w-7 h-7 flex items-center justify-center border rounded-none transition cursor-pointer shadow-2xs ${archiveBtnTheme}" title="${archiveBtnTitle}">
              <i class="fa-solid ${user.status === 'Archived' ? 'fa-rotate-left' : 'fa-box-archive'} text-xs"></i>
            </button>

          </div>
        </td>
      </tr>
    `;
    tbody.innerHTML += row;
  });

  const pagText = document.getElementById('statusPaginationText');
  if (pagText) {
    pagText.innerHTML = `Showing <span class="font-semibold text-slate-700 dark:text-slate-200">${startIndex + 1}</span> to <span class="font-semibold text-slate-700 dark:text-slate-200">${endIndex}</span> of <span class="font-semibold text-slate-700 dark:text-slate-200">${totalItems}</span> records`;
  }
  renderAccountPaginationControls(totalItems, totalPages, currentAccountPage);
}

// RENDER ACCOUNT STATUS PAGINATION CONTROLS
function renderAccountPaginationControls(totalItems, totalPages, currentPage) {
  const container = document.getElementById('statusPaginationControls');
  if (!container) return;

  const isPrevDisabled = currentPage <= 1 || totalItems === 0;
  const isNextDisabled = currentPage >= totalPages || totalItems === 0;

  let html = `
    <!-- Previous Button -->
    <button type="button" class="${isPrevDisabled ? 'px-3 py-1.5 text-xs font-medium text-slate-300 dark:text-slate-600 bg-slate-50/50 dark:bg-slate-800/30 cursor-not-allowed' : 'px-3 py-1.5 text-xs font-medium text-slate-500 hover:text-slate-700 hover:bg-slate-50 dark:text-slate-400 dark:hover:text-slate-200 dark:hover:bg-slate-800/60 transition cursor-pointer'}" 
            ${isPrevDisabled ? 'disabled' : ''} onclick="window.changeAccountStatusPage(${currentPage - 1})" aria-label="Previous Page">
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
        html += `<button type="button" class="px-3.5 py-1.5 text-xs font-medium text-slate-600 hover:text-slate-900 hover:bg-slate-50 dark:text-slate-400 dark:hover:text-slate-200 dark:hover:bg-slate-800/60 border-l border-slate-200 dark:border-slate-700 transition cursor-pointer" onclick="window.changeAccountStatusPage(${p})">${p}</button>`;
      }
    });
  }

  html += `
    <!-- Next Button -->
    <button type="button" class="${isNextDisabled ? 'px-3 py-1.5 text-xs font-medium text-slate-300 dark:text-slate-600 bg-slate-50/50 dark:bg-slate-800/30 cursor-not-allowed border-l border-slate-200 dark:border-slate-700' : 'px-3 py-1.5 text-xs font-medium text-slate-500 hover:text-slate-700 hover:bg-slate-50 dark:text-slate-400 dark:hover:text-slate-200 dark:hover:bg-slate-800/60 border-l border-slate-200 dark:border-slate-700 transition cursor-pointer'}" 
            ${isNextDisabled ? 'disabled' : ''} onclick="window.changeAccountStatusPage(${currentPage + 1})" aria-label="Next Page">
      <i class="fa-solid fa-chevron-right text-[10px]"></i>
    </button>
  `;

  container.innerHTML = html;
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
