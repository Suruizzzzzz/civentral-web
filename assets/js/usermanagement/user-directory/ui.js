// USER DIRECTORY UI

// PAGINATION STATE
let currentUserPage = 1;
const userPageSize = 10;
let activeFilteredUsers = [];

window.currentUserPage = currentUserPage;

window.resetUserDirectoryPage = function() {
  currentUserPage = 1;
  window.currentUserPage = 1;
};

window.changeUserDirectoryPage = function(targetPage) {
  currentUserPage = targetPage;
  window.currentUserPage = targetPage;
  renderTable(activeFilteredUsers);
};

// POPULATE DYNAMIC FILTER DROPDOWNS
function populateFilterOptions() {
  const roleFilter = document.getElementById('roleFilter');
  const deptFilter = document.getElementById('deptFilter');

  if (roleFilter) {
    const currentVal = roleFilter.value;
    roleFilter.innerHTML = '<option value="">All Roles</option>';
    availableRoles.forEach(r => {
      const opt = document.createElement('option');
      opt.value = r.role_name;
      opt.textContent = r.role_name;
      roleFilter.appendChild(opt);
    });
    roleFilter.value = currentVal;
  }

  if (deptFilter) {
    const currentVal = deptFilter.value;
    deptFilter.innerHTML = '<option value="">All Departments</option>';
    availableDepartments.forEach(d => {
      const opt = document.createElement('option');
      opt.value = d.department_name;
      opt.textContent = d.department_name;
      deptFilter.appendChild(opt);
    });
    deptFilter.value = currentVal;
  }
}

// POPULATE EDIT MODAL SELECT DROPDOWNS
function populateEditFormOptions() {
  const editRole = document.getElementById('editRole');
  const editDept = document.getElementById('editDept');
  const editPosition = document.getElementById('editPosition');

  if (editRole) {
    editRole.innerHTML = '<option value="">Select Role...</option>';
    availableRoles.forEach(r => {
      if (r.is_superadmin == 1 || r.is_superadmin === true) {
        return;
      }
      const opt = document.createElement('option');
      opt.value = r.role_id;
      opt.textContent = `${r.role_name} (${r.role_prefix})`;
      editRole.appendChild(opt);
    });
  }

  if (editDept) {
    editDept.innerHTML = '<option value="">Select Department...</option>';
    availableDepartments.forEach(d => {
      const opt = document.createElement('option');
      opt.value = d.department_id;
      opt.textContent = d.department_name;
      editDept.appendChild(opt);
    });
  }

  // Handle department change to filter position options
  if (editDept && editPosition) {
    editDept.onchange = function() {
      updatePositionDropdown(this.value);
    };
  }
}

function updatePositionDropdown(deptId, selectedPositionId = '') {
  const editPosition = document.getElementById('editPosition');
  if (!editPosition) return;

  editPosition.innerHTML = '<option value="">Select Position...</option>';
  const filteredPositions = deptId 
    ? availablePositions.filter(p => p.department_id == deptId)
    : availablePositions;

  filteredPositions.forEach(p => {
    const opt = document.createElement('option');
    opt.value = p.position_id;
    opt.textContent = p.position_name;
    editPosition.appendChild(opt);
  });

  if (selectedPositionId) {
    editPosition.value = selectedPositionId;
  }
}

// HELPER: GET USER FULL NAME
function getUserFullName(user) {
  const mid = user.middle_name ? user.middle_name.trim() + ' ' : '';
  return `${user.first_name || ''} ${mid}${user.last_name || ''}`.trim() || 'Unnamed User';
}

// HELPER: GET USER INITIALS
function getUserInitials(user) {
  const f = (user.first_name || 'U').charAt(0).toUpperCase();
  const l = (user.last_name || 'S').charAt(0).toUpperCase();
  return f + l;
}

// RENDER DATATABLE FROM Database records
function renderTable(usersList = systemUsers) {
  activeFilteredUsers = usersList;
  const tbody = document.getElementById('directoryTableBody');
  if (!tbody) return;
  tbody.innerHTML = '';

  const totalItems = activeFilteredUsers.length;
  const totalPages = Math.ceil(totalItems / userPageSize) || 1;

  if (currentUserPage > totalPages) currentUserPage = totalPages;
  if (currentUserPage < 1) currentUserPage = 1;
  window.currentUserPage = currentUserPage;
  
  if (totalItems === 0) {
    tbody.innerHTML = `
      <tr>
        <td colspan="6" class="px-6 py-12 text-center text-slate-400 font-semibold">
          <i class="fa-solid fa-user-xmark text-3xl mb-3 block opacity-60"></i>
          No staff records matched your current query criteria.
        </td>
      </tr>
    `;
    const pagEl = document.getElementById('paginationText');
    if (pagEl) pagEl.innerText = "Showing 0 to 0 of 0 profiles";
    renderUserPaginationControls(0, 1, 1);
    return;
  }

  const startIndex = (currentUserPage - 1) * userPageSize;
  const endIndex = Math.min(startIndex + userPageSize, totalItems);
  const pageUsers = activeFilteredUsers.slice(startIndex, endIndex);

  const isSuperAdmin = currentUserScope ? !!currentUserScope.is_superadmin : false;
  const grantedActions = currentUserScope ? (currentUserScope.granted_actions || []) : [];
  const canEdit = isSuperAdmin || grantedActions.includes('EDIT');
  const canDelete = isSuperAdmin || grantedActions.includes('DELETE');

  pageUsers.forEach(user => {
    const fullName = getUserFullName(user);
    const initials = getUserInitials(user);

    const roleObj = user.roles || {};
    const roleName = roleObj.role_name || 'Staff';
    const rolePrefix = roleObj.role_prefix || 'STF';

    const posObj = user.positions || {};
    const positionName = posObj.position_name || 'Staff Member';
    const deptObj = posObj.departments || {};
    const deptName = deptObj.department_name || 'Unassigned';

    // Role style binding
    let roleStyles = 'bg-slate-100 text-slate-600 border-slate-200';
    if (roleName === 'Super Admin' || rolePrefix === 'SADM') roleStyles = 'bg-purple-50 text-purple-700 border-purple-100/60';
    else if (roleName.includes('Admin') || rolePrefix.includes('ADM')) roleStyles = 'bg-blue-50 text-blue-700 border-blue-100/60';

    // Status style binding
    let statusStyles = 'bg-emerald-50 text-emerald-700 border-emerald-100/60';
    let dotStatus = '<span class="h-1.5 w-1.5 rounded-full bg-emerald-500 animate-pulse"></span>';
    if (user.status === 'Inactive' || user.status === 'Archived' || user.status === 'Deactivated') {
      statusStyles = 'bg-rose-50 text-rose-600 border-rose-100/60';
      dotStatus = '<span class="h-1.5 w-1.5 rounded-full bg-rose-400"></span>';
    } else if (user.status === 'Pending' || user.status === 'Locked') {
      statusStyles = 'bg-amber-50 text-amber-700 border-amber-100/60';
      dotStatus = '<span class="h-1.5 w-1.5 rounded-full bg-amber-500"></span>';
    }

    // Dynamic colors for avatar initial background
    const colors = ['bg-blue-500', 'bg-indigo-500', 'bg-purple-500', 'bg-rose-500', 'bg-emerald-500', 'bg-slate-500', 'bg-amber-600'];
    let colorIndex = (fullName.charCodeAt(0) + fullName.charCodeAt(fullName.length - 1)) % colors.length;
    let avatarBg = colors[colorIndex];

    let avatarHTML = `
      <div class="h-9 w-9 rounded-none ${avatarBg} text-white font-extrabold flex items-center justify-center text-xs shadow-xs border border-white/20 shrink-0">
        ${initials}
      </div>
    `;
    if (user.profile_picture && user.profile_picture !== 'default-avatar.png') {
      let pic = user.profile_picture;
      if (!pic.startsWith('http') && !pic.startsWith('data:')) {
        const bPath = window.civentralBasePath || '../../';
        pic = bPath + pic.replace(/^\/+/, '');
      }
      avatarHTML = `<img src="${pic}" alt="${fullName}" class="h-9 w-9 rounded-none object-cover border border-slate-200 shadow-xs shrink-0">`;
    }

    const tr = document.createElement('tr');
    tr.className = 'hover:bg-slate-50/60 dark:hover:bg-slate-800/40 transition group';
    tr.innerHTML = `
      <td class="py-3 px-4 border-b border-slate-100 dark:border-slate-800/60 align-middle">
        <div class="flex items-center space-x-3">
          ${avatarHTML}
          <div class="flex flex-col">
            <span class="font-semibold text-slate-900 dark:text-slate-100 text-xs tracking-tight leading-snug">${fullName}</span>
            <span class="text-[11px] font-normal text-slate-500 dark:text-slate-400">${user.email}</span>
          </div>
        </div>
      </td>
      <td class="py-3 px-4 font-mono text-[11px] font-normal text-slate-500 dark:text-slate-400 border-b border-slate-100 dark:border-slate-800/60 align-middle">
        ${user.employee_id}
      </td>
      <td class="py-3 px-4 border-b border-slate-100 dark:border-slate-800/60 align-middle">
        <div class="flex flex-col">
          <span class="font-semibold text-slate-900 dark:text-slate-100 text-xs leading-snug">${deptName}</span>
          <span class="text-[11px] font-normal text-slate-500 dark:text-slate-400">${positionName}</span>
        </div>
      </td>
      <td class="py-3 px-4 border-b border-slate-100 dark:border-slate-800/60 align-middle">
        <span class="text-[10px] font-bold uppercase px-2 py-0.5 rounded-none border ${roleStyles}">
          ${roleName}
        </span>
      </td>
      <td class="py-3 px-4 border-b border-slate-100 dark:border-slate-800/60 align-middle">
        <span class="text-[10px] font-bold uppercase px-2.5 py-0.5 rounded-none border ${statusStyles} flex items-center gap-1.5 w-fit">
          ${dotStatus} ${user.status}
        </span>
      </td>
      <td class="py-3 px-4 text-right whitespace-nowrap border-b border-slate-100 dark:border-slate-800/60 align-middle">
        <div class="inline-flex items-center space-x-1">
          <button onclick="if(typeof openViewModal === 'function') openViewModal(${user.user_id})" class="text-slate-400 hover:text-brand-dark hover:bg-brand-light hover:border-brand-border/40 p-1.5 rounded-none border border-transparent transition cursor-pointer" title="View Profile">
            <i class="fa-solid fa-eye text-xs"></i>
          </button>
          ${canEdit ? `
          <button onclick="if(typeof openEditModal === 'function') openEditModal(${user.user_id})" class="text-slate-400 hover:text-amber-600 hover:bg-amber-50 hover:border-amber-100 p-1.5 rounded-none border border-transparent transition cursor-pointer" title="Edit Profile">
            <i class="fa-solid fa-pen text-xs"></i>
          </button>` : ''}
          <button onclick="if(typeof openHistoryModal === 'function') openHistoryModal(${user.user_id})" class="text-slate-400 hover:text-blue-600 hover:bg-blue-50 hover:border-blue-100 p-1.5 rounded-none border border-transparent transition cursor-pointer" title="Security Audit Log">
            <i class="fa-solid fa-clock-rotate-left text-xs"></i>
          </button>
          ${canDelete ? `
          <button onclick="if(typeof openArchiveUserModal === 'function') openArchiveUserModal(${user.user_id})" class="text-slate-400 hover:text-amber-600 hover:bg-amber-50 hover:border-amber-100 p-1.5 rounded-none border border-transparent transition cursor-pointer ${user.status === 'Archived' ? 'opacity-40 cursor-not-allowed' : ''}" ${user.status === 'Archived' ? 'disabled' : ''} title="Archive User Account">
            <i class="fa-solid fa-box-archive text-xs"></i>
          </button>` : ''}
        </div>
      </td>
    `;
    tbody.appendChild(tr);
  });

  const pagEl = document.getElementById('paginationText');
  if (pagEl) {
    pagEl.innerText = `Showing ${startIndex + 1} to ${endIndex} of ${totalItems} profiles`;
  }
  renderUserPaginationControls(totalItems, totalPages, currentUserPage);
}

// RENDER USER DIRECTORY PAGINATION CONTROLS
function renderUserPaginationControls(totalItems, totalPages, currentPage) {
  const prevBtn = document.getElementById('userPrevBtn');
  const nextBtn = document.getElementById('userNextBtn');
  const pageNumbersContainer = document.getElementById('userPageNumbers');

  if (prevBtn) {
    const isPrevDisabled = currentPage <= 1 || totalItems === 0;
    prevBtn.disabled = isPrevDisabled;
    if (isPrevDisabled) {
      prevBtn.className = "px-3 py-1.5 rounded-none border border-slate-200 bg-white text-slate-300 cursor-not-allowed transition";
    } else {
      prevBtn.className = "px-3 py-1.5 rounded-none border border-slate-200 bg-white hover:bg-slate-50 text-slate-600 cursor-pointer transition";
    }
    prevBtn.onclick = () => {
      if (!isPrevDisabled) window.changeUserDirectoryPage(currentPage - 1);
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
      if (!isNextDisabled) window.changeUserDirectoryPage(currentPage + 1);
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
      firstBtn.onclick = () => window.changeUserDirectoryPage(1);
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
      pageBtn.onclick = () => window.changeUserDirectoryPage(p);
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
      lastBtn.onclick = () => window.changeUserDirectoryPage(totalPages);
      pageNumbersContainer.appendChild(lastBtn);
    }
  }
}

// UPDATE METRICS BOARD
function updateMetrics() {
  const total = systemUsers.length;
  const active = systemUsers.filter(u => u.status === 'Active').length;
  const inactive = systemUsers.filter(u => u.status === 'Inactive' || u.status === 'Archived' || u.status === 'Deactivated').length;

  const uniqueDepts = new Set();
  systemUsers.forEach(u => {
    if (u.positions && u.positions.departments && u.positions.departments.department_name) {
      uniqueDepts.add(u.positions.departments.department_name);
    }
  });

  const statTotal = document.getElementById('statTotalUsers');
  const statActive = document.getElementById('statActiveUsers');
  const statDeactive = document.getElementById('statDeactivatedUsers');
  const statDepts = document.getElementById('statDepts');
  const statBadge = document.getElementById('statActiveBadge');

  if (statTotal) statTotal.innerText = total.toLocaleString();
  if (statActive) statActive.innerText = active.toLocaleString();
  if (statDeactive) statDeactive.innerText = inactive.toLocaleString();
  if (statDepts) statDepts.innerText = uniqueDepts.size.toLocaleString();

  const activePct = total > 0 ? Math.round((active / total) * 100) : 0;
  if (statBadge) {
    statBadge.innerHTML = `
      <span class="h-1.5 w-1.5 rounded-full bg-emerald-500 animate-pulse"></span> ${activePct}%
    `;
  }
}

// EXPORT TO CSV FROM REAL DATABASE RECORDS
function exportToCSV() {
  let csvContent = "data:text/csv;charset=utf-8,";
  csvContent += "Employee ID,First Name,Last Name,Email Address,Mobile Number,Department,Position,Role,Status,Created At\r\n";

  const searchInput = document.getElementById('searchInput');
  const roleFilter = document.getElementById('roleFilter');
  const deptFilter = document.getElementById('deptFilter');

  const query = searchInput ? searchInput.value.toLowerCase().trim() : '';
  const roleVal = roleFilter ? roleFilter.value : '';
  const deptVal = deptFilter ? deptFilter.value : '';

  const filtered = systemUsers.filter(user => {
    const fullName = getUserFullName(user).toLowerCase();
    const email = (user.email || '').toLowerCase();
    const empId = (user.employee_id || '').toLowerCase();
    const matchQuery = fullName.includes(query) || email.includes(query) || empId.includes(query);

    const userRole = user.roles ? user.roles.role_name : '';
    const matchRole = roleVal === '' || userRole === roleVal;

    const userDept = (user.positions && user.positions.departments) ? user.positions.departments.department_name : '';
    const matchDept = deptVal === '' || userDept === deptVal;

    return matchQuery && matchRole && matchDept;
  });

  filtered.forEach(user => {
    const dept = (user.positions && user.positions.departments) ? user.positions.departments.department_name : '';
    const pos = user.positions ? user.positions.position_name : '';
    const role = user.roles ? user.roles.role_name : '';
    const created = user.created_at ? user.created_at.split('T')[0] : '';
    const row = `"${user.employee_id}","${user.first_name}","${user.last_name}","${user.email}","${user.mobile_number}","${dept}","${pos}","${role}","${user.status}","${created}"`;
    csvContent += row + "\r\n";
  });

  const encodedUri = encodeURI(csvContent);
  const link = document.createElement("a");
  link.setAttribute("href", encodedUri);
  link.setAttribute("download", `Civentral_User_Directory_${new Date().toISOString().split('T')[0]}.csv`);
  document.body.appendChild(link);
  link.click();
  document.body.removeChild(link);

  if (typeof showToast === 'function') showToast(`Exported ${filtered.length} staff records to CSV.`);
}
