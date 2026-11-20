<?php

declare(strict_types=1);

$system        = $system ?? xds_get_system();
$social        = $system['social_links'] ?? [];
$regional      = $system['regional_links'] ?? [];
$loadThree     = $loadThree ?? false;
$loadFlatpickr = $loadFlatpickr ?? false;
?>
  </main>

  <footer class="site-footer" role="contentinfo">
    <div class="container">
      <div class="footer-grid">
        <div class="footer-brand-col">
          <a href="<?= e(xds_url('')) ?>" class="footer-logo-link" aria-label="XDS Zimbabwe Home">
            <img src="<?= e(xds_system_asset($system['logo_file'])) ?>" alt="XDS Zimbabwe" class="footer-logo-img">
          </a>
          <p class="footer-about-copy">
            Expert Decision Systems Zimbabwe (XDS) delivers consumer and corporate credit intelligence, agricultural risk analytics, and E Check authentication across Zimbabwe.
          </p>
          <div class="social-links-row" aria-label="Social media links">
            <?php if (!empty($social['linkedin'])): ?>
            <a href="<?= e($social['linkedin']) ?>" target="_blank" rel="noopener noreferrer" class="social-btn" aria-label="XDS Zimbabwe on LinkedIn" title="LinkedIn">
              <svg viewBox="0 0 24 24" width="18" height="18" fill="currentColor" aria-hidden="true">
                <path d="M19 3A2 2 0 0 1 21 5V19A2 2 0 0 1 19 21H5A2 2 0 0 1 3 19V5A2 2 0 0 1 5 3H19M18.5 18.5V13.2A3.26 3.26 0 0 0 15.24 9.94C14.39 9.94 13.4 10.46 12.92 11.24V10.13H10.13V18.5H12.92V13.57C12.92 12.8 13.54 12.17 14.31 12.17A1.4 1.4 0 0 1 15.71 13.57V18.5H18.5M6.88 8.56A1.68 1.68 0 0 0 8.56 6.88C8.56 5.95 7.81 5.19 6.88 5.19A1.69 1.69 0 0 0 5.19 6.88C5.19 7.81 5.95 8.56 6.88 8.56M8.27 18.5V10.13H5.5V18.5H8.27Z"/>
              </svg>
            </a>
            <?php endif; ?>
            <?php if (!empty($social['twitter'])): ?>
            <a href="<?= e($social['twitter']) ?>" target="_blank" rel="noopener noreferrer" class="social-btn" aria-label="XDS Zimbabwe on X (Twitter)" title="X / Twitter">
              <svg viewBox="0 0 24 24" width="17" height="17" fill="currentColor" aria-hidden="true">
                <path d="M18.244 2.25h3.308l-7.227 8.26 8.502 11.24H16.17l-5.214-6.817L4.99 21.75H1.68l7.73-8.835L1.254 2.25H8.08l4.713 6.231zm-1.161 17.52h1.833L7.084 4.126H5.117z"/>
              </svg>
            </a>
            <?php endif; ?>
            <?php if (!empty($social['facebook'])): ?>
            <a href="<?= e($social['facebook']) ?>" target="_blank" rel="noopener noreferrer" class="social-btn" aria-label="XDS Zimbabwe on Facebook" title="Facebook">
              <svg viewBox="0 0 24 24" width="18" height="18" fill="currentColor" aria-hidden="true">
                <path d="M14 13.5h2.5l1-4H14v-2c0-1.03 0-2 2-2h1.5V2.14c-.326-.043-1.557-.14-2.857-.14C11.928 2 10 3.657 10 6.7v2.8H7v4h3V22h4v-8.5z"/>
              </svg>
            </a>
            <?php endif; ?>
          </div>
        </div>

        <div class="footer-col">
          <h3 class="footer-heading">Harare Office</h3>
          <div class="footer-contact-list">
            <div class="footer-contact-item">
              <i data-lucide="map-pin" aria-hidden="true"></i>
              <span><?= e($system['address_line']) ?></span>
            </div>
            <div class="footer-contact-item">
              <i data-lucide="phone" aria-hidden="true"></i>
              <span>
                <a href="tel:+263242779344"><?= e($system['primary_phone']) ?></a><br>
                <a href="tel:+263242779341"><?= e($system['secondary_phone']) ?></a><?php if (!empty($system['tertiary_phone'])): ?><br><a href="tel:+263242779392"><?= e($system['tertiary_phone']) ?></a><?php endif; ?>
              </span>
            </div>
            <div class="footer-contact-item">
              <i data-lucide="mail" aria-hidden="true"></i>
              <a href="mailto:<?= e($system['primary_email']) ?>"><?= e($system['primary_email']) ?></a>
            </div>
            <div class="footer-contact-item">
              <i data-lucide="clock" aria-hidden="true"></i>
              <span>
                <?= e($system['operating_hours_weekday']) ?><br>
                <?= e($system['operating_hours_weekend']) ?>
              </span>
            </div>
          </div>
        </div>

        <div class="footer-col">
          <h3 class="footer-heading">Explore XDS</h3>
          <ul class="footer-list">
            <li><a href="<?= e(xds_url('')) ?>"><i data-lucide="chevron-right" class="footer-bullet"></i>Home</a></li>
            <li><a href="<?= e(xds_url('about/')) ?>"><i data-lucide="chevron-right" class="footer-bullet"></i>About &amp; Why XDS</a></li>
            <li><a href="<?= e(xds_url('products/credit-information/')) ?>"><i data-lucide="chevron-right" class="footer-bullet"></i>Credit Information Services</a></li>
            <li><a href="<?= e(xds_url('products/agricultural-services/')) ?>"><i data-lucide="chevron-right" class="footer-bullet"></i>Agricultural Services</a></li>
            <li><a href="<?= e(xds_url('products/authentication-services/')) ?>"><i data-lucide="chevron-right" class="footer-bullet"></i>Authentication Services</a></li>
            <li><a href="<?= e(xds_url('resources/articles/')) ?>"><i data-lucide="chevron-right" class="footer-bullet"></i>Articles &amp; Insights</a></li>
            <li><a href="<?= e(xds_url('resources/faq/')) ?>"><i data-lucide="chevron-right" class="footer-bullet"></i>FAQ</a></li>
            <li><a href="<?= e(xds_url('contact/')) ?>"><i data-lucide="chevron-right" class="footer-bullet"></i>Contact Us</a></li>
          </ul>
        </div>

        <div class="footer-col">
          <h3 class="footer-heading">Client Portals</h3>
          <?php
            $footerCreditHref = xds_portal_href((string)$system['credit_portal_url']);
            $footerCreditExt  = xds_is_external_url((string)$system['credit_portal_url']);
            $footerAuthHref   = xds_portal_href((string)$system['auth_portal_url']);
          ?>
          <ul class="footer-list" style="margin-bottom:24px;">
            <li><a href="<?= e($footerCreditHref) ?>"<?= $footerCreditExt ? ' target="_blank" rel="noopener noreferrer"' : '' ?>><i data-lucide="log-in" class="footer-bullet"></i>Credit Service Portal</a></li>
            <li><a href="<?= e($footerAuthHref) ?>" target="_blank" rel="noopener noreferrer"><i data-lucide="external-link" class="footer-bullet"></i>Authentication Service Portal</a></li>
          </ul>

          <h3 class="footer-heading">Sister Sites</h3>
          <ul class="footer-list">
            <?php foreach ($regional as $reg): ?>
            <li>
              <a href="<?= e($reg['url'] ?? '#') ?>" target="_blank" rel="noopener noreferrer">
                <i data-lucide="globe-2" class="footer-bullet"></i>
                <span><?= e($reg['label'] ?? '') ?></span>
              </a>
            </li>
            <?php endforeach; ?>
          </ul>
        </div>
      </div>

      <div class="footer-bottom">
        <div class="footer-bottom-inner">
          <span><?= e($system['copyright_text']) ?></span>
          <div class="footer-legal-links">
            <a href="<?= e(xds_url('resources/data-privacy/')) ?>">Data Privacy (ISSP)</a>
            <a href="<?= e(xds_url('sitemap.xml')) ?>">Sitemap</a>
            <a href="<?= e(xds_url('contact/')) ?>">Dispute &amp; Support</a>
          </div>
        </div>
      </div>
    </div>
  </footer>

  <script src="https://unpkg.com/lucide@latest/dist/umd/lucide.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/gsap@3.12.5/dist/gsap.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/gsap@3.12.5/dist/ScrollTrigger.min.js"></script>

  <?php if ($loadFlatpickr): ?>
  <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
  <?php endif; ?>

  <?php if ($loadThree): ?>
  <script src="https://cdn.jsdelivr.net/npm/three@0.160.0/build/three.min.js"></script>
  <script src="<?= e(ASSETS_JS_URL . 'hero-three.js') ?>"></script>
  <?php endif; ?>

  <script src="<?= e(ASSETS_JS_URL . 'main.js') ?>"></script>
</body>
</html>
