<?php

declare(strict_types=1);

require_once dirname(__DIR__, 2) . '/inc/config/index.php';
require_once dirname(__DIR__, 2) . '/inc/seo/index.php';

$system   = xds_get_system();
$page     = xds_fetch_single_table('page_agricultural_services', 'page_agricultural_services');
$features = xds_decode_json($page['features_json'] ?? null);
$pillars  = xds_decode_json($page['pillars_json'] ?? null);

$activeNav = 'products';
$activeSub = 'agricultural';
$pageMeta  = [
    'title'          => $page['meta_title'],
    'description'    => $page['meta_description'],
    'canonical_path' => 'products/agricultural-services/',
    'og_image_file'  => $page['og_image_file'] ?? $page['banner_image_file'],
    'schemas'        => [
        xds_seo_organization_schema($system),
        [
            '@context'    => 'https://schema.org',
            '@type'       => 'Service',
            'serviceType' => 'Agricultural Credit Risk & Scoring',
            'name'        => $page['page_title'],
            'description' => $page['intro_paragraph'],
            'provider'    => ['@type' => 'Organization', 'name' => $system['site_name']],
            'areaServed'  => 'Zimbabwe',
        ],
    ],
];

require dirname(__DIR__, 2) . '/inc/layout/header.php';
?>

<section class="page-banner" aria-label="Agricultural Services">
  <img src="<?= e(xds_image_asset($page['banner_image_file'])) ?>" alt="XDS Zimbabwe Agricultural Credit Services" class="page-banner-img">
  <div class="page-banner-overlay" aria-hidden="true"></div>
  <div class="container page-banner-content">
    <nav class="breadcrumb" aria-label="Breadcrumb">
      <a href="<?= e(xds_url('')) ?>">Home</a>
      <span>/</span>
      <span>Products</span>
      <span>/</span>
      <span aria-current="page">Agricultural Services</span>
    </nav>
    <h1><?= e($page['page_title']) ?></h1>
    <p class="page-banner-lead">Unlocking responsible lending and contract farming for smallholder, communal, and commercial agriculture across Zimbabwe.</p>
  </div>
</section>

<section class="section">
  <div class="container">
    <div class="agri-split" style="margin-bottom:56px;">
      <div class="glass-card xds-scroll-card agri-diagram-card">
        <img
          src="<?= e(xds_image_asset($page['diagram_image_file'])) ?>"
          alt="XDS Agriculture Services Ecosystem Diagram: Credit History, Credit Data, Credit Scores, Credit Analysis, Type of Produce, Productivity Levels"
          loading="lazy"
        >
      </div>

      <div class="xds-scroll-card">
        <div class="section-header" style="margin-bottom:20px;">
          <p class="section-eyebrow">XDS Agro Business Unit</p>
          <h2 class="section-title">Data-Driven Agro Finance</h2>
          <div class="section-accent-rule"></div>
        </div>
        <p style="font-size:1.01rem;"><?= e($page['intro_paragraph']) ?></p>
        <p style="font-size:1.01rem;"><?= e($page['scoring_paragraph']) ?></p>
        <p style="font-size:1.01rem;margin-bottom:0;"><?= e($page['risk_paragraph']) ?></p>

        <div class="pillar-chips" aria-label="Six Agro Scoring Dimensions">
          <?php foreach ($pillars as $pillar): ?>
          <span class="pillar-chip">
            <i data-lucide="<?= e($pillar['icon'] ?? 'check') ?>"></i>
            <span><?= e($pillar['label'] ?? '') ?></span>
          </span>
          <?php endforeach; ?>
        </div>
      </div>
    </div>

    <div class="section-header centered">
      <p class="section-eyebrow">Risk Protection Suite</p>
      <h2 class="section-title">Core Agro Risk Capabilities</h2>
      <div class="section-accent-rule"></div>
    </div>

    <div class="cards-grid-4" style="margin-bottom:48px;">
      <?php foreach ($features as $feat): ?>
      <article class="glass-card glass-card-hover xds-scroll-card offering-card">
        <div class="service-icon-orb" style="width:56px;height:56px;border-radius:14px;margin-bottom:16px;">
          <i data-lucide="<?= e($feat['icon'] ?? 'bar-chart-3') ?>" style="width:26px;height:26px;"></i>
        </div>
        <h3 style="font-size:1.12rem;margin-bottom:10px;"><?= e($feat['title'] ?? '') ?></h3>
        <p style="font-size:0.92rem;margin-bottom:0;"><?= e($feat['description'] ?? '') ?></p>
      </article>
      <?php endforeach; ?>
    </div>

    <div class="cta-glass-banner xds-scroll-card">
      <div>
        <h2>Protect Your Seasonal Lending Portfolio</h2>
        <p>Speak with our Agro Business Unit to enable credit exposure reports, double-dip alerts, and side-marketing alerts for your next season.</p>
      </div>
      <a href="<?= e(xds_url('contact/')) ?>" class="btn btn-lime">
        <i data-lucide="calendar-clock"></i>
        <span>Book an Agro Consultation</span>
      </a>
    </div>
  </div>
</section>

<?php require dirname(__DIR__, 2) . '/inc/layout/footer.php'; ?>
