/**
 * Employee management scripts
 */

document.addEventListener('DOMContentLoaded', function () {
  initializeEmployeeDeleteButtons();
  initializeDeleteModal();
});

/**
 * Initialize delete button event listeners
 */
function initializeEmployeeDeleteButtons() {
  const deleteButtons = document.querySelectorAll('.delete-btn');
  deleteButtons.forEach((button) => {
    button.addEventListener('click', function () {
      const employeeId = this.getAttribute('data-employee-id');
      const employeeName = this.getAttribute('data-employee-name');
      confirmDelete(employeeId, employeeName);
    });
  });
}

/**
 * Show delete confirmation modal
 * @param {string|number} id - Employee ID
 * @param {string} name - Employee name
 */
function confirmDelete(id, name) {
  const employeeIdInput = document.getElementById('employeeId');
  const employeeNameElement = document.getElementById('employeeName');
  const deleteModal = document.getElementById('deleteModal');

  if (employeeIdInput && employeeNameElement && deleteModal) {
    employeeIdInput.value = id;
    employeeNameElement.textContent = name;

    // Show modal
    deleteModal.classList.remove('hidden');
    document.body.style.overflow = 'hidden';
  }
}

/**
 * Close delete confirmation modal
 */
function closeDeleteModal() {
  const deleteModal = document.getElementById('deleteModal');
  if (deleteModal) {
    deleteModal.classList.add('hidden');
    document.body.style.overflow = 'auto';
  }
}

/**
 * Initialize delete modal event listeners
 */
function initializeDeleteModal() {
  const deleteModal = document.getElementById('deleteModal');

  if (deleteModal) {
    // Close modal on outside click
    deleteModal.addEventListener('click', function (e) {
      if (e.target === this) {
        closeDeleteModal();
      }
    });
  }

  // Close modal on ESC key
  document.addEventListener('keydown', function (e) {
    if (e.key === 'Escape') {
      closeDeleteModal();
    }
  });
}

// Export functions for global use
window.confirmDelete = confirmDelete;
window.closeDeleteModal = closeDeleteModal;
