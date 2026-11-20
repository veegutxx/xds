<?php
/**
 * XDS Zimbabwe CMS — Articles Manager (`/cms/resources/articles/`)
 */
declare(strict_types=1);
require_once dirname(__DIR__, 3) . '/inc/cms/layout.php';

xds_cms_render_shell('articles', 'Articles', [
    ['label' => 'CMS', 'href' => xds_url('cms/')],
    ['label' => 'Resources', 'href' => null],
    ['label' => 'Articles', 'href' => null],
]);
