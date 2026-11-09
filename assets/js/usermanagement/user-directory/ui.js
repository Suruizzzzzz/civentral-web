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
    if (pagEl) {
      pagEl.innerHTML = `Showing <span class="font-semibold text-slate-700 dark:text-slate-200">0</span> to <span class="font-semibold text-slate-700 dark:text-slate-200">0</span> of <span class="font-semibold text-slate-700 dark:text-slate-200">0</span> records`;
    }
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
        <div class="inline-flex items-center space-x-1.5">
          <button onclick="if(typeof openViewModal === 'function') openViewModal(${user.user_id})" class="w-7 h-7 flex items-center justify-center border rounded-none transition cursor-pointer shadow-2xs border-sky-300 bg-sky-50 text-sky-600 hover:bg-sky-100 hover:text-sky-700 dark:border-sky-800/80 dark:bg-sky-950/40 dark:text-sky-400 dark:hover:bg-sky-900/60 dark:hover:text-sky-300" title="View Profile">
            <i class="fa-solid fa-eye text-xs"></i>
          </button>
          ${canEdit ? `
          <button onclick="if(typeof openEditModal === 'function') openEditModal(${user.user_id})" class="w-7 h-7 flex items-center justify-center border rounded-none transition cursor-pointer shadow-2xs border-amber-300 bg-amber-50 text-amber-600 hover:bg-amber-100 hover:text-amber-700 dark:border-amber-800/80 dark:bg-amber-950/40 dark:text-amber-400 dark:hover:bg-amber-900/60 dark:hover:text-amber-300" title="Edit Profile">
            <i class="fa-solid fa-pen text-xs"></i>
          </button>` : ''}
          ${canDelete ? `
          <button onclick="if(typeof openArchiveUserModal === 'function') openArchiveUserModal(${user.user_id})" class="w-7 h-7 flex items-center justify-center border rounded-none transition cursor-pointer shadow-2xs border-rose-300 bg-rose-50 text-rose-600 hover:bg-rose-100 hover:text-rose-700 dark:border-rose-800/80 dark:bg-rose-950/40 dark:text-rose-400 dark:hover:bg-rose-900/60 dark:hover:text-rose-300 ${user.status === 'Archived' ? 'opacity-40 cursor-not-allowed' : ''}" ${user.status === 'Archived' ? 'disabled' : ''} title="Archive User Account">
            <i class="fa-solid fa-box-archive text-xs"></i>
          </button>` : ''}
        </div>
      </td>
    `;
    tbody.appendChild(tr);
  });

  const pagEl = document.getElementById('paginationText');
  if (pagEl) {
    pagEl.innerHTML = `Showing <span class="font-semibold text-slate-700 dark:text-slate-200">${startIndex + 1}</span> to <span class="font-semibold text-slate-700 dark:text-slate-200">${endIndex}</span> of <span class="font-semibold text-slate-700 dark:text-slate-200">${totalItems}</span> records`;
  }
  renderUserPaginationControls(totalItems, totalPages, currentUserPage);
}

// RENDER USER DIRECTORY PAGINATION CONTROLS
function renderUserPaginationControls(totalItems, totalPages, currentPage) {
  const container = document.getElementById('paginationControls');
  if (!container) return;

  const isPrevDisabled = currentPage <= 1 || totalItems === 0;
  const isNextDisabled = currentPage >= totalPages || totalItems === 0;

  let html = `
    <!-- Previous Button -->
    <button type="button" class="${isPrevDisabled ? 'px-3 py-1.5 text-xs font-medium text-slate-300 dark:text-slate-600 bg-slate-50/50 dark:bg-slate-800/30 cursor-not-allowed' : 'px-3 py-1.5 text-xs font-medium text-slate-500 hover:text-slate-700 hover:bg-slate-50 dark:text-slate-400 dark:hover:text-slate-200 dark:hover:bg-slate-800/60 transition cursor-pointer'}" 
            ${isPrevDisabled ? 'disabled' : ''} onclick="window.changeUserDirectoryPage(${currentPage - 1})" aria-label="Previous Page">
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
        html += `<button type="button" class="px-3.5 py-1.5 text-xs font-medium text-slate-600 hover:text-slate-900 hover:bg-slate-50 dark:text-slate-400 dark:hover:text-slate-200 dark:hover:bg-slate-800/60 border-l border-slate-200 dark:border-slate-700 transition cursor-pointer" onclick="window.changeUserDirectoryPage(${p})">${p}</button>`;
      }
    });
  }

  html += `
    <!-- Next Button -->
    <button type="button" class="${isNextDisabled ? 'px-3 py-1.5 text-xs font-medium text-slate-300 dark:text-slate-600 bg-slate-50/50 dark:bg-slate-800/30 cursor-not-allowed border-l border-slate-200 dark:border-slate-700' : 'px-3 py-1.5 text-xs font-medium text-slate-500 hover:text-slate-700 hover:bg-slate-50 dark:text-slate-400 dark:hover:text-slate-200 dark:hover:bg-slate-800/60 border-l border-slate-200 dark:border-slate-700 transition cursor-pointer'}" 
            ${isNextDisabled ? 'disabled' : ''} onclick="window.changeUserDirectoryPage(${currentPage + 1})" aria-label="Next Page">
      <i class="fa-solid fa-chevron-right text-[10px]"></i>
    </button>
  `;

  container.innerHTML = html;
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
