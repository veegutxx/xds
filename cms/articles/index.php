<?php
/**
 * Legacy redirect: `/cms/articles/` -> `/cms/resources/articles/editor/`
 */
declare(strict_types=1);
require_once dirname(__DIR__, 2) . '/inc/config/index.php';

$qs = !empty($_GET['id']) ? ('?id=' . (int)$_GET['id']) : '';
header('Location: ' . xds_url('cms/resources/articles/editor/' . $qs), true, 302);
exit;
