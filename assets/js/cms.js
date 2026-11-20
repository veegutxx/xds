(function () {
  'use strict';

  const cfg = window.XDS_CMS_CONFIG || {};
  const workspace = document.getElementById('cms-workspace');
  const breadcrumbEl = document.getElementById('cms-breadcrumb-current');
  const toastStack = document.getElementById('cms-toast-stack');
  const modalEl = document.getElementById('cms-modal');
  const modalTitleEl = document.getElementById('cms-modal-title');
  const modalBodyEl = document.getElementById('cms-modal-body');

  const VIEW_LABELS = {
    dashboard: 'Dashboard',
    home: 'Home Page',
    about: 'About & Why XDS',
    credit: 'Credit Information Services',
    agricultural: 'Agricultural Services',
    authentication: 'Authentication Services (E Check)',
    privacy: 'Data Privacy (ISSP)',
    articles: 'Articles & Insights',
    faqs: 'Frequently Asked Questions',
    inquiries: 'Inquiries & Contact Page',
    system: 'Site Settings & Portals',
    users: 'Portal Users',
  };

  let state = null;
  const currentView = cfg.view || 'dashboard';
  const routes = cfg.routes || {
    dashboard: (cfg.basePath || '/') + 'cms/',
    inquiries: (cfg.basePath || '/') + 'cms/contact/',
    home: (cfg.basePath || '/') + 'cms/home/',
    about: (cfg.basePath || '/') + 'cms/about/',
    credit: (cfg.basePath || '/') + 'cms/products/credit-information/',
    agricultural: (cfg.basePath || '/') + 'cms/products/agricultural-services/',
    authentication: (cfg.basePath || '/') + 'cms/products/authentication-services/',
    privacy: (cfg.basePath || '/') + 'cms/resources/data-privacy/',
    articles: (cfg.basePath || '/') + 'cms/resources/articles/',
    article_editor: (cfg.basePath || '/') + 'cms/resources/articles/editor/',
    faqs: (cfg.basePath || '/') + 'cms/resources/faq/',
    system: (cfg.basePath || '/') + 'cms/settings/',
    users: (cfg.basePath || '/') + 'cms/users/',
  };
  let inquiryFilter = 'all';
  let inquiryTab = 'inbox';

  function esc(val) {
    return String(val ?? '')
      .replace(/&/g, '&amp;')
      .replace(/</g, '&lt;')
      .replace(/>/g, '&gt;')
      .replace(/"/g, '&quot;');
  }

  function parseJson(val, fallback) {
    if (Array.isArray(val) || (val && typeof val === 'object')) return val;
    if (typeof val === 'string' && val.trim() !== '') {
      try {
        return JSON.parse(val);
      } catch (e) {}
    }
    return fallback;
  }

  function refreshIcons() {
    if (window.lucide && typeof window.lucide.createIcons === 'function') {
      window.lucide.createIcons();
    }
  }

  function toast(message, isError) {
    const el = document.createElement('div');
    el.className = 'cms-toast' + (isError ? ' is-error' : '');
    el.textContent = message;
    toastStack.appendChild(el);
    setTimeout(() => {
      el.style.opacity = '0';
      setTimeout(() => el.remove(), 240);
    }, 4200);
  }

  function openModal(title, html) {
    modalTitleEl.textContent = title;
    modalBodyEl.innerHTML = html;
    modalEl.classList.add('is-open');
    modalEl.setAttribute('aria-hidden', 'false');
    refreshIcons();
  }

  function closeModal() {
    modalEl.classList.remove('is-open');
    modalEl.setAttribute('aria-hidden', 'true');
    modalBodyEl.innerHTML = '';
  }

  async function apiGet() {
    const res = await fetch(cfg.apiUrl + '?action=bootstrap', {
      headers: { Accept: 'application/json' },
    });
    const data = await res.json();
    if (!res.ok || !data.ok) {
      throw new Error(data.message || 'Failed to load CMS data.');
    }
    return data;
  }

  async function apiPost(action, payload) {
    const body = Object.assign({ action: action, csrf_token: cfg.csrfToken }, payload || {});
    const res = await fetch(cfg.apiUrl, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'X-CSRF-Token': cfg.csrfToken || '',
        Accept: 'application/json',
      },
      body: JSON.stringify(body),
    });
    const data = await res.json();
    if (!res.ok || !data.ok) {
      throw new Error(data.message || 'Action failed.');
    }
    return data;
  }

  async function uploadAssetFile(file, opts) {
    const fd = new FormData();
    fd.append('action', 'upload_asset');
    fd.append('csrf_token', cfg.csrfToken || '');
    fd.append('target', opts.target || 'image');
    if (opts.slug) fd.append('slug', opts.slug);
    if (opts.purpose) fd.append('purpose', opts.purpose);
    if (opts.doc_folder) fd.append('doc_folder', opts.doc_folder);
    fd.append('file', file);

    const res = await fetch(cfg.apiUrl, {
      method: 'POST',
      headers: { 'X-CSRF-Token': cfg.csrfToken || '', Accept: 'application/json' },
      body: fd,
    });
    const data = await res.json();
    if (!res.ok || !data.ok) {
      throw new Error(data.message || 'Upload failed.');
    }
    return data;
  }

  function renderUploadField(opts) {
    const previewUrl = opts.previewUrl || '';
    const isImg = opts.target !== 'document';
    return `
      <div class="cms-field">
        <label>${esc(opts.label)}</label>
        <div class="cms-upload-row">
          ${isImg && previewUrl ? `<img class="cms-upload-preview" data-preview-for="${esc(opts.name)}" src="${esc(previewUrl)}" alt="">` : ''}
          <div class="cms-upload-controls">
            <input type="text" class="cms-input" name="${esc(opts.name)}" value="${esc(opts.value || '')}" placeholder="${esc(opts.placeholder || 'filename.webp')}">
            <label class="cms-btn cms-btn-ghost cms-btn-sm" style="margin:0;cursor:pointer;">
              <i data-lucide="upload"></i>
              <span>Upload</span>
              <input type="file" hidden data-cms-upload-input data-target="${esc(opts.target || 'image')}" data-field="${esc(opts.name)}" data-slug-field="${esc(opts.slugField || '')}" data-purpose="${esc(opts.purpose || '')}" data-doc-folder="${esc(opts.docFolder || '')}" accept="${isImg ? '.png,.jpg,.jpeg,.webp,.svg' : '.pdf'}">
            </label>
          </div>
        </div>
      </div>
    `;
  }

  function bindUploadInputs(container) {
    container.querySelectorAll('[data-cms-upload-input]').forEach((fileInput) => {
      fileInput.addEventListener('change', async () => {
        const file = fileInput.files && fileInput.files[0];
        if (!file) return;
        const fieldName = fileInput.getAttribute('data-field');
        const target = fileInput.getAttribute('data-target') || 'image';
        const slugField = fileInput.getAttribute('data-slug-field');
        const slugVal = slugField
          ? (container.querySelector(`[name="${slugField}"]`)?.value || '').trim()
          : '';
        try {
          toast('Uploading ' + file.name + '...');
          const res = await uploadAssetFile(file, {
            target: target,
            slug: slugVal,
            purpose: fileInput.getAttribute('data-purpose') || '',
            doc_folder: fileInput.getAttribute('data-doc-folder') || '',
          });
          const textInput = container.querySelector(`input[name="${fieldName}"]`);
          if (textInput) textInput.value = res.filename;
          const preview = container.querySelector(`[data-preview-for="${fieldName}"]`);
          if (preview && res.preview_url) preview.src = res.preview_url + '?t=' + Date.now();
          toast(res.message);
        } catch (err) {
          toast(err.message, true);
        } finally {
          fileInput.value = '';
        }
      });
    });
  }

  function statusBadge(status) {
    const map = {
      new: 'cms-badge-lime',
      in_review: 'cms-badge-amber',
      responded: 'cms-badge-indigo',
      archived: 'cms-badge-muted',
    };
    const label = String(status || 'new').replace('_', ' ');
    return `<span class="cms-badge ${map[status] || 'cms-badge-muted'}">${esc(label)}</span>`;
  }

  function updateSidebarBadges() {
    if (!state) return;
    const k = state.kpis || {};
    const bInq = document.getElementById('nav-badge-inquiries');
    const bArt = document.getElementById('nav-badge-articles');
    const bFaq = document.getElementById('nav-badge-faqs');
    if (bInq) bInq.textContent = k.inquiries_new || k.inquiries_total || 0;
    if (bArt) bArt.textContent = k.articles_total || 0;
    if (bFaq) bFaq.textContent = k.faqs_total || 0;

    const dbPill = document.getElementById('cms-db-pill');
    const dbLbl = document.getElementById('cms-db-label');
    if (dbPill && dbLbl) {
      dbPill.className = 'portal-status-pill ' + (state.db_connected ? 'is-live' : 'is-fallback');
      dbLbl.textContent = state.db_connected ? 'MySQL Live' : 'DB Offline';
    }
  }

  async function reloadState(keepView) {
    state = await apiGet();
    updateSidebarBadges();
    if (keepView) renderCurrentView();
  }

  // ===========================================================================
  // VIEW 1: Dashboard (FlowAI / Repliq Inspired)
  // ===========================================================================
  function renderDashboard() {
    const k = state.kpis;
    const firstName = ((cfg.user && cfg.user.full_name) || 'Admin').split(' ')[0];
    const recentInquiries = (state.inquiries || []).slice(0, 6);
    const recentActivity = (state.activity || []).slice(0, 6);

    const quickPages = [
      { key: 'home', icon: 'home', title: 'Home Page', desc: 'Hero business insights carousel, intro & 3D flip service cards.', url: cfg.basePath },
      { key: 'about', icon: 'sliders-horizontal', title: 'About & Why XDS', desc: 'History milestones, Philosophy, People & 4 Key Value Drivers.', url: cfg.basePath + 'about/' },
      { key: 'credit', icon: 'file-spreadsheet', title: 'Credit Information', desc: 'Credit lifecycle offerings, CTAs & partners (UNTU, Yambukai).', url: cfg.basePath + 'products/credit-information/' },
      { key: 'agricultural', icon: 'tractor', title: 'Agricultural Services', desc: 'Agro scoring diagram, 6 pillars, double-dip & side-marketing alerts.', url: cfg.basePath + 'products/agricultural-services/' },
      { key: 'authentication', icon: 'shield-check', title: 'Authentication (E Check)', desc: '4 verification modules, E Check badge & 6 screening partners.', url: cfg.basePath + 'products/authentication-services/' },
      { key: 'privacy', icon: 'lock-keyhole', title: 'Data Privacy (ISSP)', desc: 'Information security mission, access philosophy, objectives & PDF.', url: cfg.basePath + 'resources/data-privacy/' },
    ];

    workspace.innerHTML = `
      <div class="cms-page-head">
        <div>
          <h1>Welcome back, ${esc(firstName)}!</h1>
          <p>Manage XDS Zimbabwe website content, publications, FAQs, inquiries, and portal accounts in real time.</p>
        </div>
        <div style="display:flex;align-items:center;gap:10px;flex-wrap:wrap;">
          <span class="cms-date-pill"><i data-lucide="calendar"></i>${esc(cfg.today || '')}</span>
          <a href="${esc(routes.article_editor)}" class="cms-btn cms-btn-lime">
            <i data-lucide="plus"></i><span>Create Article</span>
          </a>
        </div>
      </div>

      ${!state.db_connected ? `
      <div class="cms-alert is-visible is-error">
        MySQL database <strong>inungutech_xds</strong> is currently unreachable. Import <code>database/inungutech_xds.sql</code> and configure credentials in <code>inc/config/index.php</code> (or <code>inc/config/db.local.php</code>) so CMS edits persist live to the public website.
      </div>` : ''}

      <div class="cms-kpi-grid">
        <div class="cms-kpi-card">
          <div class="cms-kpi-top"><span class="cms-kpi-icon"><i data-lucide="layout-template"></i></span><span>Managed Pages</span></div>
          <div class="cms-kpi-value">${esc(k.managed_pages)}</div>
          <div class="cms-kpi-sub">Public Website Pages</div>
        </div>
        <div class="cms-kpi-card">
          <div class="cms-kpi-top"><span class="cms-kpi-icon"><i data-lucide="newspaper"></i></span><span>Articles &amp; Insights</span></div>
          <div class="cms-kpi-value">${esc(k.articles_active)}<span style="font-size:1rem;color:var(--cms-muted);">/${esc(k.articles_total)}</span></div>
          <div class="cms-kpi-sub">Published Articles</div>
        </div>
        <div class="cms-kpi-card">
          <div class="cms-kpi-top"><span class="cms-kpi-icon"><i data-lucide="inbox"></i></span><span>Contact Inquiries</span></div>
          <div class="cms-kpi-value">${esc(k.inquiries_total)}</div>
          <div class="cms-kpi-sub">Awaiting review</div>
        </div>
        <div class="cms-kpi-card">
          <div class="cms-kpi-top"><span class="cms-kpi-icon"><i data-lucide="users"></i></span><span>Portal Accounts</span></div>
          <div class="cms-kpi-value">${esc(k.users_total)}</div>
          <div class="cms-kpi-sub">Users</div>
        </div>
      </div>

      <!-- Recent Inquiries Table (Repliq Style) -->
      <div class="cms-card">
        <div class="cms-card-header">
          <div>
            <h2>Recent Contact &amp; Portal Inquiries</h2>
            <p>Live submissions from Contact, Credit Services modals, and Credit Portal subscribers.</p>
          </div>
          <a href="${esc(routes.inquiries)}" class="cms-btn cms-btn-ghost cms-btn-sm">View All Inquiries</a>
        </div>
        <div class="cms-table-wrap">
          <table class="cms-table">
            <thead>
              <tr>
                <th>Reference</th>
                <th>Contact</th>
                <th>Need / Category</th>
                <th>Callback Slot</th>
                <th>Status</th>
                <th>Action</th>
              </tr>
            </thead>
            <tbody>
              ${recentInquiries.length ? recentInquiries.map((inq) => `
                <tr>
                  <td><strong>${esc(inq.reference_code)}</strong></td>
                  <td>
                    <div style="font-weight:600;">${esc(inq.firstname)} ${esc(inq.lastname)}</div>
                    <div style="font-size:0.78rem;color:var(--cms-muted);">${esc(inq.email)}</div>
                  </td>
                  <td>${esc(inq.need_category)}</td>
                  <td>${inq.preferred_date ? `${esc(inq.preferred_date)} ${esc(inq.preferred_time || '')}` : '<span style="color:var(--cms-muted);">—</span>'}</td>
                  <td>${statusBadge(inq.status)}</td>
                  <td>
                    <button type="button" class="cms-btn cms-btn-ghost cms-btn-sm" data-view-inquiry="${inq.id}">View</button>
                  </td>
                </tr>
              `).join('') : `
                <tr><td colspan="6" style="text-align:center;color:var(--cms-muted);padding:24px;">No inquiries submitted yet. Test the Contact page form to see entries arrive here live.</td></tr>
              `}
            </tbody>
          </table>
        </div>
      </div>

      <!-- Quick Page Manager Cards (FlowAI Bottom Cards Style) -->
      <div class="cms-card">
        <div class="cms-card-header">
          <div>
            <h2>Public Website Pages</h2>
            <p>Click "Edit Page" to manage page details.</p>
          </div>
        </div>
        <div class="cms-quick-grid">
          ${quickPages.map((pg) => `
            <div class="cms-quick-card">
              <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:10px;">
                <span class="cms-kpi-icon"><i data-lucide="${esc(pg.icon)}"></i></span>
                <span class="cms-badge cms-badge-lime">Live</span>
              </div>
              <h3>${esc(pg.title)}</h3>
              <p>${esc(pg.desc)}</p>
              <div style="margin-top:auto;display:flex;gap:8px;">
                <a href="${esc(routes[pg.key] || routes.dashboard)}" class="cms-btn cms-btn-indigo cms-btn-sm" style="flex:1;">Edit Page</a>
                <a href="${esc(pg.url)}" target="_blank" class="cms-btn cms-btn-ghost cms-btn-sm" aria-label="View ${esc(pg.title)}"><i data-lucide="external-link"></i></a>
              </div>
            </div>
          `).join('')}
        </div>
      </div>

      ${recentActivity.length ? `
      <div class="cms-card">
        <div class="cms-card-header">
          <div><h2>Recent CMS Activity</h2><p>Audit log of recent updates across XDS Zimbabwe tables.</p></div>
        </div>
        <div class="cms-table-wrap">
          <table class="cms-table">
            <thead><tr><th>User</th><th>Section</th><th>Action</th><th>Summary</th><th>Time</th></tr></thead>
            <tbody>
              ${recentActivity.map((act) => `
                <tr>
                  <td><strong>${esc(act.user_name)}</strong></td>
                  <td><span class="cms-badge cms-badge-indigo">${esc(act.section)}</span></td>
                  <td>${esc(act.action)}</td>
                  <td>${esc(act.summary)}</td>
                  <td style="color:var(--cms-muted);white-space:nowrap;">${esc(act.created_at)}</td>
                </tr>
              `).join('')}
            </tbody>
          </table>
        </div>
      </div>` : ''}
    `;

    workspace.querySelectorAll('[data-view-inquiry]').forEach((b) => {
      b.addEventListener('click', () => {
        const id = parseInt(b.getAttribute('data-view-inquiry'), 10);
        const found = (state.inquiries || []).find((x) => Number(x.id) === id);
        if (found) openInquiryModal(found);
      });
    });
  }

  // ===========================================================================
  // VIEW 2: Home Page Editor (`page_home`)
  // ===========================================================================
  function renderHomeEditor() {
    const h = state.pages.home;
    const slides = parseJson(h.hero_slides_json, []);
    const services = parseJson(h.services_json, []);

    workspace.innerHTML = `
      <div class="cms-page-head">
        <div><h1>Home Page Editor</h1></div>
        <a href="${esc(cfg.basePath)}" target="_blank" class="cms-btn cms-btn-ghost cms-btn-sm"><i data-lucide="external-link"></i><span>Preview Home</span></a>
      </div>

      <form id="cms-form-home">
        <div class="cms-card">
          <div class="cms-card-header">
            <div><h2>Hero Background &amp; Insights Carousel</h2><p>Edit the Home page details</p></div>
            <button type="button" class="cms-btn cms-btn-ghost cms-btn-sm" id="add-hero-slide"><i data-lucide="plus"></i><span>Add Slide</span></button>
          </div>
          ${renderUploadField({
            label: 'Hero Background Image',
            name: 'hero_image_file',
            value: h.hero_image_file,
            target: 'image',
            previewUrl: state.asset_urls.images + (h.hero_image_file || 'hero-home.webp'),
          })}
          <div class="cms-repeater-list" id="home-slides-list">
            ${slides.map((s, i) => heroSlideItemHtml(s, i)).join('')}
          </div>
        </div>

        <div class="cms-card">
          <div class="cms-card-header"><div><h2>Introduction &amp; CTA Banner</h2><p>Zimbabwe overview block and bottom call-to-action strip.</p></div></div>
          <div class="cms-form-row-2">
            <div class="cms-field"><label>Intro Title</label><input class="cms-input" name="intro_title" value="${esc(h.intro_title)}"></div>
            <div class="cms-field"><label>Intro Subtitle</label><input class="cms-input" name="intro_subtitle" value="${esc(h.intro_subtitle)}"></div>
          </div>
          <div class="cms-field"><label>Intro Body</label><textarea class="cms-textarea" name="intro_body">${esc(h.intro_body)}</textarea></div>
          <div class="cms-form-row-2">
            <div class="cms-field"><label>Services Section Heading</label><input class="cms-input" name="services_heading" value="${esc(h.services_heading)}"></div>
            <div class="cms-field"><label>Services Subheading</label><input class="cms-input" name="services_subheading" value="${esc(h.services_subheading)}"></div>
          </div>
          <div class="cms-form-row-3">
            <div class="cms-field"><label>CTA Banner Title</label><input class="cms-input" name="cta_banner_title" value="${esc(h.cta_banner_title)}"></div>
            <div class="cms-field"><label>CTA Button Label</label><input class="cms-input" name="cta_banner_button" value="${esc(h.cta_banner_button)}"></div>
            <div class="cms-field"><label>Hero Fallback Badge</label><input class="cms-input" name="hero_badge" value="${esc(h.hero_badge)}"></div>
          </div>
          <div class="cms-field"><label>CTA Banner Copy</label><textarea class="cms-textarea" name="cta_banner_copy">${esc(h.cta_banner_copy)}</textarea></div>
        </div>

        <div class="cms-card">
          <div class="cms-card-header"><div><h2>3D Flip Service Cards (Front &amp; Back)</h2><p>Edit the 3 service cards displayed on the Home page.</p></div></div>
          <div class="cms-repeater-list" id="home-services-list">
            ${services.map((svc, idx) => `
              <div class="cms-repeater-item" data-home-service>
                <div class="cms-repeater-head"><strong>Service Card ${idx + 1}: ${esc(svc.title)}</strong></div>
                <input type="hidden" data-s-key value="${esc(svc.key || '')}">
                <div class="cms-form-row-3">
                  <div class="cms-field"><label>Title</label><input class="cms-input" data-s-title value="${esc(svc.title)}"></div>
                  <div class="cms-field"><label>Lucide Icon</label><input class="cms-input" data-s-icon value="${esc(svc.icon || 'briefcase')}"></div>
                  <div class="cms-field"><label>Target Link</label><input class="cms-input" data-s-href value="${esc(svc.href || '')}"></div>
                </div>
                <div class="cms-field"><label>Front Summary</label><textarea class="cms-textarea" rows="2" data-s-summary>${esc(svc.summary)}</textarea></div>
                <div class="cms-form-row-3">
                  <div class="cms-field"><label>Back Face Heading</label><input class="cms-input" data-s-back-title value="${esc(svc.back_title || '')}"></div>
                  <div class="cms-field"><label>CTA Label</label><input class="cms-input" data-s-cta value="${esc(svc.cta_label || 'Find Out More')}"></div>
                  <div class="cms-field"><label>Optional Badge Filename</label><input class="cms-input" data-s-badge value="${esc(svc.badge_file || '')}" placeholder="badge-echeck.png"></div>
                </div>
                <div class="cms-field" style="margin-bottom:0;"><label>Back Face Highlights (one per line)</label><textarea class="cms-textarea" rows="3" data-s-highlights>${esc((svc.highlights || []).join('\n'))}</textarea></div>
              </div>
            `).join('')}
          </div>
        </div>

        <div class="cms-card">
          <div class="cms-card-header"><div><h2>SEO Metadata</h2></div></div>
          <div class="cms-field"><label>Meta Title</label><input class="cms-input" name="meta_title" value="${esc(h.meta_title)}"></div>
          <div class="cms-field"><label>Meta Description</label><textarea class="cms-textarea" rows="2" name="meta_description">${esc(h.meta_description)}</textarea></div>
          <button type="submit" class="cms-btn cms-btn-lime"><i data-lucide="save"></i><span>Save Home Page</span></button>
        </div>
      </form>
    `;

    const form = document.getElementById('cms-form-home');
    bindUploadInputs(form);

    document.getElementById('add-hero-slide').addEventListener('click', () => {
      const list = document.getElementById('home-slides-list');
      const idx = list.querySelectorAll('[data-hero-slide-row]').length;
      list.insertAdjacentHTML('beforeend', heroSlideItemHtml({}, idx));
      bindSlideRemoves(list);
      refreshIcons();
    });
    bindSlideRemoves(document.getElementById('home-slides-list'));

    form.addEventListener('submit', async (e) => {
      e.preventDefault();
      const fd = new FormData(form);
      const heroSlides = Array.from(form.querySelectorAll('[data-hero-slide-row]')).map((row) => ({
        eyebrow: row.querySelector('[data-sl-eyebrow]').value.trim(),
        headline: row.querySelector('[data-sl-headline]').value.trim(),
        insight: row.querySelector('[data-sl-insight]').value.trim(),
        primary_label: row.querySelector('[data-sl-plabel]').value.trim(),
        primary_href: row.querySelector('[data-sl-phref]').value.trim(),
        secondary_label: row.querySelector('[data-sl-slabel]').value.trim(),
        secondary_href: row.querySelector('[data-sl-shref]').value.trim(),
      })).filter((s) => s.headline !== '');

      const svcArr = Array.from(form.querySelectorAll('[data-home-service]')).map((row) => ({
        key: row.querySelector('[data-s-key]').value.trim(),
        icon: row.querySelector('[data-s-icon]').value.trim() || 'briefcase',
        title: row.querySelector('[data-s-title]').value.trim(),
        summary: row.querySelector('[data-s-summary]').value.trim(),
        back_title: row.querySelector('[data-s-back-title]').value.trim(),
        highlights: row.querySelector('[data-s-highlights]').value.split('\n').map((x) => x.trim()).filter(Boolean),
        badge_file: row.querySelector('[data-s-badge]').value.trim() || null,
        cta_label: row.querySelector('[data-s-cta]').value.trim() || 'Find Out More',
        href: row.querySelector('[data-s-href]').value.trim() || '/',
      }));

      try {
        const res = await apiPost('save_home', {
          hero_badge: fd.get('hero_badge'),
          hero_headline: heroSlides[0]?.headline || h.hero_headline,
          hero_subheadline: heroSlides[0]?.insight || h.hero_subheadline,
          hero_image_file: fd.get('hero_image_file'),
          hero_slides: heroSlides,
          intro_title: fd.get('intro_title'),
          intro_subtitle: fd.get('intro_subtitle'),
          intro_body: fd.get('intro_body'),
          services_heading: fd.get('services_heading'),
          services_subheading: fd.get('services_subheading'),
          services: svcArr,
          cta_banner_title: fd.get('cta_banner_title'),
          cta_banner_copy: fd.get('cta_banner_copy'),
          cta_banner_button: fd.get('cta_banner_button'),
          meta_title: fd.get('meta_title'),
          meta_description: fd.get('meta_description'),
        });
        toast(res.message);
        await reloadState(false);
      } catch (err) {
        toast(err.message, true);
      }
    });
  }

  function heroSlideItemHtml(s, i) {
    return `
      <div class="cms-repeater-item" data-hero-slide-row>
        <div class="cms-repeater-head">
          <strong>Carousel Slide ${i + 1}</strong>
          <button type="button" class="cms-btn cms-btn-danger cms-btn-sm" data-remove-row>Remove</button>
        </div>
        <div class="cms-form-row-2">
          <div class="cms-field"><label>Kicker / Topic</label><input class="cms-input" data-sl-eyebrow value="${esc(s.eyebrow || '')}" placeholder="Credit Bureau Intelligence"></div>
          <div class="cms-field"><label>Headline</label><input class="cms-input" data-sl-headline value="${esc(s.headline || '')}"></div>
        </div>
        <div class="cms-field"><label>Business Insight Copy</label><textarea class="cms-textarea" rows="2" data-sl-insight>${esc(s.insight || '')}</textarea></div>
        <div class="cms-form-row-2">
          <div class="cms-field"><label>Primary CTA Label</label><input class="cms-input" data-sl-plabel value="${esc(s.primary_label || '')}"></div>
          <div class="cms-field"><label>Primary CTA Link</label><input class="cms-input" data-sl-phref value="${esc(s.primary_href || '/products/credit-information/')}"></div>
        </div>
        <div class="cms-form-row-2">
          <div class="cms-field" style="margin-bottom:0;"><label>Secondary CTA Label</label><input class="cms-input" data-sl-slabel value="${esc(s.secondary_label || '')}"></div>
          <div class="cms-field" style="margin-bottom:0;"><label>Secondary CTA Link</label><input class="cms-input" data-sl-shref value="${esc(s.secondary_href || '/contact/')}"></div>
        </div>
      </div>
    `;
  }

  function bindSlideRemoves(container) {
    container.querySelectorAll('[data-remove-row]').forEach((btn) => {
      btn.onclick = () => btn.closest('.cms-repeater-item')?.remove();
    });
  }

  // ===========================================================================
  // VIEW 3: About & Why XDS Editor (`page_about`)
  // ===========================================================================
  function renderAboutEditor() {
    const a = state.pages.about;
    const paras = parseJson(a.history_paragraphs_json, []);
    const stats = parseJson(a.footprint_stats_json, []);
    const drivers = parseJson(a.value_drivers_json, []);

    workspace.innerHTML = `
      <div class="cms-page-head">
        <div><h1>About Us Editor</h1></div>
        <a href="${esc(cfg.basePath)}about/" target="_blank" class="cms-btn cms-btn-ghost cms-btn-sm"><i data-lucide="external-link"></i><span>Preview About</span></a>
      </div>
      <form id="cms-form-about">
        <div class="cms-card">
          <div class="cms-card-header"><div><h2>Banner &amp; Overview</h2></div></div>
          ${renderUploadField({ label: 'Banner Image', name: 'banner_image_file', value: a.banner_image_file, target: 'image', previewUrl: state.asset_urls.images + a.banner_image_file })}
          <div class="cms-form-row-2">
            <div class="cms-field"><label>Overview Title</label><input class="cms-input" name="overview_title" value="${esc(a.overview_title)}"></div>
            <div class="cms-field"><label>Overview Subtitle</label><input class="cms-input" name="overview_subtitle" value="${esc(a.overview_subtitle)}"></div>
          </div>
          <div class="cms-form-row-2">
            ${stats.map((st, idx) => `
              <div class="cms-repeater-item" data-about-stat>
                <div class="cms-repeater-head"><strong>Stat ${idx + 1}</strong></div>
                <div class="cms-form-row-3">
                  <div class="cms-field" style="margin-bottom:0;"><label>Metric</label><input class="cms-input" data-st-metric value="${esc(st.metric)}"></div>
                  <div class="cms-field" style="margin-bottom:0;"><label>Label</label><input class="cms-input" data-st-label value="${esc(st.label)}"></div>
                  <div class="cms-field" style="margin-bottom:0;"><label>Icon</label><input class="cms-input" data-st-icon value="${esc(st.icon || 'check-circle')}"></div>
                </div>
              </div>
            `).join('')}
          </div>
          <div style="margin-top:16px;">
            ${paras.map((p, idx) => `
              <div class="cms-field"><label>History Milestone Paragraph ${idx + 1}</label><textarea class="cms-textarea" rows="3" data-about-para>${esc(p)}</textarea></div>
            `).join('')}
          </div>
        </div>

        <div class="cms-card">
          <div class="cms-card-header"><div><h2>Why XDS — Philosophy, People &amp; Value Drivers</h2></div></div>
          <div class="cms-form-row-2">
            <div class="cms-field"><label>Why XDS Heading</label><input class="cms-input" name="why_title" value="${esc(a.why_title)}"></div>
            <div class="cms-field"><label>Value Drivers Heading</label><input class="cms-input" name="value_drivers_title" value="${esc(a.value_drivers_title)}"></div>
          </div>
          <div class="cms-form-row-2">
            <div class="cms-field"><label>Philosophy Heading</label><input class="cms-input" name="philosophy_title" value="${esc(a.philosophy_title)}"></div>
            <div class="cms-field"><label>People Heading</label><input class="cms-input" name="people_title" value="${esc(a.people_title)}"></div>
          </div>
          <div class="cms-form-row-2">
            <div class="cms-field"><label>Our Philosophy Body</label><textarea class="cms-textarea" name="philosophy_body">${esc(a.philosophy_body)}</textarea></div>
            <div class="cms-field"><label>Our People Body</label><textarea class="cms-textarea" name="people_body">${esc(a.people_body)}</textarea></div>
          </div>
          <div class="cms-repeater-list">
            ${drivers.map((d, i) => `
              <div class="cms-repeater-item" data-about-driver>
                <div class="cms-repeater-head"><strong>Value Driver ${i + 1}: ${esc(d.title)}</strong></div>
                <div class="cms-form-row-2">
                  <div class="cms-field"><label>Title</label><input class="cms-input" data-vd-title value="${esc(d.title)}"></div>
                  <div class="cms-field"><label>Lucide Icon</label><input class="cms-input" data-vd-icon value="${esc(d.icon || 'sparkles')}"></div>
                </div>
                <div class="cms-form-row-2">
                  <div class="cms-field" style="margin-bottom:0;"><label>Front Face Copy</label><textarea class="cms-textarea" rows="2" data-vd-front>${esc(d.front_copy)}</textarea></div>
                  <div class="cms-field" style="margin-bottom:0;"><label>Back Face Copy (Flipped)</label><textarea class="cms-textarea" rows="2" data-vd-back>${esc(d.back_copy)}</textarea></div>
                </div>
              </div>
            `).join('')}
          </div>
        </div>

        <div class="cms-card">
          <div class="cms-card-header"><div><h2>SEO Metadata</h2></div></div>
          <div class="cms-field"><label>Meta Title</label><input class="cms-input" name="meta_title" value="${esc(a.meta_title)}"></div>
          <div class="cms-field"><label>Meta Description</label><textarea class="cms-textarea" rows="2" name="meta_description">${esc(a.meta_description)}</textarea></div>
          <button type="submit" class="cms-btn cms-btn-lime"><i data-lucide="save"></i><span>Save About Page</span></button>
        </div>
      </form>
    `;

    const form = document.getElementById('cms-form-about');
    bindUploadInputs(form);
    form.addEventListener('submit', async (e) => {
      e.preventDefault();
      const fd = new FormData(form);
      try {
        const res = await apiPost('save_about', {
          banner_image_file: fd.get('banner_image_file'),
          overview_title: fd.get('overview_title'),
          overview_subtitle: fd.get('overview_subtitle'),
          footprint_stats: Array.from(form.querySelectorAll('[data-about-stat]')).map((r) => ({
            metric: r.querySelector('[data-st-metric]').value.trim(),
            label: r.querySelector('[data-st-label]').value.trim(),
            icon: r.querySelector('[data-st-icon]').value.trim(),
          })),
          history_paragraphs: Array.from(form.querySelectorAll('[data-about-para]')).map((t) => t.value.trim()).filter(Boolean),
          why_title: fd.get('why_title'),
          philosophy_title: fd.get('philosophy_title'),
          philosophy_body: fd.get('philosophy_body'),
          people_title: fd.get('people_title'),
          people_body: fd.get('people_body'),
          value_drivers_title: fd.get('value_drivers_title'),
          value_drivers: Array.from(form.querySelectorAll('[data-about-driver]')).map((r) => ({
            icon: r.querySelector('[data-vd-icon]').value.trim(),
            title: r.querySelector('[data-vd-title]').value.trim(),
            front_copy: r.querySelector('[data-vd-front]').value.trim(),
            back_copy: r.querySelector('[data-vd-back]').value.trim(),
          })),
          meta_title: fd.get('meta_title'),
          meta_description: fd.get('meta_description'),
        });
        toast(res.message);
        await reloadState(false);
      } catch (err) {
        toast(err.message, true);
      }
    });
  }

  // ===========================================================================
  // VIEW 4: Credit Information Services (`page_credit_services`)
  // ===========================================================================
  function renderCreditEditor() {
    const c = state.pages.credit;
    const offerings = parseJson(c.offerings_json, []);
    const partners = parseJson(c.partners_json, []);

    workspace.innerHTML = `
      <div class="cms-page-head">
        <div><h1>Credit Information Services</h1></div>
        <a href="${esc(cfg.basePath)}products/credit-information/" target="_blank" class="cms-btn cms-btn-ghost cms-btn-sm"><i data-lucide="external-link"></i><span>Preview Page</span></a>
      </div>
      <form id="cms-form-credit">
        <div class="cms-card">
          <div class="cms-card-header"><div><h2>Overview &amp; Banner</h2></div></div>
          ${renderUploadField({ label: 'Banner Image', name: 'banner_image_file', value: c.banner_image_file, target: 'image', previewUrl: state.asset_urls.images + c.banner_image_file })}
          <div class="cms-field"><label>Page Title</label><input class="cms-input" name="page_title" value="${esc(c.page_title)}"></div>
          <div class="cms-field"><label>Overview Copy</label><textarea class="cms-textarea" name="overview_copy">${esc(c.overview_copy)}</textarea></div>
          <div class="cms-form-row-3">
            <div class="cms-field"><label>Sign Up Button Label</label><input class="cms-input" name="signup_cta_label" value="${esc(c.signup_cta_label)}"></div>
            <div class="cms-field"><label>Request Report Button Label</label><input class="cms-input" name="report_cta_label" value="${esc(c.report_cta_label)}"></div>
            <div class="cms-field"><label>Partners Section Heading</label><input class="cms-input" name="partners_heading" value="${esc(c.partners_heading)}"></div>
          </div>
        </div>

        <div class="cms-card">
          <div class="cms-card-header"><div><h2>Credit Offerings (3 Pillars)</h2></div></div>
          <div class="cms-repeater-list">
            ${offerings.map((o, i) => `
              <div class="cms-repeater-item" data-credit-offering>
                <div class="cms-repeater-head"><strong>Pillar ${i + 1}: ${esc(o.title)}</strong></div>
                <div class="cms-form-row-3">
                  <div class="cms-field"><label>Title</label><input class="cms-input" data-co-title value="${esc(o.title)}"></div>
                  <div class="cms-field"><label>Subtitle</label><input class="cms-input" data-co-sub value="${esc(o.subtitle || '')}"></div>
                  <div class="cms-field"><label>Icon</label><input class="cms-input" data-co-icon value="${esc(o.icon || 'file-text')}"></div>
                </div>
                <div class="cms-field" style="margin-bottom:0;"><label>Bullet Items (one per line)</label><textarea class="cms-textarea" rows="4" data-co-items>${esc((o.items || []).join('\n'))}</textarea></div>
              </div>
            `).join('')}
          </div>
        </div>

        <div class="cms-card">
          <div class="cms-card-header">
            <div><h2>Credit Partners</h2><p>Store logo filenames only (uploaded into <code>assets/images/</code>).</p></div>
            <button type="button" class="cms-btn cms-btn-ghost cms-btn-sm" id="add-credit-partner"><i data-lucide="plus"></i><span>Add Partner</span></button>
          </div>
          <div class="cms-repeater-list" id="credit-partners-list">
            ${partners.map((p, i) => partnerRowHtml(p, i, 'cp')).join('')}
          </div>
        </div>

        <div class="cms-card">
          <div class="cms-card-header"><div><h2>SEO Metadata</h2></div></div>
          <div class="cms-field"><label>Meta Title</label><input class="cms-input" name="meta_title" value="${esc(c.meta_title)}"></div>
          <div class="cms-field"><label>Meta Description</label><textarea class="cms-textarea" rows="2" name="meta_description">${esc(c.meta_description)}</textarea></div>
          <button type="submit" class="cms-btn cms-btn-lime"><i data-lucide="save"></i><span>Save Credit Services</span></button>
        </div>
      </form>
    `;

    const form = document.getElementById('cms-form-credit');
    bindUploadInputs(form);
    bindSlideRemoves(form);
    document.getElementById('add-credit-partner').addEventListener('click', () => {
      const list = document.getElementById('credit-partners-list');
      list.insertAdjacentHTML('beforeend', partnerRowHtml({}, list.children.length, 'cp'));
      bindUploadInputs(list);
      bindSlideRemoves(list);
      refreshIcons();
    });

    form.addEventListener('submit', async (e) => {
      e.preventDefault();
      const fd = new FormData(form);
      try {
        const res = await apiPost('save_credit', {
          banner_image_file: fd.get('banner_image_file'),
          page_title: fd.get('page_title'),
          overview_copy: fd.get('overview_copy'),
          signup_cta_label: fd.get('signup_cta_label'),
          report_cta_label: fd.get('report_cta_label'),
          partners_heading: fd.get('partners_heading'),
          offerings: Array.from(form.querySelectorAll('[data-credit-offering]')).map((r) => ({
            icon: r.querySelector('[data-co-icon]').value.trim(),
            title: r.querySelector('[data-co-title]').value.trim(),
            subtitle: r.querySelector('[data-co-sub]').value.trim(),
            items: r.querySelector('[data-co-items]').value.split('\n').map((x) => x.trim()).filter(Boolean),
          })),
          partners: Array.from(form.querySelectorAll('[data-partner-row]')).map((r) => ({
            name: r.querySelector('[data-pt-name]').value.trim(),
            logo_file: r.querySelector('[data-pt-logo]').value.trim(),
          })).filter((p) => p.name !== ''),
          meta_title: fd.get('meta_title'),
          meta_description: fd.get('meta_description'),
        });
        toast(res.message);
        await reloadState(false);
      } catch (err) {
        toast(err.message, true);
      }
    });
  }

  function partnerRowHtml(p, i, prefix) {
    const fieldName = `${prefix}_partner_logo_${i}_${Date.now()}`;
    const preview = p.logo_file ? state.asset_urls.images + p.logo_file : '';
    return `
      <div class="cms-repeater-item" data-partner-row>
        <div class="cms-repeater-head">
          <strong>Partner ${i + 1}</strong>
          <button type="button" class="cms-btn cms-btn-danger cms-btn-sm" data-remove-row>Remove</button>
        </div>
        <div class="cms-form-row-2">
          <div class="cms-field" style="margin-bottom:0;"><label>Partner Name</label><input class="cms-input" data-pt-name value="${esc(p.name || '')}"></div>
          <div class="cms-field" style="margin-bottom:0;">
            <label>Logo Filename</label>
            <div class="cms-upload-row">
              ${preview ? `<img class="cms-upload-preview" data-preview-for="${fieldName}" src="${esc(preview)}" alt="">` : ''}
              <div class="cms-upload-controls">
                <input class="cms-input" name="${fieldName}" data-pt-logo value="${esc(p.logo_file || '')}" placeholder="partner-logo.png">
                <label class="cms-btn cms-btn-ghost cms-btn-sm" style="margin:0;cursor:pointer;">
                  <i data-lucide="upload"></i>
                  <input type="file" hidden data-cms-upload-input data-target="image" data-field="${fieldName}" accept=".png,.jpg,.jpeg,.webp,.svg">
                </label>
              </div>
            </div>
          </div>
        </div>
      </div>
    `;
  }

  // ===========================================================================
  // VIEW 5: Agricultural Services (`page_agricultural_services`)
  // ===========================================================================
  function renderAgriculturalEditor() {
    const g = state.pages.agricultural;
    const features = parseJson(g.features_json, []);
    const pillars = parseJson(g.pillars_json, []);

    workspace.innerHTML = `
      <div class="cms-page-head">
        <div><h1>Agricultural Services</h1></div>
        <a href="${esc(cfg.basePath)}products/agricultural-services/" target="_blank" class="cms-btn cms-btn-ghost cms-btn-sm"><i data-lucide="external-link"></i><span>Preview Page</span></a>
      </div>
      <form id="cms-form-agri">
        <div class="cms-card">
          <div class="cms-card-header"><div><h2>Visuals &amp; Copy</h2></div></div>
          <div class="cms-form-row-2">
            ${renderUploadField({ label: 'Banner Image', name: 'banner_image_file', value: g.banner_image_file, target: 'image', previewUrl: state.asset_urls.images + g.banner_image_file })}
            ${renderUploadField({ label: 'Ecosystem Diagram Image', name: 'diagram_image_file', value: g.diagram_image_file, target: 'image', previewUrl: state.asset_urls.images + g.diagram_image_file })}
          </div>
          <div class="cms-field"><label>Page Title</label><input class="cms-input" name="page_title" value="${esc(g.page_title)}"></div>
          <div class="cms-field"><label>Intro Paragraph</label><textarea class="cms-textarea" name="intro_paragraph">${esc(g.intro_paragraph)}</textarea></div>
          <div class="cms-field"><label>Scoring Methodology Paragraph</label><textarea class="cms-textarea" name="scoring_paragraph">${esc(g.scoring_paragraph)}</textarea></div>
          <div class="cms-field"><label>Risk Management Paragraph</label><textarea class="cms-textarea" name="risk_paragraph">${esc(g.risk_paragraph)}</textarea></div>
        </div>

        <div class="cms-card">
          <div class="cms-card-header"><div><h2>Core Risk Capabilities (4 Cards)</h2></div></div>
          <div class="cms-repeater-list">
            ${features.map((f, i) => `
              <div class="cms-repeater-item" data-agri-feat>
                <div class="cms-form-row-2">
                  <div class="cms-field"><label>Title ${i + 1}</label><input class="cms-input" data-af-title value="${esc(f.title)}"></div>
                  <div class="cms-field"><label>Lucide Icon</label><input class="cms-input" data-af-icon value="${esc(f.icon || 'bar-chart-3')}"></div>
                </div>
                <div class="cms-field" style="margin-bottom:0;"><label>Description</label><textarea class="cms-textarea" rows="2" data-af-desc>${esc(f.description)}</textarea></div>
              </div>
            `).join('')}
          </div>
          <div class="cms-field"><label>Diagram Pillar Labels (one per line)</label><textarea class="cms-textarea" rows="4" name="pillars_lines">${esc(pillars.map((p) => p.label).join('\n'))}</textarea></div>
        </div>

        <div class="cms-card">
          <div class="cms-card-header"><div><h2>SEO Metadata</h2></div></div>
          <div class="cms-field"><label>Meta Title</label><input class="cms-input" name="meta_title" value="${esc(g.meta_title)}"></div>
          <div class="cms-field"><label>Meta Description</label><textarea class="cms-textarea" rows="2" name="meta_description">${esc(g.meta_description)}</textarea></div>
          <button type="submit" class="cms-btn cms-btn-lime"><i data-lucide="save"></i><span>Save Agricultural Services</span></button>
        </div>
      </form>
    `;

    const form = document.getElementById('cms-form-agri');
    bindUploadInputs(form);
    form.addEventListener('submit', async (e) => {
      e.preventDefault();
      const fd = new FormData(form);
      const icons = ['receipt', 'server', 'clipboard-check', 'filter', 'sprout', 'tractor'];
      const pillarList = String(fd.get('pillars_lines') || '').split('\n').map((l) => l.trim()).filter(Boolean)
        .map((label, idx) => ({ icon: (pillars[idx] && pillars[idx].icon) || icons[idx % icons.length], label }));
      try {
        const res = await apiPost('save_agricultural', {
          banner_image_file: fd.get('banner_image_file'),
          diagram_image_file: fd.get('diagram_image_file'),
          page_title: fd.get('page_title'),
          intro_paragraph: fd.get('intro_paragraph'),
          scoring_paragraph: fd.get('scoring_paragraph'),
          risk_paragraph: fd.get('risk_paragraph'),
          features: Array.from(form.querySelectorAll('[data-agri-feat]')).map((r) => ({
            icon: r.querySelector('[data-af-icon]').value.trim(),
            title: r.querySelector('[data-af-title]').value.trim(),
            description: r.querySelector('[data-af-desc]').value.trim(),
          })),
          pillars: pillarList,
          meta_title: fd.get('meta_title'),
          meta_description: fd.get('meta_description'),
        });
        toast(res.message);
        await reloadState(false);
      } catch (err) {
        toast(err.message, true);
      }
    });
  }

  // ===========================================================================
  // VIEW 6: Authentication Services / E Check (`page_authentication_services`)
  // ===========================================================================
  function renderAuthenticationEditor() {
    const au = state.pages.authentication;
    const checks = parseJson(au.checks_json, []);
    const partners = parseJson(au.partners_json, []);

    workspace.innerHTML = `
      <div class="cms-page-head">
        <div><h1>Authentication Services (E Check)</h1></div>
        <a href="${esc(cfg.basePath)}products/authentication-services/" target="_blank" class="cms-btn cms-btn-ghost cms-btn-sm"><i data-lucide="external-link"></i><span>Preview Page</span></a>
      </div>
      <form id="cms-form-auth">
        <div class="cms-card">
          <div class="cms-card-header"><div><h2>Overview &amp; Brand Assets</h2></div></div>
          <div class="cms-form-row-2">
            ${renderUploadField({ label: 'Banner Image', name: 'banner_image_file', value: au.banner_image_file, target: 'image', previewUrl: state.asset_urls.images + au.banner_image_file })}
            ${renderUploadField({ label: 'E Check Badge Image', name: 'echeck_badge_file', value: au.echeck_badge_file, target: 'image', previewUrl: state.asset_urls.images + au.echeck_badge_file })}
          </div>
          <div class="cms-form-row-2">
            <div class="cms-field"><label>Page Title</label><input class="cms-input" name="page_title" value="${esc(au.page_title)}"></div>
            <div class="cms-field"><label>Partners Heading</label><input class="cms-input" name="partners_heading" value="${esc(au.partners_heading)}"></div>
          </div>
          <div class="cms-field"><label>Overview Copy</label><textarea class="cms-textarea" name="overview_copy">${esc(au.overview_copy)}</textarea></div>
        </div>

        <div class="cms-card">
          <div class="cms-card-header"><div><h2>4 Verification Modules</h2></div></div>
          <div class="cms-repeater-list">
            ${checks.map((ch, i) => `
              <div class="cms-repeater-item" data-auth-check>
                <div class="cms-form-row-2">
                  <div class="cms-field"><label>Module ${i + 1} Title</label><input class="cms-input" data-ac-title value="${esc(ch.title)}"></div>
                  <div class="cms-field"><label>Lucide Icon</label><input class="cms-input" data-ac-icon value="${esc(ch.icon || 'shield-check')}"></div>
                </div>
                <div class="cms-field" style="margin-bottom:0;"><label>Verification Items (one per line)</label><textarea class="cms-textarea" rows="3" data-ac-items>${esc((ch.items || []).join('\n'))}</textarea></div>
              </div>
            `).join('')}
          </div>
        </div>

        <div class="cms-card">
          <div class="cms-card-header">
            <div><h2>Screening Partners</h2></div>
            <button type="button" class="cms-btn cms-btn-ghost cms-btn-sm" id="add-auth-partner"><i data-lucide="plus"></i><span>Add Partner</span></button>
          </div>
          <div class="cms-repeater-list" id="auth-partners-list">
            ${partners.map((p, i) => partnerRowHtml(p, i, 'ap')).join('')}
          </div>
        </div>

        <div class="cms-card">
          <div class="cms-card-header"><div><h2>SEO Metadata</h2></div></div>
          <div class="cms-field"><label>Meta Title</label><input class="cms-input" name="meta_title" value="${esc(au.meta_title)}"></div>
          <div class="cms-field"><label>Meta Description</label><textarea class="cms-textarea" rows="2" name="meta_description">${esc(au.meta_description)}</textarea></div>
          <button type="submit" class="cms-btn cms-btn-lime"><i data-lucide="save"></i><span>Save Authentication Services</span></button>
        </div>
      </form>
    `;

    const form = document.getElementById('cms-form-auth');
    bindUploadInputs(form);
    bindSlideRemoves(form);
    document.getElementById('add-auth-partner').addEventListener('click', () => {
      const list = document.getElementById('auth-partners-list');
      list.insertAdjacentHTML('beforeend', partnerRowHtml({}, list.children.length, 'ap'));
      bindUploadInputs(list);
      bindSlideRemoves(list);
      refreshIcons();
    });

    form.addEventListener('submit', async (e) => {
      e.preventDefault();
      const fd = new FormData(form);
      try {
        const res = await apiPost('save_authentication', {
          banner_image_file: fd.get('banner_image_file'),
          echeck_badge_file: fd.get('echeck_badge_file'),
          page_title: fd.get('page_title'),
          overview_copy: fd.get('overview_copy'),
          partners_heading: fd.get('partners_heading'),
          checks: Array.from(form.querySelectorAll('[data-auth-check]')).map((r) => ({
            icon: r.querySelector('[data-ac-icon]').value.trim(),
            title: r.querySelector('[data-ac-title]').value.trim(),
            items: r.querySelector('[data-ac-items]').value.split('\n').map((x) => x.trim()).filter(Boolean),
          })),
          partners: Array.from(form.querySelectorAll('[data-partner-row]')).map((r) => ({
            name: r.querySelector('[data-pt-name]').value.trim(),
            logo_file: r.querySelector('[data-pt-logo]').value.trim(),
          })).filter((p) => p.name !== ''),
          meta_title: fd.get('meta_title'),
          meta_description: fd.get('meta_description'),
        });
        toast(res.message);
        await reloadState(false);
      } catch (err) {
        toast(err.message, true);
      }
    });
  }

  // ===========================================================================
  // VIEW 7: Data Privacy (ISSP) (`page_data_privacy`)
  // ===========================================================================
  function renderPrivacyEditor() {
    const p = state.pages.privacy;
    const mBullets = parseJson(p.mission_bullets_json, []);
    const oBullets = parseJson(p.objectives_bullets_json, []);
    const sParas = parseJson(p.scope_paragraphs_json, []);

    workspace.innerHTML = `
      <div class="cms-page-head">
        <div><h1>Data Privacy (ISSP)</h1></div>
        <a href="${esc(cfg.basePath)}resources/data-privacy/" target="_blank" class="cms-btn cms-btn-ghost cms-btn-sm"><i data-lucide="external-link"></i><span>Preview Page</span></a>
      </div>
      <form id="cms-form-privacy">
        <div class="cms-card">
          <div class="cms-card-header"><div><h2>Banner, Intro &amp; Downloadable Policy PDF</h2></div></div>
          <div class="cms-form-row-2">
            ${renderUploadField({ label: 'Banner Image', name: 'banner_image_file', value: p.banner_image_file, target: 'image', previewUrl: state.asset_urls.images + p.banner_image_file })}
            ${renderUploadField({ label: 'ISSP Policy PDF', name: 'policy_doc_file', value: p.policy_doc_file, target: 'document', purpose: p.policy_doc_purpose || 'policy', docFolder: p.policy_doc_folder || 'xds-information-security-policy', placeholder: 'xds-issp-2026.pdf' })}
          </div>
          <input type="hidden" name="policy_doc_purpose" value="${esc(p.policy_doc_purpose || 'policy')}">
          <input type="hidden" name="policy_doc_folder" value="${esc(p.policy_doc_folder || 'xds-information-security-policy')}">
          <div class="cms-field"><label>Page Title</label><input class="cms-input" name="page_title" value="${esc(p.page_title)}"></div>
          <div class="cms-field"><label>Overview Intro Copy</label><textarea class="cms-textarea" name="intro_copy">${esc(p.intro_copy)}</textarea></div>
        </div>

        <div class="cms-card">
          <div class="cms-card-header"><div><h2>Policy Sections</h2></div></div>
          <div class="cms-field"><label>Mission Heading</label><input class="cms-input" name="mission_title" value="${esc(p.mission_title)}"></div>
          <div class="cms-field"><label>Mission Intro</label><textarea class="cms-textarea" rows="2" name="mission_intro">${esc(p.mission_intro)}</textarea></div>
          <div class="cms-field"><label>Mission Commitments (one per line)</label><textarea class="cms-textarea" rows="6" name="mission_bullets">${esc(mBullets.join('\n'))}</textarea></div>

          <div class="cms-field"><label>Access Philosophy Heading</label><input class="cms-input" name="philosophy_title" value="${esc(p.philosophy_title)}"></div>
          <div class="cms-field"><label>Access Philosophy Copy</label><textarea class="cms-textarea" rows="2" name="philosophy_body">${esc(p.philosophy_body)}</textarea></div>

          <div class="cms-field"><label>Objectives Heading</label><input class="cms-input" name="objectives_title" value="${esc(p.objectives_title)}"></div>
          <div class="cms-field"><label>Objectives List (one per line)</label><textarea class="cms-textarea" rows="6" name="objectives_bullets">${esc(oBullets.join('\n'))}</textarea></div>

          <div class="cms-field"><label>Scope Heading</label><input class="cms-input" name="scope_title" value="${esc(p.scope_title)}"></div>
          <div class="cms-field"><label>Scope Paragraphs (separate paragraphs with a blank line)</label><textarea class="cms-textarea" rows="6" name="scope_paragraphs">${esc(sParas.join('\n\n'))}</textarea></div>
        </div>

        <div class="cms-card">
          <div class="cms-card-header"><div><h2>SEO Metadata</h2></div></div>
          <div class="cms-field"><label>Meta Title</label><input class="cms-input" name="meta_title" value="${esc(p.meta_title)}"></div>
          <div class="cms-field"><label>Meta Description</label><textarea class="cms-textarea" rows="2" name="meta_description">${esc(p.meta_description)}</textarea></div>
          <button type="submit" class="cms-btn cms-btn-lime"><i data-lucide="save"></i><span>Save Data Privacy Page</span></button>
        </div>
      </form>
    `;

    const form = document.getElementById('cms-form-privacy');
    bindUploadInputs(form);
    form.addEventListener('submit', async (e) => {
      e.preventDefault();
      const fd = new FormData(form);
      try {
        const res = await apiPost('save_privacy', {
          banner_image_file: fd.get('banner_image_file'),
          policy_doc_purpose: fd.get('policy_doc_purpose'),
          policy_doc_folder: fd.get('policy_doc_folder'),
          policy_doc_file: fd.get('policy_doc_file'),
          page_title: fd.get('page_title'),
          intro_copy: fd.get('intro_copy'),
          mission_title: fd.get('mission_title'),
          mission_intro: fd.get('mission_intro'),
          mission_bullets: String(fd.get('mission_bullets') || '').split('\n').map((x) => x.trim()).filter(Boolean),
          philosophy_title: fd.get('philosophy_title'),
          philosophy_body: fd.get('philosophy_body'),
          objectives_title: fd.get('objectives_title'),
          objectives_bullets: String(fd.get('objectives_bullets') || '').split('\n').map((x) => x.trim()).filter(Boolean),
          scope_title: fd.get('scope_title'),
          scope_paragraphs: String(fd.get('scope_paragraphs') || '').split(/\n\s*\n/).map((x) => x.trim()).filter(Boolean),
          meta_title: fd.get('meta_title'),
          meta_description: fd.get('meta_description'),
        });
        toast(res.message);
        await reloadState(false);
      } catch (err) {
        toast(err.message, true);
      }
    });
  }

  // ===========================================================================
  // VIEW 8: Articles & Insights CRUD (`page_articles`) -> Opens Dedicated Page
  // ===========================================================================
  function renderArticlesManager() {
    const articles = state.articles || [];
    workspace.innerHTML = `
      <div class="cms-page-head">
        <div><h1>Articles &amp; Insights</h1></div>
        <div style="display:flex;gap:10px;">
          <a href="${esc(cfg.basePath)}resources/articles/" target="_blank" class="cms-btn cms-btn-ghost cms-btn-sm"><i data-lucide="external-link"></i><span>View Public Articles</span></a>
          <a href="${esc(routes.article_editor)}" class="cms-btn cms-btn-lime cms-btn-sm"><i data-lucide="plus"></i><span>New Article</span></a>
        </div>
      </div>

      <div class="cms-card">
        <div class="cms-table-wrap">
          <table class="cms-table">
            <thead>
              <tr><th>Cover</th><th>Title &amp; Slug</th><th>Category</th><th>Published</th><th>Status</th><th>Actions</th></tr>
            </thead>
            <tbody>
              ${articles.map((art) => `
                <tr>
                  <td><img src="${esc(state.asset_urls.articles + art.slug + '/' + art.image_file)}" alt="" style="width:76px;height:52px;object-fit:cover;border-radius:8px;border:1px solid var(--cms-border);"></td>
                  <td>
                    <div style="font-weight:700;">${esc(art.title)}</div>
                    <div style="font-size:0.76rem;color:var(--cms-muted);">/resources/articles/view/?slug=${esc(art.slug)}</div>
                  </td>
                  <td><span class="cms-badge cms-badge-indigo">${esc(art.category)}</span></td>
                  <td style="white-space:nowrap;">${esc(art.published_date)}</td>
                  <td>${Number(art.is_active ?? 1) === 1 ? '<span class="cms-badge cms-badge-lime">Published</span>' : '<span class="cms-badge cms-badge-muted">Draft</span>'}</td>
                  <td style="white-space:nowrap;">
                    <a href="${esc(routes.article_editor)}?id=${art.id}" class="cms-btn cms-btn-ghost cms-btn-sm">Edit</a>
                    <a href="${esc(cfg.basePath)}resources/articles/view/?slug=${encodeURIComponent(art.slug)}" target="_blank" class="cms-btn cms-btn-ghost cms-btn-sm"><i data-lucide="external-link"></i></a>
                    <button type="button" class="cms-btn cms-btn-danger cms-btn-sm" data-del-art="${art.id}">Delete</button>
                  </td>
                </tr>
              `).join('')}
            </tbody>
          </table>
        </div>
      </div>
    `;

    workspace.querySelectorAll('[data-del-art]').forEach((b) => {
      b.addEventListener('click', async () => {
        if (!confirm('Delete this article permanently?')) return;
        try {
          const res = await apiPost('delete_article', { id: Number(b.getAttribute('data-del-art')) });
          toast(res.message);
          await reloadState(true);
        } catch (err) {
          toast(err.message, true);
        }
      });
    });
  }

  // ===========================================================================
  // VIEW 9: FAQs CRUD (`page_faq`)
  // ===========================================================================
  function renderFaqsManager() {
    const faqs = state.faqs || [];
    workspace.innerHTML = `
      <div class="cms-page-head">
        <div><h1>Frequently Asked Questions</h1></div>
        <div style="display:flex;gap:10px;">
          <a href="${esc(cfg.basePath)}resources/faq/" target="_blank" class="cms-btn cms-btn-ghost cms-btn-sm"><i data-lucide="external-link"></i><span>View Public FAQ</span></a>
          <button type="button" class="cms-btn cms-btn-lime cms-btn-sm" id="btn-new-faq"><i data-lucide="plus"></i><span>Add FAQ</span></button>
        </div>
      </div>
      <div class="cms-card">
        <div class="cms-table-wrap">
          <table class="cms-table">
            <thead><tr><th>#</th><th>Question</th><th>Category</th><th>Status</th><th>Actions</th></tr></thead>
            <tbody>
              ${faqs.map((f) => `
                <tr>
                  <td>${esc(f.sort_order)}</td>
                  <td>
                    <div style="font-weight:600;">${esc(f.question)}</div>
                    <div style="font-size:0.78rem;color:var(--cms-muted);max-width:540px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">${esc(f.answer)}</div>
                  </td>
                  <td><span class="cms-badge cms-badge-indigo">${esc(f.category)}</span></td>
                  <td>${Number(f.is_active ?? 1) === 1 ? '<span class="cms-badge cms-badge-lime">Active</span>' : '<span class="cms-badge cms-badge-muted">Hidden</span>'}</td>
                  <td style="white-space:nowrap;">
                    <button type="button" class="cms-btn cms-btn-ghost cms-btn-sm" data-edit-faq="${f.id}">Edit</button>
                    <button type="button" class="cms-btn cms-btn-danger cms-btn-sm" data-del-faq="${f.id}">Delete</button>
                  </td>
                </tr>
              `).join('')}
            </tbody>
          </table>
        </div>
      </div>
    `;

    document.getElementById('btn-new-faq').addEventListener('click', () => openFaqModal(null));
    workspace.querySelectorAll('[data-edit-faq]').forEach((b) => {
      b.addEventListener('click', () => {
        const faq = faqs.find((x) => Number(x.id) === Number(b.getAttribute('data-edit-faq')));
        if (faq) openFaqModal(faq);
      });
    });
    workspace.querySelectorAll('[data-del-faq]').forEach((b) => {
      b.addEventListener('click', async () => {
        if (!confirm('Delete this FAQ?')) return;
        try {
          const res = await apiPost('delete_faq', { id: Number(b.getAttribute('data-del-faq')) });
          toast(res.message);
          await reloadState(true);
        } catch (err) {
          toast(err.message, true);
        }
      });
    });
  }

  function openFaqModal(faq) {
    const isEdit = Boolean(faq && faq.id);
    openModal(isEdit ? 'Edit FAQ' : 'Add FAQ', `
      <form id="cms-modal-faq-form">
        <input type="hidden" name="id" value="${esc(faq ? faq.id : 0)}">
        <div class="cms-field"><label>Question *</label><input class="cms-input" name="question" required value="${esc(faq ? faq.question : '')}"></div>
        <div class="cms-field"><label>Answer *</label><textarea class="cms-textarea" rows="5" name="answer" required>${esc(faq ? faq.answer : '')}</textarea></div>
        <div class="cms-form-row-3">
          <div class="cms-field"><label>Category</label><input class="cms-input" name="category" value="${esc(faq ? faq.category : 'Credit Providers')}"></div>
          <div class="cms-field"><label>Lucide Icon</label><input class="cms-input" name="icon_key" value="${esc(faq ? faq.icon_key : 'help-circle')}"></div>
          <div class="cms-field"><label>Sort Order</label><input type="number" min="0" class="cms-input" name="sort_order" value="${esc(faq ? faq.sort_order : (state.faqs.length + 1))}"></div>
        </div>
        <div class="cms-field">
          <label>Visibility</label>
          <select class="cms-select" name="is_active">
            <option value="1" ${!faq || Number(faq.is_active ?? 1) === 1 ? 'selected' : ''}>Active (Visible)</option>
            <option value="0" ${faq && Number(faq.is_active ?? 1) === 0 ? 'selected' : ''}>Hidden</option>
          </select>
        </div>
        <div style="display:flex;justify-content:flex-end;gap:10px;">
          <button type="button" class="cms-btn cms-btn-ghost" data-close-modal>Cancel</button>
          <button type="submit" class="cms-btn cms-btn-lime"><i data-lucide="save"></i><span>Save FAQ</span></button>
        </div>
      </form>
    `);
    const form = document.getElementById('cms-modal-faq-form');
    form.querySelector('[data-close-modal]').addEventListener('click', closeModal);
    form.addEventListener('submit', async (e) => {
      e.preventDefault();
      const payload = {};
      new FormData(form).forEach((v, k) => { payload[k] = v; });
      payload.id = Number(payload.id || 0);
      payload.is_active = Number(payload.is_active) === 1;
      try {
        const res = await apiPost('save_faq', payload);
        toast(res.message);
        closeModal();
        await reloadState(true);
      } catch (err) {
        toast(err.message, true);
      }
    });
  }

  // ===========================================================================
  // VIEW 10: Contact Inquiries Inbox + Office Settings (`contact_inquiries` & `page_contact`)
  // ===========================================================================
  function renderInquiriesView() {
    const c = state.pages.contact;
    const needs = parseJson(c.inquiry_needs_json, []);
    const allInq = state.inquiries || [];
    const filtered = inquiryFilter === 'all' ? allInq : allInq.filter((i) => i.status === inquiryFilter);

    workspace.innerHTML = `
      <div class="cms-page-head">
        <div><h1>Inquiries &amp; Contact Page</h1></div>
        <a href="${esc(cfg.basePath)}contact/" target="_blank" class="cms-btn cms-btn-ghost cms-btn-sm"><i data-lucide="external-link"></i><span>View Contact Page</span></a>
      </div>

      <div class="cms-tabs">
        <button type="button" class="cms-tab-btn ${inquiryTab === 'inbox' ? 'is-active' : ''}" data-inq-tab="inbox">Inquiries Inbox (${allInq.length})</button>
        <button type="button" class="cms-tab-btn ${inquiryTab === 'settings' ? 'is-active' : ''}" data-inq-tab="settings">Harare Office &amp; Contact Settings</button>
      </div>

      ${inquiryTab === 'inbox' ? `
        <div class="cms-card">
          <div class="cms-card-header">
            <div><h2>Submitted Inquiries</h2><p>Track status from new to responded across public &amp; subscriber portals.</p></div>
            <div style="display:flex;gap:6px;flex-wrap:wrap;">
              ${['all', 'new', 'in_review', 'responded', 'archived'].map((st) => `
                <button type="button" class="cms-btn cms-btn-sm ${inquiryFilter === st ? 'cms-btn-indigo' : 'cms-btn-ghost'}" data-inq-filter="${st}">${esc(st.replace('_', ' '))}</button>
              `).join('')}
            </div>
          </div>
          <div class="cms-table-wrap">
            <table class="cms-table">
              <thead><tr><th>Ref</th><th>Sender</th><th>Topic</th><th>Callback</th><th>Status</th><th>Actions</th></tr></thead>
              <tbody>
                ${filtered.length ? filtered.map((inq) => `
                  <tr>
                    <td><strong>${esc(inq.reference_code)}</strong><div style="font-size:0.74rem;color:var(--cms-muted);">${esc(inq.source_page)}</div></td>
                    <td><strong>${esc(inq.firstname)} ${esc(inq.lastname)}</strong><div style="font-size:0.78rem;color:var(--cms-muted);">${esc(inq.email)}</div></td>
                    <td>${esc(inq.need_category)}</td>
                    <td>${inq.preferred_date ? `${esc(inq.preferred_date)} ${esc(inq.preferred_time || '')}` : '—'}</td>
                    <td>
                      <select class="cms-select" style="min-height:32px;padding:4px 8px;font-size:0.8rem;" data-inq-status="${inq.id}">
                        ${['new', 'in_review', 'responded', 'archived'].map((st) => `<option value="${st}" ${inq.status === st ? 'selected' : ''}>${esc(st.replace('_', ' '))}</option>`).join('')}
                      </select>
                    </td>
                    <td style="white-space:nowrap;">
                      <button type="button" class="cms-btn cms-btn-ghost cms-btn-sm" data-open-inq="${inq.id}">Read</button>
                      <button type="button" class="cms-btn cms-btn-danger cms-btn-sm" data-del-inq="${inq.id}">Delete</button>
                    </td>
                  </tr>
                `).join('') : `<tr><td colspan="6" style="text-align:center;color:var(--cms-muted);padding:24px;">No inquiries match this filter.</td></tr>`}
              </tbody>
            </table>
          </div>
        </div>
      ` : `
        <form id="cms-form-contact" class="cms-card">
          <div class="cms-card-header"><div><h2>Harare Office &amp; Form Configuration</h2></div></div>
          <div class="cms-form-row-2">
            <div class="cms-field"><label>Page Title</label><input class="cms-input" name="page_title" value="${esc(c.page_title)}"></div>
            <div class="cms-field"><label>Form Heading</label><input class="cms-input" name="heading" value="${esc(c.heading)}"></div>
          </div>
          <div class="cms-field"><label>Intro Copy</label><textarea class="cms-textarea" rows="2" name="intro_copy">${esc(c.intro_copy)}</textarea></div>
          <div class="cms-form-row-3">
            <div class="cms-field"><label>Address Line 1</label><input class="cms-input" name="address_line1" value="${esc(c.address_line1)}"></div>
            <div class="cms-field"><label>Address Line 2</label><input class="cms-input" name="address_line2" value="${esc(c.address_line2)}"></div>
            <div class="cms-field"><label>City &amp; Country</label><input class="cms-input" name="city_country" value="${esc(c.city_country)}"></div>
          </div>
          <div class="cms-form-row-3">
            <div class="cms-field"><label>Phone 1</label><input class="cms-input" name="phone_1" value="${esc(c.phone_1)}"></div>
            <div class="cms-field"><label>Phone 2</label><input class="cms-input" name="phone_2" value="${esc(c.phone_2)}"></div>
            <div class="cms-field"><label>Phone 3</label><input class="cms-input" name="phone_3" value="${esc(c.phone_3)}"></div>
          </div>
          <div class="cms-form-row-3">
            <div class="cms-field"><label>Office Email</label><input class="cms-input" name="email" value="${esc(c.email)}"></div>
            <div class="cms-field"><label>Weekday Hours</label><input class="cms-input" name="hours_weekday" value="${esc(c.hours_weekday)}"></div>
            <div class="cms-field"><label>Weekend Hours</label><input class="cms-input" name="hours_weekend" value="${esc(c.hours_weekend)}"></div>
          </div>
          <div class="cms-field"><label>Office Card Heading</label><input class="cms-input" name="office_title" value="${esc(c.office_title)}"></div>
          <div class="cms-field"><label>Google Maps Embed URL</label><input class="cms-input" name="map_embed_url" value="${esc(c.map_embed_url)}"></div>
          <div class="cms-field"><label>Enquiry Dropdown Options (one per line)</label><textarea class="cms-textarea" rows="5" name="inquiry_needs">${esc(needs.join('\n'))}</textarea></div>
          <div class="cms-form-row-2">
            <div class="cms-field"><label>SEO Meta Title</label><input class="cms-input" name="meta_title" value="${esc(c.meta_title)}"></div>
            <div class="cms-field"><label>SEO Meta Description</label><input class="cms-input" name="meta_description" value="${esc(c.meta_description)}"></div>
          </div>
          <button type="submit" class="cms-btn cms-btn-lime"><i data-lucide="save"></i><span>Save Contact Settings</span></button>
        </form>
      `}
    `;

    workspace.querySelectorAll('[data-inq-tab]').forEach((b) => {
      b.addEventListener('click', () => {
        inquiryTab = b.getAttribute('data-inq-tab');
        try { sessionStorage.setItem('xds-cms-inq-tab', inquiryTab); } catch (e) {}
        renderInquiriesView();
        refreshIcons();
      });
    });
    workspace.querySelectorAll('[data-inq-filter]').forEach((b) => {
      b.addEventListener('click', () => { inquiryFilter = b.getAttribute('data-inq-filter'); renderInquiriesView(); refreshIcons(); });
    });
    workspace.querySelectorAll('[data-inq-status]').forEach((sel) => {
      sel.addEventListener('change', async () => {
        try {
          const res = await apiPost('update_inquiry_status', { id: Number(sel.getAttribute('data-inq-status')), status: sel.value });
          toast(res.message);
          await reloadState(true);
        } catch (err) {
          toast(err.message, true);
        }
      });
    });
    workspace.querySelectorAll('[data-open-inq]').forEach((b) => {
      b.addEventListener('click', () => {
        const found = allInq.find((x) => Number(x.id) === Number(b.getAttribute('data-open-inq')));
        if (found) openInquiryModal(found);
      });
    });
    workspace.querySelectorAll('[data-del-inq]').forEach((b) => {
      b.addEventListener('click', async () => {
        if (!confirm('Delete this inquiry permanently?')) return;
        try {
          const res = await apiPost('delete_inquiry', { id: Number(b.getAttribute('data-del-inq')) });
          toast(res.message);
          await reloadState(true);
        } catch (err) {
          toast(err.message, true);
        }
      });
    });

    const cForm = document.getElementById('cms-form-contact');
    if (cForm) {
      cForm.addEventListener('submit', async (e) => {
        e.preventDefault();
        const fd = new FormData(cForm);
        const payload = {};
        fd.forEach((v, k) => { payload[k] = v; });
        payload.inquiry_needs = String(fd.get('inquiry_needs') || '').split('\n').map((x) => x.trim()).filter(Boolean);
        try {
          const res = await apiPost('save_contact', payload);
          toast(res.message);
          await reloadState(false);
        } catch (err) {
          toast(err.message, true);
        }
      });
    }
  }

  function openInquiryModal(inq) {
    openModal(`Inquiry ${inq.reference_code}`, `
      <div style="display:grid;gap:12px;font-size:0.9rem;">
        <div><strong>From:</strong> ${esc(inq.firstname)} ${esc(inq.lastname)} &lt;${esc(inq.email)}&gt;</div>
        <div><strong>Phone / Company:</strong> ${esc(inq.phone || '—')} &middot; ${esc(inq.company || '—')}</div>
        <div><strong>Category:</strong> ${esc(inq.need_category)}</div>
        <div><strong>Preferred Callback:</strong> ${inq.preferred_date ? `${esc(inq.preferred_date)} at ${esc(inq.preferred_time || 'any business hour')}` : 'Not specified'}</div>
        <div><strong>Status:</strong> ${statusBadge(inq.status)} &nbsp; <span style="color:var(--cms-muted);font-size:0.8rem;">Received: ${esc(inq.created_at)}</span></div>
        <div style="padding:14px;border-radius:10px;background:var(--cms-surface-subtle);border:1px solid var(--cms-border);white-space:pre-wrap;">${esc(inq.message)}</div>
        <div style="display:flex;justify-content:flex-end;gap:10px;">
          <a href="mailto:${esc(inq.email)}?subject=${encodeURIComponent('Re: XDS Zimbabwe Enquiry ' + inq.reference_code)}" class="cms-btn cms-btn-lime cms-btn-sm"><i data-lucide="mail"></i><span>Reply via Email</span></a>
          <button type="button" class="cms-btn cms-btn-ghost cms-btn-sm" data-close-modal>Close</button>
        </div>
      </div>
    `);
    modalBodyEl.querySelector('[data-close-modal]').addEventListener('click', closeModal);
  }

  // ===========================================================================
  // VIEW 11: Site Settings & Portals (`site_system`)
  // ===========================================================================
  function renderSystemSettings() {
    const s = state.pages.system;
    const social = parseJson(s.social_links_json, s.social_links || {});
    const regional = parseJson(s.regional_links_json, s.regional_links || []);

    workspace.innerHTML = `
      <div class="cms-page-head">
        <div><h1>Site Settings &amp; Portals</h1></div>
      </div>
      <form id="cms-form-system">
        <div class="cms-card">
          <div class="cms-card-header"><div><h2>Brand &amp; System Assets</h2><p>Manage the Public website settings.</p></div></div>
          <div class="cms-form-row-2">
            <div class="cms-field"><label>Site Name</label><input class="cms-input" name="site_name" value="${esc(s.site_name)}"></div>
            <div class="cms-field"><label>Site Tagline</label><input class="cms-input" name="site_tagline" value="${esc(s.site_tagline)}"></div>
          </div>
          <div class="cms-form-row-3">
            ${renderUploadField({ label: 'Logo File', name: 'logo_file', value: s.logo_file, target: 'system', previewUrl: state.asset_urls.system + s.logo_file })}
            ${renderUploadField({ label: 'Favicon File', name: 'favicon_file', value: s.favicon_file, target: 'system', previewUrl: state.asset_urls.system + s.favicon_file })}
            ${renderUploadField({ label: 'Apple Touch Icon', name: 'apple_touch_icon_file', value: s.apple_touch_icon_file, target: 'system', previewUrl: state.asset_urls.system + s.apple_touch_icon_file })}
          </div>
        </div>

        <div class="cms-card">
          <div class="cms-card-header"><div><h2>Portal Links &amp; Footer Info</h2></div></div>
          <div class="cms-form-row-2">
            <div class="cms-field"><label>Credit Service Portal URL (Unified Login)</label><input class="cms-input" name="credit_portal_url" value="${esc(s.credit_portal_url)}"></div>
            <div class="cms-field"><label>Authentication Portal URL (E Check)</label><input class="cms-input" name="auth_portal_url" value="${esc(s.auth_portal_url)}"></div>
          </div>
          <div class="cms-form-row-3">
            <div class="cms-field"><label>Primary Email</label><input class="cms-input" name="primary_email" value="${esc(s.primary_email)}"></div>
            <div class="cms-field"><label>Primary Phone</label><input class="cms-input" name="primary_phone" value="${esc(s.primary_phone)}"></div>
            <div class="cms-field"><label>Secondary Phone</label><input class="cms-input" name="secondary_phone" value="${esc(s.secondary_phone)}"></div>
          </div>
          <div class="cms-form-row-3">
            <div class="cms-field"><label>Tertiary Phone</label><input class="cms-input" name="tertiary_phone" value="${esc(s.tertiary_phone || '')}"></div>
            <div class="cms-field"><label>Weekday Hours</label><input class="cms-input" name="operating_hours_weekday" value="${esc(s.operating_hours_weekday)}"></div>
            <div class="cms-field"><label>Weekend Hours</label><input class="cms-input" name="operating_hours_weekend" value="${esc(s.operating_hours_weekend)}"></div>
          </div>
          <div class="cms-form-row-2">
            <div class="cms-field"><label>Office Address</label><input class="cms-input" name="address_line" value="${esc(s.address_line)}"></div>
            <div class="cms-field"><label>Copyright Notice</label><input class="cms-input" name="copyright_text" value="${esc(s.copyright_text)}"></div>
          </div>
          <div class="cms-form-row-3">
            <div class="cms-field"><label>LinkedIn URL</label><input class="cms-input" name="social_linkedin" value="${esc(social.linkedin || '')}"></div>
            <div class="cms-field"><label>X / Twitter URL</label><input class="cms-input" name="social_twitter" value="${esc(social.twitter || '')}"></div>
            <div class="cms-field"><label>Facebook URL</label><input class="cms-input" name="social_facebook" value="${esc(social.facebook || '')}"></div>
          </div>
          <div class="cms-field">
            <label>Footer Sister Sites (format: Label | URL — one per line)</label>
            <textarea class="cms-textarea" rows="4" name="regional_lines">${esc(regional.map((r) => `${r.label} | ${r.url}`).join('\n'))}</textarea>
          </div>
          <button type="submit" class="cms-btn cms-btn-lime"><i data-lucide="save"></i><span>Save Site Settings</span></button>
        </div>
      </form>
    `;

    const form = document.getElementById('cms-form-system');
    bindUploadInputs(form);
    form.addEventListener('submit', async (e) => {
      e.preventDefault();
      const fd = new FormData(form);
      const regArr = String(fd.get('regional_lines') || '').split('\n').map((line) => {
        const parts = line.split('|').map((x) => x.trim());
        return parts[0] ? { label: parts[0], url: parts[1] || '#' } : null;
      }).filter(Boolean);

      try {
        const res = await apiPost('save_system', {
          site_name: fd.get('site_name'),
          site_tagline: fd.get('site_tagline'),
          logo_file: fd.get('logo_file'),
          favicon_file: fd.get('favicon_file'),
          apple_touch_icon_file: fd.get('apple_touch_icon_file'),
          primary_email: fd.get('primary_email'),
          primary_phone: fd.get('primary_phone'),
          secondary_phone: fd.get('secondary_phone'),
          tertiary_phone: fd.get('tertiary_phone'),
          address_line: fd.get('address_line'),
          operating_hours_weekday: fd.get('operating_hours_weekday'),
          operating_hours_weekend: fd.get('operating_hours_weekend'),
          credit_portal_url: fd.get('credit_portal_url'),
          auth_portal_url: fd.get('auth_portal_url'),
          copyright_text: fd.get('copyright_text'),
          social_links: {
            linkedin: fd.get('social_linkedin'),
            twitter: fd.get('social_twitter'),
            facebook: fd.get('social_facebook'),
          },
          regional_links: regArr,
        });
        toast(res.message);
        await reloadState(false);
      } catch (err) {
        toast(err.message, true);
      }
    });
  }

  // ===========================================================================
  // VIEW 12: Portal Users CRUD (`portal_users`)
  // ===========================================================================
  function renderUsersManager() {
    const users = state.users || [];
    workspace.innerHTML = `
      <div class="cms-page-head">
        <div><h1>Portal User Accounts</h1></div>
        <button type="button" class="cms-btn cms-btn-lime cms-btn-sm" id="btn-new-user"><i data-lucide="user-plus"></i><span>Add User</span></button>
      </div>
      <div class="cms-card">
        <div class="cms-table-wrap">
          <table class="cms-table">
            <thead><tr><th>Name &amp; Email</th><th>Role &amp; Destination</th><th>Organization</th><th>Status</th><th>Last Login</th><th>Actions</th></tr></thead>
            <tbody>
              ${users.map((u) => `
                <tr>
                  <td><strong>${esc(u.full_name)}</strong><div style="font-size:0.78rem;color:var(--cms-muted);">${esc(u.email)}</div></td>
                  <td>
                    <span class="cms-badge ${u.role === 'credit_user' ? 'cms-badge-indigo' : 'cms-badge-lime'}">${esc(u.role)}</span>
                    <div style="font-size:0.74rem;color:var(--cms-muted);">${u.role === 'credit_user' ? '→ /credit-portal/' : '→ /cms/'}</div>
                  </td>
                  <td>${esc(u.organization || '—')}</td>
                  <td>${Number(u.is_active) === 1 ? '<span class="cms-badge cms-badge-lime">Active</span>' : '<span class="cms-badge cms-badge-muted">Disabled</span>'}</td>
                  <td style="font-size:0.8rem;color:var(--cms-muted);">${esc(u.last_login_at || 'Never')}</td>
                  <td style="white-space:nowrap;">
                    <button type="button" class="cms-btn cms-btn-ghost cms-btn-sm" data-edit-user="${u.id}">Edit</button>
                    ${Number(u.id) !== Number(cfg.user?.id) ? `<button type="button" class="cms-btn cms-btn-danger cms-btn-sm" data-del-user="${u.id}">Delete</button>` : ''}
                  </td>
                </tr>
              `).join('')}
            </tbody>
          </table>
        </div>
      </div>
    `;

    document.getElementById('btn-new-user').addEventListener('click', () => openUserModal(null));
    workspace.querySelectorAll('[data-edit-user]').forEach((b) => {
      b.addEventListener('click', () => {
        const u = users.find((x) => Number(x.id) === Number(b.getAttribute('data-edit-user')));
        if (u) openUserModal(u);
      });
    });
    workspace.querySelectorAll('[data-del-user]').forEach((b) => {
      b.addEventListener('click', async () => {
        if (!confirm('Remove this user account?')) return;
        try {
          const res = await apiPost('delete_user', { id: Number(b.getAttribute('data-del-user')) });
          toast(res.message);
          await reloadState(true);
        } catch (err) {
          toast(err.message, true);
        }
      });
    });
  }

  function openUserModal(u) {
    const isEdit = Boolean(u && u.id);
    openModal(isEdit ? 'Edit Portal User' : 'Create Portal User', `
      <form id="cms-modal-user-form">
        <input type="hidden" name="id" value="${esc(u ? u.id : 0)}">
        <div class="cms-form-row-2">
          <div class="cms-field"><label>Full Name *</label><input class="cms-input" name="full_name" required value="${esc(u ? u.full_name : '')}"></div>
          <div class="cms-field"><label>Email Address *</label><input type="email" class="cms-input" name="email" required value="${esc(u ? u.email : '')}"></div>
        </div>
        <div class="cms-form-row-2">
          <div class="cms-field"><label>Role *</label>
            <select class="cms-select" name="role">
              <option value="credit_user" ${!u || u.role === 'credit_user' ? 'selected' : ''}>Credit User (→ Credit Service Portal)</option>
              <option value="editor" ${u && u.role === 'editor' ? 'selected' : ''}>Editor (→ CMS Portal)</option>
              <option value="admin" ${u && u.role === 'admin' ? 'selected' : ''}>Administrator (→ CMS Portal)</option>
            </select>
          </div>
          <div class="cms-field"><label>Organization</label><input class="cms-input" name="organization" value="${esc(u ? u.organization || '' : '')}"></div>
        </div>
        <div class="cms-form-row-2">
          <div class="cms-field"><label>${isEdit ? 'New Password (leave blank to keep)' : 'Password (min 8 chars) *'}</label><input type="password" class="cms-input" name="password" ${isEdit ? '' : 'required'} autocomplete="new-password"></div>
          <div class="cms-field"><label>Account Status</label>
            <select class="cms-select" name="is_active">
              <option value="1" ${!u || Number(u.is_active) === 1 ? 'selected' : ''}>Active</option>
              <option value="0" ${u && Number(u.is_active) === 0 ? 'selected' : ''}>Disabled</option>
            </select>
          </div>
        </div>
        <div style="display:flex;justify-content:flex-end;gap:10px;">
          <button type="button" class="cms-btn cms-btn-ghost" data-close-modal>Cancel</button>
          <button type="submit" class="cms-btn cms-btn-lime"><i data-lucide="save"></i><span>${isEdit ? 'Save User' : 'Create User'}</span></button>
        </div>
      </form>
    `);
    const form = document.getElementById('cms-modal-user-form');
    form.querySelector('[data-close-modal]').addEventListener('click', closeModal);
    form.addEventListener('submit', async (e) => {
      e.preventDefault();
      const payload = {};
      new FormData(form).forEach((v, k) => { payload[k] = v; });
      payload.id = Number(payload.id || 0);
      payload.is_active = Number(payload.is_active) === 1;
      try {
        const res = await apiPost('save_user', payload);
        toast(res.message);
        closeModal();
        await reloadState(true);
      } catch (err) {
        toast(err.message, true);
      }
    });
  }

  function renderCurrentView() {
    if (!state) return;
    switch (currentView) {
      case 'home': renderHomeEditor(); break;
      case 'about': renderAboutEditor(); break;
      case 'credit': renderCreditEditor(); break;
      case 'agricultural': renderAgriculturalEditor(); break;
      case 'authentication': renderAuthenticationEditor(); break;
      case 'privacy': renderPrivacyEditor(); break;
      case 'articles': renderArticlesManager(); break;
      case 'faqs': renderFaqsManager(); break;
      case 'inquiries': renderInquiriesView(); break;
      case 'system': renderSystemSettings(); break;
      case 'users': renderUsersManager(); break;
      default: renderDashboard(); break;
    }
    refreshIcons();
  }

  // ===========================================================================
  // Bootstrap Shell Interactions
  // ===========================================================================
  document.addEventListener('DOMContentLoaded', async () => {
    refreshIcons();

    // Theme toggle
    document.querySelectorAll('[data-cms-theme-toggle]').forEach((btn) => {
      btn.addEventListener('click', () => {
        const next = document.documentElement.getAttribute('data-theme') === 'dark' ? 'light' : 'dark';
        document.documentElement.setAttribute('data-theme', next);
        try { localStorage.setItem('xds-theme', next); } catch (e) {}
      });
    });

    // Sidebar filter search
    const filterInput = document.getElementById('cms-nav-filter');
    if (filterInput) {
      filterInput.addEventListener('input', () => {
        const q = filterInput.value.trim().toLowerCase();
        document.querySelectorAll('#cms-sidebar-nav .cms-nav-item').forEach((item) => {
          item.style.display = !q || item.textContent.toLowerCase().includes(q) ? '' : 'none';
        });
      });
    }

    // Mobile sidebar drawer
    const sb = document.getElementById('cms-sidebar');
    const bd = document.getElementById('cms-sidebar-backdrop');
    document.querySelectorAll('[data-sidebar-open]').forEach((b) => b.addEventListener('click', () => {
      sb.classList.add('is-open');
      bd.classList.add('is-open');
    }));
    document.querySelectorAll('[data-sidebar-close]').forEach((b) => b.addEventListener('click', () => {
      sb.classList.remove('is-open');
      bd.classList.remove('is-open');
    }));
    if (bd) bd.addEventListener('click', () => {
      sb.classList.remove('is-open');
      bd.classList.remove('is-open');
    });

    // Modal close handlers
    document.getElementById('cms-modal-close')?.addEventListener('click', closeModal);
    modalEl?.addEventListener('click', (e) => { if (e.target === modalEl) closeModal(); });
    document.addEventListener('keydown', (e) => { if (e.key === 'Escape') closeModal(); });

    // Restore inquiry tab preference if on Contact & Inquiries page
    try {
      const savedInqTab = sessionStorage.getItem('xds-cms-inq-tab');
      if (savedInqTab === 'inbox' || savedInqTab === 'settings') {
        inquiryTab = savedInqTab;
      }
    } catch (e) {}

    try {
      state = await apiGet();
      updateSidebarBadges();
      renderCurrentView();
    } catch (err) {
      workspace.innerHTML = `<div class="cms-card"><div class="cms-alert is-visible is-error">${esc(err.message)}</div></div>`;
    }
  });
})();
