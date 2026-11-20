<?php
/**
 * XDS Zimbabwe — Core Configuration, PDO Connection, Path Constants & Helpers
 * Module: inc/config/index.php
 */

declare(strict_types=1);

if (defined('XDS_CONFIG_LOADED')) {
    return;
}
define('XDS_CONFIG_LOADED', true);

// -----------------------------------------------------------------------------
// 1. Session & Security Bootstrap
// -----------------------------------------------------------------------------
if (session_status() === PHP_SESSION_NONE) {
    $isHttps = (
        (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ||
        (isset($_SERVER['SERVER_PORT']) && (int)$_SERVER['SERVER_PORT'] === 443) ||
        (!empty($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https')
    );

    session_set_cookie_params([
        'lifetime' => 0,
        'path'     => '/',
        'secure'   => $isHttps,
        'httponly' => true,
        'samesite' => 'Lax',
    ]);
    session_start();
}

// -----------------------------------------------------------------------------
// 2. Environment, Base URL & Hardcoded Asset Path Constants
//    (DB stores ONLY filenames; PHP injects them onto these base constants.)
// -----------------------------------------------------------------------------
define('ROOT_DIR', realpath(__DIR__ . '/../../') ?: dirname(__DIR__, 2));

$scheme = (
    (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ||
    (!empty($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https')
) ? 'https' : 'http';

$host = $_SERVER['HTTP_HOST'] ?? 'www.xds.co.zw';
$docRoot = isset($_SERVER['DOCUMENT_ROOT']) ? realpath($_SERVER['DOCUMENT_ROOT']) : '';
$basePath = '/';

if ($docRoot && str_starts_with(str_replace('\\', '/', ROOT_DIR), str_replace('\\', '/', $docRoot))) {
    $rel = trim(substr(str_replace('\\', '/', ROOT_DIR), strlen(str_replace('\\', '/', $docRoot))), '/');
    $basePath = $rel === '' ? '/' : '/' . $rel . '/';
}

define('BASE_PATH', $basePath);
define('SITE_ORIGIN', $scheme . '://' . $host);
define('BASE_URL', SITE_ORIGIN . BASE_PATH);
define('CANONICAL_ORIGIN', 'https://xds.co.zw');
define('CANONICAL_BASE_URL', CANONICAL_ORIGIN . '/');

define('ASSETS_URL', BASE_PATH . 'assets/');
define('ASSETS_CSS_URL', ASSETS_URL . 'css/');
define('ASSETS_JS_URL', ASSETS_URL . 'js/');
define('ASSETS_IMAGES_URL', ASSETS_URL . 'images/');
define('ASSETS_SYSTEM_URL', ASSETS_IMAGES_URL . 'system/');
define('ASSETS_ARTICLES_URL', ASSETS_IMAGES_URL . 'articles/');
define('ASSETS_DOCUMENTS_URL', ASSETS_URL . 'documents/');

// -----------------------------------------------------------------------------
// 3. Database Configuration (`inungutech_xds` / `inungutech_admin`)
//    Optional local override file: inc/config/db.local.php
// -----------------------------------------------------------------------------
if (is_file(__DIR__ . '/db.local.php')) {
    require_once __DIR__ . '/db.local.php';
}

if (!defined('DB_HOST')) define('DB_HOST', getenv('XDS_DB_HOST') ?: '127.0.0.1');
if (!defined('DB_PORT')) define('DB_PORT', getenv('XDS_DB_PORT') ?: '3306');
if (!defined('DB_NAME')) define('DB_NAME', getenv('XDS_DB_NAME') ?: 'inungutech_xds');
if (!defined('DB_USER')) define('DB_USER', getenv('XDS_DB_USER') ?: 'inungutech_admin');
if (!defined('DB_PASS')) define('DB_PASS', getenv('XDS_DB_PASS') !== false ? (string)getenv('XDS_DB_PASS') : '');
if (!defined('DB_CHARSET')) define('DB_CHARSET', 'utf8mb4');
if (!defined('DB_SOCKET')) define('DB_SOCKET', getenv('XDS_DB_SOCKET') ?: '/opt/lampp/var/mysql/mysql.sock');

/**
 * Returns a singleton PDO instance or null if database is unreachable.
 * Tries TCP first, then LAMPP Unix socket if present.
 */
function xds_db(): ?PDO
{
    static $pdo = false;
    if ($pdo !== false) {
        return $pdo;
    }

    $options = [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,
        PDO::ATTR_TIMEOUT            => 3,
    ];

    $dsns = [
        sprintf('mysql:host=%s;port=%s;dbname=%s;charset=%s', DB_HOST, DB_PORT, DB_NAME, DB_CHARSET),
    ];

    if (DB_SOCKET !== '' && file_exists(DB_SOCKET)) {
        $dsns[] = sprintf('mysql:unix_socket=%s;dbname=%s;charset=%s', DB_SOCKET, DB_NAME, DB_CHARSET);
    }

    foreach ($dsns as $dsn) {
        try {
            $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
            return $pdo;
        } catch (Throwable $e) {
            error_log('[XDS DB] Connection attempt failed: ' . $e->getMessage());
        }
    }

    $pdo = null;
    return $pdo;
}

function xds_db_connected(): bool
{
    return xds_db() !== null;
}

function xds_portal_href(string $url): string
{
    $trimmed = trim($url);
    if ($trimmed === '') {
        return xds_url('portal/');
    }
    if (preg_match('#^https?://#i', $trimmed)) {
        return $trimmed;
    }
    return xds_url(ltrim($trimmed, '/'));
}

function xds_is_external_url(string $url): bool
{
    return (bool)preg_match('#^https?://#i', trim($url));
}

// -----------------------------------------------------------------------------
// 4. URL, Asset & Escaping Helpers
// -----------------------------------------------------------------------------
function xds_url(string $path = ''): string
{
    $clean = ltrim($path, '/');
    return BASE_PATH . $clean;
}

function xds_canonical(string $path = ''): string
{
    $clean = ltrim($path, '/');
    return CANONICAL_BASE_URL . $clean;
}

function xds_system_asset(string $filename): string
{
    return ASSETS_SYSTEM_URL . rawurlencode(basename($filename));
}

function xds_image_asset(string $filename): string
{
    return ASSETS_IMAGES_URL . rawurlencode(basename($filename));
}

function xds_article_asset(string $slug, string $filename): string
{
    $safeSlug = preg_replace('/[^a-z0-9-]/i', '', $slug) ?: 'default';
    return ASSETS_ARTICLES_URL . $safeSlug . '/' . rawurlencode(basename($filename));
}

function xds_document_asset(string $purpose, string $docFolder, string $filename): string
{
    $safePurpose = preg_replace('/[^a-z0-9_-]/i', '', $purpose) ?: 'general';
    $safeFolder  = preg_replace('/[^a-z0-9_-]/i', '', $docFolder) ?: 'document';
    return ASSETS_DOCUMENTS_URL . $safePurpose . '/' . $safeFolder . '/' . rawurlencode(basename($filename));
}

function e(?string $value): string
{
    return htmlspecialchars((string)$value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

function xds_decode_json($value, array $default = []): array
{
    if (is_array($value)) {
        return $value;
    }
    if (!is_string($value) || trim($value) === '') {
        return $default;
    }
    $decoded = json_decode($value, true);
    return is_array($decoded) ? $decoded : $default;
}

// -----------------------------------------------------------------------------
// 5. CSRF & JSON Response Helpers
// -----------------------------------------------------------------------------
function xds_csrf_token(): string
{
    if (empty($_SESSION['xds_csrf_token'])) {
        $_SESSION['xds_csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['xds_csrf_token'];
}

function xds_verify_csrf(?string $token): bool
{
    if (empty($token) || empty($_SESSION['xds_csrf_token'])) {
        return false;
    }
    return hash_equals($_SESSION['xds_csrf_token'], $token);
}

function xds_json_response(array $payload, int $statusCode = 200): never
{
    http_response_code($statusCode);
    header('Content-Type: application/json; charset=UTF-8');
    header('Cache-Control: no-store, no-cache, must-revalidate');
    echo json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit;
}

// -----------------------------------------------------------------------------
// 6. Per-Page Data Loaders (PDO with Safe Fallback Defaults)
// -----------------------------------------------------------------------------
require_once __DIR__ . '/defaults.php';

function xds_fetch_single_table(string $table, string $fallbackKey): array
{
    $allowed = [
        'site_system',
        'page_home',
        'page_about',
        'page_credit_services',
        'page_agricultural_services',
        'page_authentication_services',
        'page_data_privacy',
        'page_contact',
    ];
    if (!in_array($table, $allowed, true)) {
        return xds_defaults($fallbackKey);
    }

    $pdo = xds_db();
    if ($pdo !== null) {
        try {
            $stmt = $pdo->query("SELECT * FROM `{$table}` ORDER BY `id` ASC LIMIT 1");
            $row = $stmt ? $stmt->fetch() : false;
            if (is_array($row) && !empty($row)) {
                return array_merge(xds_defaults($fallbackKey), $row);
            }
        } catch (Throwable $e) {
            error_log("[XDS DB] Fallback triggered for {$table}: " . $e->getMessage());
        }
    }

    return xds_defaults($fallbackKey);
}

function xds_get_system(): array
{
    static $system = null;
    if ($system !== null) {
        return $system;
    }
    $row = xds_fetch_single_table('site_system', 'site_system');
    $row['social_links'] = xds_decode_json($row['social_links_json'] ?? null, []);
    $row['regional_links'] = xds_decode_json($row['regional_links_json'] ?? null, []);
    $system = $row;
    return $system;
}

function xds_get_articles(?string $category = null, ?string $search = null, int $page = 1, int $perPage = 12): array
{
    $pdo = xds_db();
    $page = max(1, $page);
    $perPage = max(1, min(50, $perPage));
    $offset = ($page - 1) * $perPage;

    if ($pdo !== null) {
        try {
            $where = ['`is_active` = 1'];
            $params = [];
            if ($category !== null && $category !== '' && strtolower($category) !== 'all') {
                $where[] = '`category` = :category';
                $params[':category'] = $category;
            }
            if ($search !== null && trim($search) !== '') {
                $where[] = '(`title` LIKE :q1 OR `summary` LIKE :q2 OR `category` LIKE :q3)';
                $like = '%' . trim($search) . '%';
                $params[':q1'] = $like;
                $params[':q2'] = $like;
                $params[':q3'] = $like;
            }
            $whereSql = implode(' AND ', $where);

            $countStmt = $pdo->prepare("SELECT COUNT(*) FROM `page_articles` WHERE {$whereSql}");
            $countStmt->execute($params);
            $total = (int)$countStmt->fetchColumn();

            $sql = "SELECT * FROM `page_articles` WHERE {$whereSql} ORDER BY `sort_order` ASC, `published_date` DESC LIMIT :limit OFFSET :offset";
            $stmt = $pdo->prepare($sql);
            foreach ($params as $k => $v) {
                $stmt->bindValue($k, $v, PDO::PARAM_STR);
            }
            $stmt->bindValue(':limit', $perPage, PDO::PARAM_INT);
            $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
            $stmt->execute();
            $items = $stmt->fetchAll();

            $catStmt = $pdo->query("SELECT DISTINCT `category` FROM `page_articles` WHERE `is_active` = 1 ORDER BY `category` ASC");
            $categories = $catStmt ? $catStmt->fetchAll(PDO::FETCH_COLUMN) : [];

            return [
                'items'       => $items,
                'total'       => $total,
                'page'        => $page,
                'per_page'    => $perPage,
                'total_pages' => (int)ceil($total / $perPage),
                'categories'  => $categories,
            ];
        } catch (Throwable $e) {
            error_log('[XDS DB] Articles fallback: ' . $e->getMessage());
        }
    }

    $all = xds_defaults('page_articles');
    $categories = array_values(array_unique(array_column($all, 'category')));
    sort($categories);

    $filtered = array_values(array_filter($all, static function (array $item) use ($category, $search): bool {
        if ($category !== null && $category !== '' && strtolower($category) !== 'all') {
            if (strcasecmp($item['category'], $category) !== 0) {
                return false;
            }
        }
        if ($search !== null && trim($search) !== '') {
            $q = mb_strtolower(trim($search));
            $haystack = mb_strtolower($item['title'] . ' ' . $item['summary'] . ' ' . $item['category']);
            if (!str_contains($haystack, $q)) {
                return false;
            }
        }
        return true;
    }));

    $total = count($filtered);
    return [
        'items'       => array_slice($filtered, $offset, $perPage),
        'total'       => $total,
        'page'        => $page,
        'per_page'    => $perPage,
        'total_pages' => max(1, (int)ceil($total / $perPage)),
        'categories'  => $categories,
    ];
}

function xds_get_article_by_slug(string $slug): ?array
{
    $safeSlug = preg_replace('/[^a-z0-9-]/i', '', $slug) ?: '';
    if ($safeSlug === '') {
        return null;
    }

    $pdo = xds_db();
    if ($pdo !== null) {
        try {
            $stmt = $pdo->prepare("SELECT * FROM `page_articles` WHERE `slug` = :slug AND `is_active` = 1 LIMIT 1");
            $stmt->execute([':slug' => $safeSlug]);
            $row = $stmt->fetch();
            if (is_array($row) && !empty($row)) {
                return $row;
            }
        } catch (Throwable $e) {
            error_log('[XDS DB] Article slug fallback: ' . $e->getMessage());
        }
    }

    foreach (xds_defaults('page_articles') as $item) {
        if ($item['slug'] === $safeSlug) {
            return $item;
        }
    }
    return null;
}

function xds_get_faqs(?string $category = null, ?string $search = null): array
{
    $pdo = xds_db();
    if ($pdo !== null) {
        try {
            $where = ['`is_active` = 1'];
            $params = [];
            if ($category !== null && $category !== '' && strtolower($category) !== 'all') {
                $where[] = '`category` = :category';
                $params[':category'] = $category;
            }
            if ($search !== null && trim($search) !== '') {
                $where[] = '(`question` LIKE :q1 OR `answer` LIKE :q2 OR `category` LIKE :q3)';
                $like = '%' . trim($search) . '%';
                $params[':q1'] = $like;
                $params[':q2'] = $like;
                $params[':q3'] = $like;
            }
            $whereSql = implode(' AND ', $where);
            $stmt = $pdo->prepare("SELECT * FROM `page_faq` WHERE {$whereSql} ORDER BY `sort_order` ASC, `id` ASC");
            $stmt->execute($params);
            $items = $stmt->fetchAll();

            $catStmt = $pdo->query("SELECT DISTINCT `category` FROM `page_faq` WHERE `is_active` = 1 ORDER BY `category` ASC");
            $categories = $catStmt ? $catStmt->fetchAll(PDO::FETCH_COLUMN) : [];

            return [
                'items'      => $items,
                'categories' => $categories,
                'total'      => count($items),
            ];
        } catch (Throwable $e) {
            error_log('[XDS DB] FAQ fallback: ' . $e->getMessage());
        }
    }

    $all = xds_defaults('page_faq');
    $categories = array_values(array_unique(array_column($all, 'category')));
    sort($categories);

    $filtered = array_values(array_filter($all, static function (array $item) use ($category, $search): bool {
        if ($category !== null && $category !== '' && strtolower($category) !== 'all') {
            if (strcasecmp($item['category'], $category) !== 0) {
                return false;
            }
        }
        if ($search !== null && trim($search) !== '') {
            $q = mb_strtolower(trim($search));
            $haystack = mb_strtolower($item['question'] . ' ' . $item['answer'] . ' ' . $item['category']);
            if (!str_contains($haystack, $q)) {
                return false;
            }
        }
        return true;
    }));

    return [
        'items'      => $filtered,
        'categories' => $categories,
        'total'      => count($filtered),
    ];
}

// -----------------------------------------------------------------------------
// 7. Unified Portal & CMS Session / Role Helpers
// -----------------------------------------------------------------------------
function xds_current_user(): ?array
{
    $user = $_SESSION['xds_user'] ?? null;
    return is_array($user) && !empty($user['email']) ? $user : null;
}

function xds_is_logged_in(): bool
{
    return xds_current_user() !== null;
}

function xds_is_admin(): bool
{
    $u = xds_current_user();
    return $u !== null && in_array($u['role'] ?? '', ['admin', 'editor'], true);
}

function xds_redirect_for_role(string $role): string
{
    if (in_array($role, ['admin', 'editor'], true)) {
        return xds_url('cms/');
    }
    return xds_url('credit-portal/');
}

function xds_require_login(): array
{
    $user = xds_current_user();
    if ($user === null) {
        header('Location: ' . xds_url('portal/'), true, 302);
        exit;
    }
    return $user;
}

function xds_require_admin(): array
{
    $user = xds_require_login();
    if (!in_array($user['role'] ?? '', ['admin', 'editor'], true)) {
        header('Location: ' . xds_url('credit-portal/'), true, 302);
        exit;
    }
    return $user;
}

function xds_log_cms_activity(string $section, string $action, string $summary): void
{
    $pdo = xds_db();
    if ($pdo === null) {
        return;
    }
    $user = xds_current_user();
    try {
        $stmt = $pdo->prepare(
            'INSERT INTO `cms_activity_log` (`user_id`, `user_name`, `section`, `action`, `summary`)
             VALUES (:uid, :uname, :sec, :act, :sum)'
        );
        $stmt->execute([
            ':uid'   => $user['id'] ?? null,
            ':uname' => $user['full_name'] ?? 'System Admin',
            ':sec'   => substr($section, 0, 80),
            ':act'   => substr($action, 0, 60),
            ':sum'   => substr($summary, 0, 255),
        ]);
    } catch (Throwable $e) {
        error_log('[XDS CMS Log] ' . $e->getMessage());
    }
}
