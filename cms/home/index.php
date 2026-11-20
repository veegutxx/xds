<?php
/**
 * XDS Zimbabwe CMS — Home Page Editor (`/cms/home/`)
 */
declare(strict_types=1);
require_once dirname(__DIR__, 2) . '/inc/cms/layout.php';

xds_cms_render_shell('home', 'Home Page', [
    ['label' => 'CMS', 'href' => xds_url('cms/')],
    ['label' => 'Home Page', 'href' => null],
]);
