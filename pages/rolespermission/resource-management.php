<?php
$basePath = '../../';
require_once __DIR__ . '/../../src/bootstrap.php';

\App\Middleware\AuthMiddleware::handle($basePath);
\App\Middleware\PermissionMiddleware::require('resources.manage', $basePath);

include '../../includes/header.php';
include '../../includes/sidebar.php';
?>

<main class="flex-1 p-6 md:p-8 w-full space-y-6 overflow-y-auto relative pb-28 bg-[#F8FAFC]">

  <!-- Breadcrumb & Page Header -->
  <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 border-b border-slate-200/60 pb-5">
    <div class="space-y-1">
      <div class="flex items-center space-x-2 text-xs font-bold uppercase tracking-wider text-slate-400">
        <span>Role & Permissions</span>
        <i class="fa-solid fa-chevron-right text-[8px] opacity-60"></i>
        <span class="text-brand-dark">Resource Management</span>
      </div>
      <h1 class="text-2xl font-black text-slate-900 tracking-tight flex items-center gap-2.5 mt-4">
        <i class="fa-solid fa-folder-tree text-brand-dark"></i>
        Resource Management
      </h1>
      <p class="text-xs text-slate-500 max-w-2xl leading-relaxed">
        Define, structure, and organize individual system features, sub-pages, and data entities within parent modules.
      </p>
    </div>

    <!-- Primary Action Button -->
    <?php if (!empty($headerUser['is_superadmin']) || !empty($headerUser['is_global_access']) || (in_array('CREATE', $headerUser['granted_actions'] ?? []))): ?>
    <div class="shrink-0">
      <button 
        type="button"
        onclick="openCreateResourceModal()" 
        class="bg-[#86B6F6] hover:bg-[#6FA4EE] text-slate-900 font-bold px-4.5 py-2.5 rounded-xl text-xs transition duration-200 shadow-xs flex items-center gap-2 cursor-pointer focus:outline-none focus:ring-2 focus:ring-[#86B6F6]/30"
      >
        <i class="fa-solid fa-plus text-xs"></i>
        <span>Add New Resource</span>
      </button>
    </div>
    <?php endif; ?>
  </div>

  <!-- Resource Overview Metric Cards (3 columns) -->
  <div class="grid grid-cols-1 md:grid-cols-3 gap-5">
    <!-- Total Registered Resources -->
    <div class="bg-white border border-slate-200/80 rounded-2xl p-5 shadow-xs flex items-center justify-between">
      <div class="space-y-1">
        <span class="text-[10px] font-black uppercase text-slate-400 tracking-wider">Total Registered</span>
        <h3 id="metricTotalResources" class="text-2xl font-black text-slate-900 tracking-tight">12</h3>
        <p class="text-[11px] text-slate-500 font-medium">System Resources Mapped</p>
      </div>
      <div class="h-12 w-12 rounded-2xl bg-brand-light border border-brand-border/60 flex items-center justify-center text-brand-dark shrink-0">
        <i class="fa-solid fa-folder-tree text-lg"></i>
      </div>
    </div>

    <!-- Active Resources -->
    <div class="bg-white border border-slate-200/80 rounded-2xl p-5 shadow-xs flex items-center justify-between">
      <div class="space-y-1">
        <span class="text-[10px] font-black uppercase text-slate-400 tracking-wider">Active Resources</span>
        <h3 id="metricActiveResources" class="text-2xl font-black text-emerald-600 tracking-tight">11</h3>
        <p class="text-[11px] text-slate-500 font-medium">Active Endpoints & Features</p>
      </div>
      <div class="h-12 w-12 rounded-2xl bg-emerald-50 border border-emerald-100 flex items-center justify-center text-emerald-600 shrink-0">
        <i class="fa-solid fa-circle-check text-lg"></i>
      </div>
    </div>

    <!-- Inactive & Archived Resources -->
    <div class="bg-white border border-slate-200/80 rounded-2xl p-5 shadow-xs flex items-center justify-between">
      <div class="space-y-1">
        <span class="text-[10px] font-black uppercase text-slate-400 tracking-wider">Inactive / Archived</span>
        <h3 id="metricInactiveResources" class="text-2xl font-black text-amber-600 tracking-tight">1</h3>
        <p class="text-[11px] text-slate-500 font-medium">Disabled or Archived Resources</p>
      </div>
      <div class="h-12 w-12 rounded-2xl bg-amber-50 border border-amber-100 flex items-center justify-center text-amber-600 shrink-0">
        <i class="fa-solid fa-box-archive text-lg"></i>
      </div>
    </div>
  </div>

  <!-- Active / Archive Tabs Navigation -->
  <div class="flex items-center gap-2 border-b border-slate-200/80 pb-0">
    <button 
      type="button" 
      id="tabActiveResourcesBtn" 
      onclick="switchResourceTab('active')" 
      class="resource-tab-btn px-4 py-2.5 text-xs font-bold border-b-2 border-brand-dark text-brand-dark flex items-center gap-2 transition cursor-pointer"
    >
      <i class="fa-solid fa-list-check text-xs"></i>
      <span>Active Resources</span>
      <span id="tabActiveResourcesBadge" class="px-2 py-0.5 text-[10px] font-black rounded-full bg-emerald-50 text-emerald-700 border border-emerald-200">0</span>
    </button>
    
    <button 
      type="button" 
      id="tabArchivedResourcesBtn" 
      onclick="switchResourceTab('archived')" 
      class="resource-tab-btn px-4 py-2.5 text-xs font-bold border-b-2 border-transparent text-slate-500 hover:text-slate-700 flex items-center gap-2 transition cursor-pointer"
    >
      <i class="fa-solid fa-box-archive text-xs"></i>
      <span>Archived Resources</span>
      <span id="tabArchivedResourcesBadge" class="px-2 py-0.5 text-[10px] font-black rounded-full bg-slate-100 text-slate-600 border border-slate-200">0</span>
    </button>
  </div>

  <!-- Resource Directory Datatable Workspace -->
  <div class="bg-white border border-slate-200/80 rounded-2xl shadow-xs overflow-hidden space-y-4">
    
    <!-- Control Panel & Filters -->
    <div class="p-4 sm:p-5 border-b border-slate-100 flex flex-col md:flex-row md:items-center justify-between gap-4 bg-slate-50/50">
      <!-- Search Input -->
      <div class="relative flex-1 max-w-md">
        <i class="fa-solid fa-magnifying-glass absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400 text-xs"></i>
        <input 
          type="text" 
          id="resourceSearchInput" 
          oninput="filterResources()" 
          placeholder="Search resources by Name, Route, or Description..." 
          class="pl-9 pr-3 py-2 border border-slate-200 rounded-xl text-xs w-full bg-white focus:outline-none focus:border-brand-medium focus:ring-2 focus:ring-brand-medium/10 transition"
        >
      </div>

      <!-- Filters Group -->
      <div class="flex items-center gap-3">
        <!-- Bulk Delete Button (Archived Tab) -->
        <button 
          type="button" 
          id="bulkDeleteBtn" 
          onclick="openDeleteConfirmModal()" 
          class="hidden bg-rose-600 hover:bg-rose-700 text-white font-bold px-3.5 py-2 rounded-xl text-xs flex items-center gap-2 transition cursor-pointer shadow-2xs"
        >
          <i class="fa-solid fa-trash text-xs"></i>
          <span>Delete Selected (<span id="selectedCount">0</span>)</span>
        </button>

        <!-- Parent Module Filter Dropdown -->
        <select 
          id="parentModuleFilter" 
          onchange="filterResources()" 
          class="px-3 py-2 border border-slate-200 rounded-xl text-xs bg-white text-slate-700 focus:outline-none focus:border-brand-medium transition cursor-pointer"
        >
          <option value="ALL">All Parent Modules</option>
          <option value="User Management">User Management</option>
          <option value="Citizen Management">Citizen Management</option>
          <option value="Education & Scholarship">Education & Scholarship</option>
          <option value="Health Services">Health Services</option>
          <option value="BPLO Licensing & Permits">BPLO Licensing & Permits</option>
          <option value="DRRM Dispatch & Emergency">DRRM Dispatch & Emergency</option>
          <option value="Reports & Analytics">Reports & Analytics</option>
          <option value="System Settings">System Settings</option>
          <option value="Legacy Cashiering">Legacy Cashiering</option>
          <option value="Archived Portal Gateway">Archived Portal Gateway</option>
        </select>

        <!-- Status Filter Dropdown -->
        <select 
          id="statusFilterSelect" 
          onchange="filterResources()" 
          class="px-3 py-2 border border-slate-200 rounded-xl text-xs bg-white text-slate-700 focus:outline-none focus:border-brand-medium transition cursor-pointer"
        >
          <option value="ALL">All Statuses</option>
          <option value="Active">Active</option>
          <option value="Inactive">Inactive</option>
          <option value="Archived">Archived</option>
        </select>
      </div>
    </div>

    <!-- Structured Resource Datatable -->
    <div class="overflow-x-auto">
      <table class="w-full text-left border-collapse">
        <thead>
          <tr id="resourceTableHeader" class="bg-slate-50/80 border-b border-slate-200/80 text-[10px] font-black uppercase tracking-wider text-slate-400">
            <th id="checkboxTh" class="px-4 py-3.5 w-10 text-center hidden">
              <input type="checkbox" id="selectAllArchivedResources" onchange="toggleSelectAllArchivedResources(this)" class="rounded border-slate-300 text-rose-600 focus:ring-rose-500 cursor-pointer">
            </th>
            <th class="px-6 py-3.5">Resource Name</th>
            <th class="px-6 py-3.5">Parent Module</th>
            <th class="px-6 py-3.5">Description</th>
            <th class="px-6 py-3.5 text-center">Status</th>
            <th class="px-6 py-3.5">Created At</th>
            <th class="px-6 py-3.5">Updated At</th>
            <th class="px-6 py-3.5 text-right">Actions</th>
          </tr>
        </thead>
        <tbody id="resourceTableBody" class="divide-y divide-slate-100 text-xs font-medium">
          <!-- Dynamically populated by JS -->
        </tbody>
      </table>
    </div>

    <!-- Empty State -->
    <div id="emptyTableState" class="hidden p-10 text-center space-y-2">
      <i class="fa-solid fa-folder-open text-slate-300 text-3xl block"></i>
      <p class="text-xs font-bold text-slate-700">No resources match your search filter</p>
      <p class="text-[10px] text-slate-400">Try adjusting your search keyword or module dropdown filter.</p>
    </div>

    <!-- Pagination Footer Container -->
    <div id="resourcePaginationFooter" class="px-5 py-3.5 bg-slate-50/50 border-t border-slate-100 flex flex-col sm:flex-row items-center justify-between gap-3 text-xs font-semibold select-none">
      <div id="resourcePaginationInfo" class="text-xs text-slate-500 font-medium">
        Showing <span id="resourcePaginationStart" class="font-bold text-brand-dark">0</span> to <span id="resourcePaginationEnd" class="font-bold text-brand-dark">0</span> of <span id="resourcePaginationTotal" class="font-bold text-brand-dark">0</span> resources
      </div>
      <div class="flex items-center gap-1.5" id="resourcePaginationControls">
        <button id="resourcePrevPageBtn" onclick="changeResourcePage(-1)" class="px-3 py-1.5 border border-slate-200 rounded-xl bg-white hover:bg-slate-50 disabled:opacity-40 disabled:cursor-not-allowed text-slate-700 font-bold transition flex items-center gap-1 text-xs cursor-pointer shadow-2xs">
          <i class="fa-solid fa-chevron-left text-[10px]"></i> Previous
        </button>
        <div id="resourcePageNumbers" class="flex items-center gap-1 font-bold text-xs">
          <!-- Dynamic Page Numbers -->
        </div>
        <button id="resourceNextPageBtn" onclick="changeResourcePage(1)" class="px-3 py-1.5 border border-slate-200 rounded-xl bg-white hover:bg-slate-50 disabled:opacity-40 disabled:cursor-not-allowed text-slate-700 font-bold transition flex items-center gap-1 text-xs cursor-pointer shadow-2xs">
          Next <i class="fa-solid fa-chevron-right text-[10px]"></i>
        </button>
      </div>
    </div>

  </div>

</main>

<!-- MODAL 1: CREATE / EDIT RESOURCE MODAL -->
<div id="resourceModal" class="fixed inset-0 z-[9999] flex items-center justify-center overflow-y-auto p-4 sm:p-6 bg-slate-900/70 backdrop-blur-sm opacity-0 pointer-events-none transition-all duration-300">
  <div class="bg-white border border-slate-200 rounded-2xl shadow-xl w-full max-w-4xl max-h-[90vh] my-auto overflow-hidden flex flex-col transform scale-95 transition-all duration-300" id="resourceModalCard">
    
    <!-- Modal Header -->
    <div class="px-5 py-3.5 sm:px-6 sm:py-4 border-b border-slate-100 flex items-center justify-between bg-slate-50/50 shrink-0">
      <div class="flex items-center gap-2.5">
        <div class="h-8 w-8 rounded-xl bg-brand-light border border-brand-border flex items-center justify-center text-brand-dark font-bold text-xs">
          <i class="fa-solid fa-folder-tree"></i>
        </div>
        <div>
          <h3 id="modalHeaderTitle" class="text-sm font-black text-slate-900 tracking-tight">Add New System Resource</h3>
          <p class="text-[10px] text-slate-400 font-medium">Configure resource attributes, parent module mapping, and assigned action privileges.</p>
        </div>
      </div>
      <button type="button" onclick="closeResourceModal()" class="h-7 w-7 rounded-lg text-slate-400 hover:text-slate-600 hover:bg-slate-100 flex items-center justify-center transition cursor-pointer">
        <i class="fa-solid fa-xmark text-xs"></i>
      </button>
    </div>

    <!-- Modal Form -->
    <form id="resourceForm" onsubmit="handleSaveResource(event)" class="p-4 sm:p-6 overflow-y-auto custom-scrollbar flex-1 flex flex-col space-y-6">
      <input type="hidden" id="formResourceId" value="">

      <!-- Landscape 2-Column Grid -->
      <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 flex-1">
        
        <!-- Left Column: Resource Configuration -->
        <div class="lg:col-span-6 space-y-4">
          <div class="border-b border-slate-100 pb-2">
            <h4 class="text-xs font-extrabold text-slate-800 uppercase tracking-wider flex items-center gap-1.5">
              <i class="fa-solid fa-sliders text-brand-dark text-xs"></i>
              Resource Identity & Details
            </h4>
          </div>

          <!-- Parent Module Selection -->
          <div class="space-y-1.5">
            <label for="resourceParentModule" class="text-[10px] font-black text-slate-500 uppercase tracking-wider block">Parent Module</label>
            <select 
              id="resourceParentModule" 
              required 
              class="w-full px-3.5 py-2.5 border border-slate-200 rounded-xl text-xs bg-white text-slate-800 focus:outline-none focus:border-brand-medium transition cursor-pointer"
            >
              <option value="User Management">User Management</option>
              <option value="Citizen Management">Citizen Management</option>
              <option value="Education & Scholarship">Education & Scholarship</option>
              <option value="Health Services">Health Services</option>
              <option value="BPLO Licensing & Permits">BPLO Licensing & Permits</option>
              <option value="DRRM Dispatch & Emergency">DRRM Dispatch & Emergency</option>
              <option value="Reports & Analytics">Reports & Analytics</option>
              <option value="System Settings">System Settings</option>
              <option value="Legacy Cashiering">Legacy Cashiering</option>
              <option value="Archived Portal Gateway">Archived Portal Gateway</option>
            </select>
          </div>

          <!-- Resource Name -->
          <div class="space-y-1.5">
            <label for="resourceName" class="text-[10px] font-black text-slate-500 uppercase tracking-wider block">Resource Name</label>
            <input 
              type="text" 
              id="resourceName" 
              required 
              placeholder="e.g. Citizen Verification" 
              class="w-full px-3.5 py-2.5 border border-slate-200 rounded-xl text-xs text-slate-800 placeholder-slate-400 focus:outline-none focus:border-brand-medium focus:ring-2 focus:ring-brand-medium/10 transition"
            >
          </div>

          <!-- Status & Created At Row -->
          <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <!-- Status Selector -->
            <div class="space-y-1.5">
              <label for="resourceStatus" class="text-[10px] font-black text-slate-500 uppercase tracking-wider block">Status</label>
              <select 
                id="resourceStatus" 
                required 
                class="w-full px-3.5 py-2.5 border border-slate-200 rounded-xl text-xs bg-white text-slate-800 focus:outline-none focus:border-brand-medium transition cursor-pointer"
              >
                <option value="Active">Active</option>
                <option value="Inactive">Inactive</option>
              </select>
            </div>

            <!-- Created At (Non-editable) -->
            <div class="space-y-1.5">
              <label for="resourceCreatedAt" class="text-[10px] font-black text-slate-500 uppercase tracking-wider block">Created At</label>
              <input 
                type="text" 
                id="resourceCreatedAt" 
                readonly 
                disabled 
                placeholder="Auto-generated on save" 
                class="w-full px-3.5 py-2.5 border border-slate-200 rounded-xl text-xs text-slate-500 bg-slate-50 font-mono cursor-not-allowed"
              >
            </div>
          </div>

          <!-- Target Route / URI -->
          <div class="space-y-1.5">
            <label for="resourceRoute" class="text-[10px] font-black text-slate-500 uppercase tracking-wider block">Target Endpoint / URI Route</label>
            <input 
              type="text" 
              id="resourceRoute" 
              required 
              placeholder="e.g. /pages/citizen/verify.php" 
              class="w-full px-3.5 py-2.5 border border-slate-200 rounded-xl text-xs font-mono text-slate-700 placeholder-slate-400 focus:outline-none focus:border-brand-medium focus:ring-2 focus:ring-brand-medium/10 transition"
            >
          </div>

          <!-- Description -->
          <div class="space-y-1.5">
            <label for="resourceDesc" class="text-[10px] font-black text-slate-500 uppercase tracking-wider block">Feature Control Scope</label>
            <textarea 
              id="resourceDesc" 
              rows="2.5" 
              placeholder="Explain what this specific feature, endpoint, or page controls..." 
              class="w-full p-3 border border-slate-200 rounded-xl text-xs text-slate-800 placeholder-slate-400 focus:outline-none focus:border-brand-medium focus:ring-2 focus:ring-brand-medium/10 transition leading-relaxed"
            ></textarea>
          </div>
        </div>

        <!-- Right Column: Action Privileges (Action Management Integration) -->
        <div class="lg:col-span-6 bg-slate-50/80 border border-slate-200/80 rounded-xl p-4 flex flex-col space-y-3">
          
          <div class="flex items-center justify-between border-b border-slate-200/70 pb-2">
            <div>
              <h4 class="text-xs font-extrabold text-slate-900 tracking-tight flex items-center gap-1.5">
                <i class="fa-solid fa-key text-brand-dark text-xs"></i>
                Action Privileges
              </h4>
              <p class="text-[10px] text-slate-400 font-medium mt-0.5">Select action verbs enabled for this resource (reflected in permissions matrix).</p>
            </div>
          </div>

          <!-- Quick Helper Toolbar -->
          <div class="flex items-center justify-between bg-white border border-slate-200/80 rounded-lg p-1.5">
            <span class="text-[10px] font-extrabold text-slate-500 uppercase px-1.5">Quick Select:</span>
            <div class="flex items-center gap-1.5">
              <button 
                type="button" 
                onclick="applyActionHelper('crud')" 
                class="px-2.5 py-1 bg-brand-light hover:bg-brand-border/60 text-brand-dark font-extrabold text-[10px] rounded-md transition cursor-pointer flex items-center gap-1 border border-brand-border/50"
                title="Automatically check VIEW, CREATE, EDIT, DELETE actions"
              >
                <i class="fa-solid fa-bolt text-[9px]"></i>
                <span>CRUD</span>
              </button>

              <button 
                type="button" 
                onclick="applyActionHelper('select_all')" 
                class="px-2.5 py-1 bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold text-[10px] rounded-md transition cursor-pointer flex items-center gap-1 border border-slate-200"
              >
                <i class="fa-solid fa-check-double text-[9px]"></i>
                <span>Select All</span>
              </button>

              <button 
                type="button" 
                onclick="applyActionHelper('clear_all')" 
                class="px-2.5 py-1 bg-slate-100 hover:bg-slate-200 text-slate-600 font-bold text-[10px] rounded-md transition cursor-pointer flex items-center gap-1 border border-slate-200"
              >
                <i class="fa-solid fa-xmark text-[9px]"></i>
                <span>Clear All</span>
              </button>
            </div>
          </div>

          <!-- Scrollable Action Verbs Checkbox Grid Container -->
          <div id="actionCheckboxesContainer" class="grid grid-cols-1 sm:grid-cols-2 gap-2.5 max-h-[300px] overflow-y-auto custom-scrollbar p-1 flex-1">
            <!-- Dynamically populated by JS from Action Management API -->
          </div>

        </div>

      </div>

      <!-- Modal Footer Actions -->
      <div class="pt-3 border-t border-slate-100 flex items-center justify-end gap-2.5 shrink-0">
        <button 
          type="button" 
          onclick="closeResourceModal()" 
          class="px-4 py-2 border border-slate-200 hover:bg-slate-50 text-slate-600 font-bold rounded-xl text-xs transition cursor-pointer"
        >
          Cancel
        </button>
        <button 
          type="submit" 
          class="px-5 py-2 bg-[#86B6F6] hover:bg-[#6FA4EE] text-slate-900 font-bold rounded-xl text-xs transition shadow-xs cursor-pointer flex items-center gap-1.5"
        >
          <i class="fa-solid fa-floppy-disk text-xs"></i>
          <span>Save Resource</span>
        </button>
      </div>

    </form>

  </div>
</div>

<!-- MODAL 2: CONFIRM ARCHIVE MODAL -->
<div id="archiveModal" class="fixed inset-0 z-[9999] flex items-center justify-center overflow-y-auto p-4 sm:p-6 bg-slate-900/70 backdrop-blur-sm opacity-0 pointer-events-none transition-all duration-300">
  <div class="bg-white border border-slate-200 rounded-2xl shadow-xl w-full max-w-md max-h-[85vh] my-auto overflow-y-auto transform scale-95 transition-all duration-300" id="archiveModalCard">
    <div class="p-6 space-y-4 text-center">
      
      <!-- Warning Icon -->
      <div class="h-14 w-14 rounded-2xl bg-amber-50 border border-amber-100 text-amber-600 flex items-center justify-center mx-auto text-xl shadow-2xs">
        <i class="fa-solid fa-triangle-exclamation"></i>
      </div>

      <div class="space-y-1">
        <h3 class="text-base font-black text-slate-900 tracking-tight">Archive System Resource?</h3>
        <p id="archiveTargetName" class="text-xs font-bold text-brand-dark">Resource: Citizen Verification</p>
      </div>

      <!-- Warning Callout Box -->
      <div class="bg-amber-50/80 border border-amber-200/80 rounded-xl p-3.5 text-left text-xs leading-relaxed text-amber-800 flex items-start gap-2.5">
        <i class="fa-solid fa-circle-exclamation text-amber-600 text-sm mt-0.5 shrink-0"></i>
        <span>Archiving this resource will temporarily revoke permission checks for this feature across all assigned roles.</span>
      </div>

      <!-- Action Buttons -->
      <div class="pt-2 flex items-center justify-end gap-2.5">
        <button 
          type="button" 
          onclick="closeArchiveModal()" 
          class="w-1/2 py-2.5 border border-slate-200 hover:bg-slate-50 text-slate-700 font-bold rounded-xl text-xs transition cursor-pointer"
        >
          Cancel
        </button>
        <button 
          type="button" 
          onclick="confirmArchiveResource()" 
          class="w-1/2 py-2.5 bg-amber-600 hover:bg-amber-700 text-white font-bold rounded-xl text-xs transition shadow-xs cursor-pointer flex items-center justify-center gap-1.5"
        >
          <i class="fa-solid fa-box-archive text-xs"></i>
          <span>Confirm Archive</span>
        </button>
      </div>

    </div>
  </div>
</div>

<!-- MODAL 3: PERMANENT DELETE CONFIRMATION MODAL -->
<div id="deleteConfirmModal" class="fixed inset-0 z-[9999] flex items-center justify-center overflow-y-auto p-4 sm:p-6 bg-slate-900/70 backdrop-blur-sm opacity-0 pointer-events-none transition-all duration-300">
  <div class="bg-white border border-slate-200 rounded-2xl shadow-xl w-full max-w-md max-h-[85vh] my-auto overflow-y-auto transform scale-95 transition-all duration-300" id="deleteConfirmModalCard">
    <div class="p-6 space-y-4 text-center">
      
      <!-- Danger Icon -->
      <div class="h-14 w-14 rounded-2xl bg-rose-50 border border-rose-100 text-rose-600 flex items-center justify-center mx-auto text-xl shadow-2xs">
        <i class="fa-solid fa-trash-can"></i>
      </div>

      <div class="space-y-1">
        <h3 class="text-base font-black text-slate-900 tracking-tight">Permanently Delete Resource(s)?</h3>
        <p id="deleteTargetInfo" class="text-xs font-bold text-rose-600">Resource: Citizen Verification</p>
      </div>

      <!-- Warning Callout Box -->
      <div class="bg-rose-50/80 border border-rose-200/80 rounded-xl p-3.5 text-left text-xs leading-relaxed text-rose-900 flex items-start gap-2.5">
        <i class="fa-solid fa-triangle-exclamation text-rose-600 text-sm mt-0.5 shrink-0"></i>
        <span>This action will <strong>permanently delete</strong> the selected resource(s) and all associated permissions from the database. This action <strong>cannot be undone</strong>.</span>
      </div>

      <!-- Action Buttons -->
      <div class="pt-2 flex items-center justify-end gap-2.5">
        <button 
          type="button" 
          onclick="closeDeleteConfirmModal()" 
          class="w-1/2 py-2.5 border border-slate-200 hover:bg-slate-50 text-slate-700 font-bold rounded-xl text-xs transition cursor-pointer"
        >
          Cancel
        </button>
        <button 
          type="button" 
          onclick="confirmPermanentDelete()" 
          class="w-1/2 py-2.5 bg-rose-600 hover:bg-rose-700 text-white font-bold rounded-xl text-xs transition shadow-xs cursor-pointer flex items-center justify-center gap-1.5"
        >
          <i class="fa-solid fa-trash text-xs"></i>
          <span>Delete Permanently</span>
        </button>
      </div>

    </div>
  </div>
</div>

<!-- TOAST POPUP NOTIFICATION -->
<div id="toast" class="fixed bottom-4 right-4 z-50 bg-slate-900 text-white text-xs font-bold px-4 py-3.5 rounded-xl shadow-lg flex items-center gap-3 transform translate-y-4 opacity-0 pointer-events-none transition-all duration-300">
  <div class="h-5 w-5 rounded-full bg-emerald-500 flex items-center justify-center text-white text-[10px]">
    <i class="fa-solid fa-check"></i>
  </div>
  <span id="toastMsg" class="tracking-wide">Action executed successfully.</span>
</div>

<script src="<?php echo $basePath ?? '../'; ?>assets/js/rolespermission/resource-management.js"></script>

<?php include '../../includes/footer.php'; ?>
