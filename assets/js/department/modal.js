function openModal(id) {
  const modal = document.getElementById(id);
  if (!modal) return;
  modal.classList.remove('opacity-0', 'pointer-events-none');
  modal.classList.add('opacity-100', 'pointer-events-auto');
  modal.querySelector('.transform').classList.remove('scale-95');
  modal.querySelector('.transform').classList.add('scale-100');
}

function closeModal(id) {
  const modal = document.getElementById(id);
  if (!modal) return;
  modal.classList.remove('opacity-100', 'pointer-events-auto');
  modal.classList.add('opacity-0', 'pointer-events-none');
  modal.querySelector('.transform').classList.remove('scale-100');
  modal.querySelector('.transform').classList.add('scale-95');
}

// Populate Admin Dropdown
function populateAdminDropdown(selectedUserId = null) {
  const adminSelect = document.getElementById('deptAdmin');
  if (!adminSelect) return;

  adminSelect.innerHTML = '<option value="">Unassigned</option>';
  usersData.forEach(u => {
    const fullName = `${u.first_name} ${u.last_name}`;
    const opt = document.createElement('option');
    opt.value = u.user_id;
    opt.textContent = fullName;
    if (selectedUserId && parseInt(selectedUserId) === u.user_id) {
      opt.selected = true;
    }
    adminSelect.appendChild(opt);
  });
}

// Create Modal
function openCreateModal() {
  const isSuperAdmin = currentUserScope ? !!currentUserScope.is_superadmin : false;
  const grantedActions = currentUserScope ? (currentUserScope.granted_actions || []) : [];
  const canCreate = isSuperAdmin || grantedActions.includes('CREATE');

  if (!canCreate) {
    if (typeof showToast === 'function') showToast('Forbidden. View-only access level cannot create departments.');
    return;
  }

  const deptForm = document.getElementById('deptForm');
  if (deptForm) deptForm.reset();
  const deptIdRef = document.getElementById('deptIdRef');
  if (deptIdRef) deptIdRef.value = '';
  const modalTitle = document.getElementById('deptModalTitle');
  if (modalTitle) modalTitle.innerText = "Create Department";
  const modalIcon = document.getElementById('deptModalIcon');
  if (modalIcon) modalIcon.className = "fa-solid fa-building text-brand-medium";
  if (typeof populateAdminDropdown === 'function') populateAdminDropdown();
  openModal('deptModal');
}

// Edit Modal
function openEditModal(deptId) {
  const isSuperAdmin = currentUserScope ? !!currentUserScope.is_superadmin : false;
  const grantedActions = currentUserScope ? (currentUserScope.granted_actions || []) : [];
  const canEdit = isSuperAdmin || grantedActions.includes('EDIT');

  if (!canEdit) {
    if (typeof showToast === 'function') showToast('Forbidden. View-only access level cannot modify departments.');
    return;
  }

  const dept = departmentsData.find(d => d.department_id === deptId);
  if (!dept) return;

  document.getElementById('deptIdRef').value = dept.department_id;
  document.getElementById('deptName').value = dept.department_name;
  document.getElementById('deptCode').value = dept.department_code;
  document.getElementById('deptDesc').value = dept.description || '';

  populateAdminDropdown(dept.department_head_user_id);

  document.getElementById('deptModalTitle').innerText = "Modify Department Parameters";
  document.getElementById('deptModalIcon').className = "fa-solid fa-pen text-brand-medium";
  openModal('deptModal');
}

// View Details Modal
function openViewModal(deptId) {
  const dept = departmentsData.find(d => d.department_id === deptId);
  if (!dept) return;

  const adminObj = dept.users || null;
  const adminName = adminObj ? `${adminObj.first_name} ${adminObj.last_name}` : 'Unassigned';

  const codeEl = document.getElementById('viewDeptCodeBadge');
  const nameEl = document.getElementById('viewDeptName');
  const descEl = document.getElementById('viewDeptDesc');
  const staffCountEl = document.getElementById('viewDeptStaffCount');
  const adminNameEl = document.getElementById('viewDeptAdminName');

  if (codeEl) codeEl.innerText = dept.department_code || '—';
  if (nameEl) nameEl.innerText = dept.department_name || '—';
  if (descEl) descEl.innerText = dept.description || "No description provided.";
  
  // Resolve staff count properly
  const staffCount = dept.staff_count !== undefined ? dept.staff_count : (dept.users_count !== undefined ? dept.users_count : 0);
  if (staffCountEl) staffCountEl.innerText = `${staffCount} registered staff`;
  if (adminNameEl) adminNameEl.innerText = `Head: ${adminName}`;

  const scopesContainer = document.getElementById('viewDeptScopesContainer');
  if (scopesContainer) {
    scopesContainer.innerHTML = `<span class="bg-slate-150 text-slate-700 px-2.5 py-1 rounded-none text-[10px] font-black border border-slate-200/60 font-mono tracking-tight">${(dept.department_code || 'dept').toLowerCase()}_core</span>`;
  }

  // Reset Recent Admin Logs to clean empty state
  const logsBody = document.getElementById('recentAdminLogsBody');
  if (logsBody) {
    logsBody.innerHTML = `
      <tr>
        <td colspan="3" class="text-center py-4 text-slate-400 text-xs">No recent admin actions recorded.</td>
      </tr>
    `;
  }

  openModal('viewDeptModal');
}
