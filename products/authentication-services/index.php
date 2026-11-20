<?php

declare(strict_types=1);

require_once dirname(__DIR__, 2) . '/inc/config/index.php';
require_once dirname(__DIR__, 2) . '/inc/seo/index.php';

$system   = xds_get_system();
$page     = xds_fetch_single_table('page_authentication_services', 'page_authentication_services');
$checks   = xds_decode_json($page['checks_json'] ?? null);
$partners = xds_decode_json($page['partners_json'] ?? null);

$activeNav = 'products';
$activeSub = 'authentication';
$pageMeta  = [
    'title'          => $page['meta_title'],
    'description'    => $page['meta_description'],
    'canonical_path' => 'products/authentication-services/',
    'og_image_file'  => $page['og_image_file'] ?? $page['banner_image_file'],
    'schemas'        => [
        xds_seo_organization_schema($system),
        [
            '@context'    => 'https://schema.org',
            '@type'       => 'Service',
            'serviceType' => 'Employment Background Screening & Authentication',
            'name'        => 'XDS E Check Authentication Services',
            'description' => $page['overview_copy'],
            'provider'    => ['@type' => 'Organization', 'name' => $system['site_name']],
            'areaServed'  => 'Zimbabwe',
        ],
    ],
];

require dirname(__DIR__, 2) . '/inc/layout/header.php';
?>

<section class="page-banner" aria-label="Authentication Services">
  <img src="<?= e(xds_image_asset($page['banner_image_file'])) ?>" alt="XDS E Check Authentication Services" class="page-banner-img">
  <div class="page-banner-overlay" aria-hidden="true"></div>
  <div class="container page-banner-content">
    <nav class="breadcrumb" aria-label="Breadcrumb">
      <a href="<?= e(xds_url('')) ?>">Home</a>
      <span>/</span>
      <span>Products</span>
      <span>/</span>
      <span aria-current="page">Authentication Services</span>
    </nav>
    <h1><?= e($page['page_title']) ?></h1>
    <p class="page-banner-lead">Reliable employment screening and background verification for Zimbabwean employers and organisations under our E Check brand.</p>
  </div>
</section>

<section class="section">
  <div class="container">
    <div class="glass-card xds-scroll-card" style="padding:32px;margin-bottom:38px;display:flex;flex-wrap:wrap;align-items:center;justify-content:space-between;gap:24px;">
      <div style="flex:1 1 540px;">
        <div class="section-header" style="margin-bottom:14px;">
          <p class="section-eyebrow">E Check Screening Solutions</p>
          <h2 class="section-title" style="font-size:clamp(1.5rem, 2.6vw, 2rem);">Verify Before You Hire or Onboard</h2>
          <div class="section-accent-rule"></div>
        </div>
        <p style="font-size:1.02rem;margin-bottom:0;"><?= e($page['overview_copy']) ?></p>
      </div>

      <div style="display:flex;flex-direction:column;align-items:center;gap:14px;background:#fff;padding:18px 24px;border-radius:16px;border:1px solid rgba(37,45,157,0.12);">
        <img
          src="<?= e(xds_image_asset($page['echeck_badge_file'])) ?>"
          alt="E Check Employment Screening Solutions"
          style="height:46px;width:auto;"
        >
        <a href="<?= e($system['auth_portal_url']) ?>" target="_blank" rel="noopener noreferrer" class="btn btn-primary btn-sm">
          <i data-lucide="external-link"></i>
          <span>Open E Check Portal</span>
        </a>
      </div>
    </div>

    <div class="cards-grid-4" style="margin-bottom:56px;">
      <?php foreach ($checks as $check): ?>
      <article class="glass-card glass-card-hover xds-scroll-card offering-card">
        <div class="service-icon-orb" style="width:58px;height:58px;border-radius:15px;margin-bottom:18px;">
          <i data-lucide="<?= e($check['icon'] ?? 'check-circle') ?>" style="width:27px;height:27px;"></i>
        </div>
        <h3 style="font-size:1.15rem;margin-bottom:14px;"><?= e($check['title'] ?? '') ?></h3>
        <ul class="xds-check-list">
          <?php foreach (($check['items'] ?? []) as $item): ?>
          <li>
            <i data-lucide="check-circle-2"></i>
            <span><?= e((string)$item) ?></span>
          </li>
          <?php endforeach; ?>
        </ul>
      </article>
      <?php endforeach; ?>
    </div>

    <div class="section-header centered">
      <p class="section-eyebrow">Trusted Screening Partners</p>
      <h2 class="section-title"><?= e($page['partners_heading']) ?></h2>
      <div class="section-accent-rule"></div>
    </div>

    <div class="partners-grid" style="margin-bottom:50px;">
      <?php foreach ($partners as $partner): ?>
      <div class="glass-card glass-card-hover xds-scroll-card partner-tile">
        <div class="partner-logo-frame">
          <img
            src="<?= e(xds_image_asset((string)($partner['logo_file'] ?? ''))) ?>"
            alt="<?= e($partner['name'] ?? '') ?>"
            loading="lazy"
          >
        </div>
        <span class="partner-name"><?= e($partner['name'] ?? '') ?></span>
      </div>
      <?php endforeach; ?>
    </div>

    <div class="cta-glass-banner xds-scroll-card">
      <div>
        <h2>Streamline Your Candidate Vetting Pipeline</h2>
        <p>Request corporate E Check onboarding for bulk qualification, criminal record, drivers licence, and credit checks.</p>
      </div>
      <a href="<?= e(xds_url('contact/')) ?>" class="btn btn-lime">
        <i data-lucide="send"></i>
        <span>Request E Check Demo</span>
      </a>
    </div>
  </div>
</section>

<?php require dirname(__DIR__, 2) . '/inc/layout/footer.php'; ?>
