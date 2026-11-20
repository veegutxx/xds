<?php

declare(strict_types=1);

require_once dirname(__DIR__) . '/config/index.php';
require_once dirname(__DIR__) . '/seo/index.php';

$system        = xds_get_system();
$activeNav     = $activeNav ?? 'home';
$activeSub     = $activeSub ?? '';
$pageMeta      = $pageMeta ?? [];
$loadThree     = $loadThree ?? false;
$loadFlatpickr = $loadFlatpickr ?? false;
$csrfToken     = xds_csrf_token();
?>
<!DOCTYPE html>
<html lang="en" data-theme="light">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="theme-color" content="#252D9D">
  <?php xds_render_seo_tags($pageMeta, $system); ?>

  <link rel="icon" type="image/png" href="<?= e(xds_system_asset($system['favicon_file'])) ?>">
  <?php $appleTouchHref = !empty($pageMeta['apple_touch_icon_url']) ? (string)$pageMeta['apple_touch_icon_url'] : xds_system_asset($system['apple_touch_icon_file']); ?>
  <link rel="apple-touch-icon" href="<?= e($appleTouchHref) ?>">

  <script>
    (function () {
      try {
        var saved = localStorage.getItem('xds-theme');
        var prefersDark = window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches;
        var theme = saved === 'dark' || saved === 'light' ? saved : (prefersDark ? 'dark' : 'light');
        document.documentElement.setAttribute('data-theme', theme);
      } catch (e) {}
    })();
  </script>

  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Plus+Jakarta+Sans:wght@600;700;800&display=swap" rel="stylesheet">

  <?php if ($loadFlatpickr): ?>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
  <?php endif; ?>

  <link rel="stylesheet" href="<?= e(ASSETS_CSS_URL . 'style.css') ?>">

  <script>
    window.XDS_CONFIG = <?= json_encode([
      'basePath'  => BASE_PATH,
      'csrfToken' => $csrfToken,
    ], JSON_UNESCAPED_SLASHES) ?>;
  </script>
</head>
<body>
  <a class="skip-link" href="#main-content">Skip to main content</a>

  <header class="site-header" role="banner">
    <div class="container nav-inner">
      <a href="<?= e(xds_url('')) ?>" class="brand-link" aria-label="<?= e($system['site_name']) ?> Home">
        <img
          src="<?= e(xds_system_asset($system['logo_file'])) ?>"
          alt="XDS Expert Decision Systems Zimbabwe Logo"
          class="brand-logo"
          width="112"
          height="44"
        >
      </a>

      <!-- Desktop Navigation -->
      <nav class="nav-desktop" aria-label="Primary Navigation">
        <ul class="nav-list">
          <li class="nav-item">
            <a href="<?= e(xds_url('')) ?>" class="nav-link<?= $activeNav === 'home' ? ' is-active' : '' ?>">
              <i data-lucide="home" class="nav-icon" aria-hidden="true"></i>
              <span>Home</span>
            </a>
          </li>

          <li class="nav-item">
            <a href="<?= e(xds_url('about/')) ?>" class="nav-link<?= $activeNav === 'about' ? ' is-active' : '' ?>">
              <i data-lucide="sliders-horizontal" class="nav-icon" aria-hidden="true"></i>
              <span>About</span>
            </a>
          </li>

          <li class="nav-item">
            <button
              type="button"
              class="nav-link<?= $activeNav === 'products' ? ' is-active' : '' ?>"
              data-nav-dropdown-btn
              aria-expanded="false"
              aria-haspopup="true"
            >
              <i data-lucide="briefcase-business" class="nav-icon" aria-hidden="true"></i>
              <span>Products</span>
              <i data-lucide="chevron-down" class="chevron-icon" aria-hidden="true"></i>
            </button>
            <div class="nav-dropdown-menu" role="menu">
              <a href="<?= e(xds_url('products/credit-information/')) ?>" class="nav-dropdown-link<?= $activeSub === 'credit' ? ' is-active' : '' ?>" role="menuitem">
                <span class="dropdown-icon-box"><i data-lucide="file-spreadsheet"></i></span>
                <div>
                  <strong>Credit Information Services</strong>
                  <span>Consumer &amp; commercial reports, tracing</span>
                </div>
              </a>
              <a href="<?= e(xds_url('products/agricultural-services/')) ?>" class="nav-dropdown-link<?= $activeSub === 'agricultural' ? ' is-active' : '' ?>" role="menuitem">
                <span class="dropdown-icon-box"><i data-lucide="tractor"></i></span>
                <div>
                  <strong>Agricultural Services</strong>
                  <span>Agro scoring, exposure &amp; double-dip alerts</span>
                </div>
              </a>
              <a href="<?= e(xds_url('products/authentication-services/')) ?>" class="nav-dropdown-link<?= $activeSub === 'authentication' ? ' is-active' : '' ?>" role="menuitem">
                <span class="dropdown-icon-box"><i data-lucide="shield-check"></i></span>
                <div>
                  <strong>Authentication Services</strong>
                  <span>E Check background &amp; credential vetting</span>
                </div>
              </a>
            </div>
          </li>

          <li class="nav-item">
            <button
              type="button"
              class="nav-link<?= $activeNav === 'resources' ? ' is-active' : '' ?>"
              data-nav-dropdown-btn
              aria-expanded="false"
              aria-haspopup="true"
            >
              <i data-lucide="database" class="nav-icon" aria-hidden="true"></i>
              <span>Resources</span>
              <i data-lucide="chevron-down" class="chevron-icon" aria-hidden="true"></i>
            </button>
            <div class="nav-dropdown-menu" role="menu">
              <a href="<?= e(xds_url('resources/articles/')) ?>" class="nav-dropdown-link<?= $activeSub === 'articles' ? ' is-active' : '' ?>" role="menuitem">
                <span class="dropdown-icon-box"><i data-lucide="newspaper"></i></span>
                <div>
                  <strong>Articles &amp; Insights</strong>
                  <span>Bureau research, Basel II &amp; credit guides</span>
                </div>
              </a>
              <a href="<?= e(xds_url('resources/data-privacy/')) ?>" class="nav-dropdown-link<?= $activeSub === 'privacy' ? ' is-active' : '' ?>" role="menuitem">
                <span class="dropdown-icon-box"><i data-lucide="lock-keyhole"></i></span>
                <div>
                  <strong>Data Privacy (ISSP)</strong>
                  <span>Information security policy &amp; governance</span>
                </div>
              </a>
              <a href="<?= e(xds_url('resources/faq/')) ?>" class="nav-dropdown-link<?= $activeSub === 'faq' ? ' is-active' : '' ?>" role="menuitem">
                <span class="dropdown-icon-box"><i data-lucide="help-circle"></i></span>
                <div>
                  <strong>Frequently Asked Questions</strong>
                  <span>Data submission, regulation &amp; accuracy</span>
                </div>
              </a>
            </div>
          </li>

          <li class="nav-item">
            <a href="<?= e(xds_url('contact/')) ?>" class="nav-link<?= $activeNav === 'contact' ? ' is-active' : '' ?>">
              <i data-lucide="mail" class="nav-icon" aria-hidden="true"></i>
              <span>Contact</span>
            </a>
          </li>
        </ul>
      </nav>

      <div class="nav-actions">
        <button
          type="button"
          class="nav-bare-icon-btn theme-toggle-btn nav-desktop-only"
          data-theme-toggle
          aria-label="Toggle light and dark mode"
          aria-pressed="false"
          title="Switch Light / Dark Mode"
        >
          <i data-lucide="sun" stroke-width="2.5" class="icon-sun" aria-hidden="true"></i>
          <i data-lucide="moon" stroke-width="2.5" class="icon-moon" aria-hidden="true"></i>
        </button>

        <div class="avatar-portal-wrap nav-desktop-only" data-avatar-portal>
          <button
            type="button"
            class="nav-bare-icon-btn avatar-trigger-btn"
            data-avatar-trigger
            aria-label="Client Portal Access"
            aria-expanded="false"
            aria-haspopup="menu"
            title="Client Portals"
          >
            <i data-lucide="user-round" stroke-width="2.5" aria-hidden="true"></i>
          </button>

          <div class="avatar-popover" role="menu" aria-label="XDS Portals">
            <div class="avatar-popover-header">
              <span class="avatar-badge-lg"><i data-lucide="shield-user"></i></span>
              <div>
                <strong>XDS Portals</strong>
                <span>Secure sign-in</span>
              </div>
            </div>
            <?php
              $creditPortalHref = xds_portal_href((string)$system['credit_portal_url']);
              $creditExternal   = xds_is_external_url((string)$system['credit_portal_url']);
              $authPortalHref   = xds_portal_href((string)$system['auth_portal_url']);
            ?>
            <?php if (xds_is_admin()): ?>
            <a href="<?= e(xds_url('cms/')) ?>" class="nav-dropdown-link" role="menuitem">
              <span class="dropdown-icon-box"><i data-lucide="layout-dashboard"></i></span>
              <div>
                <strong>CMS Dashboard</strong>
                <span>Signed in as <?= e(xds_current_user()['full_name'] ?? 'Admin') ?></span>
              </div>
            </a>
            <?php endif; ?>
            <a href="<?= e($creditPortalHref) ?>"<?= $creditExternal ? ' target="_blank" rel="noopener noreferrer"' : '' ?> class="nav-dropdown-link" role="menuitem">
              <span class="dropdown-icon-box"><i data-lucide="file-spreadsheet"></i></span>
              <div>
                <strong>Portal</strong>
                <span>Sign In to your account.</span>
              </div>
            </a>
            <a href="<?= e($authPortalHref) ?>" target="_blank" rel="noopener noreferrer" class="nav-dropdown-link" role="menuitem">
              <span class="dropdown-icon-box"><i data-lucide="shield-check"></i></span>
              <div>
                <strong>Auth Service Portal</strong>
                <span>E-Check employment &amp; credential screening</span>
              </div>
            </a>
            <div style="border-top:1px solid var(--divider);margin-top:8px;padding-top:8px;display:flex;gap:8px;">
              <a href="<?= e(xds_url('products/credit-information/')) ?>#request-report" class="btn btn-glass btn-sm" style="flex:1;">
                <span>My Report</span>
              </a>
              <a href="<?= e(xds_url('contact/')) ?>" class="btn btn-lime btn-sm" style="flex:1;">
                <span>Subscribe</span>
              </a>
            </div>
          </div>
        </div>

        <button
          type="button"
          class="hamburger-btn"
          data-drawer-open
          aria-label="Open navigation menu"
          aria-controls="mobile-drawer"
          aria-expanded="false"
        >
          <i data-lucide="menu" stroke-width="2.4" aria-hidden="true"></i>
        </button>
      </div>
    </div>
  </header>

  <div id="mobile-drawer-backdrop" class="mobile-drawer-backdrop" aria-hidden="true"></div>
  <aside id="mobile-drawer" class="mobile-drawer" aria-label="Mobile Navigation" aria-hidden="true">
    <div class="mobile-drawer-top">
      <a href="<?= e(xds_url('')) ?>" class="brand-link" aria-label="XDS Zimbabwe Home">
        <img src="<?= e(xds_system_asset($system['logo_file'])) ?>" alt="XDS Zimbabwe" style="height:36px;width:auto;">
      </a>
      <div style="display:flex;align-items:center;gap:10px;">
        <button
          type="button"
          class="nav-bare-icon-btn theme-toggle-btn"
          data-theme-toggle
          aria-label="Toggle light and dark mode"
          aria-pressed="false"
          title="Switch Light / Dark Mode"
        >
          <i data-lucide="sun" stroke-width="2.5" class="icon-sun" aria-hidden="true"></i>
          <i data-lucide="moon" stroke-width="2.5" class="icon-moon" aria-hidden="true"></i>
        </button>
        <button type="button" class="nav-bare-icon-btn" data-drawer-close aria-label="Close navigation menu">
          <i data-lucide="x" stroke-width="2.5" aria-hidden="true"></i>
        </button>
      </div>
    </div>

    <ul class="mobile-nav-list">
      <li>
        <a href="<?= e(xds_url('')) ?>" class="mobile-nav-link<?= $activeNav === 'home' ? ' is-active' : '' ?>">
          <span class="mobile-nav-label"><i data-lucide="home"></i><span>Home</span></span>
        </a>
      </li>

      <li>
        <a href="<?= e(xds_url('about/')) ?>" class="mobile-nav-link<?= $activeNav === 'about' ? ' is-active' : '' ?>">
          <span class="mobile-nav-label"><i data-lucide="sliders-horizontal"></i><span>About</span></span>
        </a>
      </li>

      <li class="mobile-accordion<?= $activeNav === 'products' ? ' is-open' : '' ?>">
        <button type="button" class="mobile-accordion-trigger" data-mobile-accordion-trigger aria-expanded="<?= $activeNav === 'products' ? 'true' : 'false' ?>">
          <span class="mobile-nav-label"><i data-lucide="briefcase-business"></i><span>Products</span></span>
          <i data-lucide="chevron-down" class="accordion-chevron" aria-hidden="true"></i>
        </button>
        <div class="mobile-accordion-panel">
          <a href="<?= e(xds_url('products/credit-information/')) ?>" class="mobile-sublink">
            <i data-lucide="file-spreadsheet" style="width:16px;height:16px;color:var(--xds-lime);"></i>
            <span>Credit Information Services</span>
          </a>
          <a href="<?= e(xds_url('products/agricultural-services/')) ?>" class="mobile-sublink">
            <i data-lucide="tractor" style="width:16px;height:16px;color:var(--xds-lime);"></i>
            <span>Agricultural Services</span>
          </a>
          <a href="<?= e(xds_url('products/authentication-services/')) ?>" class="mobile-sublink">
            <i data-lucide="shield-check" style="width:16px;height:16px;color:var(--xds-lime);"></i>
            <span>Authentication Services (E Check)</span>
          </a>
        </div>
      </li>

      <li class="mobile-accordion<?= $activeNav === 'resources' ? ' is-open' : '' ?>">
        <button type="button" class="mobile-accordion-trigger" data-mobile-accordion-trigger aria-expanded="<?= $activeNav === 'resources' ? 'true' : 'false' ?>">
          <span class="mobile-nav-label"><i data-lucide="database"></i><span>Resources</span></span>
          <i data-lucide="chevron-down" class="accordion-chevron" aria-hidden="true"></i>
        </button>
        <div class="mobile-accordion-panel">
          <a href="<?= e(xds_url('resources/articles/')) ?>" class="mobile-sublink">
            <i data-lucide="newspaper" style="width:16px;height:16px;color:var(--xds-lime);"></i>
            <span>Articles &amp; Insights</span>
          </a>
          <a href="<?= e(xds_url('resources/data-privacy/')) ?>" class="mobile-sublink">
            <i data-lucide="lock-keyhole" style="width:16px;height:16px;color:var(--xds-lime);"></i>
            <span>Data Privacy (ISSP)</span>
          </a>
          <a href="<?= e(xds_url('resources/faq/')) ?>" class="mobile-sublink">
            <i data-lucide="help-circle" style="width:16px;height:16px;color:var(--xds-lime);"></i>
            <span>Frequently Asked Questions</span>
          </a>
        </div>
      </li>

      <li>
        <a href="<?= e(xds_url('contact/')) ?>" class="mobile-nav-link<?= $activeNav === 'contact' ? ' is-active' : '' ?>">
          <span class="mobile-nav-label"><i data-lucide="mail"></i><span>Contact</span></span>
        </a>
      </li>
    </ul>

    <div class="mobile-drawer-footer">
      <div class="mobile-accordion mobile-portal-dropdown">
        <button type="button" class="btn btn-lime mobile-portal-trigger" data-mobile-accordion-trigger aria-expanded="false">
          <span style="display:inline-flex;align-items:center;gap:10px;">
            <i data-lucide="user-round" stroke-width="2.4"></i>
            <span>Portal Login</span>
          </span>
          <i data-lucide="chevron-down" class="accordion-chevron" aria-hidden="true"></i>
        </button>
        <div class="mobile-accordion-panel mobile-portal-panel">
          <?php if (xds_is_admin()): ?>
          <a href="<?= e(xds_url('cms/')) ?>" class="mobile-portal-option">
            <i data-lucide="layout-dashboard" style="width:17px;height:17px;color:var(--xds-lime);"></i>
            <div>
              <strong>CMS Dashboard</strong>
              <span>Manage website content</span>
            </div>
            <i data-lucide="arrow-right" style="width:14px;height:14px;margin-left:auto;opacity:0.7;"></i>
          </a>
          <?php endif; ?>
          <a href="<?= e($creditPortalHref) ?>"<?= $creditExternal ? ' target="_blank" rel="noopener noreferrer"' : '' ?> class="mobile-portal-option">
            <i data-lucide="file-spreadsheet" style="width:17px;height:17px;color:var(--xds-lime);"></i>
            <div>
              <strong>Credit Service Portal</strong>
              <span>Unified subscriber &amp; CMS login</span>
            </div>
            <i data-lucide="arrow-right" style="width:14px;height:14px;margin-left:auto;opacity:0.7;"></i>
          </a>
          <a href="<?= e($authPortalHref) ?>" target="_blank" rel="noopener noreferrer" class="mobile-portal-option">
            <i data-lucide="shield-check" style="width:17px;height:17px;color:var(--xds-lime);"></i>
            <div>
              <strong>Authentication Service Portal</strong>
              <span>E Check background screening</span>
            </div>
            <i data-lucide="external-link" style="width:14px;height:14px;margin-left:auto;opacity:0.7;"></i>
          </a>
        </div>
      </div>
    </div>
  </aside>

  <main id="main-content" tabindex="-1">
