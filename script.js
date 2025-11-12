(function(){
  const root = document.documentElement;
  const header = document.querySelector('.header-container');
  if (!header) return;

  function setHeaderBottom(){
    const h = header.offsetHeight;
    root.style.setProperty('--header-bottom', h + 'px');
  }

  let rafId = null;
  function schedule(){
    if (rafId) cancelAnimationFrame(rafId);
    rafId = requestAnimationFrame(setHeaderBottom);
  }

  window.addEventListener('load', schedule);
  window.addEventListener('resize', schedule);
  document.addEventListener('DOMContentLoaded', schedule);
  if (document.fonts && document.fonts.ready) {
    document.fonts.ready.then(schedule).catch(()=>{});
  }
  schedule();

  // ============================================
  // Banner continuous loop setup
  // Ensures the .banner-content is repeated enough to scroll continuously
  // ============================================
  function setupBannerLoop() {
    const banner = document.querySelector('.top-banner');
    if (!banner) return;
    const track = banner.querySelector('.banner-content');
    if (!track) return;

    // Remove previous inline animation so measurements are accurate
    track.style.animation = 'none';

    // Ensure content is long enough by duplicating until it's at least twice the container width
    const containerWidth = banner.clientWidth || window.innerWidth;
    let contentWidth = track.scrollWidth;

    // If content is already very large, avoid infinite loop
    let safety = 0;
    while (contentWidth < containerWidth * 2 && safety < 8) {
      track.innerHTML += track.innerHTML;
      contentWidth = track.scrollWidth;
      safety++;
    }

    // Set animation duration proportionally to content width so speed feels natural
    // speed: pixels per second (adjust as desired)
    const speed = 180; // px per second
    const duration = Math.max(8, Math.round(contentWidth / speed));
    track.style.animation = `scrollBanner ${duration}s linear infinite`;
  }

  // Run on load and resize
  window.addEventListener('load', setupBannerLoop);
  window.addEventListener('resize', function(){
    // small debounce
    clearTimeout(window._bannerResizeT);
    window._bannerResizeT = setTimeout(setupBannerLoop, 220);
  });

  // ============================================
  // Mobile Menu Toggle
  // ============================================
  const hamburger = document.querySelector('.hamburger-btn');
  const mobileNav = document.getElementById('mobile-nav');
  const body = document.body;
  
  // Create overlay if it doesn't exist
  let overlay = document.querySelector('.mobile-overlay');
  if (!overlay) {
    overlay = document.createElement('div');
    overlay.className = 'mobile-overlay';
    body.appendChild(overlay);
  }

  function toggleMenu() {
    const isOpen = mobileNav.classList.contains('open');
    
    if (isOpen) {
      closeMenu();
    } else {
      openMenu();
    }
  }

  function openMenu() {
    hamburger.classList.add('active');
    mobileNav.classList.add('open');
    overlay.classList.add('active');
    body.style.overflow = 'hidden';
  }

  function closeMenu() {
    hamburger.classList.remove('active');
    mobileNav.classList.remove('open');
    overlay.classList.remove('active');
    body.style.overflow = '';
    
    // Close all dropdowns
    document.querySelectorAll('.nav-item.dropdown-open').forEach(item => {
      item.classList.remove('dropdown-open');
    });
  }

  // Hamburger click
  if (hamburger) {
    hamburger.addEventListener('click', function(e) {
      e.stopPropagation();
      toggleMenu();
    });
  }

  // Overlay click
  if (overlay) {
    overlay.addEventListener('click', closeMenu);
  }

  // Handle dropdown clicks on mobile
  function setupDropdowns() {
    const dropdownItems = document.querySelectorAll('.nav-item.dropdown');
    
    dropdownItems.forEach(item => {
      const link = item.querySelector('.nav-link');
      
      if (link) {
        // Remove old listeners by cloning
        const newLink = link.cloneNode(true);
        link.parentNode.replaceChild(newLink, link);
        
        // Add new listener
        newLink.addEventListener('click', function(e) {
          if (window.innerWidth <= 768) {
            e.preventDefault();
            e.stopPropagation();
            
            // Close other dropdowns
            dropdownItems.forEach(otherItem => {
              if (otherItem !== item) {
                otherItem.classList.remove('dropdown-open');
              }
            });
            
            // Toggle this dropdown
            item.classList.toggle('dropdown-open');
          }
        });
      }
    });
  }

  setupDropdowns();

  // Close menu on window resize to desktop
  let resizeTimer;
  window.addEventListener('resize', function() {
    clearTimeout(resizeTimer);
    resizeTimer = setTimeout(function() {
      if (window.innerWidth > 768) {
        closeMenu();
      } else {
        setupDropdowns();
      }
    }, 250);
  });

  // Prevent menu from being stuck open on page load
  if (window.innerWidth <= 768) {
    closeMenu();
  }
})();