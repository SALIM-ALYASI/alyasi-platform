document.addEventListener('DOMContentLoaded', () => {

  /* ---------------------------------------------------------
     Header: background state on scroll
  --------------------------------------------------------- */
  const header = document.getElementById('siteHeader');
  if (header) {
    const onHeaderScroll = () => header.classList.toggle('is-scrolled', window.scrollY > 40);
    onHeaderScroll();
    window.addEventListener('scroll', onHeaderScroll, { passive: true });
  }

  /* ---------------------------------------------------------
     Mobile menu
  --------------------------------------------------------- */
  const navToggle = document.getElementById('navToggle');
  const mobileMenu = document.getElementById('mobileMenu');

  if (navToggle && mobileMenu) {
    const closeMenu = () => {
      navToggle.classList.remove('is-active');
      navToggle.setAttribute('aria-expanded', 'false');
      mobileMenu.classList.remove('is-open');
      mobileMenu.setAttribute('aria-hidden', 'true');
      document.body.style.overflow = '';
    };
    const openMenu = () => {
      navToggle.classList.add('is-active');
      navToggle.setAttribute('aria-expanded', 'true');
      mobileMenu.classList.add('is-open');
      mobileMenu.setAttribute('aria-hidden', 'false');
      document.body.style.overflow = 'hidden';
    };

    navToggle.addEventListener('click', () => {
      navToggle.classList.contains('is-active') ? closeMenu() : openMenu();
    });
    mobileMenu.querySelectorAll('a').forEach((link) => link.addEventListener('click', closeMenu));
    window.addEventListener('keydown', (e) => { if (e.key === 'Escape') closeMenu(); });
    window.addEventListener('resize', () => { if (window.innerWidth > 1024) closeMenu(); });
  }

  /* ---------------------------------------------------------
     Scroll-triggered reveal animations
  --------------------------------------------------------- */
  const revealEls = document.querySelectorAll('[data-reveal]');
  if ('IntersectionObserver' in window && revealEls.length) {
    const revealObserver = new IntersectionObserver((entries) => {
      entries.forEach((entry) => {
        if (entry.isIntersecting) {
          entry.target.classList.add('is-in');
          revealObserver.unobserve(entry.target);
        }
      });
    }, { threshold: .15 });
    revealEls.forEach((el) => revealObserver.observe(el));
  } else {
    revealEls.forEach((el) => el.classList.add('is-in'));
  }

  /* ---------------------------------------------------------
     FAQ accordion (single-open, works for any .faq-item on any page)
  --------------------------------------------------------- */
  document.querySelectorAll('.faq-list').forEach((list) => {
    const items = list.querySelectorAll('.faq-item');
    items.forEach((item) => {
      const question = item.querySelector('.faq-item__question');
      const mark = item.querySelector('.faq-item__mark');
      if (!question) return;

      question.addEventListener('click', () => {
        const willOpen = !item.classList.contains('is-open');
        items.forEach((other) => {
          other.classList.remove('is-open');
          const otherMark = other.querySelector('.faq-item__mark');
          if (otherMark) otherMark.textContent = '+';
        });
        if (willOpen) {
          item.classList.add('is-open');
          if (mark) mark.textContent = '−';
        }
      });
    });
  });

  /* ---------------------------------------------------------
     Subtle parallax for [data-parallax] backgrounds
     Disabled for reduced-motion and touch/coarse pointers.
  --------------------------------------------------------- */
  const prefersReducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
  const isCoarsePointer = window.matchMedia('(pointer: coarse)').matches;
  const parallaxEls = document.querySelectorAll('[data-parallax]');

  if (!prefersReducedMotion && !isCoarsePointer && parallaxEls.length) {
    let ticking = false;
    const applyParallax = () => {
      const scrollY = window.scrollY;
      parallaxEls.forEach((el) => {
        const speed = parseFloat(el.dataset.parallaxSpeed || '0.3');
        el.style.transform = `translateY(${scrollY * speed}px)`;
      });
      ticking = false;
    };
    window.addEventListener('scroll', () => {
      if (!ticking) { window.requestAnimationFrame(applyParallax); ticking = true; }
    }, { passive: true });
  }

});
