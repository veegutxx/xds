<?php

declare(strict_types=1);

require_once dirname(__DIR__) . '/inc/config/index.php';
require_once dirname(__DIR__) . '/inc/seo/index.php';

$system       = xds_get_system();
$about        = xds_fetch_single_table('page_about', 'page_about');
$paragraphs   = xds_decode_json($about['history_paragraphs_json'] ?? null);
$stats        = xds_decode_json($about['footprint_stats_json'] ?? null);
$valueDrivers = xds_decode_json($about['value_drivers_json'] ?? null);

$activeNav = 'about';
$pageMeta  = [
    'title'          => $about['meta_title'],
    'description'    => $about['meta_description'],
    'canonical_path' => 'about/',
    'og_image_file'  => $about['og_image_file'] ?? $about['banner_image_file'],
    'schemas'        => [xds_seo_organization_schema($system)],
];

require dirname(__DIR__) . '/inc/layout/header.php';
?>

<section class="page-banner" aria-label="About XDS Zimbabwe">
  <img src="<?= e(xds_image_asset($about['banner_image_file'])) ?>" alt="XDS Zimbabwe leadership and team" class="page-banner-img">
  <div class="page-banner-overlay" aria-hidden="true"></div>
  <div class="container page-banner-content">
    <nav class="breadcrumb" aria-label="Breadcrumb">
      <a href="<?= e(xds_url('')) ?>">Home</a>
      <span>/</span>
      <span aria-current="page">About XDS</span>
    </nav>
    <h1><?= e($about['overview_title']) ?></h1>
    <p class="page-banner-lead"><?= e($about['overview_subtitle']) ?></p>
    <div style="display:flex;flex-wrap:wrap;gap:10px;margin-top:20px;">
      <a href="#overview" class="btn btn-lime btn-sm">Company Overview</a>
      <a href="#why-xds" class="btn btn-outline-light btn-sm">Why XDS Zimbabwe</a>
    </div>
  </div>
</section>

<section id="overview" class="section">
  <div class="container">
    <div class="cards-grid-4" style="margin-bottom:44px;">
      <?php foreach ($stats as $stat): ?>
      <div class="glass-card glass-card-hover xds-scroll-card" style="padding:24px;text-align:center;">
        <div class="service-icon-orb" style="width:54px;height:54px;border-radius:14px;margin:0 auto 14px;">
          <i data-lucide="<?= e($stat['icon'] ?? 'check-circle') ?>" style="width:26px;height:26px;"></i>
        </div>
        <div style="font-family:'Plus Jakarta Sans',sans-serif;font-size:1.75rem;font-weight:800;color:var(--heading-color);line-height:1.1;margin-bottom:6px;">
          <?= e($stat['metric'] ?? '') ?>
        </div>
        <div style="font-size:0.88rem;color:var(--text-secondary);font-weight:600;">
          <?= e($stat['label'] ?? '') ?>
        </div>
      </div>
      <?php endforeach; ?>
    </div>

    <div class="glass-card xds-scroll-card" style="padding:38px 36px;">
      <div class="section-header" style="margin-bottom:24px;">
        <p class="section-eyebrow">Who We Are</p>
        <h2 class="section-title">Zimbabwe Bureau Heritage &amp; Data Stewardship</h2>
        <div class="section-accent-rule"></div>
      </div>
      <div class="cards-grid-2" style="gap:24px;">
        <?php foreach ($paragraphs as $idx => $para): ?>
        <div class="glass-card" style="padding:22px 24px;background:var(--bg-subtle);">
          <div style="display:flex;align-items:center;gap:10px;margin-bottom:10px;">
            <span class="category-badge">0<?= $idx + 1 ?></span>
            <strong style="color:var(--heading-color);font-size:0.92rem;">
              <?= e(['Zimbabwe Bureau Foundation', 'E Check Background Screening', 'Built for Local Lenders', 'Information Security Custodian'][$idx] ?? 'XDS Milestone') ?>
            </strong>
          </div>
          <p style="margin-bottom:0;font-size:0.95rem;"><?= e((string)$para) ?></p>
        </div>
        <?php endforeach; ?>
      </div>
    </div>
  </div>
</section>

<section id="why-xds" class="section section-tight">
  <div class="container">
    <div class="section-header centered">
      <p class="section-eyebrow">Why Choose Us</p>
      <h2 class="section-title"><?= e($about['why_title']) ?></h2>
      <div class="section-accent-rule"></div>
      <p class="section-subtitle">Guided by strict privacy ethics, experienced local talent, and reliable bureau innovation.</p>
    </div>

    <div class="cards-grid-2" style="margin-bottom:48px;">
      <article class="glass-card glass-card-hover xds-scroll-card offering-card">
        <div style="display:flex;align-items:center;gap:14px;margin-bottom:14px;">
          <span class="faq-icon-badge" style="width:48px;height:48px;border-radius:14px;"><i data-lucide="compass"></i></span>
          <h3 style="margin-bottom:0;"><?= e($about['philosophy_title']) ?></h3>
        </div>
        <p style="margin-bottom:0;font-size:0.98rem;"><?= e($about['philosophy_body']) ?></p>
      </article>

      <article class="glass-card glass-card-hover xds-scroll-card offering-card">
        <div style="display:flex;align-items:center;gap:14px;margin-bottom:14px;">
          <span class="faq-icon-badge" style="width:48px;height:48px;border-radius:14px;"><i data-lucide="users"></i></span>
          <h3 style="margin-bottom:0;"><?= e($about['people_title']) ?></h3>
        </div>
        <p style="margin-bottom:0;font-size:0.98rem;"><?= e($about['people_body']) ?></p>
      </article>
    </div>

    <div class="section-header centered" style="margin-bottom:32px;">
      <h3 class="section-title" style="font-size:clamp(1.45rem, 2.5vw, 1.95rem);"><?= e($about['value_drivers_title']) ?></h3>
      <div class="section-accent-rule"></div>
      <p class="section-subtitle">Hover or tap each value driver for more info.</p>
    </div>

    <div class="cards-grid-4">
      <?php foreach ($valueDrivers as $driver): ?>
      <div class="xds-flip-card compact xds-scroll-card" tabindex="0" role="region" aria-label="<?= e($driver['title'] ?? '') ?>">
        <div class="xds-flip-inner">
          <div class="xds-flip-face xds-flip-front">
            <div class="service-icon-orb" style="width:62px;height:62px;margin-bottom:16px;">
              <i data-lucide="<?= e($driver['icon'] ?? 'sparkles') ?>" style="width:28px;height:28px;"></i>
            </div>
            <h4 style="font-size:1.12rem;margin-bottom:10px;"><?= e($driver['title'] ?? '') ?></h4>
            <p style="font-size:0.9rem;margin-bottom:12px;"><?= e($driver['front_copy'] ?? '') ?></p>
            <span class="flip-hint-pill"><i data-lucide="rotate-cw" style="width:12px;height:12px;"></i> Hover for more info</span>
          </div>

          <div class="xds-flip-face xds-flip-back">
            <div>
              <span style="font-size:0.74rem;font-weight:700;text-transform:uppercase;letter-spacing:0.08em;color:var(--xds-lime);">Key Value Driver</span>
              <h4 style="font-size:1.12rem;margin:8px 0 12px;"><?= e($driver['title'] ?? '') ?></h4>
              <p style="font-size:0.9rem;margin-bottom:0;"><?= e($driver['back_copy'] ?? '') ?></p>
            </div>
            <a href="<?= e(xds_url('contact/')) ?>" class="btn btn-lime btn-sm" style="width:100%;">
              <span>Partner With Us</span>
              <i data-lucide="arrow-right"></i>
            </a>
          </div>
        </div>
      </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<section class="section">
  <div class="container">
    <div class="cta-glass-banner xds-scroll-card">
      <div>
        <h2>Committed to Data Security &amp; Regulatory Excellence</h2>
        <p>Explore our Information Security Policy (ISSP) or speak directly with our Harare bureau specialists.</p>
      </div>
      <div style="display:flex;flex-wrap:wrap;gap:12px;">
        <a href="<?= e(xds_url('resources/data-privacy/')) ?>" class="btn btn-lime">
          <i data-lucide="shield-check"></i>
          <span>View Data Privacy (ISSP)</span>
        </a>
        <a href="<?= e(xds_url('contact/')) ?>" class="btn btn-outline-light">
          <span>Contact Our Team</span>
        </a>
      </div>
    </div>
  </div>
</section>

<?php require dirname(__DIR__) . '/inc/layout/footer.php'; ?>
