<?php
/**
 * XDS Zimbabwe — Unified Authentication Endpoint (CMS + Credit Service Portal)
 * Module: inc/auth/index.php
 * Actions:
 *   POST (default / action=login)  -> Authenticates user, sets session, returns role-based redirect_url
 *   GET|POST ?action=logout        -> Destroys session and redirects or returns JSON
 *   GET      ?action=me            -> Returns current authenticated user info
 */

declare(strict_types=1);

require_once dirname(__DIR__) . '/config/index.php';

$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
$action = strtolower(trim((string)($_GET['action'] ?? ($_POST['action'] ?? ''))));

// -----------------------------------------------------------------------------
// Logout
// -----------------------------------------------------------------------------
if ($action === 'logout') {
    if (xds_is_admin()) {
        xds_log_cms_activity('Auth', 'logout', 'Signed out of XDS CMS');
    }
    $_SESSION = [];
    if (ini_get('session.use_cookies')) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000, $params['path'], $params['domain'], (bool)$params['secure'], (bool)$params['httponly']);
    }
    session_destroy();

    $accept = $_SERVER['HTTP_ACCEPT'] ?? '';
    if (stripos($accept, 'application/json') !== false || $method === 'POST') {
        xds_json_response([
            'ok'           => true,
            'message'      => 'Signed out successfully.',
            'redirect_url' => xds_url('portal/'),
        ]);
    }

    header('Location: ' . xds_url('portal/'), true, 302);
    exit;
}

// -----------------------------------------------------------------------------
// Session Check
// -----------------------------------------------------------------------------
if ($method === 'GET' && $action === 'me') {
    $user = xds_current_user();
    xds_json_response([
        'ok'           => true,
        'logged_in'    => $user !== null,
        'db_connected' => xds_db_connected(),
        'user'         => $user,
    ]);
}

// -----------------------------------------------------------------------------
// Login (POST)
// -----------------------------------------------------------------------------
if ($method !== 'POST') {
    xds_json_response(['ok' => false, 'message' => 'Method not allowed.'], 405);
}

$contentType = $_SERVER['CONTENT_TYPE'] ?? '';
if (stripos($contentType, 'application/json') !== false) {
    $input = json_decode(file_get_contents('php://input') ?: '{}', true);
    if (!is_array($input)) {
        $input = [];
    }
} else {
    $input = $_POST;
}

$csrfToken = $input['csrf_token'] ?? ($_SERVER['HTTP_X_CSRF_TOKEN'] ?? null);
if (!xds_verify_csrf(is_string($csrfToken) ? $csrfToken : null)) {
    xds_json_response([
        'ok'      => false,
        'message' => 'Security session expired. Please refresh the page and try again.',
    ], 403);
}

$email    = strtolower(trim((string)($input['email'] ?? '')));
$password = (string)($input['password'] ?? '');

if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    xds_json_response([
        'ok'      => false,
        'message' => 'Please enter a valid account email address.',
        'errors'  => ['email' => 'Enter a valid email address.'],
    ], 422);
}

if ($password === '') {
    xds_json_response([
        'ok'      => false,
        'message' => 'Please enter your password.',
        'errors'  => ['password' => 'Password is required.'],
    ], 422);
}

$userRow = null;
$pdo = xds_db();

if ($pdo !== null) {
    try {
        $stmt = $pdo->prepare(
            'SELECT `id`, `full_name`, `email`, `password_hash`, `role`, `organization`, `is_active`
             FROM `portal_users`
             WHERE LOWER(`email`) = :email
             LIMIT 1'
        );
        $stmt->execute([':email' => $email]);
        $found = $stmt->fetch();
        if (is_array($found)) {
            $userRow = $found;
        }
    } catch (Throwable $e) {
        error_log('[XDS Auth] DB query failed: ' . $e->getMessage());
        xds_json_response([
            'ok'      => false,
            'message' => 'Authentication database error. Please verify `portal_users` table exists in `inungutech_xds`.',
        ], 500);
    }
} else {
    // Graceful local-preview fallback only when MySQL is offline
    $fallbackUsers = [
        'admin@xds.co.zw' => [
            'id'            => 1,
            'full_name'     => 'XDS System Administrator',
            'email'         => 'admin@xds.co.zw',
            'password_hash' => '$2y$10$Lz1w6JPMPeXGUx/y3hoYYu2lOv36vjsIK0hzFIhc7fXMDI9YkBHW2',
            'role'          => 'admin',
            'organization'  => 'Expert Decision Systems Zimbabwe',
            'is_active'     => 1,
        ],
        'client@xds.co.zw' => [
            'id'            => 2,
            'full_name'     => 'Credit Subscriber Demo',
            'email'         => 'client@xds.co.zw',
            'password_hash' => '$2y$10$it5AlwFrlhB2Bq/tnsYdRuuQ1WbVquIeWdZTD1H/eEpOlIBmqoQcm',
            'role'          => 'credit_user',
            'organization'  => 'UNTU Microfinance',
            'is_active'     => 1,
        ],
    ];
    $userRow = $fallbackUsers[$email] ?? null;
}

if ($userRow === null || !password_verify($password, (string)$userRow['password_hash'])) {
    xds_json_response([
        'ok'      => false,
        'message' => 'Invalid email address or password.',
    ], 401);
}

if ((int)($userRow['is_active'] ?? 0) !== 1) {
    xds_json_response([
        'ok'      => false,
        'message' => 'This account has been deactivated. Contact XDS Zimbabwe support.',
    ], 403);
}

session_regenerate_id(true);
$_SESSION['xds_user'] = [
    'id'           => (int)$userRow['id'],
    'full_name'    => (string)$userRow['full_name'],
    'email'        => (string)$userRow['email'],
    'role'         => (string)$userRow['role'],
    'organization' => (string)($userRow['organization'] ?? ''),
];

// Refresh CSRF token after privilege escalation
$_SESSION['xds_csrf_token'] = bin2hex(random_bytes(32));

if ($pdo !== null) {
    try {
        $upd = $pdo->prepare('UPDATE `portal_users` SET `last_login_at` = CURRENT_TIMESTAMP WHERE `id` = :id');
        $upd->execute([':id' => (int)$userRow['id']]);
    } catch (Throwable $e) {
        // Non-fatal
    }
    if (in_array($userRow['role'], ['admin', 'editor'], true)) {
        xds_log_cms_activity('Auth', 'login', 'Signed into XDS CMS Portal');
    }
}

$redirectUrl = xds_redirect_for_role((string)$userRow['role']);

xds_json_response([
    'ok'           => true,
    'role'         => (string)$userRow['role'],
    'redirect_url' => $redirectUrl,
    'message'      => in_array($userRow['role'], ['admin', 'editor'], true)
        ? 'Welcome back! Redirecting to XDS CMS Portal...'
        : 'Welcome back! Redirecting to Credit Service Portal...',
    'user'         => $_SESSION['xds_user'],
]);
