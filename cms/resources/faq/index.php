<?php
/**
 * XDS Zimbabwe CMS — FAQ Manager (`/cms/resources/faq/`)
 */
declare(strict_types=1);
require_once dirname(__DIR__, 3) . '/inc/cms/layout.php';

xds_cms_render_shell('faqs', 'FAQ', [
    ['label' => 'CMS', 'href' => xds_url('cms/')],
    ['label' => 'Resources', 'href' => null],
    ['label' => 'FAQ', 'href' => null],
]);
