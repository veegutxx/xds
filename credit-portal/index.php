<?php

declare(strict_types=1);

require_once dirname(__DIR__) . '/inc/config/index.php';

$user      = xds_require_login();
$system    = xds_get_system();
$csrf      = xds_csrf_token();
$echeckUrl = 'https://www.xds.co.zw/echeck/';
$creditSvc = xds_fetch_single_table('page_credit_services', 'page_credit_services');
$offerings = xds_decode_json($creditSvc['offerings_json'] ?? null);

$initials = '';
foreach (preg_split('/\s+/', trim((string)$user['full_name'])) ?: [] as $part) {
    if ($part !== '') {
        $initials .= strtoupper(substr($part, 0, 1));
    }
    if (strlen($initials) >= 2) break;
}
$initials = $initials ?: 'XS';
?>
<!DOCTYPE html>
<html lang="en" data-theme="light">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="robots" content="noindex, nofollow">
  <title>Credit Service Portal | XDS Zimbabwe</title>
  <link rel="icon" type="image/png" href="<?= e(xds_system_asset($system['favicon_file'])) ?>">
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
</head>
<body class="cms-body">
  <div class="cms-layout">
    <aside class="cms-sidebar" id="cms-sidebar" aria-label="Credit Portal Navigation">
      <div class="cms-sidebar-header">
        <a href="<?= e(xds_url('credit-portal/')) ?>" class="portal-brand-link">
          <img src="<?= e(xds_system_asset($system['logo_file'])) ?>" alt="XDS Zimbabwe" style="height:36px;width:auto;">
        </a>
        <button type="button" class="cms-bare-icon-btn cms-mobile-only" data-sidebar-close aria-label="Close sidebar">
          <i data-lucide="x" stroke-width="2.4"></i>
        </button>
      </div>

      <div class="cms-sidebar-scroll">
        <div class="cms-nav-group">
          <div class="cms-nav-label">Credit Bureau</div>
          <a href="#bureau-overview" class="cms-nav-item is-active"><i data-lucide="layout-grid"></i><span>Dashboard</span></a>
          <a href="#enquiry-request" class="cms-nav-item"><i data-lucide="file-search"></i><span>Submit Enquiry</span></a>
          <a href="<?= e(xds_url('products/credit-information/')) ?>" target="_blank" class="cms-nav-item"><i data-lucide="file-spreadsheet"></i><span>Product Guide</span></a>
          <a href="<?= e($echeckUrl) ?>" target="_blank" rel="noopener noreferrer" class="cms-nav-item"><i data-lucide="shield-check"></i><span>E Check Portal</span><i data-lucide="external-link" style="width:13px;height:13px;margin-left:auto;opacity:0.6;"></i></a>
        </div>

        <div class="cms-nav-group">
          <div class="cms-nav-label">Workspace</div>
          <?php if (xds_is_admin()): ?>
          <a href="<?= e(xds_url('cms/')) ?>" class="cms-nav-item"><i data-lucide="sliders-horizontal"></i><span>Switch to CMS Portal</span></a>
          <?php endif; ?>
          <a href="<?= e(xds_url('')) ?>" class="cms-nav-item"><i data-lucide="globe"></i><span>Public Website</span></a>
          <a href="<?= e(xds_url('inc/auth/index.php?action=logout')) ?>" class="cms-nav-item"><i data-lucide="log-out"></i><span>Sign Out</span></a>
        </div>
      </div>

      <div class="cms-sidebar-footer">
        <div class="cms-user-chip">
          <span class="cms-user-avatar"><?= e($initials) ?></span>
          <div class="cms-user-meta">
            <strong><?= e($user['full_name']) ?></strong>
            <span><?= e($user['organization'] ?: 'Credit Subscriber') ?></span>
          </div>
        </div>
      </div>
    </aside>

    <div class="cms-main-wrap">
      <header class="cms-topbar">
        <div class="cms-topbar-left">
          <button type="button" class="cms-bare-icon-btn cms-mobile-only" data-sidebar-open aria-label="Open menu">
            <i data-lucide="menu" stroke-width="2.4"></i>
          </button>
          <nav class="cms-breadcrumb" aria-label="Breadcrumb">
            <a href="<?= e(xds_url('')) ?>">XDS Zimbabwe</a>
            <span>/</span>
            <span aria-current="page">Credit Service Portal</span>
          </nav>
        </div>
        <div class="cms-topbar-right">
          <a href="<?= e($echeckUrl) ?>" target="_blank" rel="noopener noreferrer" class="cms-btn cms-btn-lime cms-btn-sm">
            <span>E Check Portal</span>
            <i data-lucide="external-link"></i>
          </a>
          <button type="button" class="cms-bare-icon-btn" data-cms-theme-toggle aria-label="Toggle theme">
            <i data-lucide="sun" stroke-width="2.4" class="icon-sun"></i>
            <i data-lucide="moon" stroke-width="2.4" class="icon-moon"></i>
          </button>
          <a href="<?= e(xds_url('inc/auth/index.php?action=logout')) ?>" class="cms-btn cms-btn-ghost cms-btn-sm">
            <i data-lucide="log-out"></i>
            <span>Sign Out</span>
          </a>
        </div>
      </header>

      <main class="cms-content" id="bureau-overview">
        <div class="cms-page-head">
          <div>
            <h1>Welcome back, <?= e(explode(' ', trim((string)$user['full_name']))[0] ?: 'Subscriber') ?>!</h1>
            <p>Access XDS Zimbabwe Credit Bureau enquiries, commercial due diligence, and tracing desks.</p>
          </div>
          <span class="cms-date-pill"><i data-lucide="calendar"></i><?= e(date('d F, Y')) ?></span>
        </div>

        <div class="cms-kpi-grid">
          <div class="cms-kpi-card">
            <div class="cms-kpi-top"><span class="cms-kpi-icon"><i data-lucide="database"></i></span><span>Bureau Records</span></div>
            <div class="cms-kpi-value">2M+</div>
            <div class="cms-kpi-sub">Consumer &amp; corporate profiles</div>
          </div>
          <div class="cms-kpi-card">
            <div class="cms-kpi-top"><span class="cms-kpi-icon"><i data-lucide="building-2"></i></span><span>Data Contributors</span></div>
            <div class="cms-kpi-value">125+</div>
            <div class="cms-kpi-sub">Zimbabwean credit providers</div>
          </div>
          <div class="cms-kpi-card">
            <div class="cms-kpi-top"><span class="cms-kpi-icon"><i data-lucide="shield-check"></i></span><span>Account Role</span></div>
            <div class="cms-kpi-value" style="font-size:1.25rem;"><?= e(strtoupper(str_replace('_', ' ', (string)$user['role']))) ?></div>
            <div class="cms-kpi-sub"><?= e($user['organization'] ?: $user['email']) ?></div>
          </div>
          <div class="cms-kpi-card">
            <div class="cms-kpi-top"><span class="cms-kpi-icon"><i data-lucide="clock"></i></span><span>Support Hours</span></div>
            <div class="cms-kpi-value" style="font-size:1.2rem;">08:00 – 16:30</div>
            <div class="cms-kpi-sub">Mon–Fri Harare Support Desk</div>
          </div>
        </div>

        <div class="cms-card" style="margin-bottom:24px;">
          <div class="cms-card-header">
            <div>
              <h2>Your Credit Bureau Modules</h2>
              <p>Live service modules configured by XDS Zimbabwe.</p>
            </div>
          </div>
          <div class="cms-quick-grid">
            <?php foreach ($offerings as $off): ?>
            <div class="cms-quick-card">
              <span class="cms-kpi-icon" style="margin-bottom:12px;"><i data-lucide="<?= e($off['icon'] ?? 'file-text') ?>"></i></span>
              <h3><?= e($off['title'] ?? '') ?></h3>
              <p style="margin-bottom:12px;"><?= e($off['subtitle'] ?? '') ?></p>
              <ul style="margin:0;padding-left:18px;font-size:0.84rem;color:var(--cms-muted);display:flex;flex-direction:column;gap:4px;">
                <?php foreach (($off['items'] ?? []) as $it): ?>
                <li><?= e((string)$it) ?></li>
                <?php endforeach; ?>
              </ul>
            </div>
            <?php endforeach; ?>
          </div>
        </div>

        <div class="cms-card" id="enquiry-request">
          <div class="cms-card-header">
            <div>
              <h2>Submit Bureau Desk Request</h2>
              <p>Request batch credit analytics, commercial reports, tracing support, or API credentials.</p>
            </div>
          </div>
          <div id="sub-alert" class="cms-alert" role="status"></div>
          <form id="sub-request-form">
            <input type="hidden" name="csrf_token" value="<?= e($csrf) ?>">
            <input type="hidden" name="source_page" value="credit-portal">
            <?php
              $parts = preg_split('/\s+/', trim((string)$user['full_name']), 2) ?: ['', ''];
              $fn = $parts[0] ?? 'Subscriber';
              $ln = $parts[1] ?? 'User';
            ?>
            <input type="hidden" name="firstname" value="<?= e($fn) ?>">
            <input type="hidden" name="lastname" value="<?= e($ln) ?>">
            <input type="hidden" name="email" value="<?= e($user['email']) ?>">
            <input type="hidden" name="company" value="<?= e($user['organization'] ?: '') ?>">

            <div class="cms-form-row-2">
              <div class="cms-field">
                <label for="sub-need">Service Category</label>
                <select id="sub-need" name="need_category" class="cms-input" required>
                  <option value="Credit Information Services / Bureau Subscription">Consumer Credit Profile Batch</option>
                  <option value="Commercial Reports & Collections Tracing">Commercial Report / Director KYC</option>
                  <option value="Agricultural Services & Agro Credit Scoring">Agro Credit Exposure / Alerts</option>
                  <option value="Data Dispute / Profile Query">Data Submission / Dispute Support</option>
                </select>
              </div>
              <div class="cms-field">
                <label for="sub-phone">Direct Phone</label>
                <input id="sub-phone" name="phone" type="tel" class="cms-input" placeholder="+263 242 ...">
              </div>
            </div>
            <div class="cms-field">
              <label for="sub-msg">Request Details</label>
              <textarea id="sub-msg" name="message" class="cms-input" rows="4" required placeholder="Specify account reference, report type, or support details..."></textarea>
            </div>
            <button type="submit" class="cms-btn cms-btn-lime">
              <i data-lucide="send"></i>
              <span>Submit Desk Request</span>
            </button>
          </form>
        </div>
      </main>
    </div>
  </div>

  <script src="https://unpkg.com/lucide@latest/dist/umd/lucide.min.js"></script>
  <script>
    (function () {
      if (window.lucide) window.lucide.createIcons();
      document.querySelectorAll('[data-cms-theme-toggle]').forEach(function (b) {
        b.addEventListener('click', function () {
          var next = document.documentElement.getAttribute('data-theme') === 'dark' ? 'light' : 'dark';
          document.documentElement.setAttribute('data-theme', next);
          try { localStorage.setItem('xds-theme', next); } catch (e) {}
        });
      });
      var sb = document.getElementById('cms-sidebar');
      document.querySelectorAll('[data-sidebar-open]').forEach(function (b) { b.addEventListener('click', function () { sb.classList.add('is-open'); }); });
      document.querySelectorAll('[data-sidebar-close]').forEach(function (b) { b.addEventListener('click', function () { sb.classList.remove('is-open'); }); });

      var form = document.getElementById('sub-request-form');
      var alertBox = document.getElementById('sub-alert');
      form.addEventListener('submit', async function (e) {
        e.preventDefault();
        alertBox.className = 'cms-alert';
        var payload = {};
        new FormData(form).forEach(function (v, k) { payload[k] = v; });
        try {
          var res = await fetch(<?= json_encode(xds_url('inc/contact/index.php'), JSON_UNESCAPED_SLASHES) ?>, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-Token': payload.csrf_token || '', 'Accept': 'application/json' },
            body: JSON.stringify(payload)
          });
          var data = await res.json();
          alertBox.className = 'cms-alert is-visible ' + (data.ok ? 'is-success' : 'is-error');
          alertBox.textContent = data.message || (data.ok ? 'Request submitted!' : 'Could not submit request.');
          if (data.ok) form.reset();
        } catch (err) {
          alertBox.className = 'cms-alert is-visible is-error';
          alertBox.textContent = 'Network error. Please try again.';
        }
      });
    })();
  </script>
</body>
</html>
