<?php

declare(strict_types=1);

require_once dirname(__DIR__, 2) . '/inc/config/index.php';
require_once dirname(__DIR__, 2) . '/inc/seo/index.php';

$system     = xds_get_system();
$faqData    = xds_get_faqs();
$faqs       = $faqData['items'] ?? [];
$categories = $faqData['categories'] ?? [];
$bannerFile = $faqs[0]['banner_image_file'] ?? 'banner-faq.webp';

$faqSchemaEntities = array_map(static function (array $item): array {
    return [
        '@type'          => 'Question',
        'name'           => $item['question'],
        'acceptedAnswer' => [
            '@type' => 'Answer',
            'text'  => $item['answer'],
        ],
    ];
}, $faqs);

$activeNav = 'resources';
$activeSub = 'faq';
$pageMeta  = [
    'title'          => $faqs[0]['meta_title'] ?? 'FAQ | XDS Zimbabwe',
    'description'    => $faqs[0]['meta_description'] ?? 'Find answers to common questions about XDS Zimbabwe credit bureau reports, data submission, regulation, data security, and accuracy.',
    'canonical_path' => 'resources/faq/',
    'og_image_file'  => $bannerFile,
    'schemas'        => [
        xds_seo_organization_schema($system),
        [
            '@context'   => 'https://schema.org',
            '@type'      => 'FAQPage',
            'mainEntity' => $faqSchemaEntities,
        ],
    ],
];

require dirname(__DIR__, 2) . '/inc/layout/header.php';
?>

<section class="page-banner" aria-label="Frequently Asked Questions">
  <img src="<?= e(xds_image_asset($bannerFile)) ?>" alt="XDS Zimbabwe client support team" class="page-banner-img">
  <div class="page-banner-overlay" aria-hidden="true"></div>
  <div class="container page-banner-content">
    <nav class="breadcrumb" aria-label="Breadcrumb">
      <a href="<?= e(xds_url('')) ?>">Home</a>
      <span>/</span>
      <span>Resources</span>
      <span>/</span>
      <span aria-current="page">FAQ</span>
    </nav>
    <h1>Frequently Asked Questions</h1>
    <p class="page-banner-lead">You have a question? Find clear answers on XDS reports, data contribution, Zimbabwean bureau regulations, and security.</p>
  </div>
</section>

<section class="section">
  <div class="container" style="max-width:1000px;">
    <div class="glass-card filter-toolbar" role="search" aria-label="Search FAQs">
      <div class="search-input-wrap">
        <i data-lucide="search" aria-hidden="true"></i>
        <label for="faq-search" class="sr-only">Search frequently asked questions</label>
        <input id="faq-search" type="search" placeholder="Search questions (e.g. regulation, accuracy, security)..." autocomplete="off">
      </div>

      <div class="filter-pills" role="group" aria-label="FAQ Categories">
        <button type="button" class="filter-pill is-active" data-faq-category="all">All</button>
        <?php foreach ($categories as $cat): ?>
        <button type="button" class="filter-pill" data-faq-category="<?= e((string)$cat) ?>"><?= e((string)$cat) ?></button>
        <?php endforeach; ?>
      </div>

      <span id="faq-count" style="font-size:0.84rem;font-weight:600;color:var(--text-muted);">
        <?= count($faqs) ?> questions
      </span>
    </div>

    <div id="faq-list" class="faq-list" style="margin-bottom:46px;">
      <?php foreach ($faqs as $idx => $faq): ?>
      <div class="glass-card xds-scroll-card faq-item<?= $idx === 0 ? ' is-open' : '' ?>">
        <button
          type="button"
          class="faq-trigger"
          aria-expanded="<?= $idx === 0 ? 'true' : 'false' ?>"
        >
          <span class="faq-trigger-left">
            <span class="faq-icon-badge" aria-hidden="true">
              <i data-lucide="<?= e($faq['icon_key'] ?? 'help-circle') ?>"></i>
            </span>
            <span><?= e($faq['question']) ?></span>
          </span>
          <i data-lucide="chevron-down" class="faq-chevron" aria-hidden="true"></i>
        </button>
        <div class="faq-panel" style="<?= $idx === 0 ? 'max-height:480px;' : '' ?>">
          <div class="faq-panel-inner">
            <p style="margin-bottom:10px;"><?= e($faq['answer']) ?></p>
            <span class="category-badge"><?= e($faq['category']) ?></span>
          </div>
        </div>
      </div>
      <?php endforeach; ?>
    </div>

    <div class="cta-glass-banner xds-scroll-card">
      <div>
        <h2>Still Need Assistance?</h2>
        <p>Our Harare bureau support team is available Monday to Friday, 0800hrs – 1630hrs to answer technical or consumer enquiries.</p>
      </div>
      <a href="<?= e(xds_url('contact/')) ?>" class="btn btn-lime">
        <i data-lucide="message-square"></i>
        <span>Ask Our Team</span>
      </a>
    </div>
  </div>
</section>

<?php require dirname(__DIR__, 2) . '/inc/layout/footer.php'; ?>
