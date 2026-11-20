<?php

declare(strict_types=1);

require_once dirname(__DIR__) . '/config/index.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    xds_json_response([
        'ok'      => false,
        'message' => 'Method not allowed. Use POST.',
    ], 405);
}

$contentType = $_SERVER['CONTENT_TYPE'] ?? '';
if (stripos($contentType, 'application/json') !== false) {
    $raw = file_get_contents('php://input');
    $input = json_decode($raw ?: '{}', true);
    if (!is_array($input)) {
        $input = [];
    }
} else {
    $input = $_POST;
}

if (!empty($input['website_url'])) {
    xds_json_response([
        'ok'           => true,
        'message'      => 'Thank you. Your inquiry has been received.',
        'reference_id' => 'XDS-' . strtoupper(bin2hex(random_bytes(3))),
    ]);
}

$csrfToken = $input['csrf_token'] ?? ($_SERVER['HTTP_X_CSRF_TOKEN'] ?? null);
if (!xds_verify_csrf(is_string($csrfToken) ? $csrfToken : null)) {
    xds_json_response([
        'ok'      => false,
        'message' => 'Your security session expired. Please refresh the page and try again.',
    ], 403);
}

$firstname     = trim((string)($input['firstname'] ?? ''));
$lastname      = trim((string)($input['lastname'] ?? ''));
$email         = trim((string)($input['email'] ?? ''));
$phone         = trim((string)($input['phone'] ?? ''));
$company       = trim((string)($input['company'] ?? ''));
$needCategory  = trim((string)($input['need_category'] ?? ''));
$preferredDate = trim((string)($input['preferred_date'] ?? ''));
$preferredTime = trim((string)($input['preferred_time'] ?? ''));
$message       = trim((string)($input['message'] ?? ''));
$sourcePage    = preg_replace('/[^a-z0-9_-]/i', '', (string)($input['source_page'] ?? 'contact')) ?: 'contact';

$errors = [];

if ($firstname === '' || mb_strlen($firstname) > 80) {
    $errors['firstname'] = 'Please enter your first name (max 80 characters).';
}
if ($lastname === '' || mb_strlen($lastname) > 80) {
    $errors['lastname'] = 'Please enter your last name (max 80 characters).';
}
if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL) || mb_strlen($email) > 160) {
    $errors['email'] = 'Please enter a valid email address.';
}
if ($phone !== '' && mb_strlen($phone) > 40) {
    $errors['phone'] = 'Phone number must be 40 characters or fewer.';
}
if ($needCategory === '' || mb_strlen($needCategory) > 160) {
    $errors['need_category'] = 'Please specify your enquiry need.';
}
if ($message === '' || mb_strlen($message) < 10 || mb_strlen($message) > 5000) {
    $errors['message'] = 'Please provide a brief message (at least 10 characters).';
}

// Validate optional Flatpickr date (YYYY-MM-DD) & business hours time (08:00 - 16:30)
$dateForDb = null;
if ($preferredDate !== '') {
    $dt = DateTime::createFromFormat('Y-m-d', $preferredDate);
    if (!$dt || $dt->format('Y-m-d') !== $preferredDate) {
        $errors['preferred_date'] = 'Please select a valid consultation date (YYYY-MM-DD).';
    } else {
        $dayOfWeek = (int)$dt->format('N'); // 6=Sat, 7=Sun
        if ($dayOfWeek >= 6) {
            $errors['preferred_date'] = 'XDS Zimbabwe operates Monday to Friday. Please choose a weekday.';
        } else {
            $dateForDb = $preferredDate;
        }
    }
}

$timeForDb = null;
if ($preferredTime !== '') {
    if (!preg_match('/^([01]\d|2[0-3]):[0-5]\d$/', $preferredTime)) {
        $errors['preferred_time'] = 'Please select a valid time slot (HH:MM).';
    } elseif ($preferredTime < '08:00' || $preferredTime > '16:30') {
        $errors['preferred_time'] = 'Please select a time within business hours (08:00 - 16:30).';
    } else {
        $timeForDb = $preferredTime;
    }
}

if (!empty($errors)) {
    xds_json_response([
        'ok'      => false,
        'message' => 'Please review the highlighted fields and try again.',
        'errors'  => $errors,
    ], 422);
}

$referenceCode = 'XDS-' . date('ymd') . '-' . strtoupper(bin2hex(random_bytes(2)));
$ipAddress = substr((string)($_SERVER['REMOTE_ADDR'] ?? ''), 0, 45);
$userAgent = substr((string)($_SERVER['HTTP_USER_AGENT'] ?? ''), 0, 255);

$storedInDb = false;
$pdo = xds_db();

if ($pdo !== null) {
    try {
        $stmt = $pdo->prepare(
            'INSERT INTO `contact_inquiries`
                (`reference_code`, `firstname`, `lastname`, `email`, `phone`, `company`,
                 `need_category`, `preferred_date`, `preferred_time`, `message`,
                 `source_page`, `ip_address`, `user_agent`)
             VALUES
                (:ref, :fname, :lname, :email, :phone, :company,
                 :need, :pdate, :ptime, :msg,
                 :source, :ip, :ua)'
        );
        $stmt->execute([
            ':ref'     => $referenceCode,
            ':fname'   => $firstname,
            ':lname'   => $lastname,
            ':email'   => $email,
            ':phone'   => $phone !== '' ? $phone : null,
            ':company' => $company !== '' ? $company : null,
            ':need'    => $needCategory,
            ':pdate'   => $dateForDb,
            ':ptime'   => $timeForDb,
            ':msg'     => $message,
            ':source'  => $sourcePage,
            ':ip'      => $ipAddress ?: null,
            ':ua'      => $userAgent ?: null,
        ]);
        $storedInDb = true;
    } catch (Throwable $e) {
        error_log('[XDS Contact] Insert failed: ' . $e->getMessage());
        xds_json_response([
            'ok'      => false,
            'message' => 'We could not record your inquiry right now. Please call +263 242 779 344 or email info@xds.co.zw.',
        ], 500);
    }
}

xds_json_response([
    'ok'           => true,
    'stored'       => $storedInDb,
    'reference_id' => $referenceCode,
    'message'      => sprintf(
        'Thank you, %s! Your enquiry (%s) has been received. Our Harare team will respond during operating hours (Mon–Fri, 08:00–16:30).',
        $firstname,
        $referenceCode
    ),
], 201);
