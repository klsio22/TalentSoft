/**
 * Employee Status Toggle Management
 * Handles visual toggle between Active/Inactive status for employee forms
 */

/**
 * Initialize employee status toggle functionality
 */
function initializeEmployeeStatusToggle() {
  const statusToggle = document.getElementById('statusToggle');
  const statusBadge = document.getElementById('statusBadge');
  const statusBadgeText = document.getElementById('statusBadgeText');
  const statusBadgeIcon = document.getElementById('statusBadgeIcon');

  const editStatusToggle = document.getElementById('status');
  const editStatusText = document.getElementById('status-text');
  const editStatusLabel = document.getElementById('status-label');

  /**
   * Update status badge appearance for create form
   */
  function updateStatusBadge(isActive) {
    if (!statusBadge || !statusBadgeText || !statusBadgeIcon) return;

    if (isActive) {
      statusBadge.className =
        'inline-flex items-center px-3 py-1 rounded-full text-sm font-semibold bg-gradient-to-r from-green-100 to-green-200 text-green-800 border border-green-300';
      statusBadgeText.textContent = 'Ativo';
      statusBadgeIcon.className = 'fas fa-check-circle mr-1.5 text-green-600';
    } else {
      statusBadge.className =
        'inline-flex items-center px-3 py-1 rounded-full text-sm font-semibold bg-gradient-to-r from-gray-100 to-gray-200 text-gray-800 border border-gray-300';
      statusBadgeText.textContent = 'Inativo';
      statusBadgeIcon.className = 'fas fa-times-circle mr-1.5 text-gray-600';
    }
  }

  /**
   * Update status text appearance for edit form
   */
  function updateEditStatusText(isActive) {
    if (!editStatusText || !editStatusLabel) return;

    if (isActive) {
      editStatusText.className =
        'inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold bg-green-100 text-green-800';
      editStatusLabel.textContent = 'Ativo';
    } else {
      editStatusText.className =
        'inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold bg-red-100 text-red-800';
      editStatusLabel.textContent = 'Inativo';
    }
  }

  if (statusToggle) {
    statusToggle.addEventListener('change', function () {
      updateStatusBadge(this.checked);
    });
    updateStatusBadge(statusToggle.checked);
  }

  if (editStatusToggle) {
    editStatusToggle.addEventListener('change', function () {
      updateEditStatusText(this.checked);
    });
    updateEditStatusText(editStatusToggle.checked);
  }
}

/**
 * Initialize the script when DOM content is fully loaded
 */
document.addEventListener('DOMContentLoaded', function () {
  initializeEmployeeStatusToggle();
});
