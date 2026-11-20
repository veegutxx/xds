<?php
declare(strict_types=1);

require_once __DIR__ . '/inc/config/index.php';
require_once __DIR__ . '/inc/seo/index.php';

$system         = xds_get_system();
$home           = xds_fetch_single_table('page_home', 'page_home');
$slides         = xds_decode_json($home['hero_slides_json'] ?? null);
$services       = xds_decode_json($home['services_json'] ?? null);
$latestArticles = xds_get_articles(null, null, 1, 3)['items'] ?? [];

if (empty($slides)) {
    $slides = [[
        'eyebrow'         => $home['hero_badge'] ?? 'Zimbabwe Credit Bureau & Risk Intelligence',
        'headline'        => $home['hero_headline'] ?? 'Turning Credit & Verification Data Into Confident Decisions',
        'insight'         => $home['hero_subheadline'] ?? '',
        'primary_label'   => $home['hero_primary_cta_label'] ?? 'Explore Our Solutions',
        'primary_href'    => $home['hero_primary_cta_href'] ?? '#our-services',
        'secondary_label' => $home['hero_secondary_cta_label'] ?? 'Schedule a Consultation',
        'secondary_href'  => $home['hero_secondary_cta_href'] ?? '/contact/',
    ]];
}

$activeNav = 'home';
$loadThree = true;
$pageMeta  = [
    'title'          => $home['meta_title'],
    'description'    => $home['meta_description'],
    'canonical_path' => '',
    'og_image_file'  => $home['og_image_file'] ?? $home['hero_image_file'],
    'schemas'        => [
        xds_seo_organization_schema($system),
        [
            '@context' => 'https://schema.org',
            '@type'    => 'WebSite',
            'name'     => $system['site_name'],
            'url'      => CANONICAL_BASE_URL,
        ],
    ],
];

require __DIR__ . '/inc/layout/header.php';
?>

<section class="hero-showcase" aria-label="XDS Zimbabwe Business Insights">
  <div
    class="hero-bg-photo"
    style="background-image: url('<?= e(xds_image_asset($home['hero_image_file'])) ?>');"
    aria-hidden="true"
  ></div>
  <div class="hero-bg-overlay" aria-hidden="true"></div>
  <canvas id="xds-hero-canvas" aria-hidden="true"></canvas>

  <div class="container hero-content-wrap hero-centered-wrap">
    <nav class="breadcrumb breadcrumb-centered" aria-label="Breadcrumb">
      <a href="<?= e(xds_url('')) ?>">XDS Zimbabwe</a>
      <span>/</span>
      <span aria-current="page">Home</span>
    </nav>

    <div class="hero-carousel" data-hero-carousel aria-roledescription="carousel" aria-label="Zimbabwe Business Risk Insights">
      <div class="hero-slides-stage">
        <?php foreach ($slides as $idx => $slide):
          $pHref = (string)($slide['primary_href'] ?? '#our-services');
          $sHref = (string)($slide['secondary_href'] ?? '/contact/');
          $pUrl  = str_starts_with($pHref, '#') ? $pHref : xds_url(ltrim($pHref, '/'));
          $sUrl  = str_starts_with($sHref, '#') ? $sHref : xds_url(ltrim($sHref, '/'));
        ?>
        <div
          class="hero-slide<?= $idx === 0 ? ' is-active' : '' ?>"
          data-hero-slide
          role="group"
          aria-roledescription="slide"
          aria-label="Slide <?= $idx + 1 ?> of <?= count($slides) ?>"
          aria-hidden="<?= $idx === 0 ? 'false' : 'true' ?>"
        >
          <p class="hero-kicker"><?= e($slide['eyebrow'] ?? '') ?></p>
          <?php if ($idx === 0): ?>
            <h1 class="hero-title"><?= e($slide['headline'] ?? '') ?></h1>
          <?php else: ?>
            <h2 class="hero-title"><?= e($slide['headline'] ?? '') ?></h2>
          <?php endif; ?>
          <p class="hero-subtitle"><?= e($slide['insight'] ?? '') ?></p>
          <div class="hero-actions hero-actions-centered">
            <a href="<?= e($pUrl) ?>" class="btn btn-lime btn-lg">
              <span><?= e($slide['primary_label'] ?? 'Explore Solutions') ?></span>
              <i data-lucide="arrow-right"></i>
            </a>
            <a href="<?= e($sUrl) ?>" class="btn btn-outline-light btn-lg">
              <i data-lucide="calendar-clock"></i>
              <span><?= e($slide['secondary_label'] ?? 'Contact Us') ?></span>
            </a>
          </div>
        </div>
        <?php endforeach; ?>
      </div>

      <?php if (count($slides) > 1): ?>
      <div class="hero-carousel-controls" aria-label="Carousel Controls">
        <button type="button" class="hero-carousel-arrow" data-carousel-prev aria-label="Previous business insight">
          <i data-lucide="chevron-left"></i>
        </button>
        <div class="hero-carousel-dots" role="tablist" aria-label="Select insight slide">
          <?php foreach ($slides as $idx => $slide): ?>
          <button
            type="button"
            class="hero-carousel-dot<?= $idx === 0 ? ' is-active' : '' ?>"
            data-carousel-dot="<?= $idx ?>"
            role="tab"
            aria-selected="<?= $idx === 0 ? 'true' : 'false' ?>"
            aria-label="Show insight <?= $idx + 1 ?>: <?= e($slide['eyebrow'] ?? '') ?>"
          ></button>
          <?php endforeach; ?>
        </div>
        <button type="button" class="hero-carousel-arrow" data-carousel-next aria-label="Next business insight">
          <i data-lucide="chevron-right"></i>
        </button>
      </div>
      <?php endif; ?>
    </div>
  </div>
</section>

<section class="section">
  <div class="container">
    <div class="glass-card xds-scroll-card intro-split-grid">
      <div>
        <div class="section-header" style="margin-bottom:18px;">
          <p class="section-eyebrow"><?= e($home['intro_subtitle']) ?></p>
          <h2 class="section-title"><?= e($home['intro_title']) ?></h2>
          <div class="section-accent-rule"></div>
        </div>
        <p style="font-size:1.04rem;margin-bottom:22px;"><?= e($home['intro_body']) ?></p>
        <div style="display:flex;flex-wrap:wrap;gap:12px;">
          <a href="<?= e(xds_url('about/')) ?>#overview" class="btn btn-primary">
            <span>About XDS Zimbabwe</span>
            <i data-lucide="arrow-right"></i>
          </a>
          <a href="<?= e(xds_url('about/')) ?>#why-xds" class="btn btn-glass">
            <span>Why Choose XDS</span>
          </a>
        </div>
      </div>

      <div style="display:flex;flex-direction:column;gap:14px;">
        <div class="glass-card" style="padding:18px 20px;display:flex;align-items:center;gap:14px;">
          <span class="faq-icon-badge"><i data-lucide="lock-keyhole"></i></span>
          <div>
            <strong style="display:block;color:var(--heading-color);font-size:0.95rem;">Data Privacy &amp; ISSP</strong>
            <span style="font-size:0.84rem;color:var(--text-muted);">Strict need-to-know access &amp; encrypted transit</span>
          </div>
        </div>
        <div class="glass-card" style="padding:18px 20px;display:flex;align-items:center;gap:14px;">
          <span class="faq-icon-badge"><i data-lucide="building-2"></i></span>
          <div>
            <strong style="display:block;color:var(--heading-color);font-size:0.95rem;">125+ Zimbabwean Providers</strong>
            <span style="font-size:0.84rem;color:var(--text-muted);">Over 2 million local consumer &amp; corporate profiles</span>
          </div>
        </div>
        <div class="glass-card" style="padding:18px 20px;display:flex;align-items:center;gap:14px;">
          <span class="faq-icon-badge"><i data-lucide="zap"></i></span>
          <div>
            <strong style="display:block;color:var(--heading-color);font-size:0.95rem;">24/7 Online Portal</strong>
            <span style="font-size:0.84rem;color:var(--text-muted);">Instant consumer, corporate &amp; E Check reports</span>
          </div>
        </div>
      </div>
    </div>
  </div>
</section>

<!-- 3D Hover Flip Service Cards Section -->
<section id="our-services" class="section section-tight">
  <div class="container">
    <div class="section-header centered">
      <p class="section-eyebrow">Risk &amp; Verification Suite</p>
      <h2 class="section-title"><?= e($home['services_heading']) ?></h2>
      <div class="section-accent-rule"></div>
      <p class="section-subtitle"><?= e($home['services_subheading']) ?> Hover or tap any card for more info.</p>
    </div>

    <div class="cards-grid-3">
      <?php foreach ($services as $svc): ?>
      <div class="xds-flip-card xds-scroll-card" tabindex="0" role="region" aria-label="<?= e($svc['title'] ?? '') ?>">
        <div class="xds-flip-inner">
          <!-- Front Face -->
          <div class="xds-flip-face xds-flip-front">
            <div class="service-icon-orb" aria-hidden="true">
              <i data-lucide="<?= e($svc['icon'] ?? 'briefcase') ?>"></i>
            </div>
            <h3 style="font-size:1.3rem;margin-bottom:12px;"><?= e($svc['title'] ?? '') ?></h3>
            <p style="font-size:0.94rem;margin-bottom:10px;"><?= e($svc['summary'] ?? '') ?></p>

            <?php if (!empty($svc['badge_file'])): ?>
              <img
                src="<?= e(xds_image_asset((string)$svc['badge_file'])) ?>"
                alt="E Check Employment Screening Solutions"
                class="echeck-badge-inline"
                loading="lazy"
              >
            <?php endif; ?>

            <span class="flip-hint-pill">
              <i data-lucide="rotate-cw" style="width:13px;height:13px;"></i>
              <span>Hover for more info</span>
            </span>
          </div>

          <!-- Back Face -->
          <div class="xds-flip-face xds-flip-back">
            <div>
              <span style="display:inline-block;font-size:0.74rem;font-weight:700;letter-spacing:0.08em;text-transform:uppercase;color:var(--xds-lime);margin-bottom:8px;">
                <?= e($svc['title'] ?? '') ?>
              </span>
              <h4 style="font-size:1.25rem;margin-bottom:16px;"><?= e($svc['back_title'] ?? '') ?></h4>
              <ul class="flip-highlights-list">
                <?php foreach (($svc['highlights'] ?? []) as $hl): ?>
                <li>
                  <i data-lucide="check-circle-2"></i>
                  <span><?= e((string)$hl) ?></span>
                </li>
                <?php endforeach; ?>
              </ul>
            </div>

            <a href="<?= e(xds_url(ltrim((string)($svc['href'] ?? ''), '/'))) ?>" class="btn btn-lime" style="width:100%;">
              <span><?= e($svc['cta_label'] ?? 'Find Out More') ?></span>
              <i data-lucide="arrow-right"></i>
            </a>
          </div>
        </div>
      </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<?php if (!empty($latestArticles)): ?>
<section class="section">
  <div class="container">
    <div style="display:flex;flex-wrap:wrap;align-items:flex-end;justify-content:space-between;gap:16px;margin-bottom:34px;">
      <div class="section-header" style="margin-bottom:0;">
        <p class="section-eyebrow">XDS Insights</p>
        <h2 class="section-title" style="margin-bottom:8px;">Latest Bureau Research &amp; Guides</h2>
        <div class="section-accent-rule" style="margin-bottom:0;"></div>
      </div>
      <a href="<?= e(xds_url('resources/articles/')) ?>" class="btn btn-glass">
        <span>View All Articles</span>
        <i data-lucide="arrow-right"></i>
      </a>
    </div>

    <div class="cards-grid-3">
      <?php foreach ($latestArticles as $art): ?>
      <article class="glass-card glass-card-hover xds-scroll-card article-card">
        <div style="height:170px;overflow:hidden;border-bottom:1px solid var(--glass-border);">
          <img
            src="<?= e(xds_article_asset($art['slug'], $art['image_file'])) ?>"
            alt="<?= e($art['title']) ?>"
            style="width:100%;height:100%;object-fit:cover;"
            loading="lazy"
          >
        </div>
        <div class="article-card-body">
          <div class="article-meta">
            <span class="category-badge"><?= e($art['category']) ?></span>
            <span><?= e(date('d M Y', strtotime((string)$art['published_date']))) ?></span>
          </div>
          <h3><?= e($art['title']) ?></h3>
          <p style="font-size:0.9rem;margin-bottom:16px;"><?= e($art['summary']) ?></p>
          <div class="article-card-footer">
            <span style="font-size:0.8rem;color:var(--text-muted);"><?= (int)($art['read_time_minutes'] ?? 4) ?> min read</span>
            <a href="<?= e(xds_url('resources/articles/view/?slug=' . rawurlencode((string)$art['slug']))) ?>" class="btn btn-primary btn-sm">
              <span>Read More</span>
              <i data-lucide="arrow-right"></i>
            </a>
          </div>
        </div>
      </article>
      <?php endforeach; ?>
    </div>
  </div>
</section>
<?php endif; ?>

<section class="section section-tight">
  <div class="container">
    <div class="cta-glass-banner xds-scroll-card">
      <div>
        <h2><?= e($home['cta_banner_title']) ?></h2>
        <p><?= e($home['cta_banner_copy']) ?></p>
      </div>
      <div style="display:flex;flex-wrap:wrap;gap:12px;">
        <a href="<?= e(xds_url('contact/')) ?>" class="btn btn-lime">
          <i data-lucide="send"></i>
          <span><?= e($home['cta_banner_button']) ?></span>
        </a>
        <a href="<?= e(xds_url('resources/faq/')) ?>" class="btn btn-outline-light">
          <i data-lucide="help-circle"></i>
          <span>Explore FAQ</span>
        </a>
      </div>
    </div>
  </div>
</section>

<?php require __DIR__ . '/inc/layout/footer.php'; ?>
