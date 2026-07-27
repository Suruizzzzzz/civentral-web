<?php include '../includes/header.php'; ?>

<?php include '../includes/sidebar.php'; ?>

    <main class="flex-1 p-6 md:p-8 max-w-7xl mx-auto space-y-8 overflow-y-auto">
      
      <div class="bg-gradient-to-r from-brand-dark to-slate-900 text-white rounded-2xl p-6 shadow-md border border-brand-dark/40 space-y-2">
        <h1 class="text-xl sm:text-2xl font-black tracking-tight">Welcome, Superadmin!</h1>
        <p class="text-xs text-brand-light/80 max-w-2xl leading-relaxed">
          Operational infrastructure management console running. Select a module branch from the central network gateway below to coordinate data entries across district subfolders.
        </p>
      </div>

      <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4">
        
        <a href="modules/citizen_info/index.php" class="bg-white border border-slate-200 hover:border-brand-medium rounded-xl p-5 flex flex-col justify-between space-y-6 shadow-xs hover:shadow-md group transition">
          <div class="space-y-3">
            <div class="h-9 w-9 rounded-lg bg-brand-light border border-brand-border flex items-center justify-center text-brand-dark transition group-hover:bg-brand-medium group-hover:text-white">
              <i class="fa-solid fa-users text-sm"></i>
            </div>
            <h4 class="font-extrabold text-slate-900 text-sm tracking-tight leading-snug">Citizen Information & Engagement</h4>
          </div>
          <div class="flex items-center justify-between pt-2 border-t border-slate-100 text-[10px] font-bold uppercase tracking-wider text-brand-dark">
            <span>Access Department</span><i class="fa-solid fa-arrow-right text-xs transition transform group-hover:translate-x-1"></i>
          </div>
        </a>

        <a href="modules/permits/index.php" class="bg-white border border-slate-200 hover:border-brand-medium rounded-xl p-5 flex flex-col justify-between space-y-6 shadow-xs hover:shadow-md group transition">
          <div class="space-y-3">
            <div class="h-9 w-9 rounded-lg bg-brand-light border border-brand-border flex items-center justify-center text-brand-dark transition group-hover:bg-brand-medium group-hover:text-white">
              <i class="fa-solid fa-file-signature text-sm"></i>
            </div>
            <h4 class="font-extrabold text-slate-900 text-sm tracking-tight leading-snug">Permits & Licensing Management</h4>
          </div>
          <div class="flex items-center justify-between pt-2 border-t border-slate-100 text-[10px] font-bold uppercase tracking-wider text-brand-dark">
            <span>Access Department</span><i class="fa-solid fa-arrow-right text-xs transition transform group-hover:translate-x-1"></i>
          </div>
        </a>

        <a href="modules/social_services/index.php" class="bg-white border border-slate-200 hover:border-brand-medium rounded-xl p-5 flex flex-col justify-between space-y-6 shadow-xs hover:shadow-md group transition">
          <div class="space-y-3">
            <div class="h-9 w-9 rounded-lg bg-brand-light border border-brand-border flex items-center justify-center text-brand-dark transition group-hover:bg-brand-medium group-hover:text-white">
              <i class="fa-solid fa-hand-holding-heart text-sm"></i>
            </div>
            <h4 class="font-extrabold text-slate-900 text-sm tracking-tight leading-snug">Social Services Management</h4>
          </div>
          <div class="flex items-center justify-between pt-2 border-t border-slate-100 text-[10px] font-bold uppercase tracking-wider text-brand-dark">
            <span>Access Department</span><i class="fa-solid fa-arrow-right text-xs transition transform group-hover:translate-x-1"></i>
          </div>
        </a>

        <a href="modules/health/index.php" class="bg-white border border-slate-200 hover:border-brand-medium rounded-xl p-5 flex flex-col justify-between space-y-6 shadow-xs hover:shadow-md group transition">
          <div class="space-y-3">
            <div class="h-9 w-9 rounded-lg bg-brand-light border border-brand-border flex items-center justify-center text-brand-dark transition group-hover:bg-brand-medium group-hover:text-white">
              <i class="fa-solid fa-heart-pulse text-sm"></i>
            </div>
            <h4 class="font-extrabold text-slate-900 text-sm tracking-tight leading-snug">Health & Sanitation Management</h4>
          </div>
          <div class="flex items-center justify-between pt-2 border-t border-slate-100 text-[10px] font-bold uppercase tracking-wider text-brand-dark">
            <span>Access Department</span><i class="fa-solid fa-arrow-right text-xs transition transform group-hover:translate-x-1"></i>
          </div>
        </a>

        <a href="modules/education/index.php" class="bg-white border border-slate-200 hover:border-brand-medium rounded-xl p-5 flex flex-col justify-between space-y-6 shadow-xs hover:shadow-md group transition">
          <div class="space-y-3">
            <div class="h-9 w-9 rounded-lg bg-brand-light border border-brand-border flex items-center justify-center text-brand-dark transition group-hover:bg-brand-medium group-hover:text-white">
              <i class="fa-solid fa-graduation-cap text-sm"></i>
            </div>
            <h4 class="font-extrabold text-slate-900 text-sm tracking-tight leading-snug">Education & Scholarship Management</h4>
          </div>
          <div class="flex items-center justify-between pt-2 border-t border-slate-100 text-[10px] font-bold uppercase tracking-wider text-brand-dark">
            <span>Access Department</span><i class="fa-solid fa-arrow-right text-xs transition transform group-hover:translate-x-1"></i>
          </div>
        </a>

        <a href="modules/drrm/index.php" class="bg-white border border-slate-200 hover:border-brand-medium rounded-xl p-5 flex flex-col justify-between space-y-6 shadow-xs hover:shadow-md group transition">
          <div class="space-y-3">
            <div class="h-9 w-9 rounded-lg bg-brand-light border border-brand-border flex items-center justify-center text-brand-dark transition group-hover:bg-brand-medium group-hover:text-white">
              <i class="fa-solid fa-triangle-exclamation text-sm"></i>
            </div>
            <h4 class="font-extrabold text-slate-900 text-sm tracking-tight leading-snug">Disaster Risk Reduction (DRRM)</h4>
          </div>
          <div class="flex items-center justify-between pt-2 border-t border-slate-100 text-[10px] font-bold uppercase tracking-wider text-brand-dark">
            <span>Access Department</span><i class="fa-solid fa-arrow-right text-xs transition transform group-hover:translate-x-1"></i>
          </div>
        </a>

        <a href="modules/urban_planning/index.php" class="bg-white border border-slate-200 hover:border-brand-medium rounded-xl p-5 flex flex-col justify-between space-y-6 shadow-xs hover:shadow-md group transition">
          <div class="space-y-3">
            <div class="h-9 w-9 rounded-lg bg-brand-light border border-brand-border flex items-center justify-center text-brand-dark transition group-hover:bg-brand-medium group-hover:text-white">
              <i class="fa-solid fa-map-location-dot text-sm"></i>
            </div>
            <h4 class="font-extrabold text-slate-900 text-sm tracking-tight leading-snug">Urban Planning, Zoning & Housing</h4>
          </div>
          <div class="flex items-center justify-between pt-2 border-t border-slate-100 text-[10px] font-bold uppercase tracking-wider text-brand-dark">
            <span>Access Department</span><i class="fa-solid fa-arrow-right text-xs transition transform group-hover:translate-x-1"></i>
          </div>
        </a>

        <a href="modules/treasury/index.php" class="bg-white border border-slate-200 hover:border-brand-medium rounded-xl p-5 flex flex-col justify-between space-y-6 shadow-xs hover:shadow-md group transition">
          <div class="space-y-3">
            <div class="h-9 w-9 rounded-lg bg-brand-light border border-brand-border flex items-center justify-center text-brand-dark transition group-hover:bg-brand-medium group-hover:text-white">
              <i class="fa-solid fa-cash-register text-sm"></i>
            </div>
            <h4 class="font-extrabold text-slate-900 text-sm tracking-tight leading-snug">Revenue Collection & Treasury</h4>
          </div>
          <div class="flex items-center justify-between pt-2 border-t border-slate-100 text-[10px] font-bold uppercase tracking-wider text-brand-dark">
            <span>Access Department</span><i class="fa-solid fa-arrow-right text-xs transition transform group-hover:translate-x-1"></i>
          </div>
        </a>

        <a href="modules/transport/index.php" class="bg-white border border-slate-200 hover:border-brand-medium rounded-xl p-5 flex flex-col justify-between space-y-6 shadow-xs hover:shadow-md group transition">
          <div class="space-y-3">
            <div class="h-9 w-9 rounded-lg bg-brand-light border border-brand-border flex items-center justify-center text-brand-dark transition group-hover:bg-brand-medium group-hover:text-white">
              <i class="fa-solid fa-bus text-sm"></i>
            </div>
            <h4 class="font-extrabold text-slate-900 text-sm tracking-tight leading-snug">Transport & Mobility Management</h4>
          </div>
          <div class="flex items-center justify-between pt-2 border-t border-slate-100 text-[10px] font-bold uppercase tracking-wider text-brand-dark">
            <span>Access Department</span><i class="fa-solid fa-arrow-right text-xs transition transform group-hover:translate-x-1"></i>
          </div>
        </a>

        <a href="modules/assets/index.php" class="bg-white border border-slate-200 hover:border-brand-medium rounded-xl p-5 flex flex-col justify-between space-y-6 shadow-xs hover:shadow-md group transition">
          <div class="space-y-3">
            <div class="h-9 w-9 rounded-lg bg-brand-light border border-brand-border flex items-center justify-center text-brand-dark transition group-hover:bg-brand-medium group-hover:text-white">
              <i class="fa-solid fa-warehouse text-sm"></i>
            </div>
            <h4 class="font-extrabold text-slate-900 text-sm tracking-tight leading-snug">Public Assets & Facilities Management</h4>
          </div>
          <div class="flex items-center justify-between pt-2 border-t border-slate-100 text-[10px] font-bold uppercase tracking-wider text-brand-dark">
            <span>Access Department</span><i class="fa-solid fa-arrow-right text-xs transition transform group-hover:translate-x-1"></i>
          </div>
        </a>

      </div>
    </main>
    
<?php include '../includes/footer.php'; ?>