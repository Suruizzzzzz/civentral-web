window.civAudit = window.civAudit || {};
window.civAudit.dataChanges = window.civAudit.dataChanges || {};

window.civAudit.dataChanges.ui = {
  currentPage: 1,
  pageSize: 50,
  currentFilteredLogs: [],

  populateModuleDropdown() {
    const moduleSelect = document.getElementById('filterModule');
    if (!moduleSelect) return;
    const currentVal = moduleSelect.value;
    
    let html = '<option value="All">All Modules</option>';
    const api = window.civAudit.dataChanges.api;
    
    const moduleNames = new Set();
    api.availableModules.forEach(m => { if (m.module_name) moduleNames.add(m.module_name); });
    api.auditLogsData.forEach(log => {
      if (log.modules && log.modules.module_name) {
        moduleNames.add(log.modules.module_name);
      } else if (log.target_table) {
        moduleNames.add(log.target_table);
      }
    });

    const esc = window.escapeHtml || (s => s);
    Array.from(moduleNames).sort().forEach(mod => {
      html += `<option value="${esc(mod)}">${esc(mod)}</option>`;
    });

    moduleSelect.innerHTML = html;
    if (currentVal && moduleNames.has(currentVal)) {
      moduleSelect.value = currentVal;
    }
  },

  changePage(delta) {
    const totalPages = Math.ceil(this.currentFilteredLogs.length / this.pageSize) || 1;
    const newPage = this.currentPage + delta;
    if (newPage >= 1 && newPage <= totalPages) {
      this.renderMutationLogs(this.currentFilteredLogs, newPage);
    }
  },

  goToPage(pageNum) {
    const totalPages = Math.ceil(this.currentFilteredLogs.length / this.pageSize) || 1;
    if (pageNum >= 1 && pageNum <= totalPages) {
      this.renderMutationLogs(this.currentFilteredLogs, pageNum);
    }
  },

  renderMutationLogs(logs, page = 1) {
    this.currentFilteredLogs = logs || [];
    this.currentPage = page;

    const tbody = document.getElementById('mutationTableBody');
    if (!tbody) return;

    const total = this.currentFilteredLogs.length;
    const totalPages = Math.ceil(total / this.pageSize) || 1;

    if (this.currentPage > totalPages) this.currentPage = totalPages;
    if (this.currentPage < 1) this.currentPage = 1;

    const startIdx = total > 0 ? (this.currentPage - 1) * this.pageSize : 0;
    const endIdx = Math.min(startIdx + this.pageSize, total);

    const startEl = document.getElementById('paginationStart');
    const endEl = document.getElementById('paginationEnd');
    const totalEl = document.getElementById('paginationTotal');
    const prevBtn = document.getElementById('prevPageBtn');
    const nextBtn = document.getElementById('nextPageBtn');
    const pageNumbersEl = document.getElementById('pageNumbers');

    if (startEl) startEl.innerText = total > 0 ? startIdx + 1 : 0;
    if (endEl) endEl.innerText = endIdx;
    if (totalEl) totalEl.innerText = total;

    if (prevBtn) prevBtn.disabled = (this.currentPage <= 1);
    if (nextBtn) nextBtn.disabled = (this.currentPage >= totalPages);

    if (pageNumbersEl) {
      pageNumbersEl.innerHTML = '';
      let maxVisible = 5;
      let startP = Math.max(1, this.currentPage - 2);
      let endP = Math.min(totalPages, startP + maxVisible - 1);
      if (endP - startP + 1 < maxVisible) {
        startP = Math.max(1, endP - maxVisible + 1);
      }

      for (let p = startP; p <= endP; p++) {
        const btn = document.createElement('button');
        btn.onclick = () => this.goToPage(p);
        btn.className = `h-7 min-w-[28px] px-2 rounded-lg text-xs font-bold transition cursor-pointer ${
          p === this.currentPage
            ? 'bg-[#0f172a] text-white shadow-xs'
            : 'bg-white border border-slate-200 text-slate-700 hover:bg-slate-100'
        }`;
        btn.innerText = p;
        pageNumbersEl.appendChild(btn);
      }
    }

    if (!logs || logs.length === 0) {
      tbody.innerHTML = `
        <tr id="noResultsRow">
          <td colspan="9" class="py-12 text-center text-slate-400">
            <div class="flex flex-col items-center justify-center space-y-2">
              <i class="fa-solid fa-database text-3xl text-slate-300"></i>
              <p class="text-xs font-bold">No data mutation logs found in database</p>
              <p class="text-[10px] font-semibold text-slate-400">New system actions and data edits will automatically appear here.</p>
            </div>
          </td>
        </tr>
      `;
      return;
    }

    let html = '';
    const esc = window.escapeHtml || (s => s);

    const pageLogs = this.currentFilteredLogs.slice(startIdx, endIdx);

    pageLogs.forEach(log => {
      const mutId = `#MUT-${log.audit_id}`;
      const rawDate = log.created_at || '';
      const dateObj = new Date(rawDate);
      const dateStr = !isNaN(dateObj) ? dateObj.toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' }) : 'N/A';
      const timeStr = !isNaN(dateObj) ? dateObj.toLocaleTimeString('en-US', { hour: '2-digit', minute: '2-digit' }) : '';
      const isoDate = rawDate.split(' ')[0] || '';

      let actorName = 'System / Automated';
      if (log.users) {
        actorName = `${log.users.first_name || ''} ${log.users.last_name || ''}`.trim() || log.users.email || 'User';
      }

      const moduleName = (log.modules && log.modules.module_name) ? log.modules.module_name : (log.target_table || 'System');
      const recordId = log.target_id ? `ID: ${log.target_id}` : (log.session_id ? `SESS-${log.session_id}` : 'REC-CORE');

      const actionField = log.action || 'Data Update';
      const isSuccess = (log.status || 'Success') === 'Success';
      const oldVal = isSuccess ? (log.target_table ? `${log.target_table}` : 'Active') : 'Attempted';
      const newVal = log.status || 'Success';

      let oldJsonStr = '{}';
      let newJsonStr = '{}';
      if (log.context_json) {
        try {
          const parsed = typeof log.context_json === 'string' ? JSON.parse(log.context_json) : log.context_json;
          if (parsed.old) oldJsonStr = JSON.stringify(parsed.old, null, 2);
          if (parsed.new) newJsonStr = JSON.stringify(parsed.new, null, 2);
          if (!parsed.old && !parsed.new) {
            newJsonStr = JSON.stringify(parsed, null, 2);
          }
        } catch (e) {
          newJsonStr = JSON.stringify({ raw_context: log.context_json }, null, 2);
        }
      } else {
        newJsonStr = JSON.stringify({
          audit_id: log.audit_id,
          action: log.action,
          target_table: log.target_table,
          target_id: log.target_id,
          ip_address: log.ip_address,
          browser: log.browser,
          request_method: log.request_method,
          request_uri: log.request_uri
        }, null, 2);
      }

      html += `
        <tr class="hover:bg-slate-50/50 transition border-b border-slate-100"
            data-id="${esc(mutId)}"
            data-audit-id="${log.audit_id}"
            data-date="${esc(isoDate)}"
            data-time="${esc(dateStr + ' / ' + timeStr)}"
            data-actor="${esc(actorName)}"
            data-module="${esc(moduleName)}"
            data-record="${esc(recordId)}"
            data-field="${esc(actionField)}"
            data-old="${esc(oldVal)}"
            data-new="${esc(newVal)}"
            data-reason="${esc(log.description || 'No description provided.')}"
            data-ip="${esc(log.ip_address || '127.0.0.1')}"
            data-method="${esc(log.request_method || 'POST')}"
            data-uri="${esc(log.request_uri || '/api')}"
            data-browser="${esc(log.browser || 'Unknown')}"
            data-old-json="${esc(oldJsonStr)}"
            data-new-json="${esc(newJsonStr)}">
          <td class="py-4 px-5 font-mono text-[11px] font-bold text-slate-500">${esc(mutId)}</td>
          <td class="py-4 px-5 whitespace-nowrap">
            <div class="font-bold text-slate-800 font-mono">${esc(dateStr)}</div>
            <div class="text-[10px] text-slate-400 font-semibold mt-0.5">${esc(timeStr)}</div>
          </td>
          <td class="py-4 px-5 font-bold text-slate-800">${esc(actorName)}</td>
          <td class="py-4 px-5">
            <div class="font-bold text-slate-800">${esc(moduleName)}</div>
            <div class="text-[10px] text-slate-400 font-mono mt-0.5">${esc(recordId)}</div>
          </td>
          <td class="py-4 px-5">
            <span class="inline-flex items-center px-2 py-0.5 rounded text-[9px] font-black uppercase bg-slate-100 text-slate-700 border border-slate-200/50">${esc(actionField)}</span>
          </td>
          <td class="py-4 px-5">
            <span class="inline-block bg-rose-50 text-rose-700 line-through px-2.5 py-1 rounded-lg border border-rose-100 font-mono text-[10px] font-semibold">${esc(oldVal)}</span>
          </td>
          <td class="py-4 px-5">
            <span class="inline-block ${isSuccess ? 'bg-emerald-50 text-emerald-800 border-emerald-100' : 'bg-rose-50 text-rose-800 border-rose-100'} font-bold px-2.5 py-1 rounded-lg border font-mono text-[10px]">${esc(newVal)}</span>
          </td>
          <td class="py-4 px-5 text-slate-500 font-medium leading-relaxed max-w-xs truncate" title="${esc(log.description || '')}">${esc(log.description || 'No description.')}</td>
          <td class="py-4 px-5 text-center">
            <button onclick="window.civAudit.dataChanges.modal.openMutationModal(this.closest('tr'))" title="View Snapshot" class="h-8 w-8 inline-flex items-center justify-center text-slate-600 hover:text-slate-900 border border-slate-200 bg-white hover:bg-slate-100 rounded-xl shadow-xs cursor-pointer transition focus:outline-none">
              <i class="fa-solid fa-eye text-xs"></i>
            </button>
          </td>
        </tr>
      `;
    });

    tbody.innerHTML = html;
  }
};
