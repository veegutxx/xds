<?php
/**
 * XDS Zimbabwe CMS — Site Settings (`/cms/settings/`)
 */
declare(strict_types=1);
require_once dirname(__DIR__, 2) . '/inc/cms/layout.php';

xds_cms_render_shell('system', 'Site Settings', [
    ['label' => 'CMS', 'href' => xds_url('cms/')],
    ['label' => 'Site Settings', 'href' => null],
]);
