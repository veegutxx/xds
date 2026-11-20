<?php
declare(strict_types=1);
require_once dirname(__DIR__, 2) . '/inc/cms/layout.php';

xds_cms_render_shell('inquiries', 'Contact Us', [
    ['label' => 'CMS', 'href' => xds_url('cms/')],
    ['label' => 'Contact Us', 'href' => null],
]);
