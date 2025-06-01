/**
 * TalentSoft - Main JavaScript module coordinator
 */

/**
 * Initialize all system modules
 */
document.addEventListener('DOMContentLoaded', function () {
  console.log('TalentSoft: Initializing JavaScript modules...');

  // Check available modules
  const availableModules = {
    navigation: typeof window.initializeMobileMenu === 'function',
    flashMessages: typeof window.initializeFlashMessages === 'function',
    employees: typeof window.confirmDelete === 'function',
    imagePreview: checkImagePreviewElements(),
    customModal: typeof window.showCustomModal === 'function',
  };

  // Log loaded modules for debug
  console.log('Available modules:', availableModules);

  // Initialize page-specific modules
  initializePageSpecificModules();
});

/**
 * Check if image preview elements are present
 */
function checkImagePreviewElements() {
  return !!(
    document.getElementById('image_preview_input') &&
    document.getElementById('image_preview')
  );
}

/**
 * Initialize page-specific modules
 */
function initializePageSpecificModules() {
  const currentPath = window.location.pathname;

  // Employee pages
  if (currentPath.includes('/employees')) {
    console.log('Employee page detected');
  }

  // Auth pages
  if (currentPath.includes('/auth') || currentPath.includes('/login')) {
    console.log('Auth page detected');
  }

  // Admin pages
  if (currentPath.includes('/admin')) {
    console.log('Admin page detected');
  }
}

/**
 * Safe event listener utility
 * @param {string} selector - CSS selector
 * @param {string} event - Event type
 * @param {function} handler - Event handler
 * @param {object} options - Event listener options
 */
function safeAddEventListener(selector, event, handler, options = {}) {
  const elements = document.querySelectorAll(selector);
  elements.forEach((element) => {
    element.addEventListener(event, handler, options);
  });
}

/**
 * Debug utility - show page elements info
 */
function debugPageElements() {
  if (window.location.search.includes('debug=js')) {
    console.log('=== DEBUG: Page elements ===');
    console.log(
      'Delete buttons:',
      document.querySelectorAll('.delete-btn').length,
    );
    console.log(
      'Flash messages:',
      document.querySelectorAll('.flash-message').length,
    );
    console.log(
      'Mobile menu button:',
      !!document.getElementById('mobile-menu-button'),
    );
    console.log('Mobile menu:', !!document.getElementById('mobile-menu'));
    console.log('Image preview:', !!document.getElementById('image_preview'));
  }
}

// Run debug if requested
debugPageElements();

// Export utilities for global use
window.TalentSoft = {
  safeAddEventListener,
  debugPageElements,
  initializePageSpecificModules,
};
