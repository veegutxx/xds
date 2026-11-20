<?php

declare(strict_types=1);

require_once dirname(__DIR__, 2) . '/inc/config/index.php';
require_once dirname(__DIR__, 2) . '/inc/seo/index.php';

$system           = xds_get_system();
$page             = xds_fetch_single_table('page_data_privacy', 'page_data_privacy');
$missionBullets   = xds_decode_json($page['mission_bullets_json'] ?? null);
$objectiveBullets = xds_decode_json($page['objectives_bullets_json'] ?? null);
$scopeParagraphs  = xds_decode_json($page['scope_paragraphs_json'] ?? null);

$policyDocUrl = (!empty($page['policy_doc_file']))
    ? xds_document_asset(
        (string)($page['policy_doc_purpose'] ?? 'policy'),
        (string)($page['policy_doc_folder'] ?? 'xds-information-security-policy'),
        (string)$page['policy_doc_file']
    )
    : null;

$activeNav = 'resources';
$activeSub = 'privacy';
$pageMeta  = [
    'title'          => $page['meta_title'],
    'description'    => $page['meta_description'],
    'canonical_path' => 'resources/data-privacy/',
    'og_image_file'  => $page['og_image_file'] ?? $page['banner_image_file'],
    'schemas'        => [xds_seo_organization_schema($system)],
];

require dirname(__DIR__, 2) . '/inc/layout/header.php';
?>

<section class="page-banner" aria-label="Data Privacy and Information Security">
  <img src="<?= e(xds_image_asset($page['banner_image_file'])) ?>" alt="XDS Information Security Shield" class="page-banner-img">
  <div class="page-banner-overlay" aria-hidden="true"></div>
  <div class="container page-banner-content">
    <nav class="breadcrumb" aria-label="Breadcrumb">
      <a href="<?= e(xds_url('')) ?>">Home</a>
      <span>/</span>
      <span>Resources</span>
      <span>/</span>
      <span aria-current="page">Data Privacy</span>
    </nav>
    <h1><?= e($page['page_title']) ?></h1>
    <p class="page-banner-lead">Our Information Security Policy (ISSP) governing the confidentiality, integrity, and availability of subscriber and consumer records.</p>
  </div>
</section>

<section class="section">
  <div class="container" style="max-width:1020px;">
    <div class="glass-card xds-scroll-card" style="padding:32px;margin-bottom:28px;display:flex;flex-wrap:wrap;align-items:center;justify-content:space-between;gap:22px;">
      <div style="flex:1 1 520px;">
        <p class="section-eyebrow">Information Security Policy (ISSP)</p>
        <p style="font-size:1.05rem;margin-bottom:0;"><?= e($page['intro_copy']) ?></p>
      </div>
      <?php if ($policyDocUrl): ?>
      <a href="<?= e($policyDocUrl) ?>" class="btn btn-primary" download>
        <i data-lucide="file-down"></i>
        <span>Download ISSP PDF</span>
      </a>
      <?php endif; ?>
    </div>

    <article class="glass-card xds-scroll-card offering-card" style="margin-bottom:24px;">
      <div style="display:flex;align-items:center;gap:14px;margin-bottom:14px;">
        <span class="faq-icon-badge" style="width:46px;height:46px;border-radius:12px;"><i data-lucide="shield-alert"></i></span>
        <h2 style="font-size:1.45rem;margin-bottom:0;"><?= e($page['mission_title']) ?></h2>
      </div>
      <p style="font-size:0.98rem;margin-bottom:18px;"><?= e($page['mission_intro']) ?></p>
      <ul class="xds-check-list">
        <?php foreach ($missionBullets as $item): ?>
        <li>
          <i data-lucide="check-circle-2"></i>
          <span><?= e((string)$item) ?></span>
        </li>
        <?php endforeach; ?>
      </ul>
    </article>

    <article class="glass-card xds-scroll-card offering-card" style="margin-bottom:24px;">
      <div style="display:flex;align-items:center;gap:14px;margin-bottom:14px;">
        <span class="faq-icon-badge" style="width:46px;height:46px;border-radius:12px;"><i data-lucide="key-round"></i></span>
        <h2 style="font-size:1.45rem;margin-bottom:0;"><?= e($page['philosophy_title']) ?></h2>
      </div>
      <p style="font-size:1.01rem;margin-bottom:0;"><?= e($page['philosophy_body']) ?></p>
    </article>

    <article class="glass-card xds-scroll-card offering-card" style="margin-bottom:24px;">
      <div style="display:flex;align-items:center;gap:14px;margin-bottom:14px;">
        <span class="faq-icon-badge" style="width:46px;height:46px;border-radius:12px;"><i data-lucide="target"></i></span>
        <h2 style="font-size:1.45rem;margin-bottom:0;"><?= e($page['objectives_title']) ?></h2>
      </div>
      <ul class="xds-check-list">
        <?php foreach ($objectiveBullets as $obj): ?>
        <li>
          <i data-lucide="shield-check"></i>
          <span><?= e((string)$obj) ?></span>
        </li>
        <?php endforeach; ?>
      </ul>
    </article>

    <article class="glass-card xds-scroll-card offering-card" style="margin-bottom:32px;">
      <div style="display:flex;align-items:center;gap:14px;margin-bottom:14px;">
        <span class="faq-icon-badge" style="width:46px;height:46px;border-radius:12px;"><i data-lucide="globe-lock"></i></span>
        <h2 style="font-size:1.45rem;margin-bottom:0;"><?= e($page['scope_title']) ?></h2>
      </div>
      <?php foreach ($scopeParagraphs as $idx => $para): ?>
      <p style="<?= $idx === count($scopeParagraphs) - 1 ? 'margin-bottom:0;' : '' ?>"><?= e((string)$para) ?></p>
      <?php endforeach; ?>
    </article>

    <div class="cta-glass-banner xds-scroll-card">
      <div>
        <h2>Have a Data Privacy or Compliance Question?</h2>
        <p>Reach out to our Harare compliance team for subscriber security questionnaires or data protection guidance.</p>
      </div>
      <a href="<?= e(xds_url('contact/')) ?>" class="btn btn-lime">
        <i data-lucide="mail"></i>
        <span>Contact Compliance Desk</span>
      </a>
    </div>
  </div>
</section>

<?php require dirname(__DIR__, 2) . '/inc/layout/footer.php'; ?>
