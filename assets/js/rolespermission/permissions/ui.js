// ACTION SUPPORT MAPPING PER RESOURCE
const resourceSupportedActionsMap = {
  'users account': ['VIEW', 'CREATE', 'EDIT', 'DELETE', 'EXPORT'],
  'user directory': ['VIEW', 'CREATE', 'EDIT', 'DELETE', 'EXPORT'],
  'create staff accounts': ['VIEW', 'CREATE'],
  'account status': ['VIEW', 'EDIT'],
  'roles': ['VIEW', 'CREATE', 'EDIT', 'DELETE'],
  'module management': ['VIEW', 'CREATE', 'EDIT', 'DELETE'],
  'resource management': ['VIEW', 'CREATE', 'EDIT', 'DELETE'],
  'action management': ['VIEW', 'CREATE', 'EDIT', 'DELETE'],
  'permission builder': ['VIEW', 'EDIT'],
  'role permission matrix': ['VIEW', 'EDIT'],
  'department management': ['VIEW', 'CREATE', 'EDIT', 'DELETE'],
  'citizen management': ['VIEW', 'CREATE', 'EDIT', 'DELETE', 'EXPORT', 'APPROVE'],
  'citizen directory': ['VIEW', 'CREATE', 'EDIT', 'DELETE', 'EXPORT', 'APPROVE'],
  'citizen account': ['VIEW', 'CREATE', 'EDIT', 'DELETE'],
  'audit logs system': ['VIEW', 'EXPORT'],
  'user activities': ['VIEW', 'EXPORT'],
  'login history': ['VIEW', 'EXPORT'],
  'data changes': ['VIEW', 'EXPORT']
};

function isActionSupportedForResource(resObj, actObj) {
  if (!resObj || !actObj) return true;
  const actId = parseInt(actObj.action_id);
  const resName = (resObj.name || '').toLowerCase().trim();
  const actName = (actObj.action_name || '').toUpperCase().trim();

  if (Array.isArray(resObj.supported_action_ids) && resObj.supported_action_ids.length > 0) {
    return resObj.supported_action_ids.includes(actId);
  }

  if (resourceSupportedActionsMap[resName]) {
    return resourceSupportedActionsMap[resName].includes(actName);
  }

  return true;
}

// RENDER ROLE SELECTION SIDEBAR
function renderRoleSelector(filterQuery = null) {
  const roleListContainer = document.getElementById('roleSelectorList');
  const roleSearchInput = document.getElementById('roleSearchInput');
  const deptFilterSelect = document.getElementById('roleDepartmentFilter');
  if (!roleListContainer) return;
  roleListContainer.innerHTML = '';

  const query = (filterQuery !== null ? filterQuery : (roleSearchInput ? roleSearchInput.value : '')).toLowerCase().trim();
  const selectedDept = deptFilterSelect ? deptFilterSelect.value : 'ALL';

  const filteredRoles = rolesData.filter(r => {
    const roleName = (r.role_name || '').toLowerCase();
    const rolePrefix = (r.role_prefix || '').toLowerCase();
    const deptName = (r.department_name || '').toLowerCase();

    const matchesSearch = query === '' || roleName.includes(query) || rolePrefix.includes(query) || deptName.includes(query);

    let matchesDept = true;
    if (selectedDept !== 'ALL') {
      const targetDeptId = parseInt(selectedDept);
      if (!isNaN(targetDeptId)) {
        matchesDept = (r.department_id === targetDeptId);
      } else {
        matchesDept = deptName.includes(selectedDept.toLowerCase());
      }
    }

    return matchesSearch && matchesDept;
  });

  if (filteredRoles.length === 0) {
    roleListContainer.innerHTML = '<p class="text-[11px] text-slate-400 text-center py-2 font-semibold">No roles found matching filter</p>';
    return;
  }

  filteredRoles.forEach(roleObj => {
    const isActive = roleObj.role_id === window.selectedRoleId;
    const isOwnRole = currentUserScope && !currentUserScope.is_superadmin && (roleObj.role_id === currentUserScope.role_id);
    
    const activeClasses = isActive 
      ? 'border-l-4 border-brand-dark bg-brand-light font-black text-brand-dark shadow-xs' 
      : 'border-l-4 border-transparent text-slate-500 hover:text-slate-800 hover:bg-slate-50 font-semibold';

    const btn = document.createElement('button');
    btn.type = 'button';
    btn.className = `w-full text-left px-3 py-2.5 rounded-lg text-xs transition cursor-pointer flex items-center justify-between ${activeClasses}`;
    btn.innerHTML = `
      <div class="flex flex-col min-w-0 pr-2">
        <div class="flex items-center gap-1.5 truncate">
          <span class="truncate">${roleObj.role_name}</span>
          ${isOwnRole ? `<span class="text-[8px] bg-amber-100 text-amber-800 font-bold px-1.5 py-0.5 rounded shrink-0">Your Role</span>` : ''}
        </div>
        ${roleObj.department_name ? `<span class="text-[9px] text-slate-400 font-medium truncate">${roleObj.department_name}</span>` : ''}
      </div>
      <i class="fa-solid fa-chevron-right text-[8px] opacity-60 shrink-0"></i>
    `;
    btn.onclick = () => { if (typeof selectRole === 'function') selectRole(roleObj.role_id); };
    roleListContainer.appendChild(btn);
  });
}

// RENDER COLLAPSIBLE MODULES AND NESTED RESOURCES
function renderAccordions() {
  const accordionsContainer = document.getElementById('moduleAccordionList');
  const moduleSearchInput = document.getElementById('moduleSearchInput');
  if (!accordionsContainer) return;
  accordionsContainer.innerHTML = '';
  const roleId = window.selectedRoleId;
  const rolePermissions = (window.currentPermissions && window.currentPermissions[roleId]) ? window.currentPermissions[roleId] : {};
  const query = moduleSearchInput ? moduleSearchInput.value.toLowerCase().trim() : '';

  const isSuperAdmin = currentUserScope ? !!currentUserScope.is_superadmin : false;
  const userRoleId = currentUserScope ? currentUserScope.role_id : null;
  const isEditingOwnRole = !isSuperAdmin && (roleId === userRoleId);
  const myPermPairs = (currentUserScope && Array.isArray(currentUserScope.granted_perm_pairs)) ? currentUserScope.granted_perm_pairs : [];

  if (isEditingOwnRole) {
    const noticeDiv = document.createElement('div');
    noticeDiv.className = 'bg-amber-50 border border-amber-200/80 rounded-2xl p-4 text-xs text-amber-900 font-bold flex items-center gap-3 shadow-xs';
    noticeDiv.innerHTML = `
      <div class="h-8 w-8 rounded-xl bg-amber-100 border border-amber-200 flex items-center justify-center text-amber-700 shrink-0">
        <i class="fa-solid fa-user-lock text-sm"></i>
      </div>
      <div>
        <p class="font-extrabold text-amber-900">View-Only Access (Your Own Role)</p>
        <p class="text-[11px] text-amber-700 font-medium mt-0.5 leading-relaxed">Your role permissions were assigned by Super Administrator and cannot be modified by yourself. You can only assign permissions to sub-roles that you manage.</p>
      </div>
    `;
    accordionsContainer.appendChild(noticeDiv);
  }

  modulesData.forEach(mod => {
    const moduleMatches = mod.name.toLowerCase().includes(query) || mod.desc.toLowerCase().includes(query);
    const matchingResources = mod.resources.filter(res => 
      res.name.toLowerCase().includes(query) || res.desc.toLowerCase().includes(query)
    );

    if (query !== '' && !moduleMatches && matchingResources.length === 0) {
      return;
    }

    const isExpanded = query !== '' ? true : !!window.expandedModules[mod.name];
    const masterChecked = (typeof isModuleMasterChecked === 'function') ? isModuleMasterChecked(mod, rolePermissions) : false;

    const card = document.createElement('div');
    card.className = 'bg-white border border-slate-200/80 rounded-2xl shadow-xs overflow-hidden transition-all duration-200';

    const header = document.createElement('div');
    header.className = 'px-5 py-4 flex items-center justify-between gap-4 bg-slate-50 border-b border-slate-100 hover:bg-slate-100/40 transition select-none cursor-pointer';
    
    header.addEventListener('click', (e) => {
      if (e.target.closest('.checkbox-container')) return;
      if (typeof toggleModuleCollapse === 'function') toggleModuleCollapse(mod.name);
    });

    const isMasterClickable = !isEditingOwnRole && (isSuperAdmin || mod.resources.some(r => myPermPairs.some(p => p.startsWith(r.id + '_'))));

    header.innerHTML = `
      <div class="flex items-center gap-3 flex-1 min-w-0">
        <div class="h-8 w-8 rounded-lg bg-white border border-slate-200/60 flex items-center justify-center text-slate-500 shrink-0">
          <i class="fa-solid ${mod.icon || 'fa-folder-tree'} text-xs"></i>
        </div>
        <div class="space-y-0.5 min-w-0">
          <h4 class="font-extrabold text-slate-800 tracking-tight text-xs">${mod.name}</h4>
          <p class="text-[10px] text-slate-400 font-medium leading-relaxed truncate">${mod.desc}</p>
        </div>
      </div>

      <div class="flex items-center gap-5 shrink-0">
        <label class="checkbox-container flex items-center gap-1.5 ${!isMasterClickable ? 'opacity-40 cursor-not-allowed pointer-events-none' : 'cursor-pointer'} text-[10px] font-black text-slate-500 uppercase tracking-wide" title="${!isMasterClickable ? 'You cannot delegate this module because your own role does not have access to it' : ''}">
          <input type="checkbox" data-module-master="${mod.name}" ${masterChecked ? 'checked' : ''} ${!isMasterClickable ? 'disabled' : ''} onchange="if(typeof toggleModuleMaster === 'function') toggleModuleMaster(this)" class="h-4.5 w-4.5 rounded border-slate-350 text-brand-dark focus:ring-brand-dark/20 ${!isMasterClickable ? 'cursor-not-allowed' : 'cursor-pointer'}">
          <span class="hidden sm:inline">Master Access</span>
        </label>
        
        <div class="text-slate-400 text-xs">
          <i class="fa-solid ${isExpanded ? 'fa-chevron-up' : 'fa-chevron-down'} transition-transform duration-200"></i>
        </div>
      </div>
    `;

    card.appendChild(header);

    if (isExpanded) {
      const body = document.createElement('div');
      body.className = 'divide-y divide-slate-100 text-xs';

      const resourcesToRender = query !== '' && !moduleMatches ? matchingResources : mod.resources;

      if (resourcesToRender.length === 0) {
        body.innerHTML = `
          <div class="px-5 py-6 text-center text-[10px] text-slate-400 font-semibold italic">
            No resources match the active search.
          </div>
        `;
      } else {
        resourcesToRender.forEach(res => {
          const resourcePerms = rolePermissions[res.id] || [];
          const isFeatureEnabled = resourcePerms.length > 0;

          const row = document.createElement('div');
          row.className = `px-5 py-4 flex flex-col md:flex-row md:items-center justify-between border-t border-slate-100 gap-4 transition ${isFeatureEnabled ? 'bg-white' : 'bg-slate-50/70 opacity-75'}`;

          const grantedActions = currentUserScope ? (currentUserScope.granted_actions || []) : [];
          const canEditUser = isSuperAdmin || grantedActions.includes('EDIT') || grantedActions.includes('CREATE');
          const isMyRoleHasResource = isSuperAdmin || myPermPairs.some(pair => pair.startsWith(res.id + '_'));
          const canUserToggleFeature = canEditUser && !isEditingOwnRole && isMyRoleHasResource;

          // Feature Enable Switch & Details
          const detailsDiv = document.createElement('div');
          detailsDiv.className = 'flex items-start gap-3.5 max-w-md';
          detailsDiv.innerHTML = `
            <label class="relative inline-flex items-center mt-0.5 shrink-0 ${!canUserToggleFeature ? 'opacity-40 cursor-not-allowed pointer-events-none' : 'cursor-pointer'}" title="${!canEditUser ? 'View-only access level cannot modify permissions' : (isEditingOwnRole ? 'You cannot modify your own role permissions' : (!isMyRoleHasResource ? 'You cannot delegate this feature because your own role does not have access to it' : 'Check to enable this feature for this role.'))}">
              <input type="checkbox" data-resource-toggle="${res.id}" ${isFeatureEnabled ? 'checked' : ''} ${!canUserToggleFeature ? 'disabled' : ''} onchange="if(typeof toggleFeatureEnable === 'function') toggleFeatureEnable(this)" class="sr-only peer">
              <div class="w-8 h-4.5 bg-slate-300 dark:bg-slate-700 rounded-full peer peer-focus:ring-2 peer-focus:ring-brand-medium/20 peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-slate-300 after:border after:rounded-full after:h-3.5 after:w-3.5 after:transition-all peer-checked:bg-brand-dark"></div>
            </label>
            <div class="space-y-0.5 min-w-0">
              <div class="flex items-center gap-2">
                <h5 class="font-extrabold text-slate-800 tracking-tight text-[11px] leading-snug">${res.name}</h5>
                <span class="text-[9px] font-extrabold px-2 py-0.5 rounded-full border ${isFeatureEnabled ? 'bg-emerald-50 text-emerald-700 border-emerald-200' : 'bg-slate-100 text-slate-500 border-slate-200'}">
                  ${isFeatureEnabled ? 'Feature Enabled' : 'Disabled (Hidden in Sidebar)'}
                </span>
                ${!isMyRoleHasResource ? `<span class="text-[8px] bg-rose-50 text-rose-700 border border-rose-200 font-bold px-1.5 py-0.5 rounded">Not Granted To You</span>` : ''}
              </div>
              <p class="text-[9px] text-slate-400 font-medium leading-relaxed">${res.desc}</p>
            </div>
          `;
          row.appendChild(detailsDiv);

          // Action Checkboxes Group (View, Create, Edit, Delete)
          const checkboxesDiv = document.createElement('div');
          checkboxesDiv.className = 'flex items-center gap-5 flex-wrap';

          actionsData.forEach(act => {
            const isSupported = isActionSupportedForResource(res, act);
            const isChecked = resourcePerms.includes(act.action_id) && isSupported;
            const pairKey = res.id + '_' + act.action_id;
            const isGrantedToCurrentUser = isSuperAdmin || myPermPairs.includes(pairKey);

            const isClickable = canEditUser && !isEditingOwnRole && isFeatureEnabled && isSupported && isGrantedToCurrentUser;
            const disabledAttr = !isClickable ? 'disabled' : '';
            const labelStyle = !isClickable ? 'opacity-30 cursor-not-allowed pointer-events-none' : 'cursor-pointer';
            
            let hintTitle = '';
            if (!canEditUser) hintTitle = 'View-only access level cannot modify permissions.';
            else if (isEditingOwnRole) hintTitle = 'You cannot modify your own role permissions.';
            else if (!isSupported) hintTitle = `The ${act.action_name} feature is not applicable to ${res.name} in the system UI.`;
            else if (!isGrantedToCurrentUser) hintTitle = `You cannot delegate the ${act.action_name} permission for ${res.name} because your own role does not have access to it.`;

            const label = document.createElement('label');
            label.className = `flex items-center gap-1.5 select-none ${labelStyle}`;
            if (hintTitle) label.title = hintTitle;

            label.innerHTML = `
              <input type="checkbox" data-resource="${res.id}" data-action="${act.action_id}" ${isChecked ? 'checked' : ''} ${disabledAttr} onchange="if(typeof toggleResourcePermission === 'function') toggleResourcePermission(this)" class="h-4 w-4 rounded border-slate-300 text-brand-dark focus:ring-brand-dark/20 ${!isClickable ? 'cursor-not-allowed bg-slate-100 opacity-40' : 'cursor-pointer'}">
              <span class="text-[9px] ${!isSupported ? 'text-slate-350 font-bold line-through' : (!isGrantedToCurrentUser ? 'text-slate-300 font-bold line-through' : 'text-slate-450 uppercase font-black')} tracking-wide">${act.action_name}</span>
            `;
            checkboxesDiv.appendChild(label);
          });

          row.appendChild(checkboxesDiv);
          body.appendChild(row);
        });
      }
      card.appendChild(body);
    }

    accordionsContainer.appendChild(card);
  });

  if (accordionsContainer.innerHTML === '') {
    accordionsContainer.innerHTML = `
      <div class="bg-white border border-slate-200/80 rounded-2xl p-8 text-center shadow-xs">
        <i class="fa-solid fa-folder-open text-slate-300 text-2xl mb-2"></i>
        <p class="text-xs text-slate-400 font-bold">No system modules or resources matched your search filter</p>
      </div>
    `;
  }
}

// RENDER ALLOWED & FORBIDDEN SECURITY LABELS
function renderRestrictions() {
  const allowedList = document.getElementById('allowedRestrictionsList');
  const forbiddenList = document.getElementById('forbiddenRestrictionsList');
  if (!allowedList || !forbiddenList) return;
  allowedList.innerHTML = '';
  forbiddenList.innerHTML = '';
  
  const roleId = window.selectedRoleId;
  const currentRoleObj = rolesData.find(r => r.role_id === roleId);

  let allowedRules = [];
  let forbiddenRules = [];

  if (currentRoleObj) {
    if (currentRoleObj.is_global_access) {
      allowedRules = ["City-wide cross-departmental access granted", "Execute administrative report exports", "Manage operational resource configurations"];
    } else {
      allowedRules = ["Department-scoped resource access", "Submit localized unit transactions", "Export department report summaries"];
      forbiddenRules = ["Global city-wide system configuration override", "Bypass department data boundaries"];
    }
    if (currentRoleObj.is_system_role) {
      allowedRules.push("Protected system core designation");
    }
  }

  if (allowedRules.length === 0) {
    allowedList.innerHTML = '<li class="text-slate-400 italic">No specific privileges assigned</li>';
  } else {
    allowedRules.forEach(rule => {
      allowedList.innerHTML += `
        <li class="flex items-center gap-2 font-semibold">
          <i class="fa-solid fa-circle-check text-emerald-500 text-xs"></i>
          <span>${rule}</span>
        </li>
      `;
    });
  }

  if (forbiddenRules.length === 0) {
    forbiddenList.innerHTML = '<li class="text-slate-400 italic">No explicit security restrictions</li>';
  } else {
    forbiddenRules.forEach(rule => {
      forbiddenList.innerHTML += `
        <li class="flex items-center gap-2 font-semibold">
          <i class="fa-solid fa-circle-xmark text-rose-500 text-xs"></i>
          <span>${rule}</span>
        </li>
      `;
    });
  }
}
