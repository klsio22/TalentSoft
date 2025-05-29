/**
 * TalentSoft Custom Modal System
 * Modern modal dialogs with glass effects that replace native browser alerts
 */

/**
 * Create and display a custom modal dialog
 * @param {string} title - Modal title text
 * @param {string} message - Modal message content (can include HTML)
 * @param {string} type - Modal type: 'warning', 'error', 'success', 'info', 'confirm'
 * @param {object} options - Additional configuration options
 * @returns {Promise} Promise that resolves with user's choice (for confirm modals)
 */
function showCustomModal(title, message, type = 'info', options = {}) {
  const existingModal = document.getElementById('customModal');
  if (existingModal) {
    existingModal.remove();
  }

  const typeConfig = {
    warning: {
      color: '#f59e0b',
      bgColor: 'rgba(245, 158, 11, 0.1)',
      icon: '⚠️',
      buttonText: 'Entendi',
    },
    error: {
      color: '#ef4444',
      bgColor: 'rgba(239, 68, 68, 0.1)',
      icon: '❌',
      buttonText: 'OK',
    },
    success: {
      color: '#10b981',
      bgColor: 'rgba(16, 185, 129, 0.1)',
      icon: '✅',
      buttonText: 'Perfeito',
    },
    info: {
      color: '#3b82f6',
      bgColor: 'rgba(59, 130, 246, 0.1)',
      icon: 'ℹ️',
      buttonText: 'OK',
    },
    confirm: {
      color: '#8b5cf6',
      bgColor: 'rgba(139, 92, 246, 0.1)',
      icon: '❓',
      buttonText: 'Confirmar',
    },
  };

  const config = typeConfig[type] || typeConfig.info;
  const buttonText = options.buttonText || config.buttonText;
  const showCancel = options.showCancel || false;

  const modalOverlay = document.createElement('div');
  modalOverlay.id = 'customModal';
  modalOverlay.style.cssText = `
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0, 0, 0, 0.5);
    backdrop-filter: blur(5px);
    display: flex;
    justify-content: center;
    align-items: center;
    z-index: 10000;
    opacity: 0;
    transition: opacity 0.3s ease;
  `;

  const modalContent = document.createElement('div');
  modalContent.style.cssText = `
    background: rgba(255, 255, 255, 0.95);
    backdrop-filter: blur(20px);
    border: 1px solid rgba(255, 255, 255, 0.3);
    border-radius: 20px;
    padding: 30px;
    max-width: 500px;
    width: 90%;
    margin: 20px;
    box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1);
    transform: scale(0.8) translateY(20px);
    transition: all 0.3s cubic-bezier(0.34, 1.56, 0.64, 1);
    position: relative;
    text-align: center;
  `;

  const modalHeader = document.createElement('div');
  modalHeader.style.cssText = `
    display: flex;
    align-items: center;
    justify-content: center;
    margin-bottom: 20px;
    gap: 10px;
  `;

  const modalIcon = document.createElement('span');
  modalIcon.textContent = config.icon;
  modalIcon.style.cssText = `
    font-size: 24px;
    filter: drop-shadow(0 2px 4px rgba(0, 0, 0, 0.1));
  `;

  const modalTitle = document.createElement('h3');
  modalTitle.textContent = title;
  modalTitle.style.cssText = `
    color: ${config.color};
    font-size: 22px;
    font-weight: 600;
    margin: 0;
    text-shadow: 0 1px 2px rgba(0, 0, 0, 0.1);
  `;

  const modalMessage = document.createElement('div');
  modalMessage.innerHTML = message;
  modalMessage.style.cssText = `
    color: #374151;
    font-size: 16px;
    line-height: 1.6;
    margin-bottom: 25px;
    background: ${config.bgColor};
    padding: 15px;
    border-radius: 12px;
    border: 1px solid rgba(255, 255, 255, 0.3);
  `;

  const modalButtons = document.createElement('div');
  modalButtons.style.cssText = `
    display: flex;
    gap: 15px;
    justify-content: center;
    flex-wrap: wrap;
  `;

  const modalButton = document.createElement('button');
  modalButton.textContent = buttonText;
  modalButton.style.cssText = `
    background: linear-gradient(135deg, ${config.color}, ${config.color}dd);
    color: white;
    border: none;
    padding: 12px 30px;
    border-radius: 12px;
    font-size: 16px;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.3s ease;
    box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2);
    min-width: 120px;
  `;

  let cancelButton = null;
  if (showCancel) {
    cancelButton = document.createElement('button');
    cancelButton.textContent = options.cancelText || 'Cancelar';
    cancelButton.style.cssText = `
      background: rgba(107, 114, 128, 0.1);
      color: #6b7280;
      border: 1px solid rgba(107, 114, 128, 0.3);
      padding: 12px 30px;
      border-radius: 12px;
      font-size: 16px;
      font-weight: 600;
      cursor: pointer;
      transition: all 0.3s ease;
      min-width: 120px;
    `;
  }

  modalButton.addEventListener('mouseenter', () => {
    modalButton.style.transform = 'translateY(-2px)';
    modalButton.style.boxShadow = '0 6px 20px rgba(0, 0, 0, 0.3)';
  });

  modalButton.addEventListener('mouseleave', () => {
    modalButton.style.transform = 'translateY(0)';
    modalButton.style.boxShadow = '0 4px 15px rgba(0, 0, 0, 0.2)';
  });

  if (cancelButton) {
    cancelButton.addEventListener('mouseenter', () => {
      cancelButton.style.background = 'rgba(107, 114, 128, 0.2)';
      cancelButton.style.transform = 'translateY(-2px)';
    });

    cancelButton.addEventListener('mouseleave', () => {
      cancelButton.style.background = 'rgba(107, 114, 128, 0.1)';
      cancelButton.style.transform = 'translateY(0)';
    });
  }

  const closeModal = (result = false) => {
    modalOverlay.style.opacity = '0';
    modalContent.style.transform = 'scale(0.8) translateY(20px)';
    setTimeout(() => {
      if (modalOverlay.parentNode) {
        modalOverlay.parentNode.removeChild(modalOverlay);
      }
    }, 300);
    return result;
  };

  return new Promise((resolve) => {
    modalButton.addEventListener('click', () => {
      closeModal();
      resolve(true);
    });

    if (cancelButton) {
      cancelButton.addEventListener('click', () => {
        closeModal();
        resolve(false);
      });
    }

    modalOverlay.addEventListener('click', (e) => {
      if (e.target === modalOverlay) {
        closeModal();
        resolve(false);
      }
    });

    const escapeHandler = (e) => {
      if (e.key === 'Escape') {
        closeModal();
        resolve(false);
        document.removeEventListener('keydown', escapeHandler);
      }
    };
    document.addEventListener('keydown', escapeHandler);

    modalHeader.appendChild(modalIcon);
    modalHeader.appendChild(modalTitle);
    modalContent.appendChild(modalHeader);
    modalContent.appendChild(modalMessage);

    modalButtons.appendChild(modalButton);
    if (cancelButton) {
      modalButtons.appendChild(cancelButton);
    }
    modalContent.appendChild(modalButtons);
    modalOverlay.appendChild(modalContent);

    document.body.appendChild(modalOverlay);

    requestAnimationFrame(() => {
      modalOverlay.style.opacity = '1';
      modalContent.style.transform = 'scale(1) translateY(0)';
    });

    setTimeout(() => modalButton.focus(), 300);
  });
}

/**
 * Display a success modal with green styling
 */
function showSuccessModal(title, message) {
  return showCustomModal(title, message, 'success');
}

/**
 * Display an error modal with red styling
 */
function showErrorModal(title, message) {
  return showCustomModal(title, message, 'error');
}

/**
 * Display a warning modal with orange styling
 */
function showWarningModal(title, message) {
  return showCustomModal(title, message, 'warning');
}

/**
 * Display an info modal with blue styling
 */
function showInfoModal(title, message) {
  return showCustomModal(title, message, 'info');
}

/**
 * Display a confirmation modal with confirm/cancel buttons
 * Returns Promise that resolves to true/false based on user choice
 */
function showConfirmModal(title, message, options = {}) {
  return showCustomModal(title, message, 'confirm', {
    showCancel: true,
    buttonText: options.confirmText || 'Confirmar',
    cancelText: options.cancelText || 'Cancelar',
    ...options,
  });
}

/**
 * Modern replacement for native alert()
 */
function modernAlert(message, title = 'Aviso') {
  return showWarningModal(title, message);
}

/**
 * Modern replacement for native confirm()
 * Returns Promise that resolves to true/false
 */
function modernConfirm(message, title = 'Confirmação') {
  return showConfirmModal(title, message);
}

/**
 * Display quick toast notification for brief messages
 * Auto-dismisses after specified duration
 */
function showToast(message, type = 'info', duration = 4000) {
  const existingToast = document.getElementById('customToast');
  if (existingToast) {
    existingToast.remove();
  }

  const typeConfig = {
    success: { color: '#10b981', icon: '✅' },
    error: { color: '#ef4444', icon: '❌' },
    warning: { color: '#f59e0b', icon: '⚠️' },
    info: { color: '#3b82f6', icon: 'ℹ️' },
  };

  const config = typeConfig[type] || typeConfig.info;

  const toast = document.createElement('div');
  toast.id = 'customToast';
  toast.style.cssText = `
    position: fixed;
    top: 20px;
    right: 20px;
    background: rgba(255, 255, 255, 0.95);
    backdrop-filter: blur(20px);
    border: 1px solid rgba(255, 255, 255, 0.3);
    border-left: 4px solid ${config.color};
    border-radius: 12px;
    padding: 15px 20px;
    max-width: 400px;
    box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
    z-index: 10001;
    transform: translateX(400px);
    transition: transform 0.3s cubic-bezier(0.34, 1.56, 0.64, 1);
    display: flex;
    align-items: center;
    gap: 10px;
  `;

  const icon = document.createElement('span');
  icon.textContent = config.icon;
  icon.style.fontSize = '18px';

  const messageElement = document.createElement('span');
  messageElement.textContent = message;
  messageElement.style.cssText = `
    color: #374151;
    font-size: 14px;
    font-weight: 500;
    flex: 1;
  `;

  toast.appendChild(icon);
  toast.appendChild(messageElement);
  document.body.appendChild(toast);

  requestAnimationFrame(() => {
    toast.style.transform = 'translateX(0)';
  });

  setTimeout(() => {
    toast.style.transform = 'translateX(400px)';
    setTimeout(() => {
      if (toast.parentNode) {
        toast.parentNode.removeChild(toast);
      }
    }, 300);
  }, duration);
}

// Export functions to global window object for use throughout the application
window.showCustomModal = showCustomModal;
window.showSuccessModal = showSuccessModal;
window.showErrorModal = showErrorModal;
window.showWarningModal = showWarningModal;
window.showInfoModal = showInfoModal;
window.showConfirmModal = showConfirmModal;
window.modernAlert = modernAlert;
window.modernConfirm = modernConfirm;
window.showToast = showToast;
