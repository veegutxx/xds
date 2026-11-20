<?php
/**
 * XDS Zimbabwe CMS — Data Privacy Editor (`/cms/resources/data-privacy/`)
 */
declare(strict_types=1);
require_once dirname(__DIR__, 3) . '/inc/cms/layout.php';

xds_cms_render_shell('privacy', 'Data Privacy', [
    ['label' => 'CMS', 'href' => xds_url('cms/')],
    ['label' => 'Resources', 'href' => null],
    ['label' => 'Data Privacy', 'href' => null],
]);
