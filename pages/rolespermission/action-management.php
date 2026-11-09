<?php
$basePath = '../../';
require_once __DIR__ . '/../../src/bootstrap.php';

\App\Middleware\AuthMiddleware::handle($basePath);
\App\Middleware\PermissionMiddleware::require('actions.manage', $basePath);

include '../../includes/header.php';
include '../../includes/sidebar.php';
?>

<main class="flex-1 p-6 md:p-8 w-full space-y-6 overflow-y-auto relative pb-28 bg-[#F8FAFC]">

  <!-- Page Header -->
  <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 border-b border-slate-200/60 dark:border-slate-800 pb-5">
    <div class="space-y-1">
      <h1 class="text-2xl font-bold tracking-tight text-slate-900 dark:text-white">
        System Action Operations Management
      </h1>
      <p class="text-xs text-slate-500 dark:text-slate-400 mt-1 font-normal max-w-2xl leading-relaxed">
        Define reusable operational verbs and system privileges that map onto resources to generate granular role permissions.
      </p>
    </div>

    <!-- Primary Action Button -->
    <?php if (!empty($headerUser['is_superadmin']) || !empty($headerUser['is_global_access']) || (in_array('CREATE', $headerUser['granted_actions'] ?? []))): ?>
    <div class="shrink-0">
      <button 
        type="button"
        onclick="openCreateActionModal()" 
        class="px-4 py-2 bg-teal-600 hover:bg-teal-700 text-white text-xs font-semibold tracking-wide rounded-none transition flex items-center space-x-2 shadow-xs cursor-pointer"
      >
        <i class="fa-solid fa-plus text-xs"></i>
        <span>Create New Action</span>
      </button>
    </div>
    <?php endif; ?>
  </div>

  <!-- Action Overview Metric Cards (3 columns) -->
  <div class="grid grid-cols-1 md:grid-cols-3 gap-5">
    <!-- Total Registered Actions -->
    <div class="bg-white border border-slate-200/80 rounded-none p-5 shadow-xs flex items-center justify-between">
      <div class="space-y-1">
        <span class="text-[10px] font-black uppercase text-slate-400 tracking-wider">Total Registered</span>
        <h3 id="metricTotalActions" class="text-2xl font-black text-slate-900 tracking-tight">—</h3>
        <p class="text-[11px] text-slate-500 font-medium">System Action Verbs</p>
      </div>
      <div class="h-12 w-12 rounded-none bg-brand-light border border-brand-border/60 flex items-center justify-center text-brand-dark shrink-0">
        <i class="fa-solid fa-bolt text-lg"></i>
      </div>
    </div>

    <!-- Active Actions -->
    <div class="bg-white border border-slate-200/80 rounded-none p-5 shadow-xs flex items-center justify-between">
      <div class="space-y-1">
        <span class="text-[10px] font-black uppercase text-slate-400 tracking-wider">Active Verbs</span>
        <h3 id="metricActiveActions" class="text-2xl font-black text-emerald-600 tracking-tight">—</h3>
        <p class="text-[11px] text-slate-500 font-medium">Active Action Privileges</p>
      </div>
      <div class="h-12 w-12 rounded-none bg-emerald-50 border border-emerald-100 flex items-center justify-center text-emerald-600 shrink-0">
        <i class="fa-solid fa-circle-check text-lg"></i>
      </div>
    </div>

    <!-- Inactive & Archived Actions -->
    <div class="bg-white border border-slate-200/80 rounded-none p-5 shadow-xs flex items-center justify-between">
      <div class="space-y-1">
        <span class="text-[10px] font-black uppercase text-slate-400 tracking-wider">Inactive / Archived</span>
        <h3 id="metricInactiveActions" class="text-2xl font-black text-amber-600 tracking-tight">—</h3>
        <p class="text-[11px] text-slate-500 font-medium">Disabled or Archived Actions</p>
      </div>
      <div class="h-12 w-12 rounded-none bg-amber-50 border border-amber-100 flex items-center justify-center text-amber-600 shrink-0">
        <i class="fa-solid fa-box-archive text-lg"></i>
      </div>
    </div>
  </div>

  <!-- Active / Archive Tabs Navigation -->
  <div class="flex items-center gap-2 border-b border-slate-200/80 pb-0">
    <button 
      type="button" 
      id="tabActiveActionsBtn" 
      onclick="switchActionTab('active')" 
      class="action-tab-btn px-4 py-2.5 text-xs font-bold border-b-2 border-brand-dark text-brand-dark flex items-center gap-2 transition cursor-pointer"
    >
      <i class="fa-solid fa-list-check text-xs"></i>
      <span>Active Actions</span>
      <span id="tabActiveActionsBadge" class="px-2 py-0.5 text-[10px] font-black rounded-none bg-emerald-50 text-emerald-700 border border-emerald-200">0</span>
    </button>
    
    <button 
      type="button" 
      id="tabArchivedActionsBtn" 
      onclick="switchActionTab('archived')" 
      class="action-tab-btn px-4 py-2.5 text-xs font-bold border-b-2 border-transparent text-slate-500 hover:text-slate-700 flex items-center gap-2 transition cursor-pointer"
    >
      <i class="fa-solid fa-box-archive text-xs"></i>
      <span>Archived Actions</span>
      <span id="tabArchivedActionsBadge" class="px-2 py-0.5 text-[10px] font-black rounded-none bg-slate-100 text-slate-600 border border-slate-200">0</span>
    </button>
  </div>

  <!-- Actions Directory Control Panel & Datatable Workspace -->
  <div class="bg-white border border-slate-200/80 rounded-none shadow-xs overflow-hidden space-y-4">
    
    <!-- Control Panel & Filters -->
    <div class="p-4 sm:p-5 border-b border-slate-100 flex flex-col md:flex-row md:items-center justify-between gap-4 bg-slate-50/50">
      <!-- Search Input -->
      <div class="relative flex-1 max-w-md">
        <i class="fa-solid fa-magnifying-glass absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400 text-xs"></i>
        <input 
          type="text" 
          id="actionSearchInput" 
          oninput="filterActions()" 
          placeholder="Search actions by Action Name or Description..." 
          class="pl-9 pr-3 py-2 border border-slate-200 rounded-none text-xs w-full bg-white focus:outline-none focus:border-brand-medium focus:ring-2 focus:ring-brand-medium/10 transition"
        >
      </div>

      <!-- Filters Group -->
      <div class="flex items-center gap-3">
        <!-- Status Filter Dropdown -->
        <select 
          id="statusFilterSelect" 
          onchange="filterActions()" 
          class="px-3 py-2 border border-slate-200 rounded-none text-xs bg-white text-slate-700 focus:outline-none focus:border-brand-medium transition cursor-pointer"
        >
          <option value="ALL">All Statuses</option>
          <option value="Active">Active</option>
          <option value="Inactive">Inactive</option>
          <option value="Archived">Archived</option>
        </select>
      </div>
    </div>

    <!-- Structured Action Operations Datatable -->
    <div class="overflow-x-auto">
      <table class="w-full text-left border-collapse">
        <thead>
          <tr class="bg-slate-50/70 dark:bg-slate-800/40 border-b border-slate-200 dark:border-slate-800 text-[11px] font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400">
            <th class="py-3 px-4">Action Name</th>
            <th class="py-3 px-4">Description</th>
            <th class="py-3 px-4 text-center">Status</th>
            <th class="py-3 px-4">Created At</th>
            <th class="py-3 px-4">Updated At</th>
            <th class="py-3 px-4 text-right">Actions</th>
          </tr>
        </thead>
        <tbody id="actionTableBody" class="divide-y divide-slate-100 text-xs font-medium">
          <!-- Dynamically populated by JS -->
        </tbody>
      </table>
    </div>

    <!-- Empty State -->
    <div id="emptyTableState" class="hidden p-10 text-center space-y-2">
      <i class="fa-solid fa-folder-open text-slate-300 text-3xl block"></i>
      <p class="text-xs font-bold text-slate-700">No action verbs match your search filter</p>
      <p class="text-[10px] text-slate-400">Try adjusting your search keyword or category dropdown filter.</p>
    </div>

    <!-- Pagination Footer Container -->
    <div id="actionPaginationFooter" class="flex flex-col sm:flex-row items-center justify-between gap-4 px-6 py-4 border-t border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900">
      <!-- Left Side: Record Counter -->
      <div id="actionPaginationInfo" class="text-xs text-slate-500 dark:text-slate-400 font-medium">
        Showing <span id="actionPaginationStart" class="font-semibold text-slate-700 dark:text-slate-200">0</span> to <span id="actionPaginationEnd" class="font-semibold text-slate-700 dark:text-slate-200">0</span> of <span id="actionPaginationTotal" class="font-semibold text-slate-700 dark:text-slate-200">0</span> records
      </div>

      <!-- Right Side: Segmented Numbered Pagination Bar -->
      <nav class="inline-flex -space-x-px border border-slate-200 dark:border-slate-700 rounded-none shadow-xs" aria-label="Pagination" id="actionPaginationControls">
        <!-- Previous Button -->
        <button type="button" class="px-3 py-1.5 text-xs font-medium text-slate-300 dark:text-slate-600 bg-slate-50/50 dark:bg-slate-800/30 cursor-not-allowed" disabled>
          <i class="fa-solid fa-chevron-left text-[10px]"></i>
        </button>

        <!-- Page Number Sequence -->
        <button type="button" class="px-3.5 py-1.5 text-xs font-bold text-sky-600 dark:text-sky-400 bg-sky-50/80 dark:bg-sky-950/40 border border-sky-300 dark:border-sky-600/60 z-10 cursor-default">1</button>

        <!-- Next Button -->
        <button type="button" class="px-3 py-1.5 text-xs font-medium text-slate-300 dark:text-slate-600 bg-slate-50/50 dark:bg-slate-800/30 cursor-not-allowed border-l border-slate-200 dark:border-slate-700" disabled>
          <i class="fa-solid fa-chevron-right text-[10px]"></i>
        </button>
      </nav>
    </div>

  </div>

</main>

<!-- MODAL 1: CREATE / EDIT ACTION MODAL -->
<div id="actionModal" class="fixed inset-0 z-[9999] flex items-center justify-center overflow-y-auto p-4 sm:p-6 bg-slate-900/70 backdrop-blur-sm hidden transition-opacity duration-200">
  <div class="bg-white border border-slate-200 rounded-none shadow-xl w-full max-w-lg max-h-[85vh] my-auto overflow-hidden flex flex-col transform transition-all scale-95 opacity-0" id="actionModalCard">
    
    <!-- Modal Header -->
    <div class="px-5 py-3.5 sm:px-6 sm:py-4 border-b border-slate-100 flex items-center justify-between bg-slate-50/50 shrink-0">
      <div class="flex items-center gap-2.5">
        <div class="h-8 w-8 rounded-none bg-brand-light border border-brand-border flex items-center justify-center text-brand-dark font-bold text-xs">
          <i class="fa-solid fa-bolt"></i>
        </div>
        <div>
          <h3 id="modalHeaderTitle" class="text-sm font-black text-slate-900 tracking-tight">Create New System Action</h3>
          <p class="text-[10px] text-slate-400 font-medium">Define operational verbs used for RBAC permission checks.</p>
        </div>
      </div>
    </div>

    <!-- Modal Form -->
    <form id="actionForm" onsubmit="handleSaveAction(event)" class="p-4 sm:p-6 space-y-4 overflow-y-auto custom-scrollbar flex-1">
      <input type="hidden" id="formActionId" value="">

      <!-- Action Name -->
      <div class="space-y-1.5">
        <label for="actionName" class="text-[10px] font-black text-slate-500 uppercase tracking-wider block">Action Name</label>
        <input 
          type="text" 
          id="actionName" 
          required 
          placeholder="e.g. Approve" 
          class="w-full px-3.5 py-2.5 border border-slate-200 rounded-none text-xs text-slate-800 placeholder-slate-400 focus:outline-none focus:border-brand-medium focus:ring-2 focus:ring-brand-medium/10 transition"
        >
      </div>

      <!-- Status & Created At Row -->
      <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
        <!-- Status Selector -->
        <div class="space-y-1.5">
          <label for="actionStatus" class="text-[10px] font-black text-slate-500 uppercase tracking-wider block">Status</label>
          <select 
            id="actionStatus" 
            required 
            class="w-full px-3.5 py-2.5 border border-slate-200 rounded-none text-xs bg-white text-slate-800 focus:outline-none focus:border-brand-medium transition cursor-pointer"
          >
            <option value="Active">Active</option>
            <option value="Inactive">Inactive</option>
          </select>
        </div>

        <!-- Created At (Non-editable) -->
        <div class="space-y-1.5">
          <label for="actionCreatedAt" class="text-[10px] font-black text-slate-500 uppercase tracking-wider block">Created At</label>
          <input 
            type="text" 
            id="actionCreatedAt" 
            readonly 
            disabled 
            placeholder="Auto-generated on save" 
            class="w-full px-3.5 py-2.5 border border-slate-200 rounded-none text-xs text-slate-500 bg-slate-50 font-mono cursor-not-allowed"
          >
        </div>
      </div>

      <!-- Description -->
      <div class="space-y-1.5">
        <label for="actionDesc" class="text-[10px] font-black text-slate-500 uppercase tracking-wider block">Operational Boundary Description</label>
        <textarea 
          id="actionDesc" 
          rows="3" 
          placeholder="Grants privilege to validate and approve submitted citizen applications..." 
          class="w-full p-3.5 border border-slate-200 rounded-none text-xs text-slate-800 placeholder-slate-400 focus:outline-none focus:border-brand-medium focus:ring-2 focus:ring-brand-medium/10 transition leading-relaxed"
        ></textarea>
      </div>

      <!-- Modal Actions -->
      <div class="pt-3 border-t border-slate-100 flex items-center justify-end gap-2.5">
        <button 
          type="button" 
          onclick="closeActionModal()" 
          class="px-4 py-2 border border-slate-200 hover:bg-slate-50 text-slate-600 font-bold rounded-none text-xs transition cursor-pointer"
        >
          Cancel
        </button>
        <button 
          type="submit" 
          class="px-5 py-2 bg-teal-600 hover:bg-teal-700 text-white font-medium rounded-none text-xs transition shadow-xs cursor-pointer flex items-center gap-1.5 focus:outline-none focus:ring-2 focus:ring-teal-500/40"
        >
          <i class="fa-solid fa-floppy-disk text-xs"></i>
          <span>Save Action</span>
        </button>
      </div>

    </form>

  </div>
</div>

<!-- MODAL 2: CONFIRM ARCHIVE MODAL -->
<div id="archiveModal" class="fixed inset-0 z-[9999] flex items-center justify-center overflow-y-auto p-4 sm:p-6 bg-slate-900/70 backdrop-blur-sm hidden transition-opacity duration-200">
  <div class="bg-white border border-slate-200 rounded-none shadow-xl w-full max-w-md max-h-[85vh] my-auto overflow-y-auto transform transition-all scale-95 opacity-0" id="archiveModalCard">
    <div class="p-6 space-y-4 text-center">
      
      <!-- Warning Icon -->
      <div class="h-14 w-14 rounded-none bg-amber-50 border border-amber-100 text-amber-600 flex items-center justify-center mx-auto text-xl shadow-2xs">
        <i class="fa-solid fa-triangle-exclamation"></i>
      </div>

      <div class="space-y-1">
        <h3 class="text-base font-black text-slate-900 tracking-tight">Archive Operational Action?</h3>
        <p id="archiveTargetName" class="text-xs font-bold text-brand-dark">Action: Approve (act_approve)</p>
      </div>

      <!-- Warning Callout Box -->
      <div class="bg-amber-50/80 border border-amber-200/80 rounded-none p-3.5 text-left text-xs leading-relaxed text-amber-800 flex items-start gap-2.5">
        <i class="fa-solid fa-circle-exclamation text-amber-600 text-sm mt-0.5 shrink-0"></i>
        <span>Archiving this action operation will automatically strip this privilege from all active resource permissions across Civentral roles.</span>
      </div>

      <!-- Action Buttons -->
      <div class="pt-2 flex items-center justify-end gap-2.5">
        <button 
          type="button" 
          onclick="closeArchiveModal()" 
          class="w-1/2 py-2.5 border border-slate-200 hover:bg-slate-50 text-slate-700 font-bold rounded-none text-xs transition cursor-pointer"
        >
          Cancel
        </button>
        <button 
          type="button" 
          onclick="confirmArchiveAction()" 
          class="w-1/2 py-2.5 bg-amber-600 hover:bg-amber-700 text-white font-bold rounded-none text-xs transition shadow-xs cursor-pointer flex items-center justify-center gap-1.5"
        >
          <i class="fa-solid fa-box-archive text-xs"></i>
          <span>Confirm Archive</span>
        </button>
      </div>

    </div>
  </div>
</div>


<script>
// INITIAL SYSTEM ACTION VERBS DATASTORE
// HELPER: GET NOW TIMESTAMP
function getNowTimestamp() {
  const now = new Date();
  const year = now.getFullYear();
  const month = String(now.getMonth() + 1).padStart(2, '0');
  const day = String(now.getDate()).padStart(2, '0');
  const hours = String(now.getHours()).padStart(2, '0');
  const mins = String(now.getMinutes()).padStart(2, '0');
  const secs = String(now.getSeconds()).padStart(2, '0');
  return `${year}-${month}-${day} ${hours}:${mins}:${secs}`;
}

// INITIAL SYSTEM ACTION VERBS DATASTORE CONNECTED TO DATABASE
let systemActions = [];
window.currentUserScope = window.currentUserScope || 'Global';
let archiveTargetActionId = null;

// FETCH ACTIONS FROM Database API
async function fetchActions() {
  try {
    const response = await fetch('../../api/employee/actions.php');
    const result = await response.json();
    if (result.status === 'success' && Array.isArray(result.data)) {
      systemActions = result.data.map(a => ({
        id: a.action_id,
        name: a.action_name,
        desc: a.description || '',
        status: a.status || 'Active',
        created_at: a.created_at ? a.created_at.replace('T', ' ').substring(0, 19) : '',
        updated_at: a.updated_at ? a.updated_at.replace('T', ' ').substring(0, 19) : ''
      }));
      window.currentUserScope = result.current_user || null;
      filterActions();
    } else {
      console.warn('Actions fetch notice:', result.message);
    }
  } catch (err) {
    console.error('Error fetching actions FROM DATABASE:', err);
    showToast('Network error connecting to Database.');
  }
}

// RENDER ACTIONS DATATABLE & PAGINATION
var currentActionPage = 1;
var actionPageSize = 10;
var currentFilteredActions = [];

function changeActionPage(delta) {
  const totalPages = Math.ceil(currentFilteredActions.length / actionPageSize) || 1;
  const newPage = currentActionPage + delta;
  if (newPage >= 1 && newPage <= totalPages) {
    currentActionPage = newPage;
    renderActionsTable(currentFilteredActions, false);
  }
}

function goToActionPage(page) {
  const totalPages = Math.ceil(currentFilteredActions.length / actionPageSize) || 1;
  if (page >= 1 && page <= totalPages) {
    currentActionPage = page;
    renderActionsTable(currentFilteredActions, false);
  }
}

window.changeActionPage = changeActionPage;
window.goToActionPage = goToActionPage;

function renderActionsTable(dataToRender = systemActions, resetPage = true) {
  currentFilteredActions = dataToRender;
  if (resetPage) currentActionPage = 1;

  const tableBody = document.getElementById('actionTableBody');
  const emptyState = document.getElementById('emptyTableState');
  const paginationFooter = document.getElementById('actionPaginationFooter');
  if (!tableBody) return;

  tableBody.innerHTML = '';

  if (dataToRender.length === 0) {
    if (emptyState) emptyState.classList.remove('hidden');
    if (paginationFooter) paginationFooter.classList.add('hidden');
    updateActionMetrics();
    return;
  } else {
    if (emptyState) emptyState.classList.add('hidden');
    if (paginationFooter) paginationFooter.classList.remove('hidden');
  }

  const totalPages = Math.ceil(dataToRender.length / actionPageSize) || 1;
  if (currentActionPage > totalPages) currentActionPage = totalPages;

  const startIdx = (currentActionPage - 1) * actionPageSize;
  const endIdx = Math.min(startIdx + actionPageSize, dataToRender.length);
  const pageItems = dataToRender.slice(startIdx, endIdx);

  const isSuperAdmin = window.currentUserScope ? !!window.currentUserScope.is_superadmin : false;
  const grantedActions = window.currentUserScope ? (window.currentUserScope.granted_actions || []) : [];
  const canEdit = isSuperAdmin || grantedActions.includes('EDIT');

  pageItems.forEach(act => {
    const tr = document.createElement('tr');
    tr.className = 'hover:bg-slate-50/60 dark:hover:bg-slate-800/40 transition group';

    // Status Badge HTML
    let statusBadgeHtml = '';
    if (act.status === 'Active') {
      statusBadgeHtml = `
        <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-none text-[10px] font-black bg-emerald-50 text-emerald-700 border border-emerald-200">
          <span class="h-1.5 w-1.5 rounded-full bg-emerald-500 animate-pulse"></span>
          Active
        </span>
      `;
    } else if (act.status === 'Inactive') {
      statusBadgeHtml = `
        <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-none text-[10px] font-black bg-slate-100 text-slate-600 border border-slate-200">
          <span class="h-1.5 w-1.5 rounded-full bg-slate-400"></span>
          Inactive
        </span>
      `;
    } else {
      statusBadgeHtml = `
        <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-none text-[10px] font-black bg-amber-50 text-amber-700 border border-amber-200">
          <span class="h-1.5 w-1.5 rounded-full bg-amber-500"></span>
          Archived
        </span>
      `;
    }

    const isChecked = act.status === 'Active';
    const isArchived = act.status === 'Archived';

    tr.innerHTML = `
      <td class="py-3 px-4 border-b border-slate-100 dark:border-slate-800/60 align-middle">
        <span class="font-semibold text-slate-900 dark:text-slate-100 text-xs tracking-tight block">${act.name}</span>
      </td>

      <td class="py-3 px-4 max-w-xs border-b border-slate-100 dark:border-slate-800/60 align-middle">
        <p class="text-xs text-slate-600 dark:text-slate-300 font-normal leading-relaxed">${act.desc || '<span class="italic text-[11px] text-slate-400 dark:text-slate-500">No description</span>'}</p>
      </td>

      <td class="py-3 px-4 text-center border-b border-slate-100 dark:border-slate-800/60 align-middle">
        ${statusBadgeHtml}
      </td>

      <td class="py-3 px-4 text-[11px] font-normal text-slate-500 dark:text-slate-400 font-mono border-b border-slate-100 dark:border-slate-800/60 align-middle">
        ${act.created_at}
      </td>

      <td class="py-3 px-4 text-[11px] font-normal text-slate-500 dark:text-slate-400 font-mono border-b border-slate-100 dark:border-slate-800/60 align-middle">
        ${act.updated_at}
      </td>

      <td class="py-3 px-4 text-right border-b border-slate-100 dark:border-slate-800/60 align-middle">
        <div class="flex items-center justify-end gap-3">
          ${canEdit ? `
          <!-- iOS Toggle Switch -->
          <label class="relative inline-flex items-center cursor-pointer ${isArchived ? 'opacity-50 pointer-events-none' : ''}" title="Activate/Deactivate Action Verb">
            <input 
              type="checkbox" 
              ${isChecked ? 'checked' : ''} 
              onchange="toggleActionStatus(${act.id})" 
              class="sr-only peer"
            >
            <div class="w-8 h-4.5 bg-slate-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-slate-300 after:border after:rounded-full after:h-3.5 after:w-3.5 after:transition-all peer-checked:bg-emerald-600"></div>
          </label>

          <!-- Edit Button -->
          <button 
            type="button" 
            onclick="openEditActionModal(${act.id})" 
            class="w-7 h-7 flex items-center justify-center border rounded-none transition cursor-pointer shadow-2xs border-amber-300 bg-amber-50 text-amber-600 hover:bg-amber-100 hover:text-amber-700 dark:border-amber-800/80 dark:bg-amber-950/40 dark:text-amber-400 dark:hover:bg-amber-900/60 dark:hover:text-amber-300"
            title="Edit Action Verb"
          >
            <i class="fa-solid fa-pen text-xs"></i>
          </button>

          ${isArchived ? `
          <!-- Restore Button -->
          <button 
            type="button" 
            onclick="restoreAction(${act.id})" 
            class="w-7 h-7 flex items-center justify-center border rounded-none transition cursor-pointer shadow-2xs border-emerald-300 bg-emerald-50 text-emerald-600 hover:bg-emerald-100 hover:text-emerald-700 dark:border-emerald-800/80 dark:bg-emerald-950/40 dark:text-emerald-400 dark:hover:bg-emerald-900/60 dark:hover:text-emerald-300"
            title="Restore Action to Active Tab"
          >
            <i class="fa-solid fa-rotate-left text-xs"></i>
          </button>
          ` : `
          <!-- Archive Button -->
          <button 
            type="button" 
            onclick="openArchiveActionModal(${act.id})" 
            class="w-7 h-7 flex items-center justify-center border rounded-none transition cursor-pointer shadow-2xs border-rose-300 bg-rose-50 text-rose-600 hover:bg-rose-100 hover:text-rose-700 dark:border-rose-800/80 dark:bg-rose-950/40 dark:text-rose-400 dark:hover:bg-rose-900/60 dark:hover:text-rose-300"
            title="Archive Action"
          >
            <i class="fa-solid fa-box-archive text-xs"></i>
          </button>
          `}` : `<span class="text-[10px] text-slate-400 font-bold italic">Read-only</span>`}
        </div>
      </td>
    `;

    tableBody.appendChild(tr);
  });

  // Update Pagination UI
  const startEl = document.getElementById('actionPaginationStart');
  const endEl = document.getElementById('actionPaginationEnd');
  const totalEl = document.getElementById('actionPaginationTotal');
  const container = document.getElementById('actionPaginationControls');

  if (startEl) startEl.textContent = dataToRender.length > 0 ? (startIdx + 1) : 0;
  if (endEl) endEl.textContent = endIdx;
  if (totalEl) totalEl.textContent = dataToRender.length;

  if (container) {
    const isPrevDisabled = currentActionPage <= 1 || dataToRender.length === 0;
    const isNextDisabled = currentActionPage >= totalPages || dataToRender.length === 0;

    let html = `
      <!-- Previous Button -->
      <button type="button" class="${isPrevDisabled ? 'px-3 py-1.5 text-xs font-medium text-slate-300 dark:text-slate-600 bg-slate-50/50 dark:bg-slate-800/30 cursor-not-allowed' : 'px-3 py-1.5 text-xs font-medium text-slate-500 hover:text-slate-700 hover:bg-slate-50 dark:text-slate-400 dark:hover:text-slate-200 dark:hover:bg-slate-800/60 transition cursor-pointer'}" 
              ${isPrevDisabled ? 'disabled' : ''} onclick="goToActionPage(${currentActionPage - 1})" aria-label="Previous Page">
        <i class="fa-solid fa-chevron-left text-[10px]"></i>
      </button>
    `;

    if (dataToRender.length === 0) {
      html += `
        <button type="button" class="px-3.5 py-1.5 text-xs font-bold text-sky-600 dark:text-sky-400 bg-sky-50/80 dark:bg-sky-950/40 border border-sky-300 dark:border-sky-600/60 z-10 cursor-default" disabled>1</button>
      `;
    } else {
      let pages = [];
      if (totalPages <= 7) {
        for (let i = 1; i <= totalPages; i++) pages.push(i);
      } else if (currentActionPage <= 4) {
        pages = [1, 2, 3, 4, 5, '...', totalPages];
      } else if (currentActionPage >= totalPages - 3) {
        pages = [1, '...', totalPages - 4, totalPages - 3, totalPages - 2, totalPages - 1, totalPages];
      } else {
        pages = [1, '...', currentActionPage - 1, currentActionPage, currentActionPage + 1, '...', totalPages];
      }

      pages.forEach(p => {
        if (p === '...') {
          html += `<span class="px-3.5 py-1.5 text-xs font-medium text-slate-400 dark:text-slate-500 border-l border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 select-none">...</span>`;
        } else if (p === currentActionPage) {
          html += `<button type="button" class="px-3.5 py-1.5 text-xs font-bold text-sky-600 dark:text-sky-400 bg-sky-50/80 dark:bg-sky-950/40 border border-sky-300 dark:border-sky-600/60 z-10 cursor-default" aria-current="page">${p}</button>`;
        } else {
          html += `<button type="button" class="px-3.5 py-1.5 text-xs font-medium text-slate-600 hover:text-slate-900 hover:bg-slate-50 dark:text-slate-400 dark:hover:text-slate-200 dark:hover:bg-slate-800/60 border-l border-slate-200 dark:border-slate-700 transition cursor-pointer" onclick="goToActionPage(${p})">${p}</button>`;
        }
      });
    }

    html += `
      <!-- Next Button -->
      <button type="button" class="${isNextDisabled ? 'px-3 py-1.5 text-xs font-medium text-slate-300 dark:text-slate-600 bg-slate-50/50 dark:bg-slate-800/30 cursor-not-allowed border-l border-slate-200 dark:border-slate-700' : 'px-3 py-1.5 text-xs font-medium text-slate-500 hover:text-slate-700 hover:bg-slate-50 dark:text-slate-400 dark:hover:text-slate-200 dark:hover:bg-slate-800/60 border-l border-slate-200 dark:border-slate-700 transition cursor-pointer'}" 
              ${isNextDisabled ? 'disabled' : ''} onclick="goToActionPage(${currentActionPage + 1})" aria-label="Next Page">
        <i class="fa-solid fa-chevron-right text-[10px]"></i>
      </button>
    `;

    container.innerHTML = html;
  }

  updateActionMetrics();
}

window.currentActionTab = 'active';

function switchActionTab(tabName) {
  window.currentActionTab = tabName;
  const activeBtn = document.getElementById('tabActiveActionsBtn');
  const archivedBtn = document.getElementById('tabArchivedActionsBtn');
  const statusFilterSelect = document.getElementById('statusFilterSelect');

  if (activeBtn && archivedBtn) {
    if (tabName === 'active') {
      activeBtn.className = "action-tab-btn px-4 py-2.5 text-xs font-bold border-b-2 border-brand-dark text-brand-dark flex items-center gap-2 transition cursor-pointer";
      archivedBtn.className = "action-tab-btn px-4 py-2.5 text-xs font-bold border-b-2 border-transparent text-slate-500 hover:text-slate-700 flex items-center gap-2 transition cursor-pointer";
      if (statusFilterSelect) statusFilterSelect.disabled = false;
    } else {
      activeBtn.className = "action-tab-btn px-4 py-2.5 text-xs font-bold border-b-2 border-transparent text-slate-500 hover:text-slate-700 flex items-center gap-2 transition cursor-pointer";
      archivedBtn.className = "action-tab-btn px-4 py-2.5 text-xs font-bold border-b-2 border-amber-600 text-amber-700 flex items-center gap-2 transition cursor-pointer";
      if (statusFilterSelect) statusFilterSelect.disabled = true;
    }
  }

  filterActions();
}

window.switchActionTab = switchActionTab;

async function restoreAction(id) {
  await updateActionStatusInDb(id, 'Active');
}

window.restoreAction = restoreAction;

// UPDATE METRIC CARDS
function updateActionMetrics() {
  const totalEl = document.getElementById('metricTotalActions');
  const activeEl = document.getElementById('metricActiveActions');
  const inactiveEl = document.getElementById('metricInactiveActions');

  if (totalEl) totalEl.textContent = systemActions.length;
  
  const activeCount = systemActions.filter(a => a.status === 'Active').length;
  if (activeEl) activeEl.textContent = activeCount;

  const inactiveCount = systemActions.filter(a => a.status !== 'Active').length;
  if (inactiveEl) inactiveEl.textContent = inactiveCount;
}

// FILTER ACTIONS REAL TIME
function filterActions() {
  const searchInput = document.getElementById('actionSearchInput');
  const statusFilter = document.getElementById('statusFilterSelect');
  const activeBadge = document.getElementById('tabActiveActionsBadge');
  const archivedBadge = document.getElementById('tabArchivedActionsBadge');

  const query = searchInput ? searchInput.value.toLowerCase().trim() : '';
  const selectedStatus = statusFilter ? statusFilter.value : 'ALL';

  const activeCount = systemActions.filter(a => a.status !== 'Archived').length;
  const archivedCount = systemActions.filter(a => a.status === 'Archived').length;

  if (activeBadge) activeBadge.textContent = activeCount;
  if (archivedBadge) archivedBadge.textContent = archivedCount;

  const filtered = systemActions.filter(act => {
    const matchesQuery = act.name.toLowerCase().includes(query) || 
                         (act.desc && act.desc.toLowerCase().includes(query));

    const matchesStatus = selectedStatus === 'ALL' || act.status === selectedStatus;

    if (currentActionTab === 'archived') {
      return matchesQuery && act.status === 'Archived';
    } else {
      return matchesQuery && matchesStatus && act.status !== 'Archived';
    }
  });

  renderActionsTable(filtered);
}

// UPDATE ACTION STATUS IN DATABASE
async function updateActionStatusInDb(actionId, newStatus) {
  try {
    const response = await fetch('../../api/employee/actions.php', {
      method: 'PATCH',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ action_id: actionId, status: newStatus })
    });
    const result = await response.json();
    if (result.status === 'success') {
      showToast(`Action status updated to ${newStatus}.`);
      await fetchActions();
    } else {
      showToast(result.message || 'Failed to update action status.');
    }
  } catch (err) {
    console.error('Error updating status:', err);
    showToast('Error updating status IN DATABASE.');
  }
}

// TOGGLE STATUS
async function toggleActionStatus(id) {
  const act = systemActions.find(a => a.id === id);
  if (!act) return;

  const nextStatus = act.status === 'Active' ? 'Inactive' : 'Active';
  await updateActionStatusInDb(id, nextStatus);
}

// MODAL CONTROLS - CREATE / EDIT
function openCreateActionModal() {
  const isSuperAdmin = window.currentUserScope ? !!window.currentUserScope.is_superadmin : false;
  const grantedActions = window.currentUserScope ? (window.currentUserScope.granted_actions || []) : [];
  const canCreate = isSuperAdmin || grantedActions.includes('CREATE');

  if (!canCreate) {
    showToast('Forbidden. View-only access level cannot create system action verbs.');
    return;
  }

  document.getElementById('formActionId').value = '';
  document.getElementById('actionForm').reset();
  document.getElementById('modalHeaderTitle').textContent = 'Create New System Action';
  document.getElementById('actionStatus').value = 'Active';
  document.getElementById('actionCreatedAt').value = 'Auto-generated on save';
  
  showModalOverlay('actionModal', 'actionModalCard');
}

function openEditActionModal(id) {
  const isSuperAdmin = window.currentUserScope ? !!window.currentUserScope.is_superadmin : false;
  const grantedActions = window.currentUserScope ? (window.currentUserScope.granted_actions || []) : [];
  const canEdit = isSuperAdmin || grantedActions.includes('EDIT');

  if (!canEdit) {
    showToast('Forbidden. View-only access level cannot modify system action verbs.');
    return;
  }

  const act = systemActions.find(a => a.id === id);
  if (!act) return;

  document.getElementById('formActionId').value = act.id;
  document.getElementById('actionName').value = act.name;
  document.getElementById('actionStatus').value = act.status || 'Active';
  document.getElementById('actionCreatedAt').value = act.created_at || '';
  document.getElementById('actionDesc').value = act.desc || '';

  document.getElementById('modalHeaderTitle').textContent = `Edit Action: ${act.name}`;

  showModalOverlay('actionModal', 'actionModalCard');
}

function closeActionModal() {
  hideModalOverlay('actionModal', 'actionModalCard');
}

async function handleSaveAction(event) {
  event.preventDefault();

  const idVal = document.getElementById('formActionId').value;
  const name = document.getElementById('actionName').value.trim();
  const status = document.getElementById('actionStatus').value;
  const desc = document.getElementById('actionDesc').value.trim();

  const payload = {
    action_name: name,
    description: desc,
    status: status
  };

  if (idVal !== '') {
    payload.action_id = parseInt(idVal);
  }

  const method = idVal === '' ? 'POST' : 'PUT';

  try {
    const response = await fetch('../../api/employee/actions.php', {
      method: method,
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify(payload)
    });
    const result = await response.json();

    if (result.status === 'success') {
      showToast(result.message || 'Action saved successfully.');
      closeActionModal();
      await fetchActions();
    } else {
      showToast(result.message || 'Failed to save action.');
    }
  } catch (err) {
    console.error('Error saving action:', err);
    showToast('Failed to save action TO DATABASE.');
  }
}

// MODAL CONTROLS - ARCHIVE
function openArchiveActionModal(id) {
  const act = systemActions.find(a => a.id === id);
  if (!act) return;

  archiveTargetActionId = id;
  const targetNameEl = document.getElementById('archiveTargetName');
  if (targetNameEl) targetNameEl.textContent = `Action: ${act.name}`;

  showModalOverlay('archiveModal', 'archiveModalCard');
}

function closeArchiveModal() {
  archiveTargetActionId = null;
  hideModalOverlay('archiveModal', 'archiveModalCard');
}

async function confirmArchiveAction() {
  if (!archiveTargetActionId) return;

  const targetId = archiveTargetActionId;
  closeArchiveModal();
  await updateActionStatusInDb(targetId, 'Archived');
}

// MODAL ANIMATION HELPERS
function showModalOverlay(modalId, cardId) {
  const modal = document.getElementById(modalId);
  const card = document.getElementById(cardId);
  if (!modal || !card) return;

  modal.classList.remove('hidden');
  setTimeout(() => {
    card.classList.remove('scale-95', 'opacity-0');
    card.classList.add('scale-100', 'opacity-100');
  }, 10);
}

function hideModalOverlay(modalId, cardId) {
  const modal = document.getElementById(modalId);
  const card = document.getElementById(cardId);
  if (!modal || !card) return;

  card.classList.remove('scale-100', 'opacity-100');
  card.classList.add('scale-95', 'opacity-0');
  setTimeout(() => {
    modal.classList.add('hidden');
  }, 200);
}

// DISMISS MODALS ON BACKDROP CLICK & ESCAPE KEY
document.addEventListener('click', (e) => {
  if (e.target.id === 'actionModal') closeActionModal();
  if (e.target.id === 'archiveModal') closeArchiveModal();
});

document.addEventListener('keydown', (e) => {
  if (e.key === 'Escape') {
    closeActionModal();
    closeArchiveModal();
  }
});

// TOAST NOTIFICATION & SEMANTIC TITLE ENGINE
function deriveModalTitle(msgStr, isErr, isWarn) {
  if (isErr) {
    if (/conflict|already exist|duplicate/i.test(msgStr)) return 'Conflict Detected';
    if (/invalid|required|missing|provide/i.test(msgStr)) return 'Validation Error';
    if (/unauthorized|forbidden|denied|permission/i.test(msgStr)) return 'Access Denied';
    if (/not found/i.test(msgStr)) return 'Record Not Found';
    return 'Action Failed';
  }

  if (isWarn) return 'Attention Required';

  // Contextual Success Titles based on action verb
  if (/created|registered|added/i.test(msgStr)) return 'Successfully Created';
  if (/updated|modified|saved|changes/i.test(msgStr)) return 'Successfully Updated';
  if (/deleted|removed|purged|archived/i.test(msgStr)) return 'Successfully Removed';
  if (/status|activated|deactivated|enabled|disabled|locked|unlocked/i.test(msgStr)) return 'Status Updated';
  if (/password|credential|reset/i.test(msgStr)) return 'Security Updated';
  if (/imported|exported|downloaded/i.test(msgStr)) return 'Data Transfer Complete';

  return 'Action Completed';
}

function showToast(message, isError = false) {
  const msgStr = String(message || '');
  const isErr = isError === true || isError === 'error' || isError === 'danger' || 
                /error|failed|invalid|required|denied|conflict|exist|already/i.test(msgStr);
  const isWarn = isError === 'warning';
  const modalTitle = deriveModalTitle(msgStr, isErr, isWarn);

  if (window.Swal) {
    Swal.fire({
      icon: isErr ? 'error' : (isWarn ? 'warning' : 'success'),
      title: modalTitle,
      text: msgStr,
      confirmButtonText: 'OK',
      confirmButtonColor: '#0D9488', // Vibrant Teal
      customClass: {
        confirmButton: 'rounded-none px-6 py-2 font-medium tracking-wide shadow-none'
      }
    });
  } else if (typeof window.swal === 'function') {
    window.swal({
      title: modalTitle,
      text: msgStr,
      icon: isErr ? 'error' : 'success',
      button: 'OK'
    });
  } else {
    alert(modalTitle + ': ' + msgStr);
  }
}
window.showToast = showToast;

// INITIAL DOM READY BINDING
document.addEventListener('DOMContentLoaded', () => {
  fetchActions();
});
</script>

<?php include '../../includes/footer.php'; ?>
