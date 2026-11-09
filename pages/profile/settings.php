<?php
$basePath = '../../';
require_once __DIR__ . '/../../src/bootstrap.php';

\App\Middleware\AuthMiddleware::handle($basePath);

include '../../includes/header.php';
include '../../includes/sidebar.php';
?>

<main class="flex-1 p-6 md:p-8 w-full space-y-6 overflow-y-auto">

  <!-- Page Header -->
  <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 border-b border-slate-200/60 dark:border-slate-800 pb-5">
    <div class="space-y-1">
      <h1 class="text-2xl font-bold tracking-tight text-slate-900 dark:text-white">
        Account &amp; System Settings
      </h1>
      <p class="text-xs text-slate-500 dark:text-slate-400 mt-1 font-normal max-w-2xl leading-relaxed">
        Manage your personal profile, credentials, security policies, and Civentral portal display preferences.
      </p>
    </div>
    <div class="shrink-0 flex items-center gap-2 bg-emerald-50 dark:bg-emerald-950/40 border border-emerald-200/80 dark:border-emerald-800/60 text-emerald-700 dark:text-emerald-400 text-[10px] font-black uppercase tracking-wider px-3 py-2 rounded-none">
      <i class="fa-solid fa-circle-check text-emerald-500"></i>
      <span>Profile Synced</span>
    </div>
  </div>

  <!-- Unified Settings 2-Column Responsive Workspace -->
  <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-start">

    <!-- Left Column: Sticky Sub-Nav Menu (lg:col-span-3, ~25%) -->
    <aside class="lg:col-span-3 lg:sticky lg:top-24 self-start">
      <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-none shadow-2xs p-2 space-y-1">
        
        <!-- Tab Button 1: Account -->
        <button type="button" onclick="switchSettingsTab('account')" id="navTabAccount"
          class="settings-nav-item w-full text-left px-4 py-3 flex items-start gap-3 transition cursor-pointer rounded-none border-l-2 bg-teal-50 dark:bg-teal-950/40 text-teal-700 dark:text-teal-400 border-l-teal-600 dark:border-l-teal-400 font-bold">
          <i class="fa-solid fa-circle-user mt-1 text-sm"></i>
          <div>
            <p class="text-xs font-bold font-title">Account</p>
            <p class="text-[11px] text-slate-500 dark:text-slate-400">Personal &amp; official profile details</p>
          </div>
        </button>

        <!-- Tab Button 2: Security -->
        <button type="button" onclick="switchSettingsTab('security')" id="navTabSecurity"
          class="settings-nav-item w-full text-left px-4 py-3 flex items-start gap-3 transition cursor-pointer rounded-none border-l-2 border-l-transparent text-slate-600 dark:text-slate-400 hover:bg-slate-50 dark:hover:bg-slate-800/60 hover:text-slate-900 dark:hover:text-white">
          <i class="fa-solid fa-lock mt-1 text-sm"></i>
          <div>
            <p class="text-xs font-bold font-title">Security</p>
            <p class="text-[11px] text-slate-500 dark:text-slate-400">Password &amp; session credentials</p>
          </div>
        </button>

        <!-- Tab Button 3: Preferences -->
        <button type="button" onclick="switchSettingsTab('preferences')" id="navTabPreferences"
          class="settings-nav-item w-full text-left px-4 py-3 flex items-start gap-3 transition cursor-pointer rounded-none border-l-2 border-l-transparent text-slate-600 dark:text-slate-400 hover:bg-slate-50 dark:hover:bg-slate-800/60 hover:text-slate-900 dark:hover:text-white">
          <i class="fa-solid fa-sliders mt-1 text-sm"></i>
          <div>
            <p class="text-xs font-bold font-title">Preferences</p>
            <p class="text-[11px] text-slate-500 dark:text-slate-400">Theme mode &amp; appearance</p>
          </div>
        </button>

      </div>
    </aside>

    <!-- Right Column: Content Canvas (lg:col-span-9, ~75%) -->
    <div class="lg:col-span-9 space-y-6">

      <!-- ========================================================================= -->
      <!-- PANE 1: ACCOUNT (Profile Identity, Avatar, Contact Details)               -->
      <!-- ========================================================================= -->
      <div id="tab-account" class="settings-tab-pane space-y-6">
        <div class="grid grid-cols-1 xl:grid-cols-12 gap-6 items-start">

          <!-- Left Side: Avatar Card & Account Summary (xl:col-span-4) -->
          <div class="xl:col-span-4 space-y-5">

            <!-- Profile Avatar Card -->
            <div class="bg-white dark:bg-slate-900 border border-slate-200/80 dark:border-slate-800 rounded-none shadow-xs overflow-hidden">
              <div class="bg-slate-50 dark:bg-slate-800/50 border-b border-slate-200/60 dark:border-slate-800 px-5 py-3.5 flex items-center gap-2">
                <i class="fa-solid fa-circle-user text-brand-medium text-sm"></i>
                <span class="text-[10px] font-black text-slate-500 dark:text-slate-400 uppercase tracking-wider">Profile Avatar</span>
              </div>
              <div class="p-6 flex flex-col items-center gap-5">

                <div id="avatarWrapper"
                     class="relative group cursor-pointer w-32 h-32 shrink-0"
                     onclick="document.getElementById('avatarFileInput').click()"
                     title="Click to change photo">
                  <img
                    id="avatarPreview"
                    src="https://ui-avatars.com/api/?name=User&background=EEF5FF&color=176B87&bold=true&size=128&font-size=0.42"
                    alt="Profile Avatar"
                    class="w-32 h-32 rounded-full object-cover border-4 border-white dark:border-slate-800 shadow-lg ring-2 ring-brand-border dark:ring-slate-700 transition-all duration-300 group-hover:ring-brand-medium"
                  >
                  <div class="absolute inset-0 rounded-full bg-slate-900/55 opacity-0 group-hover:opacity-100 transition-all duration-300 flex flex-col items-center justify-center gap-1.5 backdrop-blur-sm">
                    <i class="fa-solid fa-camera text-white text-xl drop-shadow"></i>
                    <span class="text-white text-[9px] font-black uppercase tracking-widest leading-none">Change Photo</span>
                  </div>
                </div>

                <input type="file" id="avatarFileInput" accept="image/jpeg, image/png" class="hidden" onchange="previewAvatar(event)">

                <div class="text-center space-y-0.5">
                  <p id="sidebarName" class="text-sm font-black text-slate-800 dark:text-white tracking-tight">...</p>
                  <span id="sidebarRole" class="inline-block text-[9px] font-black uppercase tracking-widest text-brand-dark dark:text-cyan-400 bg-brand-light dark:bg-slate-800 border border-brand-border/60 dark:border-slate-700 px-2 py-0.5 rounded-none">...</span>
                </div>

                <div class="w-full space-y-2">
                  <button type="button" onclick="document.getElementById('avatarFileInput').click()" class="w-full bg-[#0f172a] dark:bg-slate-800 hover:bg-slate-800 dark:hover:bg-slate-700 text-white font-bold px-4 py-2.5 rounded-none text-xs flex items-center justify-center gap-2 cursor-pointer transition shadow-xs">
                    <i class="fa-solid fa-arrow-up-from-bracket text-[10px]"></i>
                    Upload New Image
                  </button>
                  <button type="button" onclick="removePhoto()" class="w-full border border-red-200 dark:border-rose-900/50 hover:bg-red-50 dark:hover:bg-rose-950/40 text-red-500 hover:text-red-600 dark:text-rose-400 font-bold px-4 py-2.5 rounded-none text-xs flex items-center justify-center gap-2 cursor-pointer transition">
                    <i class="fa-solid fa-trash-can text-[10px]"></i>
                    Remove Photo
                  </button>
                </div>

                <p class="text-[10px] text-slate-400 dark:text-slate-500 text-center leading-relaxed font-medium">
                  Supported formats: <span class="font-black text-slate-500 dark:text-slate-400">JPG, PNG</span>.<br>
                  Max file size: <span class="font-black text-slate-500 dark:text-slate-400">2MB</span>.
                </p>
              </div>
            </div>

            <!-- Account Summary Card -->
            <div class="bg-white dark:bg-slate-900 border border-slate-200/80 dark:border-slate-800 rounded-none shadow-xs overflow-hidden">
              <div class="bg-slate-50 dark:bg-slate-800/50 border-b border-slate-200/60 dark:border-slate-800 px-5 py-3.5 flex items-center gap-2">
                <i class="fa-solid fa-id-badge text-brand-medium text-sm"></i>
                <span class="text-[10px] font-black text-slate-500 dark:text-slate-400 uppercase tracking-wider">Account Summary</span>
              </div>
              <div class="p-5 space-y-3.5">
                <div class="flex items-center justify-between text-xs">
                  <span class="text-slate-400 font-semibold">Employee ID</span>
                  <span id="summaryEmpId" class="font-black text-slate-700 dark:text-slate-200 font-mono tracking-wider">...</span>
                </div>
                <div class="border-t border-slate-100 dark:border-slate-800"></div>
                <div class="flex items-center justify-between text-xs">
                  <span class="text-slate-400 font-semibold">Role</span>
                  <span id="summaryRole" class="font-black text-slate-700 dark:text-slate-200">...</span>
                </div>
                <div class="border-t border-slate-100 dark:border-slate-800"></div>
                <div class="flex items-center justify-between text-xs">
                  <span class="text-slate-400 font-semibold">Department</span>
                  <span id="summaryDept" class="font-black text-slate-700 dark:text-slate-200">...</span>
                </div>
                <div class="border-t border-slate-100 dark:border-slate-800"></div>
                <div class="flex items-center justify-between text-xs">
                  <span class="text-slate-400 font-semibold">Account Status</span>
                  <span id="summaryStatus" class="inline-flex items-center gap-1 text-emerald-700 dark:text-emerald-400 bg-emerald-50 dark:bg-emerald-950/50 border border-emerald-200/80 dark:border-emerald-800/60 font-black px-2 py-0.5 rounded-none text-[9px] uppercase tracking-wider">
                    <span class="h-1.5 w-1.5 rounded-full bg-emerald-500 animate-pulse"></span>
                    Active
                  </span>
                </div>
                <div class="border-t border-slate-100 dark:border-slate-800"></div>
                <div class="flex items-center justify-between text-xs">
                  <span class="text-slate-400 font-semibold">Member Since</span>
                  <span id="summaryMemberSince" class="font-black text-slate-700 dark:text-slate-200">...</span>
                </div>
              </div>
            </div>

          </div>

          <!-- Right Side: Editable Details & Form Actions (xl:col-span-8) -->
          <div class="xl:col-span-8 space-y-5">
            <div class="bg-white dark:bg-slate-900 border border-slate-200/80 dark:border-slate-800 rounded-none shadow-xs overflow-hidden">
              <div class="bg-slate-50 dark:bg-slate-800/50 border-b border-slate-200/60 dark:border-slate-800 px-6 py-4 flex items-center justify-between">
                <div class="flex items-center gap-2.5">
                  <div class="h-7 w-7 rounded-none bg-brand-light dark:bg-slate-800 border border-brand-border dark:border-slate-700 flex items-center justify-center">
                    <i class="fa-solid fa-user text-brand-dark dark:text-cyan-400 text-xs"></i>
                  </div>
                  <div>
                    <span class="text-xs font-black text-slate-700 dark:text-slate-200 block leading-none tracking-tight">Profile Details</span>
                    <span class="text-[9px] text-slate-400 dark:text-slate-500 font-semibold uppercase tracking-wider">Identity &amp; Contact Information</span>
                  </div>
                </div>
                <span class="text-[9px] font-black uppercase tracking-wider text-slate-400 dark:text-slate-400 bg-slate-100 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 px-2.5 py-1 rounded-none">Profile Details</span>
              </div>

              <div class="p-6 space-y-6">

                <!-- Full Name (Read-Only) -->
                <div class="space-y-2">
                  <div class="flex items-center gap-2">
                    <label for="fieldFullName" class="text-[10px] font-black text-slate-400 uppercase tracking-wider block">Full Name</label>
                    <div class="relative group/tooltip">
                      <span class="cursor-help"><i class="fa-solid fa-lock text-amber-500 text-[10px]"></i></span>
                      <div class="pointer-events-none absolute bottom-full left-1/2 -translate-x-1/2 mb-2 w-72 bg-slate-900 text-white text-[10px] font-semibold leading-relaxed px-3.5 py-2.5 rounded-none border border-slate-700 shadow-xl opacity-0 group-hover/tooltip:opacity-100 transition-opacity duration-200 z-20">
                        <div class="flex items-start gap-2">
                          <i class="fa-solid fa-triangle-exclamation text-amber-400 shrink-0 mt-0.5"></i>
                          <span>Name modifications require <strong class="text-amber-300">Superadmin validation</strong>. Please contact the IT Division for any official name change requests.</span>
                        </div>
                        <div class="absolute top-full left-1/2 -translate-x-1/2 w-0 h-0 border-l-[6px] border-l-transparent border-r-[6px] border-r-transparent border-t-[6px] border-t-slate-900"></div>
                      </div>
                    </div>
                    <span class="text-[9px] font-black text-amber-600 bg-amber-50 dark:bg-amber-950/50 border border-amber-200/80 dark:border-amber-800/60 px-2 py-0.5 rounded-none uppercase tracking-wider">Read-Only</span>
                  </div>
                  <div class="relative">
                    <span class="absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-300 dark:text-slate-600"><i class="fa-solid fa-user-tie text-sm"></i></span>
                    <input type="text" id="fieldFullName" value="" placeholder="Staff Account" readonly disabled
                      class="pl-10 pr-10 py-3 border border-slate-200 dark:border-slate-800 bg-slate-50/70 dark:bg-slate-800/40 rounded-none text-xs w-full font-bold text-slate-400 dark:text-slate-500 cursor-not-allowed select-none">
                    <span class="absolute right-3.5 top-1/2 -translate-y-1/2"><i class="fa-solid fa-lock text-slate-300 dark:text-slate-600 text-xs"></i></span>
                  </div>
                  <p class="text-[10px] text-slate-400 dark:text-slate-500 font-medium leading-relaxed">
                    This field is managed by system administrators. Submit a request to <span class="font-black text-brand-dark dark:text-cyan-400">it-support@caloocan.gov.ph</span> for name corrections.
                  </p>
                </div>

                <div class="border-t border-slate-100 dark:border-slate-800"></div>

                <!-- Contact Number -->
                <div class="space-y-2">
                  <label for="fieldPhone" class="text-[10px] font-black text-slate-400 uppercase tracking-wider block">Contact Number</label>
                  <div class="relative">
                    <span class="absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400"><i class="fa-solid fa-phone text-sm"></i></span>
                    <input type="tel" id="fieldPhone" value="" placeholder="Enter contact number (e.g. +63 9XX XXX XXXX)"
                      class="pl-10 pr-4 py-3 border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 rounded-none text-xs w-full font-semibold text-slate-700 dark:text-slate-200 focus:outline-none focus:ring-2 focus:ring-slate-900/10 dark:focus:ring-slate-400/10 focus:border-slate-900 dark:focus:border-slate-400 transition placeholder:text-slate-300 dark:placeholder:text-slate-600">
                  </div>
                  <p class="text-[10px] text-slate-400 dark:text-slate-500 font-medium">Enter your active mobile number for official system notifications and recovery.</p>
                </div>

                <div class="border-t border-slate-100 dark:border-slate-800"></div>

                <!-- Email Address -->
                <div class="space-y-2">
                  <div class="flex items-center gap-2">
                    <label for="fieldEmail" class="text-[10px] font-black text-slate-400 uppercase tracking-wider block">Email Address</label>
                    <span class="text-[9px] font-black text-blue-600 dark:text-blue-400 bg-blue-50 dark:bg-blue-950/50 border border-blue-200/80 dark:border-blue-800/60 px-2 py-0.5 rounded-none uppercase tracking-wider">Government Email</span>
                  </div>
                  <div class="relative">
                    <span class="absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400"><i class="fa-solid fa-envelope text-sm"></i></span>
                    <input type="email" id="fieldEmail" value="" placeholder="Enter official email (e.g. name@caloocan.gov.ph)"
                      class="pl-10 pr-12 py-3 border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 rounded-none text-xs w-full font-semibold text-slate-700 dark:text-slate-200 focus:outline-none focus:ring-2 focus:ring-slate-900/10 dark:focus:ring-slate-400/10 focus:border-slate-900 dark:focus:border-slate-400 transition placeholder:text-slate-300 dark:placeholder:text-slate-600">
                    <span class="absolute right-3.5 top-1/2 -translate-y-1/2"><i class="fa-solid fa-shield-halved text-blue-400 text-xs"></i></span>
                  </div>
                  <div class="flex items-start gap-2.5 bg-blue-50/70 dark:bg-blue-950/30 border border-blue-100 dark:border-blue-900/50 rounded-none px-3.5 py-2.5">
                    <i class="fa-solid fa-circle-info text-blue-400 text-xs shrink-0 mt-0.5"></i>
                    <p class="text-[10px] text-blue-700 dark:text-blue-300 font-semibold leading-relaxed">
                      Updates to official municipal email addresses will trigger an <strong>activation link</strong> to the new address before applying. Your current address remains active until confirmed.
                    </p>
                  </div>
                </div>

                <div class="border-t border-slate-100 dark:border-slate-800"></div>

                <!-- Job Title / Position -->
                <div class="space-y-2">
                  <label for="fieldPosition" class="text-[10px] font-black text-slate-400 uppercase tracking-wider block">Job Title / Position</label>
                  <div class="relative">
                    <span class="absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400"><i class="fa-solid fa-briefcase text-sm"></i></span>
                    <input type="text" id="fieldPosition" value="" placeholder="Enter official position (e.g. Department Head, IT Officer)"
                      class="pl-10 pr-4 py-3 border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 rounded-none text-xs w-full font-semibold text-slate-700 dark:text-slate-200 focus:outline-none focus:ring-2 focus:ring-slate-900/10 dark:focus:ring-slate-400/10 focus:border-slate-900 dark:focus:border-slate-400 transition placeholder:text-slate-300 dark:placeholder:text-slate-600">
                  </div>
                </div>

              </div>
            </div>

            <!-- Form Action Footer -->
            <div class="bg-white dark:bg-slate-900 border border-slate-200/80 dark:border-slate-800 rounded-none shadow-xs px-6 py-4 flex flex-col sm:flex-row items-center justify-between gap-3">
              <p class="text-[10px] text-slate-400 font-semibold leading-relaxed">
                <i class="fa-solid fa-circle-info text-slate-300 mr-1"></i>
                Changes are saved to your profile upon clicking <strong class="text-slate-600 dark:text-slate-300">Save Settings</strong>. Unsaved inputs will be discarded.
              </p>
              <div class="flex items-center gap-2.5 shrink-0">
                <button type="button" onclick="discardChanges()" class="border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 hover:bg-slate-50 dark:hover:bg-slate-750 text-slate-600 dark:text-slate-300 font-bold px-4 py-2.5 rounded-none text-xs flex items-center gap-2 cursor-pointer transition">
                  <i class="fa-solid fa-rotate-left text-[10px]"></i>
                  Discard Changes
                </button>
                <button type="button" onclick="saveSettings()" id="saveSettingsBtn" class="bg-teal-600 hover:bg-teal-700 text-white font-medium px-5 py-2.5 rounded-none text-xs flex items-center gap-2 cursor-pointer transition shadow-xs focus:outline-none focus:ring-2 focus:ring-teal-500/40">
                  <i class="fa-solid fa-floppy-disk text-[10px]"></i>
                  Save Settings
                </button>
              </div>
            </div>
          </div>

        </div>
      </div>

      <!-- ========================================================================= -->
      <!-- PANE 2: SECURITY (Credentials, Password Update, Active Sessions)          -->
      <!-- ========================================================================= -->
      <div id="tab-security" class="settings-tab-pane hidden space-y-6">

        <!-- Active Session & Security Metrics Cards -->
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
          <!-- Last Active Card -->
          <div class="bg-white dark:bg-slate-900 border border-slate-200/80 dark:border-slate-800 rounded-none p-5 shadow-xs space-y-1.5">
            <div class="flex items-center justify-between">
              <span class="text-[10px] font-black text-slate-400 dark:text-slate-500 uppercase tracking-wider block">Last Active Session</span>
              <i class="fa-solid fa-clock-rotate-left text-brand-medium text-xs"></i>
            </div>
            <div class="text-sm font-extrabold text-slate-800 dark:text-slate-100">
              <span id="profileLastLogin">—</span>
            </div>
            <p class="text-[10px] text-slate-400 dark:text-slate-500 font-medium">Recorded timestamp of last portal authorization</p>
          </div>

          <!-- Total Logins Card -->
          <div class="bg-white dark:bg-slate-900 border border-slate-200/80 dark:border-slate-800 rounded-none p-5 shadow-xs space-y-1.5">
            <div class="flex items-center justify-between">
              <span class="text-[10px] font-black text-slate-400 dark:text-slate-500 uppercase tracking-wider block">Total Logins</span>
              <i class="fa-solid fa-right-to-bracket text-emerald-600 text-xs"></i>
            </div>
            <div class="text-sm font-extrabold text-slate-800 dark:text-slate-100">
              <span id="profileTotalLogins">—</span>
            </div>
            <p class="text-[10px] text-slate-400 dark:text-slate-500 font-medium">Aggregated successful authentication count</p>
          </div>

          <!-- Session Security Card -->
          <div class="bg-white dark:bg-slate-900 border border-slate-200/80 dark:border-slate-800 rounded-none p-5 shadow-xs space-y-1.5">
            <div class="flex items-center justify-between">
              <span class="text-[10px] font-black text-slate-400 dark:text-slate-500 uppercase tracking-wider block">Session Security</span>
              <span class="h-2 w-2 rounded-full bg-emerald-500 animate-pulse"></span>
            </div>
            <div class="text-xs font-black text-emerald-600 dark:text-emerald-400 uppercase tracking-wider">
              Active 2FA Protected
            </div>
            <p class="text-[10px] text-slate-400 dark:text-slate-500 font-medium">Session token encrypted with AES-256 standard</p>
          </div>
        </div>

        <!-- Password Update 2-Column Grid -->
        <div class="grid grid-cols-1 xl:grid-cols-12 gap-6 items-start">
          
          <!-- Left: Policy Context & Tip Cards (xl:col-span-5) -->
          <div class="xl:col-span-5 space-y-5">
            
            <!-- PASSWORD POLICY TIP CARD -->
            <div class="bg-gradient-to-br from-slate-900 via-slate-850 to-slate-950 rounded-none p-6 space-y-4 shadow-md text-white border border-slate-800">
              <div class="flex items-center gap-3">
                <div class="h-9 w-9 rounded-none bg-white/10 border border-white/10 flex items-center justify-center shrink-0">
                  <i class="fa-solid fa-vault text-brand-medium text-sm"></i>
                </div>
                <div>
                  <h3 class="text-xs font-black tracking-tight text-white">Civentral Password Standard</h3>
                  <p class="text-[10px] text-slate-400 font-semibold">Enforced by Municipal Security Directives</p>
                </div>
              </div>

              <p class="text-[11px] text-slate-300 font-medium leading-relaxed">
                Passwords must meet all complexity checks to protect government databases against unauthorized intrusion or privilege escalation.
              </p>

              <div class="grid grid-cols-1 sm:grid-cols-2 gap-2.5 pt-2 border-t border-white/10">
                <div class="flex items-center gap-2 text-[10px] text-slate-300 font-bold">
                  <i class="fa-solid fa-circle-check text-emerald-400 text-[10px]"></i>
                  <span>8+ Characters</span>
                </div>
                <div class="flex items-center gap-2 text-[10px] text-slate-300 font-bold">
                  <i class="fa-solid fa-circle-check text-emerald-400 text-[10px]"></i>
                  <span>Uppercase (A-Z)</span>
                </div>
                <div class="flex items-center gap-2 text-[10px] text-slate-300 font-bold">
                  <i class="fa-solid fa-circle-check text-emerald-400 text-[10px]"></i>
                  <span>Lowercase (a-z)</span>
                </div>
                <div class="flex items-center gap-2 text-[10px] text-slate-300 font-bold">
                  <i class="fa-solid fa-circle-check text-emerald-400 text-[10px]"></i>
                  <span>Number (0-9)</span>
                </div>
                <div class="col-span-1 sm:col-span-2 flex items-center gap-2 text-[10px] text-slate-300 font-bold">
                  <i class="fa-solid fa-circle-check text-emerald-400 text-[10px]"></i>
                  <span>Special Character (!@#$&*)</span>
                </div>
              </div>
            </div>

            <!-- SECURITY WARNING NOTICE -->
            <div class="flex items-start gap-3 bg-amber-50/70 dark:bg-amber-950/30 border border-amber-200/80 dark:border-amber-800/60 rounded-none p-4.5">
              <i class="fa-solid fa-triangle-exclamation text-amber-500 text-sm shrink-0 mt-0.5"></i>
              <div class="space-y-1">
                <p class="text-[10px] font-black text-amber-800 dark:text-amber-300 uppercase tracking-wider">Security Awareness</p>
                <p class="text-[10px] text-amber-800/80 dark:text-amber-300/80 font-medium leading-relaxed">
                  Never disclose your credentials to anyone. If you notice unexpected activity, contact <span class="font-black text-amber-900 dark:text-amber-200">it-support@caloocan.gov.ph</span>.
                </p>
              </div>
            </div>

          </div>

          <!-- Right: Change Password Form Card (xl:col-span-7) -->
          <div class="xl:col-span-7 space-y-6">
            <div class="bg-white dark:bg-slate-900 border border-slate-200/80 dark:border-slate-800 rounded-none shadow-xs overflow-hidden">
              
              <!-- Card Header -->
              <div class="bg-slate-50 dark:bg-slate-800/50 border-b border-slate-200/60 dark:border-slate-800 px-6 py-4 flex items-center justify-between">
                <div class="flex items-center gap-2.5">
                  <div class="h-7 w-7 rounded-none bg-brand-light dark:bg-slate-800 border border-brand-border dark:border-slate-700 flex items-center justify-center">
                    <i class="fa-solid fa-key text-brand-dark dark:text-cyan-400 text-xs"></i>
                  </div>
                  <div>
                    <span class="text-xs font-black text-slate-700 dark:text-slate-200 block leading-none tracking-tight">Credential Security Update</span>
                    <span class="text-[9px] text-slate-400 dark:text-slate-500 font-semibold uppercase tracking-wider">Verify current password and set your new credentials</span>
                  </div>
                </div>
                <span class="text-[9px] font-black uppercase tracking-wider text-slate-400 dark:text-slate-400 bg-slate-100 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 px-2.5 py-1 rounded-none">Encrypted</span>
              </div>

              <!-- Form Body -->
              <div class="p-6 space-y-6">

                <!-- Current Password -->
                <div class="space-y-2">
                  <label for="currentPassword" class="text-[10px] font-black text-slate-400 uppercase tracking-wider block">
                    Current Password
                  </label>
                  <div class="relative">
                    <span class="absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400 pointer-events-none">
                      <i class="fa-solid fa-lock text-sm"></i>
                    </span>
                    <input
                      type="password"
                      id="currentPassword"
                      placeholder="Enter your current account password"
                      autocomplete="current-password"
                      class="pl-10 pr-11 py-3 border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 rounded-none text-xs w-full font-semibold text-slate-700 dark:text-slate-200 focus:outline-none focus:ring-2 focus:ring-slate-900/10 dark:focus:ring-slate-400/10 focus:border-slate-900 dark:focus:border-slate-400 transition placeholder:text-slate-300 dark:placeholder:text-slate-600 tracking-widest"
                    >
                    <button
                      type="button"
                      onclick="toggleVisibility('currentPassword', 'eyeCurrentPassword')"
                      class="absolute right-3.5 top-1/2 -translate-y-1/2 text-slate-400 hover:text-slate-700 dark:hover:text-slate-200 transition cursor-pointer focus:outline-none"
                      tabindex="-1">
                      <i id="eyeCurrentPassword" class="fa-solid fa-eye text-sm"></i>
                    </button>
                  </div>
                </div>

                <div class="border-t border-slate-100 dark:border-slate-800"></div>

                <!-- New Password -->
                <div class="space-y-2">
                  <label for="newPassword" class="text-[10px] font-black text-slate-400 uppercase tracking-wider block">
                    New Password
                  </label>
                  <div class="relative">
                    <span class="absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400 pointer-events-none">
                      <i class="fa-solid fa-key text-sm"></i>
                    </span>
                    <input
                      type="password"
                      id="newPassword"
                      placeholder="Create a strong new password"
                      autocomplete="new-password"
                      oninput="checkPasswordStrength()"
                      class="pl-10 pr-11 py-3 border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 rounded-none text-xs w-full font-semibold text-slate-700 dark:text-slate-200 focus:outline-none focus:ring-2 focus:ring-slate-900/10 dark:focus:ring-slate-400/10 focus:border-slate-900 dark:focus:border-slate-400 transition placeholder:text-slate-300 dark:placeholder:text-slate-600 tracking-widest"
                    >
                    <button
                      type="button"
                      onclick="toggleVisibility('newPassword', 'eyeNewPassword')"
                      class="absolute right-3.5 top-1/2 -translate-y-1/2 text-slate-400 hover:text-slate-700 dark:hover:text-slate-200 transition cursor-pointer focus:outline-none"
                      tabindex="-1">
                      <i id="eyeNewPassword" class="fa-solid fa-eye text-sm"></i>
                    </button>
                  </div>

                  <!-- Password Strength Progress Bar -->
                  <div class="pt-1 space-y-1.5">
                    <div class="flex gap-1">
                      <div id="strengthBar1" class="h-1 flex-1 rounded-full bg-slate-100 dark:bg-slate-800 transition-all duration-300"></div>
                      <div id="strengthBar2" class="h-1 flex-1 rounded-full bg-slate-100 dark:bg-slate-800 transition-all duration-300"></div>
                      <div id="strengthBar3" class="h-1 flex-1 rounded-full bg-slate-100 dark:bg-slate-800 transition-all duration-300"></div>
                      <div id="strengthBar4" class="h-1 flex-1 rounded-full bg-slate-100 dark:bg-slate-800 transition-all duration-300"></div>
                      <div id="strengthBar5" class="h-1 flex-1 rounded-full bg-slate-100 dark:bg-slate-800 transition-all duration-300"></div>
                    </div>
                    <p id="strengthLabel" class="text-[10px] font-bold text-slate-300 dark:text-slate-600 leading-none tracking-wide">Enter a password to evaluate strength</p>
                  </div>

                  <!-- Live Checklist -->
                  <div class="pt-3 bg-slate-50/70 dark:bg-slate-800/40 border border-slate-150 dark:border-slate-800 rounded-none p-4 space-y-2">
                    <p class="text-[9px] font-black uppercase tracking-widest text-slate-400 mb-3">Live Validation Checks</p>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-2.5">
                      <div id="rule-length" class="flex items-center gap-2 text-[10px] font-semibold text-slate-400 transition-all duration-200">
                        <span id="icon-length" class="h-4 w-4 rounded-full bg-slate-200 dark:bg-slate-700 flex items-center justify-center shrink-0 transition-all duration-200">
                          <i class="fa-solid fa-minus text-[7px] text-slate-400"></i>
                        </span>
                        At least 8 characters
                      </div>
                      <div id="rule-upper" class="flex items-center gap-2 text-[10px] font-semibold text-slate-400 transition-all duration-200">
                        <span id="icon-upper" class="h-4 w-4 rounded-full bg-slate-200 dark:bg-slate-700 flex items-center justify-center shrink-0 transition-all duration-200">
                          <i class="fa-solid fa-minus text-[7px] text-slate-400"></i>
                        </span>
                        One uppercase letter (A-Z)
                      </div>
                      <div id="rule-lower" class="flex items-center gap-2 text-[10px] font-semibold text-slate-400 transition-all duration-200">
                        <span id="icon-lower" class="h-4 w-4 rounded-full bg-slate-200 dark:bg-slate-700 flex items-center justify-center shrink-0 transition-all duration-200">
                          <i class="fa-solid fa-minus text-[7px] text-slate-400"></i>
                        </span>
                        One lowercase letter (a-z)
                      </div>
                      <div id="rule-number" class="flex items-center gap-2 text-[10px] font-semibold text-slate-400 transition-all duration-200">
                        <span id="icon-number" class="h-4 w-4 rounded-full bg-slate-200 dark:bg-slate-700 flex items-center justify-center shrink-0 transition-all duration-200">
                          <i class="fa-solid fa-minus text-[7px] text-slate-400"></i>
                        </span>
                        One number (0-9)
                      </div>
                      <div id="rule-special" class="flex items-center gap-2 text-[10px] font-semibold text-slate-400 transition-all duration-200 sm:col-span-2">
                        <span id="icon-special" class="h-4 w-4 rounded-full bg-slate-200 dark:bg-slate-700 flex items-center justify-center shrink-0 transition-all duration-200">
                          <i class="fa-solid fa-minus text-[7px] text-slate-400"></i>
                        </span>
                        One special character (!@#$&*)
                      </div>
                    </div>
                  </div>
                </div>

                <div class="border-t border-slate-100 dark:border-slate-800"></div>

                <!-- Confirm New Password -->
                <div class="space-y-2">
                  <label for="confirmPassword" class="text-[10px] font-black text-slate-400 uppercase tracking-wider block">
                    Confirm New Password
                  </label>
                  <div class="relative">
                    <span class="absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400 pointer-events-none">
                      <i class="fa-solid fa-check-double text-sm"></i>
                    </span>
                    <input
                      type="password"
                      id="confirmPassword"
                      placeholder="Re-enter your new password"
                      autocomplete="new-password"
                      oninput="checkConfirmMatch()"
                      class="pl-10 pr-11 py-3 border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 rounded-none text-xs w-full font-semibold text-slate-700 dark:text-slate-200 focus:outline-none focus:ring-2 focus:ring-slate-900/10 dark:focus:ring-slate-400/10 focus:border-slate-900 dark:focus:border-slate-400 transition placeholder:text-slate-300 dark:placeholder:text-slate-600 tracking-widest"
                    >
                    <button
                      type="button"
                      onclick="toggleVisibility('confirmPassword', 'eyeConfirmPassword')"
                      class="absolute right-3.5 top-1/2 -translate-y-1/2 text-slate-400 hover:text-slate-700 dark:hover:text-slate-200 transition cursor-pointer focus:outline-none"
                      tabindex="-1">
                      <i id="eyeConfirmPassword" class="fa-solid fa-eye text-sm"></i>
                    </button>
                  </div>
                  <!-- Match Feedback -->
                  <div id="matchFeedback" class="hidden flex items-center gap-1.5 text-[10px] font-bold">
                    <i id="matchFeedbackIcon" class="fa-solid fa-xmark text-red-500"></i>
                    <span id="matchFeedbackText" class="text-red-500">Passwords do not match</span>
                  </div>
                </div>

              </div>

              <!-- Action Footer -->
              <div class="bg-slate-50 dark:bg-slate-800/50 border-t border-slate-200/60 dark:border-slate-800 px-6 py-4 flex flex-col sm:flex-row items-center justify-between gap-3">
                <p class="text-[10px] text-slate-400 font-semibold leading-relaxed">
                  <i class="fa-solid fa-circle-info text-slate-300 mr-1"></i>
                  Changes take effect immediately upon updating.
                </p>
                <div class="flex items-center gap-2.5 shrink-0">
                  <button
                    type="button"
                    onclick="savePassword()"
                    id="savePasswordBtn"
                    class="bg-teal-600 hover:bg-teal-700 text-white font-medium px-5 py-2.5 rounded-none text-xs flex items-center gap-2 cursor-pointer transition shadow-xs focus:outline-none focus:ring-2 focus:ring-teal-500/40">
                    <i class="fa-solid fa-floppy-disk text-[10px]"></i>
                    Update Password
                  </button>
                </div>
              </div>

            </div>
          </div>

        </div>

      </div>

      <!-- ========================================================================= -->
      <!-- PANE 3: PREFERENCES (System Theme & Personalization)                      -->
      <!-- ========================================================================= -->
      <div id="tab-preferences" class="settings-tab-pane hidden space-y-6">

        <!-- APPEARANCE & SYSTEM THEME CARD -->
        <div class="bg-white dark:bg-slate-900 border border-slate-200/80 dark:border-slate-800 rounded-none shadow-xs overflow-hidden">
          <div class="bg-slate-50 dark:bg-slate-800/50 border-b border-slate-200/60 dark:border-slate-800 px-6 py-4 flex items-center justify-between">
            <div class="flex items-center gap-2.5">
              <div class="h-7 w-7 rounded-none bg-brand-light dark:bg-slate-800 border border-brand-border dark:border-slate-700 flex items-center justify-center">
                <i class="fa-solid fa-palette text-brand-dark dark:text-cyan-400 text-xs"></i>
              </div>
              <div>
                <span class="text-xs font-black text-slate-700 dark:text-slate-200 block leading-none tracking-tight">System Theme &amp; Appearance</span>
                <span class="text-[9px] text-slate-400 dark:text-slate-500 font-semibold uppercase tracking-wider">Choose your preferred portal visual display mode</span>
              </div>
            </div>
            <span class="text-[9px] font-black uppercase tracking-wider text-slate-400 dark:text-slate-400 bg-slate-100 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 px-2.5 py-1 rounded-none">Personalization</span>
          </div>

          <div class="p-6">
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
              <!-- Light Mode Choice -->
              <div id="themeCardLight" onclick="selectSystemTheme('light')" class="border-2 border-brand-medium bg-brand-light/30 rounded-none p-4 flex items-start gap-3.5 cursor-pointer transition select-none shadow-xs">
                <div class="h-10 w-10 rounded-none bg-amber-100 dark:bg-amber-950/60 text-amber-600 dark:text-amber-400 flex items-center justify-center shrink-0 shadow-xs border border-amber-200/80 dark:border-amber-700/50">
                  <i class="fa-solid fa-sun text-lg"></i>
                </div>
                <div class="flex-1 space-y-1">
                  <div class="flex items-center justify-between">
                    <span class="text-xs font-black text-slate-900 dark:text-white">Light Mode (Default)</span>
                    <span id="themeBadgeLight" class="text-[9px] font-black text-brand-dark bg-brand-light px-2 py-0.5 rounded-none uppercase border border-brand-border">Active</span>
                  </div>
                  <p class="text-[10px] text-slate-500 dark:text-slate-400 font-medium leading-relaxed">Clean municipal light theme with slate and blue color accents.</p>
                </div>
              </div>

              <!-- Dark Mode Choice -->
              <div id="themeCardDark" onclick="selectSystemTheme('dark')" class="border-2 border-slate-200 dark:border-slate-800 bg-slate-900 text-white rounded-none p-4 flex items-start gap-3.5 cursor-pointer transition select-none hover:border-slate-700">
                <div class="h-10 w-10 rounded-none bg-indigo-950/80 text-indigo-400 flex items-center justify-center shrink-0 shadow-xs border border-indigo-700/50">
                  <i class="fa-solid fa-moon text-lg"></i>
                </div>
                <div class="flex-1 space-y-1">
                  <div class="flex items-center justify-between">
                    <span class="text-xs font-black text-white">Dark Mode</span>
                    <span id="themeBadgeDark" class="hidden text-[9px] font-black text-indigo-300 bg-indigo-950/80 px-2 py-0.5 rounded-none uppercase border border-indigo-700/50">Active</span>
                  </div>
                  <p class="text-[10px] text-slate-400 font-medium leading-relaxed">Sleek dark theme reducing eye strain during low-light sessions.</p>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- PORTAL DIRECTIVES / LOCALIZATION CARD -->
        <div class="bg-white dark:bg-slate-900 border border-slate-200/80 dark:border-slate-800 rounded-none shadow-xs overflow-hidden">
          <div class="bg-slate-50 dark:bg-slate-800/50 border-b border-slate-200/60 dark:border-slate-800 px-6 py-4 flex items-center justify-between">
            <div class="flex items-center gap-2.5">
              <div class="h-7 w-7 rounded-none bg-brand-light dark:bg-slate-800 border border-brand-border dark:border-slate-700 flex items-center justify-center">
                <i class="fa-solid fa-sliders text-brand-dark dark:text-cyan-400 text-xs"></i>
              </div>
              <div>
                <span class="text-xs font-black text-slate-700 dark:text-slate-200 block leading-none tracking-tight">Portal Configuration &amp; Directives</span>
                <span class="text-[9px] text-slate-400 dark:text-slate-500 font-semibold uppercase tracking-wider">Regional standards &amp; session directives</span>
              </div>
            </div>
            <span class="text-[9px] font-black uppercase tracking-wider text-slate-400 dark:text-slate-400 bg-slate-100 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 px-2.5 py-1 rounded-none">System</span>
          </div>

          <div class="p-6 space-y-4">
            <div class="flex items-center justify-between text-xs py-2">
              <div class="space-y-0.5">
                <p class="font-bold text-slate-800 dark:text-slate-200">Regional Standard &amp; Language</p>
                <p class="text-[10px] text-slate-400 font-medium">Official municipal language localization</p>
              </div>
              <span class="text-xs font-semibold text-slate-700 dark:text-slate-300 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 px-3 py-1.5 rounded-none font-mono">
                en-PH (English - Philippines)
              </span>
            </div>

            <div class="border-t border-slate-100 dark:border-slate-800"></div>

            <div class="flex items-center justify-between text-xs py-2">
              <div class="space-y-0.5">
                <p class="font-bold text-slate-800 dark:text-slate-200">Inactivity Timeout Policy</p>
                <p class="text-[10px] text-slate-400 font-medium">Enforced session security for administrative access</p>
              </div>
              <span class="text-xs font-semibold text-emerald-700 dark:text-emerald-400 bg-emerald-50 dark:bg-emerald-950/40 border border-emerald-200/80 dark:border-emerald-800/60 px-3 py-1.5 rounded-none">
                5 Minutes (Strict Security Directive)
              </span>
            </div>

            <div class="border-t border-slate-100 dark:border-slate-800"></div>

            <div class="flex items-center justify-between text-xs py-2">
              <div class="space-y-0.5">
                <p class="font-bold text-slate-800 dark:text-slate-200">System Platform Version</p>
                <p class="text-[10px] text-slate-400 font-medium">Civentral Superadmin Portal Release</p>
              </div>
              <span class="text-xs font-bold text-slate-600 dark:text-slate-400 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 px-3 py-1.5 rounded-none font-mono">
                Civentral Portal v2.6.4
              </span>
            </div>
          </div>
        </div>

      </div>

    </div>

  </div>

</main>

<script>
// Fast-execution tab switcher for immediate click response
function switchSettingsTab(tabName) {
  const tabs = ['account', 'security', 'preferences'];
  if (!tabs.includes(tabName)) tabName = 'account';

  tabs.forEach(tab => {
    const pane = document.getElementById('tab-' + tab);
    const navBtn = document.getElementById('navTab' + tab.charAt(0).toUpperCase() + tab.slice(1));

    if (pane) {
      if (tab === tabName) {
        pane.classList.remove('hidden');
      } else {
        pane.classList.add('hidden');
      }
    }

    if (navBtn) {
      if (tab === tabName) {
        navBtn.className = 'settings-nav-item w-full text-left px-4 py-3 flex items-start gap-3 transition cursor-pointer rounded-none border-l-2 bg-teal-50 dark:bg-teal-950/40 text-teal-700 dark:text-teal-400 border-l-teal-600 dark:border-l-teal-400 font-bold';
      } else {
        navBtn.className = 'settings-nav-item w-full text-left px-4 py-3 flex items-start gap-3 transition cursor-pointer rounded-none border-l-2 border-l-transparent text-slate-600 dark:text-slate-400 hover:bg-slate-50 dark:hover:bg-slate-800/60 hover:text-slate-900 dark:hover:text-white';
      }
    }
  });

  try {
    const url = new URL(window.location);
    url.searchParams.set('tab', tabName);
    window.history.replaceState({}, '', url);
  } catch (e) {}
}

(function() {
  const params = new URLSearchParams(window.location.search);
  const activeTab = params.get('tab') || (window.location.hash ? window.location.hash.replace('#', '') : 'account');
  switchSettingsTab(activeTab);

  window.addEventListener('popstate', function() {
    const p = new URLSearchParams(window.location.search);
    switchSettingsTab(p.get('tab') || 'account');
  });
})();
</script>

<?php include '../../includes/footer.php'; ?>
