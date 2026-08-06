// EVENTS & FORM SUBMISSION

async function handleSaveResource(event) {
  event.preventDefault();

  const idVal = document.getElementById('formResourceId').value;
  const moduleId = document.getElementById('resourceParentModule').value;
  const name = document.getElementById('resourceName').value.trim();
  const status = document.getElementById('resourceStatus').value;
  const route = document.getElementById('resourceRoute').value.trim();
  const desc = document.getElementById('resourceDesc').value.trim();

  const actionCheckboxes = document.querySelectorAll('.resource-action-checkbox:checked');
  const selectedActionIds = Array.from(actionCheckboxes).map(cb => parseInt(cb.value)).filter(val => !isNaN(val));

  const payload = {
    module_id: parseInt(moduleId),
    resource_name: name,
    resource_route: route,
    description: desc,
    status: status,
    selected_action_ids: selectedActionIds
  };

  if (idVal !== '') {
    payload.resource_id = parseInt(idVal);
  }

  const method = idVal === '' ? 'POST' : 'PUT';

  try {
    const response = await fetch('../../api/employee/resources.php', {
      method: method,
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify(payload)
    });
    const result = await response.json();

    if (result.status === 'success') {
      if (typeof showToast === 'function') showToast(result.message || 'Resource saved successfully.');
      if (typeof closeResourceModal === 'function') closeResourceModal();

      if (idVal === '') {
        const searchInput = document.getElementById('resourceSearchInput');
        const parentModuleFilter = document.getElementById('parentModuleFilter');
        const statusFilterSelect = document.getElementById('statusFilterSelect');
        if (searchInput) searchInput.value = '';
        if (parentModuleFilter) parentModuleFilter.value = 'ALL';
        if (statusFilterSelect) statusFilterSelect.value = 'ALL';
        if (typeof switchResourceTab === 'function') switchResourceTab('active');
      }

      if (typeof fetchResources === 'function') await fetchResources();
    } else {
      if (typeof showToast === 'function') showToast(result.message || 'Failed to save resource.');
    }
  } catch (err) {
    console.error('Error saving resource:', err);
    if (typeof showToast === 'function') showToast('Failed to save resource TO DATABASE.');
  }
}

window.handleSaveResource = handleSaveResource;

// DISMISS MODALS ON BACKDROP CLICK & ESCAPE KEY
document.addEventListener('click', (e) => {
  if (e.target.id === 'resourceModal' && typeof closeResourceModal === 'function') closeResourceModal();
  if (e.target.id === 'archiveModal' && typeof closeArchiveModal === 'function') closeArchiveModal();
  if (e.target.id === 'deleteConfirmModal' && typeof closeDeleteConfirmModal === 'function') closeDeleteConfirmModal();
});

document.addEventListener('keydown', (e) => {
  if (e.key === 'Escape') {
    if (typeof closeResourceModal === 'function') closeResourceModal();
    if (typeof closeArchiveModal === 'function') closeArchiveModal();
    if (typeof closeDeleteConfirmModal === 'function') closeDeleteConfirmModal();
  }
});

