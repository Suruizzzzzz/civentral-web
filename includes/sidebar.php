    <?php
      $currentPage = basename($_SERVER['PHP_SELF']);

      $usermanagementPages = [
        'user-directory.php',
        'create-account.php',
        'account-status.php'
      ];

      $rolesmanagementPages = [
        'roles-management.php',
        'permissions.php',
        'module-management.php',
        'resource-management.php',
        'resourcemanagement.php',
        'action-management.php',
        'actionmanagement.php',
        'access-control.php'
      ];

      $departmentmanagementPages = [
        'departments.php'
      ];
      $citizenPages = [
        'citizen-directory.php',
        'citizen-account.php'
      ];
      $auditPages = [
        'user-activities.php',
        'login-history.php',
        'data-changes.php'
      ];

      $isSuperAdmin = !empty($headerUser['is_superadmin']) || !empty($headerUser['is_global_access']);
      $userGrantedRes = $headerUser['granted_resources'] ?? [];

      // Dynamic RBAC Permission Checker
      $hasResourceAccess = function($keywords) use ($isSuperAdmin, $userGrantedRes) {
          if ($isSuperAdmin) return true;
          if (empty($userGrantedRes)) return false;
          if (is_string($keywords)) $keywords = [$keywords];
          foreach ($userGrantedRes as $resName) {
              $resLower = strtolower($resName);
              foreach ($keywords as $kw) {
                  if (strpos($resLower, strtolower($kw)) !== false) return true;
              }
          }
          return false;
      };

      // Navigation Active State Class Generators matching Education Satellite Frontend
      $parentNavClass = function(bool $isActive) {
          return $isActive 
              ? 'bg-white dark:bg-slate-800 text-brand-dark dark:text-[#86B6F6] border border-brand-border/80 dark:border-slate-700 font-bold shadow-xs' 
              : 'hover:bg-white dark:hover:bg-slate-800 text-slate-600 dark:text-slate-400 hover:text-brand-dark dark:hover:text-[#86B6F6] border border-transparent font-semibold';
      };

      $parentIconClass = function(bool $isActive) {
          return $isActive ? 'text-brand-dark dark:text-[#86B6F6]' : 'text-slate-400 group-hover:text-brand-medium transition';
      };

      $parentChevronClass = function(bool $isActive) {
          return $isActive ? 'rotate-180 text-brand-dark dark:text-[#86B6F6]' : '';
      };

      $childNavClass = function(bool $isActive) {
          return $isActive
              ? 'text-brand-dark dark:text-white font-bold bg-brand-dark/[0.07] dark:bg-slate-800/90 border-l-2 border-brand-dark dark:border-brand-medium rounded-r-md'
              : 'text-slate-500 dark:text-slate-400 hover:text-brand-dark dark:hover:text-white hover:bg-black/[0.03] dark:hover:bg-white/[0.03] border-l-2 border-transparent rounded-r-md font-medium';
      };

      $childIconClass = function(bool $isActive) {
          return $isActive ? 'text-brand-dark dark:text-brand-medium' : 'opacity-50 group-hover:opacity-75';
      };
    ?>
    
    <aside id="sidebar" class="bg-brand-light text-slate-600 w-72 min-h-[calc(100vh-5rem)] flex flex-col justify-between transition-all duration-300 border-r border-brand-border/60 sticky top-20 h-[calc(100vh-5rem)] z-30 shrink-0 shadow-sm">
      
      <div class="flex flex-col h-full overflow-hidden">
        <nav class="flex-1 px-3 py-4 space-y-1 overflow-y-auto custom-scrollbar">
          
          <div class="sidebar-divider px-1 pb-3 mb-2 border-b">
            <button onclick="toggleSidebar()" class="sidebar-collapse-btn w-full py-2 rounded-xl border flex items-center justify-center focus:outline-none transition cursor-pointer shadow-xs" title="Collapse Menu Panel">
              <i id="toggleArrow" class="fa-solid fa-chevron-left text-xs"></i>
            </button>
          </div>

          <span class="sidebar-text text-[9px] font-bold tracking-widest text-slate-400 uppercase block px-3 mb-2">Main Controls</span>
          
          <a href="<?php echo $basePath ?? '../'; ?>pages/dashboard.php" class="flex items-center space-x-3 px-3 py-2.5 rounded-xl text-xs tracking-wide transition cursor-pointer <?php echo $parentNavClass($currentPage == 'dashboard.php'); ?>">
            <i class="fa-solid fa-table-columns text-sm <?php echo $parentIconClass($currentPage == 'dashboard.php'); ?>"></i>
            <span class="sidebar-text truncate">Dashboard Overview</span>
          </a>

          <?php 
          $canAccessUserMgmt = $isSuperAdmin || $hasResourceAccess(['user directory', 'user account', 'users account', 'account status', 'user', 'account', 'employee']);
          if ($canAccessUserMgmt): 
            $isUserMgmtActive = in_array($currentPage, $usermanagementPages);
          ?>
          <div class="space-y-1">
            <button onclick="toggleDropdown('userDropdown', 'userChevron')" class="dropdown-btn w-full flex items-center justify-between px-3 py-2.5 rounded-xl text-xs tracking-wide transition group cursor-pointer <?php echo $parentNavClass($isUserMgmtActive); ?>">
              <div class="flex items-center space-x-3">
                <i class="fa-solid fa-users-gear text-sm <?php echo $parentIconClass($isUserMgmtActive); ?>"></i>
                <span class="sidebar-text truncate">User Management</span>
              </div>
              <div class="dropdown-right">
                <i id="userChevron" class="fa-solid fa-chevron-down text-[10px] opacity-60 dropdown-chevron transition-transform duration-200 <?php echo $parentChevronClass($isUserMgmtActive); ?>"></i>
              </div>
            </button>
            <div id="userDropdown" class="<?php echo $isUserMgmtActive ? '' : 'hidden'; ?> pl-8 pr-2 space-y-0.5 font-medium sidebar-submenu">
              <a href="<?php echo $basePath ?? '../'; ?>pages/usermanagement/user-directory.php" class="flex items-center space-x-2 px-3 py-2 text-[11px] transition <?php echo $childNavClass($currentPage == 'user-directory.php'); ?>"><i class="fa-solid fa-user-pen text-[10px] <?php echo $childIconClass($currentPage == 'user-directory.php'); ?>"></i> <span>User Directory</span></a>

              <?php if ($isSuperAdmin || $hasResourceAccess(['users account', 'user account', 'create account'])): ?>
              <a href="<?php echo $basePath ?? '../'; ?>pages/usermanagement/create-account.php" class="flex items-center space-x-2 px-3 py-2 text-[11px] transition <?php echo $childNavClass($currentPage == 'create-account.php'); ?>"><i class="fa-solid fa-user-plus text-[10px] <?php echo $childIconClass($currentPage == 'create-account.php'); ?>"></i> <span>Create Staff Accounts</span></a>
              <?php endif; ?>

              <?php if ($isSuperAdmin || $hasResourceAccess(['account status', 'status control', 'status'])): ?>
              <a href="<?php echo $basePath ?? '../'; ?>pages/usermanagement/account-status.php" class="flex items-center space-x-2 px-3 py-2 text-[11px] transition <?php echo $childNavClass($currentPage == 'account-status.php'); ?>"><i class="fa-solid fa-user-check text-[10px] <?php echo $childIconClass($currentPage == 'account-status.php'); ?>"></i> <span>Activate/Deactivate</span></a>
              <?php endif; ?>
            </div>
          </div>
          <?php endif; ?>

          <?php 
          $canAccessRoleMgmt = $isSuperAdmin || $hasResourceAccess(['role', 'permission', 'module', 'resource', 'access control']);
          if ($canAccessRoleMgmt): 
            $isRoleMgmtActive = in_array($currentPage, $rolesmanagementPages);
          ?>
          <div class="space-y-1">
            <button onclick="toggleDropdown('roleDropdown', 'roleChevron')" class="dropdown-btn w-full flex items-center justify-between px-3 py-2.5 rounded-xl text-xs tracking-wide transition group cursor-pointer <?php echo $parentNavClass($isRoleMgmtActive); ?>">
              <div class="flex items-center space-x-3">
                <i class="fa-solid fa-user-shield text-sm <?php echo $parentIconClass($isRoleMgmtActive); ?>"></i>
                <span class="sidebar-text truncate">Role & Permissions</span>
              </div>
              <div class="dropdown-right">
                <i id="roleChevron" class="fa-solid fa-chevron-down text-[10px] opacity-60 dropdown-chevron transition-transform duration-200 <?php echo $parentChevronClass($isRoleMgmtActive); ?>"></i>
              </div>
            </button>
            <div id="roleDropdown" class="<?php echo $isRoleMgmtActive ? '' : 'hidden'; ?> pl-8 pr-2 space-y-0.5 font-medium sidebar-submenu">
              <?php if ($isSuperAdmin || $hasResourceAccess(['roles'])): ?>
              <a href="<?php echo $basePath ?? '../'; ?>pages/rolespermission/roles-management.php" class="flex items-center space-x-2 px-3 py-2 text-[11px] transition <?php echo $childNavClass($currentPage == 'roles-management.php'); ?>"><i class="fa-solid fa-users text-[10px] <?php echo $childIconClass($currentPage == 'roles-management.php'); ?>"></i> <span>Roles</span></a>
              <?php endif; ?>

              <?php if ($isSuperAdmin || $hasResourceAccess(['module management'])): ?>
              <a href="<?php echo $basePath ?? '../'; ?>pages/rolespermission/module-management.php" class="flex items-center space-x-2 px-3 py-2 text-[11px] transition <?php echo $childNavClass($currentPage == 'module-management.php'); ?>"><i class="fa-solid fa-cubes text-[10px] <?php echo $childIconClass($currentPage == 'module-management.php'); ?>"></i> <span>Module Management</span></a>
              <?php endif; ?>

              <?php if ($isSuperAdmin || $hasResourceAccess(['resource management'])): ?>
              <a href="<?php echo $basePath ?? '../'; ?>pages/rolespermission/resource-management.php" class="flex items-center space-x-2 px-3 py-2 text-[11px] transition <?php echo $childNavClass(in_array($currentPage, ['resource-management.php', 'resourcemanagement.php'])); ?>"><i class="fa-solid fa-file-lines text-[10px] <?php echo $childIconClass(in_array($currentPage, ['resource-management.php', 'resourcemanagement.php'])); ?>"></i> <span>Resource Management</span></a>
              <?php endif; ?>

              <?php if ($isSuperAdmin || $hasResourceAccess(['action management'])): ?>
              <a href="<?php echo $basePath ?? '../'; ?>pages/rolespermission/action-management.php" class="flex items-center space-x-2 px-3 py-2 text-[11px] transition <?php echo $childNavClass(in_array($currentPage, ['action-management.php', 'actionmanagement.php'])); ?>"><i class="fa-solid fa-bolt text-[10px] <?php echo $childIconClass(in_array($currentPage, ['action-management.php', 'actionmanagement.php'])); ?>"></i> <span>Action Management</span></a>
              <?php endif; ?>

              <?php if ($isSuperAdmin || $hasResourceAccess(['permission builder'])): ?>
              <a href="<?php echo $basePath ?? '../'; ?>pages/rolespermission/permissions.php" class="flex items-center space-x-2 px-3 py-2 text-[11px] transition <?php echo $childNavClass($currentPage == 'permissions.php'); ?>"><i class="fa-solid fa-key text-[10px] <?php echo $childIconClass($currentPage == 'permissions.php'); ?>"></i> <span>Permission Builder</span></a>
              <?php endif; ?>

              <?php if ($isSuperAdmin || $hasResourceAccess(['role permission matrix'])): ?>
              <a href="<?php echo $basePath ?? '../'; ?>pages/rolespermission/access-control.php" class="flex items-center space-x-2 px-3 py-2 text-[11px] transition <?php echo $childNavClass($currentPage == 'access-control.php'); ?>"><i class="fa-solid fa-shield-halved text-[10px] <?php echo $childIconClass($currentPage == 'access-control.php'); ?>"></i> <span>Role Permission Matrix</span></a>
              <?php endif; ?>
            </div>
          </div>
          <?php endif; ?>

          <?php 
          $canAccessDeptMgmt = $hasResourceAccess(['department', 'position', 'sitemap', 'department management']);
          if ($canAccessDeptMgmt): 
            $isDeptMgmtActive = in_array($currentPage, $departmentmanagementPages);
          ?>
          <div class="space-y-1">
            <button onclick="toggleDropdown('deptDropdown', 'deptChevron')" class="dropdown-btn w-full flex items-center justify-between px-3 py-2.5 rounded-xl text-xs tracking-wide transition group cursor-pointer <?php echo $parentNavClass($isDeptMgmtActive); ?>">
              <div class="flex items-center space-x-3">
                <i class="fa-solid fa-sitemap text-sm <?php echo $parentIconClass($isDeptMgmtActive); ?>"></i>
                <span class="sidebar-text truncate">Department Management</span>
              </div>
              <div class="dropdown-right">
                <i id="deptChevron" class="fa-solid fa-chevron-down text-[10px] opacity-60 dropdown-chevron transition-transform duration-200 <?php echo $parentChevronClass($isDeptMgmtActive); ?>"></i>
              </div>
            </button>
            <div id="deptDropdown" class="<?php echo $isDeptMgmtActive ? '' : 'hidden'; ?> pl-8 pr-2 space-y-0.5 font-medium sidebar-submenu">
              <a href="<?php echo $basePath ?? '../'; ?>pages/department/departments.php" class="flex items-center space-x-2 px-3 py-2 text-[11px] transition <?php echo $childNavClass($currentPage == 'departments.php'); ?>"><i class="fa-solid fa-building text-[10px] <?php echo $childIconClass($currentPage == 'departments.php'); ?>"></i> <span>Departments</span></a>
            </div>
          </div>
          <?php endif; ?>

          <?php 
          $canAccessCitizenMgmt = $hasResourceAccess(['citizen', 'kyc', 'verification']);
          if ($canAccessCitizenMgmt): 
            $isCitizenMgmtActive = in_array($currentPage, $citizenPages);
          ?>
          <div class="space-y-1">
            <button onclick="toggleDropdown('citizenDropdown', 'citizenChevron')" class="dropdown-btn w-full flex items-center justify-between px-3 py-2.5 rounded-xl text-xs tracking-wide transition group cursor-pointer <?php echo $parentNavClass($isCitizenMgmtActive); ?>">
              <div class="flex items-center space-x-3">
                <i class="fa-solid fa-address-book text-sm <?php echo $parentIconClass($isCitizenMgmtActive); ?>"></i>
                <span class="sidebar-text truncate">Citizen Management</span>
              </div>
              <div class="dropdown-right">
                <i id="citizenChevron" class="fa-solid fa-chevron-down text-[10px] opacity-60 dropdown-chevron transition-transform duration-200 <?php echo $parentChevronClass($isCitizenMgmtActive); ?>"></i>
              </div>
            </button>
            <div id="citizenDropdown" class="<?php echo $isCitizenMgmtActive ? '' : 'hidden'; ?> pl-8 pr-2 space-y-0.5 font-medium sidebar-submenu">
              <?php if ($hasResourceAccess('citizen directory')): ?>
              <a href="<?php echo $basePath ?? '../'; ?>pages/citizen/citizen-directory.php" class="flex items-center space-x-2 px-3 py-2 text-[11px] transition <?php echo $childNavClass($currentPage == 'citizen-directory.php'); ?>"><i class="fa-solid fa-id-card text-[10px] <?php echo $childIconClass($currentPage == 'citizen-directory.php'); ?>"></i> <span>Citizen Directory</span></a>
              <?php endif; ?>

              <?php if ($hasResourceAccess(['citizen account', 'kyc', 'verification'])): ?>
              <a href="<?php echo $basePath ?? '../'; ?>pages/citizen/citizen-account.php" class="flex items-center space-x-2 px-3 py-2 text-[11px] transition <?php echo $childNavClass($currentPage == 'citizen-account.php'); ?>"><i class="fa-solid fa-database text-[10px] <?php echo $childIconClass($currentPage == 'citizen-account.php'); ?>"></i> <span>Citizen Account</span></a>
              <?php endif; ?>
            </div>
          </div>
          <?php endif; ?>

          <?php 
          $canAccessAuditLogs = $hasResourceAccess(['audit', 'activity', 'log', 'change', 'history']);
          if ($canAccessAuditLogs): 
            $isAuditLogsActive = in_array($currentPage, $auditPages);
          ?>
          <div class="space-y-1">
            <button onclick="toggleDropdown('auditDropdown', 'auditChevron')" class="dropdown-btn w-full flex items-center justify-between px-3 py-2.5 rounded-xl text-xs tracking-wide transition group cursor-pointer <?php echo $parentNavClass($isAuditLogsActive); ?>">
              <div class="flex items-center space-x-3">
                <i class="fa-solid fa-clock-rotate-left text-sm <?php echo $parentIconClass($isAuditLogsActive); ?>"></i>
                <span class="sidebar-text truncate">Audit Logs System</span>
              </div>
              <div class="dropdown-right">
                <i id="auditChevron" class="fa-solid fa-chevron-down text-[10px] opacity-60 dropdown-chevron transition-transform duration-200 <?php echo $parentChevronClass($isAuditLogsActive); ?>"></i>
              </div>
            </button>
            <div id="auditDropdown" class="<?php echo $isAuditLogsActive ? '' : 'hidden'; ?> pl-8 pr-2 space-y-0.5 font-medium sidebar-submenu">
              <a href="<?php echo $basePath ?? '../'; ?>pages/audit/user-activities.php" class="flex items-center space-x-2 px-3 py-2 text-[11px] transition <?php echo $childNavClass($currentPage == 'user-activities.php'); ?>"><i class="fa-solid fa-chart-line text-[10px] <?php echo $childIconClass($currentPage == 'user-activities.php'); ?>"></i> <span>User Activities</span></a>
              <a href="<?php echo $basePath ?? '../'; ?>pages/audit/login-history.php" class="flex items-center space-x-2 px-3 py-2 text-[11px] transition <?php echo $childNavClass($currentPage == 'login-history.php'); ?>"><i class="fa-solid fa-history text-[10px] <?php echo $childIconClass($currentPage == 'login-history.php'); ?>"></i> <span>Login History</span></a>
              <a href="<?php echo $basePath ?? '../'; ?>pages/audit/data-changes.php" class="flex items-center space-x-2 px-3 py-2 text-[11px] transition <?php echo $childNavClass($currentPage == 'data-changes.php'); ?>"><i class="fa-solid fa-pen-to-square text-[10px] <?php echo $childIconClass($currentPage == 'data-changes.php'); ?>"></i> <span>Data Changes</span></a>
            </div>
          </div>
          <?php endif; ?>
        </nav>
        
        <div class="p-4 border-t shrink-0 sidebar-footer">
          <a href="#" onclick="openLogoutModal(event)" class="sidebar-logout-btn flex items-center space-x-3 px-3 py-2.5 rounded-xl text-xs font-bold tracking-wide transition group cursor-pointer">
            <i class="fa-solid fa-arrow-right-from-bracket text-sm"></i>
            <span class="sidebar-text truncate">Logout</span>
          </a>
        </div>
      </div>
    </aside>

    <!-- Early Sidebar State Restoration (prevents visible layout flash during MPA navigation) -->
    <script>
      (function() {
        try {
          if (localStorage.getItem('civentral_sidebar_collapsed') === 'true') {
            var sidebar = document.getElementById('sidebar');
            if (sidebar) {
              var prevTransition = sidebar.style.transition;
              sidebar.style.transition = 'none';

              var openMenu = sidebar.querySelector('[id$="Dropdown"]:not(.hidden), .sidebar-submenu:not(.hidden)');
              if (openMenu) {
                window.lastOpenDropdownId = openMenu.id;
              }

              sidebar.classList.remove('w-72');
              sidebar.classList.add('w-20');

              var arrow = document.getElementById('toggleArrow');
              if (arrow) {
                arrow.className = 'fa-solid fa-chevron-right text-xs';
              }

              var sideLabels = sidebar.querySelectorAll('.sidebar-text');
              sideLabels.forEach(function(label) {
                label.classList.add('hidden');
              });

              var dropdownRights = sidebar.querySelectorAll('.dropdown-right');
              dropdownRights.forEach(function(right) {
                right.classList.add('hidden');
              });

              var dropdownButtons = sidebar.querySelectorAll('.dropdown-btn');
              dropdownButtons.forEach(function(btn) {
                btn.classList.remove('justify-between');
                btn.classList.add('justify-center');
              });

              var submenus = sidebar.querySelectorAll('[id$="Dropdown"], .sidebar-submenu');
              submenus.forEach(function(menu) {
                menu.classList.add('hidden');
              });

              var collapseBtn = sidebar.querySelector('.sidebar-collapse-btn');
              if (collapseBtn) {
                collapseBtn.title = 'Expand Menu Panel';
              }

              window.isCollapsed = true;

              requestAnimationFrame(function() {
                requestAnimationFrame(function() {
                  if (sidebar) {
                    sidebar.style.transition = prevTransition;
                  }
                });
              });
            }
          }
        } catch (e) {}
      })();
    </script>
