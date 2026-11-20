<?php
/**
 * XDS Zimbabwe CMS — About Us Editor (`/cms/about/`)
 */
declare(strict_types=1);
require_once dirname(__DIR__, 2) . '/inc/cms/layout.php';

xds_cms_render_shell('about', 'About Us', [
    ['label' => 'CMS', 'href' => xds_url('cms/')],
    ['label' => 'About Us', 'href' => null],
]);
