<?php
/**
 * XDS Zimbabwe CMS — Dashboard (`/cms/`)
 */
declare(strict_types=1);
require_once dirname(__DIR__) . '/inc/cms/layout.php';

xds_cms_render_shell('dashboard', 'Dashboard', [
    ['label' => 'CMS', 'href' => xds_url('cms/')],
    ['label' => 'Dashboard', 'href' => null],
]);
