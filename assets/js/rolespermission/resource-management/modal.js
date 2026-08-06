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

function openCreateResourceModal() {
  const isSuperAdmin = currentUserScope ? !!currentUserScope.is_superadmin : false;
  const grantedActions = currentUserScope ? (currentUserScope.granted_actions || []) : [];
  const canCreate = isSuperAdmin || grantedActions.includes('CREATE');

  if (!canCreate) {
    if (typeof showToast === 'function') showToast('Forbidden. View-only access level cannot create system resources.');
    return;
  }

  const formResourceId = document.getElementById('formResourceId');
  const resourceForm = document.getElementById('resourceForm');
  const modalHeaderTitle = document.getElementById('modalHeaderTitle');
  const resourceStatus = document.getElementById('resourceStatus');
  const resourceCreatedAt = document.getElementById('resourceCreatedAt');
  const resourceName = document.getElementById('resourceName');
  const resourceRoute = document.getElementById('resourceRoute');
  const resourceDesc = document.getElementById('resourceDesc');

  if (formResourceId) formResourceId.value = '';
  if (resourceForm) resourceForm.reset();
  if (resourceName) resourceName.value = '';
  if (resourceRoute) resourceRoute.value = '';
  if (resourceDesc) resourceDesc.value = '';
  if (modalHeaderTitle) modalHeaderTitle.textContent = 'Add New System Resource';
  if (resourceStatus) resourceStatus.value = 'Active';
  if (resourceCreatedAt) resourceCreatedAt.value = 'Auto-generated on save';
  
  renderActionCheckboxes(null);

  showModalOverlay('resourceModal', 'resourceModalCard');
}

function openEditResourceModal(id) {
  const isSuperAdmin = currentUserScope ? !!currentUserScope.is_superadmin : false;
  const grantedActions = currentUserScope ? (currentUserScope.granted_actions || []) : [];
  const canEdit = isSuperAdmin || grantedActions.includes('EDIT');

  if (!canEdit) {
    if (typeof showToast === 'function') showToast('Forbidden. View-only access level cannot modify system resources.');
    return;
  }

  const res = systemResources.find(r => r.id === id);
  if (!res) return;

  const formResourceId = document.getElementById('formResourceId');
  const resourceParentModule = document.getElementById('resourceParentModule');
  const resourceName = document.getElementById('resourceName');
  const resourceStatus = document.getElementById('resourceStatus');
  const resourceCreatedAt = document.getElementById('resourceCreatedAt');
  const resourceRoute = document.getElementById('resourceRoute');
  const resourceDesc = document.getElementById('resourceDesc');
  const modalHeaderTitle = document.getElementById('modalHeaderTitle');

  if (formResourceId) formResourceId.value = res.id;
  if (resourceParentModule) resourceParentModule.value = res.module_id || '';
  if (resourceName) resourceName.value = res.name || '';
  if (resourceStatus) resourceStatus.value = res.status || 'Active';
  if (resourceCreatedAt) resourceCreatedAt.value = res.created_at || '';
  if (resourceRoute) resourceRoute.value = res.route || '';
  if (resourceDesc) resourceDesc.value = res.desc || '';

  if (modalHeaderTitle) modalHeaderTitle.textContent = `Edit Resource: ${res.name}`;

  renderActionCheckboxes(res.action_ids || []);

  showModalOverlay('resourceModal', 'resourceModalCard');
}

function renderActionCheckboxes(selectedActionIds = null) {
  const container = document.getElementById('actionCheckboxesContainer');
  if (!container) return;

  const actions = window.systemActionVerbsList || [];

  if (actions.length === 0) {
    container.innerHTML = `
      <div class="col-span-2 p-4 text-center text-xs text-slate-400 font-semibold italic">
        Loading action verbs...
      </div>
    `;
    return;
  }

  const isDefaultNew = selectedActionIds === null;
  const crudNames = ['VIEW', 'CREATE', 'EDIT', 'DELETE'];

  let html = '';
  actions.forEach(act => {
    const actId = parseInt(act.action_id);
    const actNameUpper = (act.action_name || '').toUpperCase().trim();
    
    let isChecked = false;
    if (isDefaultNew) {
      isChecked = crudNames.includes(actNameUpper);
    } else if (Array.isArray(selectedActionIds)) {
      isChecked = selectedActionIds.includes(actId);
    }

    const badgeColorMap = {
      'VIEW': 'bg-blue-50 text-blue-700 border-blue-200',
      'CREATE': 'bg-emerald-50 text-emerald-700 border-emerald-200',
      'EDIT': 'bg-amber-50 text-amber-700 border-amber-200',
      'DELETE': 'bg-rose-50 text-rose-700 border-rose-200',
      'EXPORT': 'bg-purple-50 text-purple-700 border-purple-200',
      'APPROVE': 'bg-teal-50 text-teal-700 border-teal-200'
    };

    const badgeStyle = badgeColorMap[actNameUpper] || 'bg-slate-100 text-slate-700 border-slate-200';

    html += `
      <label class="flex items-center justify-between p-2.5 bg-white border border-slate-200 rounded-xl hover:border-brand-medium/50 hover:shadow-2xs transition cursor-pointer select-none group">
        <div class="flex items-center gap-2.5 min-w-0 pr-1">
          <input 
            type="checkbox" 
            value="${actId}" 
            data-action-name="${actNameUpper}" 
            ${isChecked ? 'checked' : ''} 
            class="resource-action-checkbox h-4 w-4 rounded border-slate-300 text-brand-dark focus:ring-brand-dark/20 cursor-pointer"
          >
          <div class="min-w-0">
            <span class="text-xs font-bold text-slate-800 tracking-tight block truncate">${act.action_name}</span>
            ${act.description ? `<span class="text-[9.5px] text-slate-400 font-medium block truncate" title="${act.description}">${act.description}</span>` : ''}
          </div>
        </div>
        <span class="text-[9px] font-black uppercase px-2 py-0.5 rounded-full border ${badgeStyle} shrink-0">
          ${actNameUpper}
        </span>
      </label>
    `;
  });

  container.innerHTML = html;
}

function applyActionHelper(type) {
  const checkboxes = document.querySelectorAll('.resource-action-checkbox');
  if (!checkboxes || checkboxes.length === 0) return;

  const crudNames = ['VIEW', 'CREATE', 'EDIT', 'DELETE'];

  checkboxes.forEach(cb => {
    const nameUpper = (cb.getAttribute('data-action-name') || '').toUpperCase().trim();

    if (type === 'crud') {
      cb.checked = crudNames.includes(nameUpper);
    } else if (type === 'select_all') {
      cb.checked = true;
    } else if (type === 'clear_all') {
      cb.checked = false;
    }
  });
}

function closeResourceModal() {
  hideModalOverlay('resourceModal', 'resourceModalCard');
}

function openArchiveResourceModal(id) {
  const res = systemResources.find(r => r.id === id);
  if (!res) return;

  archiveTargetResourceId = id;
  const targetNameEl = document.getElementById('archiveTargetName');
  if (targetNameEl) targetNameEl.textContent = `Resource: ${res.name}`;

  showModalOverlay('archiveModal', 'archiveModalCard');
}

function closeArchiveModal() {
  archiveTargetResourceId = null;
  hideModalOverlay('archiveModal', 'archiveModalCard');
}

async function confirmArchiveResource() {
  if (!archiveTargetResourceId) return;

  const targetId = archiveTargetResourceId;
  closeArchiveModal();
  if (typeof updateResourceStatusInDb === 'function') await updateResourceStatusInDb(targetId, 'Archived');
}

async function toggleResourceStatus(id) {
  const res = systemResources.find(r => r.id === id);
  if (!res) return;

  const nextStatus = res.status === 'Active' ? 'Inactive' : 'Active';
  if (typeof updateResourceStatusInDb === 'function') await updateResourceStatusInDb(id, nextStatus);
}

function openDeleteConfirmModal(id = null) {
  const isSuperAdmin = currentUserScope ? !!currentUserScope.is_superadmin : false;
  const grantedActions = currentUserScope ? (currentUserScope.granted_actions || []) : [];
  const canDelete = isSuperAdmin || grantedActions.includes('DELETE');

  if (!canDelete) {
    if (typeof showToast === 'function') showToast('Forbidden. View-only access level cannot delete system resources.');
    return;
  }

  const targetInfoEl = document.getElementById('deleteTargetInfo');

  if (id) {
    window.singleDeleteTargetResourceId = id;
    const res = (window.systemResources || []).find(r => r.id === id);
    if (targetInfoEl) targetInfoEl.textContent = res ? `Resource: ${res.name}` : `Resource ID #${id}`;
  } else {
    window.singleDeleteTargetResourceId = null;
    const count = (window.selectedArchivedResourceIds || []).length;
    if (count === 0) {
      if (typeof showToast === 'function') showToast('No archived resources selected.');
      return;
    }
    if (targetInfoEl) targetInfoEl.textContent = `${count} selected archived resource(s)`;
  }

  showModalOverlay('deleteConfirmModal', 'deleteConfirmModalCard');
}

function closeDeleteConfirmModal() {
  window.singleDeleteTargetResourceId = null;
  hideModalOverlay('deleteConfirmModal', 'deleteConfirmModalCard');
}

async function confirmPermanentDelete() {
  const targetIds = window.singleDeleteTargetResourceId ? [window.singleDeleteTargetResourceId] : (window.selectedArchivedResourceIds || []);
  
  closeDeleteConfirmModal();

  if (targetIds.length === 0) return;

  if (typeof deleteResourcesPermanently === 'function') {
    await deleteResourcesPermanently(targetIds);
  }
}

window.openCreateResourceModal = openCreateResourceModal;
window.openEditResourceModal = openEditResourceModal;
window.closeResourceModal = closeResourceModal;
window.renderActionCheckboxes = renderActionCheckboxes;
window.applyActionHelper = applyActionHelper;
window.openArchiveResourceModal = openArchiveResourceModal;
window.closeArchiveModal = closeArchiveModal;
window.confirmArchiveResource = confirmArchiveResource;
window.toggleResourceStatus = toggleResourceStatus;
window.openDeleteConfirmModal = openDeleteConfirmModal;
window.closeDeleteConfirmModal = closeDeleteConfirmModal;
window.confirmPermanentDelete = confirmPermanentDelete;

