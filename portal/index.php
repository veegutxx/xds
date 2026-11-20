<?php

declare(strict_types=1);

require_once dirname(__DIR__) . '/inc/config/index.php';

$existing = xds_current_user();
if ($existing !== null) {
    header('Location: ' . xds_redirect_for_role((string)$existing['role']), true, 302);
    exit;
}

$system    = xds_get_system();
$home      = xds_fetch_single_table('page_home', 'page_home');
$heroImg   = $home['hero_image_file'] ?? 'hero-home.webp';
$csrf      = xds_csrf_token();
$echeckUrl = 'https://www.xds.co.zw/echeck/';
$dbLive    = xds_db_connected();
?>
<!DOCTYPE html>
<html lang="en" data-theme="light">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="robots" content="noindex, nofollow">
  <title>Portal Sign In | XDS Zimbabwe</title>
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
</head>
<body class="portal-auth-body portal-hero-bg-page">
  <div class="portal-canvas-stage" aria-hidden="true">
    <div class="portal-bg-photo" style="background-image: url('<?= e(xds_image_asset($heroImg)) ?>');"></div>
    <div class="portal-bg-overlay"></div>
    <canvas id="xds-hero-canvas"></canvas>
  </div>

  <div class="portal-auth-shell">
    <header class="portal-auth-topbar">
      <a href="<?= e(xds_url('')) ?>" class="portal-brand-link" aria-label="Return to XDS Zimbabwe Home">
        <img src="<?= e(xds_system_asset($system['logo_file'])) ?>" alt="XDS Zimbabwe" style="height:42px;width:auto;">
      </a>
      <div class="portal-auth-top-actions">
        <a href="<?= e(xds_url('')) ?>" class="portal-top-link">
          <i data-lucide="arrow-left"></i>
          <span>Public Website</span>
        </a>
        <button type="button" class="cms-bare-icon-btn portal-top-icon-btn" data-cms-theme-toggle aria-label="Toggle light and dark mode" title="Toggle theme">
          <i data-lucide="sun" stroke-width="2.4" class="icon-sun"></i>
          <i data-lucide="moon" stroke-width="2.4" class="icon-moon"></i>
        </button>
      </div>
    </header>

    <main class="portal-auth-main">
      <section class="portal-login-card portal-login-card-single" aria-label="Unified Sign In Form">

        <div style="margin-bottom:24px;">
          <h1 style="font-size:1.65rem;margin:0 0 6px;">Sign In to Your Account</h1>
          <p style="margin:0;font-size:0.9rem;color:var(--cms-muted);">Access the XDS Credit Service Portal or Website CMS.</p>
        </div>

        <div id="portal-login-alert" class="cms-alert" role="status" aria-live="polite"></div>

        <form id="portal-login-form" novalidate>
          <input type="hidden" name="csrf_token" value="<?= e($csrf) ?>">

          <div class="cms-field">
            <label for="login-email">Email Address</label>
            <div class="cms-input-icon-wrap">
              <i data-lucide="mail" aria-hidden="true"></i>
              <input id="login-email" name="email" type="email" class="cms-input" required autocomplete="username" placeholder="name@xds.co.zw">
            </div>
          </div>

          <div class="cms-field">
            <label for="login-password">Password</label>
            <div class="cms-input-icon-wrap">
              <i data-lucide="lock" aria-hidden="true"></i>
              <input id="login-password" name="password" type="password" class="cms-input" required autocomplete="current-password" placeholder="Enter your password">
              <button type="button" class="cms-pw-toggle" id="toggle-pw-btn" aria-label="Show password">
                <i data-lucide="eye"></i>
              </button>
            </div>
          </div>

          <button type="submit" id="portal-login-submit" class="cms-btn cms-btn-lime" style="width:100%;margin-top:6px;">
            <i data-lucide="log-in"></i>
            <span>Sign In</span>
          </button>
        </form>

        <div class="portal-echeck-inline">
          <span class="portal-echeck-label">Need background verification?</span>
          <a href="<?= e($echeckUrl) ?>" target="_blank" rel="noopener noreferrer" class="cms-btn cms-btn-indigo" style="width:100%;">
            <i data-lucide="shield-check"></i>
            <span>Authentication Service Portal (E Check)</span>
            <i data-lucide="external-link"></i>
          </a>
        </div>

        <div class="portal-helper-footer">
          <p style="margin:10px 0 0;font-size:0.81rem;color:var(--cms-muted);">
            Need help? Contact <a href="mailto:<?= e($system['primary_email']) ?>" style="color:var(--xds-lime-deep);font-weight:600;"><?= e($system['primary_email']) ?></a> or <?= e($system['primary_phone']) ?>.
          </p>
        </div>
      </section>
    </main>
  </div>

  <script src="https://unpkg.com/lucide@latest/dist/umd/lucide.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/three@0.160.0/build/three.min.js"></script>
  <script src="<?= e(ASSETS_JS_URL . 'hero-three.js') ?>"></script>
  <script>
    (function () {
      if (window.lucide) window.lucide.createIcons();

      document.querySelectorAll('[data-cms-theme-toggle]').forEach(function (btn) {
        btn.addEventListener('click', function () {
          var next = document.documentElement.getAttribute('data-theme') === 'dark' ? 'light' : 'dark';
          document.documentElement.setAttribute('data-theme', next);
          try { localStorage.setItem('xds-theme', next); } catch (e) {}
        });
      });

      var pwInput = document.getElementById('login-password');
      var pwBtn = document.getElementById('toggle-pw-btn');
      if (pwInput && pwBtn) {
        pwBtn.addEventListener('click', function () {
          var show = pwInput.type === 'password';
          pwInput.type = show ? 'text' : 'password';
          pwBtn.innerHTML = show ? '<i data-lucide="eye-off"></i>' : '<i data-lucide="eye"></i>';
          if (window.lucide) window.lucide.createIcons();
        });
      }

      var form = document.getElementById('portal-login-form');
      var alertBox = document.getElementById('portal-login-alert');
      var submitBtn = document.getElementById('portal-login-submit');
      var authEndpoint = <?= json_encode(xds_url('inc/auth/index.php'), JSON_UNESCAPED_SLASHES) ?>;

      form.addEventListener('submit', async function (e) {
        e.preventDefault();
        alertBox.className = 'cms-alert';
        alertBox.textContent = '';

        var payload = {};
        new FormData(form).forEach(function (v, k) { payload[k] = v; });

        submitBtn.disabled = true;
        submitBtn.innerHTML = '<span>Signing in...</span>';

        try {
          var res = await fetch(authEndpoint, {
            method: 'POST',
            headers: {
              'Content-Type': 'application/json',
              'X-CSRF-Token': payload.csrf_token || '',
              'Accept': 'application/json'
            },
            body: JSON.stringify(payload)
          });
          var data = await res.json();
          if (!res.ok || !data.ok) {
            alertBox.className = 'cms-alert is-visible is-error';
            alertBox.textContent = data.message || 'Sign in failed. Please check your credentials.';
            submitBtn.disabled = false;
            submitBtn.innerHTML = '<i data-lucide="log-in"></i><span>Sign In</span>';
            if (window.lucide) window.lucide.createIcons();
            return;
          }

          alertBox.className = 'cms-alert is-visible is-success';
          alertBox.textContent = data.message || 'Signed in! Redirecting...';
          window.location.href = data.redirect_url;
        } catch (err) {
          alertBox.className = 'cms-alert is-visible is-error';
          alertBox.textContent = 'Network error while signing in. Please try again.';
          submitBtn.disabled = false;
          submitBtn.innerHTML = '<i data-lucide="log-in"></i><span>Sign In</span>';
          if (window.lucide) window.lucide.createIcons();
        }
      });
    })();
  </script>
</body>
</html>
