<?php

declare(strict_types=1);

require_once dirname(__DIR__, 2) . '/inc/config/index.php';
require_once dirname(__DIR__, 2) . '/inc/seo/index.php';

if (!empty($_GET['slug'])) {
    $slug = preg_replace('/[^a-z0-9-]/i', '', (string)$_GET['slug']) ?: '';
    if ($slug !== '') {
        header('Location: ' . xds_url('resources/articles/view/?slug=' . rawurlencode($slug)), true, 302);
        exit;
    }
}

$system     = xds_get_system();
$result     = xds_get_articles(null, null, 1, 12);
$articles   = $result['items'] ?? [];
$categories = $result['categories'] ?? [];
$bannerFile = $articles[0]['banner_image_file'] ?? 'banner-articles.webp';

$activeNav = 'resources';
$activeSub = 'articles';
$pageMeta  = [
    'title'          => 'Articles | XDS Zimbabwe',
    'description'    => 'Read expert articles from XDS Zimbabwe covering credit scoring, Basel II, credit information sharing, smallholder agro-finance, and good credit habits.',
    'canonical_path' => 'resources/articles/',
    'og_type'        => 'website',
    'og_image_file'  => $bannerFile,
    'schemas'        => [xds_seo_organization_schema($system)],
];

require dirname(__DIR__, 2) . '/inc/layout/header.php';
?>

<section class="page-banner" aria-label="XDS Articles and Insights">
  <img src="<?= e(xds_image_asset($bannerFile)) ?>" alt="XDS Zimbabwe Articles and Research" class="page-banner-img">
  <div class="page-banner-overlay" aria-hidden="true"></div>
  <div class="container page-banner-content">
    <nav class="breadcrumb" aria-label="Breadcrumb">
      <a href="<?= e(xds_url('')) ?>">Home</a>
      <span>/</span>
      <span>Resources</span>
      <span>/</span>
      <span aria-current="page">Articles</span>
    </nav>
    <h1>Articles &amp; Insights</h1>
    <p class="page-banner-lead">Research perspectives on credit scoring, regulatory compliance, agricultural risk profiling, and building healthy credit habits in Zimbabwe.</p>
  </div>
</section>

<section class="section">
  <div class="container">
    <div class="glass-card filter-toolbar" role="search" aria-label="Filter articles">
      <div class="search-input-wrap">
        <i data-lucide="search" aria-hidden="true"></i>
        <label for="articles-search" class="sr-only">Search articles</label>
        <input id="articles-search" type="search" placeholder="Search articles by keyword, topic, or title..." autocomplete="off">
      </div>

      <div class="filter-pills" role="group" aria-label="Article categories">
        <button type="button" class="filter-pill is-active" data-article-category="all">All Topics</button>
        <?php foreach ($categories as $cat): ?>
        <button type="button" class="filter-pill" data-article-category="<?= e((string)$cat) ?>"><?= e((string)$cat) ?></button>
        <?php endforeach; ?>
      </div>

      <span id="articles-count" style="font-size:0.84rem;font-weight:600;color:var(--text-muted);">
        <?= (int)($result['total'] ?? count($articles)) ?> articles found
      </span>
    </div>

    <div id="articles-grid" class="cards-grid-2">
      <?php foreach ($articles as $art):
        $viewUrl = xds_url('resources/articles/view/?slug=' . rawurlencode((string)$art['slug']));
      ?>
      <article class="glass-card glass-card-hover xds-scroll-card article-card">
        <div class="article-card-body">
          <div class="article-meta">
            <span class="category-badge"><?= e($art['category']) ?></span>
            <span><?= e(date('d M Y', strtotime((string)$art['published_date']))) ?></span>
            <span>&bull; <?= (int)($art['read_time_minutes'] ?? 4) ?> min read</span>
          </div>

          <div class="article-card-top">
            <a href="<?= e($viewUrl) ?>" style="flex-shrink:0;">
              <img
                class="article-thumb"
                src="<?= e(xds_article_asset($art['slug'], $art['image_file'])) ?>"
                alt="<?= e($art['title']) ?>"
                loading="lazy"
              >
            </a>
            <div>
              <h3><a href="<?= e($viewUrl) ?>"><?= e($art['title']) ?></a></h3>
              <p style="margin-bottom:0;font-size:0.92rem;"><?= e($art['summary']) ?></p>
            </div>
          </div>

          <div class="article-card-footer">
            <span style="font-size:0.82rem;color:var(--text-muted);"><?= e($art['author']) ?></span>
            <div style="display:flex;align-items:center;gap:8px;">
              <button
                type="button"
                class="btn btn-glass btn-sm"
                data-quick-share
                data-share-title="<?= e($art['title']) ?>"
                data-share-text="<?= e($art['summary']) ?>"
                data-share-url="<?= e(xds_canonical('resources/articles/' . $art['slug'] . '/')) ?>"
                aria-label="Share <?= e($art['title']) ?>"
                title="Share article"
              >
                <i data-lucide="share-2"></i>
              </button>
              <a href="<?= e($viewUrl) ?>" class="btn btn-primary btn-sm">
                <span>Read More</span>
                <i data-lucide="arrow-right"></i>
              </a>
            </div>
          </div>
        </div>
      </article>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<?php require dirname(__DIR__, 2) . '/inc/layout/footer.php'; ?>
