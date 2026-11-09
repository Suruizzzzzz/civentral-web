<?php
$basePath = '../../';
require_once __DIR__ . '/../../src/bootstrap.php';

\App\Middleware\AuthMiddleware::handle($basePath);
\App\Middleware\PermissionMiddleware::require('departments.manage', $basePath);

include '../../includes/header.php';
include '../../includes/sidebar.php';
?>

<main class="flex-1 p-6 md:p-8 w-full space-y-6 overflow-y-auto">

  <!-- Page Header -->
  <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 border-b border-slate-200/60 dark:border-slate-800 pb-5">
    <div class="space-y-1">
      <h1 class="text-2xl font-bold tracking-tight text-slate-900 dark:text-white">
        Department Directory
      </h1>
      <p class="text-xs text-slate-500 dark:text-slate-400 mt-1 font-normal max-w-2xl leading-relaxed">
        Manage, configure, and monitor all municipal departments integrated into the Civentral portal network.
      </p>
    </div>

    <!-- Create Button -->
    <?php if (!empty($headerUser['is_superadmin']) || !empty($headerUser['is_global_access']) || (in_array('CREATE', $headerUser['granted_actions'] ?? []))): ?>
    <div class="shrink-0">
      <button onclick="openCreateModal()" class="px-4 py-2 bg-teal-600 hover:bg-teal-700 text-white text-xs font-semibold tracking-wide rounded-none transition flex items-center space-x-2 shadow-xs cursor-pointer">
        <i class="fa-solid fa-plus text-xs"></i>
        <span>Create Department</span>
      </button>
    </div>
    <?php endif; ?>
  </div>

  <!-- Summary Cards -->
  <div class="grid grid-cols-1 sm:grid-cols-3 gap-5">
    <!-- Total Departments Card -->
    <div class="bg-white border border-slate-200/80 rounded-none p-5 shadow-xs flex items-center justify-between group relative overflow-hidden">
      <div class="absolute top-0 left-0 w-1.5 h-full bg-brand-dark"></div>
      <div class="space-y-1">
        <span class="text-[10px] font-black uppercase tracking-wider text-slate-400 block">Total Departments</span>
        <h3 id="statTotalDepts" class="text-2xl font-black text-slate-900 tracking-tight">0 Active</h3>
      </div>
      <div class="h-10 w-10 rounded-none bg-slate-50 border border-slate-100 flex items-center justify-center text-slate-450 group-hover:bg-brand-light group-hover:text-brand-dark transition duration-350">
        <i class="fa-solid fa-sitemap text-sm"></i>
      </div>
    </div>

    <!-- Integrations Card -->
    <div class="bg-white border border-slate-200/80 rounded-none p-5 shadow-xs flex items-center justify-between group relative overflow-hidden">
      <div class="absolute top-0 left-0 w-1.5 h-full bg-emerald-500"></div>
      <div class="space-y-1">
        <span class="text-[10px] font-black uppercase tracking-wider text-slate-400 block">System Integrations</span>
        <h3 id="statIntegrations" class="text-lg font-bold text-slate-900 dark:text-white">Active</h3>
      </div>
      <div class="h-10 w-10 rounded-none bg-slate-50 border border-slate-100 flex items-center justify-center text-slate-450 group-hover:bg-emerald-50 group-hover:text-emerald-700 transition duration-350">
        <i class="fa-solid fa-link text-sm"></i>
      </div>
    </div>

    <!-- Admins Card -->
    <div class="bg-white border border-slate-200/80 rounded-none p-5 shadow-xs flex items-center justify-between group relative overflow-hidden">
      <div class="absolute top-0 left-0 w-1.5 h-full bg-amber-500"></div>
      <div class="space-y-1">
        <span class="text-[10px] font-black uppercase tracking-wider text-slate-400 block">Assigned Administrators</span>
        <h3 id="statAssignedAdmins" class="text-2xl font-black text-slate-900 tracking-tight">0 Assigned</h3>
      </div>
      <div class="h-10 w-10 rounded-none bg-slate-50 border border-slate-100 flex items-center justify-center text-slate-450 group-hover:bg-amber-50 group-hover:text-amber-700 transition duration-350">
        <i class="fa-solid fa-user-shield text-sm"></i>
      </div>
    </div>
  </div>

  <!-- Search and Actions Roster Bar -->
  <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 bg-white p-4 rounded-none border border-slate-200/80 shadow-xs">
    <!-- Search Field -->
    <div class="relative flex-1 max-w-md">
      <i class="fa-solid fa-magnifying-glass absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400 text-sm"></i>
      <input type="text" id="deptSearchInput" placeholder="Search by department name, code, or administrator..." class="pl-10 pr-4 py-2.5 border border-slate-200 rounded-none text-xs w-full bg-slate-50/50 focus:bg-white focus:outline-none focus:border-brand-medium focus:ring-2 focus:ring-brand-medium/10 transition">
    </div>
    
    <!-- Export Excel simulator -->
    <button onclick="exportDeptsCsv()" class="border border-slate-200 hover:bg-slate-50 text-slate-700 font-bold px-4 py-2.5 rounded-none text-xs flex items-center gap-2 cursor-pointer transition">
      <i class="fa-solid fa-download"></i>
      <span>Export Directory</span>
    </button>
  </div>

  <!-- Datatable list -->
  <div class="bg-white border border-slate-200/80 rounded-none shadow-xs overflow-hidden">
    <div class="overflow-x-auto">
      <table class="w-full text-left border-collapse">
        <thead>
          <tr class="bg-slate-50/70 dark:bg-slate-800/40 border-b border-slate-200 dark:border-slate-800 text-[11px] font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400">
            <th class="py-3 px-4 w-1/3">Department Details</th>
            <th class="py-3 px-4">Department Administrator</th>
            <th class="py-3 px-4">Access Scope Modules</th>
            <th class="py-3 px-4">Status</th>
            <th class="py-3 px-4 text-right">Actions</th>
          </tr>
        </thead>
        <tbody id="deptsTableBody" class="divide-y divide-slate-100/80 text-xs">
          <!-- Dynamically Populated by JS -->
        </tbody>
      </table>
    </div>

    <!-- Table Footer / Pagination -->
    <div class="flex flex-col sm:flex-row items-center justify-between gap-4 px-6 py-4 border-t border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900">
      <!-- Left Side: Record Counter -->
      <div class="text-xs text-slate-500 dark:text-slate-400 font-medium" id="deptsPaginationText">
        Showing <span class="font-semibold text-slate-700 dark:text-slate-200">0</span> to <span class="font-semibold text-slate-700 dark:text-slate-200">0</span> of <span class="font-semibold text-slate-700 dark:text-slate-200">0</span> records
      </div>

      <!-- Right Side: Segmented Numbered Pagination Bar -->
      <nav class="inline-flex -space-x-px border border-slate-200 dark:border-slate-700 rounded-none shadow-xs" aria-label="Pagination" id="deptsPaginationControls">
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


<!-- 1. CREATE / EDIT DEPARTMENT MODAL -->
<div id="deptModal" class="fixed inset-0 z-[9999] flex items-center justify-center overflow-y-auto p-4 sm:p-6 opacity-0 pointer-events-none transition-all duration-300 bg-slate-900/70 backdrop-blur-sm">
  <div class="bg-white rounded-none shadow-xl w-full max-w-md max-h-[85vh] my-auto overflow-hidden flex flex-col transform scale-95 transition-all duration-300">
    <div class="bg-slate-900 text-white px-5 py-3.5 sm:px-6 sm:py-4 flex items-center justify-between shrink-0">
      <div class="flex items-center space-x-2">
        <i id="deptModalIcon" class="fa-solid fa-building text-brand-medium"></i>
        <h3 id="deptModalTitle" class="font-extrabold text-sm tracking-tight uppercase">Create Department</h3>
      </div>
    </div>
    <form id="deptForm" onsubmit="handleSaveDept(event)" class="flex flex-col flex-1 overflow-hidden">
      <input type="hidden" id="deptIdRef">
      <div class="p-4 sm:p-6 space-y-4 flex-1 overflow-y-auto custom-scrollbar">
        <!-- Department Name -->
        <div class="space-y-1.5">
          <label class="text-[10px] font-black text-slate-400 uppercase tracking-wider block">Department Name</label>
          <input type="text" id="deptName" required placeholder="e.g. Business Permits & Licensing Office" class="border border-slate-200 rounded-none px-3 py-2.5 text-xs w-full focus:outline-none focus:border-brand-medium transition">
        </div>
        <!-- Department Code -->
        <div class="space-y-1.5">
          <label class="text-[10px] font-black text-slate-400 uppercase tracking-wider block">Department System Code</label>
          <input type="text" id="deptCode" required placeholder="e.g. BPLO" class="border border-slate-200 rounded-none px-3 py-2.5 text-xs w-full focus:outline-none focus:border-brand-medium transition uppercase font-bold tracking-wider">
        </div>
        <!-- Department Admin selector -->
        <div class="space-y-1.5">
          <label class="text-[10px] font-black text-slate-400 uppercase tracking-wider block">Department Administrator</label>
          <select id="deptAdmin" class="border border-slate-200 rounded-none px-3 py-2.5 text-xs w-full bg-white focus:outline-none focus:border-brand-medium transition cursor-pointer font-semibold text-slate-700">
            <option value="">Unassigned</option>
            <!-- Dynamically populated from live users by JS -->
          </select>
        </div>
        <!-- Description -->
        <div class="space-y-1.5">
          <label class="text-[10px] font-black text-slate-400 uppercase tracking-wider block">Operational Description</label>
          <textarea id="deptDesc" rows="3" placeholder="Describe the municipal operations handled by this department unit..." class="border border-slate-200 rounded-none px-3 py-2.5 text-xs w-full focus:outline-none focus:border-brand-medium transition resize-none"></textarea>
        </div>
      </div>
      <div class="bg-slate-50 border-t border-slate-100 px-6 py-4 flex items-center justify-end space-x-2 shrink-0">
        <button type="button" onclick="closeModal('deptModal')" class="border border-slate-200 bg-white hover:bg-slate-50 text-slate-650 font-bold px-4 py-2 rounded-none text-xs cursor-pointer transition">Cancel</button>
        <button type="submit" class="bg-teal-600 hover:bg-teal-700 text-white px-4 py-2 rounded-none text-xs font-medium cursor-pointer transition shadow-xs focus:outline-none focus:ring-2 focus:ring-teal-500/40">Save Department</button>
      </div>
    </form>
  </div>
</div>

<!-- 2. VIEW DETAILS INFO MODAL -->
<div id="viewDeptModal" class="fixed inset-0 z-[9999] flex items-center justify-center overflow-y-auto p-4 sm:p-6 opacity-0 pointer-events-none transition-all duration-300 bg-slate-900/70 backdrop-blur-sm">
  <div class="bg-white rounded-none shadow-xl w-full max-w-lg max-h-[85vh] my-auto overflow-hidden flex flex-col transform scale-95 transition-all duration-300">
    <div class="bg-slate-900 text-white px-5 py-3.5 sm:px-6 sm:py-4 flex items-center justify-between shrink-0">
      <div class="flex items-center space-x-2">
        <i class="fa-solid fa-circle-info text-brand-medium"></i>
        <h3 class="font-extrabold text-sm tracking-tight uppercase">Department Parameters Summary</h3>
      </div>
    </div>
    <div class="p-4 sm:p-6 space-y-5 flex-1 overflow-y-auto custom-scrollbar">
      
      <!-- Primary Title Header -->
      <div class="border-b border-slate-100 pb-3 space-y-1">
        <div class="flex items-center gap-2">
          <span id="viewDeptCodeBadge" class="bg-slate-100 text-slate-700 px-2 py-0.5 rounded-none text-[10px] font-black border border-slate-200">—</span>
          <h4 id="viewDeptName" class="font-extrabold text-sm text-slate-900">—</h4>
        </div>
        <p id="viewDeptAdminName" class="text-xs font-semibold text-brand-dark">Head: Unassigned</p>
        <p id="viewDeptDesc" class="text-xs text-slate-500 leading-relaxed">—</p>
      </div>

      <!-- Quick Metadata Grid -->
      <div class="grid grid-cols-2 gap-4">
        <div class="bg-slate-50 border border-slate-200/40 rounded-none p-3.5 space-y-1">
          <span class="text-[9px] font-black text-slate-400 uppercase tracking-wider block">Connected Staff</span>
          <p id="viewDeptStaffCount" class="text-sm font-extrabold text-slate-800">0 registered staff</p>
        </div>
        <div class="bg-slate-50 border border-slate-200/40 rounded-none p-3.5 space-y-1">
          <span class="text-[9px] font-black text-slate-400 uppercase tracking-wider block">Integration Port</span>
          <p class="text-sm font-extrabold text-emerald-600 flex items-center gap-1">
            <i class="fa-solid fa-circle-check text-[10px]"></i>
            <span>Active REST SSO</span>
          </p>
        </div>
      </div>

      <!-- Detail list -->
      <div class="space-y-3">
        <h5 class="text-[10px] font-black text-slate-400 uppercase tracking-wider block">Recent Admin Logs</h5>
        <div class="border border-slate-200/60 rounded-none overflow-hidden text-xs">
          <table class="w-full text-left border-collapse">
            <thead>
              <tr class="bg-slate-50 border-b border-slate-100 font-bold text-slate-500 text-[11px]">
                <th class="px-4 py-2.5">Action Taken</th>
                <th class="px-4 py-2.5 text-right">Date & Time</th>
              </tr>
            </thead>
            <tbody id="recentAdminLogsBody" class="divide-y divide-slate-100 text-slate-650">
              <tr>
                <td colspan="3" class="text-center py-4 text-slate-400 text-xs">No recent admin actions recorded.</td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>

      <!-- Scope Scopes List -->
      <div class="space-y-2">
        <h5 class="text-[10px] font-black text-slate-400 uppercase tracking-wider block">Core Module Scopes Authorized</h5>
        <div id="viewDeptScopesContainer" class="flex flex-wrap gap-1.5">
          <!-- JS Badges -->
        </div>
      </div>
    </div>
    <div class="bg-slate-50 border-t border-slate-100 px-6 py-4 flex items-center justify-end shrink-0">
      <button onclick="closeModal('viewDeptModal')" class="bg-slate-900 hover:bg-slate-800 text-white px-5 py-2.5 rounded-none text-xs font-bold cursor-pointer transition">Close Summary</button>
    </div>
  </div>
</div>


<?php include '../../includes/footer.php'; ?>
