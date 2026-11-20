<?php
/**
 * XDS Zimbabwe CMS — Portal Users (`/cms/users/`)
 */
declare(strict_types=1);
require_once dirname(__DIR__, 2) . '/inc/cms/layout.php';

xds_cms_render_shell('users', 'Portal Users', [
    ['label' => 'CMS', 'href' => xds_url('cms/')],
    ['label' => 'Portal Users', 'href' => null],
]);
