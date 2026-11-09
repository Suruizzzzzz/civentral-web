// Settings UI
// Responsible for DOM manipulation and updating the user interface for settings

function populateProfileUI() {
  if (!userProfile) return;

  const defaultAvatar = `https://ui-avatars.com/api/?name=${encodeURIComponent(userProfile.full_name || 'User')}&background=EEF5FF&color=176B87&bold=true&size=128&font-size=0.42`;
  
  const avatarImg = document.getElementById('avatarPreview');
  if (avatarImg) {
    let pic = userProfile.profile_picture || 'default-avatar.png';
    if (pic !== 'default-avatar.png' && !pic.startsWith('http') && !pic.startsWith('data:')) {
      const bPath = window.civentralBasePath || '../../';
      pic = bPath + pic.replace(/^\/+/, '');
    } else if (pic === 'default-avatar.png') {
      pic = defaultAvatar;
    }
    avatarImg.src = pic;
  }

  // Sidebar card
  const sName = document.getElementById('sidebarName');
  if (sName) sName.innerText = userProfile.full_name || '';

  const sRole = document.getElementById('sidebarRole');
  if (sRole) sRole.innerText = userProfile.role_name || 'Staff';

  // Account summary
  const sumEmpId = document.getElementById('summaryEmpId');
  if (sumEmpId) sumEmpId.innerText = userProfile.employee_id || '';

  const sumRole = document.getElementById('summaryRole');
  if (sumRole) sumRole.innerText = userProfile.role_name || '';

  const sumDept = document.getElementById('summaryDept');
  if (sumDept) sumDept.innerText = userProfile.department_name || '';

  const sumMemberSince = document.getElementById('summaryMemberSince');
  if (sumMemberSince) sumMemberSince.innerText = userProfile.created_date || userProfile.created_at || '—';

  // Editable input fields
  const fName = document.getElementById('fieldFullName');
  if (fName) fName.value = userProfile.full_name || '';

  const fPhone = document.getElementById('fieldPhone');
  if (fPhone) fPhone.value = userProfile.mobile_number || '';

  const fEmail = document.getElementById('fieldEmail');
  if (fEmail) fEmail.value = userProfile.email || '';

  const fPosition = document.getElementById('fieldPosition');
  if (fPosition) fPosition.value = userProfile.position_name || '';

  // Security metrics
  const pLastLogin = document.getElementById('profileLastLogin');
  if (pLastLogin) pLastLogin.innerText = userProfile.last_login || '—';

  const pTotalLogins = document.getElementById('profileTotalLogins');
  if (pTotalLogins) pTotalLogins.innerText = userProfile.total_logins ? `${userProfile.total_logins} Logins` : '—';
}

function discardChanges() {
  populateProfileUI();
  currentAvatarBase64 = null;
  const avatarInput = document.getElementById('avatarFileInput');
  if (avatarInput) avatarInput.value = '';
  showToast('Changes discarded.', 'info', 'All fields restored to last saved values.');
}

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

window.switchSettingsTab = switchSettingsTab;
