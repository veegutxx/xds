<?php
/**
 * XDS Zimbabwe CMS — Dedicated Article Studio (`/cms/resources/articles/editor/`)
 * Supports creating a new article (`/cms/resources/articles/editor/`) or editing (`/cms/resources/articles/editor/?id={id}`).
 */

declare(strict_types=1);

require_once dirname(__DIR__, 4) . '/inc/config/index.php';

$user   = xds_require_admin();
$system = xds_get_system();
$csrf   = xds_csrf_token();
$pdo    = xds_db();

$editId  = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$article = null;

if ($editId > 0) {
    if ($pdo !== null) {
        try {
            $stmt = $pdo->prepare('SELECT * FROM `page_articles` WHERE `id` = :id LIMIT 1');
            $stmt->execute([':id' => $editId]);
            $row = $stmt->fetch();
            if (is_array($row)) {
                $article = $row;
            }
        } catch (Throwable $e) {
            error_log('[XDS CMS Article Load] ' . $e->getMessage());
        }
    } else {
        foreach (xds_defaults('page_articles') as $def) {
            if ((int)($def['id'] ?? 0) === $editId) {
                $article = $def;
                $article['is_active'] = 1;
                break;
            }
        }
    }
}

$isEdit    = $article !== null;
$pageTitle = $isEdit ? 'Edit Article' : 'Create Article';
$artTitle  = (string)($article['title'] ?? '');
$artSlug   = (string)($article['slug'] ?? '');
$artImage  = (string)($article['image_file'] ?? 'cover.webp');
$artCat    = (string)($article['category'] ?? 'Bureau Insights');
$artAuthor = (string)($article['author'] ?? 'XDS Research Desk');
$artDate   = (string)($article['published_date'] ?? date('Y-m-d'));
$artRead   = (int)($article['read_time_minutes'] ?? 4);
$artSort   = (int)($article['sort_order'] ?? 1);
$artActive = $isEdit ? (int)($article['is_active'] ?? 1) : 1;
$artBody   = (string)($article['body_content'] ?? '');
$coverPreviewUrl = ($artSlug !== '' && $artImage !== '')
    ? xds_article_asset($artSlug, $artImage)
    : '';

$initials = '';
foreach (preg_split('/\s+/', trim((string)$user['full_name'])) ?: [] as $part) {
    if ($part !== '') $initials .= strtoupper(substr($part, 0, 1));
    if (strlen($initials) >= 2) break;
}
$initials = $initials ?: 'AD';
?>
<!DOCTYPE html>
<html lang="en" data-theme="light">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="robots" content="noindex, nofollow">
  <title><?= e($pageTitle) ?> | XDS Zimbabwe</title>
  <link rel="icon" type="image/png" href="<?= e(xds_system_asset($system['favicon_file'])) ?>">
  <link rel="apple-touch-icon" href="<?= e(xds_system_asset($system['apple_touch_icon_file'])) ?>">
  <script>
    (function () {
      try {
        var saved = localStorage.getItem('xds-theme');
        var prefersDark = window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches;
        document.documentElement.setAttribute('data-theme', saved === 'dark' || saved === 'light' ? saved : (prefersDark ? 'dark' : 'light'));
      } catch (e) {}
    })();
  </script>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Plus+Jakarta+Sans:wght@600;700;800&display=swap" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/quill@2.0.3/dist/quill.snow.css" rel="stylesheet">
  <link rel="stylesheet" href="<?= e(ASSETS_CSS_URL . 'cms.css') ?>">
</head>
<body class="cms-body">
  <div class="cms-sidebar-backdrop" id="cms-sidebar-backdrop"></div>

  <div class="cms-layout">
    <!-- Left Navigation Sidebar (Real Folder-Based Links) -->
    <aside class="cms-sidebar" id="cms-sidebar" aria-label="CMS Navigation">
      <div class="cms-sidebar-header">
        <a href="<?= e(xds_url('cms/')) ?>" class="portal-brand-link" aria-label="XDS CMS Dashboard">
          <img src="<?= e(xds_system_asset($system['logo_file'])) ?>" alt="XDS Zimbabwe" style="height:36px;width:auto;">
        </a>
        <button type="button" class="cms-bare-icon-btn cms-mobile-only" data-sidebar-close aria-label="Close sidebar">
          <i data-lucide="x" stroke-width="2.4"></i>
        </button>
      </div>

      <nav class="cms-sidebar-scroll">
        <div class="cms-nav-group">
          <div class="cms-nav-label">Workspace</div>
          <a href="<?= e(xds_url('cms/')) ?>" class="cms-nav-item"><i data-lucide="layout-grid"></i><span>Dashboard</span></a>
          <a href="<?= e(xds_url('cms/contact/')) ?>" class="cms-nav-item"><i data-lucide="inbox"></i><span>Contact &amp; Inquiries</span></a>
        </div>

        <div class="cms-nav-group">
          <div class="cms-nav-label">Public Pages</div>
          <a href="<?= e(xds_url('cms/home/')) ?>" class="cms-nav-item"><i data-lucide="home"></i><span>Home Page</span></a>
          <a href="<?= e(xds_url('cms/about/')) ?>" class="cms-nav-item"><i data-lucide="sliders-horizontal"></i><span>About Us</span></a>
          <a href="<?= e(xds_url('cms/products/credit-information/')) ?>" class="cms-nav-item"><i data-lucide="file-spreadsheet"></i><span>Credit Information</span></a>
          <a href="<?= e(xds_url('cms/products/agricultural-services/')) ?>" class="cms-nav-item"><i data-lucide="tractor"></i><span>Agricultural Services</span></a>
          <a href="<?= e(xds_url('cms/products/authentication-services/')) ?>" class="cms-nav-item"><i data-lucide="shield-check"></i><span>Authentication (E Check)</span></a>
          <a href="<?= e(xds_url('cms/resources/data-privacy/')) ?>" class="cms-nav-item"><i data-lucide="lock-keyhole"></i><span>Data Privacy (ISSP)</span></a>
        </div>

        <div class="cms-nav-group">
          <div class="cms-nav-label">Collections</div>
          <a href="<?= e(xds_url('cms/resources/articles/')) ?>" class="cms-nav-item is-active"><i data-lucide="newspaper"></i><span>Articles</span></a>
          <a href="<?= e(xds_url('cms/resources/faq/')) ?>" class="cms-nav-item"><i data-lucide="help-circle"></i><span>FAQ</span></a>
        </div>

        <div class="cms-nav-group">
          <div class="cms-nav-label">Administration</div>
          <a href="<?= e(xds_url('cms/settings/')) ?>" class="cms-nav-item"><i data-lucide="settings"></i><span>Site Settings</span></a>
          <a href="<?= e(xds_url('cms/users/')) ?>" class="cms-nav-item"><i data-lucide="users"></i><span>Portal Users</span></a>
        </div>
      </nav>

      <div class="cms-sidebar-footer">
        <div class="cms-user-chip">
          <span class="cms-user-avatar"><?= e($initials) ?></span>
          <div class="cms-user-meta">
            <strong><?= e($user['full_name']) ?></strong>
            <span><?= e(ucfirst((string)$user['role'])) ?></span>
          </div>
        </div>
        <a href="<?= e(xds_url('inc/auth/index.php?action=logout')) ?>" class="cms-bare-icon-btn" title="Sign Out" aria-label="Sign Out">
          <i data-lucide="log-out" stroke-width="2.4"></i>
        </a>
      </div>
    </aside>

    <!-- Main Column -->
    <div class="cms-main-wrap">
      <header class="cms-topbar">
        <div class="cms-topbar-left">
          <button type="button" class="cms-bare-icon-btn cms-mobile-only" data-sidebar-open aria-label="Open menu">
            <i data-lucide="menu" stroke-width="2.4"></i>
          </button>
          <nav class="cms-breadcrumb" aria-label="Breadcrumb">
            <a href="<?= e(xds_url('cms/')) ?>">CMS</a>
            <span>/</span>
            <a href="<?= e(xds_url('cms/resources/articles/')) ?>">Articles</a>
            <span>/</span>
            <span aria-current="page"><?= e($pageTitle) ?></span>
          </nav>
        </div>

        <div class="cms-topbar-right">
          <a href="<?= e(xds_url('cms/resources/articles/')) ?>" class="cms-btn cms-btn-ghost cms-btn-sm">
            <i data-lucide="arrow-left"></i>
            <span>All Articles</span>
          </a>
          <button type="button" class="cms-bare-icon-btn" data-cms-theme-toggle aria-label="Toggle theme" title="Toggle Theme">
            <i data-lucide="sun" stroke-width="2.4" class="icon-sun"></i>
            <i data-lucide="moon" stroke-width="2.4" class="icon-moon"></i>
          </button>
        </div>
      </header>

      <main class="cms-content">
        <div class="cms-page-head">
          <div>
            <h1><?= e($pageTitle) ?></h1>
            <p>Slug, meta title, summary, and meta description are automatically generated as you write.</p>
          </div>
          <div style="display:flex;gap:10px;flex-wrap:wrap;">
            <?php if ($isEdit && $artSlug !== ''): ?>
            <a href="<?= e(xds_url('resources/articles/view/?slug=' . rawurlencode($artSlug))) ?>" target="_blank" class="cms-btn cms-btn-ghost cms-btn-sm" id="btn-view-live-article">
              <i data-lucide="external-link"></i>
              <span>View Live Article</span>
            </a>
            <?php else: ?>
            <a href="#" target="_blank" class="cms-btn cms-btn-ghost cms-btn-sm" id="btn-view-live-article" style="display:none;">
              <i data-lucide="external-link"></i>
              <span>View Live Article</span>
            </a>
            <?php endif; ?>
            <button type="submit" form="article-studio-form" class="cms-btn cms-btn-lime" id="btn-save-top">
              <i data-lucide="save"></i>
              <span><?= $isEdit ? 'Save Changes' : 'Publish Article' ?></span>
            </button>
          </div>
        </div>

        <form id="article-studio-form" novalidate>
          <input type="hidden" name="id" id="art-id" value="<?= (int)($article['id'] ?? 0) ?>">

          <div class="cms-article-studio-grid">
            <div class="cms-studio-main">
              <div class="cms-card">
                <div class="cms-field">
                  <label for="art-title">Article Title <span style="color:#E11D48;">*</span></label>
                  <input id="art-title" name="title" type="text" class="cms-input cms-title-input" required value="<?= e($artTitle) ?>" placeholder="Enter a clear, compelling article title...">
                </div>

                <div class="cms-auto-slug-bar">
                  <span class="cms-auto-slug-label">Auto-generated Slug &amp; URL:</span>
                  <code id="art-slug-preview"><?= e($artSlug ?: 'enter-article-title') ?></code>
                  <input type="hidden" name="slug" id="art-slug-input" value="<?= e($artSlug) ?>">
                </div>

                <div class="cms-field" style="margin-top:18px;margin-bottom:0;">
                  <label>Article Content <span style="color:#E11D48;">*</span> <span style="font-weight:400;color:var(--cms-muted);">(supports headings, links, inline image uploads, lists &amp; formatting)</span></label>
                  <div class="cms-quill-wrap">
                    <div id="art-quill-editor"></div>
                  </div>
                </div>
              </div>

              <div class="cms-card">
                <div class="cms-card-header">
                  <div>
                    <h2>Auto-Generated Summary &amp; SEO Preview</h2>
                    <p>Generated automatically from your title and first paragraph (max 250 characters).</p>
                  </div>
                  <span class="cms-badge cms-badge-lime" id="seo-char-counter">0 / 250 chars</span>
                </div>

                <div class="cms-seo-preview-box">
                  <div class="cms-seo-url" id="seo-preview-url">https://xds.co.zw/resources/articles/<?= e($artSlug ?: 'article-slug') ?>/</div>
                  <div class="cms-seo-title" id="seo-preview-title"><?= e($artTitle !== '' ? ($artTitle . ' | XDS Zimbabwe') : 'Article Title | XDS Zimbabwe') ?></div>
                  <p class="cms-seo-desc" id="seo-preview-desc">Start typing your first paragraph above to preview the auto-generated summary and meta description...</p>
                </div>
              </div>
            </div>

            <div class="cms-studio-side">
              <div class="cms-card">
                <div class="cms-card-header">
                  <div>
                    <h2>Cover Image</h2>
                    <p>Also used as the article's share preview &amp; Apple Touch Icon.</p>
                  </div>
                </div>

                <div class="cms-cover-preview-stage" id="cover-preview-stage">
                  <?php if ($coverPreviewUrl !== ''): ?>
                    <img id="cover-preview-img" src="<?= e($coverPreviewUrl) ?>" alt="Article cover preview">
                  <?php else: ?>
                    <img id="cover-preview-img" src="" alt="Article cover preview" style="display:none;">
                    <div id="cover-preview-placeholder" class="cms-cover-placeholder">
                      <i data-lucide="image-plus"></i>
                      <span>Upload a cover photo (16:10 recommended)</span>
                    </div>
                  <?php endif; ?>
                </div>

                <div class="cms-field" style="margin-top:14px;margin-bottom:0;">
                  <label for="art-image-file">Cover Filename <span style="font-weight:400;color:var(--cms-muted);">(stored in <code>articles/{slug}/</code>)</span></label>
                  <div class="cms-upload-controls">
                    <input id="art-image-file" name="image_file" type="text" class="cms-input" value="<?= e($artImage) ?>" placeholder="cover.webp">
                    <label class="cms-btn cms-btn-indigo cms-btn-sm" style="margin:0;cursor:pointer;">
                      <i data-lucide="upload"></i>
                      <span>Upload</span>
                      <input type="file" id="art-cover-file-input" hidden accept=".png,.jpg,.jpeg,.webp">
                    </label>
                  </div>
                </div>
              </div>

              <div class="cms-card">
                <div class="cms-card-header"><div><h2>Publishing Details</h2></div></div>

                <div class="cms-field">
                  <label for="art-status">Status</label>
                  <select id="art-status" name="is_active" class="cms-select">
                    <option value="1" <?= $artActive === 1 ? 'selected' : '' ?>>Published (Live)</option>
                    <option value="0" <?= $artActive === 0 ? 'selected' : '' ?>>Draft (Hidden)</option>
                  </select>
                </div>

                <div class="cms-field">
                  <label for="art-category">Category</label>
                  <input id="art-category" name="category" type="text" class="cms-input" value="<?= e($artCat) ?>" placeholder="Credit Scoring">
                </div>

                <div class="cms-field">
                  <label for="art-author">Author</label>
                  <input id="art-author" name="author" type="text" class="cms-input" value="<?= e($artAuthor) ?>" placeholder="XDS Research Desk">
                </div>

                <div class="cms-form-row-2">
                  <div class="cms-field">
                    <label for="art-date">Published Date</label>
                    <input id="art-date" name="published_date" type="date" class="cms-input" value="<?= e($artDate) ?>">
                  </div>
                  <div class="cms-field">
                    <label for="art-read">Read Minutes</label>
                    <input id="art-read" name="read_time_minutes" type="number" min="1" class="cms-input" value="<?= $artRead ?>">
                  </div>
                </div>

                <div class="cms-field">
                  <label for="art-sort">Sort Order</label>
                  <input id="art-sort" name="sort_order" type="number" min="0" class="cms-input" value="<?= $artSort ?>">
                </div>

                <div style="display:flex;flex-direction:column;gap:10px;margin-top:16px;">
                  <button type="submit" class="cms-btn cms-btn-lime" id="btn-save-side" style="width:100%;">
                    <i data-lucide="save"></i>
                    <span><?= $isEdit ? 'Save Changes' : 'Publish Article' ?></span>
                  </button>
                  <a href="<?= e(xds_url('cms/resources/articles/')) ?>" class="cms-btn cms-btn-ghost" style="width:100%;">
                    <span>Cancel &amp; Return</span>
                  </a>
                </div>
              </div>
            </div>
          </div>
        </form>
      </main>
    </div>
  </div>

  <div class="cms-toast-stack" id="cms-toast-stack" aria-live="polite"></div>

  <script src="https://unpkg.com/lucide@latest/dist/umd/lucide.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/quill@2.0.3/dist/quill.js"></script>
  <script>
    (function () {
      'use strict';

      if (window.lucide) window.lucide.createIcons();

      const API_URL = <?= json_encode(xds_url('inc/cms/index.php'), JSON_UNESCAPED_SLASHES) ?>;
      const CSRF = <?= json_encode($csrf) ?>;
      const BASE_PATH = <?= json_encode(BASE_PATH) ?>;
      const INITIAL_BODY = <?= json_encode($artBody, JSON_UNESCAPED_UNICODE) ?>;

      const toastStack = document.getElementById('cms-toast-stack');
      function toast(msg, isError) {
        const el = document.createElement('div');
        el.className = 'cms-toast' + (isError ? ' is-error' : '');
        el.textContent = msg;
        toastStack.appendChild(el);
        setTimeout(() => { el.style.opacity = '0'; setTimeout(() => el.remove(), 240); }, 4200);
      }

      document.querySelectorAll('[data-cms-theme-toggle]').forEach((btn) => {
        btn.addEventListener('click', () => {
          const next = document.documentElement.getAttribute('data-theme') === 'dark' ? 'light' : 'dark';
          document.documentElement.setAttribute('data-theme', next);
          try { localStorage.setItem('xds-theme', next); } catch (e) {}
        });
      });
      const sb = document.getElementById('cms-sidebar');
      const bd = document.getElementById('cms-sidebar-backdrop');
      document.querySelectorAll('[data-sidebar-open]').forEach((b) => b.addEventListener('click', () => { sb.classList.add('is-open'); bd.classList.add('is-open'); }));
      document.querySelectorAll('[data-sidebar-close]').forEach((b) => b.addEventListener('click', () => { sb.classList.remove('is-open'); bd.classList.remove('is-open'); }));
      if (bd) bd.addEventListener('click', () => { sb.classList.remove('is-open'); bd.classList.remove('is-open'); });

      function slugify(str) {
        return String(str || '').toLowerCase().trim().replace(/[^a-z0-9]+/g, '-').replace(/^-+|-+$/g, '');
      }

      function extractFirstParagraph(html, maxLen) {
        const tmp = document.createElement('div');
        tmp.innerHTML = html || '';
        const blocks = tmp.querySelectorAll('p, div, li, blockquote');
        let text = '';
        for (const el of blocks) {
          const t = (el.textContent || '').replace(/\s+/g, ' ').trim();
          if (t) { text = t; break; }
        }
        if (!text) text = (tmp.textContent || '').replace(/\s+/g, ' ').trim();
        if (text.length <= maxLen) return text;
        let cut = text.slice(0, maxLen - 3);
        const lastSp = cut.lastIndexOf(' ');
        if (lastSp > maxLen * 0.6) cut = cut.slice(0, lastSp);
        return cut.replace(/[\s.,;:-]+$/, '') + '...';
      }

      const titleInput = document.getElementById('art-title');
      const slugInput = document.getElementById('art-slug-input');
      const slugPreview = document.getElementById('art-slug-preview');
      const seoUrl = document.getElementById('seo-preview-url');
      const seoTitle = document.getElementById('seo-preview-title');
      const seoDesc = document.getElementById('seo-preview-desc');
      const seoCounter = document.getElementById('seo-char-counter');
      const readMinInput = document.getElementById('art-read');

      async function uploadArticleImage(file) {
        const currentSlug = slugify(titleInput.value);
        if (!currentSlug) {
          throw new Error('Please type the Article Title first so the slug folder can be created.');
        }
        const fd = new FormData();
        fd.append('action', 'upload_asset');
        fd.append('csrf_token', CSRF);
        fd.append('target', 'article');
        fd.append('slug', currentSlug);
        fd.append('file', file);
        const res = await fetch(API_URL, {
          method: 'POST',
          headers: { 'X-CSRF-Token': CSRF, Accept: 'application/json' },
          body: fd
        });
        const data = await res.json();
        if (!res.ok || !data.ok) throw new Error(data.message || 'Image upload failed.');
        return data;
      }

      const quill = new Quill('#art-quill-editor', {
        theme: 'snow',
        placeholder: 'Write your article here... Start with an informative opening paragraph (used automatically for the article summary and SEO meta description).',
        modules: {
          toolbar: {
            container: [
              [{ header: [2, 3, 4, false] }],
              ['bold', 'italic', 'underline', 'strike'],
              [{ color: [] }, { background: [] }],
              [{ list: 'ordered' }, { list: 'bullet' }, 'blockquote'],
              [{ align: [] }],
              ['link', 'image'],
              ['clean']
            ],
            handlers: {
              image: function () {
                const picker = document.createElement('input');
                picker.type = 'file';
                picker.accept = '.png,.jpg,.jpeg,.webp';
                picker.onchange = async () => {
                  const file = picker.files && picker.files[0];
                  if (!file) return;
                  try {
                    toast('Uploading inline image...');
                    const uploaded = await uploadArticleImage(file);
                    const range = quill.getSelection(true);
                    quill.insertEmbed(range.index, 'image', uploaded.preview_url, 'user');
                    quill.setSelection(range.index + 1);
                    toast('Inline image inserted.');
                  } catch (err) {
                    toast(err.message, true);
                  }
                };
                picker.click();
              }
            }
          }
        }
      });

      if (INITIAL_BODY) {
        quill.clipboard.dangerouslyPasteHTML(INITIAL_BODY);
      }

      function syncAutoMeta() {
        const t = titleInput.value.trim();
        const s = slugify(t);
        slugInput.value = s;
        slugPreview.textContent = s || 'enter-article-title';
        seoUrl.textContent = 'https://xds.co.zw/resources/articles/' + (s || 'article-slug') + '/';
        seoTitle.textContent = (t || 'Article Title') + ' | XDS Zimbabwe';

        const html = quill.root.innerHTML;
        const summary = extractFirstParagraph(html, 250);
        seoDesc.textContent = summary || 'Start typing your first paragraph above to preview the auto-generated summary and meta description...';
        seoCounter.textContent = summary.length + ' / 250 chars';

        if (!readMinInput.dataset.manual) {
          const words = (quill.getText() || '').trim().split(/\s+/).filter(Boolean).length;
          if (words > 0) readMinInput.value = Math.max(1, Math.round(words / 200));
        }
      }

      titleInput.addEventListener('input', syncAutoMeta);
      quill.on('text-change', syncAutoMeta);
      readMinInput.addEventListener('input', () => { readMinInput.dataset.manual = '1'; });
      syncAutoMeta();

      const coverFileInput = document.getElementById('art-cover-file-input');
      const coverNameInput = document.getElementById('art-image-file');
      const coverImg = document.getElementById('cover-preview-img');
      const coverPlaceholder = document.getElementById('cover-preview-placeholder');

      coverFileInput.addEventListener('change', async () => {
        const file = coverFileInput.files && coverFileInput.files[0];
        if (!file) return;
        try {
          toast('Uploading cover image...');
          const uploaded = await uploadArticleImage(file);
          coverNameInput.value = uploaded.filename;
          coverImg.src = uploaded.preview_url + '?t=' + Date.now();
          coverImg.style.display = 'block';
          if (coverPlaceholder) coverPlaceholder.style.display = 'none';
          toast(uploaded.message);
        } catch (err) {
          toast(err.message, true);
        } finally {
          coverFileInput.value = '';
        }
      });

      const form = document.getElementById('article-studio-form');
      const saveBtns = [document.getElementById('btn-save-top'), document.getElementById('btn-save-side')];

      form.addEventListener('submit', async (e) => {
        e.preventDefault();
        const title = titleInput.value.trim();
        const bodyHtml = quill.root.innerHTML;
        if (!title) {
          toast('Please enter an article title.', true);
          titleInput.focus();
          return;
        }
        if (!quill.getText().trim()) {
          toast('Please write article content before saving.', true);
          quill.focus();
          return;
        }

        const payload = {
          action: 'save_article',
          csrf_token: CSRF,
          id: Number(document.getElementById('art-id').value || 0),
          title: title,
          image_file: coverNameInput.value.trim() || 'cover.webp',
          category: document.getElementById('art-category').value.trim() || 'Bureau Insights',
          author: document.getElementById('art-author').value.trim() || 'XDS Research Desk',
          published_date: document.getElementById('art-date').value,
          read_time_minutes: Number(readMinInput.value || 4),
          sort_order: Number(document.getElementById('art-sort').value || 0),
          is_active: Number(document.getElementById('art-status').value) === 1,
          body_content: bodyHtml
        };

        saveBtns.forEach((b) => { if (b) b.disabled = true; });
        try {
          const res = await fetch(API_URL, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-Token': CSRF, Accept: 'application/json' },
            body: JSON.stringify(payload)
          });
          const data = await res.json();
          if (!res.ok || !data.ok) throw new Error(data.message || 'Could not save article.');

          document.getElementById('art-id').value = data.id;
          if (data.slug) {
            slugInput.value = data.slug;
            slugPreview.textContent = data.slug;
            const liveBtn = document.getElementById('btn-view-live-article');
            if (liveBtn) {
              liveBtn.href = BASE_PATH + 'resources/articles/view/?slug=' + encodeURIComponent(data.slug);
              liveBtn.style.display = 'inline-flex';
            }
          }
          if (window.history && typeof window.history.replaceState === 'function') {
            window.history.replaceState(null, '', '?id=' + data.id);
          }
          toast(data.message);
        } catch (err) {
          toast(err.message, true);
        } finally {
          saveBtns.forEach((b) => { if (b) b.disabled = false; });
        }
      });
    })();
  </script>
</body>
</html>
