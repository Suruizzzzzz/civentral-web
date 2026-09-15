// USER DIRECTORY MODALS

// MODAL CONTROL HELPERS
function openModal(id) {
  const modal = document.getElementById(id);
  if (!modal) return;
  modal.classList.remove('opacity-0', 'pointer-events-none');
  modal.classList.add('opacity-100', 'pointer-events-auto');
  const card = modal.querySelector('.transform');
  if (card) {
    card.classList.remove('scale-95');
    card.classList.add('scale-100');
  }
}

function closeModal(id) {
  const modal = document.getElementById(id);
  if (!modal) return;
  modal.classList.remove('opacity-100', 'pointer-events-auto');
  modal.classList.add('opacity-0', 'pointer-events-none');
  const card = modal.querySelector('.transform');
  if (card) {
    card.classList.remove('scale-100');
    card.classList.add('scale-95');
  }
}

// VIEW PROFILE MODAL
function openViewModal(userId) {
  const user = systemUsers.find(u => u.user_id === userId);
  if (!user) return;

  const fullName = typeof getUserFullName === 'function' ? getUserFullName(user) : '';
  const initials = typeof getUserInitials === 'function' ? getUserInitials(user) : '';
  const roleName = user.roles ? user.roles.role_name : 'Staff';
  const posName = user.positions ? user.positions.position_name : 'Unassigned';
  const deptName = (user.positions && user.positions.departments) ? user.positions.departments.department_name : 'Unassigned';

  document.getElementById('viewName').innerText = fullName;
  document.getElementById('viewEmail').innerText = user.email;
  document.getElementById('viewEmpId').innerText = user.employee_id;
  document.getElementById('viewPhone').innerText = user.mobile_number || 'N/A';
  document.getElementById('viewDept').innerText = deptName;
  document.getElementById('viewPosition').innerText = posName;
  document.getElementById('viewCreated').innerText = user.created_at ? user.created_at.split('T')[0] : 'N/A';

  const imgEl = document.getElementById('viewAvatarImg');
  const textEl = document.getElementById('viewInitialsText');

  if (user.profile_picture && user.profile_picture !== 'default-avatar.png') {
    if (imgEl && textEl) {
      imgEl.src = user.profile_picture;
      imgEl.classList.remove('hidden');
      textEl.classList.add('hidden');
    }
  } else {
    if (imgEl && textEl) {
      imgEl.classList.add('hidden');
      textEl.classList.remove('hidden');
      textEl.innerText = initials;
    }
  }

  const roleBadge = document.getElementById('viewRoleBadge');
  if (roleBadge) {
    roleBadge.innerText = roleName;
    roleBadge.className = "text-[9px] font-black uppercase px-2 py-0.5 rounded-full border ";
    if (roleName === 'Super Admin' || roleName === 'Superadmin') roleBadge.classList.add('bg-purple-50', 'text-purple-700', 'border-purple-100');
    else if (roleName.includes('Admin')) roleBadge.classList.add('bg-blue-50', 'text-blue-700', 'border-blue-100');
    else roleBadge.classList.add('bg-slate-50', 'text-slate-600', 'border-slate-200');
  }

  const statusBadge = document.getElementById('viewStatusBadge');
  if (statusBadge) {
    statusBadge.innerText = user.status;
    statusBadge.className = "text-[9px] font-black uppercase px-2 py-0.5 rounded-full border ";
    if (user.status === 'Active') statusBadge.classList.add('bg-emerald-50', 'text-emerald-700', 'border-emerald-100');
    else statusBadge.classList.add('bg-rose-50', 'text-rose-600', 'border-rose-100');
  }

  const permList = document.getElementById('viewPermissionsList');
  if (permList) {
    permList.innerHTML = `
      <div class="flex items-center space-x-2 bg-slate-50 p-2 border border-slate-100 rounded-lg">
        <i class="fa-solid fa-circle-check text-emerald-500 shrink-0 text-[10px]"></i>
        <span class="text-[11px] font-semibold text-slate-600">Employee Role Access Granted</span>
      </div>
      <div class="flex items-center space-x-2 bg-slate-50 p-2 border border-slate-100 rounded-lg">
        <i class="fa-solid fa-circle-check text-emerald-500 shrink-0 text-[10px]"></i>
        <span class="text-[11px] font-semibold text-slate-600">${user.roles && user.roles.is_global_access ? 'Global Access Scope' : 'Department Access Scope'}</span>
      </div>
    `;
  }

  openModal('viewModal');
}

// EDIT STAFF ACCOUNT MODAL
function openEditModal(userId) {
  const isSuperAdmin = currentUserScope ? !!currentUserScope.is_superadmin : false;
  const grantedActions = currentUserScope ? (currentUserScope.granted_actions || []) : [];
  const canEdit = isSuperAdmin || grantedActions.includes('EDIT');

  if (!canEdit) {
    if (typeof showToast === 'function') showToast('Forbidden. View-only access level cannot modify user accounts.', true);
    return;
  }

  const user = systemUsers.find(u => u.user_id === userId);
  if (!user) return;

  const fullName = typeof getUserFullName === 'function' ? getUserFullName(user) : '';

  document.getElementById('editEmpIdRef').value = user.user_id;
  document.getElementById('editName').value = fullName;
  document.getElementById('editEmail').value = user.email;
  document.getElementById('editPhone').value = user.mobile_number || '';
  
  let currentDeptId = '';
  let currentDeptName = '';
  if (user.positions && user.positions.departments) {
    currentDeptId = user.positions.departments.department_id || '';
    currentDeptName = user.positions.departments.department_name || '';
  } else if (user.positions && user.positions.department_id) {
    currentDeptId = user.positions.department_id;
  }

  if (!currentDeptId && user.position_id && typeof availablePositions !== 'undefined') {
    const matchedPos = availablePositions.find(p => p.position_id == user.position_id);
    if (matchedPos && matchedPos.department_id) {
      currentDeptId = matchedPos.department_id;
    }
  }

  if (!currentDeptName && currentDeptId && typeof availableDepartments !== 'undefined') {
    const matchedDept = availableDepartments.find(d => d.department_id == currentDeptId);
    if (matchedDept && matchedDept.department_name) {
      currentDeptName = matchedDept.department_name;
    }
  }

  const currentPosId = user.position_id || '';

  const editDept = document.getElementById('editDept');
  if (editDept) {
    if (!isSuperAdmin) {
      editDept.innerHTML = `<option value="${currentDeptId}">${currentDeptName || 'Current Department'}</option>`;
      editDept.value = currentDeptId;
      editDept.disabled = true;
      editDept.classList.add('bg-slate-100', 'cursor-not-allowed', 'text-slate-500');
    } else {
      if (typeof populateEditFormOptions === 'function') {
        populateEditFormOptions();
      }
      editDept.value = currentDeptId;
      editDept.disabled = false;
      editDept.classList.remove('bg-slate-100', 'cursor-not-allowed', 'text-slate-500');
    }
  }

  if (typeof updatePositionDropdown === 'function') updatePositionDropdown(currentDeptId, currentPosId);

  const editRole = document.getElementById('editRole');
  if (editRole) editRole.value = user.role_id || '';

  const editStatus = document.getElementById('editStatus');
  if (editStatus) editStatus.value = user.status || 'Active';

  // Always reset the reset-password sub-pane to default state when opening
  const _rpFormFields   = document.getElementById('editFormFields');
  const _rpFooter       = document.getElementById('editModalFooter');
  const _rpResetPane    = document.getElementById('resetPasswordPane');
  const _rpOtpContainer = document.getElementById('otpFieldContainer');
  const _rpConfirmBtns  = document.getElementById('resetConfirmButtons');
  if (_rpFormFields)   _rpFormFields.classList.remove('hidden');
  if (_rpFooter)       _rpFooter.classList.remove('hidden');
  if (_rpResetPane)    _rpResetPane.classList.add('hidden');
  if (_rpOtpContainer) _rpOtpContainer.classList.add('hidden');
  if (_rpConfirmBtns)  _rpConfirmBtns.classList.remove('hidden');
  const _rpOtpInput = document.getElementById('otpInput');
  if (_rpOtpInput) _rpOtpInput.value = '';
  resetPasswordTargetUserId = null;

  openModal('editModal');
}

// SECURITY LOGIN AUDIT LOGS MODAL
function openHistoryModal(userId) {
  const user = systemUsers.find(u => u.user_id === userId);
  if (!user) return;

  const fullName = typeof getUserFullName === 'function' ? getUserFullName(user) : '';
  document.getElementById('historyStaffName').innerText = `${fullName} (${user.employee_id})`;
  
  const hBody = document.getElementById('historyTableBody');
  hBody.innerHTML = '';

  if (user.login_history && user.login_history.length > 0) {
    user.login_history.forEach(log => {
      const loginTime = log.login_time ? log.login_time.replace('T', ' ').substring(0, 19) : '—';
      hBody.innerHTML += `
        <tr class="hover:bg-slate-50/50 transition">
          <td class="px-4 py-3 font-medium text-slate-700">${loginTime}</td>
          <td class="px-4 py-3 font-mono font-bold text-slate-500">${log.ip_address || '—'}</td>
          <td class="px-4 py-3 text-slate-500">${log.browser || log.device_info || 'Browser Terminal'}</td>
          <td class="px-4 py-3 text-right">
            <span class="text-[9px] font-black uppercase ${log.login_status === 'Failed' ? 'bg-rose-50 border-rose-100 text-rose-700' : 'bg-emerald-50 border-emerald-100 text-emerald-700'} border px-1.5 py-0.5 rounded-md">
              ${log.login_status || 'Success'}
            </span>
          </td>
        </tr>
      `;
    });
  } else {
    hBody.innerHTML = `
      <tr>
        <td colspan="4" class="px-4 py-6 text-center text-slate-400">
          No security login history records found IN DATABASE.
        </td>
      </tr>
    `;
  }

  openModal('historyModal');
}

// ARCHIVE USER ACCOUNT MODAL
var archiveTargetUserId = null;

function openArchiveUserModal(userId) {
  const isSuperAdmin = currentUserScope ? !!currentUserScope.is_superadmin : false;
  const grantedActions = currentUserScope ? (currentUserScope.granted_actions || []) : [];
  const canDelete = isSuperAdmin || grantedActions.includes('DELETE');

  if (!canDelete) {
    if (typeof showToast === 'function') showToast('Forbidden. View-only access level cannot delete or archive user accounts.', true);
    return;
  }

  const user = systemUsers.find(u => u.user_id === userId);
  if (!user) return;

  archiveTargetUserId = userId;
  const fullName = typeof getUserFullName === 'function' ? getUserFullName(user) : '';
  const targetNameEl = document.getElementById('archiveTargetUserName');
  if (targetNameEl) targetNameEl.innerText = `User: ${fullName} (${user.employee_id})`;

  openModal('archiveModal');
}

async function confirmArchiveUser() {
  if (!archiveTargetUserId) return;
  const targetId = archiveTargetUserId;
  archiveTargetUserId = null;
  closeModal('archiveModal');

  try {
    const response = await fetch(`../../api/employee/users.php?user_id=${targetId}`, {
      method: 'DELETE',
      headers: { 'Content-Type': 'application/json' }
    });
    const result = await response.json();

    if (result.status === 'success') {
      if (typeof showToast === 'function') showToast(result.message || 'User account archived successfully.');
      if (typeof fetchSystemUsers === 'function') await fetchSystemUsers();
    } else {
      if (typeof showToast === 'function') showToast(result.message || 'Failed to archive user account.', true);
    }
  } catch (err) {
    console.error('Error archiving user:', err);
    if (typeof showToast === 'function') showToast('Failed to archive user account IN DATABASE.', true);
  }
}

window.openArchiveUserModal = openArchiveUserModal;
window.confirmArchiveUser = confirmArchiveUser;

// ADMIN RESET PASSWORD
var resetPasswordTargetUserId = null;

function triggerResetPassword() {
  const userId = parseInt(document.getElementById('editEmpIdRef').value);
  if (!userId) return;
  resetPasswordTargetUserId = userId;

  const user = systemUsers.find(u => u.user_id === userId);
  const fullName = user && typeof getUserFullName === 'function' ? getUserFullName(user) : 'this user';
  const targetEmail = user ? (user.email || '') : '';

  const msgEl = document.getElementById('resetConfirmMessage');
  if (msgEl) {
    msgEl.innerText = `Send a password reset authorization code to ${fullName}'s registered email (${targetEmail})?`;
  }

  // Show reset pane, hide form fields and footer
  const formFields = document.getElementById('editFormFields');
  const footer     = document.getElementById('editModalFooter');
  const resetPane  = document.getElementById('resetPasswordPane');
  const otpContainer = document.getElementById('otpFieldContainer');
  const confirmBtns  = document.getElementById('resetConfirmButtons');

  if (formFields)    formFields.classList.add('hidden');
  if (footer)        footer.classList.add('hidden');
  if (resetPane)     resetPane.classList.remove('hidden');
  if (otpContainer)  otpContainer.classList.add('hidden');
  if (confirmBtns)   confirmBtns.classList.remove('hidden');

  const otpInput = document.getElementById('otpInput');
  if (otpInput) otpInput.value = '';
}

function cancelResetPassword() {
  resetPasswordTargetUserId = null;

  const formFields   = document.getElementById('editFormFields');
  const footer       = document.getElementById('editModalFooter');
  const resetPane    = document.getElementById('resetPasswordPane');
  const otpContainer = document.getElementById('otpFieldContainer');
  const confirmBtns  = document.getElementById('resetConfirmButtons');

  if (formFields)   formFields.classList.remove('hidden');
  if (footer)       footer.classList.remove('hidden');
  if (resetPane)    resetPane.classList.add('hidden');
  if (otpContainer) otpContainer.classList.add('hidden');
  if (confirmBtns)  confirmBtns.classList.remove('hidden');

  const otpInput = document.getElementById('otpInput');
  if (otpInput) otpInput.value = '';
}

async function confirmResetPassword() {
  if (!resetPasswordTargetUserId) return;

  const confirmBtns  = document.getElementById('resetConfirmButtons');
  const otpContainer = document.getElementById('otpFieldContainer');

  // Show loading state
  if (confirmBtns) {
    confirmBtns.innerHTML = `
      <span class="text-xs text-slate-400 flex items-center gap-1.5">
        <i class="fa-solid fa-circle-notch animate-spin text-[10px]"></i>
        Dispatching authorization code...
      </span>
    `;
  }

  try {
    const response = await fetch('../../api/employee/admin-reset-password.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({
        action: 'send_otp',
        target_user_id: resetPasswordTargetUserId
      })
    });

    const result = await response.json();

    if (result.status === 'success') {
      // Show OTP input pane
      if (confirmBtns)  confirmBtns.classList.add('hidden');
      if (otpContainer) otpContainer.classList.remove('hidden');

      const msgEl = document.getElementById('resetConfirmMessage');
      if (msgEl) msgEl.innerText = result.message || 'Authorization code dispatched. Enter the 6-digit code below.';

      if (typeof showToast === 'function') showToast(result.message || 'Authorization code dispatched.');
    } else {
      if (typeof showToast === 'function') showToast(result.message || 'Failed to dispatch authorization code.', true);
      // Restore confirm buttons
      if (confirmBtns) {
        confirmBtns.innerHTML = `
          <button type="button" onclick="cancelResetPassword()" class="border border-slate-200 bg-white hover:bg-slate-50 text-slate-600 px-4 py-2 rounded-xl text-xs font-bold transition cursor-pointer">Cancel</button>
          <button type="button" onclick="confirmResetPassword()" class="bg-rose-600 hover:bg-rose-700 text-white px-4 py-2 rounded-xl text-xs font-bold transition shadow-xs cursor-pointer">Confirm Reset</button>
        `;
      }
    }
  } catch (err) {
    console.error('Admin reset password error:', err);
    if (typeof showToast === 'function') showToast('Network error. Could not dispatch authorization code.', true);
    if (confirmBtns) {
      confirmBtns.innerHTML = `
        <button type="button" onclick="cancelResetPassword()" class="border border-slate-200 bg-white hover:bg-slate-50 text-slate-600 px-4 py-2 rounded-xl text-xs font-bold transition cursor-pointer">Cancel</button>
        <button type="button" onclick="confirmResetPassword()" class="bg-rose-600 hover:bg-rose-700 text-white px-4 py-2 rounded-xl text-xs font-bold transition shadow-xs cursor-pointer">Confirm Reset</button>
      `;
    }
  }
}

async function verifyOTP() {
  if (!resetPasswordTargetUserId) return;

  const otpInput = document.getElementById('otpInput');
  const otpCode  = otpInput ? otpInput.value.trim() : '';

  if (!otpCode || otpCode.length !== 6 || !/^\d{6}$/.test(otpCode)) {
    if (typeof showToast === 'function') showToast('Please enter a valid 6-digit authorization code.', true);
    return;
  }

  try {
    const response = await fetch('../../api/employee/admin-reset-password.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({
        action: 'verify_and_reset',
        target_user_id: resetPasswordTargetUserId,
        otp_code: otpCode
      })
    });

    const result = await response.json();

    if (result.status === 'success') {
      if (typeof showToast === 'function') showToast(result.message || 'Password reset successfully.');
      cancelResetPassword();
      if (typeof closeModal === 'function') closeModal('editModal');
    } else {
      if (typeof showToast === 'function') showToast(result.message || 'Invalid authorization code.', true);
      if (otpInput) otpInput.value = '';
    }
  } catch (err) {
    console.error('Verify OTP error:', err);
    if (typeof showToast === 'function') showToast('Network error. Could not verify authorization code.', true);
  }
}

window.triggerResetPassword  = triggerResetPassword;
window.cancelResetPassword   = cancelResetPassword;
window.confirmResetPassword  = confirmResetPassword;
window.verifyOTP             = verifyOTP;
