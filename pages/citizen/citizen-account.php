<?php
$basePath = '../../';
require_once __DIR__ . '/../../src/bootstrap.php';

\App\Middleware\AuthMiddleware::handle($basePath);
\App\Middleware\PermissionMiddleware::require('citizens.account', $basePath);

include '../../includes/header.php';
include '../../includes/sidebar.php';
?>

<main class="flex-1 p-6 md:p-8 w-full space-y-6 overflow-y-auto">

  <!-- Page Header -->
  <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 border-b border-slate-200/60 dark:border-slate-800 pb-5">
    <div class="space-y-1">
      <h1 class="text-2xl font-bold tracking-tight text-slate-900 dark:text-white">
        Citizen Account Status Control
      </h1>
      <p class="text-xs text-slate-500 dark:text-slate-400 mt-1 font-normal max-w-2xl leading-relaxed">
        Manage operational status, suspend access under investigation, release security locks, and restore deactivated portal credentials.
      </p>
    </div>
  </div>

  <!-- ACTIVE -->
  <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5">
    <div class="bg-white border border-slate-200/80 rounded-none p-5 shadow-xs flex items-center justify-between group relative overflow-hidden">
      <div class="absolute top-0 left-0 w-1.5 h-full bg-emerald-500"></div>
      <div class="space-y-1">
        <span class="text-[10px] font-black uppercase tracking-wider text-slate-400 block">Active Status</span>
        <h3 id="statActiveCount" class="text-2xl font-black text-slate-900 tracking-tight">0 Accounts</h3>
      </div>
      <div class="h-10 w-10 rounded-none bg-emerald-50/40 border border-emerald-100 flex items-center justify-center text-emerald-600 transition">
        <i class="fa-solid fa-circle-check text-sm animate-pulse"></i>
      </div>
    </div>

    <!-- INACTIVE -->
    <div class="bg-white border border-slate-200/80 rounded-none p-5 shadow-xs flex items-center justify-between group relative overflow-hidden">
      <div class="absolute top-0 left-0 w-1.5 h-full bg-slate-400"></div>
      <div class="space-y-1">
        <span class="text-[10px] font-black uppercase tracking-wider text-slate-400 block">Inactive Status</span>
        <h3 id="statInactiveCount" class="text-2xl font-black text-slate-900 tracking-tight">0 Accounts</h3>
      </div>
      <div class="h-10 w-10 rounded-none bg-slate-50 border border-slate-100 flex items-center justify-center text-slate-400 transition">
        <i class="fa-solid fa-circle-minus text-sm"></i>
      </div>
    </div>

    <!-- LOCKED -->
    <div class="bg-white border border-slate-200/80 rounded-none p-5 shadow-xs flex items-center justify-between group relative overflow-hidden">
      <div class="absolute top-0 left-0 w-1.5 h-full bg-rose-500"></div>
      <div class="space-y-1">
        <span class="text-[10px] font-black uppercase tracking-wider text-slate-400 block">Locked Status</span>
        <h3 id="statLockedCount" class="text-2xl font-black text-slate-900 tracking-tight">0 Accounts</h3>
      </div>
      <div class="h-10 w-10 rounded-none bg-rose-50/40 border border-rose-100 flex items-center justify-center text-rose-500 transition">
        <i class="fa-solid fa-user-lock text-sm"></i>
      </div>
    </div>

    <!-- SUSPENDED -->
    <div class="bg-white border border-slate-200/80 rounded-none p-5 shadow-xs flex items-center justify-between group relative overflow-hidden">
      <div class="absolute top-0 left-0 w-1.5 h-full bg-amber-500"></div>
      <div class="space-y-1">
        <span class="text-[10px] font-black uppercase tracking-wider text-slate-400 block">Suspended Status</span>
        <h3 id="statSuspendedCount" class="text-2xl font-black text-slate-900 tracking-tight">0 Accounts</h3>
      </div>
      <div class="h-10 w-10 rounded-none bg-amber-50/40 border border-amber-100 flex items-center justify-center text-amber-600 transition">
        <i class="fa-solid fa-triangle-exclamation text-sm"></i>
      </div>
    </div>
  </div>

  <!-- CONTROL-->
  <div class="bg-white p-5 rounded-none border border-slate-200/80 shadow-xs space-y-4">
    <div class="flex items-center justify-between border-b border-slate-100 pb-3">
      <div class="flex items-center space-x-2">
        <i class="fa-solid fa-sliders text-brand-dark text-xs"></i>
        <h3 class="text-xs font-black uppercase tracking-wider text-slate-700">Security Control Filters</h3>
      </div>
      <button onclick="resetControlFilters()" class="text-slate-400 hover:text-rose-600 text-xs font-bold transition flex items-center gap-1 cursor-pointer">
        <i class="fa-solid fa-trash-can text-[10px]"></i>
        <span>Clear Filters</span>
      </button>
    </div>

    <div class="flex flex-col lg:flex-row gap-4 items-stretch lg:items-center justify-between">
      <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4 flex-1 max-w-4xl">
        <div class="relative">
          <i class="fa-solid fa-magnifying-glass absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-xs"></i>
          <input type="text" id="ctrlSearchInput" oninput="filterControlTable()" placeholder="Search ID, name, or email..." class="pl-8 pr-3 py-2 border border-slate-200 rounded-none text-xs w-full bg-slate-50/50 focus:bg-white focus:outline-none focus:border-brand-medium transition">
        </div>

        <!-- FILTER -->
        <select id="ctrlStatusFilter" onchange="filterControlTable()" class="border border-slate-200 rounded-none px-3 py-2 text-xs w-full bg-slate-50/50 focus:bg-white focus:outline-none focus:border-brand-medium transition cursor-pointer font-semibold text-slate-650">
          <option value="All">All Statuses</option>
          <option value="Active">Active</option>
          <option value="Inactive">Inactive</option>
          <option value="Locked">Locked</option>
          <option value="Suspended">Suspended</option>
        </select>

        <!-- FLAG -->
        <div class="flex items-center space-x-2.5 pl-2 select-none">
          <input type="checkbox" id="flaggedFilter" onchange="filterControlTable()" class="rounded-none border-slate-300 text-brand-medium focus:ring-brand-medium/20 h-4 w-4 cursor-pointer">
          <label for="flaggedFilter" class="text-xs font-bold text-slate-650 cursor-pointer flex items-center gap-1.5">
            <i class="fa-solid fa-flag text-rose-500 animate-pulse text-[10px]"></i>
            <span>Show Flagged Warnings Only</span>
          </label>
        </div>
      </div>
      
      <!-- EXCEL -->
      <button onclick="exportControlCsv()" class="border border-slate-200 hover:bg-slate-50 text-slate-700 font-bold px-4 py-2 rounded-none text-xs flex items-center gap-2 cursor-pointer transition shadow-xs">
        <i class="fa-solid fa-download"></i>
        <span>Export Log</span>
      </button>
    </div>
  </div>

  <!-- STATUS CONTROL -->
  <div class="bg-white border border-slate-200/80 rounded-none shadow-xs overflow-hidden">
    <div class="overflow-x-auto">
      <table class="w-full text-left border-collapse">
        <thead>
          <tr class="bg-slate-50/70 dark:bg-slate-800/40 border-b border-slate-200 dark:border-slate-800 text-[11px] font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400">
            <th class="py-3 px-4">Citizen ID</th>
            <th class="py-3 px-4">Citizen User</th>
            <th class="py-3 px-4">Current Security Status</th>
            <th class="py-3 px-4">Access Violations / Security Log</th>
            <th class="py-3 px-4 text-right">State Controller Actions</th>
          </tr>
        </thead>
        <tbody id="controlTableBody" class="divide-y divide-slate-100/80 text-xs">
        </tbody>
      </table>
    </div>

    <!-- PAGINATION -->
    <div class="bg-slate-50 px-6 py-4 border-t border-slate-100 flex items-center justify-between text-[11px] font-bold text-slate-400">
      <div id="ctrlPaginationText">
        Showing 0 to 0 of 0 accounts
      </div>
      <div class="flex items-center space-x-1" id="ctrlPaginationControls">
        <button id="ctrlPrevBtn" class="px-3 py-1.5 rounded-none border border-slate-200 bg-white hover:bg-slate-50 text-slate-500 cursor-not-allowed transition" disabled>
          <i class="fa-solid fa-chevron-left text-[9px]"></i>
        </button>
        <div id="ctrlPageNumbers" class="flex items-center space-x-1"></div>
        <button id="ctrlNextBtn" class="px-3 py-1.5 rounded-none border border-slate-200 bg-white hover:bg-slate-50 text-slate-500 cursor-not-allowed transition" disabled>
          <i class="fa-solid fa-chevron-right text-[9px]"></i>
        </button>
      </div>
    </div>
  </div>

</main>

<!-- MODALS SECTION -->

<!-- CHANGE ACCOUNT MODAL -->
<div id="stateChangeModal" class="fixed inset-0 z-[9999] flex items-center justify-center overflow-y-auto p-4 sm:p-6 opacity-0 pointer-events-none transition-all duration-300 bg-slate-900/70 backdrop-blur-sm">
  <div class="bg-white rounded-none shadow-xl w-full max-w-md max-h-[85vh] my-auto overflow-y-auto transform scale-95 transition-all duration-300">
    <div class="bg-slate-900 text-white px-6 py-4 flex items-center justify-between">
      <div class="flex items-center space-x-2">
        <i id="stateModalIcon" class="fa-solid fa-user-gear text-brand-medium"></i>
        <h3 id="stateModalTitle" class="font-extrabold text-sm tracking-tight uppercase">Update Security state</h3>
      </div>
    </div>
    <form id="stateForm" onsubmit="handleConfirmStateChange(event)">
      <input type="hidden" id="stateTargetId">
      <input type="hidden" id="stateTargetAction">
      
      <div class="p-6 space-y-4">
        <div class="flex items-start gap-3">
          <div id="stateIconBox" class="h-10 w-10 rounded-none bg-slate-50 flex items-center justify-center text-slate-700 shrink-0">
            <i class="fa-solid fa-circle-info text-base animate-pulse"></i>
          </div>
          <div class="space-y-1">
            <h4 id="stateHeader" class="font-bold text-slate-900 text-xs">Execute Action?</h4>
            <p id="stateText" class="text-xs text-slate-500 leading-relaxed">Confirm changing security access state.</p>
          </div>
        </div>

        <div class="space-y-1.5">
          <label class="text-[10px] font-black text-slate-400 uppercase tracking-wider block">Administrative Reason / Justification</label>
          <textarea id="stateReason" required placeholder="Write detail log justification for this security audit database update..." rows="3" class="border border-slate-200 rounded-none px-3 py-2.5 text-xs w-full focus:outline-none focus:border-brand-medium transition resize-none"></textarea>
        </div>
      </div>
      <div class="bg-slate-50 border-t border-slate-100 px-6 py-4 flex items-center justify-end space-x-2">
        <button type="button" onclick="closeModal('stateChangeModal')" class="border border-slate-200 bg-white hover:bg-slate-50 text-slate-650 font-bold px-4 py-2 rounded-none text-xs transition cursor-pointer">Cancel</button>
        <button type="submit" id="stateConfirmBtn" class="bg-teal-600 hover:bg-teal-700 text-white font-medium px-4 py-2 rounded-none text-xs transition cursor-pointer shadow-xs focus:outline-none focus:ring-2 focus:ring-teal-500/40">Confirm Action</button>
      </div>
    </form>
  </div>
</div>

<!-- SECURITY AUDIT MODAL -->
<div id="timelineModal" class="fixed inset-0 z-[9999] flex items-center justify-center overflow-y-auto p-4 sm:p-6 opacity-0 pointer-events-none transition-all duration-300 bg-slate-900/70 backdrop-blur-sm">
  <div class="bg-white rounded-none shadow-xl w-full max-w-md max-h-[85vh] my-auto overflow-hidden flex flex-col transform scale-95 transition-all duration-300">
    <div class="bg-slate-900 text-white px-5 py-3.5 sm:px-6 sm:py-4 flex items-center justify-between shrink-0">
      <div class="flex items-center space-x-2">
        <i class="fa-solid fa-clock-rotate-left text-brand-medium"></i>
        <h3 class="font-extrabold text-sm tracking-tight uppercase">Security Audit Trails</h3>
      </div>
    </div>
    <div class="p-4 sm:p-6 space-y-4 flex-1 overflow-y-auto custom-scrollbar">
      <div class="border-b border-slate-100 pb-3">
        <h4 id="timelineCitizenName" class="font-extrabold text-sm text-slate-950">Juan Dela Cruz</h4>
        <span id="timelineCitizenId" class="text-[10px] font-bold text-slate-400 font-mono">CIT-2026-0599</span>
      </div>

      <div class="relative border-l-2 border-slate-150 pl-4 ml-2 space-y-5 text-xs text-slate-650" id="timelineContainer">

      </div>
    </div>
    <div class="bg-slate-50 border-t border-slate-100 px-6 py-4 flex items-center justify-end shrink-0">
      <button onclick="closeModal('timelineModal')" class="bg-slate-900 hover:bg-slate-800 text-white px-4 py-2 rounded-none text-xs font-bold transition cursor-pointer">Close Log Trails</button>
    </div>
  </div>
</div>


<script src="<?php echo $basePath ?? '../'; ?>assets/js/citizen/citizen-account.js"></script>

<?php include '../../includes/footer.php'; ?>
