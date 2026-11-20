<?php

declare(strict_types=1);

require_once dirname(__DIR__) . '/config/index.php';

/**
 * Renders the full CMS shell locked to a specific folder-based page view.
 *
 * @param string $viewKey     One of: dashboard, home, about, credit, agricultural,
 *                            authentication, privacy, articles, faqs, inquiries, system, users
 * @param string $pageName    Simple page title (rendered as `{pageName} | XDS Zimbabwe`)
 * @param array  $breadcrumbs Optional breadcrumb pairs [['label' => '...', 'href' => '...'], ...]
 */
function xds_cms_render_shell(string $viewKey, string $pageName, array $breadcrumbs = []): void
{
    $user   = xds_require_admin();
    $system = xds_get_system();
    $csrf   = xds_csrf_token();
    $dbLive = xds_db_connected();

    $initials = '';
    foreach (preg_split('/\s+/', trim((string)$user['full_name'])) ?: [] as $part) {
        if ($part !== '') {
            $initials .= strtoupper(substr($part, 0, 1));
        }
        if (strlen($initials) >= 2) break;
    }
    $initials = $initials ?: 'AD';

    if (empty($breadcrumbs)) {
        $breadcrumbs = [
            ['label' => 'CMS', 'href' => xds_url('cms/')],
            ['label' => $pageName, 'href' => null],
        ];
    }

    $cmsRoutes = [
        'dashboard'      => xds_url('cms/'),
        'inquiries'      => xds_url('cms/contact/'),
        'home'           => xds_url('cms/home/'),
        'about'          => xds_url('cms/about/'),
        'credit'         => xds_url('cms/products/credit-information/'),
        'agricultural'   => xds_url('cms/products/agricultural-services/'),
        'authentication' => xds_url('cms/products/authentication-services/'),
        'privacy'        => xds_url('cms/resources/data-privacy/'),
        'articles'       => xds_url('cms/resources/articles/'),
        'article_editor' => xds_url('cms/resources/articles/editor/'),
        'faqs'           => xds_url('cms/resources/faq/'),
        'system'         => xds_url('cms/settings/'),
        'users'          => xds_url('cms/users/'),
    ];
?>
<!DOCTYPE html>
<html lang="en" data-theme="light">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="robots" content="noindex, nofollow">
  <title><?= e($pageName) ?> | XDS Zimbabwe</title>
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
  <link rel="stylesheet" href="<?= e(ASSETS_CSS_URL . 'cms.css') ?>">
  <script>
    window.XDS_CMS_CONFIG = <?= json_encode([
      'view'        => $viewKey,
      'routes'      => $cmsRoutes,
      'apiUrl'      => xds_url('inc/cms/index.php'),
      'logoutUrl'   => xds_url('inc/auth/index.php?action=logout'),
      'basePath'    => BASE_PATH,
      'csrfToken'   => $csrf,
      'user'        => $user,
      'dbConnected' => $dbLive,
      'today'       => date('d F, Y'),
    ], JSON_UNESCAPED_SLASHES) ?>;
  </script>
</head>
<body class="cms-body">
  <div class="cms-sidebar-backdrop" id="cms-sidebar-backdrop"></div>

  <div class="cms-layout">
    <!-- Left Navigation Sidebar (Real Folder-Based Links) -->
    <aside class="cms-sidebar" id="cms-sidebar" aria-label="CMS Navigation">
      <div class="cms-sidebar-header">
        <a href="<?= e($cmsRoutes['dashboard']) ?>" class="portal-brand-link" aria-label="XDS CMS Dashboard">
          <img src="<?= e(xds_system_asset($system['logo_file'])) ?>" alt="XDS Zimbabwe" style="height:36px;width:auto;">
        </a>
        <button type="button" class="cms-bare-icon-btn cms-mobile-only" data-sidebar-close aria-label="Close sidebar">
          <i data-lucide="x" stroke-width="2.4"></i>
        </button>
      </div>

      <div class="cms-sidebar-search">
        <input type="search" id="cms-nav-filter" class="cms-input" placeholder="Search sections..." aria-label="Filter CMS navigation">
      </div>

      <nav class="cms-sidebar-scroll" id="cms-sidebar-nav">
        <div class="cms-nav-group">
          <div class="cms-nav-label">Workspace</div>
          <a href="<?= e($cmsRoutes['dashboard']) ?>" class="cms-nav-item<?= $viewKey === 'dashboard' ? ' is-active' : '' ?>">
            <i data-lucide="layout-grid"></i>
            <span>Dashboard</span>
          </a>
          <a href="<?= e($cmsRoutes['inquiries']) ?>" class="cms-nav-item<?= $viewKey === 'inquiries' ? ' is-active' : '' ?>">
            <i data-lucide="inbox"></i>
            <span>Contact &amp; Inquiries</span>
            <span class="cms-nav-count" id="nav-badge-inquiries">0</span>
          </a>
        </div>

        <div class="cms-nav-group">
          <div class="cms-nav-label">Public Pages</div>
          <a href="<?= e($cmsRoutes['home']) ?>" class="cms-nav-item<?= $viewKey === 'home' ? ' is-active' : '' ?>">
            <i data-lucide="home"></i>
            <span>Home Page</span>
          </a>
          <a href="<?= e($cmsRoutes['about']) ?>" class="cms-nav-item<?= $viewKey === 'about' ? ' is-active' : '' ?>">
            <i data-lucide="sliders-horizontal"></i>
            <span>About Us</span>
          </a>
          <a href="<?= e($cmsRoutes['credit']) ?>" class="cms-nav-item<?= $viewKey === 'credit' ? ' is-active' : '' ?>">
            <i data-lucide="file-spreadsheet"></i>
            <span>Credit Information</span>
          </a>
          <a href="<?= e($cmsRoutes['agricultural']) ?>" class="cms-nav-item<?= $viewKey === 'agricultural' ? ' is-active' : '' ?>">
            <i data-lucide="tractor"></i>
            <span>Agricultural Services</span>
          </a>
          <a href="<?= e($cmsRoutes['authentication']) ?>" class="cms-nav-item<?= $viewKey === 'authentication' ? ' is-active' : '' ?>">
            <i data-lucide="shield-check"></i>
            <span>Authentication (E Check)</span>
          </a>
          <a href="<?= e($cmsRoutes['privacy']) ?>" class="cms-nav-item<?= $viewKey === 'privacy' ? ' is-active' : '' ?>">
            <i data-lucide="lock-keyhole"></i>
            <span>Data Privacy (ISSP)</span>
          </a>
        </div>

        <div class="cms-nav-group">
          <div class="cms-nav-label">Collections</div>
          <a href="<?= e($cmsRoutes['articles']) ?>" class="cms-nav-item<?= $viewKey === 'articles' ? ' is-active' : '' ?>">
            <i data-lucide="newspaper"></i>
            <span>Articles</span>
            <span class="cms-nav-count" id="nav-badge-articles">0</span>
          </a>
          <a href="<?= e($cmsRoutes['faqs']) ?>" class="cms-nav-item<?= $viewKey === 'faqs' ? ' is-active' : '' ?>">
            <i data-lucide="help-circle"></i>
            <span>FAQ</span>
            <span class="cms-nav-count" id="nav-badge-faqs">0</span>
          </a>
        </div>

        <div class="cms-nav-group">
          <div class="cms-nav-label">Administration</div>
          <a href="<?= e($cmsRoutes['system']) ?>" class="cms-nav-item<?= $viewKey === 'system' ? ' is-active' : '' ?>">
            <i data-lucide="settings"></i>
            <span>Site Settings</span>
          </a>
          <a href="<?= e($cmsRoutes['users']) ?>" class="cms-nav-item<?= $viewKey === 'users' ? ' is-active' : '' ?>">
            <i data-lucide="users"></i>
            <span>Portal Users</span>
          </a>
          <a href="<?= e(xds_url('credit-portal/')) ?>" class="cms-nav-item">
            <i data-lucide="briefcase-business"></i>
            <span>Credit Portal View</span>
          </a>
          <a href="https://www.xds.co.zw/echeck/" target="_blank" rel="noopener noreferrer" class="cms-nav-item">
            <i data-lucide="external-link"></i>
            <span>E Check Portal</span>
          </a>
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

    <div class="cms-main-wrap">
      <header class="cms-topbar">
        <div class="cms-topbar-left">
          <button type="button" class="cms-bare-icon-btn cms-mobile-only" data-sidebar-open aria-label="Open navigation">
            <i data-lucide="menu" stroke-width="2.4"></i>
          </button>
          <nav class="cms-breadcrumb" aria-label="Breadcrumb">
            <?php foreach ($breadcrumbs as $idx => $crumb): ?>
              <?php if ($idx > 0): ?><span>/</span><?php endif; ?>
              <?php if (!empty($crumb['href'])): ?>
                <a href="<?= e($crumb['href']) ?>"><?= e($crumb['label']) ?></a>
              <?php else: ?>
                <span aria-current="page"><?= e($crumb['label']) ?></span>
              <?php endif; ?>
            <?php endforeach; ?>
          </nav>
        </div>

        <div class="cms-topbar-right">
          <a href="<?= e(xds_url('')) ?>" target="_blank" class="cms-btn cms-btn-ghost cms-btn-sm">
            <i data-lucide="external-link"></i>
            <span>View Site</span>
          </a>
          <a href="<?= e($cmsRoutes['article_editor']) ?>" class="cms-btn cms-btn-lime cms-btn-sm">
            <i data-lucide="plus"></i>
            <span>New Article</span>
          </a>
          <button type="button" class="cms-bare-icon-btn" data-cms-theme-toggle aria-label="Toggle light and dark mode" title="Toggle Theme">
            <i data-lucide="sun" stroke-width="2.4" class="icon-sun"></i>
            <i data-lucide="moon" stroke-width="2.4" class="icon-moon"></i>
          </button>
        </div>
      </header>

      <main class="cms-content" id="cms-workspace">
        <div class="cms-card" style="text-align:center;padding:48px;">
          <p style="margin:0;color:var(--cms-muted);">Loading <?= e($pageName) ?>...</p>
        </div>
      </main>
    </div>
  </div>

  <div class="cms-modal" id="cms-modal" role="dialog" aria-modal="true" aria-hidden="true">
    <div class="cms-modal-box">
      <div class="cms-modal-head">
        <h3 id="cms-modal-title">Edit</h3>
        <button type="button" class="cms-bare-icon-btn" id="cms-modal-close" aria-label="Close modal">
          <i data-lucide="x" stroke-width="2.4"></i>
        </button>
      </div>
      <div id="cms-modal-body"></div>
    </div>
  </div>

  <div class="cms-toast-stack" id="cms-toast-stack" aria-live="polite"></div>

  <script src="https://unpkg.com/lucide@latest/dist/umd/lucide.min.js"></script>
  <script src="<?= e(ASSETS_JS_URL . 'cms.js') ?>"></script>
</body>
</html>
<?php
}
