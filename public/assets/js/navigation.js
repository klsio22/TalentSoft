/**
 * Navigation and header scripts
 */

document.addEventListener('DOMContentLoaded', function () {
  initializeMobileMenu();
});

/**
 * Initialize mobile menu toggle
 */
function initializeMobileMenu() {
  const mobileMenuButton = document.getElementById('mobile-menu-button');
  const mobileMenu = document.getElementById('mobile-menu');

  if (mobileMenuButton && mobileMenu) {
    mobileMenuButton.addEventListener('click', function () {
      mobileMenu.classList.toggle('hidden');
    });

    // Close menu on outside click
    document.addEventListener('click', function (event) {
      const isClickInsideMenu = mobileMenu.contains(event.target);
      const isClickOnButton = mobileMenuButton.contains(event.target);

      if (
        !isClickInsideMenu &&
        !isClickOnButton &&
        !mobileMenu.classList.contains('hidden')
      ) {
        mobileMenu.classList.add('hidden');
      }
    });

    // Close menu on ESC key
    document.addEventListener('keydown', function (event) {
      if (event.key === 'Escape' && !mobileMenu.classList.contains('hidden')) {
        mobileMenu.classList.add('hidden');
      }
    });
  }
}

// Export functions for global use
window.initializeMobileMenu = initializeMobileMenu;
