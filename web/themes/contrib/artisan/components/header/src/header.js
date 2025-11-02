(function (Drupal, debounce, once) {
  Drupal.behaviors.artisan_header = {
    attach: function (context, settings) {
      once('artisan-header-theme-header-height', 'header.header', context).forEach(themeHeaderHeight);
    }
  };

  /**
   * Theme header height CSS variable manage.
   */
  function themeHeaderHeight(header) {
    if (window.getComputedStyle(header, null).getPropertyValue("header-position") === 'static') {
      return;
    }
    if (header.parentNode.hasAttribute('data-component-preview')) {
      // Preview mode, omit.
      return;
    }
    const toggler = header.querySelector('.navbar-toggler');
    const mobileMenu = header.querySelector('.mobile-menu');
    document.documentElement.style.setProperty('--theme-header-sticky-height', `${header.offsetHeight}px`);
    if (toggler !== null) {
      toggler.addEventListener('click', function() {
        setTimeout(() => {
          const totalHeight = mobileMenu ? header.offsetHeight + mobileMenu.offsetHeight : header.offsetHeight;
          document.documentElement.style.setProperty('--theme-header-sticky-height', `${totalHeight}px`);
        }, 500);
      }, false);
    }
    window.addEventListener('resize', debounce(function () {
      document.documentElement.style.setProperty('--theme-header-sticky-height', `${header.offsetHeight}px`);
    }), 250, false);
  }
})(Drupal, Drupal.debounce, once);
