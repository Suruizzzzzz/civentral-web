// Shared Toast Notification for Audit Logs System

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
