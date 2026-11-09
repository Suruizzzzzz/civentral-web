window.civAudit = window.civAudit || {};
window.civAudit.userActivities = window.civAudit.userActivities || {};

window.civAudit.userActivities.ui = {
  currentPage: 1,
  pageSize: 50,
  currentFilteredLogs: [],

  populateFilterDropdowns() {
    const filterDepartment = document.getElementById('filterDepartment');
    const filterModule = document.getElementById('filterModule');
    const api = window.civAudit.userActivities.api;

    if (filterDepartment) {
      const curVal = filterDepartment.value;
      filterDepartment.innerHTML = '<option value="All">All Departments</option>';
      api.availableDepartments.forEach(d => {
        const opt = document.createElement('option');
        opt.value = d.department_name;
        opt.textContent = d.department_name;
        filterDepartment.appendChild(opt);
      });
      filterDepartment.value = curVal || 'All';
    }

    if (filterModule) {
      const curVal = filterModule.value;
      filterModule.innerHTML = '<option value="All">All Modules</option>';
      api.availableModules.forEach(m => {
        const opt = document.createElement('option');
        opt.value = m.module_name;
        opt.textContent = m.module_name;
        filterModule.appendChild(opt);
      });
      filterModule.value = curVal || 'All';
    }
  },

  changePage(delta) {
    const totalPages = Math.ceil(this.currentFilteredLogs.length / this.pageSize) || 1;
    const newPage = this.currentPage + delta;
    if (newPage >= 1 && newPage <= totalPages) {
      this.renderTableRows(this.currentFilteredLogs, newPage);
    }
  },

  goToPage(pageNum) {
    const totalPages = Math.ceil(this.currentFilteredLogs.length / this.pageSize) || 1;
    if (pageNum >= 1 && pageNum <= totalPages) {
      this.renderTableRows(this.currentFilteredLogs, pageNum);
    }
  },

  renderTableRows(logsList, page = 1) {
    this.currentFilteredLogs = logsList || [];
    this.currentPage = page;

    const tbody = document.getElementById('auditTableBody');
    if (!tbody) return;
    tbody.innerHTML = '';

    const total = this.currentFilteredLogs.length;
    const totalPages = Math.ceil(total / this.pageSize) || 1;

    // Boundary check
    if (this.currentPage > totalPages) this.currentPage = totalPages;
    if (this.currentPage < 1) this.currentPage = 1;

    const startIdx = total > 0 ? (this.currentPage - 1) * this.pageSize : 0;
    const endIdx = Math.min(startIdx + this.pageSize, total);

    // Update Pagination Footer Controls
    const startEl = document.getElementById('paginationStart');
    const endEl = document.getElementById('paginationEnd');
    const totalEl = document.getElementById('paginationTotal');
    const container = document.getElementById('paginationControls');

    if (startEl) startEl.innerText = total > 0 ? startIdx + 1 : 0;
    if (endEl) endEl.innerText = endIdx;
    if (totalEl) totalEl.innerText = total;

    if (container) {
      const isPrevDisabled = this.currentPage <= 1 || total === 0;
      const isNextDisabled = this.currentPage >= totalPages || total === 0;

      let html = `
        <!-- Previous Button -->
        <button type="button" class="${isPrevDisabled ? 'px-3 py-1.5 text-xs font-medium text-slate-300 dark:text-slate-600 bg-slate-50/50 dark:bg-slate-800/30 cursor-not-allowed' : 'px-3 py-1.5 text-xs font-medium text-slate-500 hover:text-slate-700 hover:bg-slate-50 dark:text-slate-400 dark:hover:text-slate-200 dark:hover:bg-slate-800/60 transition cursor-pointer'}" 
                ${isPrevDisabled ? 'disabled' : ''} onclick="window.civAudit.userActivities.ui.goToPage(${this.currentPage - 1})" aria-label="Previous Page">
          <i class="fa-solid fa-chevron-left text-[10px]"></i>
        </button>
      `;

      if (total === 0) {
        html += `
          <button type="button" class="px-3.5 py-1.5 text-xs font-bold text-sky-600 dark:text-sky-400 bg-sky-50/80 dark:bg-sky-950/40 border border-sky-300 dark:border-sky-600/60 z-10 cursor-default" disabled>1</button>
        `;
      } else {
        let pages = [];
        if (totalPages <= 7) {
          for (let i = 1; i <= totalPages; i++) pages.push(i);
        } else if (this.currentPage <= 4) {
          pages = [1, 2, 3, 4, 5, '...', totalPages];
        } else if (this.currentPage >= totalPages - 3) {
          pages = [1, '...', totalPages - 4, totalPages - 3, totalPages - 2, totalPages - 1, totalPages];
        } else {
          pages = [1, '...', this.currentPage - 1, this.currentPage, this.currentPage + 1, '...', totalPages];
        }

        pages.forEach(p => {
          if (p === '...') {
            html += `<span class="px-3.5 py-1.5 text-xs font-medium text-slate-400 dark:text-slate-500 border-l border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 select-none">...</span>`;
          } else if (p === this.currentPage) {
            html += `<button type="button" class="px-3.5 py-1.5 text-xs font-bold text-sky-600 dark:text-sky-400 bg-sky-50/80 dark:bg-sky-950/40 border border-sky-300 dark:border-sky-600/60 z-10 cursor-default" aria-current="page">${p}</button>`;
          } else {
            html += `<button type="button" class="px-3.5 py-1.5 text-xs font-medium text-slate-600 hover:text-slate-900 hover:bg-slate-50 dark:text-slate-400 dark:hover:text-slate-200 dark:hover:bg-slate-800/60 border-l border-slate-200 dark:border-slate-700 transition cursor-pointer" onclick="window.civAudit.userActivities.ui.goToPage(${p})">${p}</button>`;
          }
        });
      }

      html += `
        <!-- Next Button -->
        <button type="button" class="${isNextDisabled ? 'px-3 py-1.5 text-xs font-medium text-slate-300 dark:text-slate-600 bg-slate-50/50 dark:bg-slate-800/30 cursor-not-allowed border-l border-slate-200 dark:border-slate-700' : 'px-3 py-1.5 text-xs font-medium text-slate-500 hover:text-slate-700 hover:bg-slate-50 dark:text-slate-400 dark:hover:text-slate-200 dark:hover:bg-slate-800/60 border-l border-slate-200 dark:border-slate-700 transition cursor-pointer'}" 
                ${isNextDisabled ? 'disabled' : ''} onclick="window.civAudit.userActivities.ui.goToPage(${this.currentPage + 1})" aria-label="Next Page">
          <i class="fa-solid fa-chevron-right text-[10px]"></i>
        </button>
      `;

      container.innerHTML = html;
    }

    if (total === 0) {
      tbody.innerHTML = `
        <tr id="noResultsRow">
          <td colspan="7" class="py-12 text-center text-slate-400">
            <div class="flex flex-col items-center justify-center space-y-2">
              <i class="fa-solid fa-folder-open text-3xl text-slate-300"></i>
              <p class="text-xs font-bold">No matching audit logs found</p>
              <p class="text-[10px] font-semibold text-slate-400">Try adjusting your filters or resetting them to defaults.</p>
            </div>
          </td>
        </tr>
      `;
      return;
    }

    const pageLogs = this.currentFilteredLogs.slice(startIdx, endIdx);

    pageLogs.forEach(item => {
      let roleBadgeClass = 'bg-slate-100 text-slate-600 ring-1 ring-slate-600/10';
      if (item.roleColor === 'purple') roleBadgeClass = 'bg-purple-50 text-purple-700 ring-1 ring-purple-600/20';
      else if (item.roleColor === 'blue') roleBadgeClass = 'bg-blue-50 text-blue-700 ring-1 ring-blue-600/20';

      let actionBadgeClass = 'bg-slate-50 text-slate-700 ring-1 ring-slate-600/20';
      if (item.action === 'Approve') actionBadgeClass = 'bg-emerald-50 text-emerald-700 ring-1 ring-emerald-600/20';
      else if (item.action === 'Create') actionBadgeClass = 'bg-blue-50 text-blue-700 ring-1 ring-blue-600/20';
      else if (item.action === 'Update' || item.action === 'Edit') actionBadgeClass = 'bg-amber-50 text-amber-800 ring-1 ring-amber-600/20';
      else if (item.action === 'Reject' || item.action === 'Delete') actionBadgeClass = 'bg-rose-50 text-rose-700 ring-1 ring-rose-600/20';

      let statusBadgeHTML = `
        <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-none text-[10px] font-bold bg-emerald-50 text-emerald-700 ring-1 ring-emerald-600/20">
          <i class="fa-solid fa-circle-check text-[9px]"></i> Success
        </span>
      `;
      if (item.status !== 'Success') {
        statusBadgeHTML = `
          <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-none text-[10px] font-bold bg-rose-50 text-rose-700 ring-1 ring-rose-600/20">
            <i class="fa-solid fa-triangle-exclamation text-[9px]"></i> ${window.escapeHtml ? window.escapeHtml(item.status) : item.status}
          </span>
        `;
      }

      const tr = document.createElement('tr');
      tr.className = 'hover:bg-slate-50/60 dark:hover:bg-slate-800/40 transition cursor-pointer group';
      tr.setAttribute('onclick', 'window.civAudit.userActivities.modal.openLogDetailsModal(this)');
      tr.setAttribute('data-id', item.id);
      tr.setAttribute('data-date-time', item.dateTimeStr);
      tr.setAttribute('data-date', item.date);
      tr.setAttribute('data-actor', item.actor);
      tr.setAttribute('data-role', item.role);
      tr.setAttribute('data-role-color', item.roleColor);
      tr.setAttribute('data-department', item.department);
      tr.setAttribute('data-module', item.module);
      tr.setAttribute('data-action', item.action);
      tr.setAttribute('data-desc', item.desc);
      tr.setAttribute('data-ip', item.ip);
      tr.setAttribute('data-device', item.device);
      tr.setAttribute('data-status', item.status);
      tr.setAttribute('data-payload', item.payloadStr);

      const esc = window.escapeHtml || (s => s);

      tr.innerHTML = `
        <td class="py-3 px-4 font-mono text-[11px] font-normal text-slate-500 dark:text-slate-400 border-b border-slate-100 dark:border-slate-800/60 align-middle">${esc(item.id)}</td>
        <td class="py-3 px-4 border-b border-slate-100 dark:border-slate-800/60 align-middle">
          <div class="font-semibold text-slate-900 dark:text-slate-100 text-xs">${esc(item.datePart)}</div>
          <div class="text-[11px] font-normal text-slate-500 dark:text-slate-400 mt-0.5">${esc(item.timePart)}</div>
        </td>
        <td class="py-3 px-4 border-b border-slate-100 dark:border-slate-800/60 align-middle">
          <div class="font-semibold text-slate-900 dark:text-slate-100 text-xs">${esc(item.actor)}</div>
          <span class="inline-flex items-center px-1.5 py-0.5 rounded-none text-[9px] font-bold uppercase ${roleBadgeClass} mt-1">${esc(item.role)}</span>
        </td>
        <td class="py-3 px-4 border-b border-slate-100 dark:border-slate-800/60 align-middle">
          <div class="font-semibold text-slate-900 dark:text-slate-100 text-xs">${esc(item.department)}</div>
          <div class="text-[11px] font-normal text-slate-500 dark:text-slate-400 mt-0.5">${esc(item.module)}</div>
        </td>
        <td class="py-3 px-4 border-b border-slate-100 dark:border-slate-800/60 align-middle">
          <div class="flex items-center gap-2">
            <span class="inline-flex items-center px-1.5 py-0.5 rounded-none text-[9px] font-bold uppercase ${actionBadgeClass}">${esc(item.action)}</span>
            <span class="text-xs text-slate-600 dark:text-slate-300 font-normal">${esc(item.desc)}</span>
          </div>
        </td>
        <td class="py-3 px-4 border-b border-slate-100 dark:border-slate-800/60 align-middle">
          <div class="text-[11px] font-mono text-slate-500 dark:text-slate-400 font-normal">${esc(item.ip)}</div>
          <div class="text-[11px] font-normal text-slate-500 dark:text-slate-400 mt-0.5">${esc(item.device)}</div>
        </td>
        <td class="py-3 px-4 text-center border-b border-slate-100 dark:border-slate-800/60 align-middle">
          ${statusBadgeHTML}
        </td>
      `;
      tbody.appendChild(tr);
    });

    // Auto-open audit log modal if audit_id is specified in URL query
    const urlParams = new URLSearchParams(window.location.search);
    const auditIdParam = urlParams.get('audit_id');
    if (auditIdParam) {
      const targetRow = tbody.querySelector(`tr[data-id="${auditIdParam}"]`);
      if (targetRow) {
        // Remove audit_id parameter from URL so it doesn't trigger again on pagination/filters
        const newSearch = window.location.search.replace(new RegExp('[?&]audit_id=' + auditIdParam), '').replace(/^&/, '?');
        const cleanUrl = window.location.pathname + newSearch;
        window.history.replaceState({}, document.title, cleanUrl);
        window.civAudit.userActivities.modal.openLogDetailsModal(targetRow);
      } else {
        const matchIdx = this.currentFilteredLogs.findIndex(item => item.id == auditIdParam);
        if (matchIdx !== -1) {
          const targetPage = Math.floor(matchIdx / this.pageSize) + 1;
          if (targetPage !== this.currentPage) {
            this.goToPage(targetPage);
          }
        }
      }
    }
  }
};
