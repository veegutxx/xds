<?php

declare(strict_types=1);

require_once dirname(__DIR__, 3) . '/inc/config/index.php';
require_once dirname(__DIR__, 3) . '/inc/seo/index.php';

$system  = xds_get_system();
$slug    = trim((string)($_GET['slug'] ?? ''));
$article = $slug !== '' ? xds_get_article_by_slug($slug) : null;

if ($article === null) {
    http_response_code(404);
}

$allArticles = xds_get_articles(null, null, 1, 12)['items'] ?? [];
$related     = array_values(array_filter($allArticles, static function (array $item) use ($article): bool {
    return $article === null || $item['slug'] !== $article['slug'];
}));
$related     = array_slice($related, 0, 3);
$bannerFile  = $article['banner_image_file'] ?? 'banner-articles.webp';

$schemas = [xds_seo_organization_schema($system)];
if ($article !== null) {
    $schemas[] = [
        '@context'      => 'https://schema.org',
        '@type'         => 'Article',
        'headline'      => $article['title'],
        'description'   => $article['meta_description'] ?? $article['summary'],
        'datePublished' => $article['published_date'],
        'author'        => ['@type' => 'Organization', 'name' => $article['author']],
        'publisher'     => [
            '@type' => 'Organization',
            'name'  => $system['site_name'],
            'logo'  => ['@type' => 'ImageObject', 'url' => CANONICAL_ORIGIN . '/assets/images/system/' . rawurlencode(basename((string)$system['logo_file']))],
        ],
        'image'         => CANONICAL_ORIGIN . '/assets/images/articles/' . rawurlencode((string)$article['slug']) . '/' . rawurlencode(basename((string)$article['image_file'])),
    ];
}

$articleCoverUrl = $article ? xds_article_asset($article['slug'], $article['image_file']) : null;
$articleShareUrl = $article ? xds_canonical('resources/articles/' . $article['slug'] . '/') : xds_canonical('resources/articles/');

$activeNav = 'resources';
$activeSub = 'articles';
$pageMeta  = [
    'title'                => $article ? ($article['meta_title'] ?: ($article['title'] . ' | XDS Zimbabwe')) : 'Article Not Found | XDS Zimbabwe',
    'description'          => $article ? ($article['meta_description'] ?: $article['summary']) : 'The requested XDS Zimbabwe article could not be located.',
    'canonical_path'       => $article ? ('resources/articles/' . $article['slug'] . '/') : 'resources/articles/',
    'og_type'              => $article ? 'article' : 'website',
    'og_image_url'         => $article
        ? (CANONICAL_ORIGIN . '/assets/images/articles/' . rawurlencode((string)$article['slug']) . '/' . rawurlencode(basename((string)$article['image_file'])))
        : null,
    'apple_touch_icon_url' => $articleCoverUrl,
    'og_image_file'        => $bannerFile,
    'schemas'              => $schemas,
];

require dirname(__DIR__, 3) . '/inc/layout/header.php';
?>

<section class="page-banner" aria-label="Article Header">
  <img src="<?= e(xds_image_asset($bannerFile)) ?>" alt="<?= e($article['title'] ?? 'XDS Articles') ?>" class="page-banner-img">
  <div class="page-banner-overlay" aria-hidden="true"></div>
  <div class="container page-banner-content">
    <nav class="breadcrumb" aria-label="Breadcrumb">
      <a href="<?= e(xds_url('')) ?>">Home</a>
      <span>/</span>
      <span>Resources</span>
      <span>/</span>
      <a href="<?= e(xds_url('resources/articles/')) ?>">Articles</a>
      <span>/</span>
      <span aria-current="page"><?= e($article['title'] ?? 'Not Found') ?></span>
    </nav>
    <h1><?= e($article['title'] ?? 'Article Not Found') ?></h1>
    <?php if ($article !== null): ?>
    <p class="page-banner-lead">
      <?= e($article['category']) ?> &middot; <?= e(date('d M Y', strtotime((string)$article['published_date']))) ?> &middot; <?= e($article['author']) ?> &middot; <?= (int)($article['read_time_minutes'] ?? 4) ?> min read
    </p>
    <?php else: ?>
    <p class="page-banner-lead">We could not find the article you were looking for. Browse our latest Zimbabwe credit bureau insights below.</p>
    <?php endif; ?>
  </div>
</section>

<section class="section">
  <div class="container" style="max-width:920px;">
    <?php if ($article !== null): ?>
    <article class="glass-card xds-scroll-card" style="padding:36px 34px;margin-bottom:44px;">
      <!-- Article Meta & Share Action Bar -->
      <div class="article-share-topbar">
        <div class="article-meta" style="margin-bottom:0;">
          <span class="category-badge"><?= e($article['category']) ?></span>
          <span><?= e(date('d M Y', strtotime((string)$article['published_date']))) ?></span>
          <span>&bull; <?= e($article['author']) ?></span>
          <span>&bull; <?= (int)($article['read_time_minutes'] ?? 4) ?> min read</span>
        </div>

        <div class="share-control-wrap" data-share-wrap>
          <button
            type="button"
            class="btn btn-glass btn-sm"
            data-share-btn
            data-share-title="<?= e($article['title']) ?>"
            data-share-text="<?= e($article['summary']) ?>"
            data-share-url="<?= e($articleShareUrl) ?>"
            aria-expanded="false"
            aria-haspopup="menu"
          >
            <i data-lucide="share-2"></i>
            <span>Share Article</span>
          </button>
          <div class="share-popover" role="menu" aria-label="Share this article">
            <button type="button" class="share-option-btn" data-share-copy="<?= e($articleShareUrl) ?>" role="menuitem">
              <i data-lucide="link-2"></i><span>Copy Link</span>
            </button>
            <a class="share-option-btn" target="_blank" rel="noopener noreferrer" href="https://wa.me/?text=<?= rawurlencode($article['title'] . ' — ' . $articleShareUrl) ?>" role="menuitem">
              <i data-lucide="message-circle"></i><span>WhatsApp</span>
            </a>
            <a class="share-option-btn" target="_blank" rel="noopener noreferrer" href="https://www.linkedin.com/sharing/share-offsite/?url=<?= rawurlencode($articleShareUrl) ?>" role="menuitem">
              <i data-lucide="linkedin"></i><span>LinkedIn</span>
            </a>
            <a class="share-option-btn" target="_blank" rel="noopener noreferrer" href="https://twitter.com/intent/tweet?text=<?= rawurlencode($article['title']) ?>&amp;url=<?= rawurlencode($articleShareUrl) ?>" role="menuitem">
              <i data-lucide="send"></i><span>X / Twitter</span>
            </a>
            <a class="share-option-btn" target="_blank" rel="noopener noreferrer" href="https://www.facebook.com/sharer/sharer.php?u=<?= rawurlencode($articleShareUrl) ?>" role="menuitem">
              <i data-lucide="globe"></i><span>Facebook</span>
            </a>
          </div>
        </div>
      </div>

      <div style="margin-bottom:26px;">
        <img
          src="<?= e(xds_article_asset($article['slug'], $article['image_file'])) ?>"
          alt="<?= e($article['title']) ?>"
          style="width:100%;max-height:420px;object-fit:cover;border-radius:14px;border:1px solid var(--glass-border);"
        >
      </div>

      <div class="article-prose">
        <?= $article['body_content'] ?>
      </div>

      <div style="margin-top:34px;padding-top:22px;border-top:1px solid var(--divider);display:flex;flex-wrap:wrap;align-items:center;justify-content:space-between;gap:14px;">
        <a href="<?= e(xds_url('resources/articles/')) ?>" class="btn btn-glass">
          <i data-lucide="arrow-left"></i>
          <span>Back to All Articles</span>
        </a>
        <div style="display:flex;flex-wrap:wrap;gap:10px;">
          <button
            type="button"
            class="btn btn-glass"
            data-quick-share
            data-share-title="<?= e($article['title']) ?>"
            data-share-text="<?= e($article['summary']) ?>"
            data-share-url="<?= e($articleShareUrl) ?>"
          >
            <i data-lucide="share-2"></i>
            <span>Share</span>
          </button>
          <a href="<?= e(xds_url('contact/')) ?>" class="btn btn-lime">
            <i data-lucide="mail"></i>
            <span>Speak With Our Harare Team</span>
          </a>
        </div>
      </div>
    </article>
    <?php else: ?>
    <div class="glass-card" style="padding:38px;text-align:center;margin-bottom:44px;">
      <h2>Article Unavailable</h2>
      <p>The article link may have moved or been archived.</p>
      <a href="<?= e(xds_url('resources/articles/')) ?>" class="btn btn-primary">
        <i data-lucide="arrow-left"></i>
        <span>Browse All Articles</span>
      </a>
    </div>
    <?php endif; ?>

    <!-- Related Articles -->
    <?php if (!empty($related)): ?>
    <div>
      <div class="section-header" style="margin-bottom:24px;">
        <p class="section-eyebrow">Continue Reading</p>
        <h2 class="section-title" style="font-size:1.65rem;">Related Insights</h2>
        <div class="section-accent-rule"></div>
      </div>

      <div class="cards-grid-3">
        <?php foreach ($related as $rel): ?>
        <article class="glass-card glass-card-hover xds-scroll-card article-card">
          <div style="height:140px;overflow:hidden;border-bottom:1px solid var(--glass-border);">
            <img
              src="<?= e(xds_article_asset($rel['slug'], $rel['image_file'])) ?>"
              alt="<?= e($rel['title']) ?>"
              style="width:100%;height:100%;object-fit:cover;"
              loading="lazy"
            >
          </div>
          <div class="article-card-body">
            <div class="article-meta">
              <span class="category-badge"><?= e($rel['category']) ?></span>
            </div>
            <h3 style="font-size:1.02rem;"><?= e($rel['title']) ?></h3>
            <div class="article-card-footer">
              <a href="<?= e(xds_url('resources/articles/view/?slug=' . rawurlencode((string)$rel['slug']))) ?>" class="btn btn-primary btn-sm" style="width:100%;">
                <span>Read More</span>
                <i data-lucide="arrow-right"></i>
              </a>
            </div>
          </div>
        </article>
        <?php endforeach; ?>
      </div>
    </div>
    <?php endif; ?>
  </div>
</section>

<?php require dirname(__DIR__, 3) . '/inc/layout/footer.php'; ?>
