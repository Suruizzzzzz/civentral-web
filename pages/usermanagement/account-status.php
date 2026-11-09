<?php
$basePath = '../../';
require_once __DIR__ . '/../../src/bootstrap.php';

\App\Middleware\AuthMiddleware::handle($basePath);
\App\Middleware\PermissionMiddleware::require('users.status', $basePath);

include '../../includes/header.php';
include '../../includes/sidebar.php';
?>

<main class="flex-1 p-6 md:p-8 w-full space-y-6 overflow-y-auto">

  <!-- Page Header -->
  <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 border-b border-slate-200/60 dark:border-slate-800 pb-5">
    <div class="space-y-1">
      <h1 class="text-2xl font-bold tracking-tight text-slate-900 dark:text-white">
        Account Status Control Center
      </h1>
      <p class="text-xs text-slate-500 dark:text-slate-400 mt-1 font-normal max-w-2xl leading-relaxed">
        Monitor operational status, manage account access locks, and archive/restore user credentials.
      </p>
    </div>
  </div>

  <!-- Status Filter Tabs -->
  <div class="border-b border-slate-200">
    <nav class="flex flex-wrap -mb-px gap-6 text-xs font-bold text-slate-500">
      <!-- All Accounts Tab -->
      <button onclick="switchTab(this, 'all')" class="status-tab border-b-2 border-brand-dark text-brand-dark pb-3 px-1 flex items-center gap-2 cursor-pointer transition">
        <span>All Accounts</span>
        <span id="tabCountAll" class="bg-slate-100 text-slate-700 px-2 py-0.5 rounded-none text-[10px] font-black border border-slate-200/40">0</span>
      </button>

      <!-- Active Tab -->
      <button onclick="switchTab(this, 'Active')" class="status-tab border-b-2 border-transparent hover:text-slate-800 pb-3 px-1 flex items-center gap-2 cursor-pointer transition">
        <span class="h-2 w-2 rounded-full bg-emerald-500"></span>
        <span>Active</span>
        <span id="tabCountActive" class="bg-emerald-50 text-emerald-700 px-2 py-0.5 rounded-none text-[10px] font-black border border-emerald-100/40">0</span>
      </button>

      <!-- Inactive Tab -->
      <button onclick="switchTab(this, 'Deactivated')" class="status-tab border-b-2 border-transparent hover:text-slate-800 pb-3 px-1 flex items-center gap-2 cursor-pointer transition">
        <span class="h-2 w-2 rounded-full bg-slate-400"></span>
        <span>Inactive</span>
        <span id="tabCountDeactivated" class="bg-slate-100 text-slate-600 px-2 py-0.5 rounded-none text-[10px] font-black border border-slate-200/40">0</span>
      </button>

      <!-- Locked Tab -->
      <button onclick="switchTab(this, 'Locked')" class="status-tab border-b-2 border-transparent hover:text-slate-800 pb-3 px-1 flex items-center gap-2 cursor-pointer transition">
        <i class="fa-solid fa-lock text-[10px] text-rose-500"></i>
        <span>Locked</span>
        <span id="tabCountLocked" class="bg-rose-50 text-rose-600 px-2 py-0.5 rounded-none text-[10px] font-black border border-rose-100/40">0</span>
      </button>

      <!-- Archived Tab -->
      <button onclick="switchTab(this, 'Archived')" class="status-tab border-b-2 border-transparent hover:text-slate-800 pb-3 px-1 flex items-center gap-2 cursor-pointer transition">
        <i class="fa-solid fa-box-archive text-[10px] text-amber-500"></i>
        <span>Archived</span>
        <span id="tabCountArchived" class="bg-amber-50 text-amber-700 px-2 py-0.5 rounded-none text-[10px] font-black border border-amber-100/40">0</span>
      </button>
    </nav>
  </div>

  <!-- Search & Action Bar -->
  <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 bg-white p-4 rounded-none border border-slate-200/80 shadow-xs">
    <!-- Search Bar -->
    <div class="relative flex-1 max-w-md">
      <i class="fa-solid fa-magnifying-glass absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400 text-sm"></i>
      <input type="text" id="statusSearchInput" placeholder="Search by name, email, employee ID..." class="pl-10 pr-4 py-2.5 border border-slate-200 rounded-none text-xs w-full bg-slate-50/50 focus:bg-white focus:outline-none focus:border-brand-medium focus:ring-2 focus:ring-brand-medium/10 transition">
    </div>

    <!-- Export Button -->
    <div class="flex items-center gap-2 shrink-0">
      <button onclick="exportStatusList()" class="border border-slate-200 hover:bg-slate-50 text-slate-700 font-bold px-4 py-2.5 rounded-none text-xs flex items-center gap-2 cursor-pointer transition">
        <i class="fa-solid fa-download"></i>
        <span>Export</span>
      </button>
    </div>
  </div>

  <!-- Account Status Datatable -->
  <div class="bg-white border border-slate-200/80 rounded-none shadow-xs overflow-hidden">
    <div class="overflow-x-auto">
      <table class="w-full text-left border-collapse">
        <thead>
          <tr class="bg-slate-50/70 dark:bg-slate-800/40 border-b border-slate-200 dark:border-slate-800 text-[11px] font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400">
            <th class="py-3 px-4">User Details</th>
            <th class="py-3 px-4">Current Status</th>
            <th class="py-3 px-4">Security Health</th>
            <th class="py-3 px-4 text-right">Action Controls</th>
          </tr>
        </thead>
        <tbody id="statusTableBody" class="divide-y divide-slate-100/80 text-xs">
          <!-- Populated dynamically by JS -->
        </tbody>
      </table>
    </div>

    <!-- Table Footer / Pagination -->
    <div class="flex flex-col sm:flex-row items-center justify-between gap-4 px-6 py-4 border-t border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900">
      <!-- Left Side: Record Counter -->
      <div class="text-xs text-slate-500 dark:text-slate-400 font-medium" id="statusPaginationText">
        Showing <span class="font-semibold text-slate-700 dark:text-slate-200">0</span> to <span class="font-semibold text-slate-700 dark:text-slate-200">0</span> of <span class="font-semibold text-slate-700 dark:text-slate-200">0</span> records
      </div>

      <!-- Right Side: Segmented Numbered Pagination Bar -->
      <nav class="inline-flex -space-x-px border border-slate-200 dark:border-slate-700 rounded-none shadow-xs" aria-label="Pagination" id="statusPaginationControls">
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

<!-- MODALS SECTION -->

<!-- 1. DEACTIVATION CONFIRMATION MODAL -->
<div id="deactivateModal" class="fixed inset-0 z-[9999] flex items-center justify-center overflow-y-auto p-4 sm:p-6 opacity-0 pointer-events-none transition-all duration-300 bg-slate-900/70 backdrop-blur-sm">
  <div class="bg-white rounded-none shadow-xl w-full max-w-md max-h-[85vh] my-auto overflow-y-auto transform scale-95 transition-all duration-300">
    <div class="bg-slate-900 text-white px-6 py-4 flex items-center justify-between">
      <div class="flex items-center space-x-2">
        <i class="fa-solid fa-user-slash text-brand-medium"></i>
        <h3 class="font-extrabold text-sm tracking-tight uppercase">Confirm Deactivation</h3>
      </div>
    </div>
    <div class="p-6 space-y-4">
      <div class="flex items-start gap-3">
        <div class="h-10 w-10 rounded-none bg-rose-50 border border-rose-100 flex items-center justify-center text-rose-500 shrink-0">
          <i class="fa-solid fa-triangle-exclamation text-base"></i>
        </div>
        <div class="space-y-1">
          <h4 class="font-bold text-slate-900 text-xs">Revoke Operational Access?</h4>
          <p id="deactivateMessage" class="text-xs text-slate-500 leading-relaxed">Are you sure you want to deactivate this account? The user will immediately be barred from authentication hubs.</p>
        </div>
      </div>
    </div>
    <div class="bg-slate-50 border-t border-slate-100 px-6 py-4 flex items-center justify-end space-x-2">
      <button onclick="closeModal('deactivateModal')" class="border border-slate-200 bg-white hover:bg-slate-50 text-slate-600 px-4 py-2 rounded-none text-xs font-bold transition cursor-pointer">Cancel</button>
      <button onclick="executeDeactivate()" class="bg-rose-600 hover:bg-rose-700 text-white px-4 py-2 rounded-none text-xs font-bold transition cursor-pointer shadow-xs">Confirm Deactivate</button>
    </div>
  </div>
</div>

<!-- 2. LOCK/UNLOCK SUSPENSION MODAL -->
<div id="lockModal" class="fixed inset-0 z-[9999] flex items-center justify-center overflow-y-auto p-4 sm:p-6 opacity-0 pointer-events-none transition-all duration-300 bg-slate-900/70 backdrop-blur-sm">
  <div class="bg-white rounded-none shadow-xl w-full max-w-md max-h-[85vh] my-auto overflow-y-auto transform scale-95 transition-all duration-300">
    <div class="bg-slate-900 text-white px-6 py-4 flex items-center justify-between">
      <div class="flex items-center space-x-2">
        <i id="lockModalIcon" class="fa-solid fa-lock-open text-brand-medium"></i>
        <h3 id="lockModalTitle" class="font-extrabold text-sm tracking-tight uppercase">Modify Access Lock</h3>
      </div>
    </div>
    <div class="p-6 space-y-4">
      <div class="flex items-start gap-3">
        <div id="lockModalStatusIconBox" class="h-10 w-10 rounded-none bg-blue-50 border border-blue-100 flex items-center justify-center text-blue-600 shrink-0">
          <i class="fa-solid fa-shield-halved text-base"></i>
        </div>
        <div class="space-y-1">
          <h4 id="lockMessageHeader" class="font-bold text-slate-900 text-xs">Unlock Account Access?</h4>
          <p id="lockModalMessage" class="text-xs text-slate-500 leading-relaxed">Unlocking this account will immediately reset the failed login counter to 0, clearing all authentication barriers.</p>
        </div>
      </div>
    </div>
    <div class="bg-slate-50 border-t border-slate-100 px-6 py-4 flex items-center justify-end space-x-2">
      <button onclick="closeModal('lockModal')" class="border border-slate-200 bg-white hover:bg-slate-50 text-slate-600 px-4 py-2 rounded-none text-xs font-bold transition cursor-pointer">Cancel</button>
      <button id="lockModalConfirmBtn" onclick="executeLockToggle()" class="bg-teal-600 hover:bg-teal-700 text-white px-4 py-2 rounded-none text-xs font-medium transition cursor-pointer shadow-xs focus:outline-none focus:ring-2 focus:ring-teal-500/40">Unlock Access</button>
    </div>
  </div>
</div>

<!-- 3. ARCHIVE/RESTORE CONFIRMATION MODAL -->
<div id="archiveModal" class="fixed inset-0 z-[9999] flex items-center justify-center overflow-y-auto p-4 sm:p-6 opacity-0 pointer-events-none transition-all duration-300 bg-slate-900/70 backdrop-blur-sm">
  <div class="bg-white rounded-none shadow-xl w-full max-w-md max-h-[85vh] my-auto overflow-y-auto transform scale-95 transition-all duration-300">
    <div class="bg-slate-900 text-white px-6 py-4 flex items-center justify-between">
      <div class="flex items-center space-x-2">
        <i id="archiveModalIcon" class="fa-solid fa-box-archive text-brand-medium"></i>
        <h3 id="archiveModalTitle" class="font-extrabold text-sm tracking-tight uppercase">Archive Staff Profile</h3>
      </div>
    </div>
    <div class="p-6 space-y-4">
      <div class="flex items-start gap-3">
        <div id="archiveModalIconBox" class="h-10 w-10 rounded-none bg-amber-50 border border-amber-100 flex items-center justify-center text-amber-600 shrink-0">
          <i class="fa-solid fa-circle-exclamation text-base"></i>
        </div>
        <div class="space-y-1">
          <h4 id="archiveMessageHeader" class="font-bold text-slate-900 text-xs">Soft-Delete User Profile?</h4>
          <p id="archiveModalMessage" class="text-xs text-slate-500 leading-relaxed">Archiving soft-deletes the user from active lists. All active sessions will end immediately. Data is retained for audit logging.</p>
        </div>
      </div>
    </div>
    <div class="bg-slate-50 border-t border-slate-100 px-6 py-4 flex items-center justify-end space-x-2">
      <button onclick="closeModal('archiveModal')" class="border border-slate-200 bg-white hover:bg-slate-50 text-slate-600 px-4 py-2 rounded-none text-xs font-bold transition cursor-pointer">Cancel</button>
      <button id="archiveModalConfirmBtn" onclick="executeArchiveToggle()" class="bg-amber-600 hover:bg-amber-700 text-white px-4 py-2 rounded-none text-xs font-bold transition cursor-pointer shadow-xs">Confirm Archive</button>
    </div>
  </div>
</div>


<script src="<?php echo $basePath ?? '../'; ?>assets/js/usermanagement/account-status.js"></script>

<?php include '../../includes/footer.php'; ?>
