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
    if (pagText) pagText.innerText = "Showing 0 to 0 of 0 accounts";
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
          <div class="inline-flex items-center space-x-3">
            <!-- View Details icon -->
            <button onclick="if(typeof openViewModal === 'function') openViewModal('${cit.id}')" class="text-slate-400 hover:text-[#0f172a] hover:bg-slate-50 p-1.5 rounded-none border border-transparent hover:border-slate-150 transition cursor-pointer" title="View Portal Profile">
              <i class="fa-solid fa-circle-info text-xs"></i>
            </button>

            <!-- Lock/Unlock button -->
            <button onclick="if(typeof openLockModal === 'function') openLockModal('${cit.id}')" class="border p-1.5 rounded-none transition cursor-pointer flex items-center justify-center ${lockIcon}" title="${lockTitle}">
              <i class="fa-solid ${cit.status === 'Locked' ? 'fa-lock' : 'fa-lock-open'} text-xs"></i>
            </button>

            <!-- Reset password key icon -->
            <button onclick="if(typeof openResetModal === 'function') openResetModal('${cit.id}')" class="text-slate-400 hover:text-[#0f172a] hover:bg-slate-50 p-1.5 rounded-none border border-transparent hover:border-slate-150 transition cursor-pointer" title="Reset Password Access">
              <i class="fa-solid fa-key text-xs"></i>
            </button>
          </div>
        </td>
      </tr>
    `;
    citizenTbody.innerHTML += row;
  });

  const pagText = document.getElementById('citizenPaginationText');
  if (pagText) pagText.innerText = `Showing ${startIndex + 1} to ${endIndex} of ${totalItems} accounts`;

  renderCitizenPaginationControls(totalItems, totalPages, currentCitizenPage);
}

// RENDER CITIZEN PAGINATION CONTROLS
function renderCitizenPaginationControls(totalItems, totalPages, currentPage) {
  const prevBtn = document.getElementById('citizenPrevBtn');
  const nextBtn = document.getElementById('citizenNextBtn');
  const pageNumbersContainer = document.getElementById('citizenPageNumbers');

  if (prevBtn) {
    const isPrevDisabled = currentPage <= 1 || totalItems === 0;
    prevBtn.disabled = isPrevDisabled;
    if (isPrevDisabled) {
      prevBtn.className = "px-3 py-1.5 rounded-none border border-slate-200 bg-white text-slate-300 cursor-not-allowed transition";
    } else {
      prevBtn.className = "px-3 py-1.5 rounded-none border border-slate-200 bg-white hover:bg-slate-50 text-slate-600 cursor-pointer transition";
    }
    prevBtn.onclick = () => {
      if (!isPrevDisabled) window.changeCitizenPage(currentPage - 1);
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
      if (!isNextDisabled) window.changeCitizenPage(currentPage + 1);
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
      firstBtn.onclick = () => window.changeCitizenPage(1);
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
      pageBtn.onclick = () => window.changeCitizenPage(p);
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
      lastBtn.onclick = () => window.changeCitizenPage(totalPages);
      pageNumbersContainer.appendChild(lastBtn);
    }
  }
}
