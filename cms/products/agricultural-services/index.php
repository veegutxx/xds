<?php
/**
 * XDS Zimbabwe CMS — Agricultural Services Editor (`/cms/products/agricultural-services/`)
 */
declare(strict_types=1);
require_once dirname(__DIR__, 3) . '/inc/cms/layout.php';

xds_cms_render_shell('agricultural', 'Agricultural Services', [
    ['label' => 'CMS', 'href' => xds_url('cms/')],
    ['label' => 'Products', 'href' => null],
    ['label' => 'Agricultural Services', 'href' => null],
]);
