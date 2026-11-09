// CITIZEN DIRECTORY UI

// PAGINATION STATE
let currentCitizenPage = 1;
const citizenPageSize = 10;
let activeFilteredCitizens = [];

window.currentCitizenPage = currentCitizenPage;

window.resetCitizenPage = function() {
  currentCitizenPage = 1;
  window.currentCitizenPage = 1;
};

window.changeCitizenPage = function(targetPage) {
  currentCitizenPage = targetPage;
  window.currentCitizenPage = targetPage;
  renderCitizens(activeFilteredCitizens);
};

function updateMetrics(allList = mockCitizens) {
  const metricTotal = document.getElementById('metricTotal');
  const metricActive = document.getElementById('metricActive');
  const metricInactive = document.getElementById('metricInactive');
  const metricLocked = document.getElementById('metricLocked');

  if (metricTotal) metricTotal.innerText = allList.length;
  if (metricActive) metricActive.innerText = allList.filter(c => c.status === 'Active').length;
  if (metricInactive) metricInactive.innerText = allList.filter(c => c.status === 'Inactive' || c.status === 'Pending').length;
  if (metricLocked) metricLocked.innerText = allList.filter(c => c.status === 'Locked').length;
}

// RENDER DATATABLE
function renderCitizens(list = mockCitizens) {
  activeFilteredCitizens = list;
  updateMetrics();

  const citizenTbody = document.getElementById('citizenTableBody');
  if (!citizenTbody) return;
  citizenTbody.innerHTML = '';

  const totalItems = activeFilteredCitizens.length;
  const totalPages = Math.ceil(totalItems / citizenPageSize) || 1;

  if (currentCitizenPage > totalPages) currentCitizenPage = totalPages;
  if (currentCitizenPage < 1) currentCitizenPage = 1;
  window.currentCitizenPage = currentCitizenPage;

  if (totalItems === 0) {
    citizenTbody.innerHTML = `
      <tr>
        <td colspan="6" class="px-6 py-12 text-center text-slate-400 font-semibold">
          <i class="fa-solid fa-users-slash text-3xl mb-3 block opacity-60"></i>
          No citizen accounts match the criteria.
        </td>
      </tr>
    `;
    const pagText = document.getElementById('citizenPaginationText');
    if (pagText) {
      pagText.innerHTML = `Showing <span class="font-semibold text-slate-700 dark:text-slate-200">0</span> to <span class="font-semibold text-slate-700 dark:text-slate-200">0</span> of <span class="font-semibold text-slate-700 dark:text-slate-200">0</span> records`;
    }
    renderCitizenPaginationControls(0, 1, 1);
    return;
  }

  const startIndex = (currentCitizenPage - 1) * citizenPageSize;
  const endIndex = Math.min(startIndex + citizenPageSize, totalItems);
  const pageItems = activeFilteredCitizens.slice(startIndex, endIndex);

  pageItems.forEach(cit => {
    let statusClass = '';
    let dotIndicator = '';

    if (cit.status === 'Active') {
      statusClass = 'bg-emerald-50 text-emerald-700 border-emerald-150';
      dotIndicator = '<span class="h-1.5 w-1.5 rounded-none bg-emerald-500 animate-pulse"></span>';
    } else if (cit.status === 'Inactive') {
      statusClass = 'bg-slate-50 text-slate-500 border-slate-200';
      dotIndicator = '<span class="h-1.5 w-1.5 rounded-none bg-slate-400"></span>';
    } else if (cit.status === 'Locked') {
      statusClass = 'bg-rose-50 text-rose-700 border-rose-150';
      dotIndicator = '<i class="fa-solid fa-lock text-[9px] shrink-0 text-rose-500"></i>';
    }

    const lockIcon = cit.status === 'Locked' ? 'fa-lock text-rose-600 hover:bg-rose-50' : 'fa-lock-open text-slate-400 hover:text-slate-700 hover:bg-slate-50';
    const lockTitle = cit.status === 'Locked' ? 'Unlock Account' : 'Place Security Lock';
    const rawDate = new Date(cit.regDate);
    const options = { month: 'short', day: 'numeric', year: 'numeric' };
    const formattedRegDate = rawDate.toLocaleDateString('en-US', options);

    const row = `
      <tr class="hover:bg-slate-50/60 dark:hover:bg-slate-800/40 transition group">
        <!-- Citizen ID -->
        <td class="py-3 px-4 font-mono text-[11px] font-normal text-slate-500 dark:text-slate-400 select-all tracking-tight border-b border-slate-100 dark:border-slate-800/60 align-middle">
          ${cit.id}
        </td>

        <!-- Full Name & Contact -->
        <td class="py-3 px-4 border-b border-slate-100 dark:border-slate-800/60 align-middle">
          <div class="flex flex-col">
            <span class="font-semibold text-slate-900 dark:text-slate-100 text-xs tracking-tight leading-snug">${cit.name}</span>
            <span class="text-[11px] font-normal text-slate-500 dark:text-slate-400">${cit.email}</span>
          </div>
        </td>

        <!-- Barangay Residency -->
        <td class="py-3 px-4 text-xs font-normal text-slate-600 dark:text-slate-300 border-b border-slate-100 dark:border-slate-800/60 align-middle">
          ${cit.barangay}
        </td>

        <!-- Status Badge -->
        <td class="py-3 px-4 border-b border-slate-100 dark:border-slate-800/60 align-middle">
          <span class="text-[10px] font-bold uppercase px-2 py-0.5 rounded-none border ${statusClass} inline-flex items-center gap-1.5">
            ${dotIndicator}
            <span>${cit.status}</span>
          </span>
        </td>

        <!-- Activity Logs -->
        <td class="py-3 px-4 space-y-1 border-b border-slate-100 dark:border-slate-800/60 align-middle">
          <div class="flex items-center gap-1.5 text-slate-650">
            <span class="text-[9px] font-bold text-slate-400 uppercase">Registered:</span>
            <span class="font-normal text-[11px] text-slate-600 dark:text-slate-300">${formattedRegDate}</span>
          </div>
          <div class="flex items-center gap-1.5 text-slate-650">
            <span class="text-[9px] font-bold text-slate-400 uppercase">Last Active:</span>
            <span class="font-normal text-[11px] text-slate-600 dark:text-slate-300">${cit.lastLogin}</span>
          </div>
        </td>

        <!-- Actions -->
        <td class="py-3 px-4 text-right whitespace-nowrap border-b border-slate-100 dark:border-slate-800/60 align-middle">
          <div class="inline-flex items-center space-x-1.5">
            <!-- View Details icon -->
            <button onclick="if(typeof openViewModal === 'function') openViewModal('${cit.id}')" class="w-7 h-7 flex items-center justify-center border rounded-none transition cursor-pointer shadow-2xs border-sky-300 bg-sky-50 text-sky-600 hover:bg-sky-100 hover:text-sky-700 dark:border-sky-800/80 dark:bg-sky-950/40 dark:text-sky-400 dark:hover:bg-sky-900/60 dark:hover:text-sky-300" title="View Portal Profile">
              <i class="fa-solid fa-eye text-xs"></i>
            </button>

            <!-- Lock/Unlock button -->
            <button onclick="if(typeof openLockModal === 'function') openLockModal('${cit.id}')" class="w-7 h-7 flex items-center justify-center border rounded-none transition cursor-pointer shadow-2xs ${cit.status === 'Locked' ? 'border-emerald-300 bg-emerald-50 text-emerald-600 hover:bg-emerald-100 hover:text-emerald-700 dark:border-emerald-800/80 dark:bg-emerald-950/40 dark:text-emerald-400 dark:hover:bg-emerald-900/60 dark:hover:text-emerald-300' : 'border-rose-300 bg-rose-50 text-rose-600 hover:bg-rose-100 hover:text-rose-700 dark:border-rose-800/80 dark:bg-rose-950/40 dark:text-rose-400 dark:hover:bg-rose-900/60 dark:hover:text-rose-300'}" title="${lockTitle}">
              <i class="fa-solid ${cit.status === 'Locked' ? 'fa-lock' : 'fa-lock-open'} text-xs"></i>
            </button>

            <!-- Reset password key icon -->
            <button onclick="if(typeof openResetModal === 'function') openResetModal('${cit.id}')" class="w-7 h-7 flex items-center justify-center border rounded-none transition cursor-pointer shadow-2xs border-amber-300 bg-amber-50 text-amber-600 hover:bg-amber-100 hover:text-amber-700 dark:border-amber-800/80 dark:bg-amber-950/40 dark:text-amber-400 dark:hover:bg-amber-900/60 dark:hover:text-amber-300" title="Reset Password Access">
              <i class="fa-solid fa-key text-xs"></i>
            </button>
          </div>
        </td>
      </tr>
    `;
    citizenTbody.innerHTML += row;
  });

  const pagText = document.getElementById('citizenPaginationText');
  if (pagText) {
    pagText.innerHTML = `Showing <span class="font-semibold text-slate-700 dark:text-slate-200">${startIndex + 1}</span> to <span class="font-semibold text-slate-700 dark:text-slate-200">${endIndex}</span> of <span class="font-semibold text-slate-700 dark:text-slate-200">${totalItems}</span> records`;
  }

  renderCitizenPaginationControls(totalItems, totalPages, currentCitizenPage);
}

// RENDER CITIZEN PAGINATION CONTROLS
function renderCitizenPaginationControls(totalItems, totalPages, currentPage) {
  const container = document.getElementById('citizenPaginationControls');
  if (!container) return;

  const isPrevDisabled = currentPage <= 1 || totalItems === 0;
  const isNextDisabled = currentPage >= totalPages || totalItems === 0;

  let html = `
    <!-- Previous Button -->
    <button type="button" class="${isPrevDisabled ? 'px-3 py-1.5 text-xs font-medium text-slate-300 dark:text-slate-600 bg-slate-50/50 dark:bg-slate-800/30 cursor-not-allowed' : 'px-3 py-1.5 text-xs font-medium text-slate-500 hover:text-slate-700 hover:bg-slate-50 dark:text-slate-400 dark:hover:text-slate-200 dark:hover:bg-slate-800/60 transition cursor-pointer'}" 
            ${isPrevDisabled ? 'disabled' : ''} onclick="window.changeCitizenPage(${currentPage - 1})" aria-label="Previous Page">
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
        html += `<button type="button" class="px-3.5 py-1.5 text-xs font-medium text-slate-600 hover:text-slate-900 hover:bg-slate-50 dark:text-slate-400 dark:hover:text-slate-200 dark:hover:bg-slate-800/60 border-l border-slate-200 dark:border-slate-700 transition cursor-pointer" onclick="window.changeCitizenPage(${p})">${p}</button>`;
      }
    });
  }

  html += `
    <!-- Next Button -->
    <button type="button" class="${isNextDisabled ? 'px-3 py-1.5 text-xs font-medium text-slate-300 dark:text-slate-600 bg-slate-50/50 dark:bg-slate-800/30 cursor-not-allowed border-l border-slate-200 dark:border-slate-700' : 'px-3 py-1.5 text-xs font-medium text-slate-500 hover:text-slate-700 hover:bg-slate-50 dark:text-slate-400 dark:hover:text-slate-200 dark:hover:bg-slate-800/60 border-l border-slate-200 dark:border-slate-700 transition cursor-pointer'}" 
            ${isNextDisabled ? 'disabled' : ''} onclick="window.changeCitizenPage(${currentPage + 1})" aria-label="Next Page">
      <i class="fa-solid fa-chevron-right text-[10px]"></i>
    </button>
  `;

  container.innerHTML = html;
}
