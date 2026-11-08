// Filter Directory Table
function filterDepts() {
  if (typeof window.resetDeptPage === 'function') {
    window.resetDeptPage();
  }

  const query = searchInput.value.toLowerCase().trim();

  const filtered = departmentsData.filter(dept => {
    const adminObj = dept.users || null;
    const adminName = adminObj ? `${adminObj.first_name} ${adminObj.last_name}`.toLowerCase() : '';
    return dept.department_name.toLowerCase().includes(query) ||
           dept.department_code.toLowerCase().includes(query) ||
           adminName.includes(query);
  });

  renderDepts(filtered);
}
