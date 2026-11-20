<?php

declare(strict_types=1);

require_once dirname(__DIR__) . '/config/index.php';

function xds_seo_organization_schema(array $system): array
{
    $sameAs = array_values(array_filter(array_values($system['social_links'] ?? [])));

    return [
        '@context'    => 'https://schema.org',
        '@type'       => 'FinancialService',
        'name'        => $system['site_name'],
        'url'         => CANONICAL_BASE_URL,
        'logo'        => CANONICAL_ORIGIN . '/assets/images/system/' . rawurlencode(basename((string)$system['logo_file'])),
        'email'       => $system['primary_email'],
        'telephone'   => $system['primary_phone'],
        'address'     => [
            '@type'           => 'PostalAddress',
            'streetAddress'   => '8th Floor Pegasus House, Samora Machel Avenue',
            'addressLocality' => 'Harare',
            'addressCountry'  => 'ZW',
        ],
        'openingHoursSpecification' => [
            '@type'     => 'OpeningHoursSpecification',
            'dayOfWeek' => ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday'],
            'opens'     => '08:00',
            'closes'    => '16:30',
        ],
        'sameAs' => $sameAs,
    ];
}

function xds_render_seo_tags(array $seo, array $system): void
{
    $title       = $seo['title'] ?? $system['site_name'];
    $description = $seo['description'] ?? $system['site_tagline'];
    $canonical   = xds_canonical($seo['canonical_path'] ?? '');
    $ogType      = $seo['og_type'] ?? 'website';
    $ogImage     = !empty($seo['og_image_url'])
        ? $seo['og_image_url']
        : CANONICAL_ORIGIN . '/assets/images/' . rawurlencode(basename((string)($seo['og_image_file'] ?? 'hero-home.webp')));
    $schemas     = $seo['schemas'] ?? [xds_seo_organization_schema($system)];

    echo '<title>' . e($title) . "</title>\n";
    echo '  <meta name="description" content="' . e($description) . "\">\n";
    echo '  <meta name="robots" content="index, follow">' . "\n";
    echo '  <link rel="canonical" href="' . e($canonical) . "\">\n";

    echo '  <meta property="og:site_name" content="' . e($system['site_name']) . "\">\n";
    echo '  <meta property="og:type" content="' . e($ogType) . "\">\n";
    echo '  <meta property="og:title" content="' . e($title) . "\">\n";
    echo '  <meta property="og:description" content="' . e($description) . "\">\n";
    echo '  <meta property="og:url" content="' . e($canonical) . "\">\n";
    echo '  <meta property="og:image" content="' . e($ogImage) . "\">\n";
    echo '  <meta name="twitter:card" content="summary_large_image">' . "\n";
    echo '  <meta name="twitter:title" content="' . e($title) . "\">\n";
    echo '  <meta name="twitter:description" content="' . e($description) . "\">\n";
    echo '  <meta name="twitter:image" content="' . e($ogImage) . "\">\n";

    foreach ($schemas as $schema) {
        if (!is_array($schema)) {
            continue;
        }
        echo '  <script type="application/ld+json">' . "\n";
        echo json_encode($schema, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
        echo "\n  </script>\n";
    }
}

if (realpath($_SERVER['SCRIPT_FILENAME'] ?? '') === __FILE__) {
    $type = strtolower((string)($_GET['type'] ?? 'sitemap'));

    if ($type === 'robots') {
        header('Content-Type: text/plain; charset=UTF-8');
        echo "User-agent: *\n";
        echo "Allow: /\n";
        echo "Disallow: /inc/\n";
        echo "Disallow: /cms/\n";
        echo "Disallow: /portal/\n";
        echo "Disallow: /credit-portal/\n";
        echo "Disallow: /database/\n";
        echo "Disallow: /ref-images/\n\n";
        echo 'Sitemap: ' . xds_canonical('sitemap.xml') . "\n";
        exit;
    }

    header('Content-Type: application/xml; charset=UTF-8');
    $today = date('Y-m-d');

    $staticPages = [
        ['path' => '',                                 'priority' => '1.0', 'changefreq' => 'weekly',  'lastmod' => $today],
        ['path' => 'about/',                           'priority' => '0.9', 'changefreq' => 'monthly', 'lastmod' => $today],
        ['path' => 'products/credit-information/',     'priority' => '0.9', 'changefreq' => 'monthly', 'lastmod' => $today],
        ['path' => 'products/agricultural-services/',  'priority' => '0.9', 'changefreq' => 'monthly', 'lastmod' => $today],
        ['path' => 'products/authentication-services/','priority' => '0.9', 'changefreq' => 'monthly', 'lastmod' => $today],
        ['path' => 'resources/articles/',              'priority' => '0.8', 'changefreq' => 'weekly',  'lastmod' => $today],
        ['path' => 'resources/data-privacy/',          'priority' => '0.7', 'changefreq' => 'yearly',  'lastmod' => $today],
        ['path' => 'resources/faq/',                   'priority' => '0.8', 'changefreq' => 'monthly', 'lastmod' => $today],
        ['path' => 'contact/',                         'priority' => '0.8', 'changefreq' => 'monthly', 'lastmod' => $today],
    ];

    $articles = xds_get_articles(null, null, 1, 100)['items'] ?? [];

    echo '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
    echo '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n";

    foreach ($staticPages as $page) {
        echo "  <url>\n";
        echo '    <loc>' . e(xds_canonical($page['path'])) . "</loc>\n";
        echo '    <lastmod>' . e($page['lastmod']) . "</lastmod>\n";
        echo '    <changefreq>' . e($page['changefreq']) . "</changefreq>\n";
        echo '    <priority>' . e($page['priority']) . "</priority>\n";
        echo "  </url>\n";
    }

    foreach ($articles as $art) {
        $slug = $art['slug'] ?? '';
        if ($slug === '') {
            continue;
        }
        $lastmod = !empty($art['published_date']) ? substr((string)$art['published_date'], 0, 10) : $today;
        echo "  <url>\n";
        echo '    <loc>' . e(xds_canonical('resources/articles/' . $slug . '/')) . "</loc>\n";
        echo '    <lastmod>' . e($lastmod) . "</lastmod>\n";
        echo "    <changefreq>monthly</changefreq>\n";
        echo "    <priority>0.7</priority>\n";
        echo "  </url>\n";
    }

    echo "</urlset>\n";
    exit;
}
