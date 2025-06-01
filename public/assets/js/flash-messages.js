/**
 * Flash messages system
 */

document.addEventListener('DOMContentLoaded', function () {
  initializeFlashMessages();
});

/**
 * Initialize flash messages system
 */
function initializeFlashMessages() {
  const flashMessages = document.querySelectorAll('.flash-message');

  flashMessages.forEach((messageEl) => {
    // Auto-fade after 5 seconds
    setTimeout(() => {
      fadeOutMessage(messageEl);
    }, 5000);

    // Close on X button click
    const closeBtn = messageEl.querySelector('.close-flash');
    if (closeBtn) {
      closeBtn.addEventListener('click', () => {
        fadeOutMessage(messageEl);
      });
    }
  });
}

/**
 * Fade out and remove flash message
 * @param {HTMLElement} messageEl - Message element
 */
function fadeOutMessage(messageEl) {
  if (!messageEl || !messageEl.parentNode) return;

  messageEl.style.opacity = '0';
  messageEl.style.transform = 'translateY(-20px)';
  messageEl.style.transition = 'all 0.3s ease';

  setTimeout(() => {
    if (messageEl.parentNode) {
      messageEl.parentNode.removeChild(messageEl);
    }

    // Dispatch custom event
    document.dispatchEvent(
      new CustomEvent('flashMessageHidden', {
        detail: { messageType: messageEl.dataset.type || 'info' },
      }),
    );
  }, 300);
}

/**
 * Create new flash message programmatically
 * @param {string} message - Message text
 * @param {string} type - Message type (success, error, warning, info)
 */
function createFlashMessage(message, type = 'info') {
  const container = document.querySelector('main') || document.body;

  const messageHTML = `
        <div class="flash-message flash-${type} fixed top-4 right-4 z-50 max-w-sm p-4 rounded-lg shadow-lg transition-all duration-300"
             data-type="${type}">
            <div class="flex items-center justify-between">
                <span class="text-sm font-medium">${message}</span>
                <button class="close-flash ml-2 text-lg leading-none hover:opacity-70 transition-opacity">
                    &times;
                </button>
            </div>
        </div>
    `;

  container.insertAdjacentHTML('afterbegin', messageHTML);

  // Reinitialize for new message
  const newMessage = container.querySelector('.flash-message');
  if (newMessage) {
    // Auto-fade after 5 seconds
    setTimeout(() => {
      fadeOutMessage(newMessage);
    }, 5000);

    // Close on X button click
    const closeBtn = newMessage.querySelector('.close-flash');
    if (closeBtn) {
      closeBtn.addEventListener('click', () => {
        fadeOutMessage(newMessage);
      });
    }
  }
}

// Export functions for global use
window.initializeFlashMessages = initializeFlashMessages;
window.fadeOutMessage = fadeOutMessage;
window.createFlashMessage = createFlashMessage;
