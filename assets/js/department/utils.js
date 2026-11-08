// DOM Elements
const deptsTbody = document.getElementById('deptsTableBody');
const searchInput = document.getElementById('deptSearchInput');

// Toast Popup & Semantic Title Derivation
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

// Export CSV
function exportDeptsCsv() {
  const query = searchInput.value.toLowerCase().trim();

  let csvContent = "data:text/csv;charset=utf-8,";
  csvContent += "Dept Code,Department Name,Department Administrator,Status,Created At\r\n";

  const filtered = departmentsData.filter(dept => {
    const adminObj = dept.users || null;
    const adminName = adminObj ? `${adminObj.first_name} ${adminObj.last_name}`.toLowerCase() : '';
    return dept.department_name.toLowerCase().includes(query) ||
           dept.department_code.toLowerCase().includes(query) ||
           adminName.includes(query);
  });

  filtered.forEach(dept => {
    const adminObj = dept.users || null;
    const adminName = adminObj ? `${adminObj.first_name} ${adminObj.last_name}` : 'Unassigned';
    const createdAt = dept.created_at ? dept.created_at.replace('T', ' ').substring(0, 19) : '';
    csvContent += `"${dept.department_code}","${dept.department_name}","${adminName}","${dept.status}","${createdAt}"\r\n`;
  });

  const encodedUri = encodeURI(csvContent);
  const link = document.createElement("a");
  link.setAttribute("href", encodedUri);
  link.setAttribute("download", `Civentral_Department_Directory_${new Date().toISOString().split('T')[0]}.csv`);
  document.body.appendChild(link);
  link.click();
  document.body.removeChild(link);

  showToast(`Successfully exported ${filtered.length} department records.`);
}
