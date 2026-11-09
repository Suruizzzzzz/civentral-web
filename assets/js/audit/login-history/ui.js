window.civAudit = window.civAudit || {};
window.civAudit.loginHistory = window.civAudit.loginHistory || {};

window.civAudit.loginHistory.ui = {
  currentPage: 1,
  pageSize: 50,
  filteredLoginLogs: [],

  populateDepartments(departments) {
    const sel = document.getElementById('filterDepartment');
    if (!sel) return;
    const currentVal = sel.value;
    sel.innerHTML = '<option value="All">All Departments</option>';
    departments.forEach(dept => {
      const name = dept.department_name;
      const opt = document.createElement('option');
      opt.value = name;
      opt.textContent = name;
      sel.appendChild(opt);
    });
    sel.value = currentVal || 'All';
  },

  formatDateTime(isoStr) {
    if (!isoStr) return '—';
    const d = new Date(isoStr.includes('T') ? isoStr : isoStr.replace(' ', 'T') + '+08:00');
    if (isNaN(d.getTime())) return isoStr;
    return d.toLocaleString('en-US', {
      timeZone: 'Asia/Manila',
      month: 'short',
      day: 'numeric',
      year: 'numeric',
      hour: 'numeric',
      minute: '2-digit',
      hour12: true
    });
  },

  parseUserAgent(ua) {
    if (!ua) return 'Desktop - Browser';
    let os = 'Desktop';
    if (/mobile/i.test(ua)) os = 'Mobile';
    else if (/mac/i.test(ua)) os = 'Mac';
    else if (/windows/i.test(ua)) os = 'Desktop';

    let browser = 'Browser';
    if (/chrome|crios/i.test(ua) && !/edg/i.test(ua)) browser = 'Chrome';
    else if (/edg/i.test(ua)) browser = 'Edge';
    else if (/safari/i.test(ua) && !/chrome/i.test(ua)) browser = 'Safari';
    else if (/firefox|fxios/i.test(ua)) browser = 'Firefox';

    return `${os} - ${browser}`;
  },

  calculateLifespan(loginTimeStr, logoutTimeStr, loginStatus) {
    if (loginStatus === 'Failed') return '0m';
    if (!logoutTimeStr) return 'Active';
    const t1 = new Date(loginTimeStr).getTime();
    const t2 = new Date(logoutTimeStr).getTime();
    if (isNaN(t1) || isNaN(t2) || t2 <= t1) return '0m';
    const diffMs = t2 - t1;
    const mins = Math.floor(diffMs / 60000);
    const hrs = Math.floor(mins / 60);
    const remMins = mins % 60;
    if (hrs > 0) return `${hrs}h ${remMins}m`;
    return `${mins}m`;
  },

  changePage(delta) {
    const totalPages = Math.ceil(this.filteredLoginLogs.length / this.pageSize) || 1;
    const newPage = this.currentPage + delta;
    if (newPage >= 1 && newPage <= totalPages) {
      this.renderPaginatedTable(this.filteredLoginLogs, newPage);
    }
  },

  goToPage(pageNum) {
    const totalPages = Math.ceil(this.filteredLoginLogs.length / this.pageSize) || 1;
    if (pageNum >= 1 && pageNum <= totalPages) {
      this.renderPaginatedTable(this.filteredLoginLogs, pageNum);
    }
  },

  renderPaginatedTable(logsList, page = 1) {
    this.filteredLoginLogs = logsList || [];
    this.currentPage = page;

    const tbody = document.getElementById('loginTableBody');
    if (!tbody) return;

    const total = this.filteredLoginLogs.length;
    const totalPages = Math.ceil(total / this.pageSize) || 1;

    if (this.currentPage > totalPages) this.currentPage = totalPages;
    if (this.currentPage < 1) this.currentPage = 1;

    const startIdx = total > 0 ? (this.currentPage - 1) * this.pageSize : 0;
    const endIdx = Math.min(startIdx + this.pageSize, total);

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
                ${isPrevDisabled ? 'disabled' : ''} onclick="window.civAudit.loginHistory.ui.goToPage(${this.currentPage - 1})" aria-label="Previous Page">
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
            html += `<button type="button" class="px-3.5 py-1.5 text-xs font-medium text-slate-600 hover:text-slate-900 hover:bg-slate-50 dark:text-slate-400 dark:hover:text-slate-200 dark:hover:bg-slate-800/60 border-l border-slate-200 dark:border-slate-700 transition cursor-pointer" onclick="window.civAudit.loginHistory.ui.goToPage(${p})">${p}</button>`;
          }
        });
      }

      html += `
        <!-- Next Button -->
        <button type="button" class="${isNextDisabled ? 'px-3 py-1.5 text-xs font-medium text-slate-300 dark:text-slate-600 bg-slate-50/50 dark:bg-slate-800/30 cursor-not-allowed border-l border-slate-200 dark:border-slate-700' : 'px-3 py-1.5 text-xs font-medium text-slate-500 hover:text-slate-700 hover:bg-slate-50 dark:text-slate-400 dark:hover:text-slate-200 dark:hover:bg-slate-800/60 border-l border-slate-200 dark:border-slate-700 transition cursor-pointer'}" 
                ${isNextDisabled ? 'disabled' : ''} onclick="window.civAudit.loginHistory.ui.goToPage(${this.currentPage + 1})" aria-label="Next Page">
          <i class="fa-solid fa-chevron-right text-[10px]"></i>
        </button>
      `;

      container.innerHTML = html;
    }

    tbody.innerHTML = '';

    if (total === 0) {
      tbody.innerHTML = `
        <tr id="noResultsRow">
          <td colspan="6" class="py-12 text-center text-slate-400">
            <div class="flex flex-col items-center justify-center space-y-2">
              <i class="fa-solid fa-user-slash text-3xl text-slate-300"></i>
              <p class="text-xs font-bold">No matching authentication logs found</p>
              <p class="text-[10px] font-semibold text-slate-400">Try adjusting your filters or resetting them to defaults.</p>
            </div>
          </td>
        </tr>
      `;
      return;
    }

    const pageLogs = this.filteredLoginLogs.slice(startIdx, endIdx);

    pageLogs.forEach(log => {
      const user = log.users || {};
      const roleObj = user.roles || {};
      const posObj = user.positions || {};
      const deptObj = posObj.departments || {};

      const actor = user.first_name ? `${user.first_name} ${user.last_name}` : (user.email || 'Unknown User');
      const email = user.email || 'N/A';
      const role = roleObj.role_name || 'User';
      const department = deptObj.department_name || 'Central IT';

      const loginTimeFormatted = this.formatDateTime(log.login_time);
      const logoutTimeFormatted = this.formatDateTime(log.logout_time);
      const dateVal = log.login_time ? log.login_time.split('T')[0] : '';

      let statusText = 'Successful Login';
      if (log.login_status === 'Failed') {
        if (user.status === 'Locked' || (log.failure_reason && log.failure_reason.toLowerCase().includes('locked'))) {
          statusText = 'Account Locked';
        } else {
          statusText = 'Failed Login';
        }
      } else if (log.logout_time) {
        statusText = 'Logout';
      }

      const lifespan = this.calculateLifespan(log.login_time, log.logout_time, log.login_status);
      const ip = log.ip_address || '127.0.0.1';
      const device = this.parseUserAgent(log.browser);
      const details = log.failure_reason || (log.login_status === 'Success' ? 'Auth session established successfully.' : 'Authentication attempt recorded.');

      const payloadObj = {
        login_id: log.login_id,
        user_id: log.user_id,
        session_id: log.session_id,
        login_time: log.login_time,
        logout_time: log.logout_time,
        ip_address: log.ip_address,
        browser: log.browser,
        login_status: log.login_status,
        failure_reason: log.failure_reason
      };

      const tr = document.createElement('tr');
      tr.onclick = function() { window.civAudit.loginHistory.modal.openSessionModal(this); };
      tr.className = 'hover:bg-slate-50/60 dark:hover:bg-slate-800/40 transition cursor-pointer group';
      tr.setAttribute('data-id', `#LOG-${log.login_id}`);
      tr.setAttribute('data-date', dateVal);
      tr.setAttribute('data-actor', actor);
      tr.setAttribute('data-email', email);
      tr.setAttribute('data-role', role);
      tr.setAttribute('data-department', department);
      tr.setAttribute('data-login-time', loginTimeFormatted);
      tr.setAttribute('data-logout-time', logoutTimeFormatted);
      tr.setAttribute('data-lifespan', lifespan);
      tr.setAttribute('data-status', statusText);
      tr.setAttribute('data-ip', ip);
      tr.setAttribute('data-device', device);
      tr.setAttribute('data-location', 'Caloocan City, PH');
      tr.setAttribute('data-details', details);
      tr.setAttribute('data-payload', JSON.stringify(payloadObj));

      let statusBadgeHtml = '';
      if (statusText === 'Successful Login') {
        statusBadgeHtml = `<span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-none text-[10px] font-bold bg-emerald-50 text-emerald-700 ring-1 ring-emerald-600/20">🟢 Success</span>`;
      } else if (statusText === 'Logout') {
        statusBadgeHtml = `<span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-none text-[10px] font-bold bg-slate-100 text-slate-600 ring-1 ring-slate-600/10">⚪ Logged Out</span>`;
      } else if (statusText === 'Account Locked') {
        statusBadgeHtml = `<span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-none text-[10px] font-black bg-red-100 text-red-900 ring-1 ring-red-800/30"><i class="fa-solid fa-user-lock text-[9px]"></i> Account Locked</span>`;
      } else if (statusText === 'Failed Login') {
        statusBadgeHtml = `<span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-none text-[10px] font-bold bg-rose-50 text-rose-700 ring-1 ring-rose-600/20">🔴 Failed ${log.failure_reason ? '- ' + log.failure_reason : ''}</span>`;
      } else {
        statusBadgeHtml = `<span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-none text-[10px] font-bold bg-indigo-50 text-indigo-700 ring-1 ring-indigo-600/20">🔑 ${statusText}</span>`;
      }

      let loginTimeSub = '';
      if (log.logout_time) {
        loginTimeSub = `<div class="text-[11px] text-slate-500 dark:text-slate-400 font-normal mt-0.5">Logout: ${logoutTimeFormatted}</div>`;
      } else if (log.login_status === 'Success') {
        loginTimeSub = `<div class="text-[11px] text-emerald-600 font-bold mt-0.5 flex items-center gap-1"><span class="h-1.5 w-1.5 rounded-full bg-emerald-500 animate-pulse"></span> Active Session</div>`;
      } else {
        loginTimeSub = `<div class="text-[11px] text-slate-400 font-normal mt-0.5">—</div>`;
      }

      const esc = window.escapeHtml || (s => s);

      tr.innerHTML = `
        <td class="py-3 px-4 font-mono text-[11px] font-normal text-slate-500 dark:text-slate-400 border-b border-slate-100 dark:border-slate-800/60 align-middle">#LOG-${esc(log.login_id)}</td>
        <td class="py-3 px-4 border-b border-slate-100 dark:border-slate-800/60 align-middle">
          <div class="font-semibold text-slate-900 dark:text-slate-100 text-xs">${esc(actor)}</div>
          <div class="text-[11px] font-normal text-slate-500 dark:text-slate-400 mt-0.5">${esc(role)} | ${esc(department)}</div>
        </td>
        <td class="py-3 px-4 border-b border-slate-100 dark:border-slate-800/60 align-middle">
          <div class="text-xs text-slate-600 dark:text-slate-300 font-normal">Login: ${esc(loginTimeFormatted)}</div>
          ${loginTimeSub}
        </td>
        <td class="py-3 px-4 text-center text-xs text-slate-600 dark:text-slate-300 font-normal border-b border-slate-100 dark:border-slate-800/60 align-middle">${esc(lifespan)}</td>
        <td class="py-3 px-4 border-b border-slate-100 dark:border-slate-800/60 align-middle">${statusBadgeHtml}</td>
        <td class="py-3 px-4 border-b border-slate-100 dark:border-slate-800/60 align-middle">
          <div class="font-mono text-slate-500 dark:text-slate-400 text-[11px] font-normal">${esc(ip)}</div>
          <div class="text-[11px] font-normal text-slate-500 dark:text-slate-400 mt-0.5">${esc(device)} / Caloocan City, PH</div>
        </td>
      `;

      tbody.appendChild(tr);
    });

    // Auto-open audit log modal if audit_id is specified in URL query
    const urlParams = new URLSearchParams(window.location.search);
    const auditIdParam = urlParams.get('audit_id');
    if (auditIdParam) {
      const targetRow = tbody.querySelector(`tr[data-id="#LOG-${auditIdParam}"]`);
      if (targetRow) {
        // Remove audit_id parameter from URL so it doesn't trigger again on pagination/filters
        const newSearch = window.location.search.replace(new RegExp('[?&]audit_id=' + auditIdParam), '').replace(/^&/, '?');
        const cleanUrl = window.location.pathname + newSearch;
        window.history.replaceState({}, document.title, cleanUrl);
        window.civAudit.loginHistory.modal.openSessionModal(targetRow);
      } else {
        const matchIdx = this.filteredLoginLogs.findIndex(log => log.login_id == auditIdParam);
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

window.changePage = function(delta) {
  if (window.civAudit.loginHistory.ui) {
    window.civAudit.loginHistory.ui.changePage(delta);
  }
};

window.goToPage = function(pageNum) {
  if (window.civAudit.loginHistory.ui) {
    window.civAudit.loginHistory.ui.goToPage(pageNum);
  }
};
