<?php
$basePath = '../../';
require_once __DIR__ . '/../../src/bootstrap.php';

\App\Middleware\AuthMiddleware::handle($basePath);
\App\Middleware\PermissionMiddleware::require('audit.login_history', $basePath);

include '../../includes/header.php';
include '../../includes/sidebar.php';
?>

<main class="flex-1 p-6 md:p-8 w-full space-y-6 overflow-y-auto bg-slate-50">

  <!-- Breadcrumb & Page Header -->
  <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-4 border-b border-slate-200/80 pb-5">
    <div class="space-y-1.5">
      <div class="flex items-center space-x-2 text-xs font-bold uppercase tracking-wider text-slate-400">
        <span>Audit Logs</span>
        <i class="fa-solid fa-chevron-right text-[8px] opacity-60"></i>
        <span class="text-slate-900">Login History & Session Audit</span>
      </div>
      <h1 class="text-2xl font-black text-slate-950 tracking-tight flex items-center gap-2.5 mt-4">
        <i class="fa-solid fa-shield-halved text-[#0f172a]"></i>
        Login History & Authentication Audit
      </h1>
      <p class="text-xs text-slate-500 max-w-3xl leading-relaxed font-medium">
        Track real-time system entries, monitor session lifetimes, and catch unauthorized authentication compromises.
      </p>
    </div>

    <!-- Action Controls -->
    <div class="flex items-center gap-3 shrink-0">
      <button onclick="refreshLogs()" class="inline-flex items-center justify-center gap-2 px-3.5 py-2.5 border border-slate-200 text-slate-700 bg-white hover:bg-slate-50 font-bold rounded-xl text-xs tracking-wide transition shadow-xs cursor-pointer focus:outline-none focus:ring-2 focus:ring-[#0f172a]/20">
        <i class="fa-solid fa-rotate text-slate-400"></i>
        <span>Refresh Log</span>
      </button>

      <button onclick="printData()" class="inline-flex items-center justify-center gap-2 px-3.5 py-2.5 border border-slate-200 text-slate-700 bg-white hover:bg-slate-50 font-bold rounded-xl text-xs tracking-wide transition shadow-xs cursor-pointer focus:outline-none focus:ring-2 focus:ring-[#0f172a]/20">
        <i class="fa-solid fa-print text-slate-400"></i>
        <span>Print / PDF</span>
      </button>

      <!-- Export Dropdown -->
      <div class="relative inline-block text-left" id="exportDropdownContainer">
        <button id="exportDropdownBtn" onclick="toggleExportDropdown(event)" 
          class="inline-flex items-center justify-center gap-2.5 px-3.5 py-2.5 border border-slate-200 text-slate-700 bg-white hover:bg-slate-50 font-bold rounded-xl text-xs tracking-wide transition shadow-xs cursor-pointer focus:outline-none focus:ring-2 focus:ring-[#0f172a]/20">
          <i class="fa-solid fa-file-export text-slate-400"></i>
          <span>Export Logs</span>
          <i class="fa-solid fa-chevron-down text-[9px] text-slate-400"></i>
        </button>
        <!-- Dropdown Card -->
        <div id="exportDropdownMenu" class="hidden absolute right-0 mt-2 w-48 bg-white border border-slate-200 rounded-xl shadow-lg py-1 z-50 text-xs text-slate-600 transition-all transform scale-95 origin-top-right">
          <a href="#" onclick="exportLogs('PDF', event)" class="flex items-center gap-2.5 px-4 py-2.5 hover:bg-slate-50 text-slate-700 transition font-bold">
            <i class="fa-solid fa-file-pdf text-red-500 text-sm"></i>
            <span>Export to PDF</span>
          </a>
          <a href="#" onclick="exportLogs('Excel', event)" class="flex items-center gap-2.5 px-4 py-2.5 hover:bg-slate-50 text-slate-700 transition font-bold">
            <i class="fa-solid fa-file-excel text-emerald-600 text-sm"></i>
            <span>Export to Excel</span>
          </a>
          <a href="#" onclick="exportLogs('CSV', event)" class="flex items-center gap-2.5 px-4 py-2.5 hover:bg-slate-50 text-slate-700 transition font-bold">
            <i class="fa-solid fa-file-csv text-emerald-500 text-sm"></i>
            <span>Download CSV</span>
          </a>
        </div>
      </div>
    </div>
  </div>

  <!-- Quick-Glance Security Metrics Ribbon (4 Columns) -->
  <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5">
    <!-- Successful Logins -->
    <div class="bg-white border border-slate-200 rounded-2xl p-5 shadow-xs flex items-center justify-between group relative overflow-hidden transition hover:shadow-md">
      <div class="absolute top-0 left-0 w-1.5 h-full bg-emerald-500"></div>
      <div class="space-y-1.5">
        <span class="text-[9px] font-black uppercase tracking-wider text-slate-400 block">Successful Logins</span>
        <div class="flex items-baseline space-x-2">
          <h3 class="text-2xl font-black text-slate-900 tracking-tight"><span id="successfulCount">0</span></h3>
          <span class="text-[9px] font-bold text-emerald-700 bg-emerald-50 border border-emerald-100 px-1.5 py-0.5 rounded flex items-center gap-0.5">
            🟢 Clean
          </span>
        </div>
      </div>
      <div class="h-10 w-10 rounded-xl bg-slate-50 border border-slate-100 flex items-center justify-center text-slate-400 group-hover:bg-emerald-50 group-hover:text-emerald-700 group-hover:border-emerald-200/50 transition">
        <i class="fa-solid fa-circle-check text-base"></i>
      </div>
    </div>

    <!-- Failed Attempts -->
    <div class="bg-white border border-slate-200 rounded-2xl p-5 shadow-xs flex items-center justify-between group relative overflow-hidden transition hover:shadow-md">
      <div class="absolute top-0 left-0 w-1.5 h-full bg-rose-500"></div>
      <div class="space-y-1.5">
        <span class="text-[9px] font-black uppercase tracking-wider text-slate-400 block">Failed Attempts</span>
        <div class="flex items-baseline space-x-2">
          <h3 class="text-2xl font-black text-slate-900 tracking-tight"><span id="failedCount">0</span></h3>
          <span class="text-[9px] font-bold text-rose-700 bg-rose-50 border border-rose-100 px-1.5 py-0.5 rounded flex items-center gap-0.5">
            🔴 Failed
          </span>
        </div>
      </div>
      <div class="h-10 w-10 rounded-xl bg-slate-50 border border-slate-100 flex items-center justify-center text-slate-400 group-hover:bg-rose-50 group-hover:text-rose-700 group-hover:border-rose-200/50 transition">
        <i class="fa-solid fa-triangle-exclamation text-base"></i>
      </div>
    </div>

    <!-- Active Sessions -->
    <div class="bg-white border border-slate-200 rounded-2xl p-5 shadow-xs flex items-center justify-between group relative overflow-hidden transition hover:shadow-md">
      <div class="absolute top-0 left-0 w-1.5 h-full bg-blue-500"></div>
      <div class="space-y-1.5">
        <span class="text-[9px] font-black uppercase tracking-wider text-slate-400 block">Active Sessions</span>
        <div class="flex items-baseline space-x-2">
          <h3 class="text-2xl font-black text-slate-900 tracking-tight"><span id="activeCount">0</span></h3>
          <span class="text-[9px] font-bold text-blue-700 bg-blue-50 border border-blue-100 px-1.5 py-0.5 rounded flex items-center gap-1 animate-pulse">
            <span class="h-1.5 w-1.5 rounded-full bg-blue-500"></span> Online
          </span>
        </div>
      </div>
      <div class="h-10 w-10 rounded-xl bg-slate-50 border border-slate-100 flex items-center justify-center text-slate-400 group-hover:bg-blue-50 group-hover:text-blue-700 group-hover:border-blue-200/50 transition">
        <i class="fa-solid fa-user-clock text-base"></i>
      </div>
    </div>

    <!-- Account Locks -->
    <div class="bg-white border border-slate-200 rounded-2xl p-5 shadow-xs flex items-center justify-between group relative overflow-hidden transition hover:shadow-md">
      <div class="absolute top-0 left-0 w-1.5 h-full bg-amber-500"></div>
      <div class="space-y-1.5">
        <span class="text-[9px] font-black uppercase tracking-wider text-slate-400 block">Account Locks</span>
        <div class="flex items-baseline space-x-2">
          <h3 class="text-2xl font-black text-slate-900 tracking-tight"><span id="lockCount">0</span></h3>
          <span class="text-[9px] font-bold text-amber-700 bg-amber-50 border border-amber-100 px-1.5 py-0.5 rounded flex items-center gap-0.5">
            🔒 Locked
          </span>
        </div>
      </div>
      <div class="h-10 w-10 rounded-xl bg-slate-50 border border-slate-100 flex items-center justify-center text-slate-400 group-hover:bg-amber-50 group-hover:text-amber-700 group-hover:border-amber-200/50 transition">
        <i class="fa-solid fa-user-lock text-base"></i>
      </div>
    </div>
  </div>

  <!-- Audit Filtering Controls -->
  <div class="bg-white border border-slate-200 rounded-2xl shadow-xs p-5 md:p-6 space-y-4">
    <div class="flex items-center justify-between border-b border-slate-100 pb-3">
      <div class="flex items-center gap-2 text-slate-900">
        <i class="fa-solid fa-sliders text-xs text-slate-400"></i>
        <h3 class="text-xs font-bold uppercase tracking-wider">Search & Filter Controls</h3>
      </div>
      <button onclick="resetFilters()" class="text-xs font-bold text-[#0f172a] hover:text-[#1e3a8a] transition flex items-center gap-1.5 focus:outline-none cursor-pointer">
        <i class="fa-solid fa-rotate-right text-[10px]"></i>
        Reset Filters
      </button>
    </div>

    <!-- Filters Fields Grid -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
      <!-- Search Username/Email -->
      <div class="space-y-1.5">
        <label class="text-[10px] font-black uppercase tracking-wider text-slate-400 block" for="filterSearch">Search User / Email</label>
        <div class="relative">
          <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
            <i class="fa-solid fa-magnifying-glass text-xs"></i>
          </span>
          <input type="text" id="filterSearch" oninput="applyFilters()" placeholder="e.g. joshua.suruiz..."
            class="w-full bg-white border border-slate-200 text-slate-700 font-semibold text-xs rounded-xl pl-10 pr-4 py-2.5 focus:outline-none focus:border-[#0f172a] focus:ring-2 focus:ring-[#0f172a]/10 transition placeholder-slate-400">
        </div>
      </div>

      <!-- Date Range Picker -->
      <div class="space-y-1.5">
        <label class="text-[10px] font-black uppercase tracking-wider text-slate-400 block" for="filterDate">Date Picker</label>
        <div class="relative">
          <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
            <i class="fa-solid fa-calendar text-xs"></i>
          </span>
          <input type="date" id="filterDate" onchange="applyFilters()"
            class="w-full bg-white border border-slate-200 text-slate-700 font-semibold text-xs rounded-xl pl-10 pr-4 py-2.5 focus:outline-none focus:border-[#0f172a] focus:ring-2 focus:ring-[#0f172a]/10 transition">
        </div>
      </div>

      <!-- Status Filter -->
      <div class="space-y-1.5">
        <label class="text-[10px] font-black uppercase tracking-wider text-slate-400 block" for="filterStatus">Login Status Filter</label>
        <div class="relative">
          <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
            <i class="fa-solid fa-shield-halved text-xs"></i>
          </span>
          <select id="filterStatus" onchange="applyFilters()"
            class="w-full bg-white border border-slate-200 text-slate-700 font-semibold text-xs rounded-xl pl-10 pr-4 py-2.5 appearance-none focus:outline-none focus:border-[#0f172a] focus:ring-2 focus:ring-[#0f172a]/10 transition cursor-pointer">
            <option value="All">All Attempt Types</option>
            <option value="Successful Login">Successful Login</option>
            <option value="Failed Login">Failed Login</option>
            <option value="Logout">Logout</option>
            <option value="Password Changed">Password Changed</option>
            <option value="Session Expired">Session Expired</option>
            <option value="Account Locked">Account Locked</option>
          </select>
          <span class="absolute inset-y-0 right-0 pr-3.5 flex items-center pointer-events-none text-slate-400">
            <i class="fa-solid fa-chevron-down text-[9px]"></i>
          </span>
        </div>
      </div>

      <!-- Department Selector -->
      <div class="space-y-1.5">
        <label class="text-[10px] font-black uppercase tracking-wider text-slate-400 block" for="filterDepartment">Department Selector</label>
        <div class="relative">
          <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
            <i class="fa-solid fa-building text-xs"></i>
          </span>
          <select id="filterDepartment" onchange="applyFilters()"
            class="w-full bg-white border border-slate-200 text-slate-700 font-semibold text-xs rounded-xl pl-10 pr-4 py-2.5 appearance-none focus:outline-none focus:border-[#0f172a] focus:ring-2 focus:ring-[#0f172a]/10 transition cursor-pointer">
            <option value="All">All Departments</option>
            <option value="Central IT">Central IT</option>
            <option value="Education">Education</option>
            <option value="Health">Health</option>
            <option value="Treasury">Treasury</option>
          </select>
          <span class="absolute inset-y-0 right-0 pr-3.5 flex items-center pointer-events-none text-slate-400">
            <i class="fa-solid fa-chevron-down text-[9px]"></i>
          </span>
        </div>
      </div>
    </div>
  </div>

  <!-- Authentication Audit Trail Table Card -->
  <div class="bg-white border border-slate-200 rounded-2xl shadow-xs overflow-hidden flex flex-col">
    <div class="overflow-x-auto overflow-y-auto max-h-[600px] w-full custom-scrollbar">
      <table class="w-full text-left border-collapse min-w-[1100px]">
        <thead class="sticky top-0 bg-slate-50 z-10 border-b border-slate-200 shadow-xs">
          <tr>
            <th class="py-3.5 px-5 text-[10px] font-black text-slate-400 uppercase tracking-wider">Login ID</th>
            <th class="py-3.5 px-5 text-[10px] font-black text-slate-400 uppercase tracking-wider">Identity Details</th>
            <th class="py-3.5 px-5 text-[10px] font-black text-slate-400 uppercase tracking-wider">Timeline Triggers</th>
            <th class="py-3.5 px-5 text-[10px] font-black text-slate-400 uppercase tracking-wider text-center">Session Lifespan</th>
            <th class="py-3.5 px-5 text-[10px] font-black text-slate-400 uppercase tracking-wider">Authentication Status</th>
            <th class="py-3.5 px-5 text-[10px] font-black text-slate-400 uppercase tracking-wider">Network & Footprint</th>
          </tr>
        </thead>
        <tbody id="loginTableBody" class="divide-y divide-slate-100 text-xs">
          <!-- Dynamic Rows -->
          <tr id="noResultsRow" class="hidden">
            <td colspan="6" class="py-12 text-center text-slate-400">
              <div class="flex flex-col items-center justify-center space-y-2">
                <i class="fa-solid fa-user-slash text-3xl text-slate-300"></i>
                <p class="text-xs font-bold">No matching authentication logs found</p>
                <p class="text-[10px] font-semibold text-slate-400">Try adjusting your filters or resetting them to defaults.</p>
              </div>
            </td>
          </tr>
        </tbody>
      </table>
    </div>

    <!-- Pagination Footer Container (50 per page) -->
    <div id="paginationFooter" class="px-5 py-3.5 bg-slate-50/90 border-t border-slate-200 flex flex-col sm:flex-row items-center justify-between gap-3 text-xs text-slate-500 font-semibold select-none">
      <div id="paginationInfo" class="text-xs text-slate-500 font-medium">
        Showing <span id="paginationStart" class="font-bold text-slate-900">0</span> to <span id="paginationEnd" class="font-bold text-slate-900">0</span> of <span id="paginationTotal" class="font-bold text-slate-900">0</span> entries
      </div>
      <div class="flex items-center gap-1.5" id="paginationControls">
        <button id="prevPageBtn" onclick="changePage(-1)" class="px-3 py-1.5 border border-slate-200 rounded-xl bg-white hover:bg-slate-100 disabled:opacity-40 disabled:cursor-not-allowed text-slate-700 font-bold transition flex items-center gap-1 text-xs cursor-pointer shadow-xs">
          <i class="fa-solid fa-chevron-left text-[10px]"></i> Previous
        </button>
        <div id="pageNumbers" class="flex items-center gap-1 font-bold text-xs">
          <!-- Dynamic Page Numbers -->
        </div>
        <button id="nextPageBtn" onclick="changePage(1)" class="px-3 py-1.5 border border-slate-200 rounded-xl bg-white hover:bg-slate-100 disabled:opacity-40 disabled:cursor-not-allowed text-slate-700 font-bold transition flex items-center gap-1 text-xs cursor-pointer shadow-xs">
          Next <i class="fa-solid fa-chevron-right text-[10px]"></i>
        </button>
      </div>
    </div>
  </div>

  <!-- Session Inspector Modal -->
  <div id="sessionInspectorModal" class="hidden fixed inset-0 bg-slate-900/70 backdrop-blur-sm z-[9999] overflow-y-auto p-4 sm:p-6 flex items-center justify-center transition-all duration-300">
    <div id="modalCard" class="bg-white border border-slate-200 shadow-2xl rounded-2xl w-full max-w-xl max-h-[85vh] my-auto overflow-hidden flex flex-col transform scale-95 opacity-0 transition-all duration-300">
      
      <!-- Modal Header -->
      <div class="px-5 py-3.5 sm:px-6 sm:py-4 border-b border-slate-200/80 bg-slate-50 flex items-center justify-between shrink-0">
        <div class="flex items-center gap-3">
          <div class="h-9 w-9 rounded-xl bg-slate-950 flex items-center justify-center text-white text-sm shadow-sm bg-slate-900">
            <i class="fa-solid fa-key"></i>
          </div>
          <div>
            <h3 class="text-sm font-black text-slate-900">Session Profile Inspector</h3>
            <span id="modalLogId" class="font-mono text-[10px] font-black text-slate-400 uppercase">#LOG-45091</span>
          </div>
        </div>
      </div>

      <!-- Modal Body -->
      <div class="p-4 sm:p-6 space-y-4 overflow-y-auto flex-1 custom-scrollbar">
        <!-- Event Status Header Banner -->
        <div id="modalStatusBanner" class="p-4 rounded-xl flex items-center gap-3">
          <div id="modalStatusIconContainer" class="h-8 w-8 rounded-lg flex items-center justify-center shrink-0">
            <!-- Dynamic Icon -->
          </div>
          <div>
            <h4 id="modalStatusTitle" class="text-xs font-bold">Successful Login</h4>
            <p id="modalStatusMsg" class="text-[10px] leading-relaxed font-semibold">Security token registered. Session initialized.</p>
          </div>
        </div>

        <!-- Identity and Timelines -->
        <div class="grid grid-cols-2 gap-y-4 gap-x-6 border-b border-slate-100 pb-5 text-xs">
          <div>
            <span class="text-[9px] font-black uppercase tracking-wider text-slate-400 block mb-1">Actor Account</span>
            <div id="modalActorName" class="font-bold text-slate-900">joshua.suruiz</div>
            <div id="modalActorEmail" class="text-[10px] text-slate-500 font-semibold mt-0.5">joshua@caloocan.gov.ph</div>
          </div>
          
          <div>
            <span class="text-[9px] font-black uppercase tracking-wider text-slate-400 block mb-1">Role / Department</span>
            <div id="modalRoleDept" class="font-bold text-slate-900">Superadmin | Central IT</div>
          </div>

          <div>
            <span class="text-[9px] font-black uppercase tracking-wider text-slate-400 block mb-1">Login Time</span>
            <div id="modalLoginTime" class="font-bold text-slate-900">Jul 18, 2026, 08:30 AM</div>
          </div>

          <div>
            <span class="text-[9px] font-black uppercase tracking-wider text-slate-400 block mb-1">Logout / Expire Time</span>
            <div id="modalLogoutTime" class="font-bold text-slate-900">—</div>
          </div>

          <div>
            <span class="text-[9px] font-black uppercase tracking-wider text-slate-400 block mb-1">Session Lifespan</span>
            <div id="modalLifespan" class="font-bold text-slate-900">Active</div>
          </div>

          <div>
            <span class="text-[9px] font-black uppercase tracking-wider text-slate-400 block mb-1">Authentication State</span>
            <div id="modalStatusBadge" class="mt-1">
              <!-- status badge -->
            </div>
          </div>

          <div class="col-span-2 bg-slate-50 border border-slate-200/60 p-3.5 rounded-xl space-y-2">
            <span class="text-[9px] font-black uppercase tracking-wider text-slate-400 block mb-0.5">Access Log Details</span>
            <p id="modalLogDetails" class="text-[10px] font-semibold text-slate-700 leading-relaxed">Successful MFA challenge verified using hardware security key (YubiKey 5C).</p>
          </div>

          <div class="col-span-2 grid grid-cols-3 gap-4 bg-slate-50 border border-slate-200/60 p-3.5 rounded-xl font-mono text-[10px]">
            <div>
              <span class="text-[8px] font-black uppercase tracking-wider text-slate-400 block mb-0.5">IP Address</span>
              <span id="modalIp" class="font-bold text-slate-700">192.168.1.12</span>
            </div>
            <div>
              <span class="text-[8px] font-black uppercase tracking-wider text-slate-400 block mb-0.5">Device Platform</span>
              <span id="modalDevice" class="font-bold text-slate-700">Desktop - Chrome</span>
            </div>
            <div>
              <span class="text-[8px] font-black uppercase tracking-wider text-slate-400 block mb-0.5">Approx. Location</span>
              <span id="modalLocation" class="font-bold text-slate-700">Caloocan City, PH</span>
            </div>
          </div>
        </div>

        <!-- Technical Payload (JSON) -->
        <div class="space-y-2">
          <div class="flex items-center justify-between">
            <span class="text-[9px] font-black uppercase tracking-wider text-slate-400">Technical Context Payload</span>
            <button onclick="copyModalPayload()" class="text-[10px] font-bold text-[#0f172a] hover:text-[#1e3a8a] transition flex items-center gap-1 focus:outline-none cursor-pointer">
              <i class="fa-solid fa-copy"></i>
              Copy JSON
            </button>
          </div>
          <pre class="bg-slate-900 text-slate-200 p-4 rounded-xl text-[10px] font-mono overflow-x-auto leading-relaxed border border-slate-800 shadow-inner max-h-48 custom-scrollbar"><code id="modalPayloadText">{}</code></pre>
        </div>
      </div>

      <!-- Modal Footer -->
      <div class="px-6 py-4 border-t border-slate-200/80 bg-slate-50 flex items-center justify-end gap-3 shrink-0">
        <button onclick="closeSessionModal()" class="px-4 py-2 border border-slate-200 text-slate-700 hover:bg-slate-100 font-bold rounded-xl text-xs tracking-wide transition cursor-pointer focus:outline-none">
          Close Inspector
        </button>
      </div>

    </div>
  </div>

  <!-- Toast Notification Container -->
  <div id="toastContainer" class="fixed bottom-5 right-5 z-50 flex flex-col gap-2"></div>

</main>

<script src="../../assets/js/audit/audit-export.js"></script>
<script src="../../assets/js/audit/login-history.js"></script>

<?php include '../../includes/footer.php'; ?>
