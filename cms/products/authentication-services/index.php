<?php
/**
 * XDS Zimbabwe CMS — Authentication Services Editor (`/cms/products/authentication-services/`)
 */
declare(strict_types=1);
require_once dirname(__DIR__, 3) . '/inc/cms/layout.php';

xds_cms_render_shell('authentication', 'Authentication Services', [
    ['label' => 'CMS', 'href' => xds_url('cms/')],
    ['label' => 'Products', 'href' => null],
    ['label' => 'Authentication Services', 'href' => null],
]);
