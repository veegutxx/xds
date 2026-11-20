<?php
/**
 * XDS Zimbabwe CMS — Credit Information Services Editor (`/cms/products/credit-information/`)
 */
declare(strict_types=1);
require_once dirname(__DIR__, 3) . '/inc/cms/layout.php';

xds_cms_render_shell('credit', 'Credit Information Services', [
    ['label' => 'CMS', 'href' => xds_url('cms/')],
    ['label' => 'Products', 'href' => null],
    ['label' => 'Credit Information Services', 'href' => null],
]);
