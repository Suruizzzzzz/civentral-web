// MODAL CONTROLS - CREATE / EDIT / ARCHIVE
function showModalOverlay(modalId, cardId) {
  const modal = document.getElementById(modalId);
  const card = document.getElementById(cardId);
  if (!modal) return;

  modal.classList.remove('opacity-0', 'pointer-events-none');
  modal.classList.add('opacity-100', 'pointer-events-auto');

  if (card) {
    card.classList.remove('scale-95', 'opacity-0');
    card.classList.add('scale-100', 'opacity-100');
  }
}

function hideModalOverlay(modalId, cardId) {
  const modal = document.getElementById(modalId);
  const card = document.getElementById(cardId);
  if (!modal) return;

  modal.classList.remove('opacity-100', 'pointer-events-auto');
  modal.classList.add('opacity-0', 'pointer-events-none');

  if (card) {
    card.classList.remove('scale-100', 'opacity-100');
    card.classList.add('scale-95', 'opacity-0');
  }
}

function openCreateModuleModal() {
  const isSuperAdmin = currentUserScope ? !!currentUserScope.is_superadmin : false;
  const grantedActions = currentUserScope ? (currentUserScope.granted_actions || []) : [];
  const canCreate = isSuperAdmin || grantedActions.includes('CREATE');

  if (!canCreate) {
    if (typeof showToast === 'function') showToast('Forbidden. View-only access level cannot create system modules.');
    return;
  }

  const formModuleId = document.getElementById('formModuleId');
  const moduleForm = document.getElementById('moduleForm');
  const modalHeaderTitle = document.getElementById('modalHeaderTitle');
  const moduleStatus = document.getElementById('moduleStatus');
  const moduleCreatedAt = document.getElementById('moduleCreatedAt');
  const moduleName = document.getElementById('moduleName');
  const moduleDesc = document.getElementById('moduleDesc');

  if (formModuleId) formModuleId.value = '';
  if (moduleForm) moduleForm.reset();
  if (moduleName) moduleName.value = '';
  if (moduleDesc) moduleDesc.value = '';
  if (modalHeaderTitle) modalHeaderTitle.textContent = 'Create New System Module';
  if (moduleStatus) moduleStatus.value = 'Active';
  if (moduleCreatedAt) moduleCreatedAt.value = 'Auto-generated on save';
  
  showModalOverlay('moduleModal', 'moduleModalCard');
}

function openEditModal(id) {
  const isSuperAdmin = currentUserScope ? !!currentUserScope.is_superadmin : false;
  const grantedActions = currentUserScope ? (currentUserScope.granted_actions || []) : [];
  const canEdit = isSuperAdmin || grantedActions.includes('EDIT');

  if (!canEdit) {
    if (typeof showToast === 'function') showToast('Forbidden. View-only access level cannot modify system modules.');
    return;
  }

  const mod = systemModules.find(m => m.id === id);
  if (!mod) return;

  const formModuleId = document.getElementById('formModuleId');
  const moduleName = document.getElementById('moduleName');
  const moduleStatus = document.getElementById('moduleStatus');
  const moduleCreatedAt = document.getElementById('moduleCreatedAt');
  const moduleDesc = document.getElementById('moduleDesc');
  const modalHeaderTitle = document.getElementById('modalHeaderTitle');

  if (formModuleId) formModuleId.value = mod.id;
  if (moduleName) moduleName.value = mod.name || '';
  if (moduleStatus) moduleStatus.value = mod.status || 'Active';
  if (moduleCreatedAt) moduleCreatedAt.value = mod.created_at || '';
  if (moduleDesc) moduleDesc.value = mod.desc || '';
  if (modalHeaderTitle) modalHeaderTitle.textContent = `Edit Module: ${mod.name}`;

  showModalOverlay('moduleModal', 'moduleModalCard');
}

function closeModuleModal() {
  hideModalOverlay('moduleModal', 'moduleModalCard');
}

function openArchiveModal(id) {
  const mod = systemModules.find(m => m.id === id);
  if (!mod) return;

  archiveTargetId = id;
  const targetNameEl = document.getElementById('archiveTargetName');
  if (targetNameEl) targetNameEl.textContent = `Module: ${mod.name}`;

  showModalOverlay('archiveModal', 'archiveModalCard');
}

function closeArchiveModal() {
  archiveTargetId = null;
  hideModalOverlay('archiveModal', 'archiveModalCard');
}

async function confirmArchiveModule() {
  if (!archiveTargetId) return;

  const targetId = archiveTargetId;
  closeArchiveModal();
  if (typeof updateModuleStatusInDb === 'function') await updateModuleStatusInDb(targetId, 'Archived');
}

async function toggleModuleStatus(id) {
  const mod = systemModules.find(m => m.id === id);
  if (!mod) return;

  const nextStatus = mod.status === 'Active' ? 'Inactive' : 'Active';
  if (typeof updateModuleStatusInDb === 'function') await updateModuleStatusInDb(id, nextStatus);
}

function openDeleteConfirmModal(id = null) {
  const isSuperAdmin = currentUserScope ? !!currentUserScope.is_superadmin : false;
  const grantedActions = currentUserScope ? (currentUserScope.granted_actions || []) : [];
  const canDelete = isSuperAdmin || grantedActions.includes('DELETE');

  if (!canDelete) {
    if (typeof showToast === 'function') showToast('Forbidden. View-only access level cannot delete system modules.');
    return;
  }

  const targetInfoEl = document.getElementById('deleteTargetInfo');

  if (id) {
    window.singleDeleteTargetId = id;
    const mod = (window.systemModules || []).find(m => m.id === id);
    if (targetInfoEl) targetInfoEl.textContent = mod ? `Module: ${mod.name}` : `Module ID #${id}`;
  } else {
    window.singleDeleteTargetId = null;
    const count = (window.selectedArchivedIds || []).length;
    if (count === 0) {
      if (typeof showToast === 'function') showToast('No archived modules selected.');
      return;
    }
    if (targetInfoEl) targetInfoEl.textContent = `${count} selected archived module(s)`;
  }

  showModalOverlay('deleteConfirmModal', 'deleteConfirmModalCard');
}

function closeDeleteConfirmModal() {
  window.singleDeleteTargetId = null;
  hideModalOverlay('deleteConfirmModal', 'deleteConfirmModalCard');
}

async function confirmPermanentDelete() {
  const targetIds = window.singleDeleteTargetId ? [window.singleDeleteTargetId] : (window.selectedArchivedIds || []);
  
  closeDeleteConfirmModal();

  if (targetIds.length === 0) return;

  if (typeof deleteModulesPermanently === 'function') {
    await deleteModulesPermanently(targetIds);
  }
}

window.openCreateModuleModal = openCreateModuleModal;
window.openCreateModal = openCreateModuleModal;
window.openEditModal = openEditModal;
window.closeModuleModal = closeModuleModal;
window.openArchiveModal = openArchiveModal;
window.closeArchiveModal = closeArchiveModal;
window.confirmArchiveModule = confirmArchiveModule;
window.toggleModuleStatus = toggleModuleStatus;
window.openDeleteConfirmModal = openDeleteConfirmModal;
window.closeDeleteConfirmModal = closeDeleteConfirmModal;
window.confirmPermanentDelete = confirmPermanentDelete;

