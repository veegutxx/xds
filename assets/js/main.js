/**
 * XDS Zimbabwe — Core UI, Theme, GSAP Directional Scroll, 3D Flip, Flatpickr & AJAX
 */
(function () {
  'use strict';

  const cfg = window.XDS_CONFIG || { basePath: '/', csrfToken: '' };
  const prefersReducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

  function apiUrl(endpoint) {
    return (cfg.basePath || '/').replace(/\/?$/, '/') + endpoint.replace(/^\/+/, '');
  }

  function refreshIcons() {
    if (window.lucide && typeof window.lucide.createIcons === 'function') {
      window.lucide.createIcons();
    }
  }

  // ---------------------------------------------------------------------------
  // 1. Light / Dark Theme Persistence
  // ---------------------------------------------------------------------------
  function getCurrentTheme() {
    return document.documentElement.getAttribute('data-theme') === 'dark' ? 'dark' : 'light';
  }

  function applyTheme(theme, persist) {
    const normalized = theme === 'dark' ? 'dark' : 'light';
    document.documentElement.setAttribute('data-theme', normalized);
    if (persist) {
      try {
        localStorage.setItem('xds-theme', normalized);
      } catch (err) {
        // Ignore storage errors in restricted browsing modes
      }
    }
    document.querySelectorAll('[data-theme-toggle]').forEach((btn) => {
      btn.setAttribute('aria-pressed', normalized === 'dark' ? 'true' : 'false');
      btn.setAttribute(
        'aria-label',
        normalized === 'dark' ? 'Switch to light mode' : 'Switch to dark mode'
      );
      const labelEl = btn.querySelector('[data-theme-label]');
      if (labelEl) {
        labelEl.textContent = normalized === 'dark' ? 'Light Mode' : 'Dark Mode';
      }
    });
    window.dispatchEvent(new CustomEvent('xds:themechange', { detail: { theme: normalized } }));
  }

  function initTheme() {
    applyTheme(getCurrentTheme(), false);
    document.querySelectorAll('[data-theme-toggle]').forEach((btn) => {
      btn.addEventListener('click', () => {
        applyTheme(getCurrentTheme() === 'dark' ? 'light' : 'dark', true);
      });
    });

    const media = window.matchMedia('(prefers-color-scheme: dark)');
    if (media && typeof media.addEventListener === 'function') {
      media.addEventListener('change', (ev) => {
        let saved = null;
        try {
          saved = localStorage.getItem('xds-theme');
        } catch (err) {}
        if (!saved) {
          applyTheme(ev.matches ? 'dark' : 'light', false);
        }
      });
    }
  }

  // ---------------------------------------------------------------------------
  // 2. Sticky Header, Dropdowns, User Avatar Portal & Mobile Drawer
  // ---------------------------------------------------------------------------
  function initNavigation() {
    const header = document.querySelector('.site-header');
    if (header) {
      const onScroll = () => {
        header.classList.toggle('is-scrolled', window.scrollY > 12);
      };
      onScroll();
      window.addEventListener('scroll', onScroll, { passive: true });
    }

    // Desktop click-toggled dropdowns & User Avatar popover
    const avatarWrap = document.querySelector('[data-avatar-portal]');
    const avatarBtn = document.querySelector('[data-avatar-trigger]');

    if (avatarWrap && avatarBtn) {
      avatarBtn.addEventListener('click', (e) => {
        e.stopPropagation();
        const open = !avatarWrap.classList.contains('is-open');
        avatarWrap.classList.toggle('is-open', open);
        avatarBtn.setAttribute('aria-expanded', open ? 'true' : 'false');
      });
    }

    document.querySelectorAll('[data-nav-dropdown-btn]').forEach((btn) => {
      const item = btn.closest('.nav-item');
      if (!item) return;

      // Treat clicking a dropdown nav item just like hovering (never sticks on mouseleave)
      btn.addEventListener('click', (e) => {
        e.preventDefault();
        e.stopPropagation();
        document.querySelectorAll('.nav-item.is-open').forEach((other) => {
          if (other !== item) {
            other.classList.remove('is-open');
            const ob = other.querySelector('[data-nav-dropdown-btn]');
            if (ob) ob.setAttribute('aria-expanded', 'false');
          }
        });
        item.classList.add('is-open');
        btn.setAttribute('aria-expanded', 'true');
      });

      item.addEventListener('mouseenter', () => {
        document.querySelectorAll('.nav-item.is-open').forEach((other) => {
          if (other !== item) {
            other.classList.remove('is-open');
            const ob = other.querySelector('[data-nav-dropdown-btn]');
            if (ob) ob.setAttribute('aria-expanded', 'false');
          }
        });
        item.classList.add('is-open');
        btn.setAttribute('aria-expanded', 'true');
      });

      item.addEventListener('mouseleave', () => {
        item.classList.remove('is-open');
        btn.setAttribute('aria-expanded', 'false');
        if (document.activeElement === btn) {
          btn.blur();
        }
      });
    });

    document.addEventListener('click', (e) => {
      if (avatarWrap && !avatarWrap.contains(e.target)) {
        avatarWrap.classList.remove('is-open');
        if (avatarBtn) avatarBtn.setAttribute('aria-expanded', 'false');
      }
      document.querySelectorAll('.nav-item.is-open').forEach((item) => {
        if (!item.contains(e.target)) {
          item.classList.remove('is-open');
          const b = item.querySelector('[data-nav-dropdown-btn]');
          if (b) b.setAttribute('aria-expanded', 'false');
        }
      });
    });

    // Mobile Drawer
    const drawer = document.getElementById('mobile-drawer');
    const backdrop = document.getElementById('mobile-drawer-backdrop');
    const openBtns = document.querySelectorAll('[data-drawer-open]');
    const closeBtns = document.querySelectorAll('[data-drawer-close]');

    function setDrawer(open) {
      if (!drawer || !backdrop) return;
      drawer.classList.toggle('is-open', open);
      backdrop.classList.toggle('is-open', open);
      drawer.setAttribute('aria-hidden', open ? 'false' : 'true');
      openBtns.forEach((b) => b.setAttribute('aria-expanded', open ? 'true' : 'false'));
      document.body.style.overflow = open ? 'hidden' : '';
    }

    openBtns.forEach((btn) => btn.addEventListener('click', () => setDrawer(true)));
    closeBtns.forEach((btn) => btn.addEventListener('click', () => setDrawer(false)));
    if (backdrop) backdrop.addEventListener('click', () => setDrawer(false));

    // Mobile accordions
    document.querySelectorAll('[data-mobile-accordion-trigger]').forEach((trigger) => {
      trigger.addEventListener('click', () => {
        const container = trigger.closest('.mobile-accordion');
        const expanded = !container.classList.contains('is-open');
        container.classList.toggle('is-open', expanded);
        trigger.setAttribute('aria-expanded', expanded ? 'true' : 'false');
      });
    });

    document.addEventListener('keydown', (e) => {
      if (e.key === 'Escape') {
        setDrawer(false);
        if (avatarWrap) {
          avatarWrap.classList.remove('is-open');
          if (avatarBtn) avatarBtn.setAttribute('aria-expanded', 'false');
        }
        closeAllModals();
      }
    });
  }

  // ---------------------------------------------------------------------------
  // 3. GSAP + ScrollTrigger Scroll Entrance Cards
  //    Scroll down -> animate IN once and stay visible
  // ---------------------------------------------------------------------------
  function initScrollAnimations() {
    if (prefersReducedMotion || !window.gsap || !window.ScrollTrigger) {
      return;
    }

    window.gsap.registerPlugin(window.ScrollTrigger);

    const cards = window.gsap.utils.toArray('.xds-scroll-card');
    cards.forEach((card, index) => {
      const delay = (index % 4) * 0.06;
      window.gsap.fromTo(
        card,
        {
          autoAlpha: 0,
          y: 32,
          scale: 0.97,
        },
        {
          autoAlpha: 1,
          y: 0,
          scale: 1,
          duration: 0.6,
          delay: delay,
          ease: 'power3.out',
          clearProps: 'transform',
          scrollTrigger: {
            trigger: card,
            start: 'top 90%',
            once: true,
            toggleActions: 'play none none none',
          },
        }
      );
    });
  }

  // ---------------------------------------------------------------------------
  // 4. 3D Flip Cards (Keyboard & Touch Accessibility)
  // ---------------------------------------------------------------------------
  function initFlipCards() {
    document.querySelectorAll('.xds-flip-card').forEach((card) => {
      card.addEventListener('click', (e) => {
        if (e.target.closest('a, button')) return;
        card.classList.toggle('is-flipped');
      });
      card.addEventListener('keydown', (e) => {
        if (e.key === 'Enter' || e.key === ' ') {
          if (e.target.closest('a, button')) return;
          e.preventDefault();
          card.classList.toggle('is-flipped');
        }
      });
    });
  }

  // ---------------------------------------------------------------------------
  // 5. Modals (Portal, Credit Sign-up / Report Request, Article Reader)
  // ---------------------------------------------------------------------------
  function openModal(id) {
    const modal = document.getElementById(id);
    if (!modal) return;
    modal.classList.add('is-open');
    modal.setAttribute('aria-hidden', 'false');
    document.body.style.overflow = 'hidden';
    const focusable = modal.querySelector('input, select, textarea, button, a[href]');
    if (focusable) {
      setTimeout(() => focusable.focus(), 60);
    }
  }

  function closeModal(modal) {
    if (!modal) return;
    modal.classList.remove('is-open');
    modal.setAttribute('aria-hidden', 'true');
    document.body.style.overflow = '';
  }

  function closeAllModals() {
    document.querySelectorAll('.xds-modal.is-open').forEach(closeModal);
  }

  function initModals() {
    document.querySelectorAll('[data-modal-open]').forEach((trigger) => {
      trigger.addEventListener('click', (e) => {
        e.preventDefault();
        const targetId = trigger.getAttribute('data-modal-open');
        // Close mobile drawer if open
        const drawer = document.getElementById('mobile-drawer');
        if (drawer && drawer.classList.contains('is-open')) {
          drawer.classList.remove('is-open');
          const bd = document.getElementById('mobile-drawer-backdrop');
          if (bd) bd.classList.remove('is-open');
        }
        openModal(targetId);
      });
    });

    document.querySelectorAll('[data-modal-close]').forEach((btn) => {
      btn.addEventListener('click', () => {
        closeModal(btn.closest('.xds-modal'));
      });
    });

    document.querySelectorAll('.xds-modal').forEach((modal) => {
      modal.addEventListener('click', (e) => {
        if (e.target === modal) {
          closeModal(modal);
        }
      });
    });
  }

  // ---------------------------------------------------------------------------
  // 6. Flatpickr Date & Business Hours Time Picker
  // ---------------------------------------------------------------------------
  function initFlatpickr() {
    if (typeof window.flatpickr !== 'function') return;

    document.querySelectorAll('[data-flatpickr-date]').forEach((input) => {
      window.flatpickr(input, {
        dateFormat: 'Y-m-d',
        altInput: true,
        altFormat: 'D, d M Y',
        minDate: 'today',
        disableMobile: false,
        disable: [
          function (date) {
            return date.getDay() === 0 || date.getDay() === 6; // Closed Sat & Sun
          },
        ],
      });
    });

    document.querySelectorAll('[data-flatpickr-time]').forEach((input) => {
      window.flatpickr(input, {
        enableTime: true,
        noCalendar: true,
        dateFormat: 'H:i',
        time_24hr: true,
        minTime: '08:00',
        maxTime: '16:30',
        minuteIncrement: 15,
        disableMobile: false,
      });
    });
  }

  // ---------------------------------------------------------------------------
  // 7. AJAX Forms (Contact & Quick Credit Service Modals -> inc/contact/index.php)
  // ---------------------------------------------------------------------------
  function initAjaxForms() {
    document.querySelectorAll('[data-ajax-contact-form]').forEach((form) => {
      form.addEventListener('submit', async (e) => {
        e.preventDefault();

        const alertBox = form.querySelector('[data-form-alert]');
        const submitBtn = form.querySelector('button[type="submit"]');
        const originalBtnHtml = submitBtn ? submitBtn.innerHTML : '';

        // Clear prior field errors
        form.querySelectorAll('.field-error').forEach((el) => (el.textContent = ''));
        form.querySelectorAll('.is-invalid').forEach((el) => el.classList.remove('is-invalid'));
        if (alertBox) {
          alertBox.className = 'form-alert';
          alertBox.textContent = '';
        }

        const formData = new FormData(form);
        const payload = {};
        formData.forEach((val, key) => {
          payload[key] = val;
        });
        if (!payload.csrf_token) {
          payload.csrf_token = cfg.csrfToken || '';
        }

        if (submitBtn) {
          submitBtn.disabled = true;
          submitBtn.innerHTML = '<span>Sending...</span>';
        }

        try {
          const response = await fetch(apiUrl('inc/contact/index.php'), {
            method: 'POST',
            headers: {
              'Content-Type': 'application/json',
              'X-CSRF-Token': payload.csrf_token,
              Accept: 'application/json',
            },
            body: JSON.stringify(payload),
          });

          const data = await response.json();

          if (!response.ok || !data.ok) {
            if (data.errors && typeof data.errors === 'object') {
              Object.entries(data.errors).forEach(([field, msg]) => {
                const input = form.querySelector(`[name="${field}"]`);
                if (input) input.classList.add('is-invalid');
                const errEl = form.querySelector(`[data-error-for="${field}"]`);
                if (errEl) errEl.textContent = msg;
              });
            }
            if (alertBox) {
              alertBox.className = 'form-alert is-visible is-error';
              alertBox.textContent = data.message || 'Please check the form fields and try again.';
            }
          } else {
            if (alertBox) {
              alertBox.className = 'form-alert is-visible is-success';
              alertBox.textContent = data.message || 'Thank you! Your enquiry has been received.';
            }
            form.reset();
            // Clear flatpickr alt inputs if any
            form.querySelectorAll('[data-flatpickr-date], [data-flatpickr-time]').forEach((el) => {
              if (el._flatpickr) el._flatpickr.clear();
            });
          }
        } catch (err) {
          if (alertBox) {
            alertBox.className = 'form-alert is-visible is-error';
            alertBox.textContent =
              'Network error while submitting your enquiry. Please try again or call +263 242 779 344.';
          }
        } finally {
          if (submitBtn) {
            submitBtn.disabled = false;
            submitBtn.innerHTML = originalBtnHtml;
            refreshIcons();
          }
        }
      });
    });
  }

  // ---------------------------------------------------------------------------
  // 8. Articles Live AJAX Search & Category Filter (opens common view page)
  // ---------------------------------------------------------------------------
  function initArticlesModule() {
    const grid = document.getElementById('articles-grid');
    if (!grid) return;

    const searchInput = document.getElementById('articles-search');
    const pills = document.querySelectorAll('[data-article-category]');
    const countLabel = document.getElementById('articles-count');

    let currentCategory = 'all';
    let currentQuery = '';
    let debounceTimer = null;

    function escapeHtml(str) {
      return String(str || '')
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;');
    }

    async function fetchArticles() {
      grid.setAttribute('aria-busy', 'true');
      const params = new URLSearchParams();
      if (currentCategory && currentCategory !== 'all') params.set('category', currentCategory);
      if (currentQuery.trim() !== '') params.set('q', currentQuery.trim());
      params.set('per_page', '12');

      try {
        const res = await fetch(apiUrl('inc/articles/index.php?' + params.toString()), {
          headers: { Accept: 'application/json' },
        });
        const data = await res.json();
        if (!data.ok) return;

        if (countLabel) {
          countLabel.textContent = `${data.total} article${data.total === 1 ? '' : 's'} found`;
        }

        if (!data.items.length) {
          grid.innerHTML =
            '<div class="glass-card" style="padding:36px;text-align:center;grid-column:1/-1;"><h3>No matching articles</h3><p style="margin-bottom:0;">Try clearing your search or selecting a different category.</p></div>';
          return;
        }

        grid.innerHTML = data.items
          .map(
            (art) => `
          <article class="glass-card glass-card-hover article-card">
            <div class="article-card-body">
              <div class="article-meta">
                <span class="category-badge">${escapeHtml(art.category)}</span>
                <span>${escapeHtml(art.formatted_date)}</span>
                <span>&bull; ${escapeHtml(art.read_time_minutes)} min read</span>
              </div>
              <div class="article-card-top">
                <a href="${escapeHtml(art.url)}" style="flex-shrink:0;">
                  <img class="article-thumb" src="${escapeHtml(art.image_url)}" alt="${escapeHtml(art.title)}" loading="lazy">
                </a>
                <div>
                  <h3><a href="${escapeHtml(art.url)}">${escapeHtml(art.title)}</a></h3>
                  <p style="margin-bottom:0;font-size:0.92rem;">${escapeHtml(art.summary)}</p>
                </div>
              </div>
              <div class="article-card-footer">
                <span style="font-size:0.82rem;color:var(--text-muted);">${escapeHtml(art.author)}</span>
                <div style="display:flex;align-items:center;gap:8px;">
                  <button type="button" class="btn btn-glass btn-sm" data-quick-share data-share-title="${escapeHtml(art.title)}" data-share-text="${escapeHtml(art.summary)}" data-share-url="${escapeHtml(art.canonical_url || art.url)}" aria-label="Share ${escapeHtml(art.title)}" title="Share article">
                    <i data-lucide="share-2"></i>
                  </button>
                  <a href="${escapeHtml(art.url)}" class="btn btn-primary btn-sm">
                    <span>Read More</span>
                    <i data-lucide="arrow-right"></i>
                  </a>
                </div>
              </div>
            </div>
          </article>`
          )
          .join('');
        refreshIcons();
      } catch (err) {
        // Keep existing server-rendered content on transient network failure
      } finally {
        grid.setAttribute('aria-busy', 'false');
      }
    }

    pills.forEach((pill) => {
      pill.addEventListener('click', () => {
        pills.forEach((p) => p.classList.remove('is-active'));
        pill.classList.add('is-active');
        currentCategory = pill.getAttribute('data-article-category') || 'all';
        fetchArticles();
      });
    });

    if (searchInput) {
      searchInput.addEventListener('input', () => {
        currentQuery = searchInput.value;
        clearTimeout(debounceTimer);
        debounceTimer = setTimeout(fetchArticles, 260);
      });
    }
  }

  // ---------------------------------------------------------------------------
  // 8b. Hero Business Insights Text Carousel
  // ---------------------------------------------------------------------------
  function initHeroCarousel() {
    const root = document.querySelector('[data-hero-carousel]');
    if (!root) return;

    const slides = Array.from(root.querySelectorAll('[data-hero-slide]'));
    const dots = Array.from(root.querySelectorAll('[data-carousel-dot]'));
    const prevBtn = root.querySelector('[data-carousel-prev]');
    const nextBtn = root.querySelector('[data-carousel-next]');

    if (slides.length <= 1) return;

    let current = 0;
    let timer = null;

    function goTo(index) {
      const nextIndex = (index + slides.length) % slides.length;
      if (nextIndex === current) return;

      slides[current].classList.remove('is-active');
      slides[current].setAttribute('aria-hidden', 'true');
      if (dots[current]) {
        dots[current].classList.remove('is-active');
        dots[current].setAttribute('aria-selected', 'false');
      }

      current = nextIndex;
      slides[current].classList.add('is-active');
      slides[current].setAttribute('aria-hidden', 'false');
      if (dots[current]) {
        dots[current].classList.add('is-active');
        dots[current].setAttribute('aria-selected', 'true');
      }
    }

    function stopAuto() {
      if (timer) {
        clearInterval(timer);
        timer = null;
      }
    }

    function startAuto() {
      if (prefersReducedMotion) return;
      stopAuto();
      timer = setInterval(() => goTo(current + 1), 6500);
    }

    if (prevBtn) {
      prevBtn.addEventListener('click', () => {
        goTo(current - 1);
        startAuto();
      });
    }

    if (nextBtn) {
      nextBtn.addEventListener('click', () => {
        goTo(current + 1);
        startAuto();
      });
    }

    dots.forEach((dot) => {
      dot.addEventListener('click', () => {
        const idx = parseInt(dot.getAttribute('data-carousel-dot') || '0', 10);
        goTo(idx);
        startAuto();
      });
    });

    root.addEventListener('mouseenter', stopAuto);
    root.addEventListener('mouseleave', startAuto);
    root.addEventListener('focusin', stopAuto);
    root.addEventListener('focusout', startAuto);

    document.addEventListener('visibilitychange', () => {
      if (document.hidden) {
        stopAuto();
      } else {
        startAuto();
      }
    });

    startAuto();
  }

  // ---------------------------------------------------------------------------
  // 8c. Article Sharing (Native Web Share API + Copy Link & Social Popover)
  // ---------------------------------------------------------------------------
  function initArticleShare() {
    async function copyText(url, btn) {
      try {
        if (navigator.clipboard && navigator.clipboard.writeText) {
          await navigator.clipboard.writeText(url);
        } else {
          const ta = document.createElement('textarea');
          ta.value = url;
          document.body.appendChild(ta);
          ta.select();
          document.execCommand('copy');
          ta.remove();
        }
        const span = btn.querySelector('span');
        if (span) {
          const orig = span.textContent;
          span.textContent = 'Link Copied!';
          setTimeout(() => { span.textContent = orig; }, 2200);
        }
      } catch (err) {}
    }

    document.addEventListener('click', async (e) => {
      // Popover share button
      const shareBtn = e.target.closest('[data-share-btn]');
      if (shareBtn) {
        e.preventDefault();
        e.stopPropagation();
        const wrap = shareBtn.closest('[data-share-wrap]');
        const title = shareBtn.getAttribute('data-share-title') || document.title;
        const text = shareBtn.getAttribute('data-share-text') || '';
        const url = shareBtn.getAttribute('data-share-url') || window.location.href;

        // Use native Web Share sheet on touch/mobile devices when supported
        if (navigator.share && window.matchMedia('(pointer: coarse)').matches) {
          try {
            await navigator.share({ title, text, url });
            return;
          } catch (err) {}
        }

        const open = !wrap.classList.contains('is-open');
        document.querySelectorAll('[data-share-wrap].is-open').forEach((w) => w.classList.remove('is-open'));
        wrap.classList.toggle('is-open', open);
        shareBtn.setAttribute('aria-expanded', open ? 'true' : 'false');
        return;
      }

      // Copy link inside popover
      const copyBtn = e.target.closest('[data-share-copy]');
      if (copyBtn) {
        e.preventDefault();
        await copyText(copyBtn.getAttribute('data-share-copy') || window.location.href, copyBtn);
        return;
      }

      // Quick share buttons on cards & article footer
      const quickBtn = e.target.closest('[data-quick-share]');
      if (quickBtn) {
        e.preventDefault();
        const title = quickBtn.getAttribute('data-share-title') || document.title;
        const text = quickBtn.getAttribute('data-share-text') || '';
        const url = quickBtn.getAttribute('data-share-url') || window.location.href;
        if (navigator.share) {
          try {
            await navigator.share({ title, text, url });
            return;
          } catch (err) {}
        }
        await copyText(url, quickBtn);
        return;
      }

      // Close open share popovers when clicking outside
      if (!e.target.closest('[data-share-wrap]')) {
        document.querySelectorAll('[data-share-wrap].is-open').forEach((w) => {
          w.classList.remove('is-open');
          const b = w.querySelector('[data-share-btn]');
          if (b) b.setAttribute('aria-expanded', 'false');
        });
      }
    });
  }

  // ---------------------------------------------------------------------------
  // 9. FAQ Live AJAX Filtering & Smooth Accordion
  // ---------------------------------------------------------------------------
  function initFaqModule() {
    const list = document.getElementById('faq-list');
    if (!list) return;

    const searchInput = document.getElementById('faq-search');
    const pills = document.querySelectorAll('[data-faq-category]');
    const countLabel = document.getElementById('faq-count');
    let currentCategory = 'all';
    let currentQuery = '';
    let debounceTimer = null;

    function escapeHtml(str) {
      return String(str || '')
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;');
    }

    function toggleFaqItem(item) {
      const panel = item.querySelector('.faq-panel');
      const trigger = item.querySelector('.faq-trigger');
      const isOpen = item.classList.contains('is-open');

      // Collapse siblings for clean reading
      list.querySelectorAll('.faq-item.is-open').forEach((other) => {
        if (other !== item) {
          other.classList.remove('is-open');
          const op = other.querySelector('.faq-panel');
          const ot = other.querySelector('.faq-trigger');
          if (op) op.style.maxHeight = '0px';
          if (ot) ot.setAttribute('aria-expanded', 'false');
        }
      });

      if (isOpen) {
        item.classList.remove('is-open');
        if (panel) panel.style.maxHeight = '0px';
        if (trigger) trigger.setAttribute('aria-expanded', 'false');
      } else {
        item.classList.add('is-open');
        if (panel) panel.style.maxHeight = panel.scrollHeight + 'px';
        if (trigger) trigger.setAttribute('aria-expanded', 'true');
      }
    }

    list.addEventListener('click', (e) => {
      const trigger = e.target.closest('.faq-trigger');
      if (!trigger) return;
      toggleFaqItem(trigger.closest('.faq-item'));
    });

    async function fetchFaqs() {
      list.setAttribute('aria-busy', 'true');
      const params = new URLSearchParams();
      if (currentCategory && currentCategory !== 'all') params.set('category', currentCategory);
      if (currentQuery.trim() !== '') params.set('q', currentQuery.trim());

      try {
        const res = await fetch(apiUrl('inc/faqs/index.php?' + params.toString()), {
          headers: { Accept: 'application/json' },
        });
        const data = await res.json();
        if (!data.ok) return;

        if (countLabel) {
          countLabel.textContent = `${data.total} question${data.total === 1 ? '' : 's'}`;
        }

        if (!data.items.length) {
          list.innerHTML =
            '<div class="glass-card" style="padding:32px;text-align:center;"><h3>No matching questions found</h3><p style="margin-bottom:0;">Try a different keyword or contact our Harare team directly.</p></div>';
          return;
        }

        list.innerHTML = data.items
          .map(
            (faq, idx) => `
          <div class="glass-card faq-item${idx === 0 ? ' is-open' : ''}">
            <button type="button" class="faq-trigger" aria-expanded="${idx === 0 ? 'true' : 'false'}">
              <span class="faq-trigger-left">
                <span class="faq-icon-badge"><i data-lucide="${escapeHtml(faq.icon_key || 'help-circle')}"></i></span>
                <span>${escapeHtml(faq.question)}</span>
              </span>
              <i data-lucide="chevron-down" class="faq-chevron"></i>
            </button>
            <div class="faq-panel" style="${idx === 0 ? 'max-height:480px;' : ''}">
              <div class="faq-panel-inner">
                <p style="margin-bottom:10px;">${escapeHtml(faq.answer)}</p>
                <span class="category-badge">${escapeHtml(faq.category)}</span>
              </div>
            </div>
          </div>`
          )
          .join('');
        refreshIcons();
      } catch (err) {
        // Keep SSR content on network error
      } finally {
        list.setAttribute('aria-busy', 'false');
      }
    }

    pills.forEach((pill) => {
      pill.addEventListener('click', () => {
        pills.forEach((p) => p.classList.remove('is-active'));
        pill.classList.add('is-active');
        currentCategory = pill.getAttribute('data-faq-category') || 'all';
        fetchFaqs();
      });
    });

    if (searchInput) {
      searchInput.addEventListener('input', () => {
        currentQuery = searchInput.value;
        clearTimeout(debounceTimer);
        debounceTimer = setTimeout(fetchFaqs, 240);
      });
    }
  }

  // ---------------------------------------------------------------------------
  // Bootstrap on DOMContentLoaded
  // ---------------------------------------------------------------------------
  document.addEventListener('DOMContentLoaded', () => {
    refreshIcons();
    initTheme();
    initNavigation();
    initFlipCards();
    initModals();
    initFlatpickr();
    initAjaxForms();
    initArticlesModule();
    initArticleShare();
    initHeroCarousel();
    initFaqModule();
    initScrollAnimations();
  });
})();
