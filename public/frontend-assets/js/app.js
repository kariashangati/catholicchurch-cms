document.addEventListener('DOMContentLoaded', function () {
  const navbar = document.getElementById('navbar');
  const menuIcon = document.getElementById('menuIcon');
  const drawer = document.getElementById('mobileDrawer');
  const closeDrawer = document.getElementById('closeDrawer');
  const drawerBackdrop = document.getElementById('mobileDrawerBackdrop');
  const scrollBtn = document.getElementById('scrollTopBtn');
  const langDropdowns = document.querySelectorAll('.lang-dropdown');

  const syncNavbarState = () => {
    if (!navbar) return;

    if (window.scrollY > 24) {
      navbar.classList.add('scrolled');
    } else {
      navbar.classList.remove('scrolled');
    }
  };

  const openDrawer = () => {
    if (!drawer || !drawerBackdrop) return;

    drawer.classList.add('open');
    drawer.setAttribute('aria-hidden', 'false');
    drawerBackdrop.classList.add('show');
    document.body.style.overflow = 'hidden';
  };

  const closeDrawerPanel = () => {
    if (!drawer || !drawerBackdrop) return;

    drawer.classList.remove('open');
    drawer.setAttribute('aria-hidden', 'true');
    drawerBackdrop.classList.remove('show');
    document.body.style.overflow = '';
  };

  const closeAllLanguageDropdowns = () => {
    langDropdowns.forEach((dropdown) => {
      dropdown.classList.remove('open');

      const toggle = dropdown.querySelector('.lang-dropdown-toggle');
      if (toggle) {
        toggle.setAttribute('aria-expanded', 'false');
      }
    });
  };

  syncNavbarState();

  window.addEventListener('scroll', () => {
    syncNavbarState();

    if (!scrollBtn) return;

    if (window.scrollY > 400) {
      scrollBtn.classList.add('show');
    } else {
      scrollBtn.classList.remove('show');
    }
  });

  menuIcon?.addEventListener('click', openDrawer);
  closeDrawer?.addEventListener('click', closeDrawerPanel);
  drawerBackdrop?.addEventListener('click', closeDrawerPanel);

  document.querySelectorAll('#mobileDrawer a').forEach((link) => {
    link.addEventListener('click', closeDrawerPanel);
  });

  langDropdowns.forEach((dropdown) => {
    const toggle = dropdown.querySelector('.lang-dropdown-toggle');

    toggle?.addEventListener('click', function (event) {
      event.preventDefault();
      event.stopPropagation();

      const isOpen = dropdown.classList.contains('open');

      closeAllLanguageDropdowns();

      if (!isOpen) {
        dropdown.classList.add('open');
        toggle.setAttribute('aria-expanded', 'true');
      }
    });
  });

  document.addEventListener('click', closeAllLanguageDropdowns);

  document.addEventListener('keydown', (event) => {
    if (event.key === 'Escape') {
      closeDrawerPanel();
      closeAllLanguageDropdowns();
    }
  });



  document.querySelectorAll('.nav-dropdown-toggle').forEach((toggle) => {
  toggle.addEventListener('click', function (event) {
    const dropdown = toggle.closest('.nav-dropdown');
    if (!dropdown) return;

    event.preventDefault();
    dropdown.classList.toggle('open');
    toggle.setAttribute('aria-expanded', dropdown.classList.contains('open') ? 'true' : 'false');
  });
});

document.addEventListener('click', function (event) {
  document.querySelectorAll('.nav-dropdown.open').forEach((dropdown) => {
    if (!dropdown.contains(event.target)) {
      dropdown.classList.remove('open');

      const toggle = dropdown.querySelector('.nav-dropdown-toggle');
      if (toggle) toggle.setAttribute('aria-expanded', 'false');
    }
  });
});

  scrollBtn?.addEventListener('click', () => {
    window.scrollTo({
      top: 0,
      behavior: 'smooth'
    });
  });
});